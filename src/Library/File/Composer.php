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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\App\Create;
use CrazyPHP\Model\Config;

/**
 * Composer
 *
 * Methods for interacting with Composer files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Composer {

    /** Constants
     ******************************************************
     */

    # Path of composer
    public const PATH = [
        "composer.json" =>  "@app_root/composer.json",
        "composer.lock" =>  "@app_root/composer.lock",
    ];

    # Default properties of composer
    public const DEFAULT_PROPERTIES = [
        # Name
        "name"          =>  Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  Create::REQUIRED_VALUES[1],
        # Version
        "version"       =>  [
            "name"          =>  "Version",
            "description"   =>  "Version of your crazy project",
            "type"          =>  "VARCHAR",
        ],
        # Type
        "type"          =>  Create::REQUIRED_VALUES[4],
        # Keywords
        "keywords"      =>  [
            "name"          =>  "Keywords",
            "description"   =>  "Keywords about your app",
            "type"          =>  "ARRAY",
        ],
        # Homepage
        "homepage"      =>  Create::REQUIRED_VALUES[5],
        # Readme
        "readme"        =>  [
            "name"          =>  "Read Me",
            "description"   =>  "Path of the read me",
            "type"          =>  "VARCHAR",
        ],
        # Time
        "time"          =>  [
            "name"          =>  "Read Me",
            "description"   =>  "Path of the read me",
            "type"          =>  "DATETIME",
        ],
        # Licence
        "license"       =>  [
            "name"          =>  "Licence",
            "description"   =>  "Licence of your app",
            "type"          =>  "VARCHAR",
        ],
        # Author name
        "authors__name" =>  Create::REQUIRED_VALUES[2],
        # Author name
        "authors__email"=>  Create::REQUIRED_VALUES[3],
        # Authors
        "authors"       =>  [
            "name"          =>  "authors",
            "description"   =>  "Authors of your app",
            "type"          =>  "ARRAY",
        ],
        # Support
        "support"       =>  [
            "name"          =>  "Support",
            "description"   =>  "Support information of your app",
            "type"          =>  "ARRAY",
        ],
        # Funding
        "funding"       =>  [
            "name"          =>  "Funding",
            "description"   =>  "Funding information of your app",
            "type"          =>  "ARRAY",
        ],
        # Autoload
        "autoload_psr-4_App\\_0" => [
            "name"          =>  "autoload_psr-4_App\\_0",
            "description"   =>  "Autoload following PSR-4 rules.",
            "type"          =>  "VARCHAR",
            "default"       =>  "app\/",
            "required"      =>  true
        ]
    ];

    # Default value
    const DEFAULT_VALUE = [
        "require"   =>  [
            "kzarshenas/crazyphp"   =>  "@dev"
        ]
    ];

    /* @const array COMMAND_SUPPORTED supported command */
    public const COMMAND_SUPPORTED = [
        "install"   =>  [
            "command"   =>  "i"
        ],
        "update"   =>  [
            "command"   =>  "u"
        ],
        "require"   =>  [
            "command"   =>  "r"
        ],
        "remove"    =>  [
            "command"   =>  "remove"
        ],
        "search"    =>  [
            "command"   =>  "search"
        ],
    ];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Read value in composer.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function create(string $path):array{

        # Declare result
        $result = [];

        # Get reel path
        $path = self::_readPath($path);

        # Check if file already exists
        if(!file_exists($path))

            # Get collection of file
            $result = Json::create($path, self::DEFAULT_VALUE);
        
        # Return result
        return $result;

    }

    /**
     * Read value in composer.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function read(string $parameter = "", string $file = "composer.json") {

        # Set result
        $result = "";

        # Get reel path
        $file = self::_readPath($file);

        # Get collection of file
        $fileCollection = Json::open($file);

        # Check value exist in collection
        if($fileCollection[$parameter] ?? false)

            # Set result
            $result = $fileCollection[$parameter];

        # Return result
        return $result;

    }
    
    /**
     * Set value in composer.json
     * 
     * Set value in composer.json from array :
     * 1. {parameter:"value",...}
     *
     * @param array $values Values to put on composer.json
     * @param string $file File composer.json
     * @return array
     */
    public static function set(array $values = [], string $file = "composer.json"):array {

        # Set result
        $result = [];

        # Get reel path
        $file = self::_readPath($file);

        # Process value
        self::process($values);

        # Set values in composer.json
        $result = Json::set($file, $values, true);

        # Return result
        return $result;

    }

    /**
     * Set Value
     * 
     * Set value in composer.json
     * 
     * @param string $key Parameter of config to set
     * @param mixed $data Content to set
     * @param bool $createIfNotExists Create parameter if not exists
     * @param string $path Path of the package.json
     * @return void
     */
    public static function setValue(string $key = "", mixed $data = [], bool $createIfNotExists = true, string $path = self::PATH["composer.json"]):void {

        # Parse key
        $key = str_replace(FileConfig::SEPARATOR, "___", $key);

        # Explode keys 
        $keys = explode("___", $key);

        # Check config file
        if(!$path || empty($keys))

            # Stop script
            return;

        # Check not multiple file
        if(!File::exists($path))

            # New Exception
            throw new CrazyException(
                "No config file found for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "package-001",
                ]
            );

        # Open File
        $fileData = Json::open($path);

        # Check if is array
        if(!is_array($fileData))

            # New Exception
            throw new CrazyException(
                "package.json isn't valid... Array awaited !", 
                500,
                [
                    "custom_code"   =>  "package-011",
                ]
            );

        

        # Declare cursor
        $cursor = &$fileData;

        # Iteration filedata
        $i=0;while(isset($keys[$i])){

            # Check cursor.key isset
            if(!isset($cursor[$keys[$i]]))

                # Check if key should be create
                if($createIfNotExists)

                    # Create key
                    $cursor[$keys[$i]] = [];

                # Else
                else

                    # Exit
                    return;

            # Update the cursor
            $cursor = &$cursor[$keys[$i]];

        $i++;}

        # Set value in cursor
        $cursor = $data;

        # Set last resultCursor
        $result = $fileData;

        # Set value
        Json::set($path, $result, true);

    }

    /**
     * Open
     * 
     * Open and return content of composer.json
     *
     * @param string $file File composer.json
     * @return array
     */
    public static function open(string $file = "composer.json"):array {

        # Declare result
        $result = [];

        # Get reel path
        $file = self::_readPath($file);

        # Get collection of file
        $result = Json::open($file);

        # Return result
        return $result;

    }

    /**
     * Get value in composer.json
     * 
     * Get value in composer
     *
     * @param string $input Input to search in composer.json
     * @param string $file File composer.json
     * @return
     */
    public static function get(string $input = "", string $file = "composer.json"){

        # Declare result
        $result = null;

        # Declare explodedInput
        $explodedInput = [];

        # Check input
        if(!$input)

            # Return
            return $result;

        # List delimiter
        $delimiters = ["/", "."];

        # Iteration of delimiter
        foreach($delimiters as $delimiter)

            # Check if input has delimiter
            if(strpos($input, $delimiter) !== false)

                # Explode
                $explodedInput = explode($delimiter, $input);

        # Check explodeInput
        if(empty($explodedInput))

            # Set explodedInput
            $explodedInput = [$input];

        # Get reel path
        $file = self::_readPath($file);

        # Get collection of file
        $fileCollection = Json::open($file);

        # Declare resultWip
        $resultWip = $fileCollection;

        # Search input in composer
        foreach($explodedInput as $parameter)

            # Check value in array
            if(isset($resultWip[$parameter]))

                # Update value
                $resultWip = $resultWip[$parameter];

            else

                # Return result
                return $result;

        # Update result
        $result = $resultWip;

        # Return result
        return $result;

    }
    
    /**
     * Read value in composer.json
     *
     * @param array $values Values to update on composer.json
     * @param string $createIfNotExists create parameter if doesn't exists
     * @param string $file File composer.json
     * @return array
     */
    public static function update(array $values = [], bool $createIfNotExists = false, string $file = "composer.json"):array{

        # Set result
        $result = true;

        # Process value
        self::process($values);

        # Get reel path
        $file = self::_readPath($file);

        # Set values in composer.json
        $result = Json::set($file, $values);

        # Return result
        return $result;

    }
    
    /**
     * Delete value in composer.json
     *
     * @param string  $values Values to update on composer.json
     * @return string
     */
    public static function delete(array $values = []):bool{

        # Set result
        $result = true;

        # Return result
        return $result;

    }

    /**
     * Remove value
     * 
     * Set Value in composer file
     * 
     * @param string $key Parameter of config to set
     * @param string $path Path of the config
     * @return void
     */
    public static function removeValue(string $key = "", string $file = "composer.json"):void {

        # Check if file exists into PATH
        if(array_key_exists($file, self::PATH))

            # Set file
            $file = self::PATH[$file];

        # Get mime type
        $fileData = Json::open($file);

        # Parse key
        $key = str_replace(self::SEPARATOR, "___", $key);

        # Explode keys 
        $keys = explode("___", $key);

        # Check config file
        if(!$file || empty($keys))

            # Stop script
            return;

        # Check if is array
        if(!is_array($fileData))

            # New Exception
            throw new CrazyException(
                "Config \"".$keys[0]."\" isn't valid... Array waited !", 
                500,
                [
                    "custom_code"   =>  "composer-011",
                ]
            );

        # Declare cursor
        $cursor = &$fileData;
        $parentCursor = null;

        # Iteration filedata
        $i=0;while(isset($keys[$i])){

            # Check cursor.key isset
            if(!isset($cursor[$keys[$i]]))

                    # Exit
                    return;

            # Check if the last cursor
            $j = $i+1;
            if(!isset($keys[$j]))

                # Parent Cursor 
                $parentCursor = &$cursor;

            else

                # Update the cursor
                $cursor = &$cursor[$keys[$i]];

        $i++;}

        # Check parent cursor
        if($parentCursor){

            # Last valid key
            $i--;

            # Unset value
            unset($parentCursor[$keys[$i]]);

        }

        # Set last resultCursor
        $result = $fileData;

        # Set result
        Json::set($file, $result, false, false);

    }

    /**
     * Process value
     * 
     * Process value for composer.json
     *
     * @param array $inputs Values to process for composer.json
     * @return void
     */
    public static function process(array &$inputs = []):void{

        # Check name
        if(isset($inputs["name"]) && isset($inputs["authors"][0]["name"]))

            # Clean name
            $inputs["name"] = 
                Process::clean($inputs["authors"][0]["name"]).
                "/".
                Process::clean($inputs["name"])
            ;

    }

    /**
     * Exec
     * 
     * Execute command
     * 
     * @param string $commandName Command name to execute
     * @param string $argument Argument for the command
     * @param string $checkError Check error of exec
     * @return
     */
    public static function exec(string $commandName = "", string $argument = "", bool $checkError = true) {

        # Result
        $result = null;

        # Check command
        if(!$commandName || !array_key_exists($commandName, self::COMMAND_SUPPORTED))
                
            # New error
            throw new CrazyException(
                "Command \"$commandName\” isn't supported with Composer", 
                500,
                [
                    "custom_code"   =>  "composer-002",
                ]
            );

        # Check docker config
        if(Config::exists("Docker") && FileConfig::has("Docker.services.php.Name") && $dockerServiceName = FileConfig::getValue("Docker.services.php.Name"))

                # Prepare docker
                $dockerCommand = "docker exec -it $dockerServiceName ";

        # Else
        else

            # Empty docker command
            $dockerCommand = "";

        # Peepare command
        $argument = self::COMMAND_SUPPORTED[$commandName]["command"].($argument ? " $argument" : "");

        # Get result of exec
        $result = Command::exec($dockerCommand."composer", $argument);

        # Check result
        if($checkError && ($result["result_code"] !== null || $result["result_code"] > 0))
            
            # New error
            throw new CrazyException(
                "Composer ".$argument." failed".(is_array($result["output"]) ? " : ".json_encode($result["output"]) : ""),
                500,
                [
                    "custom_code"   =>  "composer-003",
                ]
            );

        return $result;

    }

    /**
     * Require
     * 
     * Add requiere vendor in composer
     * 
     * @param string $package Package to add in composer
     * @param bool $checkPackage Check package exits
     * @param bool $updateComposer Update composer
     * @param string $file Composer file
     * @return void
     */
    public static function requirePackage(string $package = "", bool $checkPackage = true, bool $updateComposer = true, string $file = "composer.json"):void {

        # Check package name
        if(!$package)
                    
            # New error
            throw new CrazyException(
                "Composer package name \"$package\” you want require looks strange, please respect \"vendor/package\" format !", 
                500,
                [
                    "custom_code"   =>  "composer-004",
                ]
            );
        
        # Check chack package
        if($checkPackage && !self::checkPackageExists($package))
                    
            # New error
            throw new CrazyException(
                "Package  \"$package\” doesn't exit on composer db.", 
                500,
                [
                    "custom_code"   =>  "composer-005",
                ]
            );

        # Array to merge
        $arrayToMerge = [
            "require"   =>  [
                $package    =>  "*"
            ]
        ];

        # Add package in json in composer.json
        self::set($arrayToMerge, $file);

        # Check update Composer
        if($updateComposer)

            # Composer Update
            Composer::exec("update", "", false);

    }

    /**
     * Require Package With Specific Version
     * 
     * Add requiere vendor in composer
     * 
     * @param string $package Package to add in composer
     * @param bool $checkPackage Check package exits
     * @param bool $updateComposer Update composer
     * @param string $file Composer file
     * @return void
     */
    public static function requirePackageWithSpecificVersion(string $package = "", string $version = "*", bool $checkPackage = true, bool $updateComposer = true, string $file = "composer.json"):void {

        # Check version
        if(!$version)

            # Set version
            $version = "*";

        # Check package name
        if(!$package)
                    
            # New error
            throw new CrazyException(
                "Composer package name \"$package\” you want require looks strange, please respect \"vendor/package\" format !", 
                500,
                [
                    "custom_code"   =>  "composer-004",
                ]
            );
        
        # Check chack package
        if($checkPackage && !self::checkPackageExists($package))
                    
            # New error
            throw new CrazyException(
                "Package  \"$package\” doesn't exit on composer db.", 
                500,
                [
                    "custom_code"   =>  "composer-005",
                ]
            );

        # Array to merge
        $arrayToMerge = [
            "require"   =>  [
                $package    =>  $version
            ]
        ];

        # Add package in json in composer.json
        self::set($arrayToMerge, $file);

        # Check update Composer
        if($updateComposer)

            # Composer Update
            Composer::exec("update", "", false);

    } 

    /**
     * Remove Package
     * 
     * Remove package in composer config file
     * 
     * @param string $package Package to remove in composer
     * @param bool $updateComposer Update composer
     * @param string $file Composer file
     * @return void
     */
    public static function removePackage(string|array $package = "", bool $updateComposer = true, string $file = "composer.json"):void {

        # Check package name
        if(!$package || empty($package))
                    
            # New error
            throw new CrazyException(
                "Composer package name \"".(is_string($package) ? $package : json_encode($package))."\” you want require looks strange, please respect \"vendor/package\" format !", 
                500,
                [
                    "custom_code"   =>  "composer-004",
                ]
            );

        # Array to merge
        $require = self::get("require", $file);

        # check package
        if(is_string($package))

            # Set package
            $package = [$package];

        # Iteration of package
        foreach($package as $v)

            # Check if in require
            if(array_key_exists($v, $require))

                # Remove package from require
                self::removeValue("require.$v", $file);

        # Check update Composer
        if($updateComposer)

            # Composer Update
            Composer::exec("update", "", false);

    }

    /**
     * Check Package Exists
     * 
     * Check package exists on composer
     * 
     * @param string $package Package name
     * @return bool
     */
    public static function checkPackageExists(string $package = ""){

        # Set result
        $result = false;

        # Check name
        if(!$package)

            # Return result
            return $result;

        # Search package
        $result = self::exec("search", "-N $package", false);

        # Check result
        if(isset($result["output"]) && $result["output"][0] == $package)

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Read Path
     * 
     * Read and get real path
     * 
     * @param string $path Path to process
     */
    private static function _readPath(string $path = ""):string {

        # Result
        $result = "";

        # Check path is not empty
        if(!$path)
                
            # New error
            throw new CrazyException(
                "Path of composer file is empty and not valid !", 
                500,
                [
                    "custom_code"   =>  "composer-001",
                ]
            );

        # Check path is in path constant
        if(array_key_exists($path, self::PATH))

            # Get long value
            $path = self::PATH[$path];

        # Check env variable in path
        $result = File::path($path);

        # Return result
        return $result;

    }

    /**
     * Get Dependencies
     * 
     * Get all composer dependencies with metadata
     *
     * @param string $path Path to composer.lock file
     * @return array
     */
    public static function getDependencies(string $path = self::PATH["composer.lock"]):array {

        # Set result
        $result = [];

        # Check file exits
        if(File::exists($path)){

            # New cache instance
            $cache = new Cache();

            # Get key
            $key = File::path(pathinfo($path, PATHINFO_FILENAME))."composerDepencies";

            # Set lastModifiedDate
            $lastModifiedDate = File::getLastModifiedDate($path);

            # Check cache is valid
            if(!$cache->hasUpToDate($key, $lastModifiedDate)){

                # Set dependencies
                $dependencies = [];

                # Get lock data
                $lockData = Json::open($path);

                # Combine "packages" and "packages-dev"
                $allPackages = array_merge(
                    $lockData['packages'] ?? [],
                    $lockData['packages-dev'] ?? []
                );

                # Iteration packages
                if(!empty($allPackages)) foreach($allPackages as $package)

                    # Push into result
                    $dependencies[] = [
                        'name'        => $package['name'] ?? '',
                        'version'     => $package['version'] ?? '',
                        'description' => $package['description'] ?? '',
                        'url'         => $package['homepage'] ?? $package['source']['url'] ?? '',
                        'license'     => $package['license'] ?? [],
                        'type'        => $package['type'] ?? '',
                        'authors'     => $package['authors'] ?? [], // Optional, often helpful
                        'source'      => $package['source'] ?? [],
                    ];

                # Set Cache
                $cache->set($key, $dependencies);

            } 

            # Get Cache
            $result = $cache->get($key, $result);

        }

        # Return result
        return $result;

    }

    /**
     * Detect Non Commercial Dependecies
     * 
     * Detect non-commercial or non-permissive licenses from dependency list
     *
     * @param string $path Path to composer.lock file
     * @return array
     */
    public static function detectNonCommercialDependecies(string $path = self::PATH["composer.lock"]):array {

        # Set result
        $result = [];

        # Get dependencies
        $dependencies = static::getDependencies($path);

        # Iteration dependencies
        if(!empty($dependencies)) foreach($dependencies as $dep)

            # Iteration licence
            foreach($dep['license'] ?? [] as $license)

                # Iteration non commerical licence
                foreach(static::NON_COMMERCIAL_LICENSE as $pattern)

                    # Check
                    if(stripos($license, $pattern) !== false) {

                            # Set flag
                            $result[] = $dep;

                            # Stop at first match per dependency
                            break 2; 
                        
                    }


        # Return result
        return $result;

    }
    
    /** Public constants
     ******************************************************
     */

    /** @const separator */
    public const SEPARATOR = [".", "___"];

    /**
     * NON_COMMERCIAL_LICENSE
     * Define license keywords considered restrictive or non-commercial 
     */
    public const NON_COMMERCIAL_LICENSE = [
        # Copyleft licenses
        'AGPL', 'GPL', 'LGPL', 'EPL',
        # Creative Commons Non-Commercial
        'CC-BY-NC', 'CC-BY-NC-SA',
        # Business Source / Proprietary
        'BUSL', 'Proprietary',
        # Generic mentions
        'Non-Commercial', 'NonCommercial',
    ];

}