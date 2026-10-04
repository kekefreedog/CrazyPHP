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
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\File\File;

/**
 * Webpack
 *
 * Methods for interacting with Webpack files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Webpack{

    /** Public Static Methods | Hash
     ******************************************************
     */

    /**
     * Get Hash
     * 
     * Search hash in folder
     * 
     * @param bool $setValueInFrontConfig Set value found in front config
     * @return string
     */
    public static function getHash(bool $setValueInFrontConfig = true):string {

        # Set result
        $result = "";

        # Path to search js files where hash is wrote
        $path = File::path("@app_root/".FileConfig::getValue("App.public")."/dist");

        # New finder
        $finder = new Finder();

        # Prepare finder
        $finder
            ->files()
            ->name("index.*.js")
            ->depth("== 0")
            ->sortByModifiedTime()
            ->reverseSorting()
            ->in($path)
        ;


        # Check if finder has result 
        if($finder->hasResults())

            # Iteration of files founded
            foreach ($finder as $file){

                # Get file name
                $fileName =  $file->getFilenameWithoutExtension();

                # Explode filename by dot
                $explodedFileName = explode(".", $fileName);

                # Get last value
                $result = array_pop($explodedFileName);

                # Stop iteration
                break;

            }

        # Check setValueInFrontConfig
        if($setValueInFrontConfig && $result !== ""){

            # Config scope
            $configScope = FileConfig::getValue("Front.lastBuild");

            # Register only assets belonging to the selected build
            $configScope["files"] = self::getScripts($result);

            # Register page scripts from the same build
            $configScope["pages"] = [];
            $pagePath = $path."/page/app";
            if(is_dir($pagePath)){

                $pages = (new Finder())->files()->name("*.$result.js")->depth("== 0")->in($pagePath);
                foreach($pages as $page)
                    $configScope["pages"][] = $page->getFilename();

            }

            # Check hash
            if(isset($configScope["hash"]))

                # Set new hash
                $configScope["hash"] = $result;

            # Set date
            $configScope["date"] = (new DateTime())->format("c");

            # Set value in file config
            FileConfig::setValue("Front.lastBuild", $configScope);

        }

        # Return result
        return $result;

    }

    /**
     * Get Scripts
     *
     * Return root scripts from one build, with the runtime before its consumers.
     *
     * @param string $hash Selected build hash
     * @return array
     */
    public static function getScripts(string $hash):array {

        # Ignore missing hashes instead of matching unrelated assets
        if($hash === "")
            return [];

        # Find scripts belonging to the selected build
        $path = File::path("@app_root/".FileConfig::getValue("App.public")."/dist");
        $finder = (new Finder())->files()->name("*.$hash.js")->depth("== 0")->in($path);
        $scripts = [];
        foreach($finder as $file)
            $scripts[] = $file->getFilename();

        # Load the runtime first, shared chunks next and the entrypoint last
        $priority = static function(string $file):int {

            return str_starts_with($file, "runtime.") ? 0
                : (str_starts_with($file, "vendors.") ? 1
                    : (str_starts_with($file, "index.") ? 3 : 2));

        };
        usort($scripts, static fn(string $left, string $right):int => ($priority($left) <=> $priority($right)) ?: strcmp($left, $right));

        # Return scripts in execution order
        return $scripts;

    }

}
