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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Cache;

/**
 * Dependances
 */

use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Drivers\Mongodb\Config as MemcachedConfig;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Config\ConfigurationOptionInterface;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Mongodb\Driver;
use CrazyPHP\Exception\CrazyException;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;
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
 * @extends [Psr16Adapter](/vendor/phpfastcache/phpfastcache/lib/Phpfastcache/Helper/Psr16Adapter.php)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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
     * @param ConfigurationOptionInterface $config Config of the cache
     * @return Create
     */
    public function __construct(string|ExtendedCacheItemPoolInterface $driver = "", ConfigurationOptionInterface $config = null){

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

            # Set config
            $config = new MemcachedConfig();
            $config
                ->setHost($configuration["options"]["host"])
                ->setPort($configuration["options"]["port"])
                ->setUsername($configuration["options"]["username"])
                ->setPassword($configuration["options"]["password"])
                ->setDatabaseName($configuration["options"]["databaseName"])
                ->setCollectionName($configuration["options"]["collectionName"])
                ->setItemDetailedDate($configuration["options"]["itemDetailedDate"])
            ;

            # Set driver instance
            $driverInstance = CacheManager::getInstance(
                $configuration["driver"], 
                $config
            );

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
     * @return bool
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

            # New error
            throw new PhpfastcacheSimpleCacheException($e->getMessage(), 0, $e);

        }

        # Return result
        return $result;

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
     * @param ConfigurationOptionInterface $config Config to force
     * @return array
     */
    public function getConfiguration(string|ExtendedCacheItemPoolInterface $driver = "", ConfigurationOptionInterface $config = null){

        # Set result
        $result = [];

        # Check not test
        if(Env::has("phpunit_test") && Env::get("phpunit_test"))

            # Set driver
            $driver = "Files";

        # Get driver if empty
        if(!$driver){

            # Get mongodb config
            $mongodbConfig = Config::getValue("Database.collection.mongodb");

            # Check mongodb config
            if($mongodbConfig && !empty($mongodbConfig))

                # Set driver
                $driver = "Mongodb";

        }

        # Check driver
        if(!$driver || !in_array($driver, self::DRIVES_ALLOWED))

            # New Exception
            throw new CrazyException(
                "Driver \"$driver\" for your cache instance isn't valid...",
                500,
                [
                    "custom_code"   =>  "cache-001",
                ]
            );

        # Push driver in result
        $result["driver"] = $driver;

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

    /* @const array DRIVES_ALLOWED */
    public const DRIVES_ALLOWED = [
        "Files", "Mongodb"
    ];

}