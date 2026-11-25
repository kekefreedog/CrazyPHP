<?php declare(strict_types=1);
/**
 * Verify partial discovery and caching through the HTML document renderer.
 *
 * @package kzarshenas/crazyphp
 */
namespace Tests\Library\Html;

/**
 * Dependances
 */
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\State\Page;
use CrazyPHP\Core\Controller;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Context;
use CrazyPHP\Model\Env;

/** Exercise production, development and watch builds in isolated applications. */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class StructurePartialsTest extends TestCase {

    /** @var string Isolated application root */
    private string $_root;

    /** Set up a page whose partial name comes from its current data. */
    protected function setUp():void {

        # Create a unique application directory with no initial partials
        $this->_root = sys_get_temp_dir()."/crazy-partials-".bin2hex(random_bytes(6));

        # Create the application root
        mkdir($this->_root);

        # Use the application's real Files pool
        Env::set([
            "crazyphp_root"    =>  dirname(__DIR__, 3),
            "cache_driver"     =>  "Files",
            "app_root"         =>  $this->_root
        ]);

        # Supply the public asset path used by the outer document in watch mode
        $GLOBALS[Config::PREFIX]["App"] = [
            "App"    =>  [
                "public"    =>  "public"
            ]
        ];

        # Set up an HTML page route
        Context::set([
            "group"    =>  "app",
            "name"     =>  "fixture"
        ], "routes.current");

        # Render a dynamic partial from the same parent template on every request
        file_put_contents($this->_root."/page.hbs", '<p>{{#if part}}{{> (lookup . "part")}}{{/if}}</p>');

    }

    /** Remove this test's private cache and templates. */
    protected function tearDown():void {

        # Remove only this fixture
        File::removeWithin(dirname($this->_root), basename($this->_root));

    }

    /** Cover both non-watching development builds and active watchers. */
    public static function liveModes():array {

        # Select each mode that must discover changes without another build
        $result = [
            "development"    =>  [
                "development",
                false
            ],
            "watch"          =>  [
                "production",
                true
            ]
        ];

        # Return the build combinations
        return $result;

    }

    /** Discover additions, same-timestamp edits, renames and deletions. */
    #[DataProvider("liveModes")]
    public function testLiveDiscovery(string $mode, bool $watch):void {

        # Keep build identity fixed throughout the edits
        $this->_build($mode, $watch);

        # Render before even the partials directory exists
        self::assertStringContainsString("<p></p>", $this->_render(""));

        # Create nested directories after the initial render
        $dir = $this->_root."/assets/Hbs/partials/nested";

        # Create the new partial's directory
        mkdir($dir, 0755, true);

        # Write a newly discovered underscore-prefixed partial
        file_put_contents($dir."/_one.hbs", "alpha");

        # Resolve the legacy alias without an underscore
        self::assertStringContainsString("<p>alpha</p>", $this->_render("nested/one"));

        # Preserve the original timestamp across an equal-length edit
        $mtime = filemtime($dir."/_one.hbs");

        # Replace the contents with the same byte count
        file_put_contents($dir."/_one.hbs", "bravo");

        # Restore the original modification time
        touch($dir."/_one.hbs", $mtime);

        # Resolve the filename alias with fresh content
        self::assertStringContainsString("<p>bravo</p>", $this->_render("nested/_one"));

        # Change both the name and the supported extension
        rename($dir."/_one.hbs", $dir."/two.handlebars");

        # Discover the renamed partial
        self::assertStringContainsString("<p>bravo</p>", $this->_render("nested/two"));

        # Capture LightnCandy's expected missing-partial diagnostics in this isolated process
        ini_set("error_log", $this->_root."/missing.log");

        # The old name must disappear
        self::assertStringContainsString("<p></p>", $this->_render("nested/one"));

        # Remove the last partial
        unlink($dir."/two.handlebars");

        # The deleted name must disappear as well
        self::assertStringContainsString("<p></p>", $this->_render("nested/two"));

    }

    /** Refresh both the page and its surrounding layout across same-timestamp edits. */
    #[DataProvider("liveModes")]
    public function testLivePageAndLayoutEdits(string $mode, bool $watch):void {

        # Give the application its own copy of the layout
        $directory = $this->_root."/resources/Hbs/App";

        # Create the fixture layout directory
        mkdir($directory, 0755, true);

        # Read the normal document shell including its recursive partial
        $shell = file_get_contents(dirname(__DIR__, 3)."/resources/Hbs/App/index.hbs");

        # Select the fixture shell
        Env::set([
            "crazyphp_root"    =>  $this->_root
        ]);

        # Use one timestamp for the old and new sources
        $timestamp = time() - 60;

        # Warm the production renderer before starting development
        $this->_build("production", false);

        # Render the same paths and build identity before and after switching modes
        foreach(["first", "other", "third"] as $word){

            # Switch to the requested live mode after the first render
            if($word !== "first")

                # Keep the production hash and date while changing the mode
                $this->_build($mode, $watch);

            # Change the page and layout without changing their lengths
            file_put_contents($this->_root."/page.hbs", "<p>$word</p>");

            # Add a visible marker to the real shell
            file_put_contents($directory."/index.hbs", $shell."<!--$word-->");

            # Preserve the page timestamp
            touch($this->_root."/page.hbs", $timestamp);

            # Preserve the layout timestamp
            touch($directory."/index.hbs", $timestamp);

            # Execute both public template loaders through a fresh document
            $html = $this->_render("");

            # Verify the current page source
            self::assertStringContainsString("<p>$word</p>", $html);

            # Verify the current layout source
            self::assertStringContainsString("<!--$word-->", $html);

        }

    }

    /** Share the production snapshot across pages and emails until a build or cache clear. */
    public function testProductionBuildCache():void {

        # Select an identifiable production build
        $this->_build("production", false);

        # Create an initial production partial
        $dir = $this->_root."/assets/Hbs/partials";

        # Create the partial directory
        mkdir($dir, 0755, true);

        # Seed the production snapshot
        file_put_contents($dir."/card.hbs", "alpha");

        # Populate both caches through a full document render
        self::assertStringContainsString("<p>alpha</p>", $this->_render("card"));

        # Locate the executable versions of both the body and shell
        $pattern = (new Cache())->getFileCacheDirectory()."/handlebars-*.php";

        # Identify the two distinct programs created by the cold render
        $programs = glob($pattern);

        # Verify that the page and layout each use a persistent renderer
        self::assertCount(2, $programs);

        # Confirm PHP included both generated programs
        foreach($programs as $program)

            # Compare canonical paths with the PHP include registry
            self::assertContains(realpath($program), get_included_files());

        # Change the partial without publishing another production build
        file_put_contents($dir."/card.hbs", "bravo");

        # The warm page retains its compiled build
        self::assertStringContainsString("<p>alpha</p>", $this->_render("card"));

        # A warm render reuses the same immutable programs
        self::assertSame($programs, glob($pattern));

        # Give the email its own source and compilation key
        file_put_contents($this->_root."/mail.hbs", '<strong>{{> card}}</strong>');

        # Compile the email from the same production partial snapshot
        $mail = (new Structure())->setBodyEmailTemplate($this->_root."/mail.hbs", null, [], null, "mail")->prepare()->render();

        # Verify the independently compiled email used the cached partial
        self::assertStringContainsString("<strong>alpha</strong>", $mail);

        # Record another build even though the asset hash stayed the same
        $this->_build("production", false, "second");

        # The next build must discover the changed partial
        self::assertStringContainsString("<p>bravo</p>", $this->_render("card"));

        # Update the deployed source in place
        file_put_contents($dir."/card.hbs", "gamma");

        # Evict the map and compiled programs with the framework's normal cache operation
        self::assertTrue((new Cache())->clear());

        # Clearing the pool must also remove its executable programs
        self::assertSame([], glob($pattern));

        # Repopulate the build from disk
        self::assertStringContainsString("<p>gamma</p>", $this->_render("card"));

    }

    /**
     * Test Per-Page Initial State Policy
     *
     * Exercise the controller factory with the default and HTML-only policies.
     *
     * @return void
     */
    public function testPerPageInitialStatePolicy():void {

        # Exercise template caching and initial state storage in the same build
        $this->_build("production", false);

        # Prepare a template that visibly depends on the page data
        file_put_contents($this->_root."/page.hbs", "<p>{{title}}</p>");

        # Supply a page controller that opts out of initial state storage
        $htmlOnly = new class extends Controller {

            /** @var bool Render this fixture as a standalone HTML page */
            protected const CACHE_INITIAL_PAGE_STATE = false;

        };

        # Start with a browser that has no pending page state
        unset($_COOKIE[Page::COOKIE_UUID_STATE]);

        # Supply identical data to both page policies
        $data = [
            "title"    =>  "Report"
        ];

        # Render a standalone document through the controller entry point
        $html = $htmlOnly::Structure()
            ->setBodyTemplate($this->_root."/page.hbs", data: $data)
            ->prepare()
            ->render()
        ;

        # Verify the standalone page still renders its data
        self::assertStringContainsString("<p>Report</p>", $html);

        # A standalone render must not mint a browser state identifier
        self::assertArrayNotHasKey(Page::COOKIE_UUID_STATE, $_COOKIE);

        # Render the same document with the normal interactive policy
        $interactive = Controller::Structure()
            ->setBodyTemplate($this->_root."/page.hbs", data: $data)
            ->prepare()
            ->render()
        ;

        # Both policies must return identical document content
        self::assertSame($html, $interactive);

        # Capture the state identifier created by the interactive page
        $uuid = $_COOKIE[Page::COOKIE_UUID_STATE];

        # Locate the cache entry used by the next catch_state request
        $key = Page::getCacheKey($uuid);

        # Open the application's normal state cache
        $cache = new Cache();

        # Check the interactive payload behind its issued identifier
        self::assertSame($data, $cache->get($key));

        # Visit another standalone document before consuming that state
        $htmlOnly::Structure()
            ->setBodyTemplate($this->_root."/page.hbs", data: [])
            ->prepare()
            ->render()
        ;

        # Preserve the pending browser identifier
        self::assertSame($uuid, $_COOKIE[Page::COOKIE_UUID_STATE]);

        # Preserve the interactive payload for its original consumer
        self::assertSame($data, $cache->get($key));

    }

    /** Set the build configuration for the next render. */
    private function _build(string $mode, bool $watch, string $date = "first"):void {

        # Simulate the configuration already loaded for the request
        $GLOBALS[Config::PREFIX]["Front"] = [
            "Front"    =>  [
                "lastBuild"    =>  [
                    "watch"    =>  $watch,
                    "mode"     =>  $mode,
                    "hash"     =>  "abc123",
                    "date"     =>  $date
                ]
            ]
        ];

    }

    /** Render a fresh document using the shared parent template. */
    private function _render(string $name):string {

        # Render via the regular body and shell entry points
        $result = (new Structure())->setBodyTemplate($this->_root."/page.hbs", null, [
            "part"    =>  $name
        ], "page")->prepare()->render();

        # Return the complete rendered HTML
        return $result;

    }

}
