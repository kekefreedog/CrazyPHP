<?php declare(strict_types=1);
/**
 * Terminal
 *
 * Library for manipulate command via terminal
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\System\Terminal;

/** Dependances
 * 
 */
use CrazyPHP\Interface\CrazyTerminal;
use CrazyPHP\Library\Cli\Command;

/**
 * Powershell
 *
 * Methods for interacting with Powershell
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Powershell implements CrazyTerminal {

    /** Public static function
     ******************************************************
     */

    /**
     * Is
     * 
     * Check if is curent terminal
     * 
     * @return bool
     */
    public static function is():bool {

        # Set result
        $result = false;

        # Check for a common PowerShell environment variable
        $result = getenv('PSModulePath') !== false;

        # Return result
        return $result;

    }

    /** Public static function | Command
     ******************************************************
     */

    /**
     * Command Set Env
     * 
     * Get command to set Env based on key value
     * 
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public static function commandSetEnv(string $key, mixed $value):string {

        # Set result
        $result = "";

        # Check value is bool
        if(is_bool($value))

            # Set value
            $value = $value ? '$true' : '$false';

        else{

            # Set value
            $value = str_replace("'", "''", $value);

            # Set value
            $value = "'$value'";

        }

        # Set uppercase on key
        $key = strtoupper($key);

        # Format the command to set the environment variable in PowerShell
        $result = "\$env:$key = $value";

        # Return result
        return $result;

    }

    /**
     * Command Chain
     * 
     * @param string $command
     * @return string
     */
    public static function commandChain(...$commands):string {

        # Set result
        $result = "";    
        
        # Check commands
        if(!empty($commands))

            # Iteration commands
            foreach ($commands as $command)

                # Check command is sting
                if(is_string($command))

                    # Append in result
                    $result .= $command . "; ";

        # Return result
        return rtrim($result);

    }

    /** Public static function | Action
     ******************************************************
     */

    /**
     * Run
     * 
     * Run command given
     * 
     * @param string $command
     * @param bool $live
     * @return mixed
     */
    public static function run(string $command, bool $liveResult = true):mixed {

        # Prepare command
        $psCommand = "powershell -command " . escapeshellarg($command);

        # Set result
        $result = Command::exec($psCommand, "", $liveResult);

        # Return result
        return $result;

    }

}