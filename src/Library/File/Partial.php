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
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlS;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\Form\Process;
use Symfony\Component\Finder\Finder;

/**
 * Partial
 *
 * Methods for interacting with Handlebars Partial files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Partial {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get All
     * 
     * Get all partial found in your crazy app
     * 
     * @param bool $minimize Minimize result
     * @param string $scriptPath
     * @param string $stylePath
     * @param string $templatePath
     * @return array
     */
    public static function getAll(
        bool $minimize = true,
        string $scriptPath = self::FRONT_TS_FILE,
        string $stylePath = self::FRONT_SCSS_FILE,
        string $templatePath = self::PARTIAL_TEMPLATE_DIR,
    ):array {

        # Set result
        $result = [];

        # Check if minimize
        if($minimize){

            # Set result
            $result = array_unique(array_merge(
                static::getAllFromScript($scriptPath),
                static::getAllFromStyle($stylePath),
                static::getAllFromTemplate($templatePath)
            ));

        }else{

            # Iteration type
            foreach(["script", "style", "template"] as $type){

                # Set type path
                $typePath = $type."Path";

                # Set type method
                $typeMethod = "getAllFrom".ucfirst($type);

                # Get type result
                $typeResult = static::{$typeMethod}($$typePath);

                # Check result
                if(!empty($typeResult)) foreach($typeResult as $item){

                    # Set temp value
                    $tempValue = true;

                    # If type template
                    if($type === "template")

                        # Set temp value
                        $tempValue = rtrim($templatePath, "/")."/".Process::camelToSnake($item).".hbs";

                    # If type script
                    if($type === "script"){

                        # Set temp value
                        $tempValue = str_replace("Front/index.ts", "Environment/Partials", $scriptPath)."/".$item.".ts";

                    }else
                    # If type style
                    if($type === "style"){

                        # Set temp value
                        $tempValue = str_replace("index.scss", "partial", $stylePath)."/_".Process::camelToSnake($item).".scss";

                    }

                    # Push into result
                    $result[$item][$type] = $tempValue;

                }

            }

        }

        # Return result
        return $result;

    }
    /**
     * Get Summary
     * 
     * Get a summary of existing routers
     * 
     * @return array
     */
    public static function getSummary(
        string $scriptPath = self::FRONT_TS_FILE,
        string $stylePath = self::FRONT_SCSS_FILE,
        string $templatePath = self::PARTIAL_TEMPLATE_DIR
    ):array {

        # Set result
        $result = [];

        # Get all
        $all = static::getAll(true, $scriptPath, $stylePath, $templatePath);

        # Iteration all
        if(!empty($all))

            # Set result
            $result = array_combine($all, $all);

        # Return result
        return $result;

    }

    /**
     * Get
     * 
     * Get partial by name
     * 
     * @param string $name of Partial to search
     * @param string $scriptPath
     * @param string $stylePath
     * @param string $templatePath
     * @return array|null
     */
    public static function get(
        string $name,
        string $scriptPath = self::FRONT_TS_FILE,
        string $stylePath = self::FRONT_SCSS_FILE,
        string $templatePath = self::PARTIAL_TEMPLATE_DIR,
    ):array|null {

        # Set result
        $result = null;

        # Checj name
        if($name){

            # Apply uc first on name
            $name = ucfirst($name);

            # Check if "_" into partial name given
            if(strpos($name, "_") !== false){

                # Clean name
                $name = Process::snakeToCamel(trim($name, "_"), true);

            }

            # Get all partial found
            $all = static::getAll(false, $scriptPath, $stylePath, $templatePath);

            # Check all and check key exists
            if(!empty($all) && array_key_exists($name, $all))

                # Set result
                $result = [
                    "name"  =>  $name,
                    "file"  =>  Process::camelToSnake($name),
                ] + $all[$name];

        }

        # Return result
        return $result;

    }

    /**
     * Get All From Script
     * 
     * Get all partial script found in your crazy app
     * 
     * @param string|null $path
     * @return array
     */
    public static function getAllFromScript(string|null $path = self::FRONT_TS_FILE):array {

        # Set result
        $result = [];

        # Check file
        if($path && File::exists($path)){

            # Read file
            $contents = File::read($path);

            # Search all partial
            preg_match_all(
                '/import\s+(\w+)\s+from\s+"..\/Environment\/Partials\/[\w\/]+";/',
                $contents,
                $matches
            );
        
            # Set array map
            $result = $matches[1];

            # Sort by descending length
            if(!empty($result)) usort($result, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
        
        }

        # Return result
        return $result;

    }

    /**
     * Get All From Style
     * 
     * Get all partial style found in your crazy app
     * 
     * @param string|null $path
     * @return array
     */
    public static function getAllFromStyle(string|null $path = self::FRONT_SCSS_FILE):array {

        # Set result
        $result = [];

        # Check file
        if($path && File::exists($path)){

            # Read file
            $contents = File::read($path);

            # Search all partial
            preg_match_all(
                "/@import\s+['\"]\.\/partial\/([^'\"]+)['\"]\s*;/",
                $contents,
                $matches
            );
        
            # Set array map
            $result = array_map(fn($name) => Process::snakeToCamel($name, true), $matches[1]);

            # Sort by descending length
            if(!empty($result)) usort($result, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
        
        }

        # Return result
        return $result;

    }

    /**
     * Get All From Template
     * 
     * Get all partial style found in your crazy app
     * 
     * @return array
     */
    public static function getAllFromTemplate(string|null $path = self::PARTIAL_TEMPLATE_DIR):array {

        # Set result
        $result = [];

        # Check file
        if($path && File::exists($path)){

            # New finder
            $finder = new Finder();

            # Prepare finder
            $finder
                ->files()
                ->in(File::path($path))
                ->name('*.hbs')
            ;
        
            # Iteration file found
            if($finder->hasResults()) foreach($finder as $file)

                # Push partial
                $result[] = Process::snakeToCamel($file->getBasename('.hbs'), true);

            # Sort by descending length
            if(!empty($result)) usort($result, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
        
        }

        # Return result
        return $result;

    }

    /** Public Constants
     ******************************************************
     */

    /** @var string PARTIAL_SCRIPT_DIR */
    public const PARTIAL_SCRIPT_DIR = "@app_root/app/Environment/Partials/";

    /** @var string PARTIAL_STYLE_DIR */
    public const PARTIAL_STYLE_DIR = "@app_root/app/Front/style/scss/partial/";

    /** @var string PARTIAL_TEMPLATE_DIR */
    public const PARTIAL_TEMPLATE_DIR = "@app_root/assets/Hbs/partials/";

    /** @var string FRONT_TS_FILE */
    public const FRONT_TS_FILE = "@app_root/app/Front/index.ts";

    /** @var string FRONT_SCSS_FILE */
    public const FRONT_SCSS_FILE = "@app_root/app/Front/style/scss/index.scss";

}