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
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Exception\CrazyException;
use Psr\Http\Message\StreamInterface;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\State\Page;
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
    
    /** @var null|StreamInterface|array $apiContent */
    public null|StreamInterface|array $apiContent = null;
    
    /** @var string|null $engineInstance Classe for convert to response format */
    public $engineInstance = null;

    /** @var int Minimum serialized body size for optional HTTP gzip */
    private const GZIP_MIN_BYTES = 1024;

    /** @var bool Whether the format comes from application defaults */
    private bool $_negotiateFormat = true;

    /** @var bool Whether the client accepts the selected representation */
    private bool $_acceptableFormat = true;

    /**
     * Constructor
     * 
     * @param ServerRequestInterface|null $request Request supplying negotiation headers
     */
    public function __construct(?ServerRequestInterface $request = null){

        # Parent constructor
        parent::__construct($request);

        # Api set default parameters
        $this->_setDefaultParametersApi();

        # Allow negotiation of the configured default
        $this->_negotiateFormat = true;

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
     * @param mixed $body
     * @return self
     */
    public function setContent(/* string|array|resource|StreamInterface */mixed $body = ""):self {

        # Check if stream
        if(gettype($body) == "StreamInterface")

            # New Exception
            throw new CrazyException(
                "StreamInterface is not supported by set content in api context",
                500,
                [
                    "custom_code"    =>  "apiResponse-001",
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
            if(!empty($keys)){

                # Iteration filedata
                $i=0;while(isset($keys[$i])){

                    # Check key exists in api content
                    $cursor[$keys[$i]] = null;

                    # Set cursor
                    $cursor = &$cursor[$keys[$i]];

                $i++;}

            }

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
     * Push State
     * 
     * Push state in Api Response
     * @return self
     */
    public function pushState(Page $state):self {

        # Render state
        $stateRendered = $state->render();

        # Push context
        $this->pushContent(content:$stateRendered);

        # Return self
        return $this;

    }

    /**
     * Prepare API Response
     *
     * Select the representation and HTTP content coding before emission.
     *
     * @return void
     */
    public function prepare():void {

        # Prepare the status and application headers using PSR-7 normalization
        parent::prepare();

        # Resolve the configured or explicitly selected media type
        $type = $this->getContentType();

        # Limit automatic format selection to the JSON and MessagePack API modes
        $binaryApi = in_array($type, ["application/json", "application/msgpack"], true);

        # Track negotiation inputs for shared caches
        $vary = [];

        # Identify statuses which permit a response body
        $hasBody = $this->statutCode >= 200 && !in_array($this->statutCode, [204, 205, 304], true);

        # Vary cache entries when the configured format participates in negotiation
        if($binaryApi && $this->_negotiateFormat)

            # Record the media range as a negotiation input
            $vary[] = "Accept";

        # Reject requests that exclude all supported representations
        if($hasBody && !$this->_acceptableFormat)

            # Suppress the response body
            $type = "";

        # Read accepted content codings
        $encoding = $this->request?->getHeaderLine("Accept-Encoding") ?? ($_SERVER["HTTP_ACCEPT_ENCODING"] ?? "");

        # Parse content coding preferences
        $codings = self::_weights($encoding);

        # Resolve identity, which is acceptable by default
        $identity = $codings["identity"] ?? (($codings["*"] ?? 1) == 0 ? 0 : 1);

        # Resolve explicit or wildcard gzip support
        $gzip = $codings["gzip"] ?? $codings["*"] ?? 0;

        # Leave wire compression to PHP when its output handler owns the response
        $sapiCompression = !in_array(strtolower((string)ini_get("zlib.output_compression")), ["", "0", "off"], true) || in_array("ob_gzhandler", ob_list_handlers(), true);

        # Preserve application-owned content encodings
        $automaticCoding = $binaryApi && !$sapiCompression && !$this->response->hasHeader("Content-Encoding");

        # Record coding negotiation even for identity responses
        if($binaryApi)

            # Keep compressed variants separate in shared caches
            $vary[] = "Accept-Encoding";

        # Merge cache variation with existing application headers
        $existing = array_map("trim", explode(",", $this->response->getHeaderLine("Vary")));

        # Preserve a wildcard Vary value
        if(!in_array("*", $existing, true)){

            # Add missing tokens case-insensitively
            foreach($vary as $field)

                # Check the existing header list
                if(!in_array(strtolower($field), array_map("strtolower", $existing), true))

                    # Append the new negotiation field
                    $this->response = $this->response->withAddedHeader("Vary", $field);

        }

        # Prepare an empty body for statuses without content or rejected requests
        $body = "";

        # Build the selected representation
        if($type !== "" && $hasBody){

            # Serialize the API envelope using the negotiated or explicitly selected encoder
            $body = $this->engineInstance::encode($this->apiContent);

            # Resolve configuration only for a client that can consume gzip
            if($automaticCoding && $gzip > 0 && $gzip >= ($codings["identity"] ?? 0) && function_exists("gzencode")){

                # Compress substantial bodies or requests explicitly excluding identity
                if($identity == 0 || strlen($body) >= self::GZIP_MIN_BYTES){

                    # Compress the encoded bytes for the HTTP transport
                    $compressed = gzencode($body);

                    # Keep gzip when it reduces bytes or is required by the client
                    if(is_string($compressed) && (strlen($compressed) < strlen($body) || $identity == 0)){

                        # Install the compressed representation
                        $body = $compressed;

                        # Tell HTTP clients to decompress before parsing
                        $this->response = $this->response->withHeader("Content-Encoding", "gzip");

                    }

                }

            }

            # Reject an unavailable content coding
            if($automaticCoding && $identity == 0 && !$this->response->hasHeader("Content-Encoding"))

                # Mark the representation unacceptable
                $type = "";

        }

        # Emit a bodyless negotiation failure
        if($type === ""){

            # Clear any serialized content
            $body = "";

            # Preserve variation headers on the rejection
            $this->response = $this->response->withStatus(406)->withoutHeader("Content-Type")->withoutHeader("Content-Encoding");

        }

        # Determine the method for HEAD suppression
        $method = $this->request?->getMethod() ?? ($_SERVER["REQUEST_METHOD"] ?? "GET");

        # Replace any stale length after choosing the final wire representation
        $this->response = $this->response->withoutHeader("Content-Length");

        # Publish the selected representation size for clients and HEAD requests
        if($hasBody && !$sapiCompression)

            # Use the final byte count before suppressing a HEAD body
            $this->response = $this->response->withHeader("Content-Length", (string)strlen($body));

        # Retain the prepared stream on the API response
        $this->content = $this->instance->createStream($method === "HEAD" ? "" : $body);

        # Install the same stream on the PSR-7 message
        $this->response = $this->response->withBody($this->content);

    }

    /**
     * Parse Preference Weights
     *
     * @param string $header HTTP media ranges or content codings
     * @return array Lowercase tokens and their quality values
     */
    private static function _weights(string $header):array {

        # Prepare the token weights
        $result = [];

        # Inspect each weighted preference
        foreach(explode(",", strtolower($header)) as $entry){

            # Split the token from its optional parameters
            $parts = array_map("trim", explode(";", $entry));

            # Read the media range or content coding
            $token = array_shift($parts);

            # Use the implicit maximum preference
            $weight = 1.0;

            # Read any explicit quality parameter
            foreach($parts as $part){

                # Validate the complete quality value
                if(str_starts_with($part, "q="))

                    # Treat malformed preferences as unacceptable
                    $weight = preg_match('/^q=(0(?:\.\d{0,3})?|1(?:\.0{0,3})?)$/D', $part, $match) ? (float)$match[1] : 0;

            }

            # Retain the strongest declaration for the same token
            $result[$token] = max($result[$token] ?? 0, $weight);

        }

        # Return result
        return $result;

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

        # Check if custom type by front mode
        if(is_array($responseFormat) && !empty($responseFormat)){

            # Get mode of front
            $mode = trim(Config::getValue("Front.lastBuild.mode") ?? "");

            # Check mode
            if($mode && array_key_exists($mode, $responseFormat) && $responseFormat[$mode]){

                # Set responseFormat
                $responseFormat = $responseFormat[$mode];

            }

        }

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
                    "custom_code"    =>  "apiResponse-002",
                ]
            );

        # Normalize extensions before comparing HTTP media ranges
        $mime = File::EXTENSION_TO_MIMETYPE[$responseFormat] ?? $responseFormat;

        # Preserve the configured format unless the client requests an alternative
        $acceptable = true;

        # Negotiate only API formats with interchangeable structured envelopes
        if($mime === "application/json" || $mime === "application/msgpack"){

            # Prefer the injected request when running outside the classic PHP SAPI
            $accept = $this->request?->getHeaderLine("Accept") ?? ($_SERVER["HTTP_ACCEPT"] ?? "");

            # Check explicit media preferences
            if(trim($accept) !== ""){

                # Parse weighted media ranges once
                $weights = self::_weights($accept);

                # Resolve the fallback for both application media types
                $wildcard = $weights["application/*"] ?? $weights["*/*"] ?? 0;

                # Compare the configured type with the alternative
                $other = $mime === "application/json" ? "application/msgpack" : "application/json";

                # Resolve the weight of the configured format
                $preferred = $weights[$mime] ?? $wildcard;

                # Resolve the weight of the alternative
                $alternative = $weights[$other] ?? $wildcard;

                # Remember an exclusion until preparation or an explicit controller override
                $acceptable = max($preferred, $alternative) > 0;

                # Preserve the configured format on ties
                if($alternative > $preferred)

                    # Select the client's preferred representation
                    $mime = $other;

            }

        }

        # Install the selected encoder and its public MIME type
        $this->setContentType($mime, $mime === "application/msgpack" ? null : "UTF-8");

        # Retain the disposition of the Accept header
        $this->_acceptableFormat = $acceptable;

    }

    /**
     * Set content type
     * 
     * Set content type of the response
     * 
     * @param string $name Type of content
     * @param string|null $charset Character settings of the content type
     * @return self
     */
    public function setContentType(string $name = "html", string|null $charset = "UTF-8"):self {

        # Call parent
        parent::setContentType($name, $charset);

        # Resolve extensions while keeping explicit MIME types as string keys
        $mimeType = File::EXTENSION_TO_MIMETYPE[$name] ?? $name;

        # Select the encoder without using a null array offset
        $engine = File::MIMTYPE_TO_CLASS[$mimeType] ?? null;

        # Check engine
        if(!$engine || $engine === null)

            # New Exception
            throw new CrazyException(
                "\"$name\" isn't supported yet by framework...",
                500,
                [
                    "custom_code"    =>  "apiResponse-003",
                ]
            );

        # Keep a controller-selected format authoritative
        $this->_negotiateFormat = false;

        # Accept an explicit controller override even after a negotiation mismatch
        $this->_acceptableFormat = true;

        # Set engine
        $this->engineInstance = $engine;

        # Return self
        return $this;

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

    /** @var array DEFAULT_CONTENT */
    public const DEFAULT_CONTENT = [
        # "errors"    =>  null,
        "results"    =>  null
    ];

    /** @var array */
    public const SEPARATOR = [
        "/",
        "."
    ];

}
