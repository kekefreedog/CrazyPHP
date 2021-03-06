<?php declare(strict_types=1);
/**
 * File
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/** Dependances
 * 
 */

/**
 * Config
 *
 * Methods for interacting with config file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Config{

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get single of multiple config 
     * 
     * @param string|array $input Name of config(s)
     * 
     * @return array
     */
    public static function get(string|array $input = "") :array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Has
     * 
     * Get single file of parameter exists
     * 
     * @param string $input Name of config(s)
     * 
     * @return array
     */
    public static function has(string $input = "") :bool {

        # Declare result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Set
     * 
     * Set value in config
     * 
     * @param string $input Name of config(s)
     * @param any $data Data to put inside parameter
     * 
     * @return void
     */
    public static function set(string $input = "", $data = null) :void {

        # Return result
        return;

    }

    /**
     * Update
     * 
     * Update value in config
     * 
     * @param string $input Name of config(s)
     * @param any $data Data to put inside parameter
     * @param bool $setValueIFNotExits Set value if not exists
     * 
     * @return void
     */
    public static function update(string $input = "", $data = null, bool $setValueIFNotExits = false) :void {

        # Return result
        return;

    }

    /**
     * Delete
     * 
     * Delete value in config
     * 
     * @param string $input Name of config(s) to delete
     * 
     * @return void
     */
    public static function delete(string $input = "") :void {

        # Return result
        return;

    }

}