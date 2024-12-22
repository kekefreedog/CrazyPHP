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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database\Singleton;

/** 
 * Dependances
 */
use MongoDB\Exception\InvalidArgumentException;
use CrazyPHP\Library\Database\Driver\Mangodb;
use MongoDB\Exception\RuntimeException;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazySingleton;
use CrazyPHP\Library\File\Config;
use MongoDB\Driver\Manager;
use MongoDB\Client;
use Exception;
use PDO;

/**
 * Mongodb Connection
 *
 * Connection to Mongodb database
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MongodbConnection implements CrazySingleton {

    /** Public Static Parameters
     ******************************************************
     */

    /** @var mixed instance */
    private static array $_instances = [];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Instance
     * 
     * Singleton method
     * 
     * @param array options
     * @return array [Client, Manager]
     */
    public static function getInstance(array $options = [
        "user"  =>  0
    ]):array {

        # Set user
        $user = ($options["user"] ?? false)
            ? $options["user"]
            : 0
        ;

        # Check instance
        if(!array_key_exists($user, static::$_instances))

            # Connect
            static::connect($options);

        # Return instance
        return static::$_instances[$user];

    }

    /**
     * Connect
     * 
     * Establish connection
     * 
     * @return mixed
     */
    public static function connect(array $options = [
        "user"  =>  0
    ]):void {

        # Get config
        $config = Config::getValue(self::CONFIG_KEY);

        # Set user
        $user = ($options["user"] ?? false)
            ? $options["user"]
            : 0
        ;

        # Connection
        $connection = [];

        # Check if root
        if($user === "root"){

            # Set login
            $connection["login"] = $config["root"]["login"];

            # Set passord
            $connection["password"] = $config["root"]["password"];

        }else
        # Check user
        if($user === "" || !array_key_exists($user, $config["users"]))

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
        if(isset($config["users"][$user]) && !empty($config["users"][$user])){

            # Set login
            $connection["login"] = $config["users"][$user]["login"];

            # Set passord
            $connection["password"] = $config["users"][$user]["password"];

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
        $connection["host"] = $config["host"];

        # Datanbase name
        $connection["database"] = $user === "root" ?
            "admin" :
                $config["database"][0];
        

            # Set database

        # Get port
        $connection["port"] = $config["port"];

        # Get connection string
        $connectionString = Mangodb::getConnectionString($connection);
        
        # Try
        try{

            # Set client
            static::$_instances[$user] = [
                new Client($connectionString),
                new Manager($connectionString)
            ];

        # Catch
        }catch(InvalidArgumentException|RuntimeException|Exception $e) {

            # New Exception
            throw new CrazyException(
                $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mongodb-010",
                ]
            );
        
        }

    }

    /**
     * Disconnect
     * 
     * isconnect method
     * 
     * @param array $option
     * @return mixed
     */
    public static function disconnect(array $options = [
        "user"  =>  0
    ]):void {

        # Set user
        $user = ($options["user"] ?? false)
            ? $options["user"]
            : 0
        ;

        # Set null on pdo
        unset(static::$_instances[$user]);

    }

    /** Public constants
     ******************************************************
     */

    /** @var string CONFIG_KEY Config key for current database */
    public const CONFIG_KEY = "Database.collection.mongodb";

}