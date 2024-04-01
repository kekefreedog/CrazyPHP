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
use CrazyPHP\Library\Extension\Extension;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Array\Arrays;

/**
 * Delete Extension
 *
 * Class for delete step by step extension
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

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
            "select"        =>  "CrazyPHP\Library\Extension\Extension::getAllInstalled",
            "multiple"      =>  true
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
     * Data
     */
    private $data = [
        "toRemove" =>  [],
        "managers"  =>  [
            "composer"  =>  false
        ]
    ];

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
         * Run Get Extension
         * - Search extension
         * - Load properties of the extension
         */
        $this->runGetExtension();

        /**
         * Run Remove Extension From Config
         * - Remove Extension From Config
         */
        $this->runRemoveExtensionFromConfig();

        /**
         * Run Backup Scripts
         * - Copy php script into the crazy app backup folder
         */
        $this->runBackupScripts();

        /**
         * Run Install Scripts
         * - Copy php script into the crazy app
         */
        $this->runRemoveScripts();

        /**
         * Run Install Dependances
         * - Install composer dependances
         */
        $this->runRemoveDependances();

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
     * Run Get Extension
     * 
     * Search extension & load properties of the extension
     * 
     * @return self
     */
    public function runGetExtension():self {

        # Check name
        $inputName = Arrays::filterByKey($this->inputs["extension"], "name", "name");

        # Get values of names
        $names = $inputName[array_key_first($inputName)]["value"] ?? [];

        # Iterations of names
        foreach($names as $name){

            # Load available extension by name
            $currentExtension = Extension::getInstalledByName($name);

            # Check current extension
            if($currentExtension === null)

                # Continue
                continue;

            # Push current extension in data
            $this->data["toRemove"][$name] = $currentExtension;

        }

        # Return instance
        return $this;

    }

    /**
     * Run Remove Extension From Config
     * 
     * Remove Extension From Config
     * 
     * @return self
     */
    public function runRemoveExtensionFromConfig():self {

        print_r($this->data);

        # Return instance
        return $this;

    }

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
    public function runRemoveScripts():self {
        
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
    public function runRemoveDependances():self {
        
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

        # Check managers composer
        if($this->data["managers"]["composer"] === true){

            # Composer Updatex
            Composer::exec("update", "", false);

        }

        # Return instance
        return $this;

    }

}