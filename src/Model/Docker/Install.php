<?php declare(strict_types=1);
/**
 * New application
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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\System\Os;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Create new Application
 *
 * Classe for create step by step new application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Install implements CrazyCommand {

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
         * Run Structure Folder
         * 1. Prepare folder structure
         */
        $this->runStructureFolder();

        # Return instance
        return $this;

    }

    /**
     * Run Structure Folder
     * 
     * Prepare folder structure
     * 
     * @return self
     */
    public function runStructureFolder():self {

        # Get path of structure
        $structurePath = File::path(Docker::STRUCTURE_PATH);

        # Get data for render
        $data = self::_getData();

        # Run creation of docker structure
        Structure::create($structurePath, $data);

        # Return instance
        return $this;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Get data
     * 
     * Get all data needed for template engine
     * 
     * @return array
     */
    private function _getData():array {

        # Set result
        $result = [];

        # Set config
        $config = Config::get([
            "App", "Database"
        ]);

        # Push config in result
        $result['_config'] = $config;

        # Check if windows
        if(Os::isWindows()){

            # Update _config.App.root
            $value = $config['_config']['App']['root'];
            
            # Change \ or \\ by /
            $value = str_replace(["\\", "\\\\"], "/", $value);

            # Split by : of disk
            $explodedValue = explode(":", $value, 2);

            # Set new value
            $value = "/mnt/".strtolower($explodedValue[0]).$explodedValue[1];

            # Set $value = _config.App.root
            $result['_config']['App']['root'] = $value;

        }

        # Return result
        return $result;

    }

}