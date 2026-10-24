<?php declare(strict_types=1);
/**
 * Runtime Tests
 *
 * Verify local execution and explicit confirmation without starting containers.
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

        # Reset explicit unattended mode
        Runtime::$assumeYes = false;

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
     * Test Missing Tools Do Not Fall Back To Docker
     *
     * @return void
     */
    public function testMissingToolsDoNotFallBackToDocker():void {

        # A local command must not start Docker even when its client is installed
        $runtime = new ProbedRuntime();
        $runtime->available = ["docker"];
        foreach(["composer", "npm"] as $tool){
            try{
                $runtime->command($tool);
                $this->fail("Missing tool should require explicit --docker.");
            }catch(CrazyException $exception){
                $this->assertStringContainsString("--docker", $exception->getMessage());
            }
        }
        $this->assertSame([], $runtime->probes);

    }

    /**
     * Test Explicit Yes Skips Input
     *
     * @return void
     */
    public function testExplicitYesSkipsInput():void {

        # Share the CLI bypass with model-level confirmation prompts
        Runtime::$assumeYes = true;
        $this->assertTrue((new Runtime())->confirm("Continue?"));

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
        foreach([["yes\n", "YES", 0], ["no\n", "NO", 0], ["\n", "NO", 0], ["maybe\nyes\n", "YES", 0], ["", "CLOSED", 2]] as [$input, $expected, $status]){

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
