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
 * Methods for interacting with Command Prompt
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class CommandPrompt implements CrazyTerminal {

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

        # Check for the ComSpec environment variable typical in CMD that includes 'cmd.exe'
        $comSpec = getenv('ComSpec');

        # Return check
        $result = ($comSpec !== false && strpos(strtolower($comSpec), 'cmd.exe') !== false);

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

        # Check if the value is boolean and convert it to the correct Command Prompt string representation
        if(is_bool($value))

            # Set value
            $value = $value ? 'True' : 'False';
        
        # Escape percent signs for strings in Command Prompt
        $escapedValue = str_replace('%', '%%', $value);

        # Set uppercase on key
        $key = strtoupper($key);

        # Format the command to set the environment variable in Command Prompt
        $result = "set $key=$escapedValue";

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

        # Set array temp
        $arrayTemp = [];

        # Set result
        $result = "";
        
        # Check commands
        if(!empty($commands))

            # Iteration commands
            foreach ($commands as $command)

                # Check command is sting
                if(is_string($command))

                    # Append in result
                    $arrayTemp[] = $command;

        # Set result
        $result = implode(" && ", $arrayTemp);

        # Return result
        return $result;

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
     * @param bool $liveResult
     * @return mixed
     */
    public static function run(string $command, bool $liveResult = true):mixed {

        # Prepare command
        $cmdCommand = "cmd /c " . escapeshellarg($command);

        # Set result
        $result = Command::exec($cmdCommand, "", $liveResult);

        # Return result
        return $result;

    }

}