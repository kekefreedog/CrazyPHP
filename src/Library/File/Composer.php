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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\Json;
use CrazyPHP\App\Create;

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

    # Default properties of composer
    public const DEFAULT_PROPERTIES = [
        # Name
        "name"          =>  Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  Create::REQUIRED_VALUES[1],
        # Version
        "version"       =>  [
            "name"          =>  "Version",
            "description"   =>  "Version of your crazy project",
            "type"          =>  "VARCHAR",
        ],
        # Type
        "type"          =>  Create::REQUIRED_VALUES[4],
        # Keywords
        "keywords"      =>  [
            "name"          =>  "Keywords",
            "description"   =>  "Keywords about your app",
            "type"          =>  "ARRAY",
        ],
        # Homepage
        "homepage"      =>  Create::REQUIRED_VALUES[5],
        # Readme
        "readme"        =>  [
            "name"          =>  "Read Me",
            "description"   =>  "Path of the read me",
            "type"          =>  "VARCHAR",
        ],
        # Time
        "time"          =>  [
            "name"          =>  "Read Me",
            "description"   =>  "Path of the read me",
            "type"          =>  "DATETIME",
        ],
        # Licence
        "license"       =>  [
            "name"          =>  "Licence",
            "description"   =>  "Licence of your app",
            "type"          =>  "VARCHAR",
        ],
        # Author name
        "authors__name" =>  Create::REQUIRED_VALUES[2],
        # Author name
        "authors__email"=>  Create::REQUIRED_VALUES[3],
        # Authors
        "authors"       =>  [
            "name"          =>  "authors",
            "description"   =>  "Authors of your app",
            "type"          =>  "ARRAY",
        ],
        # Support
        "support"       =>  [
            "name"          =>  "Support",
            "description"   =>  "Support information of your app",
            "type"          =>  "ARRAY",
        ],
        # Funding
        "funding"       =>  [
            "name"          =>  "Funding",
            "description"   =>  "Funding information of your app",
            "type"          =>  "ARRAY",
        ],
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
     * Set value in composer.json from array :
     * 1. {parameter:"value",...}
     *
     * @param array $values Values to put on composer.json
     * @param string $file File composer.json
     * @return array
     */
    public static function set(array $values = [], string $file = "composer.json"):array {

        # Set result
        $result = [];

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # Process value
        self::process($values);

        # Set values in composer.json
        $result = Json::set($file, $values, true, true);

        # Return result
        return $result;

    }
    
    /**
     * Read value in composer.json
     *
     * @param string  $values Values to update on composer.json
     * @param string $createIfNotExists create parameter if doesn't exists
     * @param string $file File composer.json
     * @return array
     */
    public static function update(array $values = [], bool $createIfNotExists = false, string $file = "composer.json"):array{

        # Set result
        $result = true;

        # Process value
        self::process($values);

        # Set values in composer.json
        $result = Json::set(self::PATH[$file], $values, true);

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

    /**
     * Process value
     * 
     * Process value for composer.json
     *
     * @param array $inputs Values to process for composer.json
     * @return void
     */
    public static function process(array &$inputs = []):void{

        # Check name
        if(isset($inputs["name"]) && isset($inputs["authors"][0]["name"]))

            # Clean name
            $inputs["name"] = 
                Process::clean($inputs["authors"][0]["name"]).
                "/".
                Process::clean($inputs["name"])
            ;

    }

}