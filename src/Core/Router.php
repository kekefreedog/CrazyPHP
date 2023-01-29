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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Router as LibraryRouter;
use Mezon\Router\Router as VendorRouter;
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
 * @copyright  2022-2022 Kévin Zarshenas
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
     * @return 
     */
    public function callRouteExtended(string $request_uri = ""){
        
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
        return $this->callRoute($url);

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
        
        # Prepare data
        $data = [
            0 => $this->staticRoutes ?? null,
            1 => $this->paramRoutes ?? null,
            2 => $this->routeNames ?? null,
            3 => $this->cachedRegExps ?? null,
            4 => $this->cachedParameters ?? null,
            5 => $this->regExpsWereCompiled ?? null
        ];
        
        # Put on Cache
        $this->cache->set($key, $data);

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

        # Ingest data
        list ($this->staticRoutes, $this->paramRoutes, $this->routeNames, $this->cachedRegExps, $this->cachedParameters, $this->regExpsWereCompiled) = $data;

    }

}