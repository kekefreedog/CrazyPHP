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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database\Driver;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Interface\CrazyDatabaseDriver;
use CrazyPHP\Exception\CrazyException;
use MongoDB\Client;

/**
 * MangoDB
 *
 * Driver for manipulate mangodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Mangodb implements CrazyDatabaseDriver {

    /** Parameters
     ******************************************************
     */

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
     * 1. Get current database config
     * @return self
     */
    public function __construct(){

        # Get current conffig
        $this->config = FileConfig::getValue(self::CONFIG_KEY);
        
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
    public function newClient(string|int $user = 0):self{

        # Connection
        $connection = [];

        # Check user
        if($user === "" || !array_key_exists($user, $this->config["users"]))

            # New Exception
            throw new CrazyException(
                "User \"$user\" can't be found...",
                500,
                [
                    "custom_code"   =>  "mongodb-001",
                ]
            );

        # Check if root
        if($user === "root"){

            # Set login
            $connection["login"] = $this->config["root"]["login"];

            # Set passord
            $connection["password"] = $this->config["root"]["password"];

        }else
        # Check if key
        if(isset($this->config["users"][$user]) && !empty($this->config["users"][$user])){

            # Set login
            $connection["login"] = $this->config["users"][$user]["login"];

            # Set passord
            $connection["password"] = $this->config["users"][$user]["password"];

        }else

            # New Exception
            throw new CrazyException(
                "User \"$user\" don't exists...",
                500,
                [
                    "custom_code"   =>  "mongodb-002",
                ]
            );

        # Get host
        $connection["host"] = $this->config["host"];

        # Get port
        $connection["port"] = $this->config["port"];

        # Get connection string
        $connectionString = self::getConnectionString($connection);

        # Set client
        $this->client = new Client($connectionString);

        # Return self
        return $this;

    }

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
    public function test():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Get Connection String
     * 
     * Useful for connect to mongo db
     * 
     * @param array $options Option
     * @return string
     */
    public static function getConnectionString(array $options = []):string {

        # Set result
        $result = "";

        # Set result
        $result =                
            $options["protocol"]."://".
            $options["username"] ?: ''.
            ( 
                $options["password"] ? 
                    ":".$options["password"] : 
                        '' 
            ).
            ( 
                $options["username"] ? 
                    '@' : 
                        ''
            ).
            $options["host"].
            ( 
                $options["port"] !== false ?
                    ":".$options["port"] :
                         ''
            ).
            (
                $options["databaseName"] ? 
                    "/".$options["databaseName"] :
                        ''
            ).
            (
                count($options["options"]) > 0 ? 
                    '?' . http_build_query($options["options"]) : 
                        ''
            )
        ;

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

    /**
     * Create Users
     * 
     * Create new user
     * 
     * @return void
     */
    public static function createUser(array $options):void {

        # New instance
        $instance = new self;

        # Connect as root
        $instance->newClient("root");

    }

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "Database.collection.mongodb";

}