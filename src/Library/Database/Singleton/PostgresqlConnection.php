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
use CrazyPHP\Library\Database\Driver\Mariadb;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazySingleton;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use PDOException;
use PDO;

/**
 * Postgresql Connection
 *
 * Connection to Postgresql database
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class PostgresqlConnection implements CrazySingleton {

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
     * @return PDO
     */
    public static function getInstance(array $options = [
        "user"  =>  0
    ]):PDO {

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

        # Cache Instance
        $cacheInstance = new Cache();

        # Set user
        $user = ($options["user"] ?? false)
            ? $options["user"]
            : 0
        ;

        # Get key
        $key = Cache::getKeyWithCacheName(__CLASS__, "connectionArray.$user");

        $lastModifiedDate = File::getLastModifiedDate(File::path(static::CONFIG_PATH));

        # Check cache is valid
        if($cacheInstance->hasUpToDate($key, $lastModifiedDate)){

            # Set connectionArray
            $connectionArray = $cacheInstance->get($key);
            
        # Set cache
        }else{

            # Get config
            $config = Config::getValue(self::CONFIG_KEY);

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
                        "custom_code"   =>  "postgresqlconnection-001",
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
                        "custom_code"   =>  "postgresqlconnection-002",
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
            $connectionArray = Mariadb::getConnectionArray($connection);

            # Push  connectionArray in cache
            $cacheInstance->set($key, $connectionArray);

        }
        
        # Try
        try{

            # Set client
            static::$_instances[$user] = new PDO(...$connectionArray);

        # Catch
        }catch(PDOException $e) {

            # New Exception
            throw new CrazyException(
                $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "postgresqlconnection-010",
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
    public const CONFIG_KEY = "Database.collection.postgresql";

    /** @var string CACHE_ROUTER */
    public const CONFIG_PATH = "@app_root/config/Database.yml";

}