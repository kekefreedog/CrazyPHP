<?php declare(strict_types=1);
/**
 * Exception
 *
 * Manipulate Exception
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Exception;

/** 
 * Dependances
 */
use CrazyPHP\Library\State\Page as State;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Exception\Error;
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Response;
use CrazyPHP\Model\Env;
use Exception;

/**
 * ExceptionResponse
 *
 * Response from exception catched
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class ExceptionResponse {

    /** Public methods
     ******************************************************
     */

    /** @var Exception $exception */
    public Exception $exception;

    /** @var string $mimeType */
    public string $mimeType; 

    /** Private methods
     ******************************************************
     */

    /** @var mixed $render */
    private mixed $render;

    /** @var $data */
    private $data = [];

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(Exception|CrazyException $exception){

        # Set env to use file cache
        Env::set(["cache_driver" => "Files"]);

        # Ingest exception
        $this->ingestException($exception);

        # Check request Accept
        $this->checkRequestAccept();
        
        # Prepare View
        $this->prepareView();

        # Prepare Data
        $this->prepareData();

        # Render
        $this->render();

        # Push to log
        $this->pushToLog();

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest Exception
     * 
     * @param Exception $exception
     * @return void
     */
    private function ingestException(Exception|CrazyException $exception):void {

        # Ingest exception
        $this->exception = $exception;

    }

    /**
     * Check Request Accept
     * 
     * Check if request is html or json...
     * 
     * @return void
     */
    private function checkRequestAccept():void {

        # Get mimetype
        $mimeType = Header::getHeaderAccept();

        # Set mimetype
        $this->mimeType = $mimeType;

    }

    /**
     * Prepare view
     * 
     * Load the view depending of accept
     * 
     * @return void
     */
    private function prepareView():void {

        # Check mimetype is html
        if($this->mimeType === "text/html")

            // Set renderCallable
            $this->render = function($data){

                # Get public folder
                $publicFolder = Config::getValue("App.public") ?: "public";

                # Template path
                $templatePath = str_replace($publicFolder, "", getcwd())."app/Environment/Page/Error/template.hbs";

                # Prepare state
                $state = (new State())
                    ->pushException($this->exception)
                    ->render()
                ;

                # Set structure
                $structure = (new Structure())
                    ->setDoctype()
                    ->setLanguage()
                    ->setHead()
                    ->setJsScripts("Error")
                    ->setBodyTemplate($templatePath, null, $state, "Error")
                    ->prepare()
                    ->render()
                ;

                # Set response
                return (new Response())
                    ->setStatusCode($data["errors"][0]["code"] ?? Error::DEFAULT["code"])
                    ->setContent($structure);

            };

        else
        # Check mimetype is supported by file class
        if(in_array($this->mimeType, File::EXTENSION_TO_MIMETYPE))

            // Set renderCallable
            $this->render = function($data){

                # Set response
                return (new ApiResponse())
                    ->setContentType($this->mimeType)
                    ->setStatusCode($data["errors"][0]["code"] ?? Error::DEFAULT["code"])
                    ->setContent($data);

            };

        else

            // Set renderCallable
            $this->render = function($data){

                # Set response
                return (new Response())
                    ->setStatusCode($data["errors"][0]["code"] ?? Error::DEFAULT["code"])
                    ->setContent($this->exception->__toString());

            };

    }

    /**
     * Prepare Data
     * 
     * @return void
     */
    private function prepareData():void {

        # Transform exception to error
        $error = Error::fromException($this->exception);

        # Push to data
        $this->data["errors"][] = $error;

    }

    /**
     * Render
     * 
     * Load the view depending of accept
     * 
     * @return void
     */
    private function render():void {

        # Set render
        $render = $this->render;

        # Call render with data
        $response = $render($this->data);

        # Send response
        $response->send();

    }

    /**
     * Prepare Data
     * 
     * @return void
     */
    private function pushToLog():void {

        

    }

}