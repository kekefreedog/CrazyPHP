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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Router;

use CrazyPHP\Library\File\Config;
use ReflectionMethod;
use ReflectionClass;

/**
 * Dependances
 */

/**
 * Middleware
 *
 * Class for manage middleware
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Middleware {

    /** Public static methods
     ******************************************************
     */

    /**
     * Get All From Core
     * 
     * @return ReflectionMethod[]
     */
    public static function getAllFromCore():array {

        # Set result
        $result = [];

        # New reflection
        $reflection = new ReflectionClass("CrazyPHP\Core\Middleware");

        # Get static methods
        $staticMethods = $reflection->getMethods(ReflectionMethod::IS_STATIC|ReflectionMethod::IS_PUBLIC);

        # Check methods
        if(!empty($staticMethods))

            # Iteration 
            foreach($staticMethods as $staticMethod)

                # Check name and class
                if($staticMethod->class && $staticMethod->name)

                    # Push to result
                    $result[] = [
                        "class" =>  $staticMethod->class,
                        "name"  =>  $staticMethod->name
                    ];

        # Return result
        return $result;

    }

    /**
     * Get All From App
     * 
     * @return array
     */
    public static function getAllFromApp():array {

        # Set result
        $result = [];

        # Get middleware
        $middlewares = Config::getValue("Middleware");

        # Set middlewares collection
        $middlewaresCollection = [];

        # Check middlewares
        if(is_array($middlewares) && !empty($middlewares))

            # Iteration
            foreach($middlewares as $middleware)

                # Check script and name
                if(is_string($middleware["name"]) && $middleware["name"] && is_string($middleware["script"]) && $middleware["script"] && is_callable($middleware["script"]))

                    # Push in middlewares collection
                    $middlewaresCollection[$middleware["name"]] = $middleware["script"];

        # Get router config
        $routerConfig = Config::getValue("Router");

        # Check router
        if(is_array($routerConfig))

            # Iteration router config
            foreach(["app", "api", "asset"] as $type)

                # Check if type isset and not empty
                if(array_key_exists($type, $routerConfig) && is_array($routerConfig[$type]) && !empty($routerConfig[$type])){

                    # Get app prefix
                    $prefix = (isset($routerConfig["prefix"][$type]) && $routerConfig["prefix"][$type]) 
                        ? trim($routerConfig["prefix"][$type], "/") 
                        : "";

                    # Iteration routers
                    foreach($routerConfig[$type] as $router){

                        # Set middlewares
                        $middlewares = [];

                        # Get middleware
                        if(isset($router["middleware"])){

                            # If string
                            if(is_string($router["middleware"]))

                                # Convert to array
                                $router["middleware"] = [$router["middleware"]];

                            # Set $middleware
                            $middlewares = $router["middleware"];

                        }

                        # Check middlewares
                        if(empty($middlewares))

                            # Continue 
                            continue;
                        
                        # Check patterns is array
                        if(!is_array($router["patterns"])) 

                            # Convert it to array
                            $router["patterns"] = [$router["patterns"]];

                        # Iteration of patterns
                        foreach($router["patterns"] as $pattern){

                            # Clean pattern
                            $pattern = trim($pattern, "/");

                            # Get pattern
                            $pattern = (isset($router["prefix"])) ?
                                (
                                    $router["prefix"] ?
                                        "/".trim($router["prefix"], "/")."/$pattern/" :
                                            "/$pattern/"
            
                                ) :
                                    "/$prefix/$pattern/"
                            ;

                            # Clean "//" in pattern
                            $pattern = str_replace("//", "/", $pattern);

                            # Iteration middlewares
                            foreach($middlewares as $middleware)

                                # Check if in middlewares collection
                                if($middleware && array_key_exists($middleware, $middlewaresCollection))

                                    # Push in result
                                    $result[$pattern][] = $middlewaresCollection[$middleware];

                        }

                }

            }

        # Return result
        return $result;

    }

    /**
     * Get All From Api
     * 
     * @return array
     */
    public static function getAllFromApi():array {

        # Set result
        $result = [];

        # Get middleware
        $middlewares = Config::getValue("Middleware");

        # Set middlewares collection
        $middlewaresCollection = [];

        # Check middlewares
        if(is_array($middlewares) && !empty($middlewares))

            # Iteration
            foreach($middlewares as $middleware)

                # Check script and name
                if(is_string($middleware["name"]) && $middleware["name"] && is_string($middleware["script"]) && $middleware["script"] && is_callable($middleware["script"]))

                    # Push in middlewares collection
                    $middlewaresCollection[$middleware["name"]] = $middleware["script"];

        # Get router config
        $routerConfig = Config::getValue("Api");

        # Check router
        if(is_array($routerConfig))

            # Iteration router config
            foreach(["v2"] as $version)

                # Check if type isset and not empty
                if(array_key_exists($version, $routerConfig) && is_array($routerConfig[$version]["routers"] ?? false) && !empty($routerConfig[$version]["routers"]) && ($routerConfig[$version]["enable"] ?? false)){

                    # Get app prefix
                    $prefix = (isset($routerConfig[$version]["prefix"]) && $routerConfig[$version]["prefix"]) 
                        ? trim($routerConfig[$version]["prefix"], "/") 
                        : "";

                    # Iteration routers
                    foreach($routerConfig[$version]["routers"] as $router){

                        # Set middlewares
                        $middlewares = [];

                        # Get middleware
                        if(isset($router["middleware"])){

                            # If string
                            if(is_string($router["middleware"]))

                                # Convert to array
                                $router["middleware"] = [$router["middleware"]];

                            # Set $middleware
                            $middlewares = $router["middleware"];

                        }

                        # Check middlewares
                        if(empty($middlewares))

                            # Continue 
                            continue;
                        
                        # Check patterns is array
                        if(!is_array($router["patterns"])) 

                            # Convert it to array
                            $router["patterns"] = [$router["patterns"]];

                        # Iteration of patterns
                        foreach($router["patterns"] as $pattern){

                            # Clean pattern
                            $pattern = trim($pattern, "/");

                            # Get pattern
                            $pattern = (isset($router["prefix"])) ?
                                (
                                    $router["prefix"] ?
                                        "/".trim($router["prefix"], "/")."/$pattern/" :
                                            "/$pattern/"
            
                                ) :
                                    "/$prefix/$pattern/"
                            ;

                            # Push in pattern
                            $pattern = "/api$pattern";

                            # Clean "//" in pattern
                            $pattern = str_replace("//", "/", $pattern);

                            # Iteration middlewares
                            foreach($middlewares as $middleware)

                                # Check if in middlewares collection
                                if($middleware && array_key_exists($middleware, $middlewaresCollection))

                                    # Push in result
                                    $result[$pattern][] = $middlewaresCollection[$middleware];

                        }

                }

            }

        # Return result
        return $result;

    }

}