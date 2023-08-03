<?php declare(strict_types=1);
/**
 * Model
 *
 * Classe for define framework models
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Model\Env;

/**
 * Config
 *
 * Methods for interacting with config file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Config{

    /** Public constants
     ******************************************************
     */
    
    /** @const DEFAULT_PATH PATH by default where stored config files */
    public const DEFAULT_PATH = "@app_root/config";

    /** @const string REGEX */
    public const REGEX = "{{(.*?)}}";

    /** @const string PREFIX  */
    public const PREFIX = "__CRAZY_CONFIG";

    /**
     * Default Config files
     */
    public const DEFAULT_CONFIG_FILES = [
        "App"       =>  [
            "path_source"   =>  "@app_root"."/composer.json",
            "path_target"   =>  self::DEFAULT_PATH."/App.yml",
            "action_set"    =>  "_setAppConfig",
        ],
        /* "Template"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Template.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Template.yml",
        ],
        "Router"    =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Router.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Router.yml",
        ],
        "Firewall"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Firewall.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Firewall.yml",
        ],
        "Database"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Database.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Database.yml",
        ],
        "Head"      =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Head.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Head.yml",
        ],
        "Style"     =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Style.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Style.yml",
        ],
        "Bridge"    =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Bridge.yml",
            "path_target"   =>  self::DEFAULT_CONFIG_FILES."/Bridge.yml",
        ], */
    ];

    /** Public static methods
     ******************************************************
     */

    /**
     * Setup
     * 
     * Setup config files
     * 
     * @param array|string $config Config to set
     * 
     * @return void
     */
    public static function setup(array|string $configs = "*"):void {

        # Check config
        if(is_string($configs))

            # Convert string to array
            $configs = [$configs];

        # Check configs
        if(empty($configs))

            # Stop function
            return;

        # Iteration DEFAULT_CONFIG_FILES
        foreach(self::DEFAULT_CONFIG_FILES as $name => $config){

            # Check not all config
            if(!in_array("*", $configs) && !in_array($name, $configs))

                # Continue
                continue;

            # Check if action_set
            if(($config['action_set'] ?? false) && method_exists(static::class, $config['action_set'])){

                # Execute methods
                static::{$config['action_set']}($config);

                # Continue
                continue;

            }

            # Copy config
            File::copy($config['path_source'], $config['path_target']);
            
        }

    }

    /**
     * Update
     * 
     * Update config files
     * 
     * @param array|string $config Config to set
     * 
     * @return void
     */
    public static function update(array|string $configs = "*"):void {

        # Check config
        if(is_string($configs))

            # Convert string to array
            $configs = [$configs];

        # Check configs
        if(empty($configs))

            # Stop function
            return;

        # Iteration DEFAULT_CONFIG_FILES
        foreach(self::DEFAULT_CONFIG_FILES as $name => $config){

            # Check not all config
            if(!in_array("*", $configs) && !in_array($name, $configs))

                # Continue
                continue;

            # Copy config
            # TBC...

        }

    }

    /**
     * Exists
     * 
     * Check config file exists
     * 
     * @param string $config Config name to search
     * @param string $folder Folder where search config
     * @return bool
     */
    public static function exists(string $config = "", string $folder = self::DEFAULT_PATH):bool {

        # Set result
        $result = false;

        # Get path
        $folderPath = File::path($folder);

        # Check config and folder
        if(!$config || !$folderPath || !File::exists($folderPath))

            # Return false
            return $result;

        # New finder instance
        $finder = new Finder();

        # Prepare finder
        $finder->files()->name(["$config.*", $config])->in($folderPath);

        # Check finder result
        if($finder->hasResults())

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Create
     * 
     * Create config files
     * 
     * @param string $name Name of the new config
     * @param array $data Data of the new config
     * @param string $folder Folder where create config
     * @return string Path of the new config file
     */
    public static function create(string $name = "", array $data = [], string $folder = self::DEFAULT_PATH, string $format = "yml"):string {

        # Result
        $result = "";

        # Get folder path
        $folderPath = File::path($folder);

        # Process name
        $name = ucfirst(strtolower($name));

        # Get target_path
        $targetPath = "$folderPath/$name.$format";

        # Check folder
        if(!$folderPath || !File::exists($folderPath))
            
            # New error
            throw new CrazyException(
                "Folder \"$folder\" given isn't valid", 
                500,
                [
                    "custom_code"   =>  "config-002",
                ]
            );

        # Check name of config
        if(!$name)
            
            # New error
            throw new CrazyException(
                "Name \"$name\" given isn't valid", 
                500,
                [
                    "custom_code"   =>  "config-003",
                ]
            );

        # Check config already exists
        if(File::exists($targetPath))
            
            # New error
            throw new CrazyException(
                "Config \"$name.$format\" already exists", 
                500,
                [
                    "custom_code"   =>  "config-004",
                ]
            );

        # Get instance of current format
        $formatInstance = File::MIMTYPE_TO_CLASS[File::EXTENSION_TO_MIMETYPE[$format] ?? false] ?? false;

        # Check format instance
        if(!$formatInstance)
            
            # New error
            throw new CrazyException(
                "Format \"$format\" isn't valid", 
                500,
                [
                    "custom_code"   =>  "config-005",
                ]
            );

        # Create file
        $formatInstance::create($targetPath, [$name => $data], Header::get($format));

        # Set content
        $GLOBALS[self::PREFIX][$name] = $data;

        # Set result
        $result = $targetPath;

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Copy App Config
     * 
     * Methods for convert json composer.json to yml config
     * 
     * @param string $config Config array with the pAth of the composer.json file
     * @return void
     */
    private static function _setAppConfig(array $config = []):void {

        # Check config
        if(!isset($config['path_source']))

            # Stop function
            return;

        # Open composer.json
        $composerContent = Composer::open();

        # Check content
        if(empty($composerContent))
            
            # New error
            throw new CrazyException(
                "composer.json is not valid or is maybe missing", 
                500,
                [
                    "custom_code"   =>  "config-001",
                ]
            );

        # Set framework content
        if(
            isset($composerContent["repositories"][0]["url"]) && 
            !empty($composerContent["repositories"][0]["url"])
        )

            # Set framework content
            $frameworkContent = [
                "path"      =>  realpath(File::path("@crazyphp_root"))
            ];

        else

            # Set framework content
            $frameworkContent = false;

        $composerContent = Arrays::mergeMultidimensionalArrays(
            true,
            $composerContent,
            [
                "root"      =>  Env::get("app_root"),
                # To delete for production
                "framework" =>  $frameworkContent,
                "public"    =>  "public"
            ]
        );

        # Create yaml
        Yaml::create($config['path_target'], ["App" => $composerContent], Header::get("yml"));

        # Set content in cache
        $GLOBALS[self::PREFIX]["App"] = $composerContent;

    }


}