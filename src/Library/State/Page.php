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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\State;

/**
 * Dependances
 */
use CrazyPHP\Library\Exception\HttpStatusCode;
use CrazyPHP\Library\State\Components\Form;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Exception\CatchState;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\String\Color;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\String\Url;
use CrazyPHP\Library\Form\Query;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;
use CrazyPHP\Model\Context;
use CrazyPHP\Model\Env;
use Exception;

/**
 * Page
 *
 * Class for manage page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Page {

    /** Private parameters
     ******************************************************
     */

    /** @var array $options */
    private $options = [];

    /** @var array $result */
    private $result = [];

    /** @var string $status code */
    private $status_code = 200;

    /** @var array $errors */
    private $errors = [];

    /** @var array $ui */
    private $ui = [];

    /** @var array $events */
    private $events = [];

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
     * Push Color Schema
     * 
     * @source https://github.com/leonardorafael/material-dynamic-colors
     * @param string $source
     * @return self
     */
    public function pushColorSchema(string $source = ""):Page {

        # Get color source
        $appSource = Config::getValue("Style.materialDynamicColors.source");

        # Check source
        if(!$source){

            # Get url parameter
            $source = $appSource;

        }else
        # Check source
        if($source && Color::isValid($source)){

            # Set language in response
            $this->pushUiContent("materialDynamicColors.source", $source);

        }

        # Return self
        return $this;

    }

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
     * Push Results
     * 
     * Push results in content
     * 
     * @param mixed $results
     * @param string $entity
     * @return Page
     */
    public function pushResults(mixed $result = [], ?string $entity = null):Page {

        # Declare result
        $resultTemp = [];

        # Check entity
        if($entity !== null && $entity)

            # Push result
            $resultTemp[$entity] = $result;

        # If no entity
        else

            # Push result
            $resultTemp = $result;

        # Merge to result
        $this->result = Arrays::mergeMultidimensionalArraysBis(true, $this->result, $resultTemp);

        # Return self
        return $this;

    }

    /**
     * Push Results
     * 
     * Push results in content
     * 
     * @param mixed $results
     * @param string $entity
     * @return Page
     */
    public function pushResultsWithKey(string $key, mixed $result = []):Page {

        # Set value
        Arrays::setKey($this->result, $key, $result);

        # Return self
        return $this;

    }

    /**
     * Get Results
     * 
     * @param string $key
     * @return mixed
     */
    public function getResults(string $key = "*"):mixed {

        # Check key
        if($key === "*" || !$key)

            # Set result
            $result = $this->result;

        else

            # Set result
            $result = Arrays::getKey($this->result, $key);

        # Return result
        return $result;

    }

    /**
     * Push Form
     * 
     * Push form in content
     * 
     * @param array $form Form parameters
     * @return Page
     */
    public function pushForm(array $form = []):self {

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
     * Push Partial State
     * 
     * Push partial state in content
     * 
     * @param string $partialId Partial id
     * @param mixed $data
     * @return Page
     */
    public function pushPartialState(string $partialId = "", mixed $data = []):self {

        # Check id
        if(!$partialId)

            # Set from id
            $partialId = empty($this->ui["partials"])
                ? "A"
                : (
                    is_string($lastKey = array_key_last($this->ui["partials"]))
                        ? $lastKey++
                        : (
                            is_int($lastKey)
                                ? strval($lastKey++)
                                : "A"
                        )
                )
            ;

        # Push in ui
        $this->pushUiContent("partials.$partialId", $data);

        # Return self
        return $this;

    }

    /**
     * Push Redirection
     * 
     * Push event of type redirection
     * @param string $nameOrUrl
     * @param bool $nameOrUrl Open into a new page
     * @param ?array $arguments Arguments
     * @return self
     */
    public function pushRedirection(string $nameOrUrl, bool $openInNewTab = false, ?array $arguments = null):self {

        # Set temp
        $temp = null;

        # Check url
        if($nameOrUrl){

            # If is external
            if(Url::isExternal($nameOrUrl))

                # Set url
                $temp["url"] = $nameOrUrl;

            # If is internal
            else{

                # Set url
                $temp["name"] = $nameOrUrl;

                # Check parameters
                if(is_array($arguments) && !empty($arguments)){

                    # Set arguments
                    $temp["arguments"] = $arguments;

                }

            }

            # Check openInNewTab
            if($openInNewTab)

                # Set target
                $temp["target"] = "_blank";

        }

        # Push event
        $this->pushEvent([
                "type"  =>  "redirect",
            ] + $temp);

        # Return self
        return $this;

    }

    /**
     * Push Event
     * 
     * Push event in state
     * @param array $eventInput
     * @return self
     */
    public function pushEvent(array $eventInput = []):self {

        # Check event input
        if(!empty($eventInput)){

            # Push into event
            $this->events[] = $eventInput;

        }

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
    public function pushContext(bool $trigger = true):self {

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
    public function pushCookie(bool $trigger = true):self {

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
    public function pushConfig(bool|string|array $configs = true):self {

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
     * Set Status Code
     * 
     * @param int $status_code
     * @return Page
     */
    public function setStatusCode(int $status_code = 200, $errorOption = []):self {

        # Check status code
        if(Validate::isValidHttpStatusCode($status_code)){

            # Set status code
            $this->status_code = $status_code;

            # Check code is error
            if($this->status_code >= 400 && $this->status_code <= 599){

                # Check error option
                if(empty($errorOption)){

                    # Check if option is empty
                    $file = File::open("@crazyphp_root/resources/Yml/HttpStatusCode.yml");

                    # Get default
                    $errorOption = $file["HttpStatusCode"]["default"] ?? [];

                }

                # Push code in error options
                $errorOption["code"] = $status_code;

                # Push error
                $this->pushError($errorOption);

            }

        }

        # Return self
        return $this;

    }

    /**
     * Push Error
     * 
     * @param array $options = [
     *      "code",
     *      "type",
     *      "detail",
     *      "_status_code",
     * ]
     * @return Page
     */
    public function pushError(array $options = []):self {

        # Check code
        if($options["code"] ?? false){

            # Check code is string
            if(is_string($options["code"]))

                # Set code as int
                $options["code"] = 500;

            # Get error
            $errorContent = HttpStatusCode::get($options["code"], $options);

            # Check error content
            if(!empty($errorContent))

                # Push in error
                $this->errors[] = $errorContent;

        }

        # Return self
        return $this;

    }

    /**
     * Push Exception
     * 
     * @param Exception $crazyException
     * @return Page
     */
    public function pushException(Exception $exception):self {

        # Prepare option
        $options = [
            "code"      =>  $exception->getCode(),
            "detail"    =>  $exception->getMessage(),
        ];

        # Push error in state
        $this->pushError($options);

        # Return self
        return $this;

    }

    /**
     * Push Errors
     * 
     * @param array $errors
     * @return Page
     */
    public function pushErrors(array $errors = []):self {

        # Check $errors
        if(!empty($errors))

            # Iteration errors
            foreach($errors as $error)

                # Check error
                if(is_array($errors))

                    # Push error
                    $this->pushError($error);

        # Return self
        return $this;

    }

    /**
     * Get Status Code
     * 
     * @return int
     */
    public function getStatusCode():int {

        # Set result
        $result = $this->status_code;

        # Return result
        return $result;

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

        # Check error
        if(!empty($this->errors))

            # Push errors
            $result["errors"] = $this->errors;

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

        # Check events
        if(!empty($this->events))

            # Push errors
            $result["_events"] = $this->events;

        # Check if catch state enable
        $this->_catchState($result);

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

    /**
     * Catch State
     * 
     * Check catch state
     * 
     * @param array $result of the page state
     * @return void
     */
    private function _catchState(array $result = []):void {

        # Check env
        if(
            Env::has(static::ENV_CATCH_STATE) && 
            Env::get(static::ENV_CATCH_STATE)
        )

            # New exception
            throw new CatchState("", 0, $result);

        else
        # Return state if env ?catch_state=true
        if(
            isset($_GET["catch_state"]) && 
            $_GET["catch_state"]
        ){

            # Set response
            (new ApiResponse())
                ->setStatusCode(200)
                ->pushContent("", $result)
                ->pushContext()
                ->send();

            # Stop script
            exit;

        }

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Get State
     * 
     * Get State for Router Api
     * 
     * @param array $options Option
     * @return array
     */
    public static function getState(array $options = [], array $currentItems = []):array {

        # Set result
        $result = [];

        # Class name
        $classname = Query::get()["filters"]["name"] ?? false;

        # Check controller in options
        if(
            $classname &&
            method_exists("App\Controller\App\\".$classname, "get")
        ){

            # Try
            try{

                # Current request data
                $currentRequestData = Controller::getHttpRequestData();
 
                # Set env to catch state
                Env::set([
                    static::ENV_CATCH_STATE         =>  true,
                    # "http_request_data_override"    =>  $currentRequestData["options"]["arguments"] ?? []
                    "parameters_url_override"       =>  $currentRequestData["options"]["arguments"] ?? []
                ]);

                # Prepare method string
                $method = "App\Controller\App\\$classname::get";

                # Execute method
                $method([]);

                # Clean env
                Env::remove("parameters_url_override");

            # Catch state
            }catch(CatchState $e){

                # Set result
                $result = $e->getState();

                # Clean env
                Env::remove("parameters_url_override");

            }

        }

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

    /** @var string ENV_CATCH_STATE  */
    public const ENV_CATCH_STATE = "catch_state";

}