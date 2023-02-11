<?php declare(strict_types=1);
/**
 * Model
 *
 * Classe for define framework models
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache;
use CrazyPHP\Model\Env;

/**
 * Context
 *
 * Methods for interacting with context of current route
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Context{

    /** Public static methods | Context
     ******************************************************
     */

    /**
     * Get
     * 
     * Get context
     * 
     * @return array
     */
    public static function get(string $key = ""):array|string|bool|int|null {
        
        $bk = $key;

        # Set result
        $result = null;

        $temp = $key;

        # Get context
        $cursor = $GLOBALS[static::PREFIX] ?? [];

        # Change case
        $cursor = Arrays::changeKeyCaseRecursively((array)$cursor, CASE_LOWER);

        # Parse where
        $key = str_replace(self::SEPARATOR, "___", $key);

        # Explode where 
        $keys = $key ? explode("___", $key) : $key;

        # Check config file
        if(!empty($keys)){

            # Iteration filedata
            $i=0;while(isset($keys[$i])){

                # Set current key
                $currentKey = strtolower($keys[$i]);

                # Check current key exists
                if(isset($cursor[$currentKey]))

                    # Set cursor
                    $cursor = $cursor[$currentKey];

                else

                    # Stop function
                    return $result;

            $i++;}

        }

        # Set result
        $result = $cursor;

        # Return result
        return $result;

    }

    /**
     * Set
     * 
     * Set values in context
     * 
     * @param string|null|array|bool $what What content you want to put in context
     * @param string $where Where do you want to put it (if empty, go to the root of context)
     * @param bool $mergeValues Merge values if is array content
     * @param bool $createIfNotExists Create parameter if not exisiting
     * @return void
     */
    public static function set(string|null|array|bool $what = "", string $where = "", bool $mergeValues = false, bool $createIfNotExists = true):void {

        # Parse where
        $where = str_replace(self::SEPARATOR, "___", $where);

        # Explode where 
        $keys = explode("___", $where);

        # Get context
        $context = $GLOBALS[static::PREFIX] ?? [];

        # Set cursor
        $cursor = &$context;

        # Check config file
        if(!empty($keys))

            # Iteration filedata
            $i=0;while(isset($keys[$i])){

                # Set current key
                $currentKey = strtoupper($keys[$i]);

                # Check current key exists
                if(isset($cursor[$currentKey]) || $createIfNotExists)

                    # Set cursor
                    $cursor = &$cursor[$currentKey];

                else

                    # Stop function
                    return;

            $i++;}

        # Check if what is array 
        if(is_array($what))

            # Change case of what
            $what = Arrays::changeKeyCaseRecursively($what, CASE_UPPER);

        # Check if what and cursor content are array
        if(is_array($what) && is_array($cursor) && $mergeValues){

            # Merge arrays
            $cursor = Arrays::mergeMultidimensionalArrays($createIfNotExists, $cursor, $what);

        }else{

            # Set value
            $cursor = $what;

        }

        # Case
        $context = Arrays::changeKeyCaseRecursively($context, CASE_UPPER);

        # Fill context
        $GLOBALS[static::PREFIX] = $context;

    }

    /**
     * Update
     * 
     * @param string|null|array|bool $what What content you want to put in context
     * @param string $where Where do you want to put it (if empty, go to the root of context)
     * @return void
     */
    public static function update(string|null|array|bool $what = "", string $where = ""):void {

        static::set($what, $where, true, false);

    }

    /** Public static methods | Routes
     ******************************************************
     */

    /**
     * Get Current Route
     * 
     * @param $attributes Optionnal specific attributes you want load
     * @return
     */
    public static function getCurrentRoute(string|array|null $attributes = null) {

        # Check current
        if(!isset($GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"]))
                
            # New error
            throw new CrazyException(
                "Current route hasn't been defined in context yet", 
                500,
                [
                    "custom_code"   =>  "context-001",
                ]
            );

        # Set result
        $result = $GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"];

        # Check attributes
        if($attributes === null || !$attributes || empty($attributes))

            # Return result
            return $result;

        # Check attributes
        if(is_string($attributes)){

            # Check given attribute
            if(isset($GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"][$attributes]))

                # Set result
                $result = $GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"][$attributes];

            else

                # Set null
                $result = null;

        }else{

            # Clean $result
            $result = [];

            # Iteration attributes
            foreach($attributes as $attribute)

                # Set attribute
                $result[str_replace([".", "/"], "___", $attribute)] = self::get("routes.current.$attribute");

            # Expand result
            $result = Arrays::stretch($result, "___");

        }

        # Return result
        return $result;

    }

    /**
     * Set Current Route
     * 
     * @param string $routeName Name of the current route
     * @return void
     */
    public static function setCurrentRoute(string $routeName = ""):void {

        # Check route name
        if(!$routeName)

            # Return
            return;

        # Route collection
        $data = Router::getByName($routeName);

        # Case change
        $data = Arrays::changeKeyCaseRecursively($data, CASE_UPPER);

        # Fill context
        $GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"] = $data;

    }

    /** Public static methods | Parameters
     ******************************************************
     */

    /**
     * Get Parameters
     * 
     * @param string|array $name Name of the parameter you are looking for
     * @return string|array|null
     */
    public static function getParameters(string|array $name = ""):string|array|null {

        # Set result
        $result = null;

        # Check name
        if(!$name || empty($name))

            # Set result
            $result = self::get("routes.current.parameters");

        # Check is string
        if(is_string($name))

            # Set result
            $result = self::get("routes.current.parameters.$name");

        # If array
        else

            # Iteration of name
            foreach($name as $v)

                # Check v
                if($v)

                    # Set result
                    $result[$v] = self::get("routes.current.parameters.$v");

        # Return result
        return $result;

    }

    /** Public Constants
     ******************************************************
     */

    /** @const PREFIX */
    public const PREFIX = "__CRAZY_CONTEXT";

    /** @const SEPARATOR */
    public const SEPARATOR = ["/", "."];

}