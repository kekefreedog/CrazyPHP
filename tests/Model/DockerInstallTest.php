<?php declare(strict_types=1);
/**
 * Docker Installation Tests
 *
 * Verify the server and security graph without starting application services.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Model;

/**
 * Dependencies
 */
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Model\Docker\Install;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use CrazyPHP\Cli\Form;

/**
 * Docker Install Test
 *
 * Exercise generated topology, preserved configuration and checked execution.
 */
class DockerInstallTest extends TestCase {

    /** @var string $_root Isolated project directory */
    private string $_root;

    /** @var DockerInstallRuntime $_runtime Subprocess recorder */
    private DockerInstallRuntime $_runtime;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Cover paths containing spaces in every generated server configuration
        $this->_root = sys_get_temp_dir()."/crazy docker ".bin2hex(random_bytes(5));

        # Create a minimal initialized application
        mkdir($this->_root."/config", 0755, true);

        # Point framework paths at this fixture only
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 2), "phpunit_test" => true]);

        # Clear state cached by earlier tests
        unset($GLOBALS[Config::PREFIX]);

        # Preserve the project's existing server and document-root settings
        Yaml::create("@app_root/config/App.yml", ["App" => ["name" => "example/app", "public" => "public", "server" => ["name" => "example.test"]]]);

        # Record checked subprocesses without contacting a daemon
        $this->_runtime = new DockerInstallRuntime();

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove only this test's application
        File::removeAll($this->_root);

        # Clear configuration that points at the removed fixture
        unset($GLOBALS[Config::PREFIX]);

        # Restore the framework environment
        Env::reset();

    }

    /**
     * Branches
     *
     * @return array
     */
    public static function branches():array {

        # Build all nine paths through the graph
        $branches = [];

        # Combine the three HTTP servers with the three security strategies
        foreach(["nginx", "apache", "frankenphp"] as $type)

            # HTTPS has both existing-proxy and managed-certificate branches
            foreach([["http", "no"], ["https", "yes"], ["https", "no"]] as [$security, $proxy])

                # Name each case for useful failure reports
                $branches["$type-$security-$proxy"] = [$type, $security, $proxy];

        # Return the complete graph matrix
        return $branches;

    }

    /**
     * Test Server And Security Branches
     *
     * @param string $type Selected server
     * @param string $security Selected protocol
     * @param string $proxy Existing reverse proxy
     * @return void
     */
    #[DataProvider("branches")]
    public function testServerAndSecurityBranches(string $type, string $security, string $proxy):void {

        # Preserve the source configuration byte for byte
        $app = file_get_contents($this->_root."/config/App.yml");

        # Run the actual generator for this graph branch
        $install = new Install(compact("type", "security", "proxy"), $this->_runtime);

        # Execute every generation and validation step
        $install->run();

        # Inspect the persisted Compose document rather than implementation arrays
        $compose = Yaml::open("@app_root/docker-compose.yml");

        # Identify which component owns TLS
        $managed = $security === "https" && $proxy === "no";

        # Nginx requires FPM; integrated servers do not
        $this->assertSame($type === "nginx", isset($compose["services"]["php-fpm"]));

        # Create Caddy only when HTTPS needs it and FrankenPHP does not already provide it
        $this->assertSame($managed && $type !== "frankenphp", isset($compose["services"]["caddy"]));

        # Keep an existing proxy's backend on loopback
        if($proxy === "yes"){

            # Avoid exposing an HTTP bypass for the existing HTTPS proxy
            $this->assertSame(["127.0.0.1:\${CRAZY_HTTP_PORT:-8080}:80"], $compose["services"]["webserver"]["ports"]);

            # Provide both requested reverse-proxy examples
            $this->assertStringContainsString("proxy_set_header X-Forwarded-Proto", $install->describe());

            # Caddy configuration must point to the published upstream
            $this->assertStringContainsString("reverse_proxy 127.0.0.1:8080", $install->describe());

        }

        # Persist certificates only when this stack owns TLS
        $this->assertSame($managed, array_key_exists("caddy_data", $compose["volumes"] ?? []));

        # Standalone TLS must publish HTTPS on its edge service
        if($managed){

            # Resolve the public service for this server type
            $edge = $type === "frankenphp" ? "webserver" : "caddy";

            # Expose the TLS listener alongside HTTP redirects
            $this->assertContains("\${CRAZY_HTTPS_PORT:-443}:443", $compose["services"][$edge]["ports"]);

            # Prevent publishing the backend's plain HTTP port behind a separate Caddy
            if($edge === "caddy")

                # Only the proxy should accept host connections
                $this->assertArrayNotHasKey("ports", $compose["services"]["webserver"]);

        }

        # Verify the selected server's generated front-controller configuration
        $configuration = file_get_contents($this->_root."/docker/$type/".match($type){"nginx" => "nginx.conf", "apache" => "apache.conf", default => "Caddyfile"});

        # Quoted roots must preserve spaces in application paths
        $this->assertStringContainsString('"'.realpath($this->_root).'/public"', $configuration);

        # FrankenPHP must not enable HTTPS behind an existing proxy or for plain HTTP
        if($type === "frankenphp"){

            # Use the hostname only when the embedded Caddy manages TLS
            $this->assertStringContainsString($managed ? "example.test {" : ":80 {", $configuration);

            # The image must load our mounted file even when its default path changes
            $this->assertSame(["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"], $compose["services"]["webserver"]["command"]);

        }

        # Keep local application settings unchanged
        $this->assertSame($app, file_get_contents($this->_root."/config/App.yml"));

        # No database file is needed for applications without databases
        $this->assertFileDoesNotExist($this->_root."/config/Database.yml");

        # Resolve the actual host port for direct, proxied and separate Caddy services
        $this->assertSame($proxy === "yes" ? 8080 : 80, Docker::getHttpPort());

        # Resolve HTTPS only when this stack publishes TLS itself
        $this->assertSame($managed ? 443 : null, Docker::getHttpPort(443));

        # Store the chosen server so subsequent Docker commands find PHP correctly
        $this->assertSame($type === "nginx" ? "php-fpm" : "webserver", Yaml::open("@app_root/config/Docker.yml")["Docker"]["runtime"]["service"]);

        # Gate the application on successful preparation rather than starting it during setup
        $runtime = $type === "nginx" ? "php-fpm" : "webserver";

        # Keep HTTP cache writes compatible with files created by local commands
        $this->assertSame([
            "CRAZY_UID" => '${CRAZY_UID:-1000}',
            "CRAZY_GID" => '${CRAZY_GID:-1000}',
        ], $compose["services"][$runtime]["build"]["args"]);

        # Preparation and HTTP must build the same worker identity
        $this->assertSame($compose["services"][$runtime]["build"], $compose["services"]["prepare"]["build"]);

        # Keep FrankenPHP's single HTTP process unprivileged, including its certificate storage
        $dockerfile = file_get_contents($this->_root."/docker/php/Dockerfile");
        $this->assertSame($type === "frankenphp", str_contains($dockerfile, "USER www-data"));

        # Preparation errors must prevent the PHP runtime from starting
        $this->assertSame("service_completed_successfully", $compose["services"][$runtime]["depends_on"]["prepare"]["condition"]);

        # Run dependency installation through a portable shell entrypoint
        $this->assertSame(["/bin/sh", "docker/bin/Prepare"], $compose["services"]["prepare"]["entrypoint"]);

        # Read container settings without rewriting the user's local configuration
        $this->assertFileExists($this->_root."/docker/config/Database.yml");

        # Avoid Docker creating an empty source file for applications without databases
        $this->assertEmpty(array_filter($compose["services"][$runtime]["volumes"], static fn($mount):bool => is_array($mount) && str_ends_with($mount["target"], "/config/Database.yml")));

        # Compose must discover port defaults without an explicit --env-file argument
        $this->assertFileExists($this->_root."/.env");

        # Do not generate the legacy custom environment file
        $this->assertFileDoesNotExist($this->_root."/docker/variables.env");

        # Check the daemon and Compose before validating and building, without starting services
        $this->assertSame([
            ["docker", ["info", "--format", "{{.ServerVersion}}"]],
            ["docker", ["compose", "version"]],
            ["docker", ["compose", "config", "--quiet"]],
            ["docker", ["compose", "build"]],
        ], $this->_runtime->commands);

    }

    /**
     * Test Unsupported MySQL Root Password Stops Before Generation
     *
     * @return void
     */
    public function testUnsupportedMysqlRootPasswordStopsBeforeGeneration():void {

        # Exercise characters the official image cannot safely initialize as its root password
        Yaml::create("@app_root/config/Database.yml", ["Database" => ["collection" => ["mysql" => ["database" => ["app"], "root" => ["password" => "quote'"], "users" => [["login" => "app", "password" => "app-password"]]]]]]);

        # Explain the image constraint without printing the secret
        $this->expectExceptionMessage("root password without quotes");

        try{

            # Validate before any Docker files or live volumes can be created
            (new Install([], $this->_runtime))->run();

        }finally{

            # Preserve a clean project when initialization would fail
            $this->assertFileDoesNotExist($this->_root."/docker-compose.yml");
            $this->assertDirectoryDoesNotExist($this->_root."/docker");

        }

    }

    /**
     * Test Multiple Database Services
     *
     * @return void
     */
    public function testMultipleDatabaseServices():void {

        # Include literal Compose interpolation syntax in credentials
        $password = 'quotes " and ${PASSWORD} and $cash';

        # Prepare all engines with disabled and enabled definitions supported by the app
        $collection = [];

        # Give every supported engine a distinct database but identical fixture credentials
        foreach(["mongodb", "mysql", "mariadb", "postgresql"] as $engine)

            # Preserve local hosts and extra configured databases
            $collection[$engine] = ["host" => "127.0.0.1", "port" => 12345, "database" => ["first", "second"], "root" => ["login" => "root", "password" => $engine === "mysql" ? 'root ${PASSWORD} $cash' : $password], "users" => [["login" => "app", "password" => $password]]];

        # Write the application's source configuration
        Yaml::create("@app_root/config/Database.yml", ["Database" => ["collection" => $collection]]);

        # Generate every enabled service
        (new Install([], $this->_runtime))->run();

        # Read the generated services
        $compose = Yaml::open("@app_root/docker-compose.yml");

        # Keep all four databases alongside Nginx and PHP
        $this->assertCount(7, $compose["services"]);

        # Preserve literal passwords through Compose's dollar interpolation
        $this->assertSame(str_replace('$', '$$', $password), $compose["services"]["mariadb"]["environment"]["MYSQL_PASSWORD"]);

        # Let PDO create MySQL application accounts rather than interpolating passwords in the image entrypoint
        $this->assertArrayNotHasKey("MYSQL_USER", $compose["services"]["mysql"]["environment"]);
        $this->assertArrayNotHasKey("MYSQL_PASSWORD", $compose["services"]["mysql"]["environment"]);
        $this->assertArrayNotHasKey("MYSQL_DATABASE", $compose["services"]["mysql"]["environment"]);

        # Keep each engine's persistent data independent
        $this->assertCount(4, $compose["volumes"]);

        # Do not change the source passwords or local host while adding service metadata
        $configuration = Yaml::open("@app_root/config/Database.yml")["Database"]["collection"];

        # Check every database retained its full settings
        foreach($collection as $engine => $original){

            # Remove the newly added metadata before comparing user-owned settings
            unset($configuration[$engine]["docker"]);

            # Preserve additional databases, custom ports and special characters
            $this->assertSame($original, $configuration[$engine]);

        }

        # Verify database readiness gates and container-only connection overrides
        $overlay = Yaml::open("@app_root/docker/config/Database.yml")["Database"]["collection"];

        # Cover every generated engine and its stable Compose hostname
        foreach(["mongodb" => "mongo", "mysql" => "mysql", "mariadb" => "mariadb", "postgresql" => "postgresql"] as $engine => $service){

            # Wait for actual database readiness before preparation
            $this->assertSame("service_healthy", $compose["services"]["prepare"]["depends_on"][$service]["condition"]);

            # Use the database service's internal endpoint only in the container document
            $this->assertSame($service, $overlay[$engine]["host"]);

            # Keep port overrides from the host out of container-to-container connections
            $this->assertSame($engine === "postgresql" ? 5432 : ($engine === "mongodb" ? 27017 : 3306), $overlay[$engine]["port"]);

        }

        # Confirm the runtime includes all required drivers
        $dockerfile = file_get_contents($this->_root."/docker/php/Dockerfile");

        # MongoDB Composer driver 1 requires the extension's compatible major
        $this->assertStringContainsString("mongodb-1.21.12", $dockerfile);

        # SQL engines require both PDO extensions
        $this->assertStringContainsString("pdo_mysql pdo_pgsql", $dockerfile);

        # Keep credentials out of the displayed summary and generated documentation
        $this->assertStringNotContainsString($password, file_get_contents($this->_root."/docker/README.md"));

    }

    /**
     * Test Existing Environment Is Preserved
     *
     * @return void
     */
    public function testExistingEnvironmentIsPreserved():void {

        # Preserve custom values and literal secrets already owned by the application
        $content = "# User settings\nSECRET='literal $'\nCRAZY_HTTP_PORT=49123\n";

        # Set a non-default port before Docker generation
        file_put_contents($this->_root."/.env", $content);

        # Generate the normal Compose environment
        (new Install([], $this->_runtime))->run();

        # Keep the original block intact while appending missing Compose defaults
        $this->assertStringStartsWith($content, file_get_contents($this->_root."/.env"));

        # Resolve the user's port without an explicit env-file option
        $this->assertSame(49123, Docker::getHttpPort());

    }

    /**
     * Test Used Ports Are Replaced By Free Proposals
     *
     * @return void
     */
    public function testUsedPortsAreReplacedByFreeProposals():void {

        # Simulate standard and first fallback ports already used
        $this->_runtime->busyPorts = [80, 443, 8080];

        # Generate a managed HTTPS stack without choosing ports
        $install = new Install(["type" => "nginx", "security" => "https", "proxy" => "no", "hostname" => "localhost"], $this->_runtime);
        $install->run();

        # Get .env
        $environment = file_get_contents($this->_root."/.env");

        # Propose the next free HTTP port
        $this->assertStringContainsString("CRAZY_HTTP_PORT=8081\n", $environment);

        # Propose the first free HTTPS fallback port
        $this->assertStringContainsString("CRAZY_HTTPS_PORT=8443\n", $environment);

        # Show the chosen ports in the summary
        $this->assertStringContainsString("HTTP port: 8081", $install->describe());

    }

    /**
     * Test Chosen Port Overrides Saved Port
     *
     * @return void
     */
    public function testChosenPortOverridesSavedPort():void {

        # Save a previous port
        file_put_contents($this->_root."/.env", "CRAZY_HTTP_PORT=49123\n");

        # Choose another port, even one reported as used
        $this->_runtime->busyPorts = [9000];
        $install = new Install(["type" => "apache", "security" => "http", "http_port" => "9000"], $this->_runtime);
        $install->run();

        # Replace the saved port with the chosen one
        $this->assertStringContainsString("CRAZY_HTTP_PORT=9000\n", file_get_contents($this->_root."/.env"));
        $this->assertStringNotContainsString("49123", file_get_contents($this->_root."/.env"));

        # Warn that the chosen port is used
        $this->assertStringContainsString("HTTP port: 9000 (currently in use", $install->describe());

    }

    /**
     * Test Invalid Ports Are Rejected
     *
     * @param array $options Named options
     * @param string $message Expected error
     * @return void
     */
    #[DataProvider("invalidPorts")]
    public function testInvalidPortsAreRejected(array $options, string $message):void {

        # Expect error
        $this->expectExceptionMessage($message);

        # Validate options
        new Install(["type" => "nginx", "hostname" => "localhost"] + $options, $this->_runtime);

    }

    /**
     * Invalid Ports
     *
     * @return array
     */
    public static function invalidPorts():array {

        # Return invalid port choices
        return [
            "not a number"  => [["http_port" => "web"], "between 1 and 65535"],
            "out of range"  => [["http_port" => "70000"], "between 1 and 65535"],
            "same port"     => [["security" => "https", "proxy" => "no", "http_port" => "8443", "https_port" => "8443"], "must be different"],
        ];

    }

    /**
     * Test Images Follow Project Php Version
     *
     * @return void
     */
    public function testImagesFollowProjectPhpVersion():void {

        # Choose PHP 8.5 in the project manifest
        file_put_contents($this->_root."/composer.json", json_encode(["require" => ["php" => "^8.5"], "config" => ["platform" => ["php" => "8.5.0"]]]));

        # Generate a FrankenPHP stack
        $install = new Install(["type" => "frankenphp", "security" => "http"], $this->_runtime);
        $install->run();

        # Build the server image for that version
        $this->assertStringContainsString("FROM dunglas/frankenphp:1-php8.5-bookworm", file_get_contents($this->_root."/docker/php/Dockerfile"));

        # Show the version in the summary
        $this->assertStringContainsString("Server: frankenphp (PHP 8.5)", $install->describe());

    }

    /**
     * Test Docker Failure Does Not Write Files
     *
     * @return void
     */
    public function testDockerFailureDoesNotWriteFiles():void {

        # Fail the initial daemon check
        $this->_runtime->failAt = 1;

        # Require the subprocess failure to reach the caller
        $this->expectExceptionMessage("Docker command failed");

        try{

            # Attempt the normal generation flow
            (new Install([], $this->_runtime))->run();

        }finally{

            # Stop before creating any Docker folders
            $this->assertDirectoryDoesNotExist($this->_root."/docker");

        }

    }

    /**
     * Test Validation Failure Stops Build
     *
     * @return void
     */
    public function testValidationFailureStopsBuild():void {

        # Fail Compose validation after the two prerequisite checks
        $this->_runtime->failAt = 3;

        # Require validation failures to stop generation
        $this->expectExceptionMessage("Docker command failed");

        try{

            # Run the graph through its validation step
            (new Install([], $this->_runtime))->run();

        }finally{

            # A failed Compose configuration must never reach build
            $this->assertCount(3, $this->_runtime->commands);

        }

    }

    /**
     * Test Existing Configuration Is Preserved
     *
     * @return void
     */
    public function testExistingConfigurationIsPreserved():void {

        # Preserve a custom user-owned Compose file
        file_put_contents($this->_root."/compose.yaml", "custom configuration\n");

        # Require a useful explanation before any overwrite
        $this->expectExceptionMessage("compose.yaml already exists");

        try{

            # Refuse to merge unknown custom infrastructure silently
            (new Install([], $this->_runtime))->run();

        }finally{

            # Do not modify the existing file
            $this->assertSame("custom configuration\n", file_get_contents($this->_root."/compose.yaml"));

            # Do not create a partial replacement stack
            $this->assertDirectoryDoesNotExist($this->_root."/docker");

        }

    }

    /**
     * Test Form Answers Pass Validation
     *
     * Radio questions return one string and checkbox questions return a list,
     * so each question type must match the answer shape of its prompt.
     *
     * @return void
     */
    public function testFormAnswersPassValidation():void {

        # Set form result
        $result = [];

        # Answer every question the way the interactive prompt does
        foreach(Install::REQUIRED_VALUES as $question)

            # Pick the first choice, or the question default
            $result[] = $question + [
                "value" => isset($question["select"])
                    ? (($question["multiple"] ?? false) ? [array_key_first($question["select"])] : array_key_first($question["select"]))
                    : ($question["type"] === "INT" ? $question["default"]([], $this->_runtime) : "localhost")
            ];

        # Process then validate like CrazyDocker new
        $values = (new Validate((new Process($result))->getResult()))->getResult();

        # Keep the selected server type
        $this->assertSame("nginx", $values[0]["value"]);

    }

    /**
     * Test Conditional Questions And Cli Inputs
     *
     * @return void
     */
    public function testConditionalQuestionsAndCliInputs():void {

        # Find the existing-proxy question in the public form schema
        $question = Install::getRequiredValues()[2];

        # Plain HTTP skips the HTTPS proxy question
        $this->assertFalse(Form::isVisible($question, ["security" => ["value" => "http"]]));

        # HTTPS requires choosing who manages TLS
        $this->assertTrue(Form::isVisible($question, ["security" => ["value" => "https"]]));

        # Normalize the same form records passed by CrazyDocker new
        $install = new Install(["input" => [["name" => "type", "value" => "apache"], ["name" => "security", "value" => "http"]]], $this->_runtime);

        # Ensure direct and CLI execution share the declared storyline
        $this->assertSame(Install::STORYLINE, $install->getStoryline());

        # Execute the normalized selection
        $install->run();

        # Generate the chosen integrated server
        $this->assertFileExists($this->_root."/docker/apache/apache.conf");

    }

    /**
     * Test Invalid Hostname Is Rejected
     *
     * @return void
     */
    public function testInvalidHostnameIsRejected():void {

        # Reject injected server directives before any filesystem mutation
        $this->expectExceptionMessage("Enter a hostname");

        # Newlines and braces must never enter a generated Caddyfile through input
        new Install(["security" => "https", "hostname" => "example.com {\n respond hacked\n}"], $this->_runtime);

    }

}

/**
 * Docker Install Runtime
 *
 * Record subprocess calls and inject failures at specific graph steps.
 */
class DockerInstallRuntime extends Runtime {

    /** @var array $commands Executed tools and argument arrays */
    public array $commands = [];

    /** @var int $failAt One-based command index to fail */
    public int $failAt = 0;

    /** @var array $busyPorts Host ports reported as already used */
    public array $busyPorts = [];

    /**
     * Is Port Available
     *
     * @param int $port Host TCP port
     * @return bool
     */
    public function isPortAvailable(int $port):bool {

        # Simulate used ports without probing the host
        return !in_array($port, $this->busyPorts, true);

    }

    /**
     * Execute
     *
     * @param string $tool Requested tool
     * @param array $arguments Preserved arguments
     * @return void
     */
    public function execute(string $tool, array $arguments = []):void {

        # Record each command in execution order
        $this->commands[] = [$tool, $arguments];

        # Simulate failures without changing the host environment
        if(count($this->commands) === $this->failAt)

            # Exercise the generator's normal failure propagation
            throw new CrazyException("Docker command failed", 500);

    }

}
