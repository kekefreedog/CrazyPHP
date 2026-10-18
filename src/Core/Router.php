<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Router as LibraryRouter;
use CrazyPHP\Library\Router\Adapter\LeagueRoute;
use CrazyPHP\Driver\Model\Config as ModelConfig;
use CrazyPHP\Model\Config as ApplicationConfig;
use CrazyPHP\Library\Router\Adapter\FastRoute;
use CrazyPHP\Library\Router\Adapter\Symfony;
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Library\Router\Adapter\Mezon;
use CrazyPHP\Interface\CrazyRouterType;
use CrazyPHP\Library\Router\Middleware;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyRouter;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Context;
use CrazyPHP\Model\Env;

/**
 * Router
 *
 * Class for dispatch client request...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Router implements CrazyRouter {

    /** Parameters
     ******************************************************
     */

    /** @var ?Cache $cache Cache instance */
    public ?Cache $cache = null;

    /** @var CrazyRouter $_adapter Routing engine used by the framework */
    private CrazyRouter $_adapter;

    /** @var DateTime lastModifiedDateRouter */
    private DateTime $_lastModifiedDateRouter;

    /** @var DateTime lastModifiedDateApi */
    private DateTime $_lastModifiedDateApi;

    /** @var DateTime lastModifiedDateMiddleware */
    private DateTime $_lastModifiedDateMiddleware;

    /**
     * Constructor
     * 
     * @param CrazyRouter|null $adapter Routing engine; defaults to Mezon
     */
    public function __construct(?CrazyRouter $adapter = null){

        # Honor an explicitly injected adapter before reading application defaults
        if($adapter === null){

            # Locate optional application settings using the configured directory
            $location = Env::has("config_location") ? Env::get("config_location") : Config::FOLDER_PATH;

            # Support minimal projects and tests without a Router configuration file
            $hasRouter = isset($GLOBALS[Config::PREFIX]["Router"]) || ApplicationConfig::exists("Router", $location);

            # Read the selected vendor alongside the route collections
            $vendor = $hasRouter && Config::has("Router.vendor") ? Config::getValue("Router.vendor") : "mezon";

            # Select the configured implementation while preserving the legacy default
            $adapter = match($vendor){
                "mezon", "none", null => new Mezon(),
                "fast-route" => new FastRoute(),
                "league" => new LeagueRoute(),
                "symfony" => new Symfony(),
                default => throw new CrazyException("Unknown router vendor: $vendor", 500),
            };

        }

        # Store the adapter shared by dispatch, reverse routing and caching
        $this->_adapter = $adapter;

        # Set last modified date
        $this->_setLastModifiedDates();
    }

    /** Public methods
     ******************************************************
     */

    /**
     * call Route Extended
     * 
     * Call Route and prepare context
     * 
     * @param string $request_uri Request uri from server
     * @return mixed
     */
    public function callRouteExtended(string $request_uri = ""):mixed {
        
        # Set result
        $result = null;

        # Check request uri
        if(!$request_uri)

            # Fill request uri
            $request_uri = $_SERVER["REQUEST_URI"];

        # Get usefull path
        $url = parse_url($request_uri, PHP_URL_PATH);

        # Call route controller
        $controller = $this->getCallback($url);

        # Get Class Name
        $className = strstr(ltrim(strrchr($controller, '\\'), '\\'), '::', true);

        # Fill context
        Context::setCurrentRoute($className);

        # Call route
        $result = $this->callRoute($url);

        # Return result
        return $result;

    }

    /**
     * PushCollection
     * 
     * Push collection of router in current instance
     * 
     * @return void
     */
    public function pushCollection(string $collectionPath = ""):void {

        # Prepare collection name
        $collectionName = $collectionPath ?
            pathinfo($collectionPath, PATHINFO_FILENAME) :
                "ConfigRouter";

        # Get key
        $key = Cache::getKeyWithCacheName(__CLASS__, ".RouterCollectionCached.$collectionName.".$this->_adapter::class);

        # Check cache
        if($this->cache === null)

            # New cache instance
            $this->cache = new Cache();

        # Set lastModifiedDate
        $lastModifiedDate = $collectionPath 
            ? File::getLastModifiedDate(File::path($collectionPath))
            : $this->_lastModifiedDateRouter
        ;

        # Get last modified date of api
        $lastModifiedDateApi = $this->_lastModifiedDateApi;

        # Get last modified date of api
        $lastModifiedDateMiddleware = $this->_lastModifiedDateMiddleware;

        # Compare two dates
        if($lastModifiedDateApi > $lastModifiedDate)

            # Update last modified date
            $lastModifiedDate = $lastModifiedDateApi;

        # Compare two dates
        if($lastModifiedDateMiddleware > $lastModifiedDate)

            # Update last modified date
            $lastModifiedDate = $lastModifiedDateMiddleware;

        # Register custom types for both freshly compiled and cached routes
        $this->addRouterType();

        # Check cache is valid
        if($this->cache->hasUpToDate($key, $lastModifiedDate)){

            # Load From Cache
            $this->loadFromCache($key);

            # Stop function
            return;
            
        }

        # Check collection path
        if($collectionPath)

            # Set collection
            $collection = File::open($collectionPath);

        # Else read config router
        else

            # Set collection
            $collection = Config::get("Router");

        /* Add Pages */

        # Check router.page
        if(!isset($collection["Router"]) || empty($collection["Router"]))

            # New Exception
            throw new CrazyException(
                "No page is declared in your router collection",
                500,
                [
                    "custom_code"   =>  "router-002",
                ]
            );

        # Parse collection
        $collectionParsed = LibraryRouter::parseCollection($collection);

        # Push api routers
        $collectionParsed["api"] = array_merge(
            $collectionParsed["api"] ?? [],
            LibraryRouter::parseApiCollection()["api"] ?? []
        );

        # Check collection
        if(empty($collectionParsed))

            # New Exception
            throw new CrazyException(
                "Collection of router is empty... Check your router config file.",
                500,
                [
                    "custom_code"   =>  "router-001",
                ]
            );

        # Iteration of groups
        foreach(LibraryRouter::GROUPS as $group)

            # Check group not empty
            if(!empty($collectionParsed[$group]))

                # Iteration of collection
                foreach($collectionParsed[$group] as $item){

                    # Check type
                    if($item["type"] == "router"){
                        
                        # Add router
                        $this->addRoute(
                            $item["pattern"], 
                            $item["controller"], 
                            $item["method"], 
                            $item["name"]
                        );

                    }

                }

        # Dump On cache
        $this->dumpOnCache($key);

    }

    /**
     * Push Middlewares
     * 
     * Put middlewares on router
     * 
     * @return void
     */
    public function pushMiddlewares(string $collectionPath = ""):void {

        # Prepare collection name
        $collectionName = $collectionPath ?
            pathinfo($collectionPath, PATHINFO_FILENAME) :
                "ConfigMiddlewares";

        # Get key
        $key = Cache::getKeyWithCacheName(__CLASS__, ".RouterCollectionCached.$collectionName.".$this->_adapter::class);

        # Check cache
        if($this->cache === null)

            # New cache instance
            $this->cache = new Cache();

        # Set lastModifiedDate
        $lastModifiedDate = $collectionPath 
            ? File::getLastModifiedDate(File::path($collectionPath))
            : $this->_lastModifiedDateRouter
        ;

        # Get last modified date of api
        $lastModifiedDateApi = $this->_lastModifiedDateApi;

        # Get last modified date of api
        $lastModifiedDateMiddleware = $this->_lastModifiedDateMiddleware;

        # Compare two dates
        if($lastModifiedDateApi > $lastModifiedDate)

            # Update last modified date
            $lastModifiedDate = $lastModifiedDateApi;

        # Compare two dates
        if($lastModifiedDateMiddleware > $lastModifiedDate)

            # Update last modified date
            $lastModifiedDate = $lastModifiedDateMiddleware;

        # Check cache is valid
        if($this->cache->hasUpToDate($key, $lastModifiedDate) && false){

            # Get cached data
            list($coreMiddlewares, $appMiddlewares, $apiMiddlewares) = $this->cache->get($key);
            
        }else{

            # Get core middlewares
            $coreMiddlewares = Middleware::getAllFromCore(); 

            # Get app middlewares
            $appMiddlewares = Middleware::getAllFromApp();

            # Get api middlewares
            $apiMiddlewares = Middleware::getAllFromApi();

            # Put on Cache
            $this->cache->set($key, [
                $coreMiddlewares,
                $appMiddlewares,
                $apiMiddlewares
            ]);

        }

        # Check middleware
        if(!empty($coreMiddlewares))

            /** Iteration core middlewares */
            foreach($coreMiddlewares as $middleware)

                # Register middleware
                $this->registerMiddleware(
                    "*", 
                    function(string $route, ...$parameters) use ($middleware){
                        return $middleware["class"]::{$middleware["name"]}($route, ...$parameters);
                    }
                );

        # Set app api middlewares
        $appApiMiddlewares = (is_array($appMiddlewares) ? $appMiddlewares : []) + (is_array($apiMiddlewares) ? $apiMiddlewares : []);

        # Check not empty
        if(!empty($appApiMiddlewares))

            # Iteration middleware
            foreach($appApiMiddlewares as $pattern => $middlewares)

                # Check middlewares
                if(!empty($middlewares))

                    # Iteration of middlewares
                    foreach($middlewares as $middleware)

                        # Register middleware
                        $this->registerMiddleware(
                            $pattern, 
                            function(ServerRequestInterface $request) use ($middleware){
                                return $middleware($request);
                            }
                        );

    }

    /**
     * Dump On Cache
     * 
     * Put on cache the current routes
     * 
     * @param string $key Key of the cached router collection to create or u^date
     * @return void
     */
    public function dumpOnCache(string $key = ""):void {

        # Check key
        if(!$key)
            
            # New Exception
            throw new CrazyException(
                "Key of the cache isn't valid for create router cache.",
                500,
                [
                    "custom_code"   =>  "router-003",
                ]
            );

        # Tmp file Path
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'routerDump');

        try{

            # Create file cache through the selected adapter
            $this->dumpOnDisk($tmpFilePath);

            # Put the compiled routes on cache
            $this->cache->set($key, file_get_contents($tmpFilePath));

        }finally{

            # Release the temporary adapter cache file
            unlink($tmpFilePath);

        }

    }

    /**
     * Load From Cache
     * 
     * Get route from the cache
     * 
     * @param string $key Key of the current cached router collection
     * @return void
     */
    public function loadFromCache(string $key = ""):void {

        # Check key
        if(!$key)
            
            # New Exception
            throw new CrazyException(
                "Key of the cache isn't valid for load router cache.",
                500,
                [
                    "custom_code"   =>  "router-004",
                ]
            );

        # Get cached data
        $data = $this->cache->get($key);

        # Check data
        if(empty($data))
            
            # New Exception
            throw new CrazyException(
                "Oups... Cached router collection is empty and can't be load...",
                500,
                [
                    "custom_code"   =>  "router-005",
                ]
            );

        # Tmp file Path
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'routerDump');

        try{

            # Restore the compiled routes through the selected adapter
            file_put_contents($tmpFilePath, $data);
            $this->loadFromDisk($tmpFilePath);

        }finally{

            # Release the temporary adapter cache file
            unlink($tmpFilePath);

        }

    }

    /** Public Methods | Adapter
     ******************************************************
     */

    /**
     * Add Route
     *
     * {@inheritdoc}
     */
    public function addRoute(string $route, $callback, $requestMethod = "GET", string $routeName = ""):void {

        # Delegate to the selected router adapter
        $this->_adapter->addRoute($route, $callback, $requestMethod, $routeName);

    }

    /**
     * Get Callback
     *
     * {@inheritdoc}
     */
    public function getCallback($route):mixed {

        # Delegate to the selected router adapter
        return $this->_adapter->getCallback($route);

    }

    /**
     * Call Route
     *
     * {@inheritdoc}
     */
    public function callRoute($route):mixed {

        # Delegate to the selected router adapter
        return $this->_adapter->callRoute($route);

    }

    /**
     * Reverse
     *
     * {@inheritdoc}
     */
    public function reverse(string $routeName, array $parameters = []):string {

        # Delegate to the selected router adapter
        return $this->_adapter->reverse($routeName, $parameters);

    }

    /**
     * Register Middleware
     *
     * {@inheritdoc}
     */
    public function registerMiddleware(string $router, callable $middleware):void {

        # Delegate to the selected router adapter
        $this->_adapter->registerMiddleware($router, $middleware);

    }

    /**
     * Add Type
     *
     * {@inheritdoc}
     */
    public function addType(string $typeName, string $className):void {

        # Delegate to the selected router adapter
        $this->_adapter->addType($typeName, $className);

    }

    /**
     * Route Exists
     *
     * {@inheritdoc}
     */
    public function routeExists(string $route):bool {

        # Delegate to the selected router adapter
        return $this->_adapter->routeExists($route);

    }

    /**
     * Get Route By Name
     *
     * {@inheritdoc}
     */
    public function getRouteByName(string $routeName):string {

        # Delegate to the selected router adapter
        return $this->_adapter->getRouteByName($routeName);

    }

    /**
     * Get Param
     *
     * {@inheritdoc}
     */
    public function getParam(string $name):mixed {

        # Delegate to the selected router adapter
        return $this->_adapter->getParam($name);

    }

    /**
     * Has Param
     *
     * {@inheritdoc}
     */
    public function hasParam(string $name):bool {

        # Delegate to the selected router adapter
        return $this->_adapter->hasParam($name);

    }

    /**
     * Get Called Route
     *
     * {@inheritdoc}
     */
    public function getCalledRoute():string {

        # Delegate to the selected router adapter
        return $this->_adapter->getCalledRoute();

    }

    /**
     * Clear
     *
     * {@inheritdoc}
     */
    public function clear():void {

        # Delegate to the selected router adapter
        $this->_adapter->clear();

    }

    /**
     * Dump On Disk
     *
     * {@inheritdoc}
     */
    public function dumpOnDisk(string $filePath = "./cache/cache.php"):void {

        # Delegate to the selected router adapter
        $this->_adapter->dumpOnDisk($filePath);

    }

    /**
     * Load From Disk
     *
     * {@inheritdoc}
     */
    public function loadFromDisk(string $filePath = "./cache/cache.php"):void {

        # Delegate to the selected router adapter
        $this->_adapter->loadFromDisk($filePath);

    }

    /**
     * Warm Cache
     *
     * {@inheritdoc}
     */
    public function warmCache():void {

        # Delegate to the selected router adapter
        $this->_adapter->warmCache();

    }

    /**
     * Set No Processor Found Error Handler
     *
     * {@inheritdoc}
     */
    public function setNoProcessorFoundErrorHandler(callable $function):?callable {

        # Delegate to the selected router adapter
        return $this->_adapter->setNoProcessorFoundErrorHandler($function);

    }

    /**
     * Get No Processor Error Handler
     *
     * {@inheritdoc}
     */
    public function getNoProcessorErrorHandler():callable {

        # Delegate to the selected router adapter
        return $this->_adapter->getNoProcessorErrorHandler();

    }

    /**
     * No Processor Found Error Handler
     *
     * {@inheritdoc}
     */
    public function noProcessorFoundErrorHandler(string $route):void {

        # Delegate to the selected router adapter
        $this->_adapter->noProcessorFoundErrorHandler($route);

    }

    /**
     * Add Get Route
     *
     * {@inheritdoc}
     */
    public function addGetRoute(string $route, object $object, string $method):void {

        # Delegate to the selected router adapter
        $this->_adapter->addGetRoute($route, $object, $method);

    }

    /**
     * Add Post Route
     *
     * {@inheritdoc}
     */
    public function addPostRoute(string $route, object $object, string $method):void {

        # Delegate to the selected router adapter
        $this->_adapter->addPostRoute($route, $object, $method);

    }

    /**
     * Fetch Actions
     *
     * {@inheritdoc}
     */
    public function fetchActions(object $object, array $map = []):void {

        # Delegate to the selected router adapter
        $this->_adapter->fetchActions($object, $map);

    }

    /** Public static methods | Router
     ******************************************************
     */

    /**
     * Redirect To
     * 
     * @param string $routerName
     * @param ?array $arguments
     */
    public static function redirectTo(string $routerName, ?array $arguments = null, ?array $query = null):void {

        # Check route name
        if($routerName){

            # Get accept value from header
            $accept = Header::getHeaderAccept();

            # Check if text
            if($accept == "text/html"){

                # Set query
                $queryString = is_array($query) && !empty($query)
                    ? "?".http_build_query($query)
                    : ""
                ;

                # New response
                (new Response())
                    ->addHeader("Location", ModelConfig::getRouterPath([
                        "name"      =>  $routerName,
                        "arguments" =>  $arguments
                    ]).$queryString)
                    ->send()
                ;

            }else
            # Check is json
            if($accept == "application/json"){

                # Set response
                (new ApiResponse())
                    ->pushContent("_events", [
                        # Redirect
                        [
                            "type"      =>  "redirect",
                            "name"      =>  $routerName,
                            "arguments" =>  $arguments,
                            "query"     =>  $query
                        ]
                    ])
                    ->send();

            }

            # Exit
            exit();

        }

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Add Router Type
     * 
     * Add router type define in the app
     * 
     * @return void
     */
    private function addRouterType():void {

        # Get router type config
        $routerTypeCollection = Config::getValue("Router.type");

        # Check router type
        if(!empty($routerTypeCollection))

            # Iteration router type
            foreach($routerTypeCollection as $router){

                # Check name and collectio
                if(
                    ($router["name"] ?? false) &&
                    ($router["class"] ?? false) &&
                    class_exists($router["class"]) &&
                    is_a($router["class"], CrazyRouterType::class, true)
                ){

                    # Push in router
                    $this->addType(strtolower($router["name"]), $router["class"]);

                }

            }

    }

    /**
     * Set Last Modified Dates
     * 
     * @return void
     */
    private function _setLastModifiedDates():void {

        # Set last modified date router
        $this->_lastModifiedDateRouter = File::getLastModifiedDate(File::path("@app_root/config/Router.yml"));

        # Set last modified date api
        $this->_lastModifiedDateApi = File::getLastModifiedDate(File::path("@app_root/config/Api.yml"));
        
        # Set last modified date middleware
        $this->_lastModifiedDateMiddleware = File::getLastModifiedDate(File::path("@app_root/config/Middleware.yml"));

    }

    /** Public constant
     ******************************************************
     */

    /** @var string PARAMETER_NAME_REGEX */
    public const PARAMETER_NAME_REGEX = CrazyRouterType::PARAMETER_NAME_REGEX;

    /** @var string */
    public const CACHE_PATH = "@app_root/.cache/app/router/";

}
