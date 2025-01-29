<?php declare(strict_types=1);
/**
 * Extension
 *
 * Classes utilities for extensions
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Extension;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;

/**
 * Extension
 *
 * Class for manage extension of crazy php
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Extension {

    /** Private parameters
     ******************************************************
     */

    /** @var array $properties */
    private array $_properties = [];

    /** @var array $scripts */
    private array $_scripts = [];

    /** @var array $dependencies */
    private array $_dependencies = [];

    /** @var array $dependencies */
    private string $_version = "";

    /** @var array $name */
    private string $_name = "";

    /** @var array $description */
    private string $_description = "";

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $properties Array of property
     * @return self
     */
    public function __construct(array $properties = []){

        # Get first key
        $key = array_key_first($properties);

        # Check key is equal to name
        if($key !== ($this->_properties["name"] ?? false))

            # Error
            throw new CrazyException(
                "Properties given isn't valid, check name please",
                500,
                [
                    "custom_code"   =>  "extension-001"
                ]
            );

        # Set properties
        $this->_properties = $properties[$key];

        # Set scripts
        $this->_scripts = $this->_properties["scripts"] ?? [];

        # Set dependencies
        $this->_dependencies = $this->_properties["dependencies"] ?? [];

        # Set version
        $this->_version = $this->_properties["version"] ?? "";

        # Set name
        $this->_name = $this->_properties["name"];

        # Set description
        $this->_description = $this->_properties["description"] ?? [];

    }

    /** Public methods | Get
     ******************************************************
     */

    /**
     * Get Scripts
     * 
     * Get Scripts to install
     */
    public function getScripts():array {

        # Set result
        $result = $this->_scripts;

        # Return result
        return $result;

    }

    /** Public methods | Get
     ******************************************************
     */

    /** Public static methods
     ******************************************************
     */

    /**
     * Get All Available
     * 
     * Get all extensions available
     * 
     * @param bool $onlyValue Return only value, else return key => value
     * @return array
     */
    public static function getAllAvailable(bool $onlyValue = false):array {

        # Set result
        $result = [];

        # New finder
        $finder = new Finder();

        # Search all extensions properties
        $files = $finder
            ->files()
            ->name(['*.yml', '*.yaml'])
            ->in(File::path(static::EXTENSION_DIRECTORY))
            ->depth('== 1')
        ;

        # Check has result
        if($finder->hasResults())

            # Iteration of files
            foreach($files as $file){

                # Open file
                $fileContent = File::open($file->getRealPath());

                # Get properties
                $properties = $fileContent[array_key_first($fileContent)];

                # Get name
                $name = $properties["name"];

                # Check only value
                if($onlyValue)

                    # Push only value in result
                    $result[] = $name;

                # Else
                else
                
                    # Push name as key and value in result
                    $result[$name] = $name;

            }

        # Return result
        return $result;

    }

    /**
     * Get Available By Name
     * 
     * @param string $name
     * @return array|null
     */
    public static function getAvailableByName(string $name):array|null {

        # Set result
        $result = null;

        # Check name
        if($name){

            # New finder
            $finder = new Finder();

            # Search all extensions properties
            $files = $finder
                ->files()
                ->name(["*.yml", "*.yaml"])
                ->in(File::path(static::EXTENSION_DIRECTORY)."/".$name)
                ->depth('== 0')
            ;

            # Check has result
            if($finder->hasResults())

                # Iterations of files
                foreach($files as $file){

                    # Set result
                    $result = File::open($file->getRealPath());

                    # Break
                    break;

                }

        }

        # Return result
        return $result;

    }

    /**
     * Get Installed By Name
     * 
     * @param string $name
     * @return array|null
     */
    public static function getInstalledByName(string $name):array|null {

        # Set result
        $result = null;

        # Check name
        if($name)

            # Get from config
            $result = Config::getValue("Extension.installed.$name");

        # check result is null
        is_array($result) && empty($result) && ($result = null);

        # Return result
        return $result;

    }

    /**
     * Get All Installed
     * 
     * Get all extensions available
     * 
     * @param bool $onlyValue Return only value, else return key => value
     * @return array
     */
    public static function getAllInstalled(bool $onlyValue = false):array {

        # Set result
        $result = [];

        # New finder
        $extensionsInstalled = Config::getValue("Extension.installed");

        # Check extensions installed
        if(!empty($extensionsInstalled))

            # Iteration of files
            foreach(array_keys($extensionsInstalled) as $extensionName)

                # Check only value
                if($onlyValue)

                    # Push only value in result
                    $result[] = $extensionName;

                # Else
                else
                
                    # Push name as key and value in result
                    $result[$extensionName] = $extensionName;

        # Return result
        return $result;

    }

    /**
     * Get Scripts Installed
     * 
     * Get Scripts installed
     */
    public function getScriptsInstalled():array {

        # Set result
        $result = [];

        # Get extension config
        $config = Config::get("Extension");

        # Check extension cofig
        if(array_key_exists("Extension", $config))

            # Get scripts installed
            $result = $config["Extension"]["installed"] ?? [];

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @var string EXTENSION_DIRECTORY */
    public const EXTENSION_DIRECTORY = "@crazyphp_root/resources/Extensions";


}