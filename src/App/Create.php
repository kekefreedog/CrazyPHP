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
            "description"   =>  "Author of this crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Type
        [
            "name"          =>  "type",
            "description"   =>  "Type of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "library",
            "select"        =>  [
                ""              =>  "Undifined",
                "library"       =>  "Library",
                "project"       =>  "Project"
            ]
        ],
        # Homepage
        [
            "name"          =>  "homepage",
            "description"   =>  "Home page of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "https://github.com/kekefreedog/CrazyPHP/",
            "validate"      =>  ['http'],
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

        # Create database

        # Init composer

        # Init package

        # Create structure

        # Create rooter

        # Create basic interface

    }

}