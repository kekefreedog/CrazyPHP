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
namespace CrazyPHP\Library\State;

/**
 * Dependances
 */
use CrazyPHP\Library\Exception\HttpStatusCode;;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Context;
use Exception;

/**
 * Api
 *
 * Class for manage api state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Api {

    /** Private parameters
     ******************************************************
     */

    /** @var array $options */
    private $options = [];

    /** @var array $result */
    private $result = [];

    /** @var array $errors */
    private $errors = [];

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
     * Push Results
     * 
     * Push results in content
     * 
     * @param mixed $results
     * @param string $entity
     * @return self
     */
    public function pushResults(mixed $result = [], ?string $entity = null):self {

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
     * Push Context
     * 
     * Push config in state
     * 
     * @param bool $trigger
     * @return self
     */
    public function pushContext(bool $trigger = true):self {

        # Swith value in options
        $this->options["context"] = $trigger;

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
     * @return self
     */
    public function pushError(array $options = []):self {

        # Check code
        if($options["code"] ?? false){

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
     * @return self
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
        // Have context
        "context"   =>  false,
        // Cookie
        "cookie"    =>  false,
        // Config => ["app", "router"...]
        "config"    =>  false
    ];

}