<?php declare(strict_types=1);
/**
 * Runtime Tests
 *
 * Verify local execution and Docker fallback without starting containers.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\Cli;

/**
 * Dependencies
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Runtime Test
 *
 * Exercise command construction and real subprocess exit statuses.
 */
class RuntimeTest extends TestCase {

    /** @var string $_root Temporary project with spaces in its path */
    private string $_root;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Create a separate application root for each test
        $this->_root = sys_get_temp_dir()."/crazy runtime ".bin2hex(random_bytes(5));

        # Create the test directory
        mkdir($this->_root);

        # Configure the runtime's host mounts
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 3)]);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove this test's fixture only
        File::removeAll($this->_root);

        # Reset application environment state
        Env::reset();

    }

    /**
     * Test Local Tools Win
     *
     * @return void
     */
    public function testLocalToolsWin():void {

        # Provide local tools and an available Docker executable
        $runtime = new ProbedRuntime();

        # Simulate all local tools being installed
        $runtime->available = ["composer", "node", "npm", "docker"];

        # Preserve each argument including shell metacharacters
        $this->assertSame([PHP_BINARY, "/tools/composer", "update", "a b;$(false)"], $runtime->command("composer", ["update", "a b;$(false)"]));

        # Prefer local npm as well
        $this->assertSame(["/tools/npm", "install"], $runtime->command("npm", ["install"]));

        # Docker must not even be probed when local tools exist
        $this->assertSame([], $runtime->probes);

    }

    /**
     * Test Docker Management Uses Local Client
     *
     * @return void
     */
    public function testDockerManagementUsesLocalClient():void {

        # Make Docker available without Node or Composer
        $runtime = new ProbedRuntime();

        # Expose only the Docker client
        $runtime->available = ["docker"];

        # Preserve Compose arguments without entering the tool fallback
        $this->assertSame(["/tools/docker", "compose", "config", "--quiet"], $runtime->command("docker", ["compose", "config", "--quiet"]));

        # Docker management must not recursively build a PHP container
        $this->assertSame([], $runtime->probes);

        # Remove the client to exercise the actionable missing-tool failure
        $runtime->available = [];

        # Require the Docker installation message
        $this->expectExceptionMessage("Install Docker and its Compose plugin");

        # Resolve Docker without falling back to Docker itself
        $runtime->command("docker", ["info"]);

    }

    /**
     * Test Compose Aliases Without PHP
     *
     * @return void
     */
    public function testComposeAliasesWithoutPhp():void {

        # Expose only a fake Docker executable so the launcher cannot find PHP
        file_put_contents($this->_root."/docker", '#!/bin/sh'."\n".'printf "%s\n" "$@"'."\nexit 7\n");

        # Permit the shell to execute the argument recorder
        chmod($this->_root."/docker", 0755);

        # Verify both lifecycle aliases and propagation of Docker's exit status
        foreach(["up", "down"] as $action){

            # Keep arguments containing spaces intact without shell interpolation
            $process = proc_open(["/bin/sh", dirname(__DIR__, 3)."/bin/crazy", "docker", $action, "argument with spaces"], [1 => ["pipe", "w"], 2 => ["pipe", "w"]], $pipes, $this->_root, ["PATH" => $this->_root]);

            # Capture the exact forwarded arguments
            $output = stream_get_contents($pipes[1]);

            # Capture unexpected launcher errors
            $error = stream_get_contents($pipes[2]);

            # Release descriptors before collecting the child status
            fclose($pipes[1]);
            fclose($pipes[2]);

            # Preserve Docker's result and every positional argument
            $this->assertSame(7, proc_close($process));
            $this->assertSame("compose\n$action\nargument with spaces\n", $output);
            $this->assertSame("", $error);

        }

    }

    /**
     * Test Docker Fallback
     *
     * @return void
     */
    public function testDockerFallback():void {

        # Simulate a machine with Docker and no Composer executable
        $runtime = new ProbedRuntime();

        # Expose only the Docker executable
        $runtime->available = ["docker"];

        # Prepare the fallback command without running Docker
        $command = $runtime->command("composer", ["update", "--no-interaction"]);

        # Probe the daemon before building its runtime image
        $this->assertSame(["docker", "info"], $runtime->probes[0]);

        # Build the maintained runtime Dockerfile
        $this->assertSame("build", $runtime->probes[1][1]);

        # Preserve application paths with spaces as single arguments
        $this->assertContains(realpath($this->_root).":".realpath($this->_root), $command);

        # Mount an external linked library at its real host path
        $library = realpath(dirname(__DIR__, 3));

        # Keep Composer symlink targets accessible inside the container
        $this->assertContains("$library:$library", $command);

        # Keep the image, tool and user arguments in the correct order
        $this->assertSame([Runtime::image(), "composer", "update", "--no-interaction"], array_slice($command, -4));

    }

    /**
     * Test Unavailable Daemon Stops Before Build
     *
     * @return void
     */
    public function testUnavailableDaemonStopsBeforeBuild():void {

        # Simulate a Docker client whose daemon is stopped
        $runtime = new ProbedRuntime();

        # Make the client discoverable
        $runtime->available = ["docker"];

        # Fail its availability probe
        $runtime->status = 1;

        # Require a useful runtime error
        $this->expectException(CrazyException::class);

        # Do not attempt installation with an unavailable daemon
        try{

            # Resolve the missing npm runtime
            $runtime->command("npm", ["install"]);

        }finally{

            # Never start an image build after a failed probe
            $this->assertCount(1, $runtime->probes);

        }

    }

    /**
     * Test Subprocess Exit Code
     *
     * @return void
     */
    public function testSubprocessExitCode():void {

        # Require a real nonzero child status to become an exception
        $this->expectException(CrazyException::class);

        # Keep the original exit status visible in the error
        $this->expectExceptionMessage("exit code 7");

        # Run a harmless PHP subprocess that fails deliberately
        (new Runtime())->execute("php", ["-r", "exit(7);"]);

    }

    /**
     * Test Argument Boundaries And Working Directory
     *
     * @return void
     */
    public function testArgumentBoundariesAndWorkingDirectory():void {

        # Include characters a shell would otherwise interpret
        $argument = "spaces ' quotes ; $(false)";

        # Write the received arguments from a real child process
        (new Runtime())->execute("php", ["-r", 'file_put_contents("arguments.json", json_encode([$argv[1], getcwd()]));', $argument]);

        # Verify literal input and the selected working directory
        $this->assertSame([$argument, realpath($this->_root)], json_decode(file_get_contents($this->_root."/arguments.json"), true));

    }

    /**
     * Test Confirmation Requires An Explicit Answer
     *
     * @return void
     */
    public function testConfirmationRequiresAnExplicitAnswer():void {

        # Exercise the real terminal input path without blocking the test runner
        foreach([["yes\n", "YES", 0], ["no\n", "NO", 0], ["\n", "NO", 0], ["", "CLOSED", 2]] as [$input, $expected, $status]){

            # Supply a controlled stream, including the closed-input case
            file_put_contents($this->_root."/input", $input);

            # Run the actual confirmation method in an isolated PHP process
            $code = 'require $argv[1]; try { echo (new \\CrazyPHP\\Library\\Cli\\Runtime())->confirm("Passwords updated?") ? "YES" : "NO"; } catch (\\CrazyPHP\\Exception\\CrazyException $exception) { echo "CLOSED"; exit(2); }';

            # Keep the bootstrap path separate from executable code
            $process = proc_open([PHP_BINARY, "-r", $code, dirname(__DIR__, 3)."/vendor/autoload.php"], [
                ["file", $this->_root."/input", "r"], ["pipe", "w"], ["pipe", "w"],
            ], $pipes);

            # Read the displayed prompt and the observed answer
            $output = stream_get_contents($pipes[1]);

            # Collect unexpected PHP errors separately
            $errors = stream_get_contents($pipes[2]);

            # Close the output stream before collecting the child status
            fclose($pipes[1]);

            # Close the error stream too
            fclose($pipes[2]);

            # Require a nonzero exit for closed input instead of an infinite prompt loop
            $this->assertSame($status, proc_close($process), $errors);

            # Only an explicit affirmative response may approve the confirmation
            $this->assertStringEndsWith($expected, $output);

        }

    }

    /**
     * Test Launcher Without PHP
     *
     * @return void
     */
    public function testLauncherWithoutPhp():void {

        # Exercise the portable shell launcher on Unix hosts
        if(PHP_OS_FAMILY === "Windows")

            # Windows environments need a compatible shell for this entrypoint
            $this->markTestSkipped("The launcher test requires a POSIX shell.");

        # Use a PATH containing only tools controlled by this fixture
        $directory = $this->_root."/bin";

        # Create the isolated executable directory
        mkdir($directory);

        # Provide the launcher's path and user utilities
        foreach(["dirname", "id"] as $name)

            # Expose only the required system executable
            symlink("/usr/bin/".$name, $directory."/".$name);

        # Record Docker invocations without building or running containers
        file_put_contents($directory."/docker", '#!/bin/sh'."\n".'printf "%s\\n" "$@" >> "$CALL_LOG"'."\n");

        # Allow the fake Docker client to run
        chmod($directory."/docker", 0755);

        # Hide PHP and pass a log destination with spaces
        $environment = ["PATH" => $directory, "CALL_LOG" => $this->_root."/calls"];

        # Start the actual launcher with arguments that must remain separate
        $process = proc_open([
            "/bin/sh", dirname(__DIR__, 3)."/bin/crazy", "new", "project", "with spaces",
        ], [["file", "/dev/null", "r"], ["file", "/dev/null", "w"], ["file", "/dev/null", "w"]], $pipes, $this->_root, $environment);

        # Verify that PHP absence activates a successful Docker fallback
        $this->assertSame(0, proc_close($process));

        # Read every argument received by the Docker client
        $arguments = file($this->_root."/calls", FILE_IGNORE_NEW_LINES);

        # Check daemon availability before performing a build
        $this->assertSame("info", $arguments[0]);

        # Preserve the user's command arguments
        $this->assertSame(["new", "project", "with spaces"], array_slice($arguments, -3));

        # Keep the application mount as one argument
        $root = realpath($this->_root);

        # Preserve paths with spaces through the shell wrapper
        $this->assertContains("$root:$root", $arguments);

        # Route frontend scripts through the same PHP-free launcher without daemon socket access
        file_put_contents($this->_root."/calls", "");
        $process = proc_open(["/bin/sh", dirname(__DIR__, 3)."/bin/crazy", "front", "run", "build", "--", "--mode=development"], [["file", "/dev/null", "r"], ["file", "/dev/null", "w"], ["file", "/dev/null", "w"]], $pipes, $this->_root, $environment);
        $this->assertSame(0, proc_close($process));

        # Keep the dedicated frontend entrypoint and arguments intact inside the image
        $arguments = file($this->_root."/calls", FILE_IGNORE_NEW_LINES);
        $this->assertSame([dirname(__DIR__, 3)."/bin/CrazyFront", "run", "build", "--", "--mode=development"], array_slice($arguments, -5));
        $this->assertNotContains("DOCKER_HOST=unix:///var/run/docker.sock", $arguments);

    }

    /**
     * Test Docker Launcher Without PHP
     *
     * @return void
     */
    public function testDockerLauncherWithoutPhp():void {

        # The portable launcher uses POSIX paths and Unix daemon sockets
        if(PHP_OS_FAMILY === "Windows")

            # Native Windows can continue to use the PHP entrypoint
            $this->markTestSkipped("This launcher test requires a POSIX shell.");

        # Create a harmless local socket so the real launcher can validate its mount
        $socketPath = $this->_root."/daemon.sock";

        # Listen without accepting requests; the fake client records commands only
        $socket = @stream_socket_server("unix://".$socketPath, $error, $message);

        # Some sandbox policies prohibit even isolated local socket fixtures
        if($socket === false)

            # Keep the test executable on hosts that permit Unix sockets
            $this->markTestSkipped("The environment does not permit creating a Unix socket fixture.");

        try{

            # Hide PHP from the launcher's executable search path
            $directory = $this->_root."/docker-bin";

            # Create an isolated tool directory
            mkdir($directory);

            # Expose only the shell utilities needed by the wrapper
            foreach(["dirname", "id", "uname"] as $name)

                # Use the actual host utility without exposing PHP
                symlink("/usr/bin/".$name, $directory."/".$name);

            # Record invocation boundaries and answer the context probe
            file_put_contents($directory."/docker", '#!/bin/sh'."\n".'printf "%s\\n" "$@" >> "$CALL_LOG"'."\n".'if [ "$1" = context ]; then printf "unix://%s\\n" "$SOCKET_PATH"; fi'."\n".'for arg in "$@"; do if [ "$arg" = stat ]; then printf "0\\n"; fi; done'."\n");

            # Allow the fake client to execute
            chmod($directory."/docker", 0755);

            # Keep environment values separate from shell code
            $environment = ["PATH" => $directory, "CALL_LOG" => $this->_root."/docker-calls", "SOCKET_PATH" => $socketPath];

            # Dispatch the user-facing Docker command through the fallback image
            $process = proc_open(["/bin/sh", dirname(__DIR__, 3)."/bin/crazy", "docker", "new"], [["file", "/dev/null", "r"], ["file", "/dev/null", "w"], ["pipe", "w"]], $pipes, $this->_root, $environment);

            # Capture errors for a useful failure message
            $errors = stream_get_contents($pipes[2]);

            # Close the output stream before collecting the process status
            fclose($pipes[2]);

            # Require a successful fallback without a local PHP executable
            $this->assertSame(0, proc_close($process), $errors);

            # Inspect every argument passed through the shell wrapper
            $arguments = file($this->_root."/docker-calls", FILE_IGNORE_NEW_LINES);

            # The command must run the dedicated Docker entrypoint
            $this->assertSame([dirname(__DIR__, 3)."/bin/CrazyDocker", "new"], array_slice($arguments, -2));

            # Resolve the bind source inside the Linux VM on macOS, or on the native daemon
            $this->assertContains((PHP_OS_FAMILY === "Darwin" ? "/var/run/docker.sock" : $socketPath).":/var/run/docker.sock", $arguments);

            # Direct the image's client at the mounted host daemon
            $this->assertContains("DOCKER_HOST=unix:///var/run/docker.sock", $arguments);

            # Project deletion also needs the host daemon when its Compose environment exists
            file_put_contents($this->_root."/docker-compose.yml", "services: {}\n");
            file_put_contents($this->_root."/docker-calls", "");
            $process = proc_open(["/bin/sh", dirname(__DIR__, 3)."/bin/crazy", "delete", "project"], [["file", "/dev/null", "r"], ["file", "/dev/null", "w"], ["pipe", "w"]], $pipes, $this->_root, $environment);

            # Capture launcher failures without invoking a real Docker daemon
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
            $arguments = file($this->_root."/docker-calls", FILE_IGNORE_NEW_LINES);

            # Mount the socket for application deletion while retaining the normal command entrypoint
            $this->assertSame([dirname(__DIR__, 3)."/bin/CrazyCommand", "delete", "project"], array_slice($arguments, -3));
            $this->assertContains("DOCKER_HOST=unix:///var/run/docker.sock", $arguments);

        }finally{

            # Release the fixture socket before deleting the temporary directory
            fclose($socket);

        }

    }

}

/**
 * Probed Runtime
 *
 * Simulate executable discovery and Docker availability.
 */
class ProbedRuntime extends Runtime {

    /** @var array $available Available executable names */
    public array $available = [];

    /** @var array $probes Recorded availability and build commands */
    public array $probes = [];

    /** @var int $status Simulated subprocess status */
    public int $status = 0;

    /**
     * Find
     *
     * @param string $name Executable name
     * @return string|null
     */
    protected function find(string $name):?string {

        # Return a predictable executable path when installed
        return in_array($name, $this->available, true) ? "/tools/".$name : null;

    }

    /**
     * Invoke
     *
     * @param array $command Requested command
     * @param string|null $directory Requested directory
     * @param bool $quiet Probe output mode
     * @return int
     */
    protected function invoke(array $command, ?string $directory = null, bool $quiet = false):int {

        # Record the command without contacting Docker
        $this->probes[] = $command;

        # Return the selected probe result
        return $this->status;

    }

}
