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

    /** Public constants
     ******************************************************
     */

    /** @var string EXTENSION_DIRECTORY */
    public const EXTENSION_DIRECTORY = "@crazyphp_root/resources/Extensions";


}