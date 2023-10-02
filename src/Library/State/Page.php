<?php declare(strict_types=1);
/**
 * State
 *
 * Classes for manipulate state
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\State;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Context;

/**
 * Page
 *
 * Class for manage page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Page {

    /** Private parameters
     ******************************************************
     */

    /** @var array $options */
    private $options = [];

    /** @var array $result */
    private $result = [];

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param bool $process Just instance the class or run all process
     * @return self
     */
    public function __construct(array $options = []){

        # Fill options
        $this->_fillOptions($options);

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Push Context
     * 
     * Push config in state
     * 
     * @param bool $trigger
     * @return void
     */
    public function pushContext(bool $trigger = true):void {

        # Swith value in options
        $this->options["context"] = $trigger;

    }

    /**
     * Push Cookie
     * 
     * Push cookie in state
     * 
     * @param bool $trigger
     * @return void
     */
    public function pushCookie(bool $trigger = true):void {

        # Swith value in options
        $this->options["cookie"] = $trigger;

    }

    /**
     * Push Config
     * 
     * Push config in state
     * 
     * @param bool|string|array $configs
     * @return void
     */
    public function pushConfig(bool|string|array $configs = true):void {

        # Check if string
        if(is_string($configs))

            # Convert to array
            $configs = [$configs];

        # Push config
        $this->options["config"] = $configs;

    }

    /**
     * Render
     * 
     * Get result
     * 
     * @return $result
     */
    public function render():array {

        # Set result
        $result = $this->result;

        # Check if context
        if($this->options["context"] ?? false)
        
            # Push context
            $result["_context"] = Context::get();

        # Check if cookie
        if($this->options["cookie"] ?? false)
        
            # Push context
            $result["_cookies"] = $_COOKIE;

        # Check if configs
        if($this->options["config"] ?? false)

            # Push config
            $result["_config"] = $this->_getConfigs(
                $this->options["config"] === true
                    ? []
                    : $this->options["config"]
            );

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Fill Options
     * 
     * Fill options given
     * 
     * @param array $options
     * @return void
     */
    public function _fillOptions(array $options):void {

        // Check options
        $this->options = self::DEFAULT_OPTIONS;

        // Check options given
        if(!empty($options))

            # Iteration options
            foreach($options as $k => $v)

                # Check k in options
                if(array_key_exists($k, $this->options))

                    # Set value in option
                    $this->options[$k] = $v;

    }

    /**
     * Get Configs
     * 
     * Return configs
     * 
     * @param array $configs 
     * @return array
     */
    public function _getConfigs(array $configs):array {

        # Set result
        $result = [];

        # Get config
        $result = Config::get($configs);

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    public const DEFAULT_OPTIONS = [
        // HTML Template
        "template"  =>  [
            "path"      =>  null,
            "content"   =>  null,
        ],
        // Have context
        "context"   =>  false,
        // Cookie
        "cookie"    =>  false,
        // Config => ["app", "router"...]
        "config"    =>  false
    ];

}