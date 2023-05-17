<?php declare(strict_types=1);
/**
 * Router
 *
 * Classes for manage router
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Router;

/**
 * Dependances
 */
use CrazyPHP\Core\Router as CoreRouter;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Router
 *
 * Class for manage router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Router {

    /** Public static methods | Router redirection
     ******************************************************
     */

    /**
     * Parse collection
     * 
     * Parse config router collection
     * 
     * @param array $collection Collection of router
     * @return array
     */
    public static function parseCollection(array $collection = []):array {

        # Declare result
        $result = [];

        /* get methods */

            # Declare methods
            $methods = [];

            # Check
            if($collection["Router"]["methods"] ?? false && !empty($collection["Router"]["methods"])){

                # Check methodsTemp is array
                $methodsTemp = !is_array($collection["Router"]["methods"]) ?
                    [$collection["Router"]["methods"]] :
                        $collection["Router"]["methods"];

                # Iteration of methods
                foreach($methodsTemp as $method)

                    # Check method is valid
                    if(strtoupper($method) && in_array(strtoupper($method), self::METHODS))

                        # Push method
                        $methods[] = strtoupper($method);
                
            }else{

                # Set methods
                $methods = self::METHODS;

            }

        /* Get methods | end */

        /* Prepare router */

            # Iteration of group
            foreach(self::GROUPS as $group){

                # Get app prefix
                $currentPrefix = (isset($collection["Router"]["prefix"][$group]) && $collection["Router"]["prefix"][$group]) ?
                    trim($collection["Router"]["prefix"][$group], "/") :
                        "/";

                # Check
                if($collection["Router"][$group] ?? false && !empty($collection["Router"][$group])){

                    # Iteration of app
                    foreach($collection["Router"][$group] as $appRouter){

                        # Get parse router result
                        $router = self::parseRouter($appRouter, $currentPrefix, $methods);

                        # Check router
                        if(!empty($router))

                            # Iteration des router
                            foreach($router as $childRouter)

                                # Push router in result
                                $result[$group][] = $childRouter;

                    }
                }
                
            }

        # Return result
        return $result;

    }

    /**
     * Parse Api Collection
     * 
     * Parse config router collection
     * 
     * @param array $collection Collection of api router
     * @return array
     */
    public static function parseApiCollection():array {

        # Declare result
        $result = [];

        # Open api config
        $api = Config::get("Api");

        /* get methods */

            # Declare methods
            $methods = [];

            # Get methods allowed
            $methodsAllowed = Config::getValue("Router.methods");

            # Get api prefix
            $apiPrefix = (string) Config::getValue("Router.prefix.api");

            # Check
            if(!empty($methodsAllowed)){

                # Check methodsTemp is array
                $methodsTemp = !is_array($methodsAllowed) ?
                    [$methodsAllowed] :
                        $methodsAllowed;

                # Iteration of methods
                foreach($methodsTemp as $method)

                    # Check method is valid
                    if(strtoupper($method) && in_array(strtoupper($method), self::METHODS))

                        # Push method
                        $methods[] = strtoupper($method);
                
            }else{

                # Set methods
                $methods = self::METHODS;

            }

        /* Get methods | end */

        /* Prepare router */

            # Check api 2
            if(
                $api && 
                isset($api["Api"]["v2"]) && 
                ($api["Api"]["v2"]["enable"] ?? false) && 
                !empty($api["Api"]["v2"]["routers"] ?? [])
            ){

                # Get prefix
                $currentPrefix = rtrim($apiPrefix, "/") . "/" . $api["Api"]["v2"]["prefix"] ?? "" ;

                # Iteration of app
                foreach($api["Api"]["v2"]["routers"] as $apiRouter){

                    # Get parse router result
                    $router = self::parseRouter($apiRouter, $currentPrefix, $methods);

                    # Check router
                    if(!empty($router))

                        # Iteration des router
                        foreach($router as $childRouter)

                            # Push router in result
                            $result["api"][] = $childRouter;

                }
            
            }

        # Return result
        return $result;

    }

    /**
     * Parse Router
     * 
     * Parse router from collection
     * 
     * @param array $router Router collection
     * @param string $prefix Prefix to put on pattern before
     * @param array $methodsAllowed Methods allowed
     * @return array
     */
    public static function parseRouter(array|null $router = [], string $prefix = "/", array $methodsAllowed = self::METHODS):array {

        # Prepare result
        $result = [];

        # Check patterns
        if(
            $router === null ||
            empty($router) ||
            !isset($router["patterns"]) || 
            empty($router["patterns"])
        )

            # Stop function
            return $result;

        # Prepare prefix
        $prefix = trim($prefix, "/");

        # Check methods
        if(!isset($router["methods"]) || empty($router["methods"]))

            # Set get
            $router["methods"] = ["GET"];

        # Check router is array
        if(!is_array($router["methods"])) $router["methods"] = [$router["methods"]];

        # Iteration of methods
        foreach($router["methods"] as $method){

            # Check current router
            if(!in_array(strtoupper($method), $methodsAllowed))

                # Continue iteration
                continue;

            # Check patterns is array
            if(!is_array($router["patterns"])) 
                $router["patterns"] = [$router["patterns"]];

            # Iteration of patterns
            foreach($router["patterns"] as $pattern){

                # Set and/or clear data 
                $data = [];

                # Clean pattern
                $pattern = trim($pattern, "/");

                # Fill name in data
                if(!isset($router["name"]) || empty($router["name"]))

                    # New Exception
                    throw new CrazyException(
                        "Name is missing in router given \"".json_encode($router)."\"",
                        500,
                        [
                            "custom_code"   =>  "router-001",
                        ]
                    );

                else

                    # Fill name
                    $data["name"] = $router["name"];

                # Fill controller in data
                if(!isset($router["controller"]) || empty($router["controller"]))

                    # New Exception
                    throw new CrazyException(
                        "Controller class is missing in router given \"".json_encode($router)."\"",
                        500,
                        [
                            "custom_code"   =>  "router-002",
                        ]
                    );

                else

                    # Fill controller
                    $data["controller"] = $router["controller"]."::".$method;

                # Fill pattern
                $data["pattern"] = (isset($router["prefix"])) ?
                    (
                        $router["prefix"] ?
                            "/".trim($router["prefix"], "/")."/$pattern/" :
                                "/$pattern/"

                    ) :
                        "/$prefix/$pattern/";

                # Check if not double //
                $data["pattern"] = str_replace("//", "/", $data["pattern"]);

                # Fill method
                $data["method"] = strtoupper($method);

                # Fill type
                $data["type"] = "router";

                # Push data in result
                $result[] = $data;

            }

        }

        # Return result
        return $result;

    }
    
    /**
     * Get By Name
     * 
     * Get Router by name
     * 
     * @param string $routerName Name of the router
     * @return null|array
     */
    public static function getByName(string $routerName = ""):null|array {

        # Set result
        $result = null;

        # Check router name
        if(!$routerName)

            # Return result
            return $result;

        # Cache Instance
        $cacheInstance = new Cache();

        # Prepare key
        $key = Cache::getKeyWithCacheName(__METHOD__, "collection");

        # Get last modified date
        $lastModifiedDate = File::getLastModifiedDate(File::path("@app_root/config/Router.yml"));

        # Check if modified
        if(!$cacheInstance->hasUpToDate($key, $lastModifiedDate)){

            # Dump on cache
            static::dumpOnCache();

        }

        # Get collection from cache
        $routersCollection = self::loadFromCache();

        # Check router exists
        if(!isset($routersCollection[$routerName]))

            # New Exception
            throw new CrazyException(
                "Router \"$routerName\" doesn't exists...",
                500,
                [
                    "custom_code"   =>  "router-003",
                ]
            );

        # Set result
        $result = $routersCollection[$routerName];

        # Return result
        return $result;
        
    }

    /**
     * Dump On Cache
     * 
     * Cache router collection
     */
    public static function dumpOnCache(){

        # Cache Instance
        $cacheInstance = new Cache();

        # Prepare key
        $key = Cache::getKeyWithCacheName(__CLASS__, "routerCollection");

        # Get router collection from config
        $routerConfig = Config::get("Router");

        # Get Api 2 config
        $routerApi = Config::get("Api");

        # Data to cache
        $dataToCache = [];

        # Iteration of router
        foreach(static::GROUPS as $group)

            # Check not empty
            if(!empty($routerConfig["Router"][$group])){

                # Prepare prefix
                $prefix = $routerConfig["Router"]["prefix"][$group] ?? null;

                # Iteration of groups
                foreach($routerConfig["Router"][$group] as $router){

                    # Check router name
                    if(!isset($router["name"]) || empty($router))

                        # Check router
                        continue;

                    # Check router name doesn't exists
                    if(isset($dataToCache[$router["name"]]))

                        # New Exception
                        throw new CrazyException(
                            "Router \"".$router["name"]."\" already exists in router config, make sure all router have unique name !",
                            500,
                            [
                                "custom_code"   =>  "router-004",
                            ]
                        );

                    # Fill router
                    if(!isset($router["prefix"]))
                        $router["prefix"] = $prefix;

                    # Fill group
                    $router["group"] = $group;

                    # Push in data to cache
                    $dataToCache[$router["name"]] = $router;

                }

                # Check if group is api
                if(
                    $group === "api" && 
                    isset($routerApi["Api"]["v2"]["enable"]) &&
                    $routerApi["Api"]["v2"]["enable"] === true &&
                    isset($routerApi["Api"]["v2"]["routers"]) &&
                    !empty($routerApi["Api"]["v2"]["routers"])
                ){

                    # Set api prefix
                    $apiPrefix = 
                        (
                            $prefix ? 
                                $prefix : 
                                    ""
                        ) . 
                        (
                            ($routerApi["Api"]["v2"]["prefix"] ?? false) ?
                                (
                                    ($prefix ? "/" : "") .
                                    trim($routerApi["Api"]["v2"]["prefix"], "/")
                                ):
                                    ""
                        )
                    ;

                    # Iteration des routers
                    foreach($routerApi["Api"]["v2"]["routers"] as $router){

                        # Check router name
                        if(!isset($router["name"]) || empty($router))
    
                            # Check router
                            continue;
    
                        # Check router name doesn't exists
                        if(isset($dataToCache[$router["name"]]))
    
                            # New Exception
                            throw new CrazyException(
                                "Api router \"".$router["name"]."\" already exists in router config, make sure all router have unique name !",
                                500,
                                [
                                    "custom_code"   =>  "router-005",
                                ]
                            );
    
                        # Fill router
                        if($apiPrefix)
                            $router["prefix"] = $apiPrefix;
    
                        # Fill group
                        $router["group"] = $group;
    
                        # Push in data to cache
                        $dataToCache[$router["name"]] = $router;

                    }

                }

            }
            
        # Put on Cache
        $cacheInstance->set($key, $dataToCache);

    }

    /**
     * Dump On Cache
     * 
     * Cache router collection
     * 
     * @return array;
     */
    public static function loadFromCache():array {

        # Result
        $result = [];

        # Cache Instance
        $cacheInstance = new Cache();

        # Prepare key
        $key = Cache::getKeyWithCacheName(__CLASS__, "routerCollection");

        # Get data from cache
        $result = $cacheInstance->get($key);

        # Return result
        return $result;

    }

    /**
     * Reverse
     * 
     * Reverse Route
     * 
     * @param string $name Name of the router
     * @param ?array $arguments Arguments of the router
     * @return string
     */
    public static function reverse(string $name, ?array $arguments = null):string {

        # Set result
        $result = "";

        # New router instance
        $routerInstance = new CoreRouter();

        # Push collection
        $routerInstance->pushCollection();

        # Set result
        $result = $routerInstance->reverse($name, $arguments ?: []);

        # Return result
        return $result;

    }

    /**
     * Get Summary
     * 
     * Get a summary of existing routers
     * @return array
     */
    public static function getSummary():array {

        # Set result
        $result = [];

        # Get config values
        foreach(["app", "api", "asset"] as $type){

            # Get routers
            $routers = Config::getValue("Router.$type");

            # Check routers
            if(is_array($routers) && !empty($routers)){

                # Iteration routers
                foreach($routers as $router){

                    # Check router name
                    if(isset($router["name"]) && $router["name"]){

                        $result["$type.".$router["name"]] = "(".ucfirst($type).") ".$router["name"];

                    }

                }

            }

        }

        # Return result
        return $result;

    }

    /** Public static methods | Routers file
     ******************************************************
     */

    /**
     * Get App Path
     * 
     * Get path of the page environnement : with index / style / template
     * env : "router_app_path"
     * 
     * @return string
     */
    public static function getAppPath():string {

        # Set result
        $result = Env::get("router_app_path", true) ?: static::ROUTER_APP_PATH;

        # Return result
        return $result;

    }

    /**
     * Get Controller Path
     * 
     * Get path of the page environnement : with index / style / template
     * env : "router_controller_path"
     * 
     * @return string
     */
    public static function getControllerPath():string {

        # Set result
        $result = Env::get("router_controller_path", true) ?: static::ROUTER_CONTROLLER_PATH;

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array METHODS Methods supported */
    public const METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTION',
        'PATCH'
    ];

    /** @const array GROUPS Type of router */
    public const GROUPS = ["app", "api", "asset"];

    /** @const string ROUTER_APP_PATH */
    public const ROUTER_APP_PATH = "@app_root/app/Environment/Page/";

    /** @const public ROUTER_CONTROLLER_PATH */
    public const ROUTER_CONTROLLER_PATH = "@app_root/app/Controller/";

}