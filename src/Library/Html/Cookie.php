<?php declare(strict_types=1);
/**
 * Cookie
 *
 * Class for manage request and response cookies
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog
 * @copyright  2022-2024
 */
namespace CrazyPHP\Library\Html;

/** 
 * Dependances
 */
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cookie
 *
 * Class for manage request and response cookies
 *
 * @package    kzarshenas/crazyphp
 */
class Cookie {

    /** Parameters
     ******************************************************
     */

    /** @var ?ServerRequestInterface $request */
    private $request;

    /** @var ResponseInterface $response */
    private $response;

    /** @var array $_local */
    private $_local = [];

    /** Public methods
     ******************************************************
     */

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param ResponseInterface $response Response instance
     * @param ?ServerRequestInterface $request Request instance
     * @return self
     */
    public function __construct(ResponseInterface $response, ?ServerRequestInterface $request = null){

        # Set request
        $this->request = $request;

        # Set response
        $this->response = $response;

    }

    /**
     * add
     * 
     * Add cookie to response
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param array $options Cookie options
     * @return self
     */
    public function add(string $name, string $value, array $options = []):self {

        # Store locally
        $this->_local[$name] = $value;

        # Build cookie
        $cookie = $this->_build($name, $value, $options);

        # Append header
        $this->response = $this->response->withAddedHeader("Set-Cookie", $cookie);

        # Return self
        return $this;

    }

    /**
     * get
     * 
     * Get cookie
     * 
     * @param string $name Cookie name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $name, mixed $default = null){

        # Set result
        $result = $default;

        # Set cookies
        $cookies = [];

        # Retrieve request cookies
        if($this->request)

            # Get request cookies
            $cookies = $this->request->getCookieParams();

        # Merge local cookies
        $cookies = array_merge($cookies, $this->_local);

        # Check cookie
        if(isset($cookies[$name]))

            # Get request cookie
            $result = $cookies[$name];

        # Return result
        return $result;

    }

    /**
     * Get All
     * 
     * Get all cookies
     * 
     * @return array
     */
    public function getAll():array {

        # Set result
        $result = [];

        # Retrieve request cookies
        if($this->request)

            # Get request cookie
            $result = $this->request->getCookieParams();

        # Merge local cookies
        $result = array_merge($result, $this->_local);

        # Return result
        return $result;

    }

    /**
     * delete
     * 
     * Delete cookie
     * 
     * @param string $name Cookie name
     * @param array $options Cookie options
     * @return self
     */
    public function delete(string $name, array $options = []):self {

        # Remove local cookie
        if(isset($this->_local[$name]))
            
            # Delete cookie by name
            unset($this->_local[$name]);

        # Force expire
        $options["expires"] = time() - 3600;

        # Set max age
        $options["max-age"] = 0;

        # Add expired cookie
        return $this->add($name, "", $options);

    }

    /**
     * deleteAll
     * 
     * Delete all cookies
     * 
     * @return self
     */
    public function deleteAll():self {

        # Loop cookies
        foreach($this->getAll() as $name => $value)

            # Delete cookie by name
            $this->delete($name);

        # Return self
        return $this;

    }

    /**
     * getResponse
     * 
     * Get modified response
     * 
     * @return ResponseInterface
     */
    public function getResponse():ResponseInterface {

        # Return response
        return $this->response;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Build
     * 
     * Build cookie header string
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param array $options Cookie options
     * @return string
     */
    private function _build(string $name, string $value, array $options = []):string {

        # Default options
        $defaults = [
            "expires"   =>  null,
            "path"      =>  "/",
            "domain"    =>  null,
            "secure"    =>  false,
            "httponly"  =>  true,
            "samesite"  =>  null,
            "max-age"   =>  null,
        ];

        # Merge options
        $options = array_merge($defaults, $options);

        # Base cookie
        $cookie = rawurlencode($name) . "=" . rawurlencode($value);

        # Expires
        if($options["expires"] !== null){

            # Set timestamp
            $timestamp = $options["expires"] instanceof \DateTimeInterface
                ? $options["expires"]->getTimestamp()
                : (int) $options["expires"]
            ;

            # Set cookie
            $cookie .= "; Expires=" . gmdate("D, d M Y H:i:s \G\M\T", $timestamp);

        }

        # Max-Age
        if($options["max-age"] !== null)

            # Push in cookie
            $cookie .= "; Max-Age=" . (int) $options["max-age"];

        # Path
        if($options["path"])

            # Push in cookie
            $cookie .= "; Path=" . $options["path"];

        # Domain
        if($options["domain"])

            # Push in cookie
            $cookie .= "; Domain=" . $options["domain"];

        # Secure
        if($options["secure"])

            # Push in cookie
            $cookie .= "; Secure";

        # HttpOnly
        if($options["httponly"])

            # Push in cookie
            $cookie .= "; HttpOnly";

        # SameSite
        if($options["samesite"]){

            # Normalize
            $sameSite = ucfirst(strtolower($options["samesite"]));

            # Validate
            if(in_array($sameSite, ["Lax", "Strict", "None"], true))

                # Push in cookie
                $cookie .= "; SameSite=" . $sameSite;

        }

        # Return cookie string
        return $cookie;

    }

}
