<?php declare(strict_types=1);
/**
 * Databse Driver
 *
 * Drivers for manage differents database system
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database\Driver;

/**
 * Dependances
 */
use CrazyPHP\Interface\CrazyDatabaseDriver;

/**
 * Mysql
 *
 * Driver for manipulate MySQL
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Mysql implements CrazyDatabaseDriver {

    /**
     * @var $config Config of current database
     */
    public $config = null;

    /**
     * @var $client Client of current database
     */
    public $client = null;

    /**
     * Constructor
     * 
     * Get current database config
     * 
     * @return self
     */
    public function __construct() {



    }

    /** Public Methods | Clients
     ******************************************************
     */

    /**
     * New Client
     * 
     * Define new databse client
     * 
     * @param string|int $user from option in Config > Database
     * @return self
     */
    public function newClient(string|int $user = ""):self {

        # Return result
        return $this;

    }

    /** Public Methods | User
     ******************************************************
     */

    /**
     * Create Users
     * 
     * Create new user
     * 
     * @param string $user User name
     * @param string $password Password
     * @param string|array databases Name of database
     * @param string|array $options Options for create user
     * @return self
     */
    public function createUser(string $user = "", string $password = "", string|array $databases = [], string|array $options = []):self {

        # Return result
        return $this;

    }

    /**
     * Create Users From Config
     * 
     * Create users from config
     * 
     * @return self
     */
    public function createUserFromConfig():self {

        # Return result
        return $this;

    }

    /** Public Static Methods | Utilities
     ******************************************************
     */

    /**
     * Test
     * 
     * Test Database connection
     * 
     * @param array $options Option from Config > Database
     * @return bool
     */
    public static function test():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Setup
     * 
     * Setup current database
     * 
     * @return void
     */
    public static function setup():void {



    }

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "Database.collection.mysql";

}