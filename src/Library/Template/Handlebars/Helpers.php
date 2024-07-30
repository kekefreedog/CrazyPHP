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
namespace CrazyPHP\Library\Template\Handlebars;

/**
 * Dependances
 */

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Form\Process;
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
     * Color Prefix
     * 
     * Add color prefix for convert material color to specific color propoerty
     * 
     * @param a Value to compare
     * @param v Value to compare with
     * @return string
     */
    public static function colorPrefix($a, $v) {

        # Set result
        $result = $a;

        # Check parameters 
        if(is_string($a) && is_string($v) && $a && $v){

            # Check if space in string a
            if(strpos($a, " ")!==false)

                # Set result
                $result = "$v-$result-$v";

            else

                # Set result
                $result = "$v-$result";

        }

        # Return result
        return $result;

    }

    /**
     * Expand Color Fill
     * 
     * @param mixed $color Color object
     * @param string $type
     * @return string
     */
    public static function expandColorFill(mixed $color, string $type=""):string {
    
        # Declare result
        $result = "";

        # Check if color is not an array or is null
        if (!is_array($color) || $color === null)

            # Return object
            return $result;

        # Check type
        if(!is_string($type))

            # Set type
            $type = "";
    
        # Default color
        $defaultFill = "grey darken-1";
        $defaultText = "white";

        # Set text and fill with defaults if not set
        $text = isset($color['text']) && $color['text'] ? $color['text'] : $defaultText;
        $fill = isset($color['fill']) && $color['fill'] ? $color['fill'] : $defaultFill;

        # Process fill with suffix then prefix for light mode
        $fillSuffix = is_string($fill) && is_string($type) && $fill && $type ? (strpos($fill, " ") !== false ? str_replace(" ", "-$type $type-", $fill) : $fill . "-$type") : $fill;
        $result .= is_string($fillSuffix) && is_string("light-mode") && $fillSuffix && "light-mode" ? (strpos($fillSuffix, " ") !== false ? "light-mode-$fillSuffix-light-mode" : "light-mode-$fillSuffix") : $fillSuffix;
        $result .= " ";

        # Process text with suffix then prefix for dark mode
        $textSuffix = is_string($text) && is_string($type) && $text && $type ? (strpos($text, " ") !== false ? str_replace(" ", "-$type $type-", $text) : $text . "-$type") : $text;
        $result .= is_string($textSuffix) && is_string("dark-mode") && $textSuffix && "dark-mode" ? (strpos($textSuffix, " ") !== false ? "dark-mode-$textSuffix-dark-mode" : "dark-mode-$textSuffix") : $textSuffix;
        $result .= " ";

        # Return result
        return $result;

    }

    /**
     * Expand Color Text
     * 
     * @param $color Color object
     * @param string $type
     * @return string
     */
    public static function expandColorText(mixed $color, string $type=""):string {
    
        # Declare result
        $result = "";

        # Check if color is not an array or is null
        if (!is_array($color) || $color === null)

            # Return object
            return $result;

        # Check type
        if(!is_string($type))

            # Set type
            $type = "";
    
        # Default color
        $defaultFill = "grey darken-1";
        $defaultText = "white";

        # Set text and fill with defaults if not set
        $text = isset($color['text']) && $color['text'] ? $color['text'] : $defaultText;
        $fill = isset($color['fill']) && $color['fill'] ? $color['fill'] : $defaultFill;

        # Process fill with suffix then prefix for light mode
        $textSuffix = is_string($text) && is_string($type) && $text && $type ? (strpos($text, " ") !== false ? str_replace(" ", "-$type $type-", $text) : $text . "-$type") : $text;
        $result .= is_string($textSuffix) && is_string("light-mode") && $textSuffix && "light-mode" ? (strpos($textSuffix, " ") !== false ? "light-mode-$textSuffix-light-mode" : "light-mode-$textSuffix") : $textSuffix;
        $result .= " ";

        # Process text with suffix then prefix for dark mode
        $fillSuffix = is_string($fill) && is_string($type) && $fill && $type ? (strpos($fill, " ") !== false ? str_replace(" ", "-$type $type-", $fill) : $fill . "-$type") : $fill;
        $result .= is_string($fillSuffix) && is_string("dark-mode") && $fillSuffix && "dark-mode" ? (strpos($fillSuffix, " ") !== false ? "dark-mode-$fillSuffix-dark-mode" : "dark-mode-$fillSuffix") : $fillSuffix;
        $result .= " ";

        # Return result
        return $result;

    }

    /**
     * Expand Color
     * 
     * @param $color Color
     * @param $inverse Without inverse, color is set as fill in light and text in dark. Just invert that fact
     * @return string
     */
    public static function expandColor(mixed $color, mixed $inverse = false, string $type="text"):string {
    
        # Declare result
        $result = "";

        # Check if color is not an array or is null
        if (!is_array($color) || $color === null)

            # Return object
            return $result;
    
        # Default color
        $defaultFill = "grey darken-1";
        $defaultText = "white";
    
        # Set text and fill with defaults if not set
        $text = isset($color['text']) && $color['text'] ? $color['text'] : $defaultText;
        $fill = isset($color['fill']) && $color['fill'] ? $color['fill'] : $defaultFill;
    
        # Retrieve bool value
        $isInverse = filter_var($inverse, FILTER_VALIDATE_BOOL) ? true : false;

        # If is not inverse
        if($isInverse){

            # Set fill as fill for light mode
            $result .= is_string($fill) && is_string("light-mode") && $fill && "light-mode" ? (strpos($fill, " ") !== false ? "light-mode-$fill-light-mode" : "light-mode-$fill") : $fill;
            $result .= " ";

            # Process text with suffix then prefix for light mode
            $textSuffix = is_string($text) && is_string($type) && $text && $type ? (strpos($text, " ") !== false ? str_replace(" ", "-$type $type-", $text) : $text . "-$type") : $text;
            $result .= is_string($textSuffix) && is_string("light-mode") && $textSuffix && "light-mode" ? (strpos($textSuffix, " ") !== false ? "light-mode-$textSuffix-light-mode" : "light-mode-$textSuffix") : $textSuffix;
            $result .= " ";

            # Process fill with suffix then prefix for dark mode
            $fillSuffix = is_string($fill) && is_string($type) && $fill && $type ? (strpos($fill, " ") !== false ? str_replace(" ", "-$type $type-", $fill) : $fill . "-$type") : $fill;
            $result .= is_string($fillSuffix) && is_string("dark-mode") && $fillSuffix && "dark-mode" ? (strpos($fillSuffix, " ") !== false ? "dark-mode-$fillSuffix-dark-mode" : "dark-mode-$fillSuffix") : $fillSuffix;
            $result .= " ";

            # Set text as fill for dark mode
            $result .= is_string($text) && is_string("dark-mode") && $text && "dark-mode" ? (strpos($text, " ") !== false ? "dark-mode-$text-dark-mode" : "dark-mode-$text") : $text;
            $result .= " ";

        }else{

            # Process fill with suffix then prefix for light mode
            $fillSuffix = is_string($fill) && is_string($type) && $fill && $type ? (strpos($fill, " ") !== false ? str_replace(" ", "-$type $type-", $fill) : $fill . "-$type") : $fill;
            $result .= is_string($fillSuffix) && is_string("light-mode") && $fillSuffix && "light-mode" ? (strpos($fillSuffix, " ") !== false ? "light-mode-$fillSuffix-light-mode" : "light-mode-$fillSuffix") : $fillSuffix;
            $result .= " ";

            # Set text as fill for light mode
            $result .= is_string($text) && is_string("light-mode") && $text && "light-mode" ? (strpos($text, " ") !== false ? "light-mode-$text-light-mode" : "light-mode-$text") : $text;
            $result .= " ";

            # Set fill as fill for dark mode
            $result .= is_string($fill) && is_string("dark-mode") && $fill && "dark-mode" ? (strpos($fill, " ") !== false ? "dark-mode-$fill-dark-mode" : "dark-mode-$fill") : $fill;
            $result .= " ";

            # Process text with suffix then prefix for dark mode
            $textSuffix = is_string($text) && is_string($type) && $text && $type ? (strpos($text, " ") !== false ? str_replace(" ", "-$type $type-", $text) : $text . "-$type") : $text;
            $result .= is_string($textSuffix) && is_string("dark-mode") && $textSuffix && "dark-mode" ? (strpos($textSuffix, " ") !== false ? "dark-mode-$textSuffix-dark-mode" : "dark-mode-$textSuffix") : $textSuffix;
            $result .= " ";

        };
    
        // Trim the result to remove any trailing space
        return trim($result)." ";
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
        return (
            (!$a && !$b) || 
            ($a == $b)
        ) 
            ? $option["fn"]() 
            : $option["inverse"]()
        ;

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

    /**
     * Resolve
     * 
     * Get realpath of path
     * 
     * @param mixed $a
     * @return string
     */
    public static function resolve(mixed $a):mixed {

        # Set result
        $result = $a;

        # Check if is file
        if(is_string($a) && $a && (is_dir($a) || is_file($a)))

            # Get real path
            $result = realpath($a);

        # Return result
        return $result;

    }

    /**
     * In Array
     * 
     * Block helper that renders the block if an array has the
     * given `value`. Optionally specify an inverse block to render
     * when the array does not have the given value.
     * 
     * @param mixed $array
     * @param mixed $value
     * @param mixed $options
     */
    public static function inArray(mixed $array, mixed $value, mixed $option) {

        # Check array
        if(is_array($array) && is_string($value) && $value && in_array($value, $array))

            # Return fn
            return $option["fn"]();

        # Else
        return $option["inverse"]();

    }

    /**
     * Length
     * 
     * Returns the length of the given string or array.
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function length($value) {

        # Check if array
        if(is_array($value))

            # Return length
            return count($value);

        else
        # If string
        if(is_string($value))

            # Return length
            return strlen($value);

        else

            # Return 0
            return 0;
        
    }

    /**
     * Round
     * 
     * Returns the length of the given string or array.
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function round($value) {

        # Check if array
        if(is_int($value) || is_float($value) || ctype_digit($value))

            # Return round value
            return round(intval($value));

        else

            # Return value
            return $value;
        
    }

    /**
     * Uppercase
     * 
     * Returns the string in uppercase
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function uppercase($value) {

        # Set result
        $result = $value;

        # Check if string
        if(is_string($value))

            # Set result
            $result = mb_strtoupper($result, 'UTF-8');

        else
        # check if array
        if(is_array($result))

            # Iteration value
            foreach($result as &$v)

                # Check if is string
                if(is_string($v))

                    # Uppercase
                    $v = mb_strtoupper($v);

        # Return result
        return $result;
        
    }

    /**
     * Lowercase
     * 
     * Returns the string in uppercase
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function lowercase($value) {

        # Set result
        $result = $value;

        # Check if string
        if(is_string($value))

            # Set result
            $result = mb_strtolower($result, 'UTF-8');

        else
        # check if array
        if(is_array($result))

            # Iteration value
            foreach($result as &$v)

                # Check if is string
                if(is_string($v))

                    # Uppercase
                    $v = mb_strtolower($v);

        # Return result
        return $result;
        
    }

    /**
     * Capitalize
     * 
     * Returns the string in capitalize
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function capitalize($value) {

        # Set result
        $result = $value;

        # Check if string
        if(is_string($value))

            # Set result
            $result = ucfirst($result);

        /* else
        # check if array
        if(is_array($result))

            # Iteration value
            foreach($result as &$v)

                # Check if is string
                if(is_string($v))

                    # Uppercase
                    $v = mb_strtoupper($v); */

        # Return result
        return $result;
        
    }

    /**
     * Join
     * 
     * Join all elements of array into a string, optionally using a
     * given separator.
     *
     * ```handlebars
     * <!-- array: ['a', 'b', 'c'] -->
     * {{join array}}
     * <!-- results in: 'a, b, c' -->
     *
     * {{join array '-'}}
     * <!-- results in: 'a-b-c' -->
     * ```
     * @param mixed $array `array`
     * @param string $separator The separator to use. Defaults to `, `.
     * @return string
     */
    public static function join(mixed $array, mixed $separator = ", "):string {

        # Check is is string
        if(is_string($array)) return $array;

        # Check if 
        if(!is_array($array)) return "";

        # Check separator
        $separator = is_string($separator) ? $separator : ', ';

        # Return
        return implode($separator, $array);

    }

    /**
     * Replace
     * 
     * Replace all occurrences of substring `a` with substring `b`.
     *
     * @param mixed $str
     * @param mixed $a
     * @param mixed $b
     * @return string
     */
    public static function replace(mixed $str, mixed $a, mixed $b):string {

        # Check is is string
        return is_string($str) && is_string($a) && is_string($b)
            ? str_replace($a, $b, $str)
            : $str
        ;

    }

    /**
     * Gt
     * 
     * Block helper that renders a block if a is greater than b.
     * If an inverse block is specified it will be rendered when falsy. You may optionally use the compare="" hash argument for the second value.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return boolean
     */
    public static function gt($a, $b, $option) {

        # Check arguments are equivalent
        return $a > $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * Gte
     * 
     * Block helper that renders a block if `a` is **greater than or
     * equal to** `b`.
     * If an inverse block is specified it will be rendered when falsy.
     * You may optionally use the `compare=""` hash argument for the
     * second value. 
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return boolean
     */
    public static function gte($a, $b, $option) {

        # Check arguments are equivalent
        return $a >= $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * Lt
     * 
     * Block helper that renders a block if `a` is **less than** `b`.
     * If an inverse block is specified it will be rendered when falsy.
     * You may optionally use the `compare=""` hash argument for the
     * second value.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return boolean
     */
    public static function lt($a, $b, $option) {

        # Check arguments are equivalent
        return $a < $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * Lte
     * 
     * Block helper that renders a block if `a` is **less than or
     * equal to** `b`.
     * If an inverse block is specified it will be rendered when falsy.
     * You may optionally use the `compare=""` hash argument for the
     * second value.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return boolean
     */
    public static function lte($a, $b, $option) {

        # Check arguments are equivalent
        return $a <= $b ? $option["fn"]() : $option["inverse"]();

    }

    /**
     * Add
     * 
     * Return the sum of `a` plus `b`.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return mixed
     */
    public static function add($a, $b, $option) {

        # Check if both are numbers
        if (is_numeric($a) && is_numeric($b))

            # Return sum, casting to numbers explicitly
            return (float)$a + (float)$b;

        # Check if both are strings
        if (is_string($a) && is_string($b))

            # Return concatenated string
            return $a . $b;

        # Return empty string
        return '';

    }

    /**
     * Is Last
     * 
     * Block helper that renders a block if index gien is the last of the list given
     * 
     * @param $index Value to compare
     * @param $list Value to compare with
     * 
     * @return boolean
     */
    public static function isLast($index, $list, $option) {

        # Set result
        $result = (
            is_numeric($index) && 
            is_array($list) && 
            $index === (count($list) - 1)
        )
            ? $option['fn']()
            : $option['inverse']()
        ;

        # Return result
        return $result;

    }

    /**
     * Divide
     * 
     * Divide a by b
     * 
     * @param mixed $a
     * @param mixed $b
     * @param mixed options
     */
    public static function divide($a, $b, $option) {

        # Check if array
        if(is_numeric($a) && is_numeric($b) && intval($b) != 0)

            # Return length
            return intval($a) / intval($b);

        else

            # Return string
            return "$a/$b";
        
    }

    /**
     * Multiply
     * 
     * Multiply a with b
     * 
     * @param mixed $a
     * @param mixed $b
     * @param mixed options
     */
    public static function multiply($a, $b, $option) {

        # Check if array
        if(is_numeric($a) && is_numeric($b))

            # Return length
            return intval($a) * intval($b);

        else

            # Return string
            return "$a*$b";
        
    }

    /**
     * Date Status
     * 
     * Multiply a with b
     * 
     * @param mixed $a
     * @param mixed $b
     * @param mixed options
     */
    public static function date_status($date, $option) {

        # Check input
        if(!is_string($date) || !strtotime($date))

            # Return the input if it is not a valid date string
            return $date;
    
        # Date instance
        $givenDate = new DateTime($date);

        # Today instance
        $today = new DateTime();

        # Set time to 00:00:00 to only compare dates
        $today->setTime(0, 0, 0);
    
        # Check past
        if($givenDate < $today)
            
            # Date is in the past
            return -1;
        
        # Furure
        elseif($givenDate > $today)
            
            # Date is in the future
            return 1;

        else

            # Date is today
            return 0;
        
    }

}