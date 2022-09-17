<?php declare(strict_types=1);
/**
 * Cli
 *
 * Library for manipulate command via shell
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Cli;

/** Dependances
 * 
 */

/**
 * Command
 *
 * Methods for interacting with commands
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Command{

    /** Public static function
     ******************************************************
     */

    /**
     * Exists
     * 
     * Check command shell exists
     * @param string $command Name of the command to check
     * @return bool
     */
    public static function exists(string $command = ""):bool {

        # Set result
        $result = false;

        # Check command
        if(!$command)

            # Return false
            return $result;

        # Set prefix
        $prefix = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 
            "where %s" : 
                "which %s";

        # Check command
        $result = shell_exec(sprintf($prefix, escapeshellarg($command))) ? true : false;

        # Return result
        return $result;

    }

}