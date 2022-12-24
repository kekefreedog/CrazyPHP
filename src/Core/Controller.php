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
use CrazyPHP\Library\Router\Router as LibraryRouter;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Mezon\Router\Router as VendorRouter;
use CrazyPHP\Exception\CrazyException;
use Nyholm\Psr7\Factory\Psr17Factory;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Context;
use Nyholm\Psr7\Request;

/**
 * Controller
 *
 * Class for manage you app controllers'...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Controller {

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
    public static function getParametersUrl(string $name = ""):string|array|null {

        # Set result
        $result = null;

        # Check name
        if(!$name)

            # Get value from context
            $result = Context::get("routes.current.parameters");

        else

            # Get value from context
            $result = Context::get("routes.current.parameters.$name");

        # Check if result is array
        if(is_array($result))

            # Change key case
            $result = array_change_key_case($result);

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