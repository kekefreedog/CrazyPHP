<?php declare(strict_types=1);
/**
 * CLI Bootstrap Tests
 *
 * Exercise the real entrypoints with isolated projects and a recorded Docker client.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\Cli;

/**
 * Dependencies
 */
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;

/**
 * Bootstrap Test
 *
 * Verify explicit runtime selection, prompt bypass and literal argument forwarding.
 */
class BootstrapTest extends TestCase {

    /** @var string $_root Isolated application directory */
    private string $_root;

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Reproduce an installed package without installing dependencies again
        $this->_root = sys_get_temp_dir()."/crazy bootstrap ".bin2hex(random_bytes(5));
        mkdir($this->_root."/vendor/kzarshenas", 0755, true);
        mkdir($this->_root."/tools");
        $library = dirname(__DIR__, 3);
        symlink($library, $this->_root."/vendor/kzarshenas/crazyphp");
        file_put_contents($this->_root."/vendor/autoload.php", "<?php require ".var_export($library."/vendor/autoload.php", true).";");
        file_put_contents($this->_root."/composer.json", json_encode(["version" => "1.2.3", "config" => ["platform" => ["php" => "8.5.0"]]]));

        # Record each invocation and control failures without contacting Docker
        file_put_contents($this->_root."/tools/docker", "#!".PHP_BINARY."\n<?php\n". <<<'CLIENT'
file_put_contents(getenv("CALL_LOG"), json_encode(array_slice($argv, 1)).PHP_EOL, FILE_APPEND);
if($argv[1] === "context")
    echo getenv("DOCKER_ENDPOINT");
if(in_array("stat", $argv, true))
    echo "0";
exit($argv[1] === getenv("FAIL_COMMAND") ? 7 : 0);
CLIENT);
        chmod($this->_root."/tools/docker", 0755);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Unlink the package without touching the shared library
        File::removeWithin(dirname($this->_root), basename($this->_root));

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Every Entrypoint Exposes Global Options
     *
     * @return void
     */
    public function testEveryEntrypointExposesGlobalOptions():void {

        # Help requires no confirmation and never invokes Docker by default
        foreach(glob(dirname(__DIR__, 3)."/bin/Crazy*") as $entry){
            [$status, $output, $error] = $this->_run(basename($entry), ["--help"]);
            $this->assertSame(0, $status, $error);
            $this->assertStringContainsString("--docker", $output);
            $this->assertStringContainsString("--yes", $output);
            $this->assertStringNotContainsString("[y/N]", $output);
        }
        $this->assertFileDoesNotExist($this->_root."/calls");

    }

    /**
     * Test Startup Confirmation And Bypass
     *
     * @return void
     */
    public function testStartupConfirmationAndBypass():void {

        # Negative and empty responses cancel before the action; EOF fails clearly
        foreach([["no\n", 0, false], ["\n", 0, false], ["yes\n", 0, true], ["", 1, false]] as [$answer, $expected, $executed]){
            [$status, $output, $error] = $this->_run("CrazyCommand", ["version"], $answer);
            $this->assertSame($expected, $status, $error);
            $this->assertStringContainsString("Run CrazyCommand version?", $output);
            $this->assertSame($executed, str_contains($output.$error, "1.2.3"));
            if($answer === "")
                $this->assertStringContainsString("--yes", $output.$error);
        }

        # Both option spellings work after the command with stdin already closed
        foreach(["--yes", "-y"] as $option){
            [$status, $output, $error] = $this->_run("CrazyCommand", ["version", $option]);
            $this->assertSame(0, $status, $error);
            $this->assertStringContainsString("1.2.3", $output.$error);
            $this->assertStringNotContainsString("[y/N]", $output);
        }
        $this->assertFileDoesNotExist($this->_root."/calls");

    }

    /**
     * Test Header Precedes Startup Confirmation
     *
     * @return void
     */
    public function testHeaderPrecedesStartupConfirmation():void {

        # Cancel every action before forms, extension checks or project mutations
        $commands = [
            ["CrazyCommand", ["version"], "🚀 Run version"],
            ["CrazyCommand", ["new", "project"], "🚀 Run new project"],
            ["CrazyCommand", ["update", "extension"], "🍿 Run update extension"],
            ["CrazyCommand", ["delete", "project"], "🚀 Run delete project"],
            ["CrazyDocker", ["new"], "🚀 Run new Docker"],
            ["CrazyDocker", ["delete"], "🚀 Run delete Docker"],
            ["CrazyAsset", ["register"], "🚀 Run Register Config Asset"],
            ["CrazyFront", ["run", "build"], "🚀 Run Frontend Build"],
            ["CrazyMigration", ["check"], "🚀 Run check migration"],
            ["CrazyMigration", ["run"], "🚀 Run migration"],
            ["CrazyWebsocket", ["run"], "🚀 Run Websocket Server"],
            ["CrazyWebsocket", ["stop"], "🚀 Stop Websocket Server"],
            ["CrazyWorkers", ["run"], "🚀 Run Workers Server"],
            ["CrazyWorkers", ["stop"], "🚀 Stop Workers Server"],
        ];

        # The logo and script title must precede the first yes/no prompt
        foreach($commands as [$entry, $arguments, $title]){
            [$status, $output, $error] = $this->_run($entry, $arguments, "no\n");
            $this->assertSame(0, $status, $error);
            $logoPosition = strpos($output, "▄████▄");
            $titlePosition = strpos($output, $title);
            $promptPosition = strpos($output, "✅ Run ".$entry." ".$arguments[0]."? ✅");
            $this->assertNotFalse($logoPosition, $output);
            $this->assertNotFalse($titlePosition, $output);
            $this->assertNotFalse($promptPosition, $output);
            $this->assertLessThan($titlePosition, $logoPosition);
            $this->assertLessThan($promptPosition, $titlePosition);
            $this->assertSame(1, substr_count($output, "▄████▄"));
            $this->assertSame(1, substr_count($output, "[y/N]"));
        }

    }

    /**
     * Test Yes Also Skips Action Confirmation
     *
     * @return void
     */
    public function testYesAlsoSkipsActionConfirmation():void {

        # Asset registration has a second confirmation after its startup prompt
        [$status, $output, $error] = $this->_run("CrazyAsset", ["register", "--yes"]);
        $this->assertSame(0, $status, $error);
        $this->assertStringContainsString("Asset registered with success", $output);
        $this->assertStringNotContainsString("[y/N]", $output);
        $this->assertSame(1, substr_count($output, "▄████▄"));
        $this->assertSame(1, substr_count($output, "🚀 Run Register Config Asset"));

        # An explicit No at the startup prompt prevents even that second prompt
        [$status, $output, $error] = $this->_run("CrazyAsset", ["register"], "no\n");
        $this->assertSame(0, $status, $error);
        $this->assertStringNotContainsString("Asset registered with success", $output);
        $this->assertSame(1, substr_count($output, "[y/N]"));

        # Successive prompts must retain piped answers and display input on the prompt line
        [$status, $output, $error] = $this->_run("CrazyAsset", ["register"], "yes\nyes\n");
        $this->assertSame(0, $status, $error);
        $this->assertStringContainsString("Asset registered with success", $output);
        $this->assertSame(2, substr_count($output, "[y/N]"));
        $this->assertStringContainsString("✅ Do you want register asset config ? ✅ [y/N] ", $output);

    }

    /**
     * Test Docker Runs Before Autoload And Preserves Arguments
     *
     * @return void
     */
    public function testDockerRunsBeforeAutoloadAndPreservesArguments():void {

        # A host-side platform check must not execute when Docker is explicitly selected
        file_put_contents($this->_root."/vendor/autoload.php", "<?php exit(93);");
        $flags = ["--", "--docker", "--yes", "literal spaces;$(false)"];
        [$status, , $error] = $this->_run("CrazyFront", ["run", "build", "--docker", "-y", ...$flags]);
        $this->assertSame(0, $status, $error);
        $calls = $this->_calls();
        $this->assertSame(["info"], $calls[0]);
        $this->assertSame(["build", "--build-arg", "PHP_VERSION=8.5"], array_slice($calls[1], 0, 3));
        $command = $calls[2];
        $this->assertContains("CRAZY_DOCKER_RUNTIME=1", $command);
        $root = realpath($this->_root);
        $this->assertContains("$root:$root", $command);
        $library = dirname(__DIR__, 3);
        $this->assertContains("$library:$library", $command);
        $this->assertSame(["crazyphp-cli:php8.5-node22", "php", $library."/bin/CrazyFront", "--docker", "-y", "run", "build", ...$flags], array_slice($command, -11));
        $this->assertNotContains("DOCKER_HOST=unix:///var/run/docker.sock", $command);

    }

    /**
     * Test Docker Failure Propagation
     *
     * @return void
     */
    public function testDockerFailurePropagation():void {

        # Probe and build failures stop before launching a command; command status reaches callers
        foreach(["info" => [1, 1], "build" => [1, 2], "run" => [7, 3]] as $failure => [$expected, $count]){
            @unlink($this->_root."/calls");
            [$status, , $error] = $this->_run("CrazyCommand", ["--docker", "--yes", "version"], "", ["FAIL_COMMAND" => $failure]);
            $this->assertSame($expected, $status, $error);
            $this->assertCount($count, $this->_calls());
        }

    }

    /**
     * Test Container Does Not Reenter Docker
     *
     * @return void
     */
    public function testContainerDoesNotReenterDocker():void {

        # The inner entrypoint keeps the option but runs PHP and Node in its existing container
        [$status, $output, $error] = $this->_run("CrazyCommand", ["--docker", "--yes", "version"], "", ["CRAZY_DOCKER_RUNTIME" => "1"]);
        $this->assertSame(0, $status, $error);
        $this->assertStringContainsString("1.2.3", $output.$error);
        $this->assertFileDoesNotExist($this->_root."/calls");

    }

    /**
     * Test Script Flags Do Not Select Docker
     *
     * @return void
     */
    public function testScriptFlagsDoNotSelectDocker():void {

        # Options after the separator belong to the frontend script
        [$status] = $this->_run("CrazyFront", ["run", "missing", "--", "--docker", "--yes"], "no\n");
        $this->assertSame(0, $status);
        $this->assertFileDoesNotExist($this->_root."/calls");

    }

    /**
     * Test Docker Management Mounts The Selected Socket
     *
     * @return void
     */
    public function testDockerManagementMountsTheSelectedSocket():void {

        # Model a local Docker context without accessing its real daemon
        $path = $this->_root."/daemon.sock";
        $socket = @stream_socket_server("unix://".$path, $code, $message);
        if($socket === false)
            $this->markTestSkipped("The environment does not permit Unix socket fixtures.");

        try{

            # Both stack generation and project deletion need the selected host daemon
            file_put_contents($this->_root."/docker-compose.yml", "services: {}\n");
            foreach([["CrazyDocker", ["new"]], ["CrazyCommand", ["delete", "project"]]] as [$entry, $arguments]){
                @unlink($this->_root."/calls");
                [$status, , $error] = $this->_run($entry, [...$arguments, "--docker", "--yes"], "", ["DOCKER_ENDPOINT" => "unix://".$path]);
                $this->assertSame(0, $status, $error);
                $calls = $this->_calls();
                $command = end($calls);
                $source = PHP_OS_FAMILY === "Darwin" ? "/var/run/docker.sock" : $path;
                $this->assertContains("$source:/var/run/docker.sock", $command);
                $this->assertContains("DOCKER_HOST=unix:///var/run/docker.sock", $command);
                $this->assertContains("--group-add", $command);
            }

        }finally{

            # Release only the isolated fixture socket
            fclose($socket);
            unlink($path);

        }

    }

    /**
     * Test Remote Docker Management Requires Local PHP
     *
     * @return void
     */
    public function testRemoteDockerManagementRequiresLocalPhp():void {

        # Reject unsupported daemon mounts before launching the entrypoint
        [$status, , $error] = $this->_run("CrazyDocker", ["new", "--docker", "--yes"], "", ["DOCKER_HOST" => "tcp://example.invalid:2375"]);
        $this->assertSame(1, $status);
        $this->assertStringContainsString("requires a local Unix Docker socket", $error);
        $this->assertCount(2, $this->_calls());

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Run
     *
     * @param string $entry Entrypoint name
     * @param array $arguments CLI arguments
     * @param string $input Confirmation answers
     * @param array $environment Additional environment variables
     * @return array
     */
    private function _run(string $entry, array $arguments, string $input = "", array $environment = []):array {

        # Use files for input so a refused or skipped prompt cannot block the test
        file_put_contents($this->_root."/input", $input);
        $environment += ["PATH" => $this->_root."/tools", "CALL_LOG" => $this->_root."/calls", "CRAZY_DOCKER_RUNTIME" => "", "DOCKER_HOST" => ""];
        $process = proc_open([PHP_BINARY, dirname(__DIR__, 3)."/bin/".$entry, ...$arguments], [
            ["file", $this->_root."/input", "r"], ["pipe", "w"], ["pipe", "w"],
        ], $pipes, $this->_root, $environment);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), $output, $error];

    }

    /**
     * Calls
     *
     * @return array
     */
    private function _calls():array {

        # Read complete invocations, preserving every argument boundary
        return array_map(fn($line) => json_decode($line, true), file($this->_root."/calls", FILE_IGNORE_NEW_LINES));

    }

}
