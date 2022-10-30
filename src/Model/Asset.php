<?php declare(strict_types=1);
/**
 * Model
 *
 * Classe for define framework models
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;

/**
 * Asset
 *
 * Methods for interacting with assets
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Asset{

    /** Public static methods
     ******************************************************
     */

    /**
     * Register
     * 
     * Register assets
     * 
     * @param bool $force Force register of the file
     * @return void
     */
    public static function register(bool $force = false):void {

        # New cache instance
        $cache = new Cache();

        # Get path file
        $path = File::path(self::PATH);

        # Check file exists
        if(File::exists($path))

            # Return
            return;

        # Key of updated date
        $keyUpdatedTime = Cache::getKeyWithCacheName(__CLASS__, "updatedTime");

        # Key of updated date
        $keyCollection = Cache::getKeyWithCacheName(__CLASS__, "collection");

        # Get Updated Time
        $updatedTime = $cache->get($keyUpdatedTime);

        # Check updated time
        if(!$updatedTime || $updatedTime < DateTime::lastUpdateFile($path) || $force){

            # Cache File
            $cache->get($keyCollection, static::_parseAssets());

            # Update time
            $updatedTime = $cache->set($keyUpdatedTime, new DateTime("now"));

        }

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Parse Assets
     * 
     * Parse Assets config file
     * 
     * @param string $path Path of the file to parse
     * @return array
     */
    private static function _parseAssets(string $path = self::PATH):array {

        # Set result
        $result = [];

        # Open file
        $collection = File::open($path);

        # Set result
        $result = $collection;

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @const PATH */
    public const PATH = "@app_root/config/Asset.yml";

}