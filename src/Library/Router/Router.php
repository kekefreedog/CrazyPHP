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
use CrazyPHP\Exception\CrazyException;

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

    /** Public static methods
     ******************************************************
     */

    /**
     * Parse collection
     * 
     * Parse config router collection
     */
    public static function parseCollection($collection):array {

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
                    if(strtoupper($method) && in_array($method, self::METHODS))

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
                $appPrefix = (isset($collection["Router"][$group]) && $collection["Router"][$group]) ?
                    trim($collection["Router"][$group], "/") :
                        "/";

                # Check
                if($collection["Router"][$group] ?? false && !empty($collection["Router"][$group])){

                    # Iteration of app
                    foreach($collection["Router"][$group] as $appRouter){

                        # Get parse router result
                        $router = self::parseRouter($appRouter, $appPrefix, $methods);

                        # Check router
                        if(!empty($router))

                            # Push router in result
                            $result[$group] = $router;

                    }
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
    public static function parseRouter(array $router = [], string $prefix = "/", array $methodsAllowed = self::METHODS):array {

        # Prepare result
        $result = [];

        # Check patterns
        if(!isset($router["patterns"]) || empty($router["patterns"]))

            # Stop function
            return $result;

        # Prepare prefix
        $prefix = trim($prefix, "/");

        # Check methods
        if(!isset($router["methods"]) || empty($router["methods"]))

            # Set get
            $router["methods"] = "GET";

        # Check router is array
        if(!is_array($router["methods"])) $router["methods"] = [$router["methods"]];

        # Iteration of methods
        foreach($router["methods"] as $method){

            # Check current router
            if(!in_array(strtoupper($router["methods"]), $methodsAllowed))

                # Continue iteration
                continue;

            # Check patterns is array
            if(!is_array($router["patterns"])) $router["patterns"] = [$router["patterns"]];

            # Iteration of patterns
            foreach($router["patterns"] as $pattern){

                # Process patterns if prefix
                if($prefix) $pattern = ltrim($pattern, "/");

                # Prepare data 
                $data = [];

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
                $data["pattern"] = "/$prefix/$pattern";

                # Fill method
                $data["method"] = strtolower($method);

                # Fill type
                $data["type"] = "router";

                # Push data in result
                $result[] = $data;

            }

        }

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /* @const array METHODS Methods supported */
    public const METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTION',
        'PATCH'
    ];

    /* @const array GROUPS Type of router */
    public const GROUPS = ["app", "api", "assets"];

}