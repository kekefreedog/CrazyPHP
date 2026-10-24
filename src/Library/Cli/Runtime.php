<?php declare(strict_types=1);
/**
 * Command Runtime
 *
 * Run project tools in the runtime selected by the PHP entrypoint.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Cli;

/**
 * Dependencies
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\File;
use League\CLImate\CLImate;

/**
 * Runtime
 *
 * Preserve argument boundaries and stop creation when a tool fails.
 */
class Runtime {

    /** Public Constants
     ******************************************************
     */

    /** @var string IMAGE Shared image used by CLI commands for the minimum PHP version */
    public const IMAGE = "crazyphp-cli:php8.3-node22";

    /** Public Static Parameters
     ******************************************************
     */

    /** @var bool $assumeYes Skip yes/no prompts for explicitly unattended commands */
    public static bool $assumeYes = false;

    /** Private Parameters
     ******************************************************
     */

    /** @var CLImate|null $_climate Styled terminal for confirmation prompts */
    private ?CLImate $_climate = null;

    /**
     * Constructor
     *
     * @param CLImate|null $climate Styled terminal for confirmation prompts
     */
    public function __construct(?CLImate $climate = null){

        # Preserve the command's existing colors and formatting
        $this->_climate = $climate;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Image
     *
     * Name the CLI image for the project's PHP version, matching the bootstrap's tag
     *
     * @param string|null $version PHP major and minor version, the project's version when null
     * @return string
     */
    public static function image(?string $version = null):string {

        # Return image tag
        return "crazyphp-cli:php".($version ?? Composer::getPhpVersion())."-node22";

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Confirm
     *
     * @param string $question Confirmation displayed to the user
     * @return bool
     */
    public function confirm(string $question):bool {

        # Honor the command's explicit unattended option
        if(self::$assumeYes)

            # Skip reading input when the caller supplied --yes
            return true;

        # Use the shared command helper with the existing terminal styling
        return Command::confirm($question, $this->_climate);

    }

    /**
     * Execute
     *
     * @param string $tool Tool to execute
     * @param array $arguments Separate command arguments
     * @return void
     */
    public function execute(string $tool, array $arguments = []):void {

        # Prepare the command in the application directory
        $command = $this->command($tool, $arguments);

        # Execute without a shell and propagate failures
        $status = $this->invoke($command, File::path("@app_root"));

        # Stop before the next creation step on failure
        if($status !== 0)

            # Include the tool and exit code in the error
            throw new CrazyException("$tool failed with exit code $status", 500);

    }

    /**
     * Command
     *
     * @param string $tool PHP, Composer, npm or Docker
     * @param array $arguments Separate command arguments
     * @return array
     */
    public function command(string $tool, array $arguments = []):array {

        # Restrict execution to project creation tools
        if(!in_array($tool, ["php", "composer", "npm", "docker"], true))

            # Reject unsupported tools
            throw new CrazyException("Unsupported project tool: $tool", 500);

        # Prefer the PHP interpreter already running this command
        if($tool === "php")

            # Reuse its extensions and configuration
            return [PHP_BINARY, ...$arguments];

        # Find the local tool in the selected runtime
        $executable = $this->find($tool);

        # Docker commands must use the available client, never recursively start a fallback
        if($tool === "docker")

            # Report a missing client before trying any project mutation
            return $executable !== null ? [$executable, ...$arguments] : throw new CrazyException("Install Docker and its Compose plugin before running CrazyDocker new.", 500);

        # Use local Composer through the current PHP interpreter
        if($executable !== null && $tool === "composer")

            # Preserve the PHP runtime selected by the entrypoint
            return [PHP_BINARY, $executable, ...$arguments];

        # Use local npm only when Node is also installed
        if($executable !== null && $this->find("node") !== null)

            # Run npm with separate arguments
            return [$executable, ...$arguments];

        # Keep Docker opt-in instead of silently switching runtimes
        throw new CrazyException("Install $tool locally (npm also requires Node), or rerun the Crazy command with --docker.", 500);

    }

    /**
     * Is Port Available
     *
     * Check no local service or published container already answers on a host port.
     *
     * @param int $port Host TCP port
     * @return bool
     */
    public function isPortAvailable(int $port):bool {

        # Iteration of IPv4 and IPv6 loopback addresses
        foreach(["127.0.0.1", "[::1]"] as $host){

            # Try a short connection without waiting on filtered addresses
            $socket = @stream_socket_client("tcp://$host:$port", $errorCode, $errorMessage, 0.2);

            # Check a listener answered
            if($socket !== false){

                # Release the probe connection
                fclose($socket);

                # Report the port as used
                return false;

            }

        }

        # Report the port as available
        return true;

    }

    /** Protected Methods
     ******************************************************
     */

    /**
     * Find
     *
     * @param string $name Executable name
     * @return string|null
     */
    protected function find(string $name):?string {

        # Search each directory in the executable search path
        foreach(explode(PATH_SEPARATOR, getenv("PATH") ?: "") as $directory){

            # Include Windows command wrappers when applicable
            foreach(PHP_OS_FAMILY === "Windows" ? ["", ".exe", ".bat", ".cmd"] : [""] as $suffix){

                # Resolve the candidate without executing a shell
                $path = $directory.DIRECTORY_SEPARATOR.$name.$suffix;

                # Accept only executable files
                if(is_file($path) && is_executable($path))

                    # Return the first matching executable
                    return $path;

            }

        }

        # Report the missing executable
        return null;

    }

    /**
     * Invoke
     *
     * @param array $command Executable and arguments
     * @param string|null $directory Working directory
     * @param bool $quiet Discard probe output
     * @return int
     */
    protected function invoke(array $command, ?string $directory = null, bool $quiet = false):int {

        # Select the platform's null device for availability probes
        $null = PHP_OS_FAMILY === "Windows" ? "NUL" : "/dev/null";

        # Stream normal commands while keeping availability probes quiet
        $descriptors = [STDIN, $quiet ? ["file", $null, "w"] : STDOUT, $quiet ? ["file", $null, "w"] : STDERR];

        # Start the process with argument boundaries preserved
        $process = proc_open($command, $descriptors, $pipes, $directory, null, ["bypass_shell" => true]);

        # Return the real subprocess exit code
        return is_resource($process) ? proc_close($process) : 127;

    }

}
