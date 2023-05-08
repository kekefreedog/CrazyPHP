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
 * @copyright  2022-2022 Kévin Zarshenas
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
 * @copyright  2022-2022 Kévin Zarshenas
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
            ->name('*.*.js')
            ->in($path);
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
        if($setValueInFrontConfig){

            # Config scope
            $configScope = FileConfig::getValue("Front.lastBuild");

            # Check files
            if(isset($configScope["files"]))

                # Iteration of files
                foreach($configScope["files"] as &$v)

                    # Replace hash in value
                    $v = preg_replace('/\.([a-zA-Z0-9]+)\.js$/', ".$result.js", $v);

            # Check pages
            if(isset($configScope["pages"]))

                # Iteration of pages
                foreach($configScope["pages"] as &$v)

                    # Replace hash in value
                    $v = preg_replace('/\.([a-zA-Z0-9]+)\.js$/', ".$result.js", $v);

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

}