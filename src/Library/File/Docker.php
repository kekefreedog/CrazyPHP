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
 * @copyright  2022-2023 Kévin Zarshenas
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

/**
 * Docker
 *
 * Methods for interacting with Docker File
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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
    public static function up(bool $detach = true) {

        # Set result
        $result = "";

        # Prepare command shell
        $command = self::DOCKER_COMPOSE_COMMAND." up".($detach ? " -d --no-color" : "");

        # Exec command
        $result = Command::exec($command);

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
    public static function down() {

        # Set result
        $result = "";

        # Prepare command shell
        $command = self::DOCKER_COMPOSE_COMMAND." down";

        # Exec command
        exec($command, $empty, $result);

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
        $result = null;

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
            $result = intval($currentSource[0]);

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

}