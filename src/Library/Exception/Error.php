<?php declare(strict_types=1);
/**
 * Public Exception Data
 *
 * Convert failures to public HTTP errors without serializing internal diagnostics.
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
use Nyholm\Psr7\Response;
use Throwable;

/**
 * Error
 *
 * Keep status codes separate from exception messages, paths, traces and arguments.
 */
class Error {

    /** Public Constants
     ******************************************************
     */

    /** @var array Default public error for failures without an HTTP error status */
    public const DEFAULT = [
        "code"      =>  500,
        "type"      =>  "error",
        "detail"    =>  "Internal server error",
    ];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * From Exception
     *
     * Use only a valid HTTP error status and its fixed reason phrase in public responses.
     *
     * @param Throwable $exception Exception or native PHP error
     * @return array Public error without exception diagnostics
     */
    public static function fromException(Throwable $exception):array {

        # Prepare result
        $result = static::DEFAULT;

        # Inspect only the numeric status, never the exception message or trace
        $code = $exception->getCode();

        # Preserve valid HTTP error statuses while rejecting success and database-specific codes
        if(is_int($code) && $code >= 400 && $code <= 599){

            # Retain the intended error status
            $result["code"] = $code;

            # Reuse PSR-7's fixed HTTP reason phrases without loading application configuration
            $result["detail"] = (new Response($code))->getReasonPhrase() ?: "Request failed";

        }

        # Return result
        return $result;

    }

}
