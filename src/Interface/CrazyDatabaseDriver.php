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

    /** Public Methods | Utilisites
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
    public function test():bool;

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

    /**
     * Create Users
     * 
     * Create new user
     * 
     * @return void
     */
    public static function createUser(array $options):void;

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "";

}