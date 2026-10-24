<?php declare(strict_types=1);
/**
 * CLI Bootstrap
 *
 * Select Docker before loading the application's Composer platform check.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Cli;

/**
 * Dependencies
 */
use RuntimeException;

/**
 * Bootstrap
 *
 * Keep the existing PHP entrypoints usable with an explicit Docker runtime.
 */
class Bootstrap {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Run
     *
     * Normalize global options and select the requested runtime.
     *
     * @param string $entry Entrypoint path
     * @return void
     */
    public static function run(string $entry):void {

        # Normalize global switches before command arguments, preserving script flags after --
        $options = [];
        $arguments = [];
        $separator = false;

        # Read global options until the frontend argument separator
        foreach(array_slice($_SERVER["argv"], 1) as $argument){

            # Preserve the separator and all following script arguments
            if($argument === "--")

                # Treat all remaining arguments as frontend script input
                $separator = true;

            # Collect recognized global options before the command arguments
            if(!$separator && in_array($argument, ["--docker", "--yes", "-y"], true))

                # Move the option before the command for the CLI parser
                $options[] = $argument;

            else

                # Preserve the order of command and frontend arguments
                $arguments[] = $argument;

        }

        # Keep the CLI parser and application globals in sync
        $GLOBALS["argv"] = $_SERVER["argv"] = [$entry, ...$options, ...$arguments];
        $GLOBALS["argc"] = $_SERVER["argc"] = count($_SERVER["argv"]);

        # Local execution is the default; the container must not launch another container
        if(!in_array("--docker", $options, true) || getenv("CRAZY_DOCKER_RUNTIME") === "1")

            # Continue in the current PHP process
            return;

        # Dispatch the command without loading the application's dependencies
        try{

            # Run the same entrypoint with PHP and Node from the selected image
            exit(self::_docker($entry, [...$options, ...$arguments], $arguments));

        }catch(RuntimeException $exception){

            # Report bootstrap failures without requiring Composer dependencies
            fwrite(STDERR, $exception->getMessage().PHP_EOL);

            # Propagate bootstrap failures to the caller
            exit(1);

        }

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Docker
     *
     * Build the selected toolchain and run the PHP entrypoint in Docker.
     *
     * @param string $entry Entrypoint path
     * @param array $arguments Forwarded arguments
     * @param array $positionals Command arguments without global switches
     * @return int
     * @throws RuntimeException When Docker or its required configuration is unavailable
     */
    private static function _docker(string $entry, array $arguments, array $positionals):int {

        # Require Docker only when explicitly requested
        if(self::_invoke(["docker", "info"], true) !== 0)

            # Explain why the requested runtime cannot start
            throw new RuntimeException("--docker requires an installed Docker client and a running daemon.");

        # Resolve the application and library directories
        $root = getcwd();
        $library = dirname(__DIR__, 2);

        # Read the platform without loading the application's dependencies
        $composer = is_file($root."/composer.json")
            ? json_decode(file_get_contents($root."/composer.json"), true)
            : [];
        $platform = $composer["config"]["platform"]["php"] ?? "8.3.0";

        # Require a platform version that can identify the CLI image
        if(!is_string($platform) || !preg_match('/^(\d+)\.(\d+)(?:\.|$)/', $platform, $matches))

            # Report the expected version format
            throw new RuntimeException("config.platform.php must contain a PHP version such as 8.5.0.");

        # Keep the selected PHP version at or above the supported minimum
        $version = version_compare($matches[1].".".$matches[2], "8.3", "<")
            ? "8.3"
            : $matches[1].".".$matches[2];
        $image = "crazyphp-cli:php$version-node22";

        # Build the maintained PHP, Composer and Node toolchain using Docker's cache
        if(self::_invoke([
            "docker", "build",
            "--build-arg", "PHP_VERSION=$version",
            "--tag", $image,
            $library."/resources/Docker/cli"
        ]) !== 0)

            # Stop before attempting to run an unavailable image
            throw new RuntimeException("Could not build the CrazyPHP CLI Docker image.");

        # Keep application and linked library paths identical inside Docker
        $command = [
            "docker", "run", "--rm", "-i",
            "--env", "HOME=/tmp",
            "--env", "CRAZY_DOCKER_RUNTIME=1",
            "--volume", "$root:$root",
            "--workdir", $root
        ];

        # Mount a linked library located outside the application
        if($library !== $root && !str_starts_with($library, $root.DIRECTORY_SEPARATOR))

            # Preserve the linked package path inside the container
            array_push($command, "--volume", "$library:$library");

        # Preserve host ownership for files created by the container
        if(function_exists("posix_getuid"))

            # Run with the host user and group
            array_push($command, "--user", posix_getuid().":".posix_getgid());

        # Allocate a terminal only for interactive invocations
        if(function_exists("stream_isatty") && stream_isatty(STDIN) && stream_isatty(STDOUT))

            # Keep interactive prompts connected to the terminal
            $command[] = "-t";

        # Share the host daemon only for commands that manage the application's stack
        if(
            basename($entry) === "CrazyDocker" ||
            (
                basename($entry) === "CrazyCommand" &&
                array_slice($positionals, 0, 2) === ["delete", "project"] &&
                is_file($root."/docker-compose.yml")
            )
        ){

            # Resolve the daemon selected by the host environment or Docker context
            $endpoint = getenv("DOCKER_HOST") ?: self::_capture([
                "docker", "context", "inspect",
                "--format", "{{.Endpoints.docker.Host}}"
            ]);

            # Require a Unix socket that can be mounted into the CLI container
            if(!str_starts_with($endpoint, "unix://"))

                # Explain how to use Docker contexts that cannot be mounted
                throw new RuntimeException(
                    "--docker for Docker management requires a local Unix Docker socket. Use local PHP with remote Docker contexts."
                );

            # Extract the local socket path
            $socket = substr($endpoint, 7);

            # Check that the selected socket exists before mounting it
            if(@filetype($socket) !== "socket")

                # Report the missing daemon socket
                throw new RuntimeException("Docker socket not found: $socket");

            # Resolve the bind source inside the Linux VM on macOS
            if(PHP_OS_FAMILY === "Darwin")

                # Use the daemon socket path inside the Linux VM
                $socket = "/var/run/docker.sock";

            # Read the socket group inside Docker to preserve daemon access
            $group = self::_capture([
                "docker", "run", "--rm",
                "--entrypoint", "stat",
                "--volume", "$socket:/var/run/docker.sock",
                $image, "-c", "%g", "/var/run/docker.sock"
            ]);

            # Require a numeric group before preparing container permissions
            if(!ctype_digit($group))

                # Reject invalid container group permissions
                throw new RuntimeException("Could not read the Docker socket group.");

            # Mount the socket and direct the container's client to the host daemon
            array_push(
                $command,
                "--volume", "$socket:/var/run/docker.sock",
                "--env", "DOCKER_HOST=unix:///var/run/docker.sock",
                "--group-add", $group
            );

        }

        # Preserve arguments and propagate the container's exit code
        return self::_invoke([...$command, $image, "php", $entry, ...$arguments]);

    }

    /**
     * Invoke
     *
     * Run a subprocess with separate arguments and return its exit code.
     *
     * @param array $command Executable and arguments
     * @param bool $quiet Discard probe output
     * @return int
     */
    private static function _invoke(array $command, bool $quiet = false):int {

        # Select the null device for availability probes
        $null = PHP_OS_FAMILY === "Windows" ? "NUL" : "/dev/null";

        # Stream command output without shell interpolation
        $process = proc_open(
            $command,
            [
                STDIN,
                $quiet ? ["file", $null, "w"] : STDOUT,
                $quiet ? ["file", $null, "w"] : STDERR
            ],
            $pipes
        );

        # Return the process status or report that execution could not start
        return is_resource($process) ? proc_close($process) : 127;

    }

    /**
     * Capture
     *
     * Read short Docker metadata while preserving error output.
     *
     * @param array $command Executable and arguments
     * @return string
     * @throws RuntimeException When the metadata command cannot complete
     */
    private static function _capture(array $command):string {

        # Capture short daemon metadata while retaining diagnostics
        $process = proc_open($command, [STDIN, ["pipe", "w"], STDERR], $pipes);

        # Stop when the inspection process could not start
        if(!is_resource($process))

            # Report that Docker metadata could not be inspected
            throw new RuntimeException("Could not inspect the Docker runtime.");

        # Collect metadata and release the output stream
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        # Reject failed inspection commands
        if(proc_close($process) !== 0)

            # Discard metadata returned by a failed command
            throw new RuntimeException("Could not inspect the Docker runtime.");

        # Return metadata without surrounding whitespace
        return trim($output);

    }

}
