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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Response;

/**
 * Api Response
 *
 * Class for manage your http api response...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ApiResponse extends Response {    

    /** Public parameters
     ******************************************************
     */
    
    /** @var ?StreamInterface $content */
    public $apiContent = null;

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
     * @param string|bool|array|null $content to push
     * @return void
     */
    public function pushContent(string $where = "", string|bool|array|null $content = null):self {

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
     * Send
     * 
     * Send response
     * 
     * @return void
     */
    public function send():void {

        # Create stram from content
        parent::setContent($this->apiContent);

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

        # Check if api
        if(Context::get("routes.current.group") != "api")

            # Stop function
            return;

        # Get default format of response
        $responseFormat = (string) Config::getValue("Router.parameters.api.format");

        # Set default format
        $this->setContentType($responseFormat);

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
        "errors"    =>  null,
        "results"   =>  null
    ];

    /** @const SEPARATOR */
    public const SEPARATOR = ["/", "."];

}