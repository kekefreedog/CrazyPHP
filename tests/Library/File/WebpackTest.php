<?php declare(strict_types=1);
/**
 * Webpack Build Selection Test
 *
 * Keep watch-mode scripts and metadata within one build when old assets remain.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependencies
 */
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\File\Webpack;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use ReflectionClass;

/**
 * Webpack Test
 *
 * Verify build discovery, registration and HTML script selection.
 */
class WebpackTest extends TestCase {

    /** Private Parameters
     ******************************************************
     */

    /** @var string $_root Temporary application root */
    private string $_root;

    /** @var array $_globals Original configuration and environment */
    private array $_globals = [];

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Preserve globals and isolate the fixture configuration
        foreach([Env::PREFIX, Config::PREFIX] as $key){

            $this->_globals[$key] = $GLOBALS[$key] ?? null;
            unset($GLOBALS[$key]);

        }

        # Create an application with old and new builds
        $this->_root = sys_get_temp_dir()."/crazy-webpack-".bin2hex(random_bytes(8));
        mkdir($this->_root."/public/dist/page/app", 0777, true);
        mkdir($this->_root."/config");
        file_put_contents($this->_root."/config/App.yml", "App:\n    public: public\n");
        file_put_contents($this->_root."/config/Front.yml", "Front:\n    lastBuild:\n        hash: aaaaaaaa\n        watch: true\n        files: []\n        pages: []\n");
        Env::set(["app_root" => $this->_root, "phpunit_test" => true]);

        # Use explicit modification times independent of filesystem enumeration
        foreach(["aaaaaaaa" => 100, "bbbbbbbb" => 200] as $hash => $modified){

            foreach(["runtime", "vendors", "index", "page/app/Project"] as $name){

                $path = $this->_root."/public/dist/$name.$hash.js";
                file_put_contents($path, "// Fixture");
                touch($path, $modified);

            }

        }

        # Include a newer lazy chunk that must not determine the entrypoint hash
        file_put_contents($this->_root."/public/dist/lazy.cccccccc.js", "// Lazy fixture");
        touch($this->_root."/public/dist/lazy.cccccccc.js", 300);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove only the temporary application fixture
        File::remove($this->_root);

        # Restore the previous environment and configuration
        foreach($this->_globals as $key => $value){

            if($value === null)
                unset($GLOBALS[$key]);
            else
                $GLOBALS[$key] = $value;

        }

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Latest Build Registration
     *
     * @return void
     */
    public function testLatestBuildRegistration():void {

        # Discover the newest entrypoint rather than an old or lazy chunk
        $this->assertSame("bbbbbbbb", Webpack::getHash());

        # Assert registration includes only existing assets from that build
        $this->assertSame(["runtime.bbbbbbbb.js", "vendors.bbbbbbbb.js", "index.bbbbbbbb.js"], Config::getValue("Front.lastBuild.files"));
        $this->assertSame(["Project.bbbbbbbb.js"], Config::getValue("Front.lastBuild.pages"));
        $this->assertSame("bbbbbbbb", Config::getValue("Front.lastBuild.hash"));

    }

    /**
     * Test Watch Document Uses One Build
     *
     * @return void
     */
    public function testWatchDocumentUsesOneBuild():void {

        # Inspect script generation without initializing unrelated page caches
        $reflection = new ReflectionClass(Structure::class);
        $structure = $reflection->newInstanceWithoutConstructor();
        $structure->setJsScripts("Project");
        $response = $reflection->getProperty("response")->getValue($structure);

        # Collect generated script URLs from the HTML structure
        $scripts = [];
        array_walk_recursive($response, static function($value, $key) use (&$scripts):void {

            if($key === "src")
                $scripts[] = $value;

        });

        # Assert the runtime, shared bundle, entrypoint and page belong together
        $this->assertSame([
            "/dist/runtime.bbbbbbbb.js",
            "/dist/vendors.bbbbbbbb.js",
            "/dist/index.bbbbbbbb.js",
            "/dist/page/app/Project.bbbbbbbb.js",
        ], $scripts);

        # Keep document metadata pinned even if another build arrives mid-render
        file_put_contents($this->_root."/public/dist/index.dddddddd.js", "// Later build");
        $html = '<meta name="application-hash" content="aaaaaaaa">';
        $reflection->getMethod("_setHash")->invokeArgs($structure, [&$html]);
        $this->assertSame('<meta name="application-hash" content="bbbbbbbb">', $html);

    }

}
