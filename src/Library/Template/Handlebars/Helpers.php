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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Template\Handlebars;

/**
 * Dependances
 */

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Json;
use ReflectionMethod;
use ReflectionClass;
use Exception;

/**
 * Helpers
 * 
 * Add color suffix for convert material color to specific color propoerty
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
     * Http Status Code
     * 
     * Get Http Status Code Depending of code given
     * 
     * @param code Code of the error
     * @param what Wich parameter do you want, if you want all, set "*"
     * 
     * @return
     */
    public static function httpStatusCode($error, $what, $option) {

        # Set code
        $code = $error["code"] ?? false;

        # Get http status code
        # $http_status_code = Json::open("@crazyphp_root/resources/Json/http_status_code.json");
        $http_status_code = json_decode(file_get_contents("./../vendor/kzarshenas/crazyphp/resources/Json/http_status_code.json"), true);

        # Check if code is valid and in http_status_code
        if(!$code || !(array_key_exists(intval($code), $http_status_code))){

            # New Exception
            throw new CrazyException(
                "Http code \"$code\" given isn't valid",
                500,
                [
                    "custom_code"   =>  "handlebarsHelpers-001",
                ]
            );

            # Return
            return;

        }

        # Get 500 code status collection
        $collection500 = $http_status_code[500];

        # Declare result
        $result = [
            "code"              => $code ? $code : 500,
            "title"             => $http_status_code[$code]["title"] ?? $collection500["title"],
            "description"       => (isset($error["detail"]) && $error["detail"]) ?
                $error["detail"] :
                    ($http_status_code[$code]["description"] ?? $collection500["description"]),
            "icon-class"        => $http_status_code[$code]["icon"]["class"] ?? $collection500["icon"]["class"],
            "icon-text"         => $http_status_code[$code]["icon"]["text"] ?? $collection500["icon"]["text"],
            "primary-color"     => $http_status_code[$code]["color"]["primary"] ?? $collection500["color"]["primary"],
            "secondary-color"   => $http_status_code[$code]["color"]["secondary"] ?? $collection500["color"]["secondary"]
        ];

        # Check what
        if(
            (
                !$what || 
                !array_key_exists($what, $result)
            ) &&
            $what != "*"
        ){

            # New Exception
            throw new CrazyException(
                "Http code \"$code\" given isn't valid",
                500,
                [
                    "custom_code"   =>  "handlebarsHelpers-001",
                ]
            );

            # Return
            return;

        }

        # Check if what is valid
        return $what == "*" ? $option["fn"]($result) : $option["fn"]($result[$what]);

    }

    /**
     * Color Suffix
     * 
     * Add color suffix for convert material color to specific color propoerty
     * 
     * @param a Value to compare
     * @param v Value to compare with
     * @return string
     */
    public static function colorSuffix($a, $v) {

        # Set result
        $result = $a;

        # Check parameters 
        if(is_string($a) && is_string($v) && $a && $v){

            # Check if space in string a
            if(strpos($a, " ")!==false)

                # Set result
                $result = str_replace(" ", "-$v $v-", $a);

            else

                # Set result
                $result .= "-$v";

        }

        # Return result
        return $result;

    }

    /**
     * Color Suffix
     * 
     * Add color suffix for convert material color to specific color propoerty
     * 
     * @param $a Color
     * @param $v Suffix
     * @param $t Theme
     * @return string
     */
    public static function colorThemeSuffix($a, $v, $theme) {

        # Set result
        $result = $a;

        # Check theme
        if(is_string($theme) && is_string($a) && $theme && $a){

            # Set first
            $first = "$theme-mode-$a";

            # Set second
            $second = "$a-$theme-mode";

            # Set result
            $result = $first;

        }

        # Check parameters 
        if(is_string($a) && is_string($v) && $a && $v){

            # Check if space in string a
            if(strpos($a, " ")!==false)

                # Set result
                $result = "$first-$v $v-$second";

            else

                # Set result
                $result = "$first-$v";

        }

        # Return result
        return $result;

    }

    /**
     * Is
     * 
     * Block helper that renders a block if a is equal to b. 
     * If an inverse block is specified it will be rendered when falsy. 
     * Similar to eq but does not do strict equality.
     * 
     * @param a Value to compare
     * @param v Value to compare with
     * 
     * @return boolean
     */
    public static function is($a, $b, $option) {

        # Check arguments are equivalent
        return $a == $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * Isn't
     * 
     * Block helper that renders a block if a is not equal to b. 
     * If an inverse block is specified it will be rendered when falsy. 
     * Similar to unlessEq but does not use strict equality for comparisons.
     * 
     * @param a Value to compare
     * @param v Value to compare with
     * 
     * @return boolean
     */
    public static function isnt($a, $b, $option) {

        # Check arguments aren't equivalent
        return $a != $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * JSON stringify
     * 
     * Stringify an object using JSON.stringify.
     * 
     * @param a Object to stringify
     * 
     * @return string
     */
    public static function JSONstringify($a){

        # Generate json
        $result = "";

        # Try to create json
        try{

            # Encode string to json
            $result = json_encode($a);

        }catch(Exception $e){

            # Set error_clear_last
            $result = $e->getMessage();

        }

        # Return result
        return $result;

    }
    

}