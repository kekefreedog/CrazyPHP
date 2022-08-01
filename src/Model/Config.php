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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;

/**
 * Config
 *
 * Methods for interacting with config file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Config{

    /** Public constants
     ******************************************************
     */
    
    /**
     * Default Config files
     */
    public const DEFAULT_CONFIG_FILES = [
        "app"       =>  [
            "path_source"   =>  "@app_root"."/composer.json",
            "path_target"   =>  "@app_root"."/config/App.yml",
            "action_set"    =>  "_setAppConfig",
        ],
        "Template"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Template.yml",
            "path_target"   =>  "@app_root"."/config/Template.yml",
        ],
        "Router"    =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Router.yml",
            "path_target"   =>  "@app_root"."/config/Router.yml",
        ],
        "Firewall"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Firewall.yml",
            "path_target"   =>  "@app_root"."/config/Firewall.yml",
        ],
        "Database"  =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Database.yml",
            "path_target"   =>  "@app_root"."/config/Database.yml",
        ],
        "Head"      =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Head.yml",
            "path_target"   =>  "@app_root"."/config/Head.yml",
        ],
        "Style"     =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Style.yml",
            "path_target"   =>  "@app_root"."/config/Style.yml",
        ],
        "Bridge"    =>  [
            "path_source"   =>  "@crazyphp_root"."/resources/Yml/Bridge.yml",
            "path_target"   =>  "@app_root"."/config/Bridge.yml",
        ],
    ];

    /** Public static methods
     ******************************************************
     */

    /**
     * Set
     * 
     * Set config files
     * 
     * @param array|string $config Config to set
     * 
     * @return void
     */
    public static function set(array|string $configs = "*"):void {

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

        # Create yaml
        Yaml::create($config['path_target'], $composerContent/* , Header::get("yml") */);

    }


}