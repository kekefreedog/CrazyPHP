<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Response;

/**
 * Api Response
 *
 * Class for manage your http api response...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ApiResponse extends Response {    

    /** Public parameters
     ******************************************************
     */
    
    /** @var ?StreamInterface $content */
    public $apiContent = null;
    
    /** @var string|null $engineInstance Classe for convert to response format */
    public $engineInstance = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructor
        parent::__construct();

        # Api set default parameters
        $this->_setDefaultParametersApi();

        # Set default array return
        $this->_prepareDefaultContent();
        
    }

    /** Public methods
     ******************************************************
     */

    /**
     * Set content
     * 
     * Set content of response
     * 
     * @param $body
     * @return self
     */
    public function setContent(/* string|array|resource|StreamInterface */$body = ""):self {

        # Check if stream
        if(gettype($body) == "StreamInterface")

            # New Exception
            throw new CrazyException(
                "StreamInterface is not supported by set content in api context",
                500,
                [
                    "custom_code"   =>  "apiResponse-001",
                ]
            );

        # Push content in Api Content
        $this->apiContent = $body;

        # Return self
        return $this;

    }

    /**
     * Push Content
     * 
     * Push Content on Api Response
     * 
     * @param string $where Where put content in content
     * @param mixed $content to push
     * @return self
     */
    public function pushContent(string $where = "", mixed $content = null):self {

        # Check where
        if(!$where)

            # Push content
            $this->apiContent = $content;

        else{

            # Parse where
            $where = str_replace(self::SEPARATOR, "___", $where);

            # Explode where 
            $keys = explode("___", $where);

            # Set cursors
            $cursor = &$this->apiContent;

            # Check config file
            if(!empty($keys))

                # Iteration filedata
                $i=0;while(isset($keys[$i])){

                    # Check key exists in api content
                    $cursor[$keys[$i]] = null;

                    # Set cursor
                    $cursor = &$cursor[$keys[$i]];

                $i++;}

            # Push body in cursor
            $cursor = $content;

        }

        # Return self
        return $this;

    }

    /**
     * Push Context
     * 
     * Push Context in Api Response
     * @return self
     */
    public function pushContext():self {

        # Get context
        $context = Context::get();

        # Push context
        $this->pushContent("_context", $context);

        # Return self
        return $this;

    }

    /**
     * Send
     * 
     * Send response
     * 
     * @return void
     */
    public function send():void {

        # Convert with engine the api content
        $dumpApiContent = $this->engineInstance::encode($this->apiContent);

        # Create stram from content
        parent::setContent($dumpApiContent);

        # Create stream from content
        parent::send();

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Set Default Parameters Api
     * 
     * @return void
     */
    private function _setDefaultParametersApi():void {

        # Get default format of response
        $responseFormat = Config::getValue("Router.parameters.api.format");

        # Check response format
        if(!$responseFormat || $responseFormat === null || !is_string($responseFormat))

            # New Exception
            throw new CrazyException(
                (
                    $responseFormat ? 
                        "\"$responseFormat\"" :
                            "Response format"
                ) . " isn't valid...",
                500,
                [
                    "custom_code"   =>  "apiResponse-002",
                ]
            );

        # Set default format
        $this->setContentType($responseFormat);

        # Get and set engine to use
        $engine = File::MIMTYPE_TO_CLASS[File::EXTENSION_TO_MIMETYPE[$responseFormat] ?? null] ?? null;

        # Check engine
        if(!$engine || $engine === null)

            # New Exception
            throw new CrazyException(
                "\"$responseFormat\" isn't supported yet by framework...",
                500,
                [
                    "custom_code"   =>  "apiResponse-003",
                ]
            );

        # Set engine
        $this->engineInstance = $engine;

    }

    /**
     * Prepare Default Content
     * 
     * @return void
     */
    private function _prepareDefaultContent():void {

        # Set content
        $this->apiContent = self::DEFAULT_CONTENT;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array DEFAULT_CONTENT */
    public const DEFAULT_CONTENT = [
        # "errors"    =>  null,
        "results"   =>  null
    ];

    /** @const SEPARATOR */
    public const SEPARATOR = ["/", "."];

}