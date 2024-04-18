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

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\System\Os;
use CrazyPHP\Library\System\Terminal;

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
     * @return
     */
    public static function up(bool $detach = true, string $loadEnvFile = self::ENV_FILE) {

        # Set result
        $result = "";

        # Set pre command
        $preCommand = "";

        # Check os
        if(Os::isWindows()){

            # Check power shell
            if(Terminal::isWindowsPowerShell()){

                # Set pwd
                $preCommand .= '$env:PWD = Get-Location;';

                # Set env
                $envFileContent = parse_ini_file(File::path($loadEnvFile));

                # Check env file
                if(!empty($envFileContent)) 

                    # Iteration
                    foreach($envFileContent as $k => $value)

                        # Check if is string
                        if($k && is_numeric($value))

                            # Append in pre command
                            $preCommand .= ' $env:'."$k = \"".str_replace('"', '\\"', $value).'"; ';

                        else
                        # check if number is int
                        if($k && is_string($value))

                            # Append in pre command
                 $preCommand .= ' $env:'."$k = \"".str_replace('"', '\\"', $value).'"; ';

            }else
            # check if class terminal
            if(Terminal::isWindowsCommandPrompt()){

                # Set pwd
                $preCommand .= "set PWD=%CD% | ";

                # Set env
                $envFileContent = parse_ini_file(File::path($loadEnvFile));

                # Check env file
                if(!empty($envFileContent)) 

                    # Iteration
                    foreach($envFileContent as $k => $value)

                        # Check if is string
                        if($k && is_numeric($value))

                            # Append in pre command
                            $preCommand .= "set $k=$value | ";

                        else
                        # check if number is int
                        if($k && is_string($value))

                            # Append in pre command
                            $preCommand .= 'set '.$k.'="'.str_replace('"', '\\"', $value).'" | ';

            }else

                throw new CrazyException("Windows terminal used unknown, use power shell instead.");


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
     * @return
     */
    public static function down(string $loadEnvFile = self::ENV_FILE) {

        # Set result
        $result = "";

        # Set pre command
        $preCommand = "";

        # Check os
        if(Os::isWindows()){

            # Check power shell
            if(Terminal::isWindowsPowerShell()){

                # Set pwd
                $preCommand .= '$env:PWD = Get-Location;';

                # Set env
                $envFileContent = parse_ini_file(File::path($loadEnvFile));

                # Check env file
                if(!empty($envFileContent)) 

                    # Iteration
                    foreach($envFileContent as $k => $value)

                        # Check if is string
                        if($k && is_numeric($value))

                            # Append in pre command
                            $preCommand .= ' $env:'."$k = \"".str_replace('"', '\\"', $value).'"; ';

                        else
                        # check if number is int
                        if($k && is_string($value))

                            # Append in pre command
                 $preCommand .= ' $env:'."$k = \"".str_replace('"', '\\"', $value).'"; ';

            }else
            # check if class terminal
            if(Terminal::isWindowsCommandPrompt()){

                # Set pwd
                $preCommand .= "set PWD=%CD% | ";

                # Set env
                $envFileContent = parse_ini_file(File::path($loadEnvFile));

                # Check env file
                if(!empty($envFileContent)) 

                    # Iteration
                    foreach($envFileContent as $k => $value)

                        # Check if is string
                        if($k && is_numeric($value))

                            # Append in pre command
                            $preCommand .= "set $k=$value | ";

                        else
                        # check if number is int
                        if($k && is_string($value))

                            # Append in pre command
                            $preCommand .= 'set '.$k.'="'.str_replace('"', '\\"', $value).'" | ';

            }else

                throw new CrazyException("Windows terminal used unknown, use power shell instead.");


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
     * @param bool $detach Run container in background and print container ID
     * @return
     */
    public static function run(string $argument = "", string $loadEnvFile = self::ENV_FILE) {

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

        # Declare result
        $result = $source;

        # Open docker compose
        $dockerCompose = Yaml::open(File::path(self::DOCKER_COMPOSE_PATH));

        # Read services.webserver.ports
        $collection = Arrays::parseKey("services.webserver.ports", $dockerCompose);

        # Check collection
        if(empty($collection))
            
            # New error
            throw new CrazyException(
                "No ports defined in docker compose for key \"services.webserver.ports\"...", 
                500,
                [
                    "custom_code"   =>  "Docker-001",
                ]
            );

        # Iteration of collection
        foreach($collection as $port){

            # Split port
            $currentSource = explode(":", $port);

            # Check last value is equal to source you are looking for
            if(intval($currentSource[1]) !== $source)

                # Continue
                continue;

            # Update result
            # $result = intval($currentSource[0]);
            $result = intval($currentSource[1]);

        }

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /**
     * Docker Structure path 
     */
    public const STRUCTURE_PATH = "@crazyphp_root/resources/Docker/Structure.yml";

    /** @const string DOCKER_COMPOSE_PATH */
    public const DOCKER_COMPOSE_PATH = "@app_root/docker-compose.yml";

    /** @const string DOCKER_COMPOSE_COMMAND */
    public const DOCKER_COMPOSE_COMMAND = "docker-compose";

    /** @const string DOCKER_COMPOSE_COMMAND */
    public const DOCKER_COMMAND = "docker";

    /** @const array NAME_TO_SERVICE Name to services */
    public const NAME_TO_SERVICE = [
        "http"      =>  "webserver",
        "php"       =>  "php-fpm",
        "node"      =>  "node",
        "mongo"     =>  "mongo",
        "mysql"     =>  "mysql",
        "mariadb"   =>  "mariadb",
        "postgresql"=>  "postgresql",
    ];

    /** @const array DATABASE_TO_SERVICE */
    public const DATABASE_TO_SERVICE = [
        "mongodb"   =>  "mongo",
        "mysql"     =>  "mysql",
        "mariadb"   =>  "mariadb",
        "postgresql"=>  "postgresql",
    ];

    /** @const string ENV_FILE */
    public const ENV_FILE = "./docker/variables.env";

}