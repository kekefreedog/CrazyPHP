<?php declare(strict_types=1);
/**
 * New application
 *
 * Create projects using conditional database, router and frontend choices.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Model\App;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Database\Database;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Front;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Model\Config;

/**
 * Create Application
 *
 * Generate a project from the confirmed CLI choices.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
class Create extends CrazyModel implements CrazyCommand {

    /** Public Constants
     ******************************************************
     */

    /** @var array PHP_VERSIONS Supported PHP versions, latest first */
    public const PHP_VERSIONS = [
        "8.5"   => "PHP 8.5 (latest)",
        "8.4"   => "PHP 8.4",
        "8.3"   => "PHP 8.3",
    ];

    /** @var string LATEST_PHP_VERSION Version proposed by default */
    public const LATEST_PHP_VERSION = "8.5";

    /** @var array REQUIRED_VALUES Field definitions; legacy metadata offsets remain stable */
    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPHP\Model\App\Create::defaultAppName",
            "required"      =>  true,
            "process"       =>  ['trim']
        ],
        # Description
        [
            "name"          =>  "description",
            "description"   =>  "Description of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "My Crazy Web Application",
            "process"       =>  ['trim']
        ],
        # Author Name
        [
            "name"          =>  "authors__name",
            "description"   =>  "Author of this crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Author Email
        [
            "name"          =>  "authors__email",
            "description"   =>  "Email of the crazy author",
            "type"          =>  "VARCHAR",
            "default"       =>  "crazy@person.com",
            "required"      =>  true,
            "process"       =>  ['trim'],
            "validate"      =>  ['email'],
        ],
        # Type
        [
            "name"          =>  "type",
            "description"   =>  "Type of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "application",
            "select"        =>  [
                "application"   =>  "Application",
                "website"       =>  "Website",
                "api"           =>  "REST API",
                "library"       =>  "PHP Library",
                "other"         =>  "Undefined",
            ]
        ],
        # Homepage
        [
            "name"          =>  "homepage",
            "description"   =>  "Home page of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "https://github.com/kekefreedog/CrazyPHP/",
            "validate"      =>  ['http'],
        ],
        # Database
        [
            "name"          => "database",
            "description"   => "Database engines (select none to skip)",
            "type"          => "ARRAY",
            "default"       => "",
            "multiple"      => true,
            "when"          => ["type" => ["application", "website", "api"]],
            "select"        => [
                "mongodb"       => "MongoDB",
                "mariadb"       => "MariaDB (MySQL)",
                "postgresql"    => "PostgreSQL",
            ],
        ],
        # Authentication
        [
            "name"          => "authentication",
            "description"   => "Authentication library",
            "type"          => "VARCHAR",
            "default"       => "none",
            "required"      => true,
            "when"          => ["database" => ["mongodb", "mariadb", "mysql", "postgresql"]],
            "select"        => [self::class, "authenticationChoices"],
            "selectWithAnswers" => true,
        ],
        # Router
        [
            "name"          => "router",
            "description"   => "Router vendor",
            "type"          => "VARCHAR",
            "default"       => "mezon",
            "required"      => true,
            "when"          => ["type" => ["application", "website", "api"]],
            "select"        => [
                "mezon"         => "Mezon",
                "fast-route"    => "FastRoute",
                "league"        => "LeagueRoute",
                "symfony"       => "Symfony",
            ],
        ],
        # Frontend
        [
            "name"          => "frontend",
            "description"   => "Frontend bundler",
            "type"          => "VARCHAR",
            "default"       => "vite",
            "required"      => true,
            "when"          => ["type" => ["application", "website"]],
            "select"        => ["vite" => "Vite", "webpack" => "Webpack"],
        ],
        # PHP Version
        [
            "name"          => "php_version",
            "description"   => "PHP version",
            "type"          => "VARCHAR",
            "default"       => self::LATEST_PHP_VERSION,
            "required"      => true,
            "select"        => self::PHP_VERSIONS,
        ],
    ];

    /** @var array STORYLINE Shared order for the CLI and direct callers */
    public const STORYLINE = [
        "runBackupComposer",
        "runComposer",
        "runNpm",
        "runStructureFolder",
        "runConfig",
        "runComposerUpdate",
        "runFrontDependencies",
        "runDatabase",
        "runConfirmDatabasePasswords",
        "runFrontScript",
        "runFirstCompilation",
    ];

    /** Private Parameters
     ******************************************************
     */

    /** @var array $_options Normalized choices */
    private array $_options;

    /** @var Runtime $_runtime Process runner */
    private Runtime $_runtime;

    /**
     * Constructor
     *
     * @param array $inputs CLI form records or named application options
     * @param Runtime|null $runtime Tool runner
     */
    public function __construct(array $inputs = [], ?Runtime $runtime = null){

        # Start with defaults for callers using the original input format
        $options = [
            "name" => self::defaultAppName(), "description" => "My Crazy Web Application",
            "type" => "application", "database" => [], "authentication" => "none",
            "router" => "mezon", "frontend" => "vite", "php_version" => self::LATEST_PHP_VERSION,
            "database_host" => "localhost", "database_name" => "crazy_db",
            "database_user" => "crazyuser", "database_password" => "crazypassword",
        ];

        # Accept both form records and explicit options
        foreach($inputs["application"] ?? [] as $name => $input){

            # Extract form values without copying form metadata to manifests
            if(is_array($input) && isset($input["name"]))

                # Keep the answer associated with its field name
                $options[$input["name"]] = $input["value"] ?? $input["default"] ?? "";

            else

                # Accept programmatic named values
                $options[$name] = $input;

        }

        # Accept a checkbox selection or a single named engine, where None means no database
        $options["database"] = array_values(array_unique(array_diff(
            array_map("strval", (array) $options["database"]),
            ["none", ""]
        )));

        # Validate project type before applying its branch
        if(!isset(self::REQUIRED_VALUES[4]["select"][$options["type"]]))

            # Reject unknown project types
            throw new CrazyException("Unknown project type.", 400);

        # Libraries and undefined projects skip the server feature questions
        if(in_array($options["type"], ["library", "other"], true)){

            # Disable skipped database setup
            $options["database"] = [];

            # Disable skipped authentication setup
            $options["authentication"] = "none";

            # Disable router setup for these project types
            $options["router"] = "none";

        }

        # Only applications and websites have a frontend
        if(!in_array($options["type"], ["application", "website"], true))

            # Ignore legacy frontend defaults for backend projects
            $options["frontend"] = "none";

        # A missing database skips authentication in the creation graph
        if(empty($options["database"]))

            # Normalize the skipped answer
            $options["authentication"] = "none";

        # Validate every selected database engine before writing files
        foreach($options["database"] as $engine)

            # Reject unknown engines
            if(!isset(Database::CONFIG[$engine]))

                # Identify the unsupported engine
                throw new CrazyException("Unsupported database choice \"$engine\" for this project.", 400);

        # Accept the PHP version as a string, including numbers from programmatic callers
        $options["php_version"] = (string) $options["php_version"];

        # Validate selected engines before writing files
        foreach([
            "php_version" => array_map("strval", array_keys(self::PHP_VERSIONS)),
            "router" => ["none", "mezon", "fast-route", "league", "symfony"],
            "frontend" => ["none", "webpack", "vite"],
            "authentication" => array_keys(self::authenticationChoices(["database" => $options["database"]])),
        ] as $name => $accepted)

            # Reject unsupported combinations, including SQL authentication on MongoDB
            if(!in_array($options[$name], $accepted, true))

                # Identify the incompatible choice
                throw new CrazyException("Unsupported $name choice for this project.", 400);

        # Keep normalized options for all generation steps
        $this->_options = $options;

        # Use the shared local-first runtime unless a runner is supplied
        $this->_runtime = $runtime ?? new Runtime();

    }

    /** Public Static Methods | Questions
     ******************************************************
     */

    /**
     * Get Required Values
     *
     * @return array
     */
    public static function getRequiredValues():array {

        # Start with the PHP version, then follow the graph's name, description, type and database sequence
        $fields = [self::REQUIRED_VALUES[10], self::REQUIRED_VALUES[0], self::REQUIRED_VALUES[1], self::REQUIRED_VALUES[4], self::REQUIRED_VALUES[6]];

        # Finish with authentication, router and conditional frontend choices
        return [...$fields, self::REQUIRED_VALUES[7], self::REQUIRED_VALUES[8], self::REQUIRED_VALUES[9]];

    }

    /**
     * Authentication Choices
     *
     * @param array $answers Previous form answers
     * @return array
     */
    public static function authenticationChoices(array $answers = []):array {

        # Resolve the selected engines from a form record or a named option
        $databases = (array) ($answers["database"]["value"] ?? $answers["database"] ?? []);

        # Offer the OAuth client for projects with a database
        $choices = ["league/oauth2-client" => "league/oauth2-client"];

        # Delight Auth requires at least one SQL database
        if(array_intersect($databases, ["mariadb", "mysql", "postgresql"]))

            # Include password authentication only for compatible engines
            $choices["delight-im/auth"] = "delight-im/auth";

        # Always allow authentication to be omitted
        return $choices + ["none" => "None"];

    }

    /**
     * Default App Name
     *
     * @return string
     */
    public static function defaultAppName():string {

        # Use the application folder as the initial project name
        return basename(rtrim(File::path("@app_root"), "/\\")) ?: "Crazy Project";

    }

    /** Public Methods | Creation
     ******************************************************
     */

    /**
     * Run
     *
     * @return self
     */
    public function run():self {

        # Execute exactly the steps displayed by the CLI
        foreach($this->getStoryline() as $method)

            # Stop automatically if any step throws an exception
            $this->{$method}();

        # Return the completed generator
        return $this;

    }

    /**
     * Run Backup Composer
     *
     * @return self
     */
    public function runBackupComposer():self {

        # Avoid overwriting an application that has already been initialized
        if(is_file(File::path("@app_root/config/App.yml")) && filesize(File::path("@app_root/config/App.yml")) > 0)

            # Require a fresh project directory
            throw new CrazyException("This application is already initialized; use a new project directory.", 400);

        # Preserve the bootstrap manifest when present
        if(File::exists("@app_root/composer.json"))

            # Keep existing repositories and dependency constraints recoverable
            File::copy("@app_root/composer.json", "@app_root/assets/Json/backup/composer/".uniqid()."-composer.json");

        # Return the generator
        return $this;

    }

    /**
     * Run Composer
     *
     * @return self
     */
    public function runComposer():self {

        # Read the bootstrap manifest without discarding repositories or dependencies
        $manifest = File::exists("@app_root/composer.json") ? Json::open("@app_root/composer.json") : Composer::DEFAULT_VALUE;

        # Normalize the display name into a valid Composer package name
        $name = $this->_packageName();

        # Preserve a supplied vendor prefix or use an application namespace
        $manifest["name"] = str_contains($name, "/") ? $name : "app/".$name;

        # Store project metadata independently from feature choices
        $manifest["description"] = $this->_options["description"];

        # Use Composer's standard package types
        $manifest["type"] = $this->_options["type"] === "library" ? "library" : "project";

        # Autoload application classes without replacing other namespaces
        $manifest["autoload"]["psr-4"]["App\\"] = "app/";

        # Preserve optional metadata supplied by existing programmatic callers
        foreach(["homepage", "authors"] as $key)

            # Only write explicitly supplied values
            if(isset($this->_options[$key]))

                # Retain the caller's metadata
                $manifest[$key] = $this->_options[$key];

        # Preserve author fields from the original CLI record format
        foreach(["name", "email"] as $field)

            # Update only metadata explicitly supplied by the caller
            if(isset($this->_options["authors__".$field]))

                # Keep the first author's remaining metadata intact
                $manifest["authors"][0][$field] = $this->_options["authors__".$field];

        # Require the chosen PHP version
        $manifest["require"]["php"] = "^".$this->_options["php_version"];

        # Resolve dependencies for the chosen version, even when local tools run another PHP version
        $manifest["config"]["platform"]["php"] = $this->_options["php_version"].".0";

        # Include the chosen authentication dependency
        if($this->_options["authentication"] !== "none")

            # Keep an existing constraint when the package is already present
            $manifest["require"][$this->_options["authentication"]] ??= $this->_options["authentication"] === "delight-im/auth" ? "^9.0" : "^2.8";

        # Declare database dependencies before running Composer once
        foreach($this->_databaseDependencies() as $package => $version)

            # Preserve explicit constraints from the bootstrap manifest
            $manifest["require"][$package] ??= $version;

        # Write valid JSON while preserving unrelated manifest properties
        Json::create("@app_root/composer.json", $manifest);

        # Return the generator
        return $this;

    }

    /**
     * Run Npm
     *
     * Create the frontend manifest only for applications and websites.
     *
     * @return self
     */
    public function runNpm():self {

        # Skip frontend files for backend projects
        if($this->_options["frontend"] === "none")

            # Leave this branch immediately
            return $this;

        # Preserve unrelated package metadata
        $manifest = File::exists("@app_root/package.json") ? Json::open("@app_root/package.json") : [];

        # Use an npm-compatible name without Composer's vendor separator
        $manifest["name"] = str_replace("/", "-", $this->_packageName());

        # Prevent accidental publication of the generated application
        $manifest["private"] = true;

        # Carry over the human-readable project description
        $manifest["description"] = $this->_options["description"];

        # Load shared packages and only the selected compiler from package.yml
        $dependencies = Package::getDefaultDevDependencies($this->_options["frontend"]);

        # Link the frontend library to the installed Composer package
        $dependencies["crazyphp"] = Package::CRAZYPHP_PACKAGE_VALUE_DEFAULT;

        # Add defaults without replacing explicit dependency constraints
        $manifest["devDependencies"] = ($manifest["devDependencies"] ?? []) + $dependencies;

        # Select scripts matching the chosen compiler
        $manifest["scripts"] = array_replace($manifest["scripts"] ?? [], $this->_options["frontend"] === "vite" ? [
            "build" => "node vite.build.mjs",
            "dev" => "node vite.build.mjs --mode development",
            "watch" => "node vite.build.mjs --mode development --watch",
        ] : Package::DEFAULT_SCRIPTS);

        # Create the manifest before generating the remaining structure
        Json::create("@app_root/package.json", $manifest);

        # Return the generator
        return $this;

    }

    /**
     * Run Structure Folder
     *
     * @return self
     */
    public function runStructureFolder():self {

        # Start from the maintained application scaffold
        $schema = Yaml::open(Structure::DEFAULT_TEMPLATE);

        # Adapt a copy instead of changing the default template for other generators
        $root = &$schema["Structure"]["@app_root"];

        # Omit frontend sources and build configurations for backend projects
        if($this->_options["frontend"] === "none"){

            # Remove frontend files before generation
            unset($root["folders"]["app"]["folders"]["Front"], $root["folders"]["app"]["folders"]["Environment"]);

            # Remove frontend assets and page controllers
            unset($root["folders"]["assets"]["folders"]["Hbs"], $root["folders"]["app"]["folders"]["Controller"]["folders"]["App"]);

            # Do not generate npm, TypeScript or Webpack files
            unset($root["files"]["package.json"], $root["files"]["tsconfig.json"], $root["files"]["webpack.config.js"]);

        }

        # Libraries and undefined projects do not need the HTTP application shell
        if(in_array($this->_options["type"], ["library", "other"], true)){

            # Keep a small PHP project layout
            $root["folders"] = [
                "app" => null,
                "config" => ["files" => [
                    "App.yml" => null,
                    "Front.yml" => ["source" => "@crazyphp_root/resources/Yml/Front.yml"],
                ]],
                "tests" => null,
                "docs" => null,
            ];

        }

        # Replace Webpack configuration files for the Vite branch
        if($this->_options["frontend"] === "vite"){

            # Avoid copying the incompatible compiler configuration
            unset($root["files"]["webpack.config.js"]);

            # Install the maintained Vite build entrypoint
            $root["files"]["vite.build.mjs"] = ["source" => "@crazyphp_root/resources/Vite/vite.build.mjs"];

        }

        # Create the selected project structure
        Structure::create($schema);

        # Return the generator
        return $this;

    }

    /**
     * Run Config
     *
     * @return self
     */
    public function runConfig():self {

        # Derive application metadata from Composer
        Config::setup();

        # Preserve the project type separately from Composer's package type
        FileConfig::setValue("App.project.type", $this->_options["type"]);

        # Give projects without HTTP routes a minimal router configuration
        if(!File::exists("@app_root/config/Router.yml"))

            # Record router settings without copying application routes
            Yaml::create("@app_root/config/Router.yml", ["Router" => []]);

        # Store adapter selection alongside the route collections
        FileConfig::setValue("Router.vendor", $this->_options["router"]);

        # Record the authentication library without inventing provider credentials
        FileConfig::setValue("App.authentication.library", $this->_options["authentication"]);

        # Store the selected frontend implementation alongside its build configuration
        FileConfig::setValue("Front.bundler", $this->_options["frontend"]);

        # Keep backend projects free of executable frontend scripts
        if($this->_options["frontend"] === "none")

            # Record the disabled frontend explicitly in Front.yml
            FileConfig::setValue("Front.scripts", []);

        # Remove browser routes from a REST API scaffold
        if($this->_options["type"] === "api"){

            # Keep the existing route schema with no application pages
            FileConfig::setValue("Router.app", []);

            # Omit browser asset routes in the API-only project
            FileConfig::setValue("Router.asset", []);

        }

        # Return the generator
        return $this;

    }

    /**
     * Run Composer Update
     *
     * Install the newly selected dependencies once, failing on nonzero status.
     *
     * @return self
     */
    public function runComposerUpdate():self {

        # Resolve the bootstrap manifest plus selected optional dependencies
        $this->_runtime->execute("composer", ["update", "--no-interaction"]);

        # Return the generator
        return $this;

    }

    /**
     * Run Database
     *
     * Write connection and user information without connecting or starting services.
     *
     * @return self
     */
    public function runDatabase():self {

        # Iteration of selected engines, none when no database was selected
        foreach($this->_options["database"] as $engine){

            # Keep the driver's established configuration schema
            $configuration = Database::CONFIG[$engine];

            # Use the supplied server address
            $configuration["host"] = $this->_options["database_host"];

            # Register the requested database name
            $configuration["database"] = [$this->_options["database_name"]];

            # Store the application user's connection information
            $configuration["users"] = [["login" => $this->_options["database_user"], "password" => $this->_options["database_password"]]];

            # Persist only configuration; provisioning remains an explicit separate action
            FileConfig::setValue("Database.collection.$engine", $configuration);

        }

        # Return the generator
        return $this;

    }

    /**
     * Run Confirm Database Passwords
     *
     * Wait for the user to save credentials in the generated YAML before continuing.
     *
     * @return self
     */
    public function runConfirmDatabasePasswords():self {

        # Locate the editable application configuration on the mounted host directory
        $path = File::path("@app_root/config/Database.yml");

        # Libraries and undefined projects do not generate database configuration
        if(!is_file($path))

            # Skip confirmation when there is no database file
            return $this;

        # Keep this step pending until the user explicitly confirms their edits
        while(true){

            # Read the file again so edits and additional databases are included
            $configuration = Yaml::open($path);

            # Exclude disabled engines from the confirmation scope
            $databases = array_filter($configuration["Database"]["collection"] ?? [], static fn($database):bool => is_array($database) && !empty($database));

            # Skip projects without an active database configuration
            if(empty($databases))

                # Leave the wizard free of unnecessary password questions
                return $this;

            # Identify configured engines without printing credentials
            $engines = implode(", ", array_keys($databases));

            # Let the user edit and save the file before answering Yes
            if($this->_runtime->confirm(
                "Update and save all database user and administrator passwords in \"$path\" ($engines). " .
                "Have you updated the passwords for every configured database?"
            )){

                # Discard cached defaults so subsequent reads use the user's saved edits
                unset($GLOBALS[FileConfig::PREFIX]["Database"]);

                # Continue without rewriting the file or contacting a database server
                return $this;

            }

        }

    }

    /**
     * Run Front Script
     *
     * @return self
     */
    public function runFrontScript():self {

        # Only register frontend scripts when a bundler was selected
        if($this->_options["frontend"] !== "none")

            # Make the existing CrazyFront command discover the generated scripts
            FileConfig::setValue("Front.scripts", ["build", "dev", "watch"]);

        # Return the generator
        return $this;

    }

    /**
     * Run Front Dependencies
     *
     * Install the selected frontend toolchain.
     *
     * @return self
     */
    public function runFrontDependencies():self {

        # Install frontend dependencies only on the frontend branch
        if($this->_options["frontend"] !== "none")

            # Prefer local npm and otherwise use the shared Docker runtime
            $this->_runtime->execute("npm", ["install"]);

        # Return the generator
        return $this;

    }

    /**
     * Run Webpack
     *
     * @deprecated Use runFrontDependencies for either bundler.
     * @return self
     */
    public function runWebpack():self {

        # Retain direct callers without duplicating the explicit creation storyline
        return $this->runFrontDependencies();

    }

    /**
     * Run First Compilation
     *
     * @return self
     */
    public function runFirstCompilation():self {

        # Skip compilation for backend projects
        if($this->_options["frontend"] === "none")

            # Return without requiring Node or Docker
            return $this;

        # Compile once with the selected frontend scripts
        $this->_runtime->execute("npm", ["run", "build"]);

        # Register the shared hashed asset layout used by both compilers
        Front::getHash();

        # Record the successful build mode
        FileConfig::setValue("Front.lastBuild.mode", "production");

        # Return the generator
        return $this;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Package Name
     *
     * @return string
     */
    private function _packageName():string {

        # Normalize spaces and unsupported manifest characters
        $name = trim(preg_replace("/[^a-z0-9_.\/-]+/", "-", strtolower(trim($this->_options["name"]))), "-./");

        # Reject names that cannot become valid package identifiers
        if($name === "" || substr_count($name, "/") > 1)

            # Ask for a usable package name before writing a manifest
            throw new CrazyException("Provide a valid project name, optionally vendor/name.", 400);

        # Return the normalized package identifier
        return $name;

    }

    /**
     * Database Dependencies
     *
     * @return array
     */
    private function _databaseDependencies():array {

        # Set result
        $result = [];

        # Declare required PHP extensions of every selected engine so Composer checks the runtime
        foreach($this->_options["database"] as $engine)

            # Merge engine dependencies
            $result += match($engine){
                "mongodb" => ["ext-mongodb" => "^1.21", "mongodb/mongodb" => "^1.21", "phpfastcache/mongodb-extension" => "^9.2"],
                "mariadb", "mysql" => ["ext-pdo_mysql" => "*", "envms/fluentpdo" => "^2.2"],
                "postgresql" => ["ext-pdo_pgsql" => "*"],
                default => [],
            };

        # Return result
        return $result;

    }

}
