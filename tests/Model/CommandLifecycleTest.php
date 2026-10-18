<?php declare(strict_types=1);
/**
 * Command Cleanup Tests
 *
 * Exercise deletion using isolated projects and a recorded Docker runtime.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Model;

/** Dependencies */
use CrazyPHP\Model\Docker\Delete as DockerDelete;
use CrazyPHP\Model\App\Delete as AppDelete;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Command Lifecycle Test
 *
 * Keep data and outside symlink targets intact while removing generated files.
 */
class CommandLifecycleTest extends TestCase {

    /** @var string $_root Isolated application */
    private string $_root;

    /** @var CleanupRuntime $_runtime Recorded subprocesses */
    private CleanupRuntime $_runtime;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Initialize a separate application with a custom public folder
        $this->_root = sys_get_temp_dir()."/crazy cleanup ".bin2hex(random_bytes(6));
        mkdir($this->_root."/config", 0755, true);
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 2)]);
        unset($GLOBALS[Config::PREFIX]);
        Yaml::create($this->_root."/config/App.yml", ["App" => ["public" => "web"]]);

        # Prepare database settings including disabled entries and Docker metadata
        Yaml::create($this->_root."/config/Database.yml", ["Database" => ["collection" => ["mysql" => false, "mongodb" => ["host" => "localhost", "port" => 27018, "root" => ["password" => "keep-secret"], "docker" => ["service" => ["name" => "mongo"]]]]]]);

        # Record Docker commands without contacting a daemon
        $this->_runtime = new CleanupRuntime($this->_root);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Delete only this fixture without following linked targets
        File::removeWithin(dirname($this->_root), basename($this->_root));
        unset($GLOBALS[Config::PREFIX]);
        Env::reset();

    }

    /**
     * Test Docker Cleanup Preserves Data And Custom Files
     *
     * @return void
     */
    public function testDockerCleanupPreservesDataAndCustomFiles():void {

        # Include generated and custom files in the same Docker directory
        $this->_docker();
        $this->_files(["docker/custom.txt", ".env", ".database/mongo/data"]);

        # Exercise the complete ordered command
        (new DockerDelete([], $this->_runtime))->run();

        # Stop containers once, without any volume-removal flag
        $this->assertSame([["docker", ["compose", "down"]]], $this->_runtime->commands);
        $this->assertTrue($this->_runtime->configurationPresent);

        # Remove the current lifecycle files and marker
        foreach(["docker-compose.yml", "config/Docker.yml", "docker/bin/Prepare", "docker/bin/SetupDatabase", "docker/config/Database.yml", "docker/php/Dockerfile"] as $path)
            $this->assertFileDoesNotExist($this->_root."/".$path);

        # Preserve custom files, host data, ports and secrets
        foreach(["docker/custom.txt", ".env", ".database/mongo/data"] as $path)
            $this->assertFileExists($this->_root."/".$path);
        $database = Yaml::open($this->_root."/config/Database.yml")["Database"]["collection"];
        $this->assertFalse($database["mysql"]);
        $this->assertSame(["host" => "localhost", "port" => 27018, "root" => ["password" => "keep-secret"]], $database["mongodb"]);

        # Repeating cleanup requires no Docker daemon after generated files are gone
        (new DockerDelete([], $this->_runtime))->run();
        $this->assertCount(1, $this->_runtime->commands);

    }

    /**
     * Test Failed Shutdown Leaves Application Intact
     *
     * @return void
     */
    public function testFailedShutdownLeavesApplicationIntact():void {

        # Prepare application files that must survive a failed Docker command
        $this->_docker();
        $this->_files(["app/Index.php", "web/index.php", "assets/keep.txt", "composer.json"]);
        $this->_runtime->fail = true;
        $this->expectExceptionMessage("shutdown failed");

        try{

            # Fail at shutdown before any cleanup occurs
            (new AppDelete([], $this->_runtime))->run();

        }finally{

            # Retain all files needed to diagnose and retry the operation
            foreach(["docker-compose.yml", "config/App.yml", "config/Docker.yml", "app/Index.php", "web/index.php", "assets/keep.txt", "composer.json"] as $path)
                $this->assertFileExists($this->_root."/".$path);

        }

    }

    /**
     * Test Application Cleanup Restores Current Backup And Removes Both Bundlers
     *
     * @return void
     */
    public function testApplicationCleanupRestoresCurrentBackupAndRemovesBothBundlers():void {

        # Include both old and new frontend artifacts and preserved project data
        $this->_docker();
        $this->_files(["app/Index.php", "assets/custom.txt", "web/index.php", "public/unrelated.txt", "vite.build.mjs", "webpack.config.js", "webpack.dev.js", "webpack.prod.js", "package.json", "package-lock.json", "node_modules/module/index.js", "composer.lock", "vendor/keep.php", ".env", ".gitignore", ".database/mongo/data", ".cache/app/stale", "config/Custom.yml"]);

        # Restore the latest recognized backup, including the current uniqid naming format
        $this->_files(["assets/Json/backup/composer/123-new-composer.json", "assets/Json/backup/composer/123456789abcd-composer.json"]);
        file_put_contents($this->_root."/assets/Json/backup/composer/123-new-composer.json", '{"name":"old/app"}');
        touch($this->_root."/assets/Json/backup/composer/123-new-composer.json", 100);
        file_put_contents($this->_root."/assets/Json/backup/composer/123456789abcd-composer.json", '{"name":"bootstrap/app"}');
        file_put_contents($this->_root."/composer.json", '{"name":"generated/app"}');

        # Use the same explicit sequence as the CLI
        $delete = new AppDelete([], $this->_runtime);
        $this->assertSame(AppDelete::STORYLINE, $delete->getStoryline());
        $delete->run();

        # Recover the bootstrap manifest and invalidate the generated lock
        $this->assertSame('{"name":"bootstrap/app"}', file_get_contents($this->_root."/composer.json"));
        foreach(["app", "assets", "web", "node_modules", ".cache/app", ".database"] as $path)
            $this->assertDirectoryDoesNotExist($this->_root."/".$path);
        foreach(["config/App.yml", "config/Database.yml", ".env", "vite.build.mjs", "webpack.config.js", "webpack.dev.js", "webpack.prod.js", "package.json", "package-lock.json", "composer.lock"] as $path)
            $this->assertFileDoesNotExist($this->_root."/".$path);

        # Keep only Composer's manifest and dependencies
        $this->assertFileExists($this->_root."/vendor/keep.php");
        $this->assertSame(["composer.json", "vendor"], array_values(array_diff(scandir($this->_root), [".", ".."])));
        $this->assertTrue($this->_runtime->configurationPresent);

    }

    /**
     * Test BackendCleanupWithoutDockerOrBackups
     *
     * @return void
     */
    public function testBackendCleanupWithoutDockerOrBackups():void {

        # Backend projects may have neither frontend files nor a manifest backup
        file_put_contents($this->_root."/composer.json", '{"name":"keep/app"}');
        (new AppDelete([], $this->_runtime))->run();

        # Leave the installed framework manifest usable and execute no external tools
        $this->assertSame([], $this->_runtime->commands);
        $this->assertSame('{"name":"keep/app"}', file_get_contents($this->_root."/composer.json"));

        # Remove generated database settings and the then empty config folder
        $this->assertDirectoryDoesNotExist($this->_root."/config");

    }

    /**
     * Test Linked Directory Targets Survive
     *
     * @return void
     */
    public function testLinkedDirectoryTargetsSurvive():void {

        # Model an external asset checkout using another isolated sibling directory
        $outside = $this->_root."-outside";
        mkdir($outside);
        file_put_contents($outside."/keep.txt", "outside");

        try{

            # Application-owned links must be unlinked without touching their targets
            symlink($outside, $this->_root."/assets");
            symlink($outside, $this->_root."/web");
            (new AppDelete([], $this->_runtime))->run();
            $this->assertFileExists($outside."/keep.txt");
            $this->assertFalse(is_link($this->_root."/assets"));
            $this->assertFalse(is_link($this->_root."/web"));

        }finally{

            # Clean only the fixture's external target
            File::removeWithin(dirname($outside), basename($outside));

        }

    }

    /**
     * Test Unsafe Public Directory Stops Before Cleanup
     *
     * @return void
     */
    public function testUnsafePublicDirectoryStopsBeforeCleanup():void {

        # Reject traversal stored in configuration before removing any source files
        Yaml::create($this->_root."/config/App.yml", ["App" => ["public" => "../outside"]]);
        $this->expectExceptionMessage("safe application document root");
        (new AppDelete([], $this->_runtime))->run();

    }

    /**
     * Docker
     *
     * @return void
     */
    private function _docker():void {

        # Create the files expected from the current Docker generator
        $this->_files(["docker-compose.yml", "config/Docker.yml", "docker/bin/Prepare", "docker/bin/SetupDatabase", "docker/config/Database.yml", "docker/php/Dockerfile"]);

    }

    /**
     * Files
     *
     * @param array $paths Relative fixture files
     * @return void
     */
    private function _files(array $paths):void {

        # Populate only this test's temporary application
        foreach($paths as $path){

            # Create the containing directory when needed
            if(!is_dir(dirname($this->_root."/".$path)))
                mkdir(dirname($this->_root."/".$path), 0755, true);

            # Mark the path as an ordinary fixture file
            file_put_contents($this->_root."/".$path, "fixture");

        }

    }

}

/** Recorded Docker runtime for cleanup tests. */
class CleanupRuntime extends Runtime {

    /** @var array $commands Executed tool arguments */
    public array $commands = [];

    /** @var bool $fail Inject a daemon failure */
    public bool $fail = false;

    /** @var bool $configurationPresent Configuration exists at shutdown */
    public bool $configurationPresent = false;

    /**
     * Constructor
     *
     * @param string $_root Fixture root
     */
    public function __construct(private string $_root){}

    /**
     * Execute
     *
     * @param string $tool Tool name
     * @param array $arguments Command arguments
     * @return void
     */
    public function execute(string $tool, array $arguments = []):void {

        # Record the command while checking the files required by Compose still exist
        $this->commands[] = [$tool, $arguments];
        $this->configurationPresent = is_file($this->_root."/docker-compose.yml") && is_file($this->_root."/config/App.yml");

        # Simulate a failed shutdown without contacting a real daemon
        if($this->fail)
            throw new CrazyException("shutdown failed", 500);

    }

}
