<?php declare(strict_types=1);
/**
 * File
 *
 * TDB
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
use CrazyPHP\Library\File\Json;

/**
 * Composer
 *
 * Methods for interacting with Composer files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Composer{

    /** Constants
     ******************************************************
     */

    # Path of composer
    public const PATH = [
        "composer.json" =>  __DIR__."/../../../composer.json",
        "composer.lock" =>  __DIR__."/../../../composer.lock",
    ];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Read value in composer.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function read(string $parameter = "", string $file = "composer.json"):string {

        # Set result
        $result = "";

        # Check parameter in path
        if(!array_key_exists($file, Composer::PATH))
            return $result;

        # Get collection of file
        $fileCollection = Json::open(Composer::PATH[$file]);

        # Check value exist in collection
        if($fileCollection[$parameter] ?? false)

            # Set result
            $result = $fileCollection[$parameter];

        # Return result
        return $result;

    }
    
    /**
     * Set value in composer.json
     *
     * @param array $values Values to put on composer.json
     * @return string
     */
    public static function set(array $values = []):bool{

        # Set result
        $result = true;

        # Return result
        return $result;

    }
    
    /**
     * Read value in composer.json
     *
     * @param string  $values Values to update on composer.json
     * @return string
     */
    public static function update(array $values = [], bool $createIfNotExists = false):bool{

        # Set result
        $result = true;

        # Return result
        return $result;

    }
    
    /**
     * Delete value in composer.json
     *
     * @param string  $values Values to update on composer.json
     * @return string
     */
    public static function delete(array $values = []):bool{

        # Set result
        $result = true;

        # Return result
        return $result;

    }

}