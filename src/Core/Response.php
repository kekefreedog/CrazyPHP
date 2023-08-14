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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Nyholm\Psr7\Response as Psr17Response;
use Psr\Http\Message\ResponseInterface;
use CrazyPHP\Exception\CrazyException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\StreamInterface;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\File\File;

/**
 * Response
 *
 * Class for manage your http response...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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

    /** @var array */
    public $header = [];

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # New instance
        $this->instance = new Psr17Factory();

        # Extra Infos In Header
        $this->extraInfoInHeader();
        
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

        # Check if array
        if(is_array($body))

            # Check if know format
            if(isset(File::MIMTYPE_TO_CLASS[self::getContentType()])){

                # Get instance
                $instance = File::MIMTYPE_TO_CLASS[self::getContentType()];

                # Encode body
                $body = $instance::encode($body);
            
            }

        # Get type object
        if(gettype($body) == "object" && method_exists($body, "getPath")){
            
            # Set Stream
            $stream = $this->instance->createStreamFromFile($body->getPath());

            # Set content type
            $this->setContentType($body->getMimeType());

        }else
        # Get type object
        if(is_resource($body))
            
            # Set Stream
            $stream = $this->instance->createStreamFromResource($body);
            
        else
        # Create stream
        if(gettype($body) == "StreamInterface")

            # Set stream
            $stream = $body;
            
        else
        # If String
        if(is_string($body))
        
            # Set Stream
            $stream = $this->instance->createStream($body);
                
        else

            # New error
            throw new CrazyException(
                "Body given is not valid type", 
                500,
                [
                    "custom_code"   =>  "core-001",
                ]
            );

        # Set content
        $this->content = $stream;

        # Return self
        return $this;

    }

    /**
     * Set content type
     * 
     * Set content type of the response
     * 
     * @param string $name Type of content
     * @param string $charset Character settings of the content type
     * @return self
     */
    public function setContentType(string $name = "html", string $charset = "UTF-8"):self {

        # Check name
        if(!$name)

            # Return self
            return $this;

        # Check name is in extenson to mimetype
        if(array_key_exists(strtolower($name), File::EXTENSION_TO_MIMETYPE))

            #Set name
            $name = File::EXTENSION_TO_MIMETYPE[$name];

        else

            $name = $name;

        # Check charset
        if($charset)

            $name .= "; charset=$charset";

        # Push name in header
        $this->header["content-type"] = $name;

        # Return self
        return $this;

    }

    /**
     * Get content type
     * 
     * Get content type of response
     * 
     * @param bool $hideCharset
     * @return string
     */
    public function getContentType(bool $hideCharset = true):string {

        # Set result
        $result = "";

        # Check content-type is set
        if(isset($this->header["content-type"]))
        
            # Set result
            $result = $this->header["content-type"];

        # Check withCharset
        if($hideCharset)

            $result = explode("; ", $result, 2)[0];

        # Return result
        return $result;

    }

    /**
     * Add Header
     * 
     * Add new header
     * 
     * @param string $name Name of the header
     * @param string|bool|null $value Value of the header
     * @return self
     */
    public function addHeader(string $name = "", string|bool|null $value = ""):self {

        # Add header in current instance
        $this->header[$name] = $value;

        # Return self
        return $this;

    }

    /**
     * Add Last Modified
     * 
     * Add Last Modified date in header
     * @param DateTime|\DateTime|null $datetime Date time of the last modification on current content
     * @return self
     */
    public function addLastModified(DateTime|\DateTime|null $datetime = null):self {

        # Check time
        if($datetime === null)

            # Return self
            return $this;

        # Set value on header
        $this->addHeader("Last-Modified", $datetime->format(Datetime::RFC7231));

        # Return self
        return $this;

    }

    /**
     * Allow Cache
     * 
     * Allow Content to be cached by client
     * 
     * @param string $directive Directive of cache (public, private)
     * @param int $maxAge Max age of cache
     * @param bool $noTransform Not allow client to modify content
     * @return self
     */
    public function allowCache(string $directive = "public", int $maxAge = 604800, bool $noTransform = false):self {

        # Declare value
        $value = "";

        # Check directive
        if($directive)

            # Append directive in value
            $value .= $directive;

        # Check max age
        if($maxAge)

            # Append max age
            $value .= ($value ? ", " : "")."max-age=$maxAge";

        # Check no transform
        if($noTransform)

            # Append no tranform
            $value .= ($value ? ", " : "")."no-transform";

        # Check value
        if($value)

            # Push on header
            $this->addHeader("Cache-Control", $value);

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

        # Create response
        $this->response = new Psr17Response(
            $this->statutCode, 
            $this->header, 
            $this->content
        );

    }

    /**
     * Just for hide errors in VSCODE
     */
    public function pushContent():self {
        # Return self
        return $this;
    }
    public function pushContext():self {
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

        # Check response
        if($this->response === null)

            # Prepare response
            $this->prepare();

        # Emit result
        (new SapiEmitter())->emit($this->response);

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Extra Info In Header
     * 
     * Add extra info in headers like :
     * - Crazy-Version
     * - Crazy-Hash
     */
    private function extraInfoInHeader():void {

        # Add crazy version
        $this->header["Crazy-Version"] = Composer::get("version", "@crazyphp_root/composer.json");

        # Add crazy hash
        $this->header["Crazy-Hash"] = Config::getValue("Front.lastBuild.hash");

    }

}