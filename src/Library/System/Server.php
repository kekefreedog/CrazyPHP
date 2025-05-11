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
namespace  CrazyPHP\Library\System;

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

    /** Public Static Methods
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

    /** Public Constants
     ******************************************************
     */

    /** @var array CONTENT_TYPE_KEYS */
    public const CONTENT_TYPE_KEYS = ["HTTP_CONTENT_TYPE", "CONTENT_TYPE"];


}