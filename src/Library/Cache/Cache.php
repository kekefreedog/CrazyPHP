<?php declare(strict_types=1);
/**
 * Cache
 *
 * Classe for cache
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Cache;

/**
 * Dependances
 */

use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheDriverConnectException;
use Phpfastcache\Drivers\Mongodb\Config as MemcachedConfig;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Config\ConfigurationOptionInterface;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Config\ConfigurationOption;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Composer;
use Phpfastcache\Helper\Psr16Adapter;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use Phpfastcache\CacheManager;
use CrazyPHP\Model\Env;

/**
 * Cache
 *
 * Cache manipulation
 * 
 * @info extend [Psr16Adapter](/vendor/phpfastcache/phpfastcache/lib/Phpfastcache/Helper/Psr16Adapter.php)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Cache extends Psr16Adapter {

    /** Variables
     ******************************************************
     */

    /**
     * Instance of the cache
     */
    private $InstanceCache = null;

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param string|ExtendedCacheItemPoolInterface $driver Driver of the cache
     * @param ConfigurationOptionInterface|null $config Config of the cache
     * @return self
     */
    public function __construct(string|ExtendedCacheItemPoolInterface $driver = "", ConfigurationOptionInterface|null $config = null) {

        # Declare driver instance
        $driverInstance = "";

        # Get Configuration
        $configuration = $this->getConfiguration($driver, $config);

        # Check driver is
        ## Files
        if($configuration["driver"] == "Files"){

            # Set path of cache
            CacheManager::setDefaultConfig(
                new ConfigurationOption($configuration["options"])
            );

            # Set driver instance
            $driverInstance = CacheManager::getInstance('files');

        }else
        ## Mongodb
        if($configuration["driver"] == "Mongodb"){

            # Check MemcachedConfig exists
            if(!class_exists("Phpfastcache\Drivers\Mongodb\Config")){

                # Install mongo extension of php fast cache
                Composer::requirePackage("phpfastcache/mongodb-extension");

            }

            # Set config
            /** @disregard P1009 */
            $config = new MemcachedConfig();

            # Set config
            $config
                ->setHost($configuration["options"]["host"])
                ->setPort($configuration["options"]["port"])
                ->setUsername($configuration["options"]["username"])
                ->setPassword($configuration["options"]["password"])
                ->setDatabaseName($configuration["options"]["databaseName"])
                ->setCollectionName($configuration["options"]["collectionName"])
                ->setItemDetailedDate($configuration["options"]["itemDetailedDate"])
            ;

            # Try
            try{

                # Set driver instance
                /** @disregard P1006 */
                $driverInstance = CacheManager::getInstance(
                    $configuration["driver"], 
                    $config
                );

            }catch(PhpfastcacheDriverConnectException $e){

                # New exception
                $exception = new CrazyException(
                    "Mango issue, restart your docker container",
                    500,    
                    [
                        "custom_code"   =>  "cache-001",
                    ],
                    $e
                );

                # Render exception
                $exception->render();

            }

        }

        # Initialize the explicitly selected Redis driver
        if($configuration["driver"] === "Redis"){

            # Require the native extension used by Phpfastcache's Redis driver
            if(!extension_loaded("redis")){

                # Explain the missing runtime dependency before attempting a connection
                throw new CrazyException("The Redis cache driver requires the PHP redis extension.", 500, ["custom_code" => "cache-003"]);

            }

            # Pass the Redis-specific options without modifying other cache pools
            $driverInstance = CacheManager::getInstance("Redis", $configuration["options"]);

        }

        # Parent constructor
        parent::__construct($driverInstance);

    }

    /** Public methods
     ******************************************************
     */

    /** 
     * Has Up To Date
     * 
     * Chack if db has a up to date cached of the current key given
     * 
     * @return bool True is up to date, False if old
     */
    public function hasUpToDate(string $key, DateTime $lastModifiedDate ): bool {

        # Declare result
        $result = false;

        try {

            # Get cache item
            $cacheItem = $this->internalCacheInstance->getItem($key);
            
            # Check key is valid
            if(
                $cacheItem->isHit() && 
                !$cacheItem->isExpired() &&
                $cacheItem->getModificationDate() >= $lastModifiedDate
            )

                # Set result
                $result = true;

        } catch (PhpfastcacheInvalidArgumentException $e) {

            # New Exception
            throw new CrazyException(
                $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "cache-020",
                ],
                $e
            );

        }

        # Return result
        return $result;

    }

    /** 
     * Is Up To Date
     * 
     * Chack if db has a up to date cached of the current key given
     * 
     * @return bool True is up to date, False if old
     */
    public function isUpToDate(string $key, DateTime $lastModifiedDate ): bool {

        # Return has up to date
        return $this->hasUpToDate($key, $lastModifiedDate);

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Get Cache Name
     * 
     * Return clean cache name from methods or class
     * 
     * @param string $context Context like __METHOD__ or __CLASS__ or __FUNCTION__
     * @param string $customName custum name
     * @return string
     */
    public static function getCacheName(string $context = "", string $customName = ""):string {

        # Declare result
        $result = "";

        # Check context or customName
        if(!$context && !$customName)
            return $result;

        # Clean Array
        $cleanArray = [
            "\\"    =>  "/",
            "::"    =>  "/",
            "//"    =>  "/",
        ];

        # Fill result
        $result = $context.($customName ? "/".ucfirst(strtolower($customName)) : "");
            
        # Iteration of cleanArray
        foreach($cleanArray as $search => $replace)

            # Process
            $result = str_replace($search, $replace, $result);

        # Return result
        return $result;

    }

    /**
     * Get Key With Cache Name
     * 
     * @param string $context Context like __METHOD__ or __CLASS__ or __FUNCTION__
     * @param string $customName custum name
     * @return string
     */
    public static function getKeyWithCacheName(string $context = "", string $customName = ""):string {

        # Get key
        $result = str_replace(
            ["{", "}", "(", ")", "/", "\\", "@", ":"],
            ".",
            static::getCacheName($context, $customName)
        );

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    private function _string_to_tags(string $input = ""):array {

        # Set result
        $result = [];

        # check input
        if(!$input)
            # Return result
            return $result;

        # List delimiter
        $delimiters = ["/", "."];

        # Iteration of delimiter
        foreach($delimiters as $delimiter)

            # Check if input has delimiter
            if(strpos($input, $delimiter) !== false){

                # Explode
                $result = explode($delimiter, $input);

                # Add full input in result
                $result[] = $input;

            }

            # Check explodeInput
            if(empty($result))
    
                # Set result
                $result = $input;

        # Return result
        return $result;

    }

    /**
     * Get Path
     * 
     * Get Path, check if not test
     */
    private function _getPath():string {

        # Declare result
        $result = self::PATH;

        # check constant
        if(Env::has("phpunit_test") && Env::get("phpunit_test"))

            # Set result
            $result = self::PATH_TEST;

        # Strange reaction... Allow to debug next command... ¯\_(ツ)_/¯
        Env::get("crazyphp_root");

        # Process result
        $result = File::path($result);

        # Return result
        return $result;

    }

    /**
     * Get configuration
     * 
     * Get configuration driver & options
     * 
     * @param string|ExtendedCacheItemPoolInterface $driver Driver to force
     * @param ?ConfigurationOptionInterface $config Config to force
     * @return array
     */
    public function getConfiguration(string|ExtendedCacheItemPoolInterface $driver = "", ?ConfigurationOptionInterface $config = null){

        # Set result
        $result = [];

        # Check not test
        if(
            (Env::has("phpunit_test") && Env::get("phpunit_test")) ||
            (Env::has("cache_driver") && Env::get("cache_driver") == "Files")
        ){

            # Discard Redis connection options when tests or the environment force file caching
            if($driver === "Redis"){

                # Keep the file-cache override independent of Redis configuration
                $config = null;

            }

            # Set driver
            $driver = "Files";

        }

        # Get driver if empty
        if(!$driver){

            # Get mongodb config
            try{

                # Read the configured mongodb engine
                $mongodbConfig = Config::getValue("Database.collection.mongodb");

            }catch(CrazyException $e){

                # Projects without database configuration (library, other) use files
                $mongodbConfig = null;

            }

            # Check mongodb config
            if($mongodbConfig && !empty($mongodbConfig))

                # Set driver
                $driver = "Mongodb";

            # Else default driver
            else

                # Retain file caching as the default without MongoDB configuration
                $driver = "Files";

        }

        # Check driver
        if(!$driver || !in_array($driver, self::DRIVERS_ALLOWED)){

            # Report the unsupported driver
            echo $driver;

            # New Exception
            throw new CrazyException(
                "Driver \"$driver\" for your cache instance isn't valid...",
                500,
                [
                    "custom_code"   =>  "cache-002",
                ]
            );

        }

        # Push driver in result
        $result["driver"] = $driver;

        # Resolve Redis configuration only when explicitly selected
        if($driver === "Redis"){

            # Require options compatible with Phpfastcache's Redis driver
            if($config !== null && !$config instanceof RedisConfig){

                # Reject incompatible configuration before establishing a connection
                throw new CrazyException("The Redis cache driver requires Phpfastcache\\Drivers\\Redis\\Config.", 500, ["custom_code" => "cache-004"]);

            }

            # Read environment options when no explicit configuration was supplied
            if($config === null){

                # Resolve the Redis host from the process environment
                $host = getenv("REDIS_HOST") ?: "127.0.0.1";

                # Read the optional Redis port without accepting malformed numeric values
                $port = getenv("REDIS_PORT");

                # Default an unset port and validate the TCP port range
                $port = filter_var(
                    $port === false || 
                    $port === "" 
                        ? 6379 
                        : $port
                    ,
                    FILTER_VALIDATE_INT,
                    [
                        "options" => [
                            "min_range" => 1,
                            "max_range" => 65535
                        ]
                    ]
                );

                # Reject invalid ports before opening a connection
                if($port === false)

                    # Identify the environment variable that needs correcting
                    throw new CrazyException(
                        "REDIS_PORT must be an integer between 1 and 65535.",
                        500,
                        [
                            "custom_code" => "cache-005"
                        ]
                    );

                # Build native Redis options with detailed cache dates
                $config = new RedisConfig([
                    "host" => $host, 
                    "port" => $port, 
                    "itemDetailedDate" => true
                ]);

            }

            # Retain the Redis configuration object for CacheManager
            $result["options"] = $config;

        }else
        # Check config
        if(!empty($config)){

            # Push config
            $result["options"] = $config;

        }else
        # Check if files driver
        if($driver == "Files"){

            # Set Files config
            $result["options"] = [
                'path'              => self::_getPath(),
                'itemDetailedDate'  => true,
            ];

        }else
        # Check if mongodb driver
        if($driver == "Mongodb"){

            # Get cache config

            # Set Mongodb config
            $result["options"] = [
                'itemDetailedDate'  =>  true,
                "host"              =>  $mongodbConfig["host"],
                "port"              =>  $mongodbConfig["port"],
                "username"          =>  $mongodbConfig["users"][0]["login"],
                "password"          =>  $mongodbConfig["users"][0]["password"],
                "collectionName"    =>  "cache", 
                "databaseName"      =>  "crazy_db",
            ];

        }

        # Return result
        return $result;

    }

    /** Private constants
     ******************************************************
     */

    /**
     * Path of the cache
     */
    public const PATH = "@app_root/.cache/app/";
    public const PATH_TEST = "@crazyphp_root/tests/.cache/cache/";


    /**
     * Directory of class name
     */
    public const DIRECTORY = [
        "CrazyPHP"  => [
            "Library"   =>  [
                "Template"  =>  [
                    "Header"    =>  [
                        "TemplatesCompilated"
                    ]
                ]
            ],
            "Core"  =>  [
                "Router"    =>  [
                    "RouterCollectionCached"    =>  [
                        "ConfigRouter"
                    ],
                ],
            ],
        ],
    ];

    /** @var array DRIVERS_ALLOWED */
    public const DRIVERS_ALLOWED = [
        "Files", "Mongodb", "Redis"
    ];

}
