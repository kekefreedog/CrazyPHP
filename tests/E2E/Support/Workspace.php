<?php declare(strict_types=1);
/**
 * End-to-end Workspace
 *
 * One generated application on disk, with the commands used to create, build, start and stop it.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\E2E\Support;

/**
 * Dependencies
 */
use Tests\E2E\Scenario\Scenario;
use Symfony\Component\Yaml\Yaml;
use RuntimeException;

/**
 * Workspace
 *
 * Run each lifecycle step of a scenario and log its output next to the generated project.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class Workspace {

    /** Public Parameters
     ******************************************************
     */

    /** @var string $root Application directory */
    public readonly string $root;

    /** @var string $log Command log of this scenario */
    public readonly string $log;

    /** @var int $httpPort Host HTTP port */
    public readonly int $httpPort;

    /** @var int $httpsPort Host HTTPS port */
    public readonly int $httpsPort;

    /** Private Parameters
     ******************************************************
     */

    /** @var bool $_started Compose stack started */
    private bool $_started = false;

    /**
     * Constructor
     *
     * @param Scenario $scenario Answers to apply
     * @param string $directory Parent directory, shared with Docker (under $HOME for Colima / Docker Desktop)
     */
    public function __construct(public readonly Scenario $scenario, string $directory){

        # Set one directory per scenario
        $this->root = rtrim($directory, "/")."/".$scenario->id();

        # Reset a previous run
        if(is_dir($this->root))

            # Remove previous files
            $this->remove();

        # Create the directory
        mkdir($this->root, 0777, true);

        # Keep the full output next to the project
        $this->log = $this->root."/e2e.log";

        # Start the log with the scenario
        file_put_contents(
            $this->log,
            "# ".$scenario->label().PHP_EOL.json_encode($scenario->parameters(), JSON_PRETTY_PRINT).PHP_EOL
        );

        # Reserve two free host ports
        [$this->httpPort, $this->httpsPort] = self::_freePorts(2);

    }

    /** Public Methods | Lifecycle
     ******************************************************
     */

    /**
     * Bootstrap
     *
     * Same starting point as docs/NewProject.md: an empty folder requiring the library, here
     * through a path repository to the checkout under test.
     *
     * @return self
     */
    public function bootstrap():self {

        # Get library checkout under test
        $library = dirname(__DIR__, 3);

        # Write the bootstrap manifest
        file_put_contents($this->root."/composer.json", json_encode([
            "repositories"      => [["type" => "path", "url" => $library, "options" => ["symlink" => true]]],
            "require"           => ["kzarshenas/crazyphp" => "*"],
            "minimum-stability" => "dev",
            "prefer-stable"     => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        # Install the library
        $this->run(["composer", "install", "--no-interaction", "--no-progress"]);

        # Return workspace
        return $this;

    }

    /**
     * Create
     *
     * @return self
     */
    public function create():self {

        # Run `crazy new project` generation with the scenario answers
        return $this->_step("create", $this->scenario->createOptions());

    }

    /**
     * Docker
     *
     * @return self
     */
    public function docker():self {

        # Run `crazy docker new` generation and build
        return $this->_step("docker", $this->scenario->dockerOptions($this->httpPort, $this->httpsPort));

    }

    /**
     * Up
     *
     * @param int $timeout Seconds to wait for healthy services
     * @return self
     */
    public function up(int $timeout = 600):self {

        # Mark first so a partial start is still stopped
        $this->_started = true;

        # Start and wait for health checks and the prepare job
        $this->run(["docker", "compose", "up", "--detach", "--wait", "--wait-timeout", (string) $timeout]);

        # Return workspace
        return $this;

    }

    /**
     * Down
     *
     * @param bool $images Also remove images built for this scenario
     * @return self
     */
    public function down(bool $images = false):self {

        # Check the stack was started
        if(!$this->_started)

            # Nothing to stop
            return $this;

        # Keep logs for diagnosis before removing containers
        $this->run(["docker", "compose", "logs", "--no-color", "--timestamps"], false);

        # Stop and remove containers, networks and database volumes
        $this->run(["docker", "compose", "down", "--volumes", "--remove-orphans", ...($images ? ["--rmi", "local"] : [])], false);

        # Mark stopped
        $this->_started = false;

        # Return workspace
        return $this;

    }

    /**
     * Remove
     *
     * @return void
     */
    public function remove():void {

        # Remove generated files without logging into them; vendor/kzarshenas/crazyphp is a symlink and is only unlinked
        proc_close(proc_open(["rm", "-rf", $this->root], [], $pipes, dirname($this->root)));

    }

    /** Public Methods | Helpers for checks
     ******************************************************
     */

    /**
     * Path
     *
     * @param string $path Relative path
     * @return string
     */
    public function path(string $path = ""):string {

        # Return absolute path inside the application
        return $path === ""
            ? $this->root
            : $this->root."/".ltrim($path, "/")
        ;

    }

    /**
     * Json
     *
     * @param string $path Relative path
     * @return array
     */
    public function json(string $path):array {

        # Decode a generated JSON file
        return json_decode((string) file_get_contents($this->path($path)), true, flags: JSON_THROW_ON_ERROR);

    }

    /**
     * Yaml
     *
     * @param string $path Relative path
     * @return array
     */
    public function yaml(string $path):array {

        # Parse a generated YAML file
        return (array) Yaml::parseFile($this->path($path));

    }

    /**
     * Run
     *
     * @param array $command Command and arguments
     * @param bool $check Throw on a nonzero exit code
     * @param string|null $directory Working directory, the application by default
     * @return array{0:int, 1:string} Exit code and combined output
     */
    public function run(array $command, bool $check = true, ?string $directory = null):array {

        # Log the command
        file_put_contents($this->log, PHP_EOL."$ ".implode(" ", array_map("escapeshellarg", $command)).PHP_EOL, FILE_APPEND);

        # Start the process, merging stderr and never waiting on stdin
        $process = proc_open(
            $command,
            [
                0 => ["file", "/dev/null", "r"],
                1 => ["pipe", "w"],
                2 => ["redirect", 1],
            ],
            $pipes,
            $directory ?? $this->root,
            self::_environment()
        );

        # Prepare output
        $output = "";

        # Stream output to the log
        while(!feof($pipes[1])){

            # Read a chunk
            $chunk = (string) fread($pipes[1], 8192);

            # Keep and log it
            $output .= $chunk;
            file_put_contents($this->log, $chunk, FILE_APPEND);

        }

        # Close the process
        fclose($pipes[1]);
        $code = proc_close($process);

        # Check the command succeeded
        if($check && $code !== 0)

            # Fail with the end of the output
            throw new RuntimeException(
                "Command failed ($code): ".implode(" ", $command).PHP_EOL.
                substr($output, -4000).PHP_EOL.
                "Full log: ".$this->log
            );

        # Return result
        return [$code, $output];

    }

    /**
     * Request
     *
     * @param string $url Absolute URL
     * @param array $headers Request headers
     * @return array{status:int, body:string}
     */
    public function request(string $url, array $headers = []):array {

        # Prepare header arguments
        $arguments = [];

        # Iteration of headers
        foreach($headers as $header)

            # Add header
            array_push($arguments, "--header", $header);

        # Use curl for TLS with Caddy's local certificates
        [, $output] = $this->run([
            "curl", "--silent", "--show-error", "--insecure", "--max-time", "30",
            "--retry", "5", "--retry-delay", "2", "--retry-all-errors",
            ...$arguments,
            "--write-out", "\n%{http_code}", $url,
        ], false);

        # Split body and status
        $position = strrpos($output, "\n");

        # Return result
        return [
            "status"    => (int) substr($output, $position + 1),
            "body"      => substr($output, 0, $position),
        ];

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Step
     *
     * @param string $step create or docker
     * @param array $options Generator options
     * @return self
     */
    private function _step(string $step, array $options):self {

        # Run the generator in its own process
        $this->run([PHP_BINARY, __DIR__."/step.php", $step, $this->root, json_encode($options, JSON_THROW_ON_ERROR)]);

        # Return workspace
        return $this;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Environment
     *
     * @return array
     */
    private static function _environment():array {

        # Inherit the environment, without colours or interactive prompts
        return getenv() + [
            "NO_COLOR"                  => "1",
            "COMPOSER_NO_INTERACTION"   => "1",
            "npm_config_fund"           => "false",
            "npm_config_audit"          => "false",
        ];

    }

    /**
     * Free Ports
     *
     * Ask the host for unused ports (not a loopback probe, which misses ports published by Docker).
     *
     * @param int $count Number of ports
     * @return int[]
     */
    private static function _freePorts(int $count):array {

        # Prepare sockets and ports
        $sockets = [];
        $ports = [];

        # Hold every socket until all ports are known so they are distinct
        for($i = 0; $i < $count; $i++){

            # Bind any free port
            $socket = stream_socket_server("tcp://0.0.0.0:0");

            # Keep socket and port
            $sockets[] = $socket;
            $ports[] = (int) substr(strrchr((string) stream_socket_get_name($socket, false), ":"), 1);

        }

        # Iteration of sockets
        foreach($sockets as $socket)

            # Release the port
            fclose($socket);

        # Return ports
        return $ports;

    }

}
