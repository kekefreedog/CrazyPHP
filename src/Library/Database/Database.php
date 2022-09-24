<?php declare(strict_types=1);
/**
 * Database
 *
 * Manipulate databases
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database;

/** 
 * Dependances
 */
use CrazyPHP\Library\File\Config;

/**
 * Database
 *
 * Core of manipulation of your database
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Database {

    /**
     * Constructor
     * 
     * @param array $options Options for create database
     */
    public function __construct(array $options = []){

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Setup Config
     * 
     * Setup config of database given using default value
     * 
     * @param string $databse Name of the database
     * @return void
     */
    public static function setupConfig(string $database = "") {

        # Check database name
        if(!$database || !array_key_exists($database, self::CONFIG))

            # Stop function
            return;

        # Fill config
        Config::set("Database.collection.$database", self::CONFIG[$database]);

    }

    /** Public constants
     ******************************************************
     */

    /* @const array CONFIG */
    public const CONFIG = [
        # MongoDB
        "mongodb"   =>  [
            "engine"    =>  "CrazyPHP\Library\Database\Driver\Mangodb",
            "host"      =>  "localhost",
            "port"      =>  27017,
            "root"      =>  [
                "login"     =>  "admin",
                "password"  =>  "password",
            ],
            "users"     =>  [
                [
                    "login" =>  "crazyuser",
                    "login" =>  "crazypassword",
                ]
            ]
        ],
        # MariaDB
        "mariadb"   =>  [
            "engine"    =>  "CrazyPHP\Library\Database\Driver\Mariadb",
            "host"      =>  "localhost",
            "port"      =>  null,
            "root"      =>  [
                "login"     =>  "admin",
                "password"  =>  "password",
            ],
            "users"     =>  [
                [
                    "login" =>  "crazyuser",
                    "login" =>  "crazypassword",
                ]
            ]
        ],
        # MySQL
        "mysql"     =>  [
            "engine"    =>  "CrazyPHP\Library\Database\Driver\Mysql",
            "host"      =>  "localhost",
            "port"      =>  null,
            "root"      =>  [
                "login"     =>  "admin",
                "password"  =>  "password",
            ],
            "users"     =>  [
                [
                    "login" =>  "crazyuser",
                    "login" =>  "crazypassword",
                ]
            ]
        ],
        # PostgreSQL
        "postgresql"=>  [
            "engine"    =>  "CrazyPHP\Library\Database\Driver\Postgresql",
            "host"      =>  "localhost",
            "port"      =>  null,
            "root"      =>  [
                "login"     =>  "admin",
                "password"  =>  "password",
            ],
            "users"     =>  [
                [
                    "login" =>  "crazyuser",
                    "login" =>  "crazypassword",
                ]
            ]
        ]
    ];

}