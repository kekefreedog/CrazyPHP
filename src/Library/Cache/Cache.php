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
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Config\ConfigurationOptionInterface;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;
use CrazyPHP\Library\Time\DateTime;
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
    public function __construct(string|ExtendedCacheItemPoolInterface $driver = "Files", ConfigurationOptionInterface $config = null){

        # Set path of cache
        CacheManager::setDefaultConfig(
            new ConfigurationOption([
                'path'              => self::_getPath(),
                'itemDetailedDate'  => true,
            ])
        );

        # Set driver instance
        $driverInstance = CacheManager::getInstance('files');

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
            ]
        ]
    ];

}