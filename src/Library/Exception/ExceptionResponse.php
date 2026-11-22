<?php declare(strict_types=1);
/**
 * Exception Response
 *
 * Render public HTTP errors while retaining diagnostics only in the server log.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Exception;

/**
 * Dependencies
 */
use CrazyPHP\Library\State\Page as State;
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\Response;
use CrazyPHP\Model\Env;
use Throwable;

/**
 * ExceptionResponse
 *
 * Use the same safe error fields in HTML, structured data and plain-text responses.
 */
class ExceptionResponse {

    /** Public Parameters
     ******************************************************
     */

    /** @var Throwable Original failure retained only for server-side diagnostics */
    public Throwable $exception;

    /** @var string Negotiated response MIME type */
    public string $mimeType;

    /** Private Parameters
     ******************************************************
     */

    /** @var mixed Response renderer */
    private mixed $render;

    /** @var array Public response envelope */
    private array $data = [];

    /** Public Methods
     ******************************************************
     */

    /**
     * Construct Response
     *
     * @param Throwable $exception Exception or native PHP error to report
     */
    public function __construct(Throwable $exception){

        # Retain the original failure for logging only
        $this->exception = $exception;

        # Record diagnostics before attempting any rendering that may itself fail
        $this->pushToLog();

        # Keep error-page caching independent from unavailable application cache services
        Env::set([
            "cache_driver"    =>  "Files"
        ]);

        # Build only public error data
        $this->prepareData();

        # Select the requested representation without exposing diagnostics in any format
        $this->mimeType = Header::getHeaderAccept();

        # Keep SPA error responses out of page-state cache retrieval and its early response path
        if($this->mimeType === "text/html" && !empty($_GET["catch_state"])){

            # Return a sanitized JSON error envelope with the original failure status
            $this->mimeType = "application/json";

        }

        # Prepare the corresponding public view
        $this->prepareView();

        # Emit the response through the normal framework emitter
        $this->render();

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Prepare View
     *
     * @return void
     */
    private function prepareView():void {

        # Preserve the application's normal HTML error page using sanitized state
        if($this->mimeType === "text/html"){

            # Render the themed error page without passing the original throwable to state
            $this->render = static function(array $data):Response {

                # Prepare result
                $result = null;

                # Resolve the existing application template through the framework file abstraction
                $templatePath = File::path("@app_root/app/Environment/Page/Error/template.hbs");

                # Expose only sanitized fields in the HTML and embedded page state
                $state = (new State())
                    ->pushErrors($data["errors"])
                    ->render()
                ;

                # Preserve the application's error-page presentation
                $structure = (new Structure())
                    ->setDoctype()
                    ->setLanguage()
                    ->setHead()
                    ->setJsScripts("Error")
                    ->setBodyTemplate($templatePath, null, (array) $state, "Error")
                    ->prepare()
                    ->render()
                ;

                # Prepare the controlled HTML response
                $result = (new Response())
                    ->setStatusCode($data["errors"][0]["code"])
                    ->setContent($structure)
                ;

                # Return result
                return $result;

            };

        }else if(array_key_exists($this->mimeType, File::MIMTYPE_TO_CLASS)){

            # Serialize supported JSON, YAML and MessagePack envelopes without API configuration dependencies
            $this->render = function(array $data):Response {

                # Prepare result
                $result = null;

                # Select only a registered structured encoder
                $encoder = File::MIMTYPE_TO_CLASS[$this->mimeType];

                # Encode the sanitized envelope before passing it to the response body
                $result = (new Response())
                    ->setContentType($this->mimeType, $this->mimeType === "application/msgpack" ? null : "UTF-8")
                    ->setStatusCode($data["errors"][0]["code"])
                    ->setContent($encoder::encode($data))
                ;

                # Return result
                return $result;

            };

        }else{

            # Return fixed plain text for unsupported Accept values instead of dumping an exception
            $this->render = static function(array $data):Response {

                # Prepare result
                $result = (new Response())
                    ->setContentType("text/plain")
                    ->setStatusCode($data["errors"][0]["code"])
                    ->setContent($data["errors"][0]["detail"])
                ;

                # Return result
                return $result;

            };

        }

    }

    /**
     * Prepare Data
     *
     * @return void
     */
    private function prepareData():void {

        # Keep diagnostic fields out of the response envelope
        $this->data = [
            "errors"    =>  [
                Error::fromException($this->exception)
            ]
        ];

    }

    /**
     * Render
     *
     * @return void
     */
    private function render():void {

        # Resolve the selected view
        $render = $this->render;

        # Build the response from public data only
        $response = $render($this->data);

        # Prevent shared caching and content sniffing for failed requests
        $response
            ->addHeader("Cache-Control", "private, no-store")
            ->addHeader("X-Content-Type-Options", "nosniff")
            ->send()
        ;

    }

    /**
     * Push To Log
     *
     * Keep diagnostic messages and call locations in the configured server log, omitting arguments.
     *
     * @return void
     */
    private function pushToLog():void {

        # Retain call locations without arguments, request objects or previous exception objects
        $trace = array_map(
            static fn(array $frame):array => array_intersect_key($frame, array_flip([
                "file",
                "line",
                "class",
                "type",
                "function"
            ])),
            array_slice($this->exception->getTrace(), 0, 40)
        );

        # Write one JSON log entry without allowing embedded line breaks to forge separate entries
        error_log(json_encode([
            "exception"    =>  $this->exception::class,
            "message"      =>  $this->exception->getMessage(),
            "file"         =>  $this->exception->getFile(),
            "line"         =>  $this->exception->getLine(),
            "trace"        =>  $trace,
        ], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES));

    }

}
