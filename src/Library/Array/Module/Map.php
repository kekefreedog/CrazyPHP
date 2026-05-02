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

}