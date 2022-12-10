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
        
        # Set result
        $result = null;

        # Get context
        $cursor = $GLOBALS[static::PREFIX] ?? [];

        # Parse where
        $key = str_replace(self::SEPARATOR, "___", $key);

        # Explode where 
        $keys = $key ? explode("___", $key) : $key;

        # Check config file
        if(!empty($keys)){

            # Iteration filedata
            $i=0;while(isset($keys[$i])){

                # Set current key
                $currentKey = strtoupper($keys[$i]);

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

        # Check if what and cursor content are array
        if(is_array($what) && is_array($cursor) && $mergeValues)

            # Merge arrays
            $cursor = Arrays::mergeMultidimensionalArrays(true, $cursor, $what);

        else

            # Set value
            $cursor = $what;

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

    /** Public Constants
     ******************************************************
     */

    /** @const PREFIX */
    public const PREFIX = "__CRAZY_CONTEXT";

    /** @const SEPARATOR */
    public const SEPARATOR = ["/", "."];

}