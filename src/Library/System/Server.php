<?php declare(strict_types=1);
/**
 * System
 *
 * Usefull class for manipulate system
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\System;

/** 
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;

/**
 * Server
 *
 * Methods for manage requests of server
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Server {

    /** Public Static Methods | Content Type
     ******************************************************
     */

    /**
     * Has Content Type
     * 
     * Check $_SERVER has content type
     * 
     * @return bool
     */
    public static function hasContentType():bool {

        # Set result
        $result = false;

        # Iteration CONTENT_TYPE_KEYS
        foreach(static::CONTENT_TYPE_KEYS as $key)

            # Check
            if(array_key_exists($key, $_SERVER)){

                # Set result
                $result = true;

                # Stop iteration
                break;

            }

        # Return result
        return $result;

    }

    /**
     * Get Content Type
     * 
     * Get value from $_SERVER for content type
     * 
     * @return string|null
     */
    public static function getContentType():string|null {

        # Set result
        $result = null;

        # Iteration CONTENT_TYPE_KEYS
        foreach(static::CONTENT_TYPE_KEYS as $key)

            # Check
            if(array_key_exists($key, $_SERVER)){

                # Set result
                $result = $_SERVER[$key];

                # Stop iteration
                break;

            }

        # Return result
        return $result;

    }

    /**
     * Is Iframe
     * 
     * Detect if the current request is being loaded inside an iframe.
     *
     * @param bool $simulate
     * @return bool
     */
    public static function isIframe(bool $simulate=false):bool {

        # Set result
        $result = $simulate;

        # Chrome, Edge, Firefox send Sec-Fetch-Dest header
        if(!empty($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe')

            # Set result
            $result = true;
        
        else
        # Safari (older versions) sometimes omit Sec-Fetch headers
        # You can add your own client-side header via JS if needed.
        if(!empty($_SERVER['HTTP_X_FRAME_CONTEXT']) && $_SERVER['HTTP_X_FRAME_CONTEXT'] === 'true') 

            # Set result
            $result = true;

        else
        # Optionally, you can add a GET parameter fallback (if you pass one intentionally)
        if(isset($_GET['iframe']) && $_GET['iframe'] === '1')

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /** Public Static Methods | Root
     ******************************************************
     */

    /**
     * Get Index Root
     * 
     * @return string
     */
    public static function getIndexRoot():string {

        # Set result
        $result = str_replace("index.php", "", $_SERVER["SCRIPT_FILENAME"] ?? ( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["PHP_SELF"] ));

        # Return result
        return $result;

    }
    
    /**
    * Get Web Root
    *
    * Retrieve absolute web root URL (scheme + host)
    *
    * @return string
    */
    public static function getWebRoot():string {

        # Detect scheme (proxy safe)
        $scheme = (
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ) ? 'https' : 'http';

        # Detect host (proxy safe)
        $host = $_SERVER['HTTP_X_FORWARDED_HOST']
            ?? $_SERVER['HTTP_HOST']
            ?? 'localhost';

        # Return web root
        return $scheme . '://' . $host;

    }

    /**
     * Get Web Request
     * 
     * Get current web request as a relative URL (no scheme, no host)
     *
     * Examples:
     *  - /assets/Png/Plan/file.png
     *  - /login?redirect=/dashboard
     *
     * @return string
     */
    public static function getWebRequest():string {

        # Set result
        $result = "";

        # CLI fallback
        if(PHP_SAPI === 'cli'){

            # Set result
            $result = "/";

        }else{

            # Get uri
            $uri = $_SERVER['REQUEST_URI'] ?? '/';

            # Normalize path
            $path = parse_url($uri, PHP_URL_PATH) ?? '/';

            # Normalize query
            $query = parse_url($uri, PHP_URL_QUERY);

            # Set result
            $result = $query ? $path . '?' . $query : $path;

        }

        # Return result
        return $result;

    }


    /** Public Constants
     ******************************************************
     */

    /** @var array CONTENT_TYPE_KEYS */
    public const CONTENT_TYPE_KEYS = ["HTTP_CONTENT_TYPE", "CONTENT_TYPE"];


}