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
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\System\Os;

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
            "select"        =>  "CrazyPHP\Library\Extension\Extension::getAllAvailable",
            "multiple"      =>  true
        ],
        # Symlink
        [
            "name"          =>  "symlink",
            "description"   =>  "Symlinks for script files",
            "type"          =>  "BOOL",
            "required"      =>  true,
            "default"       =>  false,
            "select"        =>  [
                false   =>  "False",
                true    =>  "True"
            ],
            "process"   =>  ["bool"]
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
        "toInstall" =>  [],
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
         * Run Check Os
         * - Check OS and symlink compatibility
         */
        $this->runCheckOs();

        /**
         * Run Get Extension
         * - Search extension
         * - Load properties of the extension
         */
        $this->runGetExtension();

        /**
         * Run Check Potential Conflict
         * - Copy php script into the crazy app
         */
        $this->runCheckPotentialConflict();

        /**
         * Run Install Dependances
         * - Install composer dependances
         */
        $this->runInstallDependances();

        /**
         * Run Install Scripts
         * - Copy php script into the crazy app
         */
        $this->runInstallScripts();

        /**
         * Run Append Extension Into Config
         * - Add extension property into my config
         */
        $this->runAppendExtensionIntoConfig();

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
     * Run Check Os
     * 
     * Check OS and symlink compatibility
     * 
     * @return self
     */
    public function runCheckOs():self {

        # Check symlink
        $inputSymlink = Arrays::filterByKey($this->inputs["extension"], "name", "symlink");

        # Get values of names
        $symlink = $inputSymlink[array_key_first($inputSymlink)]["value"] ?? [];

        # Check symlink and is windows
        if($symlink && Os::isWindows())

            # Extension already installed
            throw new CrazyException(
                "Symlink with Windows OS isn't supported yet.",
                500,
                [
                    "custom_code"   =>  "extension-create-001"
                ]
            );

        # Return instance
        return $this;

    }

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
            $currentExtension = Extension::getAvailableByName($name);

            # Check current extension
            if($currentExtension === null)

                # Continue
                continue;

            # Push current extension in data
            $this->data["toInstall"] = $currentExtension;

        }

        # Return instance
        return $this;

    }

    /**
     * Run Check Potential Conflict
     * 
     * Install php script
     * 
     * @return self
     */
    public function runCheckPotentialConflict():self {

        # Set conflict
        $conflicts = [];

        # Get extensions installed
        $extensionInstalled = FileConfig::getValue('Extension.installed') ?: [];

        # Check extension to install
        if(!empty($this->data["toInstall"]))

            # Iteration extensions to install
            foreach($this->data["toInstall"] as $extensionName => $extension)

                # Check extension not officially installed
                if(!array_key_exists($extensionName, $extensionInstalled)){

                    # Check scripts
                    if(isset($extension['scripts']) && !empty($extension['scripts']))

                        # Iteration of scripts
                        foreach($extension['scripts'] as $script)

                            # Check destination
                            if(isset($script["destination"]) && File::exists($script["destination"]))

                                # Push file in conflict
                                $conflicts[] = [
                                    "extension" =>  $extensionName,
                                    "script"    =>  $script["destination"]
                                ];

                }else

                    # Extension already installed
                    throw new CrazyException(
                        "Extension $extensionName is already installed",
                        500,
                        [
                            "custom_code"   =>  "extension-create-002"
                        ]
                    );

        # Check conflict
        if(!empty($conflicts))

            # New error
            throw new CrazyException(
                "Script conflict detected",
                500,
                [
                    "custom_code"   =>  "extension-create-003"
                ]
            );

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

        # Check extension to install
        if(!empty($this->data["toInstall"]))

            # Iteration extensions to install
            foreach($this->data["toInstall"] as $extension)

                # Check scripts
                if(isset($extension['dependencies']) && !empty($extension['dependencies']))

                    # Iteration of scripts
                    foreach($extension['dependencies'] as $manager => $packages)

                        # Check package
                        if(!empty($packages))

                            # Iteration packages
                            foreach($packages as $package => $version)

                                # Check manager
                                if($manager === "composer"){

                                    # Append package
                                    Composer::requirePackageWithSpecificVersion(
                                        $package,
                                        $version,
                                        true,
                                        false
                                    );

                                    # Set manager true
                                    $this->data["managers"]["composer"] = true;

                                }



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

        # Check symlink
        $inputSymlink = Arrays::filterByKey($this->inputs["extension"], "name", "symlink");

        # Get values of names
        $symlink = $inputSymlink[array_key_first($inputSymlink)]["value"] ?? [];

        # Check extension to install
        if(!empty($this->data["toInstall"]))

            # Iteration extensions to install
            foreach($this->data["toInstall"] as $extension)

                # Check scripts
                if(isset($extension['scripts']) && !empty($extension['scripts']))

                    # Iteration of scripts
                    foreach($extension['scripts'] as $script){

                        # Check and get source
                        $source = isset($script["source"]) && File::exists($script["source"])
                            ? $script["source"]
                            : ""
                        ;
                        
                        # Get destination
                        $destination = isset($script["destination"])
                            ? $script["destination"] 
                            : ""
                        ;

                        # Check source and destination
                        if($source && $destination){

                            # Check symlink
                            if($symlink){
                                
                                # Process symlink
                                if(!File::symlink($source, $destination)){

                                    # New error
                                    throw new CrazyException(
                                        "Failed to create symlink of the file '".File::path($source)."'",
                                        500,
                                        [
                                            "custom_code"   =>  "extension-create-002"
                                        ]
                                    );

                                }

                            }else
                            # Start copy
                            if(!File::copy($source, $destination))

                                # New error
                                throw new CrazyException(
                                    "Failed to copy the file '".File::path($source)."'",
                                    500,
                                    [
                                        "custom_code"   =>  "extension-create-003"
                                    ]
                                );

                        }

                    }

        # Return instance
        return $this;

    }

    /**
     * Run Append Extension Into Config
     * 
     * Add extension property into my config
     */
    public function runAppendExtensionIntoConfig():self {

        # Check symlink
        $inputSymlink = Arrays::filterByKey($this->inputs["extension"], "name", "symlink");

        # Get values of names
        $symlink = $inputSymlink[array_key_first($inputSymlink)]["value"] ?? [];

        # Check extensions to installed
        if(isset($this->data["toInstall"]) && !empty($this->data["toInstall"])){

            # Iteration to install
            foreach($this->data["toInstall"] as &$extension)

                # Check symlink
                if($symlink)

                    # Set symlink into extension property
                    $extension["symlink"] = true;

            # Get extensions installed
            $extensionsInstalled = FileConfig::getValue("Extension.installed");

            # Merge with new extensions
            $extensionsInstalled = Arrays::mergeMultidimensionalArraysBis(true, $extensionsInstalled, $this->data["toInstall"]);

            # Push value into config
            FileConfig::setValue("Extension.installed", $extensionsInstalled);

        }

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