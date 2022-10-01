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

/**
 * Dependances
 */
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\App\Create;

/**
 * Package
 *
 * Methods for interacting with Npm files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Package{

    /** Constants
     ******************************************************
     */

    # Path of composer
    public const PATH = [
        "package.json" =>  __DIR__."/../../../package.json",
        "package-lock.json" =>  __DIR__."/../../../package-lock.json",
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
        # Keywords
        "keywords"      =>  [
            "name"          =>  "Keywords",
            "description"   =>  "Keywords about your app",
            "type"          =>  "ARRAY",
        ],
        # Homepage
        "homepage"      =>  Create::REQUIRED_VALUES[5],
        # Licence
        "license"       =>  [
            "name"          =>  "Licence",
            "description"   =>  "Licence of your app",
            "type"          =>  "VARCHAR",
        ],
        # Author name
        "authors_name" =>  [
            "name"          =>  "authors_name",
            "description"   =>  "Author of this crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Author name
        "authors_email"=>  [
            "name"          =>  "authors_email",
            "description"   =>  "Email of the crazy author",
            "type"          =>  "VARCHAR",
            "default"       =>  "crazy@person.com",
            "required"      =>  true,
            "process"       =>  ['trim'],
            "validate"      =>  ['email'],
        ],
        # Authors
        "authors"       =>  [
            "name"          =>  "authors",
            "description"   =>  "Authors of your app",
            "type"          =>  "ARRAY",
        ],
        # Funding
        "funding"       =>  [
            "name"          =>  "Funding",
            "description"   =>  "Funding information of your app",
            "type"          =>  "ARRAY",
        ],
    ];

    # Default value
    const DEFAULT_VALUE = [
    ];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Read value in package.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function create(string $path):array{

        # Declare result
        $result = [];

        # Check parameter in path
        if(array_key_exists($path, self::PATH))

            # Update path
            $path = self::PATH[$path];

        # Check if file already exists
        if(!file_exists($path))

            # Get collection of file
            $result = Json::create($path, self::DEFAULT_VALUE);
        
        # Return result
        return $result;

    }

    /**
     * Read value in package.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function read(string $parameter = "", string $file = "package.json"):string {

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # update
        return Composer::read($parameter, $file);

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
    public static function set(array $values = [], string $file = "package.json"):array {

        # Set result
        $result = [];

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # Process value
        self::process($values);

        # Set values in package.json
        $result = Json::set($file, $values, true);

        # Return result
        return $result;

    }
    
    /**
     * Read value in package.json
     *
     * @param string  $values Values to update on composer.json
     * @param string $createIfNotExists create parameter if doesn't exists
     * @param string $file File composer.json
     * @return array
     */
    public static function update(array $values = [], bool $createIfNotExists = false, string $file = "composer.json"):array{

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # update
        return Composer::update($values, $createIfNotExists, $file);

    }
    
    /**
     * Delete value in package.json
     *
     * @param string  $values Values to update on composer.json
     * @return string
     */
    public static function delete(array $values = []):bool{

        # Delete
        return (bool) Composer::delete($values);

    }
    
    /**
     * Adapt Create Inputs
     * 
     * Adapt create inputs for package.json
     * 
     * @param array $inputs Input to process
     * @return void
     */
    public static function adaptCreateInputs(array &$inputs = []):void {

        # Table of conversion
        $conversionCollection = [
            "authors__name"     =>  "authors_name",
            "authors__email"    =>  "authors_email"
        ];

        # Check inputs
        if(!empty($inputs))

            # Iteration of conversionCollection
            foreach($conversionCollection as $search => $replacement)

                # if search
                if($inputs[$search] ?? false)

                    # Replace name
                    $inputs[$search] = $replacement;

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
        if(isset($inputs["name"]))

            # Clean name
            $inputs["name"] = Process::clean($inputs["name"])
            ;

    }

}