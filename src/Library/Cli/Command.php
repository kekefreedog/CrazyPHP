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

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\CLImate;

/**
 * Command
 *
 * Methods for interacting with commands
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Command {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Confirm
     *
     * Display a styled yes/no prompt and reject closed standard input.
     *
     * @param string $question Confirmation displayed to the user
     * @param CLImate|null $climate Terminal with the command's existing styling
     * @return bool
     * @throws CrazyException When standard input closes before an answer is read
     */
    public static function confirm(string $question, ?CLImate $climate = null):bool {

        # Keep the CLImate reader adapter local to the confirmation helper
        $reader = new class extends Stdin {

            /** Public Methods
             ******************************************************
             */

            /**
             * Line
             *
             * Read successive confirmations from the same standard input stream.
             *
             * @return string
             * @throws CrazyException When standard input is closed
             */
            public function line():string {

                # Preserve buffered answers for the next confirmation
                $answer = fgets(STDIN);

                # Distinguish closed input from an explicit empty answer
                if($answer === false)

                    # Explain how an automated caller can skip the prompt
                    throw new CrazyException(
                        "Confirmation requires an answer; input was closed. Use --yes (or -y) to skip yes/no prompts.",
                        400
                    );

                # Let CLImate validate the answer and apply its default
                return trim($answer);

            }

        };

        # Preserve the caller's styling or use a standard terminal
        $climate = $climate ?? new CLImate();

        # Display the original same-line confirmation, defaulting to No
        return $climate->confirm($question, $reader)->confirmed();

    }

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
     * @return ?array
     */
    public static function exec(string $command = "", string $argument = "", bool $liveResult = false):?array {

        # Prepare result
        $result = [
            "output"        =>  null,
            "result_code"   =>  null
        ];

        # Check command
        if(!$command)

            # Return
            return null;

        # Prepare command
        $command = $command.($argument ? " $argument" : "");

        # Check if live result enable
        if($liveResult){

            # End all output buffers if any
            while(@ob_end_flush());

            # Create process from command
            $proc = popen($command, 'r');

            # Change type of output
            $result["output"] = [];

            # Read the process
            while(!feof($proc)){

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
