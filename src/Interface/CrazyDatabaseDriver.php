<?php declare(strict_types=1);
/**
 * Interface
 *
 * Interface of CrazyPHP
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Interface;

/**
 * Crazy Database Driver
 * 
 * Interface for define compatible class with database driver
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
interface CrazyDatabaseDriver {

    /**
     * @var $config Config of current database
     */
    # public $config = null;

    /**
     * @var $client Client of current database
     */
    # public $client = null;

    /**
     * Constructor
     * 
     * Get current database config
     * 
     * @return self
     */
    public function __construct();

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
    public function newClient(string|int $user = ""):self;

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
    public function createUser(string $user = "", string $password = "", string|array $databases = [], string|array $options = []):self;

    /**
     * Create Users From Config
     * 
     * Create users from config
     * 
     * @return self
     */
    public function createUserFromConfig():self;

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
    public static function test():bool;

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
    public static function setup():void;

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "";

}