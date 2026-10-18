<?php declare(strict_types=1);
/**
 * Frontend Runner Tests
 *
 * Verify bundler selection, argument forwarding and build metadata transactions.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Model;

/** Dependencies */
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Front\Run;
use CrazyPHP\Model\Env;

/**
 * Front Run Test
 *
 * Exercise the public runner without invoking npm or a Docker daemon.
 */
class FrontRunTest extends TestCase {

    /** @var string $_root Isolated application directory */
    private string $_root;

    /** @var FrontRuntime $_runtime Recorded compiler runtime */
    private FrontRuntime $_runtime;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Use a custom document root to verify asset discovery follows App.public
        $this->_root = sys_get_temp_dir()."/crazy front ".bin2hex(random_bytes(6));
        mkdir($this->_root."/config", 0755, true);
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 2)]);
        unset($GLOBALS[Config::PREFIX]);
        Yaml::create($this->_root."/config/App.yml", ["App" => ["public" => "web"]]);
        $this->_runtime = new FrontRuntime($this->_root);

        # Preserve script values containing spaces and literal shell metacharacters
        file_put_contents($this->_root."/package.json", json_encode(["scripts" => ["build" => "compiler --mode production", "watch" => "compiler --watch --mode development"]]));

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Clean only this fixture and release process-local configuration
        File::removeWithin(dirname($this->_root), basename($this->_root));
        unset($GLOBALS[Config::PREFIX]);
        Env::reset();

    }

    /**
     * Bundlers
     *
     * @return array
     */
    public static function bundlers():array {

        # Include old Front.yml files that do not declare a bundler
        return [["webpack"], ["vite"], [null]];

    }

    /**
     * Test Build With Either Bundler
     *
     * @param string|null $bundler Selected compiler or legacy configuration
     * @return void
     */
    #[DataProvider("bundlers")]
    public function testBuildWithEitherBundler(?string $bundler):void {

        # Start from a stale watch build and preserve unrelated frontend settings
        $this->_front($bundler);

        # Forward an explicit mode and a value that must remain a single argument
        (new Run(["args" => ["build", "--", "--mode", "development", "--define", 'literal spaces;$(false)']], $this->_runtime))->run();

        # Use one checked installation and one compiler invocation
        $this->assertSame([
            ["npm", ["install"]],
            ["npm", ["run", "build", "--", "--mode", "development", "--define", 'literal spaces;$(false)']],
        ], $this->_runtime->commands);

        # Select only the new build and preserve runtime-before-entry ordering
        $front = Yaml::open($this->_root."/config/Front.yml")["Front"];
        $this->assertSame(["runtime.bbbbbbbb.js", "vendors.bbbbbbbb.js", "index.bbbbbbbb.js"], $front["lastBuild"]["files"]);
        $this->assertSame(["Home.bbbbbbbb.js"], $front["lastBuild"]["pages"]);
        $this->assertSame("bbbbbbbb", $front["lastBuild"]["hash"]);
        $this->assertSame("development", $front["lastBuild"]["mode"]);
        $this->assertFalse($front["lastBuild"]["watch"]);
        $this->assertNotEmpty($front["lastBuild"]["date"]);
        $this->assertSame("keep", $front["custom"]);

    }

    /**
     * Test WatchMetadataVisibleDuringCompilation
     *
     * @return void
     */
    public function testWatchMetadataVisibleDuringCompilation():void {

        # Run the watch script through the explicit storyline
        $this->_front("vite");
        $runner = new Run(["args" => ["watch"]], $this->_runtime);
        $this->assertSame(Run::STORYLINE, $runner->getStoryline());
        $runner->run();

        # Middleware can discover outputs while the child watcher is active
        $this->assertTrue($this->_runtime->watchDuringCompilation);
        $this->assertTrue(Config::getValue("Front.lastBuild.watch"));
        $this->assertSame("development", Config::getValue("Front.lastBuild.mode"));
        $this->assertCount(2, $this->_runtime->commands);

    }

    /**
     * Test Explicit Watch And Mode Overrides
     *
     * @return void
     */
    public function testExplicitWatchAndModeOverrides():void {

        # Override a watch script with one finite build and an explicit mode
        $this->_front("vite");
        (new Run(["args" => ["watch", "--watch=false", "--mode=production"]], $this->_runtime))->run();

        # Ensure recorded metadata agrees with the flags forwarded to the compiler
        $this->assertFalse($this->_runtime->watchDuringCompilation);
        $this->assertFalse(Config::getValue("Front.lastBuild.watch"));
        $this->assertSame("production", Config::getValue("Front.lastBuild.mode"));

    }

    /**
     * Test First Build Without Page Outputs
     *
     * @return void
     */
    public function testFirstBuildWithoutPageOutputs():void {

        # Missing output directories must not make discovery throw before the first build
        Yaml::create($this->_root."/config/Front.yml", ["Front" => ["bundler" => "vite"]]);
        $runner = new Run([], $this->_runtime);
        $runner->runGeneratedFilesOnConfig();
        $this->assertFalse(Config::has("Front.lastBuild.hash"));

        # Support an entrypoint-only application without a page/app directory
        mkdir($this->_root."/web/dist", 0755, true);
        file_put_contents($this->_root."/web/dist/index.12345678.js", "// fixture");
        $runner->runGeneratedFilesOnConfig();
        $this->assertSame("12345678", Config::getValue("Front.lastBuild.hash"));
        $this->assertSame([], Config::getValue("Front.lastBuild.pages"));

    }

    /**
     * Test Compiler Failure Restores Previous Build
     *
     * @return void
     */
    public function testCompilerFailureRestoresPreviousBuild():void {

        # Capture the last successful state before requesting another build
        $this->_front("vite");
        $previous = Config::getValue("Front.lastBuild");
        $this->_runtime->fail = "run";
        $this->expectExceptionMessage("compiler failed");

        try{

            # Propagate the compiler error through the public runner
            (new Run([], $this->_runtime))->run();

        }finally{

            # Do not publish success, a fresh date or stale watch/mode flags
            $this->assertSame($previous, Config::getValue("Front.lastBuild"));

        }

    }

    /**
     * Test Split Phases For Host Npm
     *
     * The launcher runs PHP in Docker and npm on the host: prepare, then finish or failed.
     *
     * @return void
     */
    public function testSplitPhasesForHostNpm():void {

        # Prepare a project and keep its last build
        $this->_front("vite");
        $previous = Config::getValue("Front.lastBuild");

        try{

            # Prepare: no npm call, watch metadata written, previous build kept for the next phase
            putenv(Run::PHASE_ENV."=prepare");
            (new Run(["args" => ["build"]], $this->_runtime))->run();
            $this->assertSame([], $this->_runtime->commands);
            $this->assertSame("production", Config::getValue("Front.lastBuild.mode"));
            $this->assertFileExists(File::path(Run::PREVIOUS_BUILD_FILE));

            # Failed: the previous build is restored, the saved copy removed and the failure reported
            putenv(Run::PHASE_ENV."=failed");
            try{
                (new Run(["args" => ["build"]], $this->_runtime))->run();
                $this->fail("A failed host script must be reported.");
            }catch(CrazyException $exception){
                $this->assertStringContainsString("failed or was stopped", $exception->getMessage());
            }
            $this->assertSame($previous, Config::getValue("Front.lastBuild"));
            $this->assertFileDoesNotExist(File::path(Run::PREVIOUS_BUILD_FILE));

            # Prepare then finish: the build is registered without any npm call in Docker
            putenv(Run::PHASE_ENV."=prepare");
            (new Run(["args" => ["build"]], $this->_runtime))->run();
            putenv(Run::PHASE_ENV."=finish");
            (new Run(["args" => ["build"]], $this->_runtime))->run();
            $this->assertSame([], $this->_runtime->commands);
            $this->assertFileDoesNotExist(File::path(Run::PREVIOUS_BUILD_FILE));

        }finally{

            # Restore a complete run for the other tests
            putenv(Run::PHASE_ENV);

        }

    }

    /**
     * Test Stopped Watcher Retains Published Build
     *
     * @return void
     */
    public function testStoppedWatcherRetainsPublishedBuild():void {

        # A watcher can be interrupted after successfully emitting a complete build
        $this->_front("vite");
        $this->_runtime->fail = "afterBuild";
        $this->expectExceptionMessage("watch stopped");

        try{

            # Keep interruption visible to callers while preserving usable output references
            (new Run(["args" => ["watch"]], $this->_runtime))->run();

        }finally{

            # Do not restore the old hash after the watcher replaced its files
            $this->assertSame("bbbbbbbb", Config::getValue("Front.lastBuild.hash"));
            $this->assertSame(["Home.bbbbbbbb.js"], Config::getValue("Front.lastBuild.pages"));

        }

    }

    /**
     * Test Installation Failure Stops Compilation
     *
     * @return void
     */
    public function testInstallationFailureStopsCompilation():void {

        # Fail installation before build metadata can change
        $this->_front("webpack");
        $previous = Config::getValue("Front.lastBuild");
        $this->_runtime->fail = "install";
        $this->expectExceptionMessage("compiler failed");

        try{

            # Run the complete sequence against the failing tool
            (new Run([], $this->_runtime))->run();

        }finally{

            # Execute no compiler process and preserve the previous build
            $this->assertSame([["npm", ["install"]]], $this->_runtime->commands);
            $this->assertSame($previous, Config::getValue("Front.lastBuild"));

        }

    }

    /**
     * Invalid Selections
     *
     * @return array
     */
    public static function invalidSelections():array {

        # Cover disabled frontend projects, unsupported bundlers and absent package scripts
        return [["none", "build"], ["unknown", "build"], ["vite", "missing"]];

    }

    /**
     * Test Invalid Selection Does Not Install
     *
     * @param string $bundler Selected bundler
     * @param string $script Selected script
     * @return void
     */
    #[DataProvider("invalidSelections")]
    public function testInvalidSelectionDoesNotInstall(string $bundler, string $script):void {

        # Validate inputs before spawning a package manager
        $this->_front($bundler);
        $this->expectException(CrazyException::class);

        try{

            # Reject the invalid selection
            (new Run(["args" => [$script]], $this->_runtime))->run();

        }finally{

            # Never install dependencies for an invalid command
            $this->assertSame([], $this->_runtime->commands);

        }

    }

    /**
     * Front
     *
     * @param string|null $bundler Optional bundler field
     * @return void
     */
    private function _front(?string $bundler):void {

        # Keep a recognizable old build for failure and watch-mode assertions
        $front = ["custom" => "keep", "lastBuild" => ["watch" => true, "mode" => "old", "hash" => "aaaaaaaa", "date" => "old", "files" => ["index.aaaaaaaa.js"]]];
        if($bundler !== null)
            $front["bundler"] = $bundler;

        # Save the fixture's frontend configuration
        Yaml::create($this->_root."/config/Front.yml", ["Front" => $front]);

    }

}

/** Fake compiler that writes observable build outputs. */
class FrontRuntime extends Runtime {

    /** @var array $commands Executed tool arguments */
    public array $commands = [];

    /** @var string $fail Step to fail */
    public string $fail = "";

    /** @var bool $watchDuringCompilation Watch flag observed by the compiler */
    public bool $watchDuringCompilation = false;

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
     * @param array $arguments Arguments preserved by the runner
     * @return void
     */
    public function execute(string $tool, array $arguments = []):void {

        # Record invocation boundaries and inject failures before emitting assets
        $this->commands[] = [$tool, $arguments];
        if($this->fail === $arguments[0])
            throw new CrazyException("compiler failed", 500);

        # Dependency installation has no build output
        if($arguments[0] !== "run")
            return;

        # Inspect live watch metadata and generate both an old and a new build
        $this->watchDuringCompilation = Config::getValue("Front.lastBuild.watch");
        mkdir($this->_root."/web/dist/page/app", 0755, true);
        foreach(["aaaaaaaa" => 100, "bbbbbbbb" => 200] as $hash => $time)
            foreach(["runtime", "vendors", "index", "page/app/Home"] as $name){

                # Give the build selector deterministic timestamps
                $path = $this->_root."/web/dist/$name.$hash.js";
                file_put_contents($path, "// fixture");
                touch($path, $time);

            }

        # Simulate a watcher being stopped after its first successful build
        if($this->fail === "afterBuild")
            throw new CrazyException("watch stopped", 500);

    }

}
