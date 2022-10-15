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
use MongoDB\Driver\Exception\CommandException as MongoDbCommandException;
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Cli\Command as CliCommand;
use CrazyPHP\Interface\CrazyDatabaseDriver;
use CrazyPHP\Exception\CrazyException;
use MongoDB\Driver\Manager;
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
     * @var $manager Manager of current database
     */
    public $manager = null;

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

        # Check if root
        if($user === "root"){

            # Set login
            $connection["login"] = $this->config["root"]["login"];

            # Set passord
            $connection["password"] = $this->config["root"]["password"];

        }else
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

        else
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

        # Datanbase name
        $connection["database"] = $user === "root" ?
            "admin" :
                $this->config["database"][0];
        

            # Set database

        # Get port
        $connection["port"] = $this->config["port"];

        # Get connection string
        $connectionString = self::getConnectionString($connection);

        # Set client
        $this->client = new Client($connectionString);

        # Set manager
        $this->manager = new Manager($connectionString);

        # Return self
        return $this;

    }

    /** Public Methods | User
     ******************************************************
     */

    /**
     * Create User
     * 
     * Create new user
     * 
     * @param string $user User name
     * @param string $password Password
     * @param string|array databases Name of database
     * @param string|array $options Options for create user
     * @return self
     */
    public function createUser(string $user = "", string $password = "", string|array $databases = [], string|array $options = []):self{

        # Check client
        if(!$this->manager)

            # New Exception
            throw new CrazyException(
                "Please execute \"newClient\" method before \"".__METHOD__."\" method...",
                500,
                [
                    "custom_code"   =>  "mongodb-003",
                ]
            );

        # Check user
        if(!$user)

            # New Exception
            throw new CrazyException(
                "User parameter is empty...",
                500,
                [
                    "custom_code"   =>  "mongodb-004",
                ]
            );

        # Check databases
        if(empty($databases))

            # Set databases
            $databases = $this->config["database"] ?? ["admin"];

        # Check is not array
        elseif(!is_array($databases))

            # Convert to array
            $databases = [$databases];

        # Prepare command
        $command = [
            "createUser"    =>  $user,
            "pwd"           =>  $password,
            "roles"         =>  $options["roles"] ?? ["readWrite"],
        ];

        # Iteration of databases
        foreach($databases as $database){

            # Select database
            $databaseInstance = $this->client->selectDatabase($database);

            # Try
            try{

                # Create user
                $result = $databaseInstance->command($command);

            }catch(MongoDbCommandException $e){

                # Get message
                $result = $e->getMessage();

            }

            # Check message
            if(is_string($result) && strpos($result, "already exists") === false)

                # New Exception
                throw new CrazyException(
                    "Error when creating user in MongoDB \"$result\"",
                    500,
                    [
                        "custom_code"   =>  "mongodb-005",
                    ]
                );

        }

        # Return self
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

        # Check client
        if(!$this->client)

            # New Exception
            throw new CrazyException(
                "Please execute \"newClient\" method before \"".__METHOD__."\" method...",
                500,
                [
                    "custom_code"   =>  "mongodb-006",
                ]
            );

        # Set users
        $users = $this->config["users"] ?? null;

        # Check users
        if(!empty($users))

            # Iteration of users
            foreach($users as $user)

                # Create user
                $this->createUser($user["login"], $user["password"]);

        # Return self
        return $this;

    }

    /** Public Methods | Database
     ******************************************************
     */

    /**
     * Get All Databases
     * 
     * Get All Databases in Mongo DB
     * 
     * @return array
     */
    public function getAllDatabases():array {

        # Declare result
        $result = [];

        # Get list of databases
        $result = $this->client->listDatabases();

        # Return result
        return $result;

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
            ($options["protocol"] ?? "mongodb")."://".
            ( $options["login"] ?: '' ).
            ( 
                $options["password"] ? 
                    ":".$options["password"] : 
                        '' 
            ).
            ( 
                $options["login"] || $options["password"] ? 
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
                $options["database"] ? 
                    "/".$options["database"] :
                        ''
            ).
            (
                isset($options["options"]) && count($options["options"]) > 0 ? 
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

        # Test mongodb
        $driver = new self();

        # New root client
        $driver->newClient("root");

        # Create config user
        $driver->createUserFromConfig();

    }

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "Database.collection.mongodb";

}