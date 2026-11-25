<?php declare(strict_types=1);
/**
 * Response Tests
 *
 * Verify PHP 8.5 response compatibility and clean API output.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependencies
 */
use CrazyPHP\Library\Time\DateTime as FrameworkDateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use CrazyPHP\Library\File\MessagePack;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Response;
use CrazyPHP\Model\Context;
use CrazyPHP\Model\Env;
use ErrorException;
use DateTimeZone;
use DateTime;

/**
 * Response Test
 *
 * Keep response headers and bodies free of diagnostic output.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class ResponseTest extends TestCase {

    /** Private Parameters
     ******************************************************
     */

    /** @var string $_root Isolated application configuration */
    private string $_root;

    /** @var array $_globals Framework state to restore after each test */
    private array $_globals = [];

    /** @var bool $_errorHandlerInstalled Whether this test installed its error handler */
    private bool $_errorHandlerInstalled = false;

    /** Protected Methods | Preparation
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Preserve framework state without reading a real application's configuration
        foreach([
            Env::PREFIX,
            Config::PREFIX,
            Context::PREFIX
        ] as $key){

            # Snapshot state from other tests
            $this->_globals[$key] = $GLOBALS[$key] ?? null;

            # Clear the saved global before configuring this test
            unset($GLOBALS[$key]);

        }

        # Set the isolated application path
        $this->_root = sys_get_temp_dir()."/crazy-response-".bin2hex(random_bytes(5));

        # Create the configuration directory
        mkdir($this->_root."/config", 0755, true);

        # Provide the frontend metadata read by response constructors
        file_put_contents(
            $this->_root."/config/Front.json",
            '{"Front":{"lastBuild":{"hash":"test","mode":"production"}}}'
        );

        # Configure an explicit API MIME type
        file_put_contents(
            $this->_root."/config/Router.json",
            '{"Router":{"parameters":{"api":{"format":"application/json"}}}}'
        );

        # Resolve framework paths against the fixture
        Env::set([
            "app_root"           =>  $this->_root,
            "crazyphp_root"      =>  dirname(__DIR__, 2),
            "config_location"    =>  $this->_root."/config",
        ]);

        # Supply route context without termination callbacks
        $GLOBALS[Context::PREFIX] = [
            "ROUTES"    =>  [
                "CURRENT"    =>  []
            ]
        ];

        # Turn PHP deprecations into failures instead of allowing them to pollute HTTP output
        set_error_handler(static function(int $severity, string $message, string $file, int $line):bool {

            # Reject both engine and user deprecations
            if($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED)

                # Preserve the original diagnostic in the test failure
                throw new ErrorException($message, 0, $severity, $file, $line);

            # Keep normal handling for other diagnostics
            return false;

        });

        # Track ownership of the installed error handler for cleanup
        $this->_errorHandlerInstalled = true;

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Restore PHPUnit's error handler
        if($this->_errorHandlerInstalled)

            # Remove only the handler installed by this test
            restore_error_handler();

        # Remove only the test's isolated configuration
        File::removeAll($this->_root);

        # Restore the surrounding framework state
        foreach($this->_globals as $key => $value){

            # Keep previously absent globals absent
            if($value === null){

                # Remove the global introduced by this test
                unset($GLOBALS[$key]);

            }else{

                # Restore the saved value
                $GLOBALS[$key] = $value;

            }

        }

    }

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Status Code Preserves The Fluent Type Contract
     *
     * Static analysis must retain the concrete response after setting its status.
     *
     * @return void
     */
    public function testStatusCodePreservesFluentTypeContract():void {

        # Inspect the public return contract used to resolve subsequent chained methods
        $method = new \ReflectionMethod(Response::class, "setStatusCode");

        # Prevent inherited setters from widening an API response to the base class
        $this->assertSame("static", (string) $method->getReturnType());

        # Keep ordinary non-API responses on their existing mutable instance
        $response = new Response();

        # Retain the original argument name and object identity
        $this->assertSame($response, $response->setStatusCode(statutCode: 201));

        # Preserve the status passed to the PSR response
        $response->prepare();

        # Confirm that the emitted HTTP status still comes from the requested value
        $this->assertSame(201, $response->response->getStatusCode());

        # Preserve the default status and fluent identity for calls without an argument
        $this->assertSame($response, $response->setStatusCode());

        # Keep the existing successful response default
        $this->assertSame(200, $response->statutCode);

    }

    /**
     * Test Status Code Supports API Content Chaining
     *
     * Preserve subclass dispatch and the existing results envelope in both API formats.
     *
     * @return void
     */
    public function testStatusCodeSupportsApiContentChaining():void {

        # Cover development JSON and production MessagePack responses
        foreach([
            "json",
            "msgpack"
        ] as $format){

            # Construct the API subclass using the isolated application configuration
            $response = new ApiResponse();

            # Select the real encoder before exercising the reported chain
            $response->setContentType($format);

            # Call the API-specific method immediately after the inherited setter
            $result = $response
                ->setStatusCode(200)
                ->pushContent("results", [
                    "revoked"    =>  true
                ])
            ;

            # Keep the original API instance throughout the fluent calls
            $this->assertSame($response, $result);

            # Preserve the established successful HTTP status
            $this->assertSame(200, $response->statutCode);

            # Verify dispatch reached the subclass rather than the base placeholder
            $this->assertSame([
                "results"    =>  [
                    "revoked"    =>  true
                ]
            ], $response->apiContent);

            # Retain the selected response encoder
            $this->assertSame($format === "json" ? Json::class : MessagePack::class, $response->engineInstance);

        }

    }

    /**
     * Test API Content Types
     *
     * @param string $name Extension or explicit MIME type
     * @param string $mimeType Expected response MIME type
     * @param string $encoder Expected encoder class
     * @return void
     */
    #[DataProvider("contentTypes")]
    public function testApiContentTypes(string $name, string $mimeType, string $encoder):void {

        # Construct with an explicit MIME type, the path that previously used a null offset
        $response = new ApiResponse();

        # Assert content type selection returns the same response
        $this->assertSame($response, $response->setContentType($name, null));

        # Assert the MIME type has no charset when none was requested
        $this->assertSame($mimeType, $response->getContentType(false));

        # Assert the selected encoder matches the requested format
        $this->assertSame($encoder, $response->engineInstance);

    }

    /**
     * Test Negotiated API Transport
     *
     * @param string $accept Accepted representations
     * @param string $coding Accepted codings
     * @param string $mime Expected response MIME or an empty value for rejection
     * @param bool $compressed Whether the response requires HTTP decompression
     * @param int $size Input string length
     * @return void
     */
    #[DataProvider("transportCases")]
    public function testNegotiatedApiTransport(string $accept, string $coding, string $mime, bool $compressed, int $size):void {

        # Build an independent request carrying both preference lists
        $request = new \Nyholm\Psr7\ServerRequest("GET", "/", [
            "Accept"             =>  $accept,
            "Accept-Encoding"    =>  $coding
        ]);

        # Use a compressible value to exercise threshold selection
        $state = [
            "value"    =>  str_repeat("a", $size)
        ];

        # Prepare through the public API response path
        $api = (new ApiResponse($request))->addHeader("Vary", "Origin")->setContent($state);

        # Assemble the actual outgoing HTTP message
        $api->prepare();

        # Read the prepared message
        $message = $api->response;

        # Read its wire bytes
        $bytes = (string)$message->getBody();

        # Verify the disposition of excluded representations and codings
        self::assertSame($mime === "" ? 406 : 200, $message->getStatusCode());

        # Verify the negotiated media type
        self::assertSame($mime, explode(";", $message->getHeaderLine("Content-Type"))[0]);

        # Verify the length after optional compression
        self::assertSame((string)strlen($bytes), $message->getHeaderLine("Content-Length"));

        # Preserve existing cache variation alongside transport preferences
        self::assertSame("Origin, Accept, Accept-Encoding", $message->getHeaderLine("Vary"));

        # Verify the gzip header matches the actual response
        self::assertSame($compressed ? "gzip" : "", $message->getHeaderLine("Content-Encoding"));

        # Inspect an accepted representation
        if($mime !== ""){

            # Undo HTTP compression exactly once
            $payload = $compressed ? gzdecode($bytes) : $bytes;

            # Decode the selected wire format
            $decoded = $mime === "application/json" ? json_decode($payload, true, flags: JSON_THROW_ON_ERROR) : MessagePack::decode($payload);

            # Confirm the complete envelope survived negotiation
            self::assertSame($state, $decoded);

        }else{

            # Confirm that a rejection carries no unacceptable representation
            self::assertSame("", $bytes);

        }

    }

    /**
     * Transport Cases
     *
     * @return iterable Named API negotiation scenarios
     */
    public static function transportCases():iterable {

        # Prepare independent requests using the JSON-configured test application
        $result = [
            "absent headers"            =>  [
                "",
                "",
                "application/json",
                false,
                8
            ],
            "wildcard honors default"   =>  [
                "*/*",
                "gzip",
                "application/json",
                true,
                4096
            ],
            "weighted binary"           =>  [
                "application/json;q=0.2, application/msgpack",
                "gzip",
                "application/msgpack",
                true,
                4096
            ],
            "tiny binary"               =>  [
                "application/msgpack",
                "gzip",
                "application/msgpack",
                false,
                8
            ],
            "explicit gzip exclusion"   =>  [
                "application/json",
                "gzip;q=0, *;q=1",
                "application/json",
                false,
                4096
            ],
            "prefer identity"           =>  [
                "application/json",
                "identity;q=1, gzip;q=0.4",
                "application/json",
                false,
                4096
            ],
            "identity excluded"         =>  [
                "application/json",
                "gzip, identity;q=0",
                "application/json",
                true,
                8
            ],
            "exact exclusion"           =>  [
                "application/json;q=0, application/*",
                "",
                "application/msgpack",
                false,
                8
            ],
            "no acceptable format"      =>  [
                "text/plain",
                "",
                "",
                false,
                8
            ],
            "no acceptable coding"      =>  [
                "application/json",
                "br, *;q=0",
                "",
                false,
                8
            ]
        ];

        # Return the cases
        return $result;

    }

    /**
     * Verify the configured response format for each frontend build mode.
     *
     * @return void
     */
    #[TestWith(["production", "application/msgpack", MessagePack::class])]
    #[TestWith(["development", "application/json", Json::class])]
    public function testModeSpecificApiFormat(string $mode, string $mime, string $encoder):void {

        # Set the build mode in the isolated application
        Config::setValue("Front.lastBuild.mode", $mode);

        # Use the legacy mode-specific format settings
        $GLOBALS[Config::PREFIX]["Router"] = [
            "Router"    =>  [
                "parameters"    =>  [
                    "api"    =>  [
                        "format"    =>  [
                            "production"     =>  "msgpack",
                            "development"    =>  "json"
                        ]
                    ]
                ]
            ]
        ];

        # Exercise requests with absent, wildcard, tied and exclusive media preferences
        foreach([
            "application/json",
            "application/msgpack",
            "",
            "*/*",
            "application/json, application/msgpack"
        ] as $accept){

            # Supply the original client preferences without opting into gzip
            $request = new \Nyholm\Psr7\ServerRequest("GET", "/", [
                "Accept"    =>  $accept
            ]);

            # Prepare a representative success envelope
            $api = (new ApiResponse($request))->pushContent("results", true);

            # Select the wire representation
            $api->prepare();

            # Verify the mode selected the expected media type
            self::assertSame($mime, explode(";", $api->response->getHeaderLine("Content-Type"))[0]);

            # Vary only on compression when the build mode fixes the format
            self::assertSame("Accept-Encoding", $api->response->getHeaderLine("Vary"));

            # Keep the response readable by clients without HTTP gzip support
            self::assertFalse($api->response->hasHeader("Content-Encoding"));

            # Verify the original response shape
            self::assertSame([
                "results"    =>  true
            ], $encoder::decode((string)$api->response->getBody()));

        }

    }

    /**
     * Test Explicit API Format And HEAD
     *
     * @return void
     */
    public function testExplicitApiFormatAndHead():void {

        # Request a HEAD response with an unsupported media range
        $request = new \Nyholm\Psr7\ServerRequest("HEAD", "/", [
            "Accept"    =>  "text/plain"
        ]);

        # Let a controller explicitly select its response representation
        $api = (new ApiResponse($request))->setContentType("json")->pushContent("results", true);

        # Prepare the equivalent GET representation metadata
        $api->prepare();

        # Preserve the explicit controller selection
        self::assertSame(200, $api->response->getStatusCode());

        # Match the wire length of the corresponding GET representation
        self::assertSame((string)strlen('{"results":true}'), $api->response->getHeaderLine("Content-Length"));

        # Suppress the body for HEAD
        self::assertSame("", (string)$api->response->getBody());

    }

    /**
     * Test Unsupported API Content Type
     *
     * @return void
     */
    public function testUnsupportedApiContentType():void {

        # Keep the framework's normal unsupported-format error without a PHP deprecation
        $response = new ApiResponse();

        # Expect the framework exception rather than a PHP diagnostic
        $this->expectException(CrazyException::class);

        # Set exception message
        $this->expectExceptionMessage('"application/unknown" isn\'t supported');

        # Request a format without a registered encoder
        $response->setContentType("application/unknown");

    }

    /**
     * Test Last Modified Uses GMT Without Mutating The Date
     *
     * @param string $class Native mutable or framework immutable date class
     * @param string $timezone Source timezone
     * @param string $expected Expected HTTP date
     * @return void
     */
    #[DataProvider("dates")]
    public function testLastModifiedUsesGmtWithoutMutatingTheDate(string $class, string $timezone, string $expected):void {

        # Use a fixed instant in a timezone that may differ from GMT
        $date = new $class("2026-07-12 12:30:00", new DateTimeZone($timezone));

        # Keep the original value for the mutation check
        $original = $date->format("c");

        # Prepare the response receiving the date header
        $response = new Response();

        # Assert setting Last-Modified returns the same response
        $this->assertSame($response, $response->addLastModified($date));

        # Assert the HTTP date uses the expected GMT value
        $this->assertSame($expected, $response->header["Last-Modified"]);

        # Assert the caller's date value is unchanged
        $this->assertSame($original, $date->format("c"));

        # Assert the caller's timezone is unchanged
        $this->assertSame($timezone, $date->getTimezone()->getName());

    }

    /**
     * Test Missing Last Modified Leaves Headers Unchanged
     *
     * @return void
     */
    public function testMissingLastModifiedLeavesHeadersUnchanged():void {

        # A missing modification time must not create or replace a header
        $response = new Response();

        # Keep the original headers for comparison
        $headers = $response->header;

        # Assert a missing modification time returns the same response
        $this->assertSame($response, $response->addLastModified());

        # Assert no header was added or replaced
        $this->assertSame($headers, $response->header);

    }

    /**
     * Test API Emission With Displayed Errors
     *
     * @return void
     */
    public function testApiEmissionWithDisplayedErrors():void {

        # Exercise the real emitter in a fresh process without PHPUnit's output or error handler
        $script = <<<'PHP'
        # Load the framework in the child process
        require $argv[1]."/vendor/autoload.php";

        # Resolve paths against the same isolated application
        CrazyPHP\Model\Env::set([
            "app_root"        => $argv[2],
            "crazyphp_root"   => $argv[1],
            "config_location" => $argv[2]."/config",
        ]);

        # Supply route context without termination callbacks
        $GLOBALS[CrazyPHP\Model\Context::PREFIX] = ["ROUTES" => ["CURRENT" => []]];

        # Create an API response using the configured MIME type
        $response = new CrazyPHP\Core\ApiResponse();

        # Exercise HTTP date formatting before emission
        $response->addLastModified(new DateTime("2026-07-12 12:30:00", new DateTimeZone("Europe/Paris")));

        # Emit the original API envelope through the inherited status setter and Laminas
        $response
            ->setStatusCode(200)
            ->pushContent("results", ["ok" => true])
            ->send()
        ;
        PHP;

        # Display diagnostics exactly as a development HTTP runtime would
        $process = proc_open(
            [
                PHP_BINARY,
                "-d",
                "error_reporting=-1",
                "-d",
                "display_errors=1",
                "-d",
                "log_errors=0",
                "-r",
                $script,
                dirname(__DIR__, 2),
                $this->_root,
            ],
            [
                0    =>  [
                    "file",
                    "/dev/null",
                    "r"
                ],
                1    =>  [
                    "pipe",
                    "w"
                ],
                2    =>  [
                    "pipe",
                    "w"
                ],
            ],
            $pipes
        );

        # Verify the child process started before reading its pipes
        $this->assertIsResource($process);

        # Capture the emitted response body
        $body = stream_get_contents($pipes[1]);

        # Capture diagnostics from the child process
        $errors = stream_get_contents($pipes[2]);

        # Close the response output stream
        fclose($pipes[1]);

        # Close the diagnostic output stream
        fclose($pipes[2]);

        # Collect the child process exit status
        $status = proc_close($process);

        # Assert response emission completed without a fatal error
        $this->assertSame(0, $status, $errors.$body);

        # Assert the child process emitted no diagnostics on stderr
        $this->assertSame("", $errors);

        # Assert stdout contains only the expected JSON body
        $this->assertSame('{"results":{"ok":true}}', $body);

    }

    /** Public Static Methods | Providers
     ******************************************************
     */

    /**
     * Content Types
     *
     * @return array
     */
    public static function contentTypes():array {

        # Cover both extension lookup and direct MIME lookup for every API encoder
        return [
            "JSON extension"           =>  [
                "json",
                "application/json",
                Json::class
            ],
            "JSON MIME"                =>  [
                "application/json",
                "application/json",
                Json::class
            ],
            "YAML extension"           =>  [
                "yaml",
                "text/yaml",
                Yaml::class
            ],
            "YML extension"            =>  [
                "yml",
                "text/yaml",
                Yaml::class
            ],
            "YAML MIME"                =>  [
                "text/yaml",
                "text/yaml",
                Yaml::class
            ],
            "MessagePack extension"    =>  [
                "msgpack",
                "application/msgpack",
                MessagePack::class
            ],
            "MessagePack MIME"         =>  [
                "application/msgpack",
                "application/msgpack",
                MessagePack::class
            ],
        ];

    }

    /**
     * Dates
     *
     * @return array
     */
    public static function dates():array {

        # Prepare the date cases
        $cases = [];

        # Cover both supported date types
        foreach([
            DateTime::class,
            FrameworkDateTime::class
        ] as $class)

            # Check positive and negative timezone offsets
            foreach([
                "UTC"                 =>  "12",
                "Europe/Paris"        =>  "10",
                "America/New_York"    =>  "16"
            ] as $timezone => $hour)

                # Preserve the timezone and expected GMT value in each named case
                $cases["$class $timezone"] = [
                    $class,
                    $timezone,
                    "Sun, 12 Jul 2026 $hour:30:00 GMT"
                ];

        # Return independently named cases for useful regression failures
        return $cases;

    }

}
