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
namespace CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\State\Api as ApiState;
use CrazyPHP\Library\State\Page as State;
use CrazyPHP\Library\Http\RequestBody;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\MessagePack;
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\System\Server;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Exception\CatchState;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\State\Page;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Response;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Model;
use CrazyPHP\Model\Env;

/**
 * Controller
 *
 * Class for manage you app controllers'...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Controller {

    /** Public static methods
     ******************************************************
     */

    /**
     * Structure
     * 
     * Return structure Instance
     * 
     * @return Structure
     */
    public static function Structure():Structure {

        # New structure
        $result = new Structure();

        # Return result
        return $result;

    }

    /**
     * Model
     * 
     * Return Model Instance
     * 
     * @param string $entity
     * @return Model
     */
    public static function Model(string $entity = ""):Model {

        # New structure
        $result = new Model($entity);

        # Return result
        return $result;

    }

    /**
     * State
     * 
     * Return state instance
     * 
     * @return State
     */
    public static function State():State {

        # New structure
        $result = new State();

        # Return result
        return $result;

    }

    /**
     * Api State
     * 
     * Return api state instance
     * 
     * @return ApiState
     */
    public static function ApiState():ApiState {

        # New structure
        $result = new ApiState();

        # Return result
        return $result;

    }

    /**
     * ApiResponse
     * 
     * Return api reponse instance
     * 
     * @return ApiResponse
     */
    public static function ApiResponse():ApiResponse {

        # New structure
        $result = new ApiResponse();

        # Return result
        return $result;

    }

    /**
     * Reponse
     * 
     * Return reponse instance
     * 
     * @return Response
     */
    public static function Response(mixed $request = null):Response {       
        
        # Catch state if not done previously or no state
        static::_catchState();

        # New structure
        $result = new Response($request);

        # Return result
        return $result;

    }

    /** Public static methods | Context
     ******************************************************
     */

    /**
     * Get context
     * 
     * Get context of current route
     * 
     * @param string $key Key of the context to get
     * @return array
     */
    public static function getContext(string $key = ""):array {

        # Set result
        $result = Context::get($key);

        # Return result
        return $result;

    }

    /**
     * Get Parameters Url
     * 
     * Get parameters from url
     * 
     * @param string $name Name of the parameter  
     * @return string|int|array|null
     */
    public static function getParametersUrl(string $name = ""):string|int|array|null {

        # Set result
        $result = null;

        # Check env http_request_data_override
        if(Env::has("parameters_url_override") && is_array(Env::get("parameters_url_override"))){

            # New parameters
            $parameters = Env::get("parameters_url_override");

            # Check name
            if(!$name){

                # Get value from context
                $result = $parameters;

            }else{

                # Get value from parameters
                $result = $parameters[$name] ?? null;

            }

        }else{

            # Check name
            if(!$name){

                # Get value from context
                $result = Context::get("routes.current.parameters");

            }else{

                # Get value from context
                $result = Context::get("routes.current.parameters.$name");

            }

        }

        # Check if result is array
        if(is_array($result))

            # Change key case
            $result = array_change_key_case($result);

        # Return result
        return $result;

    }

    /**
     * Get Http Request Data
     * 
     * Getting data from an HTTP request
     * 
     * @return array|string
     */
    public static function getHttpRequestData():array|string {

        # Set result
        $result = [];

        # Switch
        switch($_SERVER['REQUEST_METHOD']){

            # Get
            case 'GET':

                # Set result
                $result = $_GET;
                
                # Break
                break;

            # Post
            case 'POST':

                # Set result
                $result = $_POST + $_FILES;

                # Check result
                if(empty($result)){

                    # Check HTTP_CONTENT_TYPE
                    if(Server::hasContentType() && Server::getContentType() === "application/msgpack"){

                        # Get data
                        $result = MessagePack::decode((string) file_get_contents('php://input'));
    
                    }else
                    # Check if json
                    if(Server::hasContentType() && Server::getContentType() === "application/json"){
                
                        # Try to get data
                        $result = json_decode(file_get_contents('php://input'), true);

                    # Other type
                    }else{
                
                        # Set result
                        $result = file_get_contents('php://input');

                    }

                }
                
                # Break
                break;

            # PUT, DELETE, PATCH
            case 'PUT':
            case 'DELETE':
            case 'PATCH':

                # Resolve the actual media type before consuming a multipart request
                $contentType = strtolower(trim(explode(";", $_SERVER["CONTENT_TYPE"] ?? $_SERVER["HTTP_CONTENT_TYPE"] ?? "")[0]));

                # Keep multipart streaming and upload ownership inside PHP on supported runtimes
                if($contentType === "multipart/form-data"){

                    # Reuse one parsed body across model middleware and controller dispatch
                    $parsedData = RequestBody::multipartRequest();

                    # Preserve the framework's simulated form globals
                    $_POST = $parsedData["post"];

                    # Preserve PHP upload metadata, including repeated-file arrays
                    $_FILES = $parsedData["files"];

                    # Return the exact fields and files checked by middleware
                    $result = $_POST + $_FILES;

                }else{

                    # Bound nonmultipart reads by the same server-owned request ceiling
                    $rawData = RequestBody::read();

                    # Decode the already-read MessagePack bytes without consuming the stream again
                    if($contentType === "application/msgpack"){

                        # Preserve the existing structured binary transport
                        $result = MessagePack::decode($rawData);

                    }else if(Json::check($rawData)){

                        # Preserve existing JSON values and simulated form state
                        $result = $_POST = Json::decode($rawData);

                    }else if($contentType === "application/x-www-form-urlencoded"){

                        # Parse ordinary URL-encoded mutations without creating any upload files
                        parse_str($rawData, $_POST);

                        # Retain the decoded form fields
                        $result = $_POST;

                    }else if($rawData === ""){

                        # Preserve empty-body operations such as DELETE
                        $result = [];

                    }else{

                        # Refuse nonmultipart input rather than guessing a delimiter from its first line
                        throw new CrazyException("Unsupported request body format.", 400);

                    }

                }

                # Break
                break;

            # Options
            case 'OPTIONS':

                # Set result
                $result = [];
                
                # Break
                break;

            default:

                # Set result
                $result = [];
                
                # Break
                break;
        }

        # Return result
        return $result;

    }

    /** Public static methods | Header
     ******************************************************
     */

    /**
     * Get Request Headers
     * 
     * Get header given on request
     * 
     * @param string $name Name of the header
     * @return string|int|array|bool|null
     */
    public static function getHeaderFromRequest(string $name = ""):string|int|array|bool|null {

        # Set result
        $result = null;

        # check name
        if(!$name)

            # Return result
            return $result;

        # Get value from context
        $result = Context::get("routes.current.headers.$name");

        # Return value
        return $result;

    }

    /**
     * Get All Request Header
     * 
     * Get all request given on request
     * 
     * @return array|null
     */
    public static function getHeadersFromRequest():array|null {

        # Set result
        $result = null;

        # Get header
        $result = Context::get("routes.current.headers");

        # return result
        return $result;

    }

    /** Public static methods | Last Updated
     ******************************************************
     */

    /**
     * If client is not updated
     * 
     * Check if 
     */
    public static function clientIsNotUpToDate(DateTime|\DateTime|null $lastModified):bool {

        # Set result
        $result = true;

        # Check input
        if($lastModified === null || ($ifModifiedSince = Context::get("routes.current.headers.if-modified-since")) === null)

            # return result
            return $result;

        # Convert header to datetime
        $ifModifiedSinceDate = new DateTime((string) $ifModifiedSince);

        # Comparaison
        $result = $ifModifiedSinceDate <= $lastModified;

        # return result
        return $result;

    }

    /** Public static methods | Config
     ******************************************************
     */

    /**
     * Get Config
     * 
     * Get Config
     * 
     * @param string|array $configs
     * @return null|array
     */
    public static function getConfig(string|array $configs = []):null|array {

        # Set result
        $result = null;

        # Check configs
        if(empty($configs))

            # Stop function
            return $result;

        # Get configs
        $result = Config::get($configs);

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Parse Multipart Data
     *
     * Preserve the legacy raw-body API with bounded, request-scoped temporary uploads.
     *
     * @param string $rawData Complete multipart body
     * @return array Parsed post and files collections
     */
    public static function parseMultipartData($rawData):array {

        # Prepare result through the shared bounded parser
        $result = RequestBody::multipart($rawData);

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Catch State
     * 
     * Check catch state
     * 
     * @param array $result of the page state
     * @return void
     */
    private static function _catchState(array $result = []):void {

        # Check env
        if(
            Env::has(Page::ENV_CATCH_STATE) && 
            Env::get(Page::ENV_CATCH_STATE)
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
                # ->pushContext()
                ->addCookie(Page::COOKIE_UUID_STATE, "")
                ->send();

            # Stop script
            exit;

        }

    }
    

}