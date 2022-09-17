<?php declare(strict_types=1);
/**
 * Run Docker Compose
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Docker;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;

/**
 * Run docker compose
 *
 * Classe for run step by step docker compose
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Run implements CrazyCommand {

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
         * Check
         * 1. Check command docker-compose
         * 2. Check docker-compose.yml file
         */
        $this->runCheck();

        /**
         * Start
         * 1. Start docker compose
         */
        $this->runStart();

        # Return current instance
        return $this;

    }

    /**
     * Check
     * 
     * Check docker compose command
     * 
     * @return self
     */
    public function runCheck():self {

        # Check command
        if(!Command::exists(Docker::DOCKER_COMPOSE_COMMAND))
            
            # New error
            throw new CrazyException(
                "\"".Docker::DOCKER_COMPOSE_COMMAND."\" isn't available in your shell", 
                500,
                [
                    "custom_code"   =>  "Run-001",
                ]
            );

        # Check docker compse exists
        if(!File::exists(Docker::DOCKER_COMPOSE_PATH))
            
            # New error
            throw new CrazyException(
                "\"docker-compose.yml\" doesn't exist, please install CrazyDocker first",
                500,
                [
                    "custom_code"   =>  "Run-003",
                ]
            );

        # Return self
        return $this;

    }

    /**
     * Start
     * 
     * Start Docker Compose
     * 
     * @return self
     */
    public function runStart():self {

        # Run docker compose
        $result = Docker::up();

        # Check result
        if($result > 0)
            
            # New error
            throw new CrazyException(
                "Docker compose launch failed",
                500,
                [
                    "custom_code"   =>  "Run-002",
                ]
            );
        
        # Return self
        return $this;

    }

}