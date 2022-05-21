<?php declare(strict_types=1);
/**
 * New application
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\App;

/**
 * Create project
 *
 * TBD
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Create{

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "Crazy Project",
            "required"      =>  true,
            "process"       =>  ['trim']
        ],
        # Description
        [
            "name"          =>  "description",
            "description"   =>  "Description of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "My Crazy Web Application",
            "process"       =>  ['trim']
        ],
        # Author
        [
            "name"          =>  "author",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Type
        [
            "name"          =>  "type",
            "type"          =>  "VARCHAR",
            "default"       =>  "library",
            "select"        =>  [
                ""              =>  "Undifined",
                "library"       =>  "Library",
                "project"       =>  "project"
            ]
        ],
        # Homepage
        [
            "name"          =>  "homepage",
            "type"          =>  "VARCHAR",
            "default"       =>  "https://github.com/kekefreedog/CrazyPHP/",
            "process"       =>  ['http'],
            "select"        =>  [
                ""              =>  "Undifined",
                "library"       =>  "Library",
                "project"       =>  "project"
            ]
        ],
    ];

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run creation of project
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public function run(){

        # Get data

        # Init composer

        # Init package

        # Create structure

        # Create rooter

        # Create basic interface

    }

}