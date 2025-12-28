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
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid as UuidVendor;
use Ramsey\Uuid\Rfc4122\UuidV7;

/**
 * Uuid
 *
 * Methods for manage requests of server
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Uuid {

    /** Public static methods
     ******************************************************
     */

    /**
     * Create
     * 
     * Create UUID
     */
    public static function create():string {

        # Set result
        $result = UuidVendor::uuid7()->toString();

        # Return result
        return $result;

    }

    /**
     * Is Valid
     */
    public static function isValid(string $value):bool {

        # Set result
        $result = false;

        # Check is valid
        if(UuidVendor::isValid($value))

            # Set result
            $result = true;
        
        # Create uuid instance from string
        else

            # Set uuid
            $result = UuidVendor::fromString($value) instanceof UuidV7;

        # Return result
        return $result;
        
    }

    /** Public static methods | Middleware
     ******************************************************
     */

    /**
     * Append
     * 
     * Append X-Request-ID
     *
     * Generates a UUID v7 request identifier and attaches it
     * to the request headers for backend page generation state
     * correlation and tracing.
     *
     * - Reuses incoming ID if already present
     * - Header-only (no attributes, no exposure)
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public static function append(ServerRequestInterface $request):ServerRequestInterface {
        
        # Check if already has header and push it else
        if(!$request->hasHeader(static::HEADER)) $request->withHeader(static::HEADER, static::create());

        # Return request
        return $request;

    }

    /** Public static methods
     ******************************************************
     */

    /** @param string HEADER */
    public const HEADER = 'X-Request-ID';

}
