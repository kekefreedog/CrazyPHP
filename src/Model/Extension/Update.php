<?php declare(strict_types=1);
/**
 * Extension
 *
 * Manage crazy php extension
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Extension;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;

/**
 * Update Extension
 *
 * Class for update step by step new extension
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Update extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    /** @var array REQUIRED_VALUES */
    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy extension",
            "type"          =>  "ARRAY",
            "required"      =>  true,
            "select"        =>  "CrazyPHP\Library\Extension\Extension::getAllAvailable"
        ],
    ];

    /** Private Parameters
     ******************************************************
     */

    /**
     * Inputs
     */
    private $inputs = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return self
     */
    public function __construct(array $inputs = []){

        # Ingest data
        $this->inputs = $inputs;

    }

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
     * Run creation of project
     *
     * @return self
     */
    public function run():self {

        /**
         * Run Backup Scripts
         * - Copy php script into the crazy app backup folder
         */
        $this->runBackupScripts();

        /**
         * Run Install Scripts
         * - Copy php script into the crazy app
         */
        $this->runUpdateScripts();

        /**
         * Run Install Dependances
         * - Install composer dependances
         */
        $this->runUpdateDependances();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Backup Scripts
     * 
     * Remove backup of php script
     * 
     * @return self
     */
    public function runBackupScripts():self {
        
        # Return instance
        return $this;

    }

    /**
     * Run Remove Scripts
     * 
     * Remove php script
     * 
     * @return self
     */
    public function runUpdateScripts():self {
        
        # Return instance
        return $this;

    }

    /**
     * Run Remove Dependances
     * 
     * Uninstall php dependances
     * 
     * @return self
     */
    public function runUpdateDependances():self {
        
        # Return instance
        return $this;

    }

}