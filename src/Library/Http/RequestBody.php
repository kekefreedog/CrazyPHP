<?php declare(strict_types=1);
/**
 * Request Body
 *
 * Bound body parsing and keep multipart uploads alive only for their request.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Http;

/**
 * Dependencies
 */
use CrazyPHP\Exception\CrazyException;
use RequestParseBodyException;

/**
 * RequestBody
 *
 * Prefer PHP's streaming multipart parser and retain a bounded legacy string-parser contract.
 */
class RequestBody {

    /** Private Static Parameters
     ******************************************************
     */

    /** @var array|null Multipart result shared by middleware and controllers within one request */
    private static ?array $_multipart = null;

    /** @var CrazyException|null Retained failure prevents retrying an already consumed body */
    private static ?CrazyException $_multipartError = null;

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Read
     *
     * @return string Request body bounded by the configured post_max_size
     */
    public static function read():string {

        # Prepare result
        $result = "";

        # Apply the same total-size ceiling as multipart parsing
        $limits = self::_limits();

        # Reject a known excessive length before allocating the body
        self::_checkLength($limits["post_max_size"]);

        # Read at most one byte beyond the ceiling to detect absent or misleading length headers
        $body = file_get_contents("php://input", false, null, 0, $limits["post_max_size"] + 1);

        # Refuse failed reads instead of treating them as empty mutations
        if($body === false){

            # Report a controlled transport failure
            throw new CrazyException("Unable to read request body.", 400);

        }

        # Check the actual bytes as well as the declared length
        if(strlen($body) > $limits["post_max_size"]){

            # Stop before decoding or creating upload files
            throw new CrazyException("Request body is too large.", 413);

        }

        # Retain the bounded bytes
        $result = $body;

        # Return result
        return $result;

    }

    /**
     * Multipart Request
     *
     * PHP 8.4+ streams uploads and owns their cleanup; earlier runtimes use the bounded fallback.
     * Call before reading php://input. The cached result lasts for the current PHP request only.
     *
     * @return array Parsed post and files collections
     */
    public static function multipartRequest():array {

        # Prepare result
        $result = [];

        # Preserve the first failure rather than accepting an empty body on a later read
        if(self::$_multipartError !== null){

            # Repeat the original controlled failure
            throw self::$_multipartError;

        }

        # Parse only once so authorization and persistence share the same upload paths
        if(self::$_multipart === null){

            # Retain parsing failures for every later consumer in this request
            try{

                # Enforce configured byte, field, file and part counts
                $limits = self::_limits();

                # Refuse declared oversized bodies before asking PHP to create uploads
                self::_checkLength($limits["post_max_size"]);

                # Prefer the native parser without buffering the entire multipart body in userland
                if(function_exists("request_parse_body")){

                    # Let PHP register uploads for automatic request-end cleanup
                    [$post, $files] = request_parse_body($limits);

                    # Preserve the framework's post/files envelope
                    self::$_multipart = [
                        "post"     =>  $post,
                        "files"    =>  $files
                    ];

                }else{

                    # Preserve older runtimes with bounded parsing and automatically closed files
                    self::$_multipart = self::multipart(self::read());

                }

            }catch(RequestParseBodyException $exception){

                # Map native parser limits and malformed input to controlled client errors
                self::$_multipartError = new CrazyException(
                    "Invalid or excessive multipart request.",
                    preg_match("/exceed|maximum|limit|too many/i", $exception->getMessage()) ? 413 : 400
                );

                # Never expose native diagnostics or silently accept a truncated form
                throw self::$_multipartError;

            }catch(CrazyException $exception){

                # Retain framework size, syntax or storage failures
                self::$_multipartError = $exception;

                # Stop before a controller can consume partial data
                throw $exception;

            }

        }

        # Return the exact same fields and upload metadata on repeated reads
        $result = self::$_multipart;

        # Return result
        return $result;

    }

    /**
     * Multipart
     *
     * Preserve scalar and [] fields/files for callers of Controller::parseMultipartData.
     * Temporary streams are closed on failure or at request shutdown, including overwritten fields.
     *
     * @param string $rawData Complete multipart body supplied by a legacy caller
     * @return array Parsed post and files collections
     */
    public static function multipart(string $rawData):array {

        # Prepare result
        $result = null;

        # Retain every allocated stream until a successful handoff or cleanup
        $streams = [];

        # Read the configured parser ceilings
        $limits = self::_limits();

        # Bound memory already supplied by the caller before splitting it into parts
        if(strlen($rawData) > $limits["post_max_size"]){

            # Reject oversized legacy calls without creating files
            throw new CrazyException("Request body is too large.", 413);

        }

        # Require a bounded legal boundary line before splitting any fields
        if(!preg_match("/\A(--[A-Za-z0-9'()+_,.\/:=? -]{1,70})\r\n/", $rawData, $match)){

            # Refuse raw nonmultipart input and malformed delimiters
            throw new CrazyException("Invalid multipart boundary.", 400);

        }

        # Retain the complete delimiter including its two leading hyphens
        $boundary = $match[1];

        # Locate the required closing delimiter
        $end = strpos($rawData, "\r\n".$boundary."--", strlen($boundary) + 2);

        # Reject incomplete bodies and ambiguous data following the closing marker
        if(
            $end === false ||
            !in_array(substr($rawData, $end + strlen($boundary) + 4), [
                "",
                "\r\n"
            ], true)
        ){

            # Avoid saving uploads from a truncated body
            throw new CrazyException("Incomplete multipart request.", 400);

        }

        # Split only complete delimiter lines so ordinary binary boundary substrings remain intact
        $parts = explode("\r\n".$boundary."\r\n", substr($rawData, strlen($boundary) + 2, $end - strlen($boundary) - 2));

        # Enforce the aggregate part limit before allocating any upload
        if(count($parts) > $limits["max_multipart_body_parts"]){

            # Reject excessive field/file counts
            throw new CrazyException("Too many multipart parts.", 413);

        }

        # Prepare the two established result collections
        $parsed = [
            "post"     =>  [],
            "files"    =>  []
        ];

        # Count actual file parts independently from duplicate field names
        $fileCount = 0;

        # Count scalar and array fields independently from their names
        $fieldCount = 0;

        # Close every partial upload if any later part fails
        try{

            # Decode each bounded multipart part
            foreach($parts as $part){

                # Separate headers from bytes without trimming binary content
                $split = explode("\r\n\r\n", $part, 2);

                # Require bounded headers and an explicit body separator
                if(count($split) !== 2 || strlen($split[0]) > 16384){

                    # Refuse malformed parts before using any metadata
                    throw new CrazyException("Invalid multipart headers.", 400);

                }

                # Extract the required disposition header
                $valid = preg_match("/^Content-Disposition:\\s*form-data;([^\r\n]*)\r?$/mi", $split[0], $disposition);

                # Require a named field with no embedded controls
                if(!$valid || !preg_match('/(?:^|;)\s*name="([^"\r\n\x00]+)"/', $disposition[1], $nameMatch)){

                    # Reject malformed or unnamed parts
                    throw new CrazyException("Invalid multipart field.", 400);

                }

                # Preserve the submitted field name and existing [] convention
                $name = $nameMatch[1];

                # Distinguish file parts from normal form fields
                $isFile = preg_match('/(?:^|;)\s*filename="([^"\r\n\x00]*)"/', $disposition[1], $filenameMatch) === 1;

                # Count every part before storage, including duplicate names
                if($isFile){

                    # Track nonempty file selections as PHP does
                    $fileCount += $filenameMatch[1] !== "" ? 1 : 0;

                    # Enforce individual file size and upload-count limits
                    if(
                        $fileCount > $limits["max_file_uploads"] ||
                        strlen($split[1]) > $limits["upload_max_filesize"]
                    ){

                        # Close any previous uploads before reporting the rejected request
                        throw new CrazyException("Multipart upload limit exceeded.", 413);

                    }

                    # Read the optional declared MIME type as untrusted file metadata
                    preg_match("/^Content-Type:\\s*([^\r\n]*)\r?$/mi", $split[0], $typeMatch);

                    # Preserve PHP-style metadata without using the filename as a filesystem path
                    $entry = [
                        "name"        =>  $filenameMatch[1],
                        "type"        =>  $typeMatch[1] ?? "",
                        "tmp_name"    =>  "",
                        "error"       =>  UPLOAD_ERR_NO_FILE,
                        "size"        =>  0,
                    ];

                    # Allocate storage only for an actual file selection
                    if($filenameMatch[1] !== ""){

                        # Create a temporary stream that PHP automatically removes when closed
                        $stream = tmpfile();

                        # Fail closed if temporary storage is unavailable
                        if($stream === false){

                            # Report a controlled storage failure
                            throw new CrazyException("Temporary upload storage is unavailable.", 507);

                        }

                        # Track the stream before writing so partial writes are cleaned up too
                        $streams[] = $stream;

                        # Require every binary byte to reach the temporary file
                        if(fwrite($stream, $split[1]) !== strlen($split[1]) || !fflush($stream)){

                            # Reject incomplete writes instead of passing a successful upload entry
                            throw new CrazyException("Temporary upload storage is unavailable.", 507);

                        }

                        # Expose only the generated temporary path to the current request
                        $entry["tmp_name"] = stream_get_meta_data($stream)["uri"];

                        # Mark the complete file as available to the caller
                        $entry["error"] = UPLOAD_ERR_OK;

                        # Preserve the exact binary length
                        $entry["size"] = strlen($split[1]);

                    }

                    # Keep scalar and repeated upload shapes compatible with existing callers
                    self::_append($parsed["files"], $name, $entry, true);

                }else{

                    # Count duplicate and array form fields too
                    $fieldCount++;

                    # Bound text-field counts before building the result
                    if($fieldCount > $limits["max_input_vars"]){

                        # Reject excessive forms without leaving earlier file parts behind
                        throw new CrazyException("Too many multipart fields.", 413);

                    }

                    # Preserve form text without CSV-style, whitespace or character coercion
                    self::_append($parsed["post"], $name, $split[1], false);

                }

            }

            # Retain successful temporary uploads until all request consumers have finished
            register_shutdown_function(static function() use($streams):void {

                # Close only resources created by this parser invocation
                foreach($streams as $stream){

                    # Avoid closing streams already released by the caller
                    if(is_resource($stream)){

                        # Remove the parser-owned temporary file at request shutdown
                        fclose($stream);

                    }

                }

            });

            # Publish the complete parsed result only after every part succeeds
            $result = $parsed;

        }finally{

            # Release partial uploads immediately on malformed data or storage failures
            if($result === null){

                # Close every resource allocated before the failure
                foreach($streams as $stream){

                    # Remove the incomplete request's temporary file
                    fclose($stream);

                }

            }

        }

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Append Field
     *
     * @param array $target Destination post or files collection
     * @param string $name Submitted scalar or [] field name
     * @param mixed $value Text or file metadata
     * @param bool $file Whether the value is a file entry
     * @return void
     */
    private static function _append(array &$target, string $name, mixed $value, bool $file):void {

        # Preserve ordinary scalar field behavior
        if(!str_ends_with($name, "[]")){

            # Retain the last scalar value as the original parser did
            $target[$name] = $value;

        }else{

            # Extract the repeated-field key
            $name = substr($name, 0, -2);

            # Refuse contradictory scalar and repeated-field shapes
            if(
                isset($target[$name]) &&
                (!is_array($target[$name]) || ($file && !is_array($target[$name]["name"] ?? null)))
            ){

                # Fail without leaking earlier file parts
                throw new CrazyException("Conflicting multipart fields.", 400);

            }

            # Preserve PHP's parallel metadata arrays for repeated file fields
            if($file){

                # Append each metadata value to its matching list
                foreach($value as $key => $entry){

                    # Retain the original upload-field shape
                    $target[$name][$key][] = $entry;

                }

            }else{

                # Append a repeated text value
                $target[$name][] = $value;

            }

        }

    }

    /**
     * Limits
     *
     * @return array Native parser options with finite defaults for unlimited byte settings
     */
    private static function _limits():array {

        # Prepare result from server-owned PHP configuration
        $result = [
            "post_max_size"               =>  ini_parse_quantity(ini_get("post_max_size") ?: "8M"),
            "upload_max_filesize"         =>  ini_parse_quantity(ini_get("upload_max_filesize") ?: "2M"),
            "max_file_uploads"            =>  max(0, (int) ini_get("max_file_uploads")),
            "max_input_vars"              =>  max(0, (int) ini_get("max_input_vars")),
            "max_multipart_body_parts"    =>  (int) ini_get("max_multipart_body_parts"),
        ];

        # Keep bounded parsing when a deployment uses unlimited or invalid byte settings
        foreach([
            "post_max_size"          =>  8 * 1024 * 1024,
            "upload_max_filesize"    =>  2 * 1024 * 1024
        ] as $name => $fallback){

            # Leave positive deployment limits intact and reserve one byte for overflow detection
            $result[$name] = $result[$name] > 0 ? min($result[$name], PHP_INT_MAX - 1) : $fallback;

        }

        # Match PHP's default aggregate limit derived from field and file counts
        if($result["max_multipart_body_parts"] < 0){

            # Bound all parts even when their field names repeat
            $result["max_multipart_body_parts"] = $result["max_input_vars"] + $result["max_file_uploads"];

        }

        # Return result
        return $result;

    }

    /**
     * Check Length
     *
     * @param int $limit Maximum permitted request bytes
     * @return void
     */
    private static function _checkLength(int $limit):void {

        # Inspect the transport-provided length without trusting it as the only size check
        $length = $_SERVER["CONTENT_LENGTH"] ?? null;

        # Refuse excessive declared lengths before reading or creating files
        if(is_scalar($length) && is_numeric($length) && (float) $length > $limit){

            # Report the configured request-size limit
            throw new CrazyException("Request body is too large.", 413);

        }

    }

}
