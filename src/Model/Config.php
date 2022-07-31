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
use CrazyPHP\Library\File\File;

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

}