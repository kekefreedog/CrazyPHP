<?php declare(strict_types=1);
/**
 * Module
 *
 * Sub class of module
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Array\Module;

/**
 * Dependances
*/
use ReflectionMethod;
use ReflectionClass;

/**
 * Map
 *
 * Map utilities class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
abstract class Map {

    /** Private static parameters
     ******************************************************
     */

    /** @param array $_simpleCache */
    private static array $_simpleCache = [];

    /** Public static class
     ******************************************************
     */

    /**
     * Get Methods
     */
    public static function getMethodsAlias():array {

        # Get class late static binding
        $class = static::class; 

        # Get reflection
        $reflection = new ReflectionClass($class);

        # Set result
        $result = [];

        # Iteration method
        foreach($reflection->getMethods(ReflectionMethod::IS_STATIC) as $method) {

            # Only methods declared in THIS class
            if($method->getDeclaringClass()->getName() !== $class) continue;

            # Only public (optional)
            if(!$method->isPublic()) continue;

            # Get name
            $name = $method->getName();

            # Set result
            $result[$name] = $class.'::'.$name;

        }

        # Return result
        return $result;

    }

    /** Private static class
     ******************************************************
     */

    /**
     * Set Cache
     * 
     * @param string $keyOrHash
     * @param mixed $data
     * @return void
     */
    protected static function _setCache(string $keyOrHash, mixed $data):void {

        # Check input
        if($keyOrHash && $data)

            # Push into cache
            static::$_simpleCache[$keyOrHash] = $data;

    }

    /**
     * Get Cache
     * 
     * @param string $keyOrHash
     * @return mixed
     */
    protected static function _getCache(string $keyOrHash):mixed {

        # Set result null
        $result = null;

        # Check input
        if(array_key_exists($keyOrHash, static::$_simpleCache))

            # Push into cache
            $result = static::$_simpleCache[$keyOrHash];


        # Return result
        return $result;

    }

}