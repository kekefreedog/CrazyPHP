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
use CrazyPHP\Library\State\Api as ApiState;
use CrazyPHP\Library\State\Page as State;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Exception\CatchState;
use CrazyPHP\Library\File\Config;
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
    public static function Response():Response {       
        
        # Check env
        if(Env::has(State::ENV_CATCH_STATE) && Env::get(State::ENV_CATCH_STATE))

            # New exception
            throw new CatchState();

        # New structure
        $result = new Response();

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
     * @return array|null
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
     * @return array
     */
    public static function getHttpRequestData():array {

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
                $result = $_POST;

                # Check result
                if(empty($result)){
                    
                    # Try to get data
                    $result = json_decode(file_get_contents('php://input'), true);

                }
                
                # Break
                break;

            # PUT, DELETE, PATCH
            case 'PUT':
            case 'DELETE':
            case 'PATCH':

                # Set raw data
                $rawData = file_get_contents("php://input");

                # Check if formdata
                if(strpos($rawData, 'Content-Disposition: form-data;') !== false){

                    /**
                     * @source https://stackoverflow.com/questions/5483851/manually-parse-raw-multipart-form-data-data-with-php
                     */

                    // read incoming data
                    $input = file_get_contents('php://input');
                    
                    // grab multipart boundary from content type header
                    preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
                    $boundary = $matches[1];
                    
                    // split content by boundary and get rid of last -- element
                    $a_blocks = preg_split("/-+$boundary/", $input);
                    array_pop($a_blocks);
                        
                    // loop data blocks
                    foreach ($a_blocks as $id => $block)
                    {
                      if (empty($block))
                        continue;
                      
                      // you'll have to var_dump understand this and maybe replace \n or \r with a visibile char
                      
                      // parse uploaded files
                      if (strpos($block, 'application/octet-stream') !== FALSE)
                      {
                        // match "name", then everything after "stream" (optional) except for prepending newlines 
                        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
                      }
                      // parse all other fields
                      else
                      {
                        // match "name" and optional value in between newline sequences
                        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                      }
                      $data[$matches[1]] = $matches[2] ?? "";
                    } 

                }else{

                    # Parse value from input
                    parse_str($rawData, $data);

                }

                # Set result
                $result = $data;
                
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
    

}