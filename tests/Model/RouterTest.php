<?php declare(strict_types=1);
/**
 * Test Php Unit
 *
 * Test Php Unit
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Router as LibraryRouter;
use CrazyPHP\Library\Router\Adapter\LeagueRoute;
use CrazyPHP\Library\Router\Adapter\FastRoute;
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Library\Router\Adapter\Symfony;
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Library\Router\Adapter\Mezon;
use PHPUnit\Framework\Attributes\Depends;
use CrazyPHP\Core\Router as CoreRouter;
use CrazyPHP\Interface\CrazyRouterType;
use CrazyPHP\Interface\CrazyRouter;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Model\Router\Delete;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\ServerRequest;
use InvalidArgumentException;
use ReflectionProperty;
use CrazyPHP\Model\Env;

/**
 * Router test
 *
 * Methods for test interactions with model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class RouterTest extends TestCase {

    /** Public attributes
     ******************************************************
     */

    /** @var Create $createSimple */
    public Create $createSimple;
    public Create $createAdvance;

    /** @var Delete $deleteSimple */
    public Delete $deleteSimple;
    public Delete $deleteAdvance;

    /** @var array $_server Server environment restored after each test */
    private array $_server;

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Preserve the method read by router adapters
        $this->_server = $_SERVER;
        $_SERVER["REQUEST_METHOD"] = "GET";

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Restore the original request environment
        $_SERVER = $this->_server;

    }

    /**
     * Set Up Before Class
     * 
     * This method is called before the first test of this test class is run.
     * 
     * @return void
     */
    public static function setUpBeforeClass():void {

        # Setup env
        Env::set([
            "phpunit_test"              =>  true,
            "crazyphp_root"             =>  getcwd(),
            "app_root"                  =>  getcwd()."/tests/.cache/router",
            "config_location"           =>  "@crazyphp_root/resources/Yml",
            "trash_disable"             =>  true,
        ]);

        # Check app root
        if(!File::exists("@app_root")){

            # Create folder
            File::createDirectory("@app_root/config");

        }

        # Copy router config
        File::copy("@crazyphp_root/resources/Yml/Router.yml", "@app_root/config/Router.yml");

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {
        
        # Remove all file in app root
        File::removeAll("@app_root");

        # Remove file of app root
        File::remove("@app_root");

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    public function testCreateRouterSimple():void {

        # Set input
        $input = [
            "router"    =>  [
                [
                    "name"          =>  "type",
                    "description"   =>  "Type of your crazy router",
                    "type"          =>  "VARCHAR",
                    "default"       =>  "app",
                    "select"        =>  [
                        "app"   =>  "App",
                        "api"   =>  "Api",
                        "asset" =>  "Asset",
                    ],
                    "value"         =>  "app"
                ],
                [
                    "name"          =>  "name",
                    "description"   =>  "Name of your crazy router",
                    "type"          =>  "VARCHAR",
                    "default"       =>  "router",
                    "required"      =>  1,
                    "process"       => [
                        "cleanPath",
                        "snakeToCamel",
                        "ucfirst",
                        "trim"
                    ],
                    "value"         =>  "Router"
                ],
                [
                    "name"          =>  "methods",
                    "description"   =>  "Methods allowed by your crazy router",
                    "type"          =>  "ARRAY",
                    "default"       =>  "get",
                    "required"      =>  1,
                    "multiple"      =>  1,
                    "select"        =>  [
                        "get"           =>  "Get",
                        "post"          =>  "Post",
                        "put"           =>  "Put",
                        "delete"        =>  "Delete",
                        "option"        =>  "Option",
                        "patch"         =>  "Patch"
                    ],
                    "value"         =>  [
                        "get"
                    ]
                ],
                [
                    "name"          =>  "prefix",
                    "description"   =>  "Prefix of your crazy router",
                    "type"          =>  "STRING",
                    "default"       =>  "",
                    "value"         =>  ""
                ]
            ]
        ];

        # Prepare create
        $this->createSimple = new Create($input);
        
        # Run create
        $this->createSimple->run();

        ## Asserts

        # Check router config
        $this->assertTrue($this->checkRouterConfig($input));

        # Check index ts
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/index.ts"));

        # Check style
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/style.scss"));
        
        # Check template
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/template.hbs"));
        
        # Check controller
        $this->assertFileIsReadable(File::path("@app_root/app/Controller/App/Router.php"));

    }
    
    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    #[Depends('testCreateRouterSimple')]
    public function testCreateRouterComplex():void {

        # Set input
        $input = [
            "router" => [
                [
                    "name" => "type",
                    "description" => "Type of your crazy router",
                    "type" => "VARCHAR",
                    "default" => "app",
                    "select" => [
                        "app" => "App",
                        "api" => "Api",
                        "asset" => "Asset"
                    ],
                    "value" => "app"
                ],
                [
                    "name" => "name",
                    "description" => "Name of your crazy router",
                    "type" => "VARCHAR",
                    "default" => "router",
                    "required" => true,
                    "process" => [
                        "cleanPath",
                        "snakeToCamel",
                        "ucfirst",
                        "trim"
                    ],
                    "value" => "Very.Complex.Page"
                ],
                [
                    "name" => "methods",
                    "description" => "Methods allowed by your crazy router",
                    "type" => "ARRAY",
                    "default" => "get",
                    "required" => true,
                    "multiple" => true,
                    "select" => [
                        "get" => "Get",
                        "post" => "Post",
                        "put" => "Put",
                        "delete" => "Delete",
                        "option" => "Option",
                        "patch" => "Patch"
                    ],
                    "value" => [
                        "get"
                    ]
                ],
                [
                    "name" => "prefix",
                    "description" => "Prefix of your crazy router",
                    "type" => "STRING",
                    "default" => false,
                    "value" => ""
                ]
            ]
        ];

        # Prepare create
        $this->createSimple = new Create($input);
        
        # Run create
        $this->createSimple->run();

        ## Asserts

        # Check router config
        $this->assertTrue($this->checkRouterConfig($input));

        # Check index ts
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/VeryComplexPage/index.ts"));

        # Check style
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/VeryComplexPage/style.scss"));
        
        # Check template
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/VeryComplexPage/template.hbs"));
        
        # Check controller
        $this->assertFileIsReadable(File::path("@app_root/app/Controller/App/VeryComplexPage.php"));

    }

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    #[Depends('testCreateRouterSimple')]
    public function testDeleteRouterSimple():void {

        # Set input
        $input = [
            "routers"   =>  [
                [
                    "name"          =>  "routers",
                    "description"   =>  "Routers to delete",
                    "type"          =>  "ARRAY",
                    "required"      =>  1,
                    "multiple"      =>  1,
                    "select"        =>  [
                        "app.Index"     =>  "(App) Index",
                        "app.Home"      =>  "(App) Home",
                        "app.Router"    =>  "(App) Router",
                        "api.Config"    =>  "(Api) Config",
                        "asset.Favicon" =>  "(Asset) Favicon",
                        "asset.Manifest"=>  "(Asset) Manifest"
                    ],
                    "value"         =>  [
                        "app.Router"
                    ]
                ]
            ]
        ];

        # Prepare delete
        $this->deleteSimple = new Delete($input);
        
        # Run delete
        $this->deleteSimple->run();

        ## Asserts

        # Check router config
        $this->assertTrue($this->checkRouterConfigIsMissing($input));

        # Check index ts
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/Router/index.ts"));

        # Check style
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/Router/style.scss"));
        
        # Check template
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/Router/template.hbs"));
        
        # Check controller
        $this->assertFileDoesNotExist(File::path("@app_root/app/Controller/App/Router.php"));

    }

    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    #[Depends('testCreateRouterComplex')]
    public function testDeleteRouterComplex():void {

        # Set input
        $input = [
            "routers"   =>  [
                [
                    "name"          =>  "routers",
                    "description"   =>  "Routers to delete",
                    "type"          =>  "ARRAY",
                    "required"      =>  1,
                    "multiple"      =>  1,
                    "select"        =>  [
                        "app.Index"     =>  "(App) Index",
                        "app.Home"      =>  "(App) Home",
                        "app.Router"    =>  "(App) Router",
                        "api.Config"    =>  "(Api) Config",
                        "asset.Favicon" =>  "(Asset) Favicon",
                        "asset.Manifest"=>  "(Asset) Manifest"
                    ],
                    "value"         =>  [
                        "app.VeryComplexPage"
                    ]
                ]
            ]
        ];

        # Prepare delete
        $this->deleteSimple = new Delete($input);
        
        # Run delete
        $this->deleteSimple->run();

        ## Asserts

        # Check router config
        $this->assertTrue($this->checkRouterConfigIsMissing($input));

        # Check index ts
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/VeryComplexPage/index.ts"));

        # Check style
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/VeryComplexPage/style.scss"));
        
        # Check template
        $this->assertFileDoesNotExist(File::path("@app_root/app/Environment/Page/VeryComplexPage/template.hbs"));
        
        # Check controller
        $this->assertFileDoesNotExist(File::path("@app_root/app/Controller/App/VeryComplexPage.php"));
        
    }

    /**
     * Router Adapters
     *
     * @return array Engine implementations sharing the existing application contract
     */
    public static function routerAdapters():array {

        return [
            "Mezon" => [Mezon::class],
            "FastRoute" => [FastRoute::class],
            "LeagueRoute" => [LeagueRoute::class],
            "Symfony" => [Symfony::class],
        ];

    }

    /** Public Methods | Router Adapters
     ******************************************************
     */

    /**
     * Test Default Adapter Dispatch
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testDefaultAdapterDispatch(string $adapterClass):void {

        # Register static and parameterized routes through the core facade
        $router = new CoreRouter(new $adapterClass());
        $callback = static fn(string $route, array $parameters):array => $parameters;
        $router->addRoute("/health", static fn():string => "healthy", "GET", "health");
        $router->addRoute("/projects/[i:id]", $callback, ["GET", "POST"], "project");

        # Assert the facade exposes the framework contract and preserves named routes
        $this->assertInstanceOf(CrazyRouter::class, $router);
        $this->assertSame("healthy", $router->callRoute("/health"));
        $this->assertSame($callback, $router->getCallback("/projects/42"));
        $this->assertSame(["id" => "42"], $router->callRoute("/projects/42"));
        $this->assertSame("42", $router->getParam("id"));
        $this->assertTrue($router->hasParam("id"));
        $this->assertFalse($router->hasParam("missing"));
        $this->assertTrue($router->routeExists("/projects/[i:id]"));
        $this->assertSame("projects/[i:id]", $router->getRouteByName("project"));
        $this->assertSame("projects/[i:id]", $router->getCalledRoute());
        $this->assertSame("projects/42", $router->reverse("project", ["id" => 42]));
        $this->assertSame("health", $router->reverse("health"));

        # Assert the same route can be dispatched for another configured method
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->assertSame(["id" => "73"], $router->callRoute("/projects/73"));

        # Assert a custom missing-route handler receives unmatched requests
        $missing = [];
        $router->setNoProcessorFoundErrorHandler(static function(string $route) use (&$missing):void {

            $missing[] = $route;

        });
        $this->assertNull($router->callRoute("/health"));
        $this->assertSame(["health"], $missing);

        # Assert clearing the adapter removes its routes
        $router->clear();
        $this->assertFalse($router->routeExists("/health"));

    }

    /**
     * Test Adapter Middleware Dispatch
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testAdapterMiddlewareDispatch(string $adapterClass):void {

        # Register a controller that receives the request prepared by middleware
        $router = new CoreRouter(new $adapterClass());
        $events = [];
        $router->addRoute("/projects/[i:id]", static function(ServerRequestInterface $request) use (&$events):string {

            $events[] = "controller";
            return $request->getAttribute("id");

        });

        # Convert the vendor route arguments to CrazyPHP's PSR-7 request
        $router->registerMiddleware("*", static function(string $route, string $id) use (&$events):ServerRequestInterface {

            $events[] = "global";
            return (new ServerRequest("GET", "/".$route))->withAttribute("id", $id);

        });

        # Apply route-specific middleware after the global middleware
        $router->registerMiddleware("/projects/[i:id]", static function(ServerRequestInterface $request) use (&$events):ServerRequestInterface {

            $events[] = "route";
            return $request;

        });

        # Assert middleware order and the controller's result are preserved
        $this->assertSame("42", $router->callRoute("/projects/42"));
        $this->assertSame(["global", "route", "controller"], $events);

    }

    /**
     * Test Custom Router Types
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testCustomRouterTypes(string $adapterClass):void {

        # Register a type whose constructor cannot be called
        $router = new CoreRouter(new $adapterClass());
        $router->addType("slug", RouterTestParameterType::class);
        $router->addRoute("/projects/[slug:name]", static fn(string $route, array $parameters):array => $parameters, "GET", "project");
        $router->addRoute("/projects/[il:ids]/items", static fn():string => "items", "GET", "items");

        # Assert multi-character types match and generate URLs
        $this->assertSame(["name" => "project-one"], $router->callRoute("/projects/project-one"));
        $this->assertSame("projects/project-one", $router->reverse("project", ["name" => "project-one"]));
        $this->assertSame("projects/release$1", $router->reverse("project", ["name" => "release$1"]));
        $this->assertSame("projects/1,2/items", $router->reverse("items", ["ids" => "1,2"]));
        $this->assertSame(CrazyRouterType::PARAMETER_NAME_REGEX, CoreRouter::PARAMETER_NAME_REGEX);

    }

    /**
     * Test Invalid Router Type
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testInvalidRouterType(string $adapterClass):void {

        # Require the custom parameter type contract at the adapter boundary
        $router = new $adapterClass();
        $this->expectException(InvalidArgumentException::class);
        $router->addType("invalid", self::class);

    }

    /**
     * Test Injected Router Adapter
     *
     * @return void
     */
    public function testInjectedRouterAdapter():void {

        # Supply a framework contract implementation with no Mezon inheritance
        $adapter = $this->createMock(CrazyRouter::class);
        $callback = static fn():string => "handled";
        $adapter->expects($this->once())->method("addRoute")->with("/example", $callback, "GET", "example");
        $adapter->expects($this->once())->method("callRoute")->with("/example")->willReturn("handled");
        $adapter->expects($this->once())->method("reverse")->with("example", [])->willReturn("example");
        $adapter->expects($this->once())->method("addType")->with("slug", RouterTestParameterType::class);
        $router = new CoreRouter($adapter);

        # Assert registration, dispatch, URL generation and custom types use the adapter
        $router->addRoute("/example", $callback, "GET", "example");
        $router->addType("slug", RouterTestParameterType::class);
        $this->assertSame("handled", $router->callRoute("/example"));
        $this->assertSame("example", $router->reverse("example"));

    }

    /**
     * Test Configured Router Vendor
     *
     * @return void
     */
    public function testConfiguredRouterVendor():void {

        # Preserve cached configuration for subsequent collection tests
        $configuration = $GLOBALS[Config::PREFIX] ?? [];

        try{

            # Exercise each configured vendor and the legacy default without a vendor
            foreach(["mezon" => Mezon::class, "fast-route" => FastRoute::class, "league" => LeagueRoute::class, "symfony" => Symfony::class, "" => Mezon::class] as $vendor => $adapterClass){

                # Supply only router configuration, without application metadata
                $GLOBALS[Config::PREFIX] = ["Router" => ["Router" => $vendor === "" ? [] : ["vendor" => $vendor]]];

                # Resolve the implementation used by the normal framework entrypoint
                $router = new CoreRouter();

                # Verify selection rather than relying on behavior shared by every adapter
                $this->assertInstanceOf($adapterClass, (new ReflectionProperty(CoreRouter::class, "_adapter"))->getValue($router));

            }

            # An explicit implementation takes priority even over invalid configuration
            $GLOBALS[Config::PREFIX] = ["Router" => ["Router" => ["vendor" => "unknown"]]];

            # Supply a valid implementation directly
            $adapter = new Mezon();

            # Keep injection independent of configuration lookup
            $this->assertSame($adapter, (new ReflectionProperty(CoreRouter::class, "_adapter"))->getValue(new CoreRouter($adapter)));

            # Reject an unknown vendor when automatic selection is requested
            $this->expectExceptionMessage("Unknown router vendor: unknown");

            # Trigger configuration validation
            new CoreRouter();

        }finally{

            # Restore the original configuration even after an expected failure
            $GLOBALS[Config::PREFIX] = $configuration;

        }

    }

    /**
     * Test Adapter Cache Round Trip
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testAdapterCacheRoundTrip(string $adapterClass):void {

        # Use an isolated compiled route cache with a serializable callback
        $path = tempnam(sys_get_temp_dir(), "crazyphp-router-");
        $this->assertNotFalse($path);

        try{

            # Compile the custom parameter route with the selected adapter
            $router = new CoreRouter(new $adapterClass());
            $router->addType("slug", RouterTestParameterType::class);
            $router->addRoute("/projects/[slug:name]", [RouterTestParameterType::class, "handle"], "GET", "project");
            $router->warmCache();
            $router->dumpOnDisk($path);

            # Loading compiled data must not parse custom types again
            RouterTestParameterType::$parserCalls = 0;

            # Load the same routes in a fresh adapter
            $restored = new CoreRouter(new $adapterClass());
            $restored->addType("slug", RouterTestParameterType::class);
            $restored->loadFromDisk($path);

            # Assert matching, dispatch and URL generation survive serialization
            $this->assertSame(["name" => "project-one"], $restored->callRoute("/projects/project-one"));
            $this->assertSame("projects/project-one", $restored->reverse("project", ["name" => "project-one"]));
            $this->assertSame(0, RouterTestParameterType::$parserCalls);

            # Runtime middleware can be registered after cache restoration
            $restored->registerMiddleware("*", static fn(string $route, string $name):array => [$route, ["name" => strtoupper($name)]]);
            $this->assertSame(["name" => "PROJECT-ONE"], $restored->callRoute("/projects/project-one"));

            # New registrations invalidate loaded tables and are included in the next dump
            $restored->addRoute("/projects/[slug:name]/[i:id]", [RouterTestParameterType::class, "handle"], "GET", "detail");
            $this->assertSame([RouterTestParameterType::class, "handle"], $restored->getCallback("/projects/project-one/42"));
            $this->assertSame("42", $restored->getParam("id"));
            $this->assertSame(["name" => "PROJECT-ONE"], $restored->callRoute("/projects/project-one"));
            $restored->addRoute("/archive/[slug:name]", [RouterTestParameterType::class, "handle"], "POST", "archive");
            $_SERVER["REQUEST_METHOD"] = "POST";
            $this->assertSame(["name" => "PROJECT-TWO"], $restored->callRoute("/archive/project-two"));
            $this->assertGreaterThan(0, RouterTestParameterType::$parserCalls);
            $restored->dumpOnDisk($path);
            $reloaded = new CoreRouter(new $adapterClass());
            $reloaded->addType("slug", RouterTestParameterType::class);
            $reloaded->loadFromDisk($path);
            $this->assertSame(["name" => "project-two"], $reloaded->callRoute("/archive/project-two"));
            $this->assertSame("archive/project-two", $reloaded->reverse("archive", ["name" => "project-two"]));

        }finally{

            # Remove the isolated cache file
            unlink($path);

        }

    }

    /**
     * Test Existing Router Yaml
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testExistingRouterYaml(string $adapterClass):void {

        # Read the existing application route schema without modifying it
        $path = File::path("@app_root/config/Router.yml");
        $original = file_get_contents($path);
        $collection = File::open($path);
        $routes = LibraryRouter::parseCollection($collection);
        $router = new CoreRouter(new $adapterClass());

        # Register the parsed application, API and asset routes
        foreach(LibraryRouter::GROUPS as $group)
            foreach($routes[$group] ?? [] as $route)
                $router->addRoute($route["pattern"], $route["controller"], $route["method"], $route["name"]);

        # Assert existing typed paths still resolve to their configured controller
        $this->assertSame("App\\Controller\\Api\\v1\\Config::get", $router->getCallback("/api/v1/config/Router/name"));
        $this->assertSame("Router", $router->getParam("name"));
        $this->assertSame("name", $router->getParam("parameter"));
        $this->assertSame("api/v1/config/Router/name", $router->reverse("Config", ["name" => "Router", "parameter" => "name"]));
        $this->assertSame($original, file_get_contents($path));

    }

    /**
     * Test Collection Cache With Custom Types
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testCollectionCacheWithCustomTypes(string $adapterClass):void {

        # Preserve the loaded config and leave the application YAML untouched
        $original = $GLOBALS[Config::PREFIX] ?? [];
        $path = File::path("@app_root/config/Router.yml");
        $yaml = file_get_contents($path);
        $collection = File::open($path);
        $collection["Router"]["type"] = [["name" => "slug", "class" => RouterTestParameterType::class]];
        $collection["Router"]["app"][] = [
            "name"       => "ProjectAdapterTest",
            "controller" => RouterTestParameterType::class,
            "patterns"   => ["/projects/[slug:name]"],
            "methods"    => ["get"],
            "prefix"     => false,
        ];

        # Capture compiled route data in memory while exercising real adapter serialization
        $compiled = [];
        $cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["hasUpToDate", "get", "set"])
            ->getMock()
        ;
        $cache->method("hasUpToDate")->willReturnCallback(static function(string $key) use (&$compiled):bool {

            return isset($compiled[$key]);

        });
        $cache->method("get")->willReturnCallback(static function(string $key) use (&$compiled):mixed {

            return $compiled[$key] ?? null;

        });
        $cache->expects($this->once())->method("set")->willReturnCallback(static function(string $key, mixed $value) use (&$compiled):bool {

            $compiled[$key] = $value;
            return true;

        });

        try{

            # Load the unchanged config schema with a custom type from global config
            $GLOBALS[Config::PREFIX]["Router"] = $collection;
            $router = new CoreRouter(new $adapterClass());
            $router->cache = $cache;
            $router->pushCollection();
            $this->assertSame("projects/project-one", $router->reverse("ProjectAdapterTest", ["name" => "project-one"]));

            # Reload cached routes in a new adapter
            $restored = new CoreRouter(new $adapterClass());
            $restored->cache = $cache;
            $restored->pushCollection();
            $this->assertSame(RouterTestParameterType::class."::get", $restored->getCallback("/projects/project-one"));
            $this->assertSame("project-one", $restored->getParam("name"));
            $this->assertSame("projects/project-one", $restored->reverse("ProjectAdapterTest", ["name" => "project-one"]));
            $this->assertCount(1, $compiled);
            $this->assertSame($yaml, file_get_contents($path));

        }finally{

            # Restore config for the remaining suite
            $GLOBALS[Config::PREFIX] = $original;

        }

    }

    /**
     * Test Cached Collection Registers Types Before Loading
     *
     * @return void
     */
    public function testCachedCollectionRegistersTypesBeforeLoading():void {

        # Preserve config while supplying the existing Router.type structure
        $original = $GLOBALS[Config::PREFIX] ?? [];
        $adapter = $this->createMock(CrazyRouter::class);
        $registered = false;
        $adapter->expects($this->once())->method("addType")
            ->with("slug", RouterTestParameterType::class)
            ->willReturnCallback(static function() use (&$registered):void {

                $registered = true;

            })
        ;
        $adapter->expects($this->once())->method("loadFromDisk")
            ->willReturnCallback(function(string $path) use (&$registered):void {

                # Assert a future adapter receives custom types before its cached routes
                $this->assertTrue($registered);
                $this->assertSame("cached-routes", file_get_contents($path));

            })
        ;

        # Model an existing compiled cache without persistent storage
        $cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["hasUpToDate", "get"])
            ->getMock()
        ;
        $cache->method("hasUpToDate")->willReturn(true);
        $cache->method("get")->willReturn("cached-routes");

        try{

            # Load the adapter without creating a new type or configuration file
            $GLOBALS[Config::PREFIX]["Router"] = ["Router" => ["type" => [["name" => "slug", "class" => RouterTestParameterType::class]]]];
            $router = new CoreRouter($adapter);
            $router->cache = $cache;
            $router->pushCollection();

        }finally{

            # Restore the original configuration
            $GLOBALS[Config::PREFIX] = $original;

        }

    }

    /**
     * Test Adapter Pattern Compatibility
     *
     * @return void
     */
    #[DataProvider("routerAdapters")]
    public function testAdapterPatternCompatibility(string $adapterClass):void {

        # Exercise overlapping patterns, language prefixes and method-specific fallbacks
        $router = new $adapterClass();
        $router->addRoute("/[a:language]/project/[s:project]", "dynamic", "GET", "project");
        $router->addRoute("/[a:language]/project/[s:project]/[i:item-id]", "detail", "GET", "detail");
        $router->addRoute("/en/project/example", "old", "GET");
        $router->addRoute("/en/project/example", "static", "GET");
        $router->addRoute("/", "index", "GET", "index");
        $router->addRoute("/numbers/[il:ids]/[fp:value]", "numbers", "POST", "numbers");
        $router->addRoute("/alternate", "alternate", "GET", "project");
        $router->addRoute("*", "fallback", ["GET", "POST"]);
        $router->warmCache();

        # Assert static priority, legacy root normalization and original parameter names
        $this->assertSame("static", $router->getCallback("/en/project/example/"));
        $this->assertSame("index", $router->getCallback("/"));
        $this->assertSame("dynamic", $router->getCallback("/fr/project/caf%C3%A9"));
        $this->assertSame("café", $router->getParam("project"));
        $this->assertSame("dynamic", $router->getCallback("/fr/project/file%252Fname"));
        $this->assertSame("file%2Fname", $router->getParam("project"));
        $this->assertSame("detail", $router->getCallback("/en/project/example/42"));
        $this->assertSame("42", $router->getParam("item-id"));
        $this->assertSame("alternate", $router->reverse("project", ["language" => "en", "project" => "example"]));
        $this->assertSame("fallback", $router->getCallback("/not/a/page"));

        # Assert another method can use the fallback for an otherwise registered path
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->assertSame("fallback", $router->getCallback("/en/project/example"));
        $this->assertSame("numbers", $router->getCallback("/numbers/1,2/12.34"));
        $this->assertSame("1,2", $router->getParam("ids"));
        $this->assertSame("12.34", $router->getParam("value"));

        # Assert all methods from the existing YAML schema remain supported
        foreach(["GET", "POST", "PUT", "DELETE", "OPTION", "PATCH"] as $method){

            $router->addRoute("/method", $method, $method);
            $_SERVER["REQUEST_METHOD"] = $method;
            $this->assertSame($method, $router->getCallback("/method"));

        }

        # Assert middleware and old routes are removed when the router is cleared
        $router->registerMiddleware("*", static fn():array => ["unexpected"]);
        $router->clear();
        $router->addRoute("/fresh", static fn(string $route, array $parameters):string => $route, "PATCH");
        $this->assertSame("fresh", $router->callRoute("/fresh"));
        $this->assertFalse($router->routeExists("/method"));

    }

    /**
     * Test Collection Cache Isolation And Invalidation
     *
     * @return void
     */
    public function testCollectionCacheIsolationAndInvalidation():void {

        # Share one cache across vendors and model the actual modification-date comparison
        $compiled = $modified = [];
        $requestedDate = null;
        $writes = 0;
        $cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["hasUpToDate", "get", "set"])
            ->getMock()
        ;
        $cache->method("hasUpToDate")->willReturnCallback(static function(string $key, DateTime $date) use (&$compiled, &$modified, &$requestedDate):bool {

            $requestedDate = $date;
            return isset($compiled[$key]) && $modified[$key] >= $date;

        });
        $cache->method("get")->willReturnCallback(static function(string $key) use (&$compiled):mixed {

            return $compiled[$key] ?? null;

        });
        $cache->method("set")->willReturnCallback(static function(string $key, mixed $data) use (&$compiled, &$modified, &$requestedDate, &$writes):bool {

            $compiled[$key] = $data;
            $modified[$key] = $requestedDate;
            $writes++;
            return true;

        });

        # Use deterministic metadata without touching application configuration files
        $dates = [
            "_lastModifiedDateRouter" => new DateTime("2025-01-01"),
            "_lastModifiedDateApi" => new DateTime("2025-01-01"),
            "_lastModifiedDateMiddleware" => new DateTime("2025-01-01"),
        ];
        foreach([null, "_lastModifiedDateRouter", "_lastModifiedDateApi", "_lastModifiedDateMiddleware"] as $step => $changed){

            if($changed !== null)
                $dates[$changed] = new DateTime("2025-01-0".($step + 1));

            foreach(self::routerAdapters() as [$adapterClass]){

                # The first instance builds or refreshes; the second restores without writing
                foreach([false, true] as $cached){

                    $before = $writes;
                    $router = new CoreRouter(new $adapterClass());
                    foreach($dates as $property => $date)
                        (new ReflectionProperty(CoreRouter::class, $property))->setValue($router, $date);

                    $router->cache = $cache;
                    $router->pushCollection();
                    $this->assertSame($before + ($cached ? 0 : 1), $writes);
                    $this->assertSame("App\\Controller\\Api\\v1\\Config::get", $router->getCallback("/api/v1/config/Router/name"));
                    $this->assertSame("Router", $router->getParam("name"));

                }

            }

            $this->assertCount(4, $compiled);

        }

        $this->assertSame(16, $writes);

    }

    /** Private method
     ******************************************************
     */

    /**
     * Check router config
     * 
     * Extract config of the new router and compare it to the expected
     * 
     * > Method to use for test Create Router Methods
     * 
     * @param array $input Input of the create class
     * @return bool
     */
    private function checkRouterConfig(array $input):bool {

        # Set result
        $result = false;

        ## Get name | begin

            # Search name input
            $inputFiltered = Arrays::filterByKey($input["router"], "name", "name");

            # Check filtered input
            if(empty($inputFiltered))

                # Return result
                return $result;

            # Get router name from input
            $routerName = $inputFiltered[array_key_first($inputFiltered)]["value"];

            # Clean Complex Router
            $routerName = Process::snakeToCamel(str_replace(".", "_", $routerName), true);

        ## Get name | end

        ## Get type | begin

            # Search name input
            $inputFiltered = Arrays::filterByKey($input["router"], "name", "type");

            # Check filtered input
            if(empty($inputFiltered))

                # Return result
                return $result;

            # Get router name from input
            $routerType = $inputFiltered[array_key_first($inputFiltered)]["value"];

        ## Get type | end

        # Open config
        $routerConfig = Config::getValue("Router.$routerType");

        # Filter Router Config
        $routerConfigFiltered = Arrays::filterByKey($routerConfig, "name", $routerName);

        # Check if empty
        if(empty($routerConfigFiltered))

            # Return result
            return $result;

        # Get routerConfigValue
        $routerConfigValue = $routerConfigFiltered[array_key_first($routerConfigFiltered)];

        # Prepare router
        $inputSummary = Arrays::changeKeyCaseRecursively(Process::getResultSummary($input["router"]));

        # Clean summary
        $inputSummaryCleaned = [
            "name"          =>  $routerName,
            "methods"       =>  Json::decode($inputSummary['methods']),
            "patterns"      =>  ["/".strtolower(Process::camelToPath($routerName))],
            "controller"    =>  "App\Controller\\".ucfirst($routerType)."\\".$routerName
        ];

        # Check awaited reseult and test generated is the same
        if($routerConfigValue == $inputSummaryCleaned)

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Check router config is missing
     * 
     * Extract config of the new router and compare it to the expected
     * 
     * > Method to use for test Delete Router Methods
     * 
     * @param array $input Input of the create class
     * @return bool
     */
    private function checkRouterConfigIsMissing(array $input):bool {

        # Set result
        $result = true;

        # Check input
        if(!isset($input["routers"][0]["value"]) || empty($input["routers"][0]["value"]))

            # Return result
            return $result;

        # Iteration of value
        foreach($input["routers"][0]["value"] as $value){

            # Explode value
            $valueExploded = explode(".", $value);

            # Set router type
            $routerType = $valueExploded[0];

            # Set router name
            $routerName = $valueExploded[1];

            # Open config
            $routerConfig = Config::getValue("Router.$routerType");
    
            # Filter Router Config
            $routerConfigFiltered = Arrays::filterByKey($routerConfig, "name", $routerName);
    
            # Check if empty
            if(!empty($routerConfigFiltered))

                # Set result
                $result = false;

        }

        # Return result
        return $result;

    }

}

/**
 * Router Test Parameter Type
 *
 * Exercise static custom types and serializable callbacks without extra fixture files.
 */
class RouterTestParameterType implements CrazyRouterType {

    /** @var int $parserCalls Count compilation calls across a cache round trip */
    public static int $parserCalls = 0;

    /**
     * Constructor
     *
     * Keep registration independent from type instantiation.
     */
    private function __construct(){
    }

    /**
     * Search Reg Exp
     *
     * @return string
     */
    public static function searchRegExp():string {

        # Locate the custom placeholder in the existing application syntax
        return "\\[slug:".self::PARAMETER_NAME_REGEX."\\]";

    }

    /**
     * Parser Reg Exp
     *
     * @return string
     */
    public static function parserRegExp():string {

        # Accept project slugs without adding capturing groups
        self::$parserCalls++;
        return '[a-z0-9$-]+';

    }

    /**
     * Handle
     *
     * @param string $route Matched path
     * @param array $parameters Parsed route parameters
     * @return array
     */
    public static function handle(string $route, array $parameters):array {

        # Return route parameters to the adapter tests
        return $parameters;

    }

}
