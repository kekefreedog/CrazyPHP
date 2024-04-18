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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Cli;

use CrazyPHP\Library\System\Os;
use CrazyPHP\Library\System\Terminal;

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
 * @copyright  2022-2024 Kévin Zarshenas
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

    /**
     * Exec
     * 
     * Execute command
     * 
     * @param string $command Command  to execute
     * @param string $argument Argument for the command
     * @param bool $liveResult Display result in live
     * @return
     */
    public static function exec(string $command = "", string $argument = "", bool $liveResult = false) {

        # Prepare result
        $result = [
            "output"        =>  null,
            "result_code"   =>  null
        ];

        # Check command
        if(!$command)

            # Return
            return null;

        // Prepare command depending on the shell environment
        if(Os::isWindows() && Terminal::isWindowsPowerShell()){

            # PowerShell requires different syntax, especially if arguments are involved
            $command = 'powershell.exe -NonInteractive -NoProfile -Command "& {' . $command . ($argument ? " " . escapeshellarg($argument) : "") . '}"';

        }else

            # Prepare command
            $command = $command.($argument ? " $argument" : "");

        # Check if live result enable
        if($liveResult){

            # End all output buffers if any
            while (@ob_end_flush()); 

            # Create process from command
            $proc = popen($command, 'r');

            # Change type of output
            $result["output"] = [];

            # Read the process
            while (!feof($proc)){

                # Display result
                $current = fread($proc, 4096);

                # Push message in result
                $result["output"][] = $current;

                # Echo message
                echo $current;
                
                # Flush
                @flush();

            }

        }else{

                # Exec command
                exec($command, $result["output"], $result["result_code"]);

        }

        # Return result
        return $result;

    }

}