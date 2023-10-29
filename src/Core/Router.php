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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Router as LibraryRouter;
use Mezon\Router\Types\BaseType as VendorBaseType;
use Mezon\Router\Router as VendorRouter;
use CrazyPHP\Interface\CrazyRouterType;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Context;

/**
 * Router
 *
 * Class for dispatch client request...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Router extends VendorRouter {

    /** Parameters
     ******************************************************
     */

    /** @var Cache|null $cache Cache instance */
    public ?Cache $cache = null;

    /** Various parameters */
    public $staticRoutes, $paramRoutes, $routeNames, $cachedRegExps, $cachedParameters, $regExpsWereCompiled;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructor
        parent::__construct();

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
     * @return void
     */
    public function pushCollection(string $collectionPath = ""):void {

        # Prepare collection name
        $collectionName = $collectionPath ?
            pathinfo($collectionPath, PATHINFO_FILENAME) :
                "ConfigRouter";

        # Get key
        $key = str_replace(
            ["{", "}", "(", ")", "/", "\\", "@", ":"],
            ".",
            Cache::getCacheName(__CLASS__).".RouterCollectionCached.$collectionName"
        );

        # New cache instance
        $this->cache = new Cache();

        # Set lastModifiedDate
        $lastModifiedDate = File::getLastModifiedDate($collectionPath ? File::path($collectionPath) : File::path("@app_root/config/Router.yml"));

        # Get last modified date of api
        $lastModifiedDateApi = File::getLastModifiedDate(File::path("@app_root/config/Api.yml"));

        # Compare two dates
        if($lastModifiedDateApi > $lastModifiedDate)

            # Update last modified date
            $lastModifiedDate = $lastModifiedDateApi;

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

        /* Add custom type */
        $this->addRouterType();

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

        # Create temp file
        $folderCachePath = File::path(self::CACHE_PATH);
        $fileCachePath = File::path(self::CACHE_PATH.time());

        # Check folder
        if(!File::exists($folderCachePath))

            # Create folder
            File::createDirectory($folderCachePath);

        # Create file cache
        $this->dumpOnDisk($fileCachePath);

        # Get content of cache
        $data = file_get_contents($fileCachePath);
        
        # Put on Cache
        $this->cache->set($key, $data);

        # Check if file exists
        if(file_exists($fileCachePath))

            # Remove cache
            unlink($fileCachePath);

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

        # Create temp file
        $folderCachePath = File::path(self::CACHE_PATH);
        $fileCachePath = File::path(self::CACHE_PATH.time());

        # Check folder
        if(!is_dir($folderCachePath))

            # Create folder
            mkdir($folderCachePath);

        # Get content of cache
        $data = file_put_contents($fileCachePath, $data);

        # Load cache
        $this->loadFromDisk($fileCachePath);

        # Check if file exists
        if(file_exists($fileCachePath))

            # Remove cache
            unlink($fileCachePath);

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
                    new $router["class"] instanceof CrazyRouterType
                ){

                    # Push in router
                    $this->addType(strtolower($router["name"]), $router["class"]);

                }

            }

    }


    /** Public constant
     ******************************************************
     */

    /** @param string PARAMETER_NAME_REGEX */
    public const PARAMETER_NAME_REGEX = VendorBaseType::PARAMETER_NAME_REGEXP;

    /** @const CACHE_ROUTER */
    public const CACHE_PATH = "@app_root/.cache/app/router/";

}