<?php declare(strict_types=1);
/**
 * Router Cache Test
 *
 * Exercise named routes and middleware dispatch against an isolated file cache.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependances
 */
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use CrazyPHP\Library\Router\Router as LibraryRouter;
use CrazyPHP\Library\Router\Adapter\FastRoute;
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Core\Router as CoreRouter;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Router Cache Test
 *
 * Use fresh processes to isolate configuration globals and cache driver instances.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class RouterCacheTest extends TestCase {

    /** Private Parameters
     ******************************************************
     */

    /** @var string $_root Disposable application directory */
    private string $_root;

    /** Test Environment
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Choose an isolated application root
        $this->_root = sys_get_temp_dir()."/crazy-router-".bin2hex(random_bytes(8));

        # Create the custom configuration directory
        mkdir($this->_root."/settings", 0700, true);

        # Select the real file cache and fixture configuration
        Env::set([
            "app_root"           =>  $this->_root,
            "crazyphp_root"      =>  dirname(__DIR__, 2),
            "config_location"    =>  $this->_root."/settings",
            "cache_driver"       =>  "Files"
        ]);

        # Provide a request method to the routing adapter
        $_SERVER["REQUEST_METHOD"] = "GET";

        # Configure one app route and enable the API route group
        file_put_contents($this->_root."/settings/Router.yml", <<<'YAML'
Router:
  app:
    - name: Page
      patterns: [/page]
      middleware: [first, second]
  api:
    - name: ApiBase
YAML
        );

        # Declare an API route with the reverse middleware order
        file_put_contents($this->_root."/settings/Api.yml", <<<'YAML'
Api:
  v2:
    enable: true
    prefix: v2
    routers:
      - name: Item
        patterns: [/item]
        middleware: [second, first]
YAML
        );

        # Configure callable middleware names for real discovery
        file_put_contents($this->_root."/settings/Middleware.yml", <<<'YAML'
Middleware:
  - name: first
    script: 'Tests\Core\RouterCacheTest::first'
  - name: second
    script: 'Tests\Core\RouterCacheTest::second'
YAML
        );

        # Disable watch mode for the core middleware
        file_put_contents($this->_root."/settings/Front.yml", "Front: {lastBuild: {watch: false}}");

        # Give all original fixtures settled timestamps before priming the cache
        foreach(glob($this->_root."/settings/*.yml") as $source)

            # Set an earlier modification second
            touch($source, time() - 60);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove only this test's disposable application
        File::removeWithin(dirname($this->_root), basename($this->_root));

    }

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Named Route Cache
     *
     * @return void
     */
    public function testNamedRouteCache():void {

        # Warm the collection from both configuration files
        self::assertSame(
            [
                "/page"
            ],
            LibraryRouter::getByName("Page")["patterns"]
        );

        # Change the request-local configuration without modifying its source
        $GLOBALS[Config::PREFIX]["Router"]["Router"]["app"][0]["patterns"] = ["/uncached"];

        # Verify the stored collection is reused
        self::assertSame(
            [
                "/page"
            ],
            LibraryRouter::getByName("Page")["patterns"]
        );

        # Change the route source independently
        $this->_replaceConfig("Router", "/page", "/changed-page");

        # Verify a fresh lookup reads the changed route
        self::assertSame(
            [
                "/changed-page"
            ],
            LibraryRouter::getByName("Page")["patterns"]
        );

        # Settle the router timestamp before testing the independent API dependency
        touch($this->_root."/settings/Router.yml", time() - 60);

        # Change only the API configuration
        $this->_replaceConfig("Api", "/item", "/changed-item");

        # Verify API metadata is rebuilt
        self::assertSame(
            [
                "/changed-item"
            ],
            LibraryRouter::getByName("Item")["patterns"]
        );

    }

    /**
     * Test Middleware Cache
     *
     * @return void
     */
    public function testMiddlewareCache():void {

        # Dispatch through freshly discovered middleware
        $this->_assertDispatch("12", "21");

        # Hide declarations from discovery without invalidating the stored descriptors
        $GLOBALS[Config::PREFIX]["Middleware"] = [
            "Middleware"    =>  []
        ];

        # Verify a new router registers the cached callbacks in order
        $this->_assertDispatch("12", "21");

        # Reassign the second middleware and invalidate its configuration
        $this->_replaceConfig("Middleware", "::second", "::first");

        # Verify both routes use the updated declaration
        $this->_assertDispatch("11", "11");

    }

    /** Public Static Methods | Middleware
     ******************************************************
     */

    /**
     * Apply First Middleware
     *
     * @param ServerRequestInterface $request Incoming request
     * @return ServerRequestInterface
     */
    public static function first(ServerRequestInterface $request):ServerRequestInterface {

        # Append the first marker to the execution trace
        $result = $request->withAttribute("trace", $request->getAttribute("trace", "")."1");

        # Return result
        return $result;

    }

    /**
     * Apply Second Middleware
     *
     * @param ServerRequestInterface $request Incoming request
     * @return ServerRequestInterface
     */
    public static function second(ServerRequestInterface $request):ServerRequestInterface {

        # Append the second marker to the execution trace
        $result = $request->withAttribute("trace", $request->getAttribute("trace", "")."2");

        # Return result
        return $result;

    }

    /** Private Methods | Fixtures
     ******************************************************
     */

    /**
     * Replace Configuration
     *
     * @param string $name Configuration name
     * @param string $before Original text
     * @param string $after Replacement text
     * @return void
     */
    private function _replaceConfig(string $name, string $before, string $after):void {

        # Locate the fixture
        $path = $this->_root."/settings/$name.yml";

        # Update its contents
        file_put_contents($path, str_replace($before, $after, file_get_contents($path)));

        # Exercise an edit in the current second, even immediately after a cache write
        touch($path);

        # Refresh filesystem metadata
        clearstatcache();

        # Simulate fresh configuration globals on the next request
        unset($GLOBALS[Config::PREFIX][$name]);

    }

    /**
     * Assert Dispatch
     *
     * @param string $app Expected application middleware trace
     * @param string $api Expected API middleware trace
     * @return void
     */
    private function _assertDispatch(string $app, string $api):void {

        # Create a fresh routing adapter for each request cycle
        $router = new CoreRouter(new FastRoute());

        # Read the final request produced by the middleware chain
        $controller = static fn(ServerRequestInterface $request):string => $request->getAttribute("trace", "");

        # Register the app route
        $router->addRoute("/page", $controller);

        # Register the API route
        $router->addRoute("/api/v2/item", $controller);

        # Install the discovered or cached middleware
        $router->pushMiddlewares();

        # Check the application execution order
        self::assertSame($app, $router->callRoute("/page"));

        # Check the API execution order
        self::assertSame($api, $router->callRoute("/api/v2/item"));

    }

}
