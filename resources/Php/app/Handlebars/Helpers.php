<?php declare(strict_types=1);
/**
 * Handlebars
 * 
 * PHP version 8.1.2
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace App\Library\Handlebars;

/**
 * Dependances
 */
use ReflectionMethod;
use ReflectionClass;

/**
 * Helpers
 * 
 * App custim herlpers
 * 
 * @param a Value to compare
 * @param v Value to compare with
 * @return string
 */
class Helpers {

    /** Public methods | Get Helpers
     ******************************************************
     */

    /**
     * Get all helpers
     * 
     * Return the list of all helpers
     * 
     * @return array
     */
    public static function listArray():array {

        # Set result
        $result = [];

        # New reflection
        $reflection = new ReflectionClass(__CLASS__);

        # Get all static function
        $staticMethods = $reflection->getMethods(ReflectionMethod::IS_STATIC);

        # Get current function name
        $functionName = __FUNCTION__;

        # Remove current method
        $staticMethods = array_filter($staticMethods, function($method) use ($functionName) {
            return $method->name !== $functionName;
        });
        
        # Iteration of methods
        foreach ($staticMethods as $reflectionMethod)

            # Push method in result
            $result[$reflectionMethod->name] = $reflectionMethod->class."::".$reflectionMethod->name;

        # Return result
        return $result;

    }

    /** Public methods | Helpers
     ******************************************************
     */


    /**
     * Process
     * 
     * Exemple of custome helpers
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function process($value) {

        # Set result
        $result = $value;

        # Check if string
        if(is_string($result)){

            # Process value
            $result = $result 
                ? "$result process"
                : "process"
            ;

        }

        # Return result
        return $result;
        
    }

    /**
     * Condition
     * 
     * Exemple of condition
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return boolean
     */
    public static function condition($a, $b, $option) {

        # Check arguments are equivalent
        return $a == $b 
            ? $option["fn"]() 
            : $option["inverse"]()
        ;

    }

}