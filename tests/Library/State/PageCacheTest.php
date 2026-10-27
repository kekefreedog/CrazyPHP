<?php declare(strict_types=1);
/**
 * Page Cache Test
 *
 * Verify initial HTML state cannot be reused by another route.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace Tests\Library\State;

/**
 * Dependances
 */
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\State\Page;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Context;
use ReflectionClass;

/**
 * Page Cache Test
 *
 * Cover cache isolation and HTML writer compatibility.
 */
class PageCacheTest extends TestCase {

    /** @var mixed $_context Previous request context */
    private mixed $_context;

    /**
     * Preserve the surrounding request context.
     */
    protected function setUp():void {

        # Save the request context
        $this->_context = $GLOBALS[Context::PREFIX] ?? null;

    }

    /**
     * Restore the surrounding request context.
     */
    protected function tearDown():void {

        # Restore the previous request context
        if($this->_context === null){

            # Remove the fixture context
            unset($GLOBALS[Context::PREFIX]);

        }else{

            # Restore the saved context
            $GLOBALS[Context::PREFIX] = $this->_context;

        }

    }

    /**
     * A shared browser cookie must not identify two different pages.
     */
    public function testCacheIsScopedToTheRequestedRoute():void {

        # Prepare the Index route
        Context::set(["name" => "Index", "route" => "", "parameters" => []], "routes.current");

        # Capture the initial page cache key
        $indexKey = Page::getCacheKey("browser-state");

        # Prepare the Home route
        Context::set(["name" => "Home", "route" => "en", "parameters" => ["language" => "en"]], "routes.current");

        # Capture the Home state key
        $homeKey = Page::getCacheKey("browser-state");

        # Prepare the Login route
        Context::set(["name" => "Login", "route" => "en/login", "parameters" => ["language" => "en"]], "routes.current");

        # Capture the Login state key
        $loginKey = Page::getCacheKey("browser-state");

        # Reject cached Index data for Home
        self::assertNotSame($indexKey, $homeKey);

        # Reject cached Login data for Home
        self::assertNotSame($loginKey, $homeKey);

        # Keep legacy unscoped entries out of the new cache
        self::assertNotSame("browser-state", $homeKey);

    }

    /**
     * Route arguments must identify the requested page while transport headers do not.
     */
    public function testCacheSeparatesRouteArgumentsAndIgnoresTransportHeaders():void {

        # Prepare the first project route
        Context::set(["name" => "Project", "route" => "en/project/1", "parameters" => ["project" => "1"], "headers" => ["accept" => "text/html"]], "routes.current");

        # Capture the HTML state key
        $htmlKey = Page::getCacheKey("browser-state");

        # Change the state request headers
        Context::set(["accept" => "application/json"], "routes.current.headers");

        # Preserve the state key for the corresponding JSON request
        self::assertSame($htmlKey, Page::getCacheKey("browser-state"));

        # Prepare a different project route
        Context::set(["name" => "Project", "route" => "en/project/2", "parameters" => ["project" => "2"]], "routes.current");

        # Reject state belonging to another project
        self::assertNotSame($htmlKey, Page::getCacheKey("browser-state"));

        # Keep independent browser state identifiers separate
        self::assertNotSame(Page::getCacheKey("first-state"), Page::getCacheKey("second-state"));

    }

    /**
     * HTML rendering must write the same scoped key used by state retrieval.
     */
    public function testHtmlWriterStoresStateUnderTheRouteKey():void {

        # Prepare the rendered route
        Context::set(["name" => "Login", "route" => "en/login", "parameters" => ["language" => "en"]], "routes.current");

        # Resolve the reader's cache key
        $cacheKey = Page::getCacheKey("browser-state");

        # Prepare rendered page data
        $data = ["_ui" => ["title" => "Login"]];

        # Create an isolated cache adapter
        $cache = $this->createMock(Cache::class);

        # Expect deletion of only the scoped entry
        $cache->expects(self::once())->method("delete")->with($cacheKey)->willReturn(true);

        # Expect storage under the reader's key
        $cache->expects(self::once())->method("set")->with($cacheKey, $data)->willReturn(true);

        # Inspect the HTML writer without application configuration
        $reflection = new ReflectionClass(Structure::class);

        # Create the isolated HTML writer
        $structure = $reflection->newInstanceWithoutConstructor();

        # Install its browser state identifier
        $reflection->getProperty("_uuid")->setValue($structure, "browser-state");

        # Install the isolated cache
        $reflection->getProperty("_cache")->setValue($structure, $cache);

        # Cache the rendered state
        $reflection->getMethod("_cacheData")->invoke($structure, $data);

        # Verify the writer records successful caching
        self::assertTrue($reflection->getProperty("_is_cached")->getValue($structure));

    }

}
