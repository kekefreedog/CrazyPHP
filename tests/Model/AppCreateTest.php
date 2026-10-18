<?php declare(strict_types=1);
/**
 * Application Creation Tests
 *
 * Exercise conditional scaffolding without installing packages or contacting databases.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Model;

/**
 * Dependencies
 */
use CrazyPHP\Library\File\Config as FileConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Model\Front\Run as FrontRun;
use CrazyPHP\Library\Database\Database;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\App\Create;
use CrazyPHP\Core\Router;
use CrazyPHP\Model\Env;
use CrazyPHP\Cli\Form;

/**
 * App Create Test
 *
 * Verify manifests, feature branches and failure propagation in temporary projects.
 */
class AppCreateTest extends TestCase {

    /** Private Parameters
     ******************************************************
     */

    /** @var string $_root Isolated application directory */
    private string $_root;

    /** @var RecordingCreateRuntime $_runtime Tool recorder */
    private RecordingCreateRuntime $_runtime;

    /** Public Methods | Fixtures
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Use a path with spaces to catch accidental working-directory assumptions
        $this->_root = sys_get_temp_dir()."/crazy create ".bin2hex(random_bytes(6));

        # Create the isolated application directory
        mkdir($this->_root, 0777, true);

        # Clear previously cached configuration
        unset($GLOBALS[FileConfig::PREFIX]);

        # Point all generators at the fixture
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 2), "phpunit_test" => true]);

        # Preserve a custom dependency and repository in the bootstrap manifest
        Json::create("@app_root/composer.json", [
            "require" => ["kzarshenas/crazyphp" => "@dev"],
            "repositories" => [["type" => "path", "url" => dirname(__DIR__, 2)]],
            "autoload" => ["psr-4" => ["Existing\\" => "existing/"]],
        ]);

        # Record tools without changing the host runtime
        $this->_runtime = new RecordingCreateRuntime();

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove only this test's fixture
        File::removeAll($this->_root);

        # Clear application configuration between fixtures
        unset($GLOBALS[FileConfig::PREFIX]);

        # Restore framework environment defaults
        Env::reset();

    }

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Backend Projects Skip Frontend
     *
     * @param string $type Project type
     * @return void
     */
    #[DataProvider("backendTypes")]
    public function testBackendProjectsSkipFrontend(string $type):void {

        # Run the complete creation flow with a legacy frontend default supplied
        (new Create(["application" => ["name" => "Example Project", "type" => $type, "frontend" => "vite"]], $this->_runtime))->run();

        # Backend projects require only Composer
        $this->assertSame([["composer", ["update", "--no-interaction"]]], $this->_runtime->commands);

        # Skip password confirmation when no databases are configured
        $this->assertSame([], $this->_runtime->questions);

        # Omit frontend manifests and source files entirely
        $this->assertFileDoesNotExist($this->_root."/package.json");

        # Do not copy frontend application source
        $this->assertDirectoryDoesNotExist($this->_root."/app/Front");

        # Do not copy environment templates or components
        $this->assertDirectoryDoesNotExist($this->_root."/app/Environment");

        # Omit compiler configurations
        $this->assertFileDoesNotExist($this->_root."/webpack.config.js");

        # Omit the Vite build entrypoint too
        $this->assertFileDoesNotExist($this->_root."/vite.build.mjs");

        # Persist the explicit absence of frontend tooling
        $this->assertSame("none", FileConfig::getValue("Front.bundler"));

        # Do not offer frontend commands for a backend-only project
        $this->assertSame([], FileConfig::getValue("Front.scripts"));

        # Keep the bundler setting out of application metadata
        $this->assertFalse(FileConfig::has("App.frontend.bundler"));

        # Persist router selection without adding HTTP routes to library projects
        $this->assertSame($type === "api" ? "mezon" : "none", FileConfig::getValue("Router.vendor"));

        # Keep router selection out of application metadata
        $this->assertFalse(FileConfig::has("App.router.vendor"));

        # Keep existing Composer metadata
        $manifest = Json::open("@app_root/composer.json");

        # Preserve the bootstrap package dependency
        $this->assertSame("@dev", $manifest["require"]["kzarshenas/crazyphp"]);

        # Normalize a human-readable name to a valid Composer name
        $this->assertSame("app/example-project", $manifest["name"]);

        # Keep unrelated autoload mappings
        $this->assertSame("existing/", $manifest["autoload"]["psr-4"]["Existing\\"]);

        # Preserve the framework-specific type in App.yml
        $this->assertSame($type, FileConfig::getValue("App.project.type"));

        # Only REST API projects get the HTTP entrypoint
        $this->assertSame($type === "api", is_file($this->_root."/public/index.php"));

    }

    /**
     * Backend Types
     *
     * @return array
     */
    public static function backendTypes():array {

        # Cover each branch that skips frontend setup
        return [["api"], ["library"], ["other"]];

    }

    /**
     * Test Frontend Scaffolds
     *
     * @param string $bundler Selected compiler
     * @return void
     */
    #[DataProvider("frontendBundlers")]
    public function testFrontendScaffolds(string $bundler):void {

        # Create a website using the selected bundler and router
        $create = new Create(["application" => ["name" => "Example", "type" => "website", "frontend" => $bundler, "router" => "symfony"]], $this->_runtime);

        # Verify both invocation paths use the explicit scenario
        $this->assertSame(Create::STORYLINE, $create->getStoryline());

        # Execute the full scaffold workflow
        $create->run();

        # Install dependencies once and compile once
        $this->assertSame([
            ["composer", ["update", "--no-interaction"]],
            ["npm", ["install"]],
            ["npm", ["run", "build"]],
        ], $this->_runtime->commands);

        # Preserve the frontend page sources
        $this->assertFileExists($this->_root."/app/Environment/Page/Home/index.ts");

        # Include every form partial needed by the default template
        $this->assertFileExists($this->_root."/assets/Hbs/partials/form/form_separator.hbs");

        # Generate only the selected bundler's configuration
        $this->assertSame($bundler === "vite", is_file($this->_root."/vite.build.mjs"));

        # Generate Webpack configuration only when selected
        $this->assertSame($bundler === "webpack", is_file($this->_root."/webpack.config.js"));

        # Register the actual compiled entrypoint
        $this->assertSame(["index.1234abcd.js"], FileConfig::getValue("Front.lastBuild.files"));

        # Persist the compiler choice in the generated YAML, independent of its cache
        $this->assertSame($bundler, Yaml::open("@app_root/config/Front.yml")["Front"]["bundler"]);

        # Keep Front.yml as the single source of the compiler choice
        $this->assertFalse(FileConfig::has("App.frontend.bundler"));

        # Persist router vendor selection in Router.yml
        $this->assertSame("symfony", Yaml::open("@app_root/config/Router.yml")["Router"]["vendor"]);

        # Keep Router.yml as the single source of the router choice
        $this->assertFalse(FileConfig::has("App.router.vendor"));

        # Load the configured adapter through the standard framework router
        $router = new Router();

        # Ensure the selected engine participates in matching
        $router->addRoute("/example/[i:id]", static fn():string => "matched", "GET");

        # Confirm route matching through the configured implementation
        $this->assertSame("matched", $router->callRoute("/example/42", "GET"));

        # Read the generated package manifest
        $package = Json::open("@app_root/package.json");

        # Include only the chosen bundler by default
        $this->assertSame($bundler === "vite", isset($package["devDependencies"]["vite"]));

        # Exclude Webpack from the Vite dependency defaults
        $this->assertSame($bundler === "webpack", isset($package["devDependencies"]["webpack"]));

        # Select the published kmaterialize package for new applications
        $this->assertSame("^2.3.3-1.0.38", $package["devDependencies"]["kmaterialize"]);

        # Omit the original framework and unused legacy UI packages
        foreach(["@materializecss/materialize", "material-dynamic-colors", "maska", "clipboard", "@sweetalert2/theme-dark", "@types/quill", "material-symbols", "material-icons", "i18next", "repair", "handlebars-utils"] as $removed)

            # Keep removed defaults out of the generated manifest
            $this->assertArrayNotHasKey($removed, $package["devDependencies"]);

        # Retain optional peers that the generated UI imports directly
        foreach(["tippy.js", "sweetalert2", "tom-select", "filepond", "@simonwep/pickr"] as $peer)

            # Kmaterialize integration does not bundle the underlying widget
            $this->assertArrayHasKey($peer, $package["devDependencies"]);

        # Use the package's declarations instead of copying stock Materialize types
        $this->assertFileDoesNotExist($this->_root."/app/Front/types/materialize.d.ts");

        # Reuse the same runtime when compiling the project again through CrazyFront
        (new FrontRun(["args" => ["build"]], $this->_runtime))->runFrontScript();

        # Keep the public frontend command connected to the selected toolchain
        $this->assertSame(["npm", ["run", "build"]], $this->_runtime->commands[3]);

    }

    /**
     * Frontend Bundlers
     *
     * @return array
     */
    public static function frontendBundlers():array {

        # Exercise both frontend choices
        return [["webpack"], ["vite"]];

    }

    /**
     * Test Frontend Defaults Come From Yaml
     *
     * @return void
     */
    public function testFrontendDefaultsComeFromYaml():void {

        # Supply an isolated framework configuration to prove PHP has no embedded package list
        mkdir($this->_root."/framework/resources/Yml", 0777, true);

        # Define distinct shared and compiler-specific requirements
        Yaml::create($this->_root."/framework/resources/Yml/Package.yml", ["Package" => [
            "devDependencies" => ["shared-package" => "^1.0", "existing-package" => "^2.0"],
            "bundlers" => ["vite" => ["vite-only" => "^3.0"], "webpack" => ["webpack-only" => "^4.0"]],
        ]]);

        # Preserve dependency constraints and metadata supplied by the application
        Json::create("@app_root/package.json", ["devDependencies" => ["existing-package" => "^9.0"], "custom" => true]);

        # Resolve package defaults from the isolated framework root
        Env::set(["crazyphp_root" => $this->_root."/framework"]);

        # Generate only the npm manifest without running installers
        (new Create(["application" => ["frontend" => "vite"]], $this->_runtime))->runNpm();

        # Read the generated requirements
        $manifest = Json::open("@app_root/package.json");

        # Include shared YAML requirements
        $this->assertSame("^1.0", $manifest["devDependencies"]["shared-package"]);

        # Retain the application's explicit constraint
        $this->assertSame("^9.0", $manifest["devDependencies"]["existing-package"]);

        # Include only the selected compiler's YAML requirements
        $this->assertSame("^3.0", $manifest["devDependencies"]["vite-only"]);

        # Do not leak another compiler's packages into the manifest
        $this->assertArrayNotHasKey("webpack-only", $manifest["devDependencies"]);

        # Preserve unrelated application metadata
        $this->assertTrue($manifest["custom"]);

    }

    /**
     * Test Database Configuration
     *
     * @param string $engine Database engine
     * @return void
     */
    #[DataProvider("databaseEngines")]
    public function testDatabaseConfiguration(string $engine):void {

        # Generate an API with the selected database and optional OAuth dependency
        (new Create(["application" => ["type" => "api", "database" => [$engine], "authentication" => "league/oauth2-client"]], $this->_runtime))->run();

        # Read the generated YAML directly, independent of the configuration cache
        $configuration = Yaml::open("@app_root/config/Database.yml")["Database"]["collection"][$engine];

        # Keep the existing default application username
        $this->assertSame("crazyuser", $configuration["users"][0]["login"]);

        # Keep the existing default application password
        $this->assertSame("crazypassword", $configuration["users"][0]["password"]);

        # Keep the configured database name
        $this->assertSame(["crazy_db"], $configuration["database"]);

        # No database creation or Docker service startup is executed
        $this->assertCount(1, $this->_runtime->commands);

        # Ask for confirmation after writing the selected database configuration
        $this->assertCount(1, $this->_runtime->questions);

        # Include the generated file path so the user can edit it
        $this->assertStringContainsString($this->_root."/config/Database.yml", $this->_runtime->questions[0]);

        # Install the selected authentication package through the manifest
        $this->assertSame("^2.8", Json::open("@app_root/composer.json")["require"]["league/oauth2-client"]);

    }

    /**
     * Test Multiple Database Configuration
     *
     * @return void
     */
    public function testMultipleDatabaseConfiguration():void {

        # Generate an API with two selected databases, where None is ignored
        (new Create(["application" => ["type" => "api", "database" => ["mongodb", "postgresql", "none"], "authentication" => "delight-im/auth"]], $this->_runtime))->run();

        # Read the generated YAML directly, independent of the configuration cache
        $collection = Yaml::open("@app_root/config/Database.yml")["Database"]["collection"];

        # Configure each selected engine with the shared defaults
        foreach(["mongodb", "postgresql"] as $engine)

            # Keep the configured database name
            $this->assertSame(["crazy_db"], $collection[$engine]["database"]);

        # Get composer requirements
        $require = Json::open("@app_root/composer.json")["require"];

        # Require the dependencies of every selected engine
        $this->assertArrayHasKey("ext-mongodb", $require);
        $this->assertArrayHasKey("ext-pdo_pgsql", $require);

        # Allow the SQL-only authentication library when one SQL engine is selected
        $this->assertSame("^9.0", $require["delight-im/auth"]);

    }

    /**
     * Database Engines
     *
     * @return array
     */
    public static function databaseEngines():array {

        # Cover each configured driver including legacy MySQL input
        return [["mongodb"], ["mariadb"], ["mysql"], ["postgresql"]];

    }

    /**
     * Test Password Confirmation Preserves Edits And Includes Added Databases
     *
     * @return void
     */
    public function testPasswordConfirmationPreservesEditsAndIncludesAddedDatabases():void {

        # Capture the exact saved file to detect unwanted rewrites after confirmation
        $saved = "";

        # Simulate editing the file while the wizard waits for confirmation
        $this->_runtime->onConfirm = function(string $question) use (&$saved):bool {

            # Do not compile before the user has confirmed the password changes
            $this->assertCount(2, $this->_runtime->commands);

            # Never include passwords in the question
            $this->assertStringNotContainsString("crazypassword", $question);

            # Make the first answer No while adding another configured database
            if(count($this->_runtime->questions) === 1){

                # Populate the cache with the original value before the external edit
                $this->assertSame("crazypassword", FileConfig::getValue("Database.collection.mariadb.users.0.password"));

                # Read the same file the user would edit in their editor
                $configuration = Yaml::open("@app_root/config/Database.yml");

                # Replace the default application password
                $configuration["Database"]["collection"]["mariadb"]["users"][0]["password"] = "updated-user-secret";

                # Replace the administrator password too
                $configuration["Database"]["collection"]["mariadb"]["root"]["password"] = "updated-admin-secret";

                # Add a second engine with multiple database names
                $configuration["Database"]["collection"]["postgresql"] = array_replace(Database::CONFIG["postgresql"], ["database" => ["primary", "secondary"]]);

                # Save changes without updating the framework's configuration cache
                Yaml::create("@app_root/config/Database.yml", $configuration);

                # Remember the user's saved content
                $saved = file_get_contents($this->_root."/config/Database.yml");

                # Keep the confirmation step pending
                return false;

            }

            # Include all currently configured engines after the file is reloaded
            $this->assertStringContainsString("mariadb, postgresql", $question);

            # Do not reveal edited passwords either
            $this->assertStringNotContainsString("updated-user-secret", $question);

            # Explicitly confirm the saved changes on the second prompt
            return true;

        };

        # Run the entire wizard sequence with a database and frontend
        (new Create(["application" => ["database" => "mariadb"]], $this->_runtime))->run();

        # Require a new confirmation after a negative answer
        $this->assertCount(2, $this->_runtime->questions);

        # Only start compilation after the affirmative answer
        $this->assertSame(["npm", ["run", "build"]], $this->_runtime->commands[2]);

        # Preserve the user's file exactly as saved
        $this->assertSame($saved, file_get_contents($this->_root."/config/Database.yml"));

        # Reload saved credentials instead of returning the cached defaults
        $this->assertSame("updated-user-secret", FileConfig::getValue("Database.collection.mariadb.users.0.password"));

    }

    /**
     * Test Conditional Questions
     *
     * @return void
     */
    public function testConditionalQuestions():void {

        # Resolve questions by their public field names
        $fields = array_column(Create::getRequiredValues(), null, "name");

        # Follow the diagram's prompt ordering
        $this->assertSame(["php_version", "name", "description", "type", "database", "authentication", "router", "frontend"], array_keys($fields));

        # Libraries skip database selection
        $this->assertFalse(Form::isVisible($fields["database"], ["type" => ["value" => "library"]]));

        # REST APIs retain their database selection
        $this->assertTrue(Form::isVisible($fields["database"], ["type" => ["value" => "api"]]));

        # REST APIs skip frontend selection
        $this->assertFalse(Form::isVisible($fields["frontend"], ["type" => ["value" => "api"]]));

        # Database None skips authentication
        $this->assertFalse(Form::isVisible($fields["authentication"], ["database" => ["value" => "none"]]));

        # A multiple selection shows authentication when any engine is selected
        $this->assertTrue(Form::isVisible($fields["authentication"], ["database" => ["value" => ["mongodb", "postgresql"]]]));

        # An empty multiple selection skips authentication
        $this->assertFalse(Form::isVisible($fields["authentication"], ["database" => ["value" => []]]));

        # Delight Auth is offered when the selection includes a SQL engine
        $this->assertArrayHasKey("delight-im/auth", Create::authenticationChoices(["database" => ["value" => ["mongodb", "mariadb"]]]));

        # MongoDB cannot offer the SQL-only authentication library
        $this->assertArrayNotHasKey("delight-im/auth", Create::authenticationChoices(["database" => ["value" => "mongodb"]]));

        # SQL projects can select Delight Auth
        $this->assertArrayHasKey("delight-im/auth", Create::authenticationChoices(["database" => ["value" => "postgresql"]]));

        # Pass answer context to the authentication provider through the actual form resolver
        $this->assertArrayNotHasKey("delight-im/auth", Form::getChoices($fields["authentication"], ["database" => ["value" => "mongodb"]]));

        # Preserve callbacks whose optional argument predates conditional form questions
        $this->assertSame(["legacy"], Form::getChoices(["select" => static fn(bool $onlyValue = false):array => $onlyValue ? [] : ["legacy"]], ["type" => "api"]));

    }

    /**
     * Test Failed Install Stops Creation
     *
     * @return void
     */
    public function testFailedInstallStopsCreation():void {

        # Simulate a failed Composer subprocess
        $this->_runtime->fail = true;

        # Require the failure to reach the caller
        $this->expectException(CrazyException::class);

        # Stop before npm installation and compilation
        try{

            # Run the full creation sequence
            (new Create(["application" => ["name" => "Failure"]], $this->_runtime))->run();

        }finally{

            # No later tools may execute after a failed dependency installation
            $this->assertCount(1, $this->_runtime->commands);

        }

    }

    /**
     * Test Php Version
     *
     * @return void
     */
    public function testPhpVersion():void {

        # Propose Vite as the default frontend bundler
        $this->assertSame("vite", Create::REQUIRED_VALUES[9]["default"]);

        # Propose the latest PHP version first
        $this->assertSame(Create::LATEST_PHP_VERSION, array_key_first(Create::PHP_VERSIONS));
        $this->assertSame(Create::LATEST_PHP_VERSION, Create::getRequiredValues()[0]["default"]);

        # Generate an API with an older supported version
        (new Create(["application" => ["type" => "api", "php_version" => "8.4"]], $this->_runtime))->run();

        # Get composer manifest
        $manifest = Json::open("@app_root/composer.json");

        # Require the chosen version and resolve dependencies for it
        $this->assertSame("^8.4", $manifest["require"]["php"]);
        $this->assertSame("8.4.0", $manifest["config"]["platform"]["php"]);

        # Share the version with Docker and the CLI image
        $this->assertSame("8.4", Composer::getPhpVersion());

    }

    /**
     * Test Unsupported Php Version
     *
     * @return void
     */
    public function testUnsupportedPhpVersion():void {

        # Expect error
        $this->expectExceptionMessage("Unsupported php_version choice");

        # Validate before modifying project files
        new Create(["application" => ["php_version" => "7.4"]]);

    }

    /**
     * Test Invalid Authentication Combination
     *
     * @return void
     */
    public function testInvalidAuthenticationCombination():void {

        # Reject a SQL-only authentication library with MongoDB
        $this->expectException(CrazyException::class);

        # Validate before modifying project files
        new Create(["application" => ["database" => "mongodb", "authentication" => "delight-im/auth"]]);

    }

    /**
     * Test Existing Project Is Preserved
     *
     * @return void
     */
    public function testExistingProjectIsPreserved():void {

        # Create a project once
        $create = new Create(["application" => ["type" => "library"]], $this->_runtime);

        # Complete its scaffold
        $create->run();

        # Require an explicit error before overwriting an initialized project
        $this->expectException(CrazyException::class);

        # Reject the second initialization
        $create->run();

    }

}

/**
 * Recording Create Runtime
 *
 * Keep external tools out of scaffold unit tests.
 */
class RecordingCreateRuntime extends Runtime {

    /** @var array $commands Executed tools and arguments */
    public array $commands = [];

    /** @var bool $fail Simulate installation failure */
    public bool $fail = false;

    /** @var array $questions Recorded confirmation prompts */
    public array $questions = [];

    /** @var \Closure|null $onConfirm Simulated interactive edits and answers */
    public ?\Closure $onConfirm = null;

    /**
     * Confirm
     *
     * @param string $question Requested confirmation
     * @return bool
     */
    public function confirm(string $question):bool {

        # Record the prompt without requiring terminal input during tests
        $this->questions[] = $question;

        # Allow tests to simulate editor changes while the command is paused
        return $this->onConfirm !== null ? ($this->onConfirm)($question) : true;

    }

    /**
     * Execute
     *
     * @param string $tool Requested tool
     * @param array $arguments Requested arguments
     * @return void
     */
    public function execute(string $tool, array $arguments = []):void {

        # Record the observable tool invocation
        $this->commands[] = [$tool, $arguments];

        # Simulate the runtime's failure contract
        if($this->fail)

            # Stop creation at the current step
            throw new CrazyException("Simulated install failure", 500);

        # Provide a compiler artifact for asset registration
        if($tool === "npm" && $arguments === ["run", "build"])

            # Write an isolated output file with the shared hash convention
            file_put_contents(File::path("@app_root/public/dist/index.1234abcd.js"), "/* compiled fixture */");

    }

}
