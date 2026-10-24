<?php declare(strict_types=1);
/**
 * Docker Installation
 *
 * Generate a Docker environment from the application's existing configuration.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Docker;

/**
 * Dependencies
 */
use CrazyPHP\Library\File\Docker\Bootstrap;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Model\Env;
use Dotenv\Dotenv;

/**
 * Install
 *
 * Select a server and TLS strategy, generate services, then validate and build.
 */
class Install extends CrazyModel implements CrazyCommand {

    /** Public Constants
     ******************************************************
     */

    /** @var array STORYLINE Shared order for direct and interactive execution */
    public const STORYLINE = [
        "runCheck",
        "runStructureFolder",
        "runDockerConfig",
        "runDatabaseServices",
        "runValidate",
        "runDockerComposeBuild",
    ];

    /** @var array REQUIRED_VALUES Questions following the Docker refactor graph */
    public const REQUIRED_VALUES = [
        [
            "name" => "type",
            "description" => "Server type",
            "type" => "VARCHAR",
            "required" => true,
            "default" => "nginx",
            "select" => ["nginx" => "Nginx", "apache" => "Apache 2", "frankenphp" => "FrankenPHP"],
        ],
        [
            "name" => "security",
            "description" => "Connection security",
            "type" => "VARCHAR",
            "required" => true,
            "default" => "http",
            "select" => ["http" => "HTTP", "https" => "HTTPS"],
        ],
        [
            "name" => "proxy",
            "description" => "Do you already have a reverse proxy handling HTTPS?",
            "type" => "VARCHAR",
            "required" => true,
            "default" => "no",
            "select" => ["no" => "No — configure Caddy", "yes" => "Yes — show Caddy / Nginx instructions"],
            "when" => ["security" => ["https"]],
        ],
        [
            "name" => "hostname",
            "description" => "HTTPS hostname (localhost for local development)",
            "type" => "VARCHAR",
            "required" => true,
            "default" => [self::class, "defaultHostname"],
            "process" => ["trim"],
            "when" => ["security" => ["https"]],
        ],
        [
            "name" => "http_port",
            "description" => "HTTP host port (a free port is proposed)",
            "type" => "INT",
            "required" => true,
            "default" => [self::class, "defaultHttpPort"],
            "defaultWithAnswers" => true,
        ],
        [
            "name" => "https_port",
            "description" => "HTTPS host port (a free port is proposed)",
            "type" => "INT",
            "required" => true,
            "default" => [self::class, "defaultHttpsPort"],
            "defaultWithAnswers" => true,
            "when" => ["security" => ["https"], "proxy" => ["no"]],
        ],
    ];

    /** Private Parameters
     ******************************************************
     */

    /** @var array $_options Validated wizard answers */
    private array $_options;

    /** @var Runtime $_runtime Checked subprocess execution */
    private Runtime $_runtime;

    /** @var array $_compose Generated Compose document */
    private array $_compose = [];

    /** @var array $_databases Enabled database definitions */
    private array $_databases = [];

    /** @var string $_root Canonical application path */
    private string $_root;

    /** @var string $_public Application document root */
    private string $_public;

    /** @var bool $_checked Avoid repeating preflight within one execution */
    private bool $_checked = false;

    /**
     * Constructor
     *
     * @param array $inputs Named options or CLI form records
     * @param Runtime|null $runtime Injectable command runner
     */
    public function __construct(array $inputs = [], ?Runtime $runtime = null){

        # Reuse the project command's checked runtime
        $this->_runtime = $runtime ?? new Runtime();

        # Set conservative defaults without changing application files
        $options = [
            "type"          => "nginx",
            "security"      => "http",
            "proxy"         => "no",
            "hostname"      => self::defaultHostname(),
            "http_port"     => null,
            "https_port"    => null,
        ];

        # Accept both the CLI wrapper and programmatic named values
        foreach($inputs["input"] ?? $inputs as $key => $value)

            # Normalize form records while preserving their selected values
            if(is_array($value) && isset($value["name"]))

                # Read the answer produced by the shared form
                $options[$value["name"]] = $value["value"] ?? $value["default"] ?? "";

            else

                # Accept direct options for automation and tests
                $options[$key] = $value;

        # Reject legacy TLS choices rather than silently choosing HTTP
        if(isset($options["configuration"]))

            # Explain the replacement contract
            throw new CrazyException("Use type, security and proxy options; the legacy configuration choices have been removed.", 400);

        # Validate choices before generating any files
        foreach(["type" => ["nginx", "apache", "frankenphp"], "security" => ["http", "https"], "proxy" => ["yes", "no"]] as $key => $choices)

            # Reject unknown values supplied outside the interactive form
            if(!in_array($options[$key], $choices, true))

                # Keep invalid input out of server configuration
                throw new CrazyException("Invalid Docker $key.", 400);

        # HTTP does not use the HTTPS proxy branch
        if($options["security"] === "http")

            # Ignore a stale proxy answer when security was changed
            $options["proxy"] = "no";

        # Accept a single DNS hostname, including localhost, without config directives
        if(!is_string($options["hostname"]) || strlen($options["hostname"]) > 253 || !preg_match('/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/D', $options["hostname"]))

            # Require the hostname rather than a URL or a configuration fragment
            throw new CrazyException("Enter a hostname without a scheme, port or path.", 400);

        # Propose a free HTTP port when none was chosen
        if($options["http_port"] === null || $options["http_port"] === "")

            # Use the same proposal as the interactive question
            $options["http_port"] = self::defaultHttpPort($options, $this->_runtime);

        # Propose a free HTTPS port when none was chosen
        if($options["https_port"] === null || $options["https_port"] === "")

            # Use the same proposal as the interactive question
            $options["https_port"] = self::defaultHttpsPort($options, $this->_runtime);

        # Iteration of host ports
        foreach(["http_port", "https_port"] as $key){

            # Check port is a valid TCP port
            if(!preg_match('/^[0-9]{1,5}$/D', (string) $options[$key]) || (int) $options[$key] < 1 || (int) $options[$key] > 65535)

                # New Exception
                throw new CrazyException("Docker $key must be a number between 1 and 65535.", 400);

            # Normalize port
            $options[$key] = (int) $options[$key];

        }

        # Check managed HTTPS does not publish both protocols on one host port
        if($options["security"] === "https" && $options["proxy"] === "no" && $options["http_port"] === $options["https_port"])

            # New Exception
            throw new CrazyException("Docker HTTP and HTTPS ports must be different.", 400);

        # Store the validated choices
        $this->_options = $options;

        # Use file caching while preparing a database-backed application
        Env::set(["cache_driver" => "Files"]);

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Required Values
     *
     * @return array
     */
    public static function getRequiredValues():array {

        # Return the conditional questions in graph order
        return self::REQUIRED_VALUES;

    }

    /**
     * Default Hostname
     *
     * @return string
     */
    public static function defaultHostname():string {

        # Read the application directly so a missing optional key is harmless
        $path = File::path("@app_root/config/App.yml");

        # Prefer the hostname already chosen by the application
        $hostname = is_file($path) ? (Yaml::open($path)["App"]["server"]["name"] ?? null) : null;

        # Treat an unset application hostname as local development
        return is_string($hostname) && $hostname !== "" ? $hostname : "localhost";

    }

    /**
     * Default Http Port
     *
     * Propose the saved or standard HTTP port, or the next free port when it is used.
     *
     * @param array $answers Previous form answers or named options
     * @param Runtime|null $runtime Port probe
     * @return int
     */
    public static function defaultHttpPort(array $answers = [], ?Runtime $runtime = null):int {

        # An existing proxy forwards to a local backend port
        $proxy = self::_answer($answers, "proxy", "no") === "yes";

        # Prefer the port saved in .env, then the standard port for the branch
        $preferred = self::_environmentPort("CRAZY_HTTP_PORT") ?? ($proxy ? 8080 : 80);

        # Return a free port
        return self::_availablePort($preferred, 8080, [], $runtime ?? new Runtime());

    }

    /**
     * Default Https Port
     *
     * Propose the saved or standard HTTPS port, or the next free port when it is used.
     *
     * @param array $answers Previous form answers or named options
     * @param Runtime|null $runtime Port probe
     * @return int
     */
    public static function defaultHttpsPort(array $answers = [], ?Runtime $runtime = null):int {

        # Never propose the port already chosen for HTTP
        $exclude = [(int) self::_answer($answers, "http_port", 0)];

        # Prefer the port saved in .env, then the standard HTTPS port
        $preferred = self::_environmentPort("CRAZY_HTTPS_PORT") ?? 443;

        # Return a free port
        return self::_availablePort($preferred, 8443, $exclude, $runtime ?? new Runtime());

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Run
     *
     * @return self
     */
    public function run():self {

        # Execute the same explicit sequence used by the interactive CLI
        foreach(self::STORYLINE as $action)

            # Stop automatically when any action throws
            $this->{$action}();

        # Return the completed installer
        return $this;

    }

    /**
     * Run Check
     *
     * @return self
     */
    public function runCheck():self {

        # Keep checks idempotent for the interactive preview
        if($this->_checked)

            # Reuse this instance's successful preflight
            return $this;

        # Require Docker and Compose before preparing the project
        $this->_runtime->execute("docker", ["info", "--format", "{{.ServerVersion}}"]);

        # Require the supported Compose plugin instead of the legacy executable
        $this->_runtime->execute("docker", ["compose", "version"]);

        # Resolve the actual application directory for bind mounts and linked packages
        $this->_root = realpath(File::path("@app_root")) ?: throw new CrazyException("Application directory does not exist.", 400);

        # Require an initialized project
        if(!is_file($this->_root."/config/App.yml"))

            # Avoid creating Docker files in an unrelated working directory
            throw new CrazyException("Create the project before running CrazyDocker new.", 400);

        # Preserve existing custom Docker setups rather than overwriting them
        foreach(["docker", "docker-compose.yml", "docker-compose.yaml", "compose.yml", "compose.yaml", "config/Docker.yml"] as $path)

            # Reject partial installations too, allowing the user to preserve their files
            if(file_exists($this->_root."/".$path))

                # Explain how to retry without losing custom configuration
                throw new CrazyException("$path already exists; move or back it up before creating a new Docker setup.", 400);

        # Resolve the application's public folder without allowing server config injection
        $public = Yaml::open($this->_root."/config/App.yml")["App"]["public"] ?? "public";

        # Require a relative directory inside the application
        if(!is_string($public) || !preg_match('/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/D', $public))

            # Explain the invalid document root before writing files
            throw new CrazyException("App.public must be a relative directory inside the project.", 400);

        # Store the container document root while preserving the host path
        $this->_public = $this->_root."/".$public;

        # Read all configured engines, including projects without database configuration
        $collection = is_file($this->_root."/config/Database.yml") ? (Yaml::open($this->_root."/config/Database.yml")["Database"]["collection"] ?? []) : [];

        # Keep enabled definitions only
        foreach($collection as $engine => $configuration){

            # Skip explicitly disabled engines
            if(!$configuration)

                # Continue with the next configured database
                continue;

            # Reject unsupported configurations before partial file generation
            if(!isset(Docker::DATABASE_TO_SERVICE[$engine]) || !is_array($configuration))

                # Require a supported database service
                throw new CrazyException("Unsupported Docker database: $engine", 400);

            # Require administrator credentials for images that initialize a separate root account
            if($engine !== "postgresql" && empty($configuration["root"]["password"]))

                # Point users to the source without exposing the password
                throw new CrazyException("Set the $engine administrator password in config/Database.yml.", 400);

            # The official MySQL entrypoint interpolates its root password into SQL and a client option file
            if($engine === "mysql" && strpbrk((string)$configuration["root"]["password"], "'\"\\\r\n\0") !== false)

                # Reject unsupported image input before leaving a partially initialized database volume
                throw new CrazyException("The MySQL image requires a root password without quotes, backslashes or line breaks. Update mysql.root.password in config/Database.yml; application passwords are handled separately by preparation.", 400);

            # Require an initial database and its account for SQL engines
            if(empty($configuration["database"][0]) || ($engine !== "mongodb" && (empty($configuration["users"][0]["login"]) || empty($configuration["users"][0]["password"]))))

                # Explain incomplete database configuration
                throw new CrazyException("Set the $engine database and default user in config/Database.yml.", 400);

            # Preserve every enabled engine for Compose generation
            $this->_databases[$engine] = $configuration;

        }

        # Mark successful validation for later generation steps
        $this->_checked = true;

        # Return the checked installer
        return $this;

    }

    /**
     * Describe
     *
     * Preview the security branch before the CLI's final confirmation.
     *
     * @return string
     */
    public function describe():string {

        # Describe the chosen server without displaying database credentials
        $summary = "Server: ".$this->_options["type"]." (PHP ".Composer::getPhpVersion()."); security: ".$this->_options["security"].PHP_EOL;

        # Iteration of published host ports
        foreach($this->_ports() as $label => $port)

            # Describe the port and warn when another service already uses it
            $summary .= "$label port: $port".($this->_runtime->isPortAvailable($port) ? "" : " (currently in use; free it or choose another port before docker compose up)").PHP_EOL;

        # Include the existing-proxy instructions before file creation
        if($this->_options["proxy"] === "yes")

            # Explain the upstream endpoint and forwarded request headers
            $summary .= $this->_proxyInstructions();

        elseif($this->_options["security"] === "https")

            # Distinguish the integrated server from a dedicated proxy service
            $summary .= ($this->_options["type"] === "frankenphp" ? "FrankenPHP's integrated Caddy" : "A Caddy 2 service")." will manage HTTPS when started. Local certificates require trusting Caddy's CA on your client.".PHP_EOL;

        # Report only names of the configured database engines
        return $summary."Database services: ".(implode(", ", array_keys($this->_databases)) ?: "none").PHP_EOL."This command generates and builds the stack; it does not start it.";

    }

    /**
     * Run Structure Folder
     *
     * @return self
     */
    public function runStructureFolder():self {

        # Ensure direct calls cannot bypass preflight
        $this->runCheck();

        # Create only the folders needed by the selected server
        foreach(["docker/php", "docker/bin", "docker/config", "docker/".$this->_options["type"]] as $folder)

            # Create nested directories while preserving existing application content
            if(!is_dir($this->_root."/".$folder) && !mkdir($this->_root."/".$folder, 0755, true))

                # Stop on a filesystem failure
                throw new CrazyException("Could not create $folder.", 500);

        # Keep host database credentials private while allowing their container bind mount to be readable
        chmod($this->_root."/docker/config", 0700);

        # Create a separate proxy folder only when Caddy terminates TLS for another server
        if($this->_managedTls() && $this->_options["type"] !== "frankenphp")

            # Prepare the Caddyfile destination
            mkdir($this->_root."/docker/caddy", 0755, true);

        # Copy the database provisioning entrypoint executed by Compose preparation
        $this->_write("docker/bin/SetupDatabase", file_get_contents(File::path("@crazyphp_root/resources/Docker/docker/bin/SetupDatabase")));

        # Copy the checked dependency installation script
        $this->_write("docker/bin/Prepare", file_get_contents(File::path("@crazyphp_root/resources/Docker/docker/bin/Prepare")));

        # Return the installer
        return $this;

    }

    /**
     * Run Docker Config
     *
     * @return self
     */
    public function runDockerConfig():self {

        # Identify whether the PHP server also serves HTTP
        $integrated = $this->_options["type"] !== "nginx";

        # Keep the legacy PHP service name for Nginx applications
        $phpService = $integrated ? "webserver" : "php-fpm";

        # Keep host paths identical for installed Composer symlinks
        $mounts = [["type" => "bind", "source" => $this->_root, "target" => $this->_root]];

        # Resolve the actual library checkout instead of mounting an unresolved symlink
        $library = realpath(File::path("@crazyphp_root"));

        # Include external framework sources when the application uses a path repository
        if($library && !str_starts_with($library, $this->_root."/"))

            # Make the symlink target accessible inside the PHP container
            $mounts[] = ["type" => "bind", "source" => $library, "target" => $library];

        # Define the selected PHP runtime without publishing its internal port
        $php = ["build" => ["context" => "./docker/php"], "working_dir" => $this->_root, "volumes" => $mounts, "restart" => "unless-stopped", "environment" => ["CRAZYPHP_DOCKER" => "1"]];

        # Mount the generated PHP overrides at the official images' common path
        $php["volumes"][] = "./docker/php/php.ini:/usr/local/etc/php/conf.d/99-crazyphp.ini:ro";

        # Start the Compose document with the PHP runtime
        $this->_compose = ["services" => [$phpService => $php]];

        # Add Nginx in front of PHP-FPM when selected
        if(!$integrated)

            # Share the public directory and forward PHP requests over the private network
            $this->_compose["services"]["webserver"] = ["image" => "nginx:stable-alpine", "volumes" => [...$mounts, "./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro"], "depends_on" => ["php-fpm"], "restart" => "unless-stopped"];

        # Mount the selected integrated server configuration
        elseif($this->_options["type"] === "apache")

            # Override the Apache virtual host with the application's public directory
            $this->_compose["services"]["webserver"]["volumes"][] = "./docker/apache/apache.conf:/etc/apache2/sites-available/000-default.conf:ro";

        else{

            # Use FrankenPHP's embedded Caddy in classic request mode
            $this->_compose["services"]["webserver"]["volumes"][] = "./docker/frankenphp/Caddyfile:/etc/caddy/Caddyfile:ro";

            # Load our mounted configuration explicitly across image layout changes
            $this->_compose["services"]["webserver"]["command"] = ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"];

        }

        # Publish HTTP only on loopback when an existing host proxy is responsible for TLS
        $this->_compose["services"]["webserver"]["ports"] = [$this->_options["proxy"] === "yes" ? "127.0.0.1:\${CRAZY_HTTP_PORT:-8080}:80" : "\${CRAZY_HTTP_PORT:-80}:80"];

        # Configure automatic HTTPS only for the no-proxy branch
        if($this->_managedTls()){

            # Use the integrated Caddy when FrankenPHP was selected
            $edge = "webserver";

            # Add Caddy 2 when the application server cannot manage certificates itself
            if($this->_options["type"] !== "frankenphp"){

                # Keep the HTTP backend reachable only through the Compose network
                unset($this->_compose["services"]["webserver"]["ports"]);

                # Create the dedicated TLS proxy
                $this->_compose["services"]["caddy"] = ["image" => "caddy:2", "volumes" => ["./docker/caddy/Caddyfile:/etc/caddy/Caddyfile:ro"], "depends_on" => ["webserver"], "restart" => "unless-stopped"];

                # Publish ports on the TLS proxy
                $edge = "caddy";

            }

            # Persist certificates and Caddy state across container replacement
            array_push($this->_compose["services"][$edge]["volumes"], "caddy_data:/data", "caddy_config:/config");

            # Expose HTTP redirects, HTTPS and HTTP/3
            $this->_compose["services"][$edge]["ports"] = ["\${CRAZY_HTTP_PORT:-80}:80", "\${CRAZY_HTTPS_PORT:-443}:443", "\${CRAZY_HTTPS_PORT:-443}:443/udp"];

            # Declare persistent volumes without deleting existing certificate data
            $this->_compose["volumes"] = ["caddy_data" => null, "caddy_config" => null];

        }

        # Render the PHP and HTTP server configuration files
        $this->_writeServerFiles();

        # Add Compose defaults without replacing any user-owned environment values
        $this->_writeEnvironment();

        # Persist the selected server and TLS strategy separately from runtime container IDs
        Yaml::create($this->_root."/config/Docker.yml", [
            "Docker" => $this->_options + [
                "runtime" => ["service" => $phpService],
                "lifecycle" => "compose",
                "edge" => ["service" => $this->_managedTls() && $this->_options["type"] !== "frankenphp" ? "caddy" : "webserver"],
            ],
        ]);

        # Make fresh settings visible to subsequent commands
        unset($GLOBALS[Config::PREFIX]["Docker"]);

        # Save startup and proxy instructions next to the generated files
        $this->_write("docker/README.md", $this->_instructions());

        # Return the installer
        return $this;

    }

    /**
     * Run Database Services
     *
     * @return self
     */
    public function runDatabaseServices():self {

        # Generate one persistent service for every configured database engine
        foreach($this->_databases as $engine => $configuration){

            # Preserve service names used by the existing database setup command
            $service = Docker::DATABASE_TO_SERVICE[$engine];

            # Select a maintained image family and its persistent data directory
            [$image, $directory] = match($engine){
                "mongodb" => ["mongo:7", "/data/db"],
                "mysql" => ["mysql:8.4", "/var/lib/mysql"],
                "mariadb" => ["mariadb:11.4", "/var/lib/mysql"],
                "postgresql" => ["postgres:16-alpine", "/var/lib/postgresql/data"],
            };

            # Read the existing initial database and application account
            $database = $configuration["database"][0];

            # SQL image initialization uses the first configured application user
            $user = $configuration["users"][0] ?? [];

            # Pass initialization variables supported by each official image
            $environment = match($engine){
                "mongodb" => ["MONGO_INITDB_DATABASE" => $database, "MONGO_INITDB_ROOT_USERNAME" => $configuration["root"]["login"] ?? "root", "MONGO_INITDB_ROOT_PASSWORD" => $configuration["root"]["password"]],
                "postgresql" => ["POSTGRES_DB" => $database, "POSTGRES_USER" => $user["login"], "POSTGRES_PASSWORD" => $user["password"]],
                "mysql" => ["MYSQL_ROOT_PASSWORD" => $configuration["root"]["password"], "MYSQL_ROOT_HOST" => "%"],
                default => ["MYSQL_DATABASE" => $database, "MYSQL_USER" => $user["login"], "MYSQL_PASSWORD" => $user["password"], "MYSQL_ROOT_PASSWORD" => $configuration["root"]["password"], "MYSQL_ROOT_HOST" => "%"],
            };

            # Escape Compose interpolation while preserving literal password characters
            $environment = array_map(static fn($value):string => str_replace('$', '$$', (string)$value), $environment);

            # Keep databases private to the stack; external clients can add port mappings explicitly
            $this->_compose["services"][$service] = ["image" => $image, "environment" => $environment, "volumes" => ["database_$service:$directory"], "restart" => "unless-stopped"];

            # Persist each engine independently of the application containers
            $this->_compose["volumes"]["database_$service"] = null;

            # Record the Docker service without changing local database hosts or passwords
            Config::setValue("Database.collection.$engine.docker.service.name", $service);

        }

        # Connect readiness checks and initialization before the application starts
        $this->_prepareServices();

        # Serialize the final Compose document after all database services are added
        Yaml::create($this->_root."/docker-compose.yml", $this->_compose);

        # Restrict access because the generated Compose document contains database credentials
        chmod($this->_root."/docker-compose.yml", 0600);

        # Return the installer
        return $this;

    }

    /**
     * Run Validate
     *
     * @return self
     */
    public function runValidate():self {

        # Validate without printing expanded credentials
        $this->_runtime->execute("docker", ["compose", "config", "--quiet"]);

        # Return the validated installer
        return $this;

    }

    /**
     * Run Docker Compose Build
     *
     * @return self
     */
    public function runDockerComposeBuild():self {

        # Build the selected runtime and propagate any nonzero exit status
        $this->_runtime->execute("docker", ["compose", "build"]);

        # Return without starting containers or provisioning a live database
        return $this;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Write Environment
     *
     * @return void
     */
    private function _writeEnvironment():void {

        # Preserve existing project variables, comments and secrets
        $path = $this->_root."/.env";

        # Load the previous contents without expanding user values
        $content = is_file($path) ? file_get_contents($path) : "";

        # Save the chosen host ports, replacing values from a previous generation
        foreach([
            "CRAZY_HTTP_PORT" => (string) $this->_options["http_port"],
            "CRAZY_HTTPS_PORT" => (string) $this->_options["https_port"],
        ] as $name => $value){

            # Set pattern of an existing assignment, including exported variables
            $pattern = '/^\s*(?:export\s+)?'.preg_quote($name, '/').'\s*[=:].*$/m';

            # Replace the existing assignment or append a new one
            $content = preg_match($pattern, $content)
                ? preg_replace($pattern, "$name=$value", $content, 1)
                : rtrim($content, "\r\n")."\n$name=$value\n"
            ;

        }

        # Keep the generated defaults editable through Compose's automatic .env loading
        $defaults = [
            "CRAZY_UID" => function_exists("posix_getuid") ? (string)posix_getuid() : "1000",
            "CRAZY_GID" => function_exists("posix_getgid") ? (string)posix_getgid() : "1000",
        ];

        # Add only missing keys, preserving existing values
        foreach($defaults as $name => $value)

            # Recognize both dotenv assignment forms and exported variables
            if(!preg_match('/^\s*(?:export\s+)?'.preg_quote($name, '/').'\s*[=:]/m', $content))

                # Separate appended variables from a final line without a newline
                $content = rtrim($content, "\r\n")."\n$name=$value\n";

        # Save Compose defaults alongside existing project environment settings
        $this->_write(".env", $content);

    }

    /**
     * Prepare Services
     *
     * @return void
     */
    private function _prepareServices():void {

        # Resolve the runtime that will serve the application
        $service = $this->_options["type"] === "nginx" ? "php-fpm" : "webserver";

        # Share the runtime's image and application mounts with a one-shot preparation job
        $prepare = $this->_compose["services"][$service];

        # Do not expose HTTP ports or automatically restart a completed job
        unset($prepare["ports"], $prepare["restart"], $prepare["command"]);

        # Avoid invoking the FPM, Apache or FrankenPHP image's normal server entrypoint
        $prepare["entrypoint"] = ["/bin/sh", "docker/bin/Prepare"];

        # Keep dependency files and generated connection settings owned by the host user
        $prepare["user"] = '${CRAZY_UID:-1000}:${CRAZY_GID:-1000}';

        # Use a writable home and tell helpers they are already running inside Docker
        $prepare["environment"] = ["HOME" => "/tmp", "COMPOSER_HOME" => "/tmp/composer", "CRAZYPHP_DOCKER" => "1"];

        # Wait for every configured database to accept connections
        foreach($this->_databases as $engine => $configuration){

            # Use stable service DNS rather than cached container IDs
            $database = Docker::DATABASE_TO_SERVICE[$engine];

            # Use each image's supported readiness command
            $test = match($engine){
                "mariadb" => ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"],
                "mysql" => ["CMD-SHELL", 'MYSQL_PWD="$$MYSQL_ROOT_PASSWORD" mysql --protocol=TCP -h 127.0.0.1 -uroot -e "SELECT 1" >/dev/null'],
                "postgresql" => ["CMD-SHELL", 'pg_isready -h 127.0.0.1 -U "$$POSTGRES_USER" -d "$$POSTGRES_DB"'],
                "mongodb" => ["CMD", "mongosh", "--quiet", "--eval", 'quit(db.adminCommand({ping:1}).ok ? 0 : 1)'],
            };

            # Allow first-time initialization while bounding readiness failures
            $this->_compose["services"][$database]["healthcheck"] = ["test" => $test, "interval" => "5s", "timeout" => "5s", "retries" => 30, "start_period" => "20s"];

            # Delay Composer/database preparation until this engine is healthy
            $prepare["depends_on"][$database] = ["condition" => "service_healthy"];

        }

        # Initialize the container-only connection file before Docker resolves its bind mount
        $source = $this->_root."/config/Database.yml";

        # Include all user-defined database settings without changing the original file
        $configuration = is_file($source) ? Yaml::open($source) : ["Database" => ["collection" => []]];

        # Replace only the endpoints managed by this generated stack
        $configuration = Bootstrap::containerConfiguration($configuration);

        # Create a real file so Compose cannot turn a missing bind source into a directory
        Yaml::create($this->_root."/docker/config/Database.yml", $configuration);

        # Let HTTP workers read the bind mount; its host directory is private
        chmod($this->_root."/docker/config/Database.yml", 0644);

        # Avoid creating an empty host file when the application has no database configuration
        if(is_file($source))

            # Mount the generated settings over the application's file only inside its runtime
            $this->_compose["services"][$service]["volumes"][] = ["type" => "bind", "source" => "./docker/config/Database.yml", "target" => $this->_root."/config/Database.yml", "read_only" => true];

        # Start the application only after all initialization succeeds
        $this->_compose["services"][$service]["depends_on"] = ["prepare" => ["condition" => "service_completed_successfully"]];

        # Add the one-shot service without mounting the generated configuration over its input
        $this->_compose["services"]["prepare"] = $prepare;

    }

    /**
     * Managed TLS
     *
     * @return bool
     */
    private function _managedTls():bool {

        # Only manage certificates when HTTPS has no existing reverse proxy
        return $this->_options["security"] === "https" && $this->_options["proxy"] === "no";

    }

    /**
     * Write Server Files
     *
     * @return void
     */
    private function _writeServerFiles():void {

        # Use the project's PHP version
        $version = Composer::getPhpVersion();

        # Select the image variant of that version for the chosen HTTP server
        $base = match($this->_options["type"]){
            "nginx" => "php:$version-fpm-bookworm",
            "apache" => "php:$version-apache-bookworm",
            "frankenphp" => "dunglas/frankenphp:1-php$version-bookworm",
        };

        # Install the framework's shared runtime extensions and selected database drivers
        $extensions = "bcmath intl mbstring zip opcache pcntl";

        # Add SQL extensions only for the configured engines
        if(isset($this->_databases["mysql"]) || isset($this->_databases["mariadb"]))

            # Both MySQL and MariaDB use PDO MySQL
            $extensions .= " pdo_mysql";

        # Include PostgreSQL when configured
        if(isset($this->_databases["postgresql"]))

            # Enable the SQL driver used by PostgreSQL applications
            $extensions .= " pdo_pgsql";

        # Pin the MongoDB extension to the installed Composer driver's compatible major
        if(isset($this->_databases["mongodb"]))

            # Avoid installing MongoDB extension 2 with legacy driver 1
            $extensions .= " mongodb-1.21.12";

        # Render the common PHP image, enabling Apache rewrite only for Apache
        $this->_template("php/Dockerfile", "docker/php/Dockerfile", ["{{base}}" => $base, "{{extensions}}" => $extensions, "{{apache}}" => $this->_options["type"] === "apache" ? "RUN a2enmod rewrite\n" : ""]);

        # Provide the PHP overrides shared by all official image variants
        $this->_write("docker/php/php.ini", "date.timezone=UTC\nupload_max_filesize=500M\npost_max_size=512M\n");

        # Render Nginx's front-controller configuration
        if($this->_options["type"] === "nginx")

            # Quote the document root for paths containing spaces
            $this->_template("nginx/nginx.conf", "docker/nginx/nginx.conf", ["{{public}}" => $this->_quote($this->_public)]);

        # Render Apache's front-controller configuration
        elseif($this->_options["type"] === "apache")

            # Apply the same public directory without depending on .htaccess
            $this->_template("apache/apache.conf", "docker/apache/apache.conf", ["{{public}}" => $this->_quote($this->_public)]);

        # Render the integrated Caddy server in classic PHP mode
        else

            # Disable automatic HTTPS when another proxy owns certificates or HTTP was selected
            $this->_template("frankenphp/Caddyfile", "docker/frankenphp/Caddyfile", ["{{site}}" => $this->_managedTls() ? $this->_options["hostname"] : ":80", "{{public}}" => $this->_quote($this->_public)]);

        # Render a separate Caddy proxy for Nginx and Apache HTTPS deployments
        if($this->_managedTls() && $this->_options["type"] !== "frankenphp")

            # Forward requests to the internal HTTP server
            $this->_template("caddy/Caddyfile", "docker/caddy/Caddyfile", ["{{site}}" => $this->_options["hostname"]]);

    }

    /**
     * Quote
     *
     * @param string $value Server configuration token
     * @return string
     */
    private function _quote(string $value):string {

        # Escape paths for the quoted server configuration syntax
        return '"'.str_replace(["\\", '"', '$', "\n", "\r"], ["\\\\", '\\"', '\\$', "", ""], $value).'"';

    }

    /**
     * Template
     *
     * @param string $source Resource relative to the server templates
     * @param string $target Generated project file
     * @param array $values Validated replacement values
     * @return void
     */
    private function _template(string $source, string $target, array $values):void {

        # Replace explicit tokens without interpreting server directives as template code
        $this->_write($target, strtr(file_get_contents(File::path("@crazyphp_root/resources/Docker/server/".$source)), $values));

    }

    /**
     * Write
     *
     * @param string $path Application-relative output path
     * @param string $content Generated file contents
     * @return void
     */
    private function _write(string $path, string $content):void {

        # Stop generation if the filesystem refuses a write
        if(file_put_contents($this->_root."/".$path, $content) === false)

            # Report the destination without exposing generated credentials
            throw new CrazyException("Could not write $path.", 500);

    }

    /**
     * Proxy Instructions
     *
     * @return string
     */
    private function _proxyInstructions():string {

        # Use a host proxy endpoint isolated from other network clients
        $hostname = $this->_options["hostname"];

        # Get the local backend port
        $port = $this->_options["http_port"];

        # Show equivalent Caddy and Nginx upstream examples
        return "Keep TLS on your existing proxy and forward to http://127.0.0.1:$port (CRAZY_HTTP_PORT in .env).\n\nCaddy:\n$hostname {\n    reverse_proxy 127.0.0.1:$port\n}\n\nNginx: add this to your existing HTTPS server block with its certificates:\nlocation / {\n    proxy_pass http://127.0.0.1:$port;\n    proxy_set_header Host \$host;\n    proxy_set_header X-Forwarded-Proto \$scheme;\n    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n}\n\nFor a proxy in another container, join its network to webserver and use webserver:80; 127.0.0.1 refers to that proxy container itself.\n";

    }

    /**
     * Instructions
     *
     * @return string
     */
    private function _instructions():string {

        # Preserve the preview and give the commands needed after a successful build
        return "# Docker environment\n\n".$this->describe()."\n\nStart: `docker compose up -d`\n\nStop: `docker compose down`\n\nPublic HTTPS requires DNS pointing at this host and reachable ports 80/443. Caddy issues certificates at startup. For localhost/internal names, export Caddy's root certificate from `/data/caddy/pki/authorities/local/root.crt` and trust it on each client; the command does not modify your trust store or hosts file.\n\nCompose waits for healthy databases and runs the prepare service before starting PHP. Preparation runs composer install and creates configured databases and users without resetting existing passwords or deleting data. The runtime reads docker/config/Database.yml, regenerated from config/Database.yml at preparation time; local connection settings remain unchanged. Stop with docker compose down to retain database and certificate volumes. Use docker compose logs prepare to diagnose initialization failures.\n";

    }

    /**
     * Ports
     *
     * @return array
     */
    private function _ports():array {

        # Set result with the HTTP port, local only behind an existing proxy
        $result = ["HTTP" => $this->_options["http_port"]];

        # Check Caddy or FrankenPHP publishes HTTPS
        if($this->_managedTls())

            # Add HTTPS port
            $result["HTTPS"] = $this->_options["https_port"];

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Answer
     *
     * @param array $answers Form records or named options
     * @param string $name Answer name
     * @param mixed $default Value when the question was not answered
     * @return mixed
     */
    private static function _answer(array $answers, string $name, mixed $default):mixed {

        # Read a form record value or a plain named option
        return is_array($answers[$name] ?? null)
            ? ($answers[$name]["value"] ?? $default)
            : ($answers[$name] ?? $default)
        ;

    }

    /**
     * Environment Port
     *
     * @param string $name Variable name in the application .env
     * @return int|null
     */
    private static function _environmentPort(string $name):?int {

        # Get application .env
        $path = File::path("@app_root/.env");

        # Read saved ports without loading them into the process environment
        $value = is_file($path) ? (Dotenv::parse(file_get_contents($path))[$name] ?? null) : null;

        # Return a valid saved port
        return is_string($value) && preg_match('/^[0-9]{1,5}$/D', $value) && (int) $value >= 1 && (int) $value <= 65535
            ? (int) $value
            : null
        ;

    }

    /**
     * Available Port
     *
     * @param int $preferred Port to keep when it is free
     * @param int $fallback First port tried when the preferred port is used
     * @param array $exclude Ports already chosen by other questions
     * @param Runtime $runtime Port probe
     * @return int
     */
    private static function _availablePort(int $preferred, int $fallback, array $exclude, Runtime $runtime):int {

        # Keep the preferred port when it is free
        if(!in_array($preferred, $exclude, true) && $runtime->isPortAvailable($preferred))

            # Return preferred port
            return $preferred;

        # Iteration of fallback ports
        for($port = $fallback; $port < min($fallback + 1000, 65536); $port++)

            # Check port is free and not already chosen
            if($port !== $preferred && !in_array($port, $exclude, true) && $runtime->isPortAvailable($port))

                # Return free port
                return $port;

        # Keep the preferred port so the user can choose one explicitly
        return $preferred;

    }

}
