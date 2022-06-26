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
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Config\ConfigurationOptionInterface;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;
use Phpfastcache\CacheManager;

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
    public function __construct(string|ExtendedCacheItemPoolInterface $driver = "Files", ConfigurationOptionInterface $config = null, string $customPath = ""){

        # Set path of cache
        CacheManager::setDefaultConfig(
            new ConfigurationOption([
                'path' => $customPath ? $customPath : self::PATH,
            ])
        );

        # Parent constructor
        parent::__construct($driver, $config);

    }

    /** Public static
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

    /** Private constants
     ******************************************************
     */

    /**
     * Path of the cache
     */
    public const PATH = "/.cache/app/";


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