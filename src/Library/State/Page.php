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
use CrazyPHP\Library\State\Components\Form;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
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

    /** @var array $ui */
    private $ui = [];

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
     * Push UI Content
     * 
     * @param string $key
     * @param mixed $value
     * @param bool $createIfNotExists Replace content is already exists
     * @return Page
     */
    public function pushUiContent(string $key, mixed $value, bool $createIfNotExists = true):Page {

        # Set content
        $result = Arrays::setKey($this->ui, $key, $value, $createIfNotExists);

        # Check
        if(!$result)
        
            # New Exception
            throw new CrazyException(
                "Key \"$key\" do not exists in UI content of the current page state. Switch parameter to \"createIfNotExists\" for fixing it.", 
                500,
                [
                    "custom_code"   =>  "state-page-001",
                ]
            );

        # Return self
        return $this;

    }

    /**
     * Push Form
     * 
     * Push form in content
     * 
     * @param array $form Form parameters
     * @return Page
     */
    public function pushForm(array $form = []):Page {

        # Form
        $form = new Form($form);

        # Get result
        $formRender = $form->render();

        # Get id
        $formId = $form->getId();

        # Check id
        if(!$formId)

            # Set from id
            $formId = empty($this->ui["forms"])
                ? "A"
                : (
                    is_string($lastKey = array_key_last($this->ui["forms"]))
                        ? $lastKey++
                        : (
                            is_int($lastKey)
                                ? strval($lastKey++)
                                : "A"
                        )
                )
            ;

        # Push in ui
        $this->pushUiContent("forms.$formId", $formRender);

        # Return self
        return $this;

    }


    /**
     * Push Context
     * 
     * Push config in state
     * 
     * @param bool $trigger
     * @return Page
     */
    public function pushContext(bool $trigger = true):Page {

        # Swith value in options
        $this->options["context"] = $trigger;

        # Return self
        return $this;

    }

    /**
     * Push Cookie
     * 
     * Push cookie in state
     * 
     * @param bool $trigger
     * @return Page
     */
    public function pushCookie(bool $trigger = true):Page {

        # Swith value in options
        $this->options["cookie"] = $trigger;

        # Return self
        return $this;

    }

    /**
     * Push Config
     * 
     * Push config in state
     * 
     * @param bool|string|array $configs
     * @return Page
     */
    public function pushConfig(bool|string|array $configs = true):Page {

        # Check if string
        if(is_string($configs))

            # Convert to array
            $configs = [$configs];

        # Push config
        $this->options["config"] = $configs;

        # Return self
        return $this;

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
                    : ( is_array($this->options["config"])
                            ? $this->options["config"] 
                            :[$this->options["config"]]
                    )
            );

        # Check ui
        if(!empty($this->ui))

            # Push ui
            $result["_ui"] = $this->ui;

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