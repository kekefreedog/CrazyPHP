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
use CrazyPHP\Model\Docker\Down as DockerDown;
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Interface\CrazyCommand;
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
         * Start
         * 1. Start docker compose
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