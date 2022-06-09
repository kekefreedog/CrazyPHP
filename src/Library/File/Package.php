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
use CrazyPHP\App\Create;

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

    /** Public Static Methods
     ******************************************************
     */
    
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

}