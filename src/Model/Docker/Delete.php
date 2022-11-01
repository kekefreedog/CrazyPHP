<?php declare(strict_types=1);
/**
 * Docker Compose
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Docker;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Model\Docker\Down as DockerDown;
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Config;

/**
 * Delete docker compose
 *
 * Classe for run step by step docker compose
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Delete implements CrazyCommand {

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Ingest data
        $this->inputs = $inputs;

    }

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [];

    /** Public static methods
     ******************************************************
     */

    /**
     * Get Required Values
     * 
     * Return required values
     * 
     * @return array
     */
    public static function getRequiredValues():array {

        # Set result
        $result = self::REQUIRED_VALUES;

        # Return result
        return $result;

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Get story line
     * 
     * Used for execute each method one after another
     * 
     * @return array
     */
    public function getStoryline():array {

        # Declare result
        $result = [];

        # New reflection
        $reflection = new \ReflectionClass($this);

        # Get methods
        $methods = $reflection->getMethods();

        # Check methods
        if($methods)

            # Iteration of methods
            foreach($methods as $method)

                # Check run children methods
                if(
                    substr($method->name, 0, 3) == "run" && 
                    strlen($method->name) > 3
                )

                    # Push result in result
                    $result[] = $method->name;

        # Return result
        return $result;

    }

    /**
     * Run
     * 
     * Run current command
     *
     * @return self
     */
    public function run():self {

        /**
         * Down Docker Compose 
         * 1. Stops docker composer container
         */
        $this->runDockerComposeDown();

        /**
         * Remove docker-compose container
         * 1. Remove docker compose
         */
        $this->runDockeComposeRemove();

        /**
         * Run Update Database Config
         * 1. Unset docker service name in config of databases
         */
        $this->runUpdateDatabaseConfig();

        /**
         * Remove structure folder
         * 1. Remove folder and file
         */
        $this->runStructureFolder();

        # Return current instance
        return $this;

    }

    /**
     * Run Docker Compose Down
     * 
     * Steps:
     * 1. Stops docker composer container
     * 
     * @return self
     */
    public function runDockerComposeDown():self {

        # New docker down instance
        $instance = new DockerDown();

        # Run docker down
        $instance->run();

        # Return self
        return $this;

    }

    /**
     * Run Docker Compose Remove
     * 
     * Remove Dcoker Containers
     * 
     * @return self
     */
    public function runDockeComposeRemove():self {
        
        # Command
        $command = "docker-compose rm -f -s -v";

        # Exec command
        Command::exec($command);

        # Return instance
        return $this;

    }

    /**
     * Run Update Database Config
     * 
     * Unset docker service name in config of databases
     * 
     * @return self
     */
    public function runUpdateDatabaseConfig():self {

        # Get database config
        $databaseConfig = FileConfig::getValue("Database.collection");

        # Iteration config of databse
        foreach($databaseConfig as $name => $config)

            # If name is in parameters
            if(array_key_exists($name, Docker::DATABASE_TO_SERVICE) && $databaseConfig[$name]){

                # Set config
                FileConfig::removeValue("Database.collection.$name.docker");

            }

        # Return instance
        return $this;

    }

    /**
     * Run Structure Folder
     * 
     * Steps : 
     * 1. Delete structure folder
     * 
     * @return self
     */
    public function runStructureFolder():self {

        # Get path of structure
        $structurePath = File::path(Docker::STRUCTURE_PATH);

        # Check docker config
        if(Config::exists("Docker")){

            # New finder
            $finder = new Finder();

            # Prepare finder
            $finder->files()->name(["Docker", "Docker.*"])->in(File::path(Config::DEFAULT_PATH));

            # Check if has result
            if($finder->hasResults())

                # Iteration of files
                foreach ($finder as $file)

                    # Unlink file
                    unlink($file->getRealPath());


        }

        # Run creation of docker structure
        Structure::remove($structurePath);
        Structure::remove($structurePath);

        # Return instance
        return $this;

    }

}