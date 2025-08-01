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
     * And
     * 
     * Helper that renders the block if **both** of the given values
     * are truthy. If an inverse block is specified it will be rendered
     * when falsy. Works as a block helper, inline helper or
     * subexpression.
     *
     * ```handlebars
     * <!-- {great: true, magnificent: true} -->
     * {{#and great magnificent}}A{{else}}B{{/and}}
     * <!-- results in: 'A' -->
     * ```
     * 
     * @return boolean
     */
    public static function and($a, $b, $option) {

        # Check arguments are equivalent
        return ($a && $b) 
            ? $option["fn"]() 
            : $option["inverse"]()
        ;

    }

    /**
     * Or
     * 
     * Block helper that renders a block if **any of** the given values
     * is truthy. If an inverse block is specified it will be rendered
     * when falsy.
     *
     * ```handlebars
     * {{#or a b c}}
     *   If any value is true this will be rendered.
     * {{/or}}
     * ```
     * 
     * @return boolean
     */
    public static function or($a, $b, $option) {

        # Check arguments are equivalent
        return ($a || $b) 
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
     * First
     * 
     * Returns the first item, or first `n` items of an array.
     *
     * ```handlebars
     * {{first "['a', 'b', 'c', 'd', 'e']" 2}}
     * <!-- results in: '["a", "b"]' -->
     * ```
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function first($value, $n) {

        # Check if array
        if(is_array($value))

            # Return length
            return array_slice($value, 0, $n);

        else

            # Return 0
            return "";
        
    }

    /**
     * Timecode To Frame
     * 
     * Converts a timecode (TC) to the corresponding frame number if valid.
     * Returns the original timecode string if the input format or fps is invalid.
     *
     * @param string $tc The timecode in the format "HH:MM:SS:FF" where HH = hours, MM = minutes, SS = seconds, FF = frames.
     * @param int $fps The frames per second (fps) of the video.
     * @return int|string The corresponding frame number if valid, otherwise the original `$tc` string.
     */
    public static function timecodeToFrame($tc, $fps) {

        # Check tx and fps
        if(!$fps|| !$tc)

            # Return false
            return $tc;

        # Check fps
        if(is_numeric($fps) && intval($fps) > 0)

            # Set fps
            $fps = intval($fps);

        else

            # Return tc
            return $tc;

        # Regular expression to match the TC format "HH:MM:SS:FF"
        $tcRegex = '/^\d{2}:\d{2}:\d{2}:\d{2}$/';

        # Validate timecode format and fps
        if(!$tc || !preg_match($tcRegex, $tc))
            
            # Return the original timecode string if invalid
            return $tc;

        # Split the timecode into its components (HH:MM:SS:FF)
        list($hours, $minutes, $seconds, $frames) = array_map('intval', explode(':', $tc));

        # Additional validation on the components
        if(
            $minutes < 0 || $minutes >= 60 ||
            $seconds < 0 || $seconds >= 60 ||
            $frames < 0 || $frames >= $fps
        )

            # Return the original timecode string if components are invalid
            return $tc; 

        # Convert the timecode to the total number of frames
        $totalFrames = (
            $hours * 3600 * $fps) +
            ($minutes * 60 * $fps) +
            ($seconds * $fps) +
            $frames
        ;

        return $totalFrames;

    }

    /**
     * First
     * 
     * Returns the last item, or last `n` items of an array or string.
     * Opposite of [first](#first).
     *
     * ```handlebars
     * <!-- var value = ['a', 'b', 'c', 'd', 'e'] -->
     *
     * {{last value}}
     * <!-- results in: ['e'] -->
     *
     * {{last value 2}}
     * <!-- results in: ['d', 'e'] -->
     *
     * {{last value 3}}
     * <!-- results in: ['c', 'd', 'e'] -->
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function last($value, $n) {

        # Check if array
        if(is_array($value)){

            # Ensure $n is positive
            $n = max(0, $n);

            # Return the last `n` items of the array
            return array_slice($value, -$n);

        }else

            # Return 0
            return "";
        
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
            return round(floatval($value));

        else

            # Return value
            return $value;
        
    }

    /**
     * Round Decimal
     * 
     * Returns the length of the given string or array.
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function roundDecimal($value) {

        # Check if array
        if(is_int($value) || is_float($value) || ctype_digit($value))

            # Return round value
            return round(floatval($value), 1);

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
     * Splut
     * 
     * Split string a by the given character b.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return mixed
     */
    public static function split($a, $b, $option) {

        # Check if both are strings
        if (is_string($a) && is_string($b))

            # Return concatenated string
            return $a 
                ? explode($b, $a)
                : $a
            ;

        # Return empty string
        return '';

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
            return "$a + $b";

        # Return empty string
        return '';

    }

    /**
     * Subtract
     * 
     * Return the difference of `a` by `b`.
     * 
     * @param a Value to compare
     * @param b Value to compare with
     * 
     * @return mixed
     */
    public static function subtract($a, $b, $option) {

        # Check if both are numbers
        if (is_numeric($a) && is_numeric($b))

            # Return sum, casting to numbers explicitly
            return (float)$a - (float)$b;

        # Check if both are strings
        if (is_string($a) && is_string($b))

            # Return concatenated string
            return "$a - $b";

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
     * Date Status Day
     * 
     * Define if date given is in the past, present or futur based on day
     * Date given YYYY-MM-DD
     * 
     * @param mixed $date
     * @param mixed options
     */
    public static function date_status_day($date, $option) {

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

    /**
     * Date Status Week
     * 
     * Define if date given is in the past, present or futur based on week
     * Date given YYYY-MM-DD
     * 
     * @param mixed $date
     * @param mixed options
     */
    public static function date_status_week($date, $option) {

        # Check input
        if (!is_string($date) || !strtotime($date))

            # Return the input if it is not a valid date string
            return $date;
    
        # Date instance for given date
        $givenDate = new DateTime($date);
        $givenDate->setTime(0, 0, 0);
    
        # Today instance
        $today = new DateTime();
        $today->setTime(0, 0, 0);
    
        # Calculate start of the current week (Sunday)
        $startOfWeek = clone $today;
        $startOfWeek->modify('this week');
    
        # Calculate end of the current week (Saturday)
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('+6 days');
    
        # Compare given date with start and end of the current week
        if($givenDate < $startOfWeek)

            # Date is before the current week
            return -1;
        
        elseif($givenDate > $endOfWeek)

            # Date is after the current week
            return 1;

        else

            # Date is in the current week
            return 0;

    }

    /**
     * Is Array
     * 
     * Block helper that renders a block if index gien is the last of the list given
     * 
     * @param $index Value to compare
     * @param $list Value to compare with
     * 
     * @return boolean
     */

    /**
     * Is Array
     * 
     * Returns true if `value` is an es5 array.
     *
     * ```handlebars
     * {{isArray "abc"}}
     * <!-- results in: false -->
     *
     * <!-- array: [1, 2, 3] -->
     * {{isArray array}}
     * <!-- results in: true -->
     * ```
     * 
     * @param $value The value to test.
     */
    public static function isArray($input, $option) {

        # Set result
        $result = is_array($input) 
            ? array_is_list($input) 
            : false
        ;

        # Set result
        return $result 
            ? $option['fn']($input)
            : $option['inverse']()
        ;

    }

    /**
     * Is Object
     * 
     * Return true if value is an object.
     * 
     * @param $value The value to test.
     */
    public static function isObject($input, $option) {

        # Set result
        $result = false;

        # Check input
        if(is_object($input))

            # Set result
            $result = true;

        else
        # Check associative array
        if(is_array($input)){

            # Get keys
            $keys = array_keys($input);

            # Set keys
            if($keys !== range(0, count($input) - 1)) 

                # Set result
                $result = true;

        }
        

        # Set result
        return $result 
            ? $option['fn']($input)
            : $option['inverse']()
        ;

    }

    /**
     * Is String
     * 
     * Return true if `value` is a string.
     *
     * ```handlebars
     * {{isString "foo"}}
     * <!-- results in:  'true' -->
     * ``
     * 
     * @param $input The value to test.
     * @param $option The value to test.
     */
    public static function isString($input, $option) {

        # Set result
        return is_string($input) 
            ? $option['fn']($input)
            : $option['inverse']()
        ;

    }

    /**
     * Color Hex Random
     * 
     * Return random color hex format
     * 
     * @param $input The value to test.
     * @param $option The value to test.
     */
    public static function colorHexRandom($option) {

        # Generate a random integer between 0 and 16777215 (0xFFFFFF)
        $randomColor = mt_rand(0, 16777215);
        
        // Convert to hexadecimal and ensure it is 6 characters long
        return sprintf("#%06X", $randomColor);

    }

    /**
     * Format Currency
     * 
     * Returns the string in formatted as currency
     * 
     * @param mixed $value Number to use
     * @param mixed $currency "dollar" or "euro"
     * @return string
     */
    public static function formatCurrency($value, $currency, $option) {

        # Set result
        $result = $value;

        # Set number
        $number = floatval($result);

        # Check currency
        if(!$currency || !is_string($currency))

            # Set currency
            $currency = "dollar";
        
        # Format based on the currency type
        switch (strtolower($currency)) {
            # Case euro
            case 'euro':
                $result = number_format($number, 2, ',', ' ') . ' €';
                break;
            # Default case dollar
            case 'dollar':
            default:
                $result = '$ ' . number_format($number, 2, ',', '.');
                break;
        }
    
        # Return result
        return $result;

    }

    /**
     * Days of month
     * 
     * Handlebars helper to get all days of a given month and year 
     * 
     * @param int $year  The year (e.g., 2025).
     * @param int $month The month (1 for January, 12 for December).
     * @return array An array of days in the format YYYY-MM-DD.
     */
    public static function daysOfMonth($year, $month, $option) {

        # Get current date
        $now = new DateTime();

        # Validate year
        $year = (is_int($year) && $year > 0) 
            ? $year 
            : (int)$now->format('Y')
        ;

        # Validate month
        $month = (is_int($month) && $month >= 1 && $month <= 12) 
            ? $month 
            : (int)$now->format('n')
        ;

        # Get the number of days in the given month and year
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Format the date as YYYY-MM-DD
            $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $days[] = $formattedDate;
        }
    
        return $days;

    }

    /**
     * Is Current Day
     * 
     * Returns true if value (2025-01-05) is the current day
     * 
     * @param input like 2025-01-12
     * @return boolean
     */
    public static function isCurrentDay($input, $option) {

        # Set result
        $result = is_string($input) && $input && $input === date('Y-m-d')
            ? $option['fn']()
            : $option['inverse']()
        ;

        # Return result
        return $result;

    }

    /**
     * Is Weekend
     * 
     * Returns true if value (2025-01-05) is the weekend
     * 
     * @param input like 2025-01-12
     * @return boolean
     */
    public static function isWeekend($input, $option) {

        # Set result
        $result = false;

        # Check input
        if(is_string($input) && $input){

            # Set timestamp
            $timestamp = strtotime($input);

            # Get week day 1 = Monday, 7 = Sunday
            $dayOfWeek = date('N', $timestamp);

            # Check if saturday of sunday
            if($dayOfWeek == 6 || $dayOfWeek == 7)

                # Set result
                $result = true;

        }

        # Set result
        return $result
            ? $option['fn']()
            : $option['inverse']()
        ;

    }

    /**
     * Date To Local Format
     * 
     * Return date to local format Vendredi 13 Janvier
     * 
     * @param mixed $input
     * @param mixed $locale exemple : 
     * @param mixed options
     */
    public static function dateToLocalFormat($input, $locale, $option) {

        # Set result
        $result = $input;

        # Check input
        if(is_string($input) && $input && is_string($locale) && $locale){

            # Set timestamp
            $timestamp = strtotime($input);

            # Define weekday and month names for supported locales
            $locales = [
                'en_US' => [
                    'weekdays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                ],
                'fr_FR' => [
                    'weekdays' => ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                    'months' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
                ],
                # Add more locales as needed
            ];

            # Fallback to 'en_US' if the locale is not supported
            $localeData = $locales[$locale] ?? $locales['en_US'];

            # 'w' gives the day index (0 = Sunday)
            $weekday = $localeData['weekdays'][date('w', $timestamp)]; 
            
            # Day of the month without leading zeros
            $day = date('j', $timestamp); 

            # 'n' gives 1-based month
            $month = $localeData['months'][date('n', $timestamp) - 1];

            # Combine and return the formatted date
            $result = "{$weekday} {$day} {$month}";

        }

        # Return result
        return $result;
        
    }

    /**
     * Date To Local Format Month Year 
     * 
     * Return date to local format Janvier 2025
     * 
     * @param mixed $input
     * @param mixed $locale exemple : 
     * @param mixed options
     */
    public static function dateToLocalFormatMonthYear($month, $year, $locale, $option) {

        # Set result
        $result = "";

        # Set input
        $input = "$year-$month-01";

        # Check input
        if(is_string($input) && $input && is_string($locale) && $locale){

            # Set timestamp
            $timestamp = strtotime($input);

            # Define weekday and month names for supported locales
            $locales = [
                'en_US' => [
                    'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                ],
                'fr_FR' => [
                    'months' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
                ],
                # Add more locales as needed
            ];

            # Fallback to 'en_US' if the locale is not supported
            $localeData = $locales[$locale] ?? $locales['en_US'];

            # 'n' gives 1-based month
            $month = $localeData['months'][date('n', $timestamp) - 1];

            # Set year
            $year = date("Y", $timestamp);

            # Combine and return the formatted date
            $result = "{$month} {$year}";

        }

        # Return result
        return $result;
        
    }

    /**
     * Date To Local Format Month Year 
     * 
     * Return date to local format Janvier 2025
     * 
     * @param mixed $input
     * @param mixed $locale exemple : 
     * @param mixed options
     */
    public static function dateToYYYYMMDD($year, $month, $day, $options = []) {
        
        // Create a DateTime object
        $date = new DateTime();
        
        // Set the year, month, and day
        $date->setDate($year, $month, $day); // No need to add 1 to $month since PHP months are 1-indexed
    
        // Format the year, month, and day as strings
        $formattedYear = $date->format('Y'); // Get the full year
        $formattedMonth = str_pad($date->format('n'), 2, '0', STR_PAD_LEFT); // Month as two digits
        $formattedDay = str_pad($date->format('j'), 2, '0', STR_PAD_LEFT); // Day as two digits
    
        // Return the formatted string
        return $formattedYear ."-". $formattedMonth ."-". $formattedDay;
    
    }

    /**
     * Background Gradient
     * 
     * Generate background color
     * 
     * Exemple: {{backgroundGradient colors smooth=true}}
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function backgroundGradient($colors, $options) {    
        
        # Set smooth
        $smooth = false;

        // Chech hash smooth
        if(isset($options['hash']['smooth']))

            # Set smooth
            $smooth = filter_var($options['hash']['smooth'], FILTER_VALIDATE_BOOLEAN);
    
        # Check colors
        if(!is_array($colors) || count($colors) === 0) 

            # Return empty string
            return '';
    
        # Check color lenght
        if(count($colors) === 1)

            # Return simple background
            return "background: rgb({$colors[0]});";
    
        # Check smotth
        if($smooth){

            # Set total
            $total = count($colors) - 1;

            # Array map
            $parts = array_map(function($color, $i) use ($total) {

                # Set pos
                $pos = ($i / $total) * 100;

                # Return style
                return "rgba($color) {$pos}%";

            }, $colors, array_keys($colors));

        # If not smooth
        }else{

            # Set lenght
            $len = count($colors);

            # Set parts
            $parts = array_map(function($color, $i) use ($len) {

                # Set start
                $start = ($i / $len) * 100;

                # Set end
                $end = $start + (100 / $len);

                # Return style
                return "rgba($color) {$start}%, rgba($color) {$end}%";

            }, $colors, array_keys($colors));

        }
    
        # Set gradient
        $gradient = implode(', ', $parts);

        # Return color
        return "background: linear-gradient(to bottom, $gradient);";
        
    }

    /**
     * Normalize
     * 
     * Returns the string in uppercase
     * 
     * @param mixed $value
     * @param mixed options
     */
    public static function normalize($value) {

        # Set result
        $input = $value;

        # Check if string
        if(is_string($input)){

            # Convert to UTF-8
            $input = mb_convert_encoding($input, 'UTF-8', mb_list_encodings());

            # Replace accented characters with ASCII equivalents
            $input = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $input);

            # Remove non-alphanumeric characters except space
            $input = preg_replace('/[^a-zA-Z0-9\s]/', '', $input);

            # Replace multiple spaces/tabs with a single space
            $input = preg_replace('/\s+/', '_', $input);

            # Lowercase and trim
            $input = strtolower(trim($input, '_'));

        }else
        # check if array
        if(is_array($input))

            # Iteration value
            foreach($input as &$v)

                # Check if is string
                if(is_string($v)){

                    # Convert to UTF-8
                    $v = mb_convert_encoding($v, 'UTF-8', mb_list_encodings());

                    # Replace accented characters with ASCII equivalents
                    $v = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v);

                    # Remove non-alphanumeric characters except space
                    $v = preg_replace('/[^a-zA-Z0-9\s]/', '', $v);

                    # Replace multiple spaces/tabs with a single space
                    $v = preg_replace('/\s+/', '_', $v);

                    # Lowercase and trim
                    $v = strtolower(trim($v, '_'));

                }

        # Return result
        return $input;
        
    }

    /**
     * Iterate Until
     * 
     * Injects a loop range into the template.
     * 
     * @param mixed $max
     * @param mixed options
     */
    public static function iterateUntil($max, $options) {

        # Get start at one
        $startAtOne = true;

        // Chech hash smooth
        if(isset($options['hash']['startAtOne']))

            # Set smooth
            $startAtOne = filter_var($options['hash']['startAtOne'], FILTER_VALIDATE_BOOLEAN);


        # Set start
        $start = $startAtOne ? 1 : 0;

        # Set buffer
        $buffer = '';

        # Set max
        $maxCalculated = 0;

        # Set maxCalculated
        if(is_array(($max))){

            # Set max
            $maxCalculated = count($max);

        }else
        # Check is numeric
        if(is_numeric($max)){

            # Set max
            $maxCalculated = is_int($max) ? $max : intval($max);

        }else
        # Check is string
        if(is_string($max)){

            # Set max
            $maxCalculated = strlen($max);

        }

        # Set count
        $count = $maxCalculated - $start + 1;

        # Loop
        for($i = $start; $i <= $maxCalculated; $i++) {

            # Set
            $currentIndex = $i - $start;

            # Execute block with current 'i'
            $buffer .= $options['fn']([
                'i' => $i,
                '@index' => $currentIndex,
                '@first' => $currentIndex === 0,
                '@last' => $currentIndex === ($count - 1),
            ]);

        }

        # Return buffer
        return $buffer;

    }

}