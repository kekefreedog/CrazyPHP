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
 * Create Extension
 *
 * Class for create step by step new extension
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Create extends CrazyModel implements CrazyCommand {

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
         * Run Check Potential Conflict
         * - Copy php script into the crazy app
         */
        $this->runCheckPotentialConflict();

        /**
         * Run Install Scripts
         * - Copy php script into the crazy app
         */
        $this->runInstallScripts();

        /**
         * Run Install Dependances
         * - Install composer dependances
         */
        $this->runInstallDependances();

        /**
         * Run Update Composer
         * - Update composer dependances
         */
        $this->runUpdateComposer();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Check Potential Conflict
     * 
     * Install php script
     * 
     * @return self
     */
    public function runCheckPotentialConflict():self {

        # Return instance
        return $this;

    }

    /**
     * Run Install Scripts
     * 
     * Install php script
     * 
     * @return self
     */
    public function runInstallScripts():self {

        # Return instance
        return $this;

    }

    /**
     * Run Install Dependances
     * 
     * Install php dependances
     * 
     * @return self
     */
    public function runInstallDependances():self {

        # Return instance
        return $this;

    }

    /**
     * Run Update Dependances
     * 
     * Update composer dependances
     * 
     * @return self
     */
    public function runUpdateComposer():self {

        # Return instance
        return $this;

    }

}