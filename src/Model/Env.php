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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;

/**
 * Config
 *
 * Methods for interacting with config file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Env{

    /** Public static methods
     ******************************************************
     */

    /**
     * Set
     * 
     * Set env, exemple input :
     * ```php
     * $input = [
     *  "app_root"      =>  "/sites/CrazyProject",
     *  "crazyphp_root" =>  "/sites/CrazyProject/vendor/kekefreedog/crazyphp"
     * ];
     * ```
     * 
     * @param array $input Input to process
     * 
     * @return void
     */
    public static function set(array $input = []):void {

        # Check input
        if(!empty($input))

            # Iteration input
            foreach($input as $k => $v){

                # Process key
                $k = Process::clean($k);

                # Check key
                if(!$k)

                    # Continue iteration
                    continue;

                # Add double underscores
                #$k = "__".strtoupper(trim($k, "_"))."__";
                $k = strtoupper(trim($k, "_"));

                # Define env constant
                #define($k, $v);

                # Defin env in global
                $GLOBALS[static::PREFIX][$k] = $v;

            }

    }

    /**
     * Get
     * 
     * Get env from name
     * 
     * Exemple `@app_root, `app_root`, `@crazyphp_root`, `crazyphp_root`
     * 
     * @param string $input Input to process
     * @param bool $nullIfNotExists Return null is not exists
     * 
     * @return
     */
    public static function get(string $input = "", bool $nullIfNotExists = false) {

        # Declare result
        $result = "";

        # Remove @
        $input = ltrim($input, "@");

        # Process input
        $input = strtoupper($input);

        # Check input
        if(!static::has($input) && !$nullIfNotExists)
                
            # New error
            throw new CrazyException(
                "No env variable match with \"$input\", please define it before !",
                500,
                [
                    "custom_code"   =>  "composer-002",
                ]
            );

        # Get globals
        $result = $GLOBALS[static::PREFIX][$input] ?? null;

        # Return
        return $result;

    }

    /**
     * Get All
     * 
     * Get All env stored
     * 
     * @return array
     */
    public static function getAll():array {

        # Set result
        $result = $GLOBALS[static::PREFIX];

        # Change case
        $result = Arrays::changeKeyCaseRecursively($result);

        # Return result
        return $result;

    }

    /**
     * Remove
     * 
     * Remove env from name
     * 
     * Exemple `@app_root, `app_root`, `@crazyphp_root`, `crazyphp_root`
     * 
     * @param string $input Input to remove
     * 
     * @return void
     */
    public static function remove(string $input = "", bool $nullIfNotExists = false):void {

        # Remove @
        $input = ltrim($input, "@");

        # Process input
        $input = strtoupper($input);

        # Check input
        if(static::has($input))

            # Get globals
            unset($GLOBALS[static::PREFIX][$input]);

    }

    /**
     * Has
     * 
     * @param string $input Input to process
     * 
     * @return bool
     */
    public static function has(string $input = ""):bool {

        # Declare result
        $result = false;

        # Check input
        if(!$input)
                
            # New error
            throw new CrazyException(
                "Input can't be an empty string !",
                500,
                [
                    "custom_code"   =>  "composer-001",
                ]
            );

        # Process input
        $input = strtoupper($input);

        # Remove @
        $input = ltrim($input, "@");

        # Get globals
        $result = isset($GLOBALS[static::PREFIX][$input]) ? true : false;

        # Return
        return $result;

    }

    /**
     * Reset
     * 
     * Reset all values stored in env
     * @param bool $keepRoots Keep all values finishing with "_root"
     * @return void
     */
    public static function reset(bool $keepRoots = false):void {

        # Check keep roots
        if(!$keepRoots)

            # Clean global var
            $GLOBALS[static::PREFIX] = [];

        else{

            # String to keep
            $strToKeep = "_root";

            # Check global values
            if(isset($GLOBALS[static::PREFIX]) && !empty($GLOBALS[static::PREFIX]))

                # Iteration of values
                foreach($GLOBALS[static::PREFIX] as $key => $values)

                    # Check if has a _root at the end of string
                    if(substr($key, -(strlen($strToKeep)), strlen($strToKeep)) != $strToKeep)

                        # Remove value
                        unset($GLOBALS[static::PREFIX][$key]);

        }

    }

    /** Public constants
     ******************************************************
     */

    /** @const string PREFIX used in global */
    public const PREFIX = "__CRAZY_APP";

    /** @const string REGEX Regex expression for select word starting after @ */
    public const REGEX = '/@[\w]+/';

}