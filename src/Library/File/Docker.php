<?php declare(strict_types=1);
/**
 * File
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\System\Os;
use Dotenv\Dotenv;

/**
 * Docker
 *
 * Methods for interacting with Docker File
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Docker{

    /** Public static method | Docker Compose
     ******************************************************
     */

    /**
     * up
     * 
     * Run Docker Compose
     * 
     * @param bool $detach Run container in background and print container ID
     * @return mixed
     */
    public static function up(bool $detach = true, string $loadEnvFile = self::ENV_FILE):mixed {

        # Set result
        $result = "";

        # Set pre command
        $preCommand = "";

        # Check os
        if(Os::isWindows()){

            # Set pwd
            $preCommand .= "set PWD=%CD% & ";

            # Set env
            $envFileContent = $loadEnvFile ? parse_ini_file(File::path($loadEnvFile)) : [];

            # Check env file
            if(!empty($envFileContent)) 

                # Iteration
                foreach($envFileContent as $k => $value)

                    # Check if is int or string
                    if($k && (is_string($value) || is_int($value)))

                        # Append in pre command
                        $preCommand .= "set $k=".(is_int($value) ? $value : '"'.str_replace('"', '\\"', $value).'"')." & ";

            # Clean env file
            $loadEnvFile = "";

        }

        # Prepare command shell
        $command = self::DOCKER_COMPOSE_COMMAND.($loadEnvFile ? " --env-file '".$loadEnvFile."'" : "")." up".($detach ? " -d --no-color" : "");

        # Exec command
        $result = Command::exec($preCommand.$command);

        # Return result
        return $result;

    }

    /**
     * down
     * 
     * Down Docker Compose
     * 
     * @return mixed
     */
    public static function down(string $loadEnvFile = self::ENV_FILE):mixed {

        # Set result
        $result = "";

        # Set pre command
        $preCommand = "";

        # Check os
        if(Os::isWindows()){

            # Set pwd
            $preCommand .= "set PWD=%CD% & ";

            # Set env
            $envFileContent = $loadEnvFile ? parse_ini_file(File::path($loadEnvFile)) : [];

            # Check env file
            if(!empty($envFileContent)) 

                # Iteration
                foreach($envFileContent as $k => $value)

                    # Check if is int or string
                    if($k && (is_string($value) || is_int($value)))

                        # Append in pre command
                        $preCommand .= "set $k=".(is_int($value) ? $value : '"'.str_replace('"', '\\"', $value).'"')." & ";

            # Clean env file
            $loadEnvFile = "";

        }

        # Prepare command shell
        $command = self::DOCKER_COMPOSE_COMMAND.($loadEnvFile ? " --env-file '".$loadEnvFile."'" : "")." down";

        # Exec command
        exec($preCommand.$command, $empty, $result);

        # Return result
        return $result;

    }

    /**
     * up
     * 
     * Run Docker Compose
     * 
     * @param string $argument
     * @param string $loadEnvFile
     * @return mixed
     */
    public static function run(string $argument = "", string $loadEnvFile = self::ENV_FILE):mixed {

        # Set result
        $result = "";

        # Prepare command shell
        $command = trim(self::DOCKER_COMMAND." compose run ".trim($argument));

        # Exec command
        $result = Command::exec($command);

        # Return result
        return $result;

    }

    /** Public static method | docker compose
     ******************************************************
     */

    /**
     * get local host port
     * 
     * Return local host port for connect to app
     * 
     * @param int $source Port inside container to get
     * @return int|null
     */
    public static function getLocalHostPort($source = 80):int|null {

        # Resolve the host port on the configured HTTP edge
        return self::_getPublishedPort((int)$source);

    }

    /**
     * Get Http Port
     * 
     * Return external http port 
     * 
     * @param int $lookingPort
     * @return int|null
     */
    public static function getHttpPort(int $lookingPort=80):int|null {

        # Share port parsing with the Docker preflight helper
        return self::_getPublishedPort($lookingPort);

    }


    /**
     * Get Published Port
     *
     * @param int $target Container TCP port
     * @return int|null
     */
    private static function _getPublishedPort(int $target):?int {

        # Read the generated edge selection while retaining legacy webserver support
        $settings = File::exists("@app_root/config/Docker.yml") ? Yaml::open("@app_root/config/Docker.yml") : [];

        # Caddy exposes host ports when it is a separate TLS service
        $edge = $settings["Docker"]["edge"]["service"] ?? "webserver";

        # Load the configured port mappings
        $compose = Yaml::open(File::path(self::DOCKER_COMPOSE_PATH));

        # Read saved port overrides even before the up command loads its environment
        $environment = File::exists("@app_root/.env") ? Dotenv::parse(file_get_contents(File::path("@app_root/.env"))) : (File::exists("@app_root/docker/variables.env") ? Dotenv::parse(file_get_contents(File::path("@app_root/docker/variables.env"))) : []);

        # Inspect the selected service's TCP port mappings
        foreach($compose["services"][$edge]["ports"] ?? [] as $mapping){

            # Support Compose's long syntax for manually edited configurations
            if(is_array($mapping)){

                # Return the published TCP port matching the requested target
                if((int)($mapping["target"] ?? 0) === $target && ($mapping["protocol"] ?? "tcp") === "tcp")

                    # An unpublished port has no host endpoint
                    return isset($mapping["published"]) ? (int)$mapping["published"] : null;

                # Continue past unrelated mappings
                continue;

            }

            # Resolve the generated environment expressions before splitting host addresses
            $mapping = preg_replace_callback('/\$\{([A-Z_][A-Z0-9_]*)(?::-([^}]*))?\}/', static function(array $matches) use ($environment):string {

                # Match Compose's preference for the process environment over its env file
                return (string)(getenv($matches[1]) ?: ($environment[$matches[1]] ?? $matches[2] ?? ""));

            }, (string)$mapping);

            # Extract published and target ports while allowing a loopback host prefix
            if(preg_match('/(?:^|:)([0-9]+):([0-9]+)(?:\/tcp)?$/', $mapping, $ports) && (int)$ports[2] === $target)

                # Return the host port rather than the container port
                return (int)$ports[1];

        }

        # No TCP mapping exposes the requested container port
        return null;

    }

    /** Public constants
     ******************************************************
     */

    /**
     * Docker Structure path 
     */
    public const STRUCTURE_PATH = "@crazyphp_root/resources/Docker/Structure.yml";

    /** @var string DOCKER_COMPOSE_PATH */
    public const DOCKER_COMPOSE_PATH = "@app_root/docker-compose.yml";

    /** @var string DOCKER_COMPOSE_COMMAND */
    public const DOCKER_COMPOSE_COMMAND = "docker compose";

    /** @var string DOCKER_COMPOSE_ENV_VARIABLES_PATH */
    public const DOCKER_COMPOSE_ENV_VARIABLES_PATH = "@app_root/docker/variables.env";

    /** @var string DOCKER_COMPOSE_COMMAND */
    public const DOCKER_COMMAND = "docker";

    /** @var array NAME_TO_SERVICE Name to services */
    public const NAME_TO_SERVICE = [
        "http"      =>  "webserver",
        "php"       =>  "php-fpm",
        "node"      =>  "node",
        "mongo"     =>  "mongo",
        "mysql"     =>  "mysql",
        "mariadb"   =>  "mariadb",
        "postgresql"=>  "postgresql",
    ];

    /** @var array DATABASE_TO_SERVICE */
    public const DATABASE_TO_SERVICE = [
        "mongodb"   =>  "mongo",
        "mysql"     =>  "mysql",
        "mariadb"   =>  "mariadb",
        "postgresql"=>  "postgresql",
    ];

    /** @var string ENV_FILE */
    public const ENV_FILE = "";

}