<?php declare(strict_types=1);
/**
 * Html
 *
 * Class for manage html content
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Html;

/** 
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;

/**
 * Head
 *
 * Class for manipulate head of page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Head {

    /** Parameters
     ******************************************************
     */

    /** @var ?array $config */
    private $config = null;

    /** @var ?Cache $cache */
    private $cache = null;

    /** @var ?string $key */
    private $key = null;

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param array $options Options
     * @return self
     */
    public function __construct(array $options = ["config"=>"main"]){

        # Get config name
        $configName = $options["config"] ?? "main";

        # Prepare key
        $this->key = Cache::getKeyWithCacheName(__CLASS__, "config".$configName);

        # Get cache file
        $path = File::path(self::CONFIG_PATH);

        # Get Last Modified Date
        $lastModifiedDate = File::getLastModifiedDate($path);

        # New cache instance
        $this->cache = new Cache();

        # Check key in cache
        if(!$this->cache->hasUpToDate($this->key, $lastModifiedDate)){

            # Get head config
            $config = FileConfig::get("Head");

            # Adaptt config
            $config = $this->_adaptConfig($config, $configName);

            # Set config in cache
            $this->cache->set($this->key, $config);

        }

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get head data
     * 
     * @return array
     */
    public function get():array {

        # Set result
        $result = $this->cache->get($this->key);

        # Return result
        return $result;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Adapt Config
     * 
     * @param array $config Config from head config
     * @param string $name Name of the config
     * @return array
     */
    private function _adaptConfig(array $config = [], string $name = "main"):array {

        # Set result
        $result = [];

        # Check config
        if(empty($config) || !$name)

            # Return 
            return $result;

        # Check name in config data
        if(!isset($config["Head"][$name]))

            # New exception
            throw new CrazyException(
                "Head config \"$name\" doesn't exists...",
                500,
                [
                    "custom_code"   =>  "head-001",
                ]
            );

        # Set result
        $result = $config["Head"][$name];

        # Return array
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @const string CONFIG_PATH */
    public const CONFIG_PATH = "@app_root/config/Head.yml";

}