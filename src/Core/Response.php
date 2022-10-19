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
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\StreamInterface;
use \resource;

/**
 * Response
 *
 * Class for manage your http response...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Response {

    /** Public parameters
     ******************************************************
     */

    /** @var ?Psr17Factory $instance */
    public $instance = null;

    /** @var ?StreamInterface $content */
    public $content = null;

    /** @var ?int $statutCode */
    public $statutCode = 200;

    /** @var ?ResponseInterface $response */
    public $response = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # New instance
        $this->instance = new Psr17Factory();
        
    }

    /** Public methods
     ******************************************************
     */

    /**
     * Set content
     * 
     * Set content of response
     * 
     * @param string|resource|StreamInterface $body
     * @return self
     */
    public function setContent(string|resource|StreamInterface $body = ""):self {

        # Create stream
        $stream = $this->instance->createStream($body);

        # Set content
        $this->content = $stream;

        # Return self
        return $this;

    }

    /**
     * Set Statut Code
     * 
     * Set statut code of response
     * 
     * @param int
     * @return self
     */
    public function setStatusCode(int $statutCode = 200):self {

        # Set status code
        $this->statutCode = $statutCode;

        # Return self
        return $this;

    }

    /**
     * Prepare
     * 
     * Prepare response
     * 
     * @return void
     */
    public function prepare():void {

        # Reponse fill
        $this->response = $this
            ->instance
            ->createResponse($this->statutCode)
            ->withBody($this->content)
        ;

    }

    /**
     * Send
     * 
     * Send response
     * 
     * @return void
     */
    public function send():void {

        # Check response
        if($this->response === null)

            # Prepare response
            $this->prepare();

        # Emit result
        (new SapiEmitter())->emit($this->response);

    }

}