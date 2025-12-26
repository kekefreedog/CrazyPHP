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
use CrazyPHP\Exception\CrazyException;

/**
 * Port
 *
 * Methods for check port on the current local host or other one
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Port {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Is Taken
     * 
     * @param string $host Host to check (by default "localhost" <=> 127.0.0.1)
     * @param int $port Number of the port to check
     */
    public static function isTaken(string $host = "localhost", int $port = 80):bool {
        
        # Set result
        $result = false;

        # Check host
        if($host === "localhost")

            # Set host
            $host = "127.0.0.1";

        # Create a socket
        $socket = @fsockopen($host, $port, $errno, $errstr, 0.1);
    
        # Check if the connection was successful
        if($socket){

            # Close the socket if it was successfully opened
            fclose($socket);

            # Set result
            $result = true;

        }

        # Return result
        return $result;

    }

}