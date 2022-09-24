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
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;

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

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get single of multiple config 
     * 
     * @param string|array $input Name of config(s)
     * 
     * @return array
     */
    public static function get(string|array $inputs = "") :array {

        # Declare result
        $result = [];

        # Check input
        if(empty($inputs))

            # Return result
            return $result;

        # Check is array
        if(!is_array($inputs))

            # Transform input to array
            $inputs = [$inputs];

        # Iteration of input
        foreach($inputs as $input){

            # Check config has input
            if(self::has($input)){

                /* Get config file */

                # Replace separator
                $input = str_replace(self::SEPARATOR, "___", $input);

                # Explode to get first value
                $configFolder = explode("___", $input, 1)[0];

                # New finder
                $finder = new Finder();

                # Search files
                $finder
                    ->files()
                    ->name(["$configFolder.*", $configFolder])
                    ->in(File::path(self::FOLDER_PATH))
                ;

                # Check not multiple file
                if($finder->count() === 0)
        
                    # New Exception
                    throw new CrazyException(
                        "No config file found for \"$configFolder\".", 
                        500,
                        [
                            "custom_code"   =>  "config-006",
                        ]
                    );

                # Check not multiple file
                if($finder->count() > 1)

                    # New Exception
                    throw new CrazyException(
                        "Conflict of config file with the same name for \"$configFolder\".", 
                        500,
                        [
                            "custom_code"   =>  "config-001",
                        ]
                    );

                # Iteration of files
                foreach ($finder as $file){

                    # Get path file
                    $filePath = $file->getPathname();

                    # Get mime type
                    $fileMime = File::guessMime($filePath);

                    # break;
                    break;

                }

                # Check file path and file mime
                if(!$filePath || !$fileMime || !isset(File::MIMTYPE_TO_CLASS[$fileMime]))

                    # Return result
                    return $result;

                # Set file instance class
                $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];

                # Read file path
                $content = $fileInstance::open($filePath);

                # Get value
                $valueToPush = Arrays::parseKey($input, $content);

                # Push value in result
                Arrays::fill($result, $input, $valueToPush);

            }

        }

        # Return result
        return $result;

    }

    /**
     * Has
     * 
     * Get single file of parameter exists 
     * 
     * @param string $input Name of config(s)
     * 
     * @return array
     */
    public static function has(string $input = "") :bool {

        # Declare result
        $result = false;

        # Check input
        if(!$input)

            # Return result
            return $result;

        /* Get config file */

        # Replace separator
        $input = str_replace(self::SEPARATOR, "___", $input);

        # Explode to get first value
        $configFolder = explode("___", $input, 1)[0];

        # New finder
        $finder = new Finder();

        # Search files
        $finder
            ->files()
            ->name(["$configFolder.*", $configFolder])
            ->in(File::path(self::FOLDER_PATH))
        ;

        # Check not multiple file
        if($finder->count() === 0)

            # New Exception
            throw new CrazyException(
                "No config file found for \"$configFolder\".", 
                500,
                [
                    "custom_code"   =>  "config-005",
                ]
            );

        # Check not multiple file
        if($finder->count() > 1)

            # New Exception
            throw new CrazyException(
                "Conflict of config file with the same name for \"$configFolder\".", 
                500,
                [
                    "custom_code"   =>  "config-002",
                ]
            );


        # Iteration of files
        foreach ($finder as $file){

            # Get path file
            $filePath = $file->getPathname();

            # Get mime type
            $fileMime = File::guessMime($filePath);

            # break;
            break;

        }

        # Set file instance class
        $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];

        # Read file path
        $content = $fileInstance::open($filePath);

        # Get value
        $valueToCheck = Arrays::parseKey($input, $content);

        # Check value to check
        if($valueToCheck !== null)

            # Update result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Set
     * 
     * Set value in config
     * 
     * @param string $input Name of config(s)
     * @param any $data Data to put inside parameter
     * 
     * @return void
     */
    public static function set(string $input = "", $data = null) :void {

        # Check input
        if(!$input)

            # Return result
            return;

        /* Get config file */

        # Replace separator
        $input = str_replace(self::SEPARATOR, "___", $input);

        # Explode to get first value
        $configFolder = explode("___", $input, 2)[0];

        # New finder
        $finder = new Finder();

        # Search files
        $finder
            ->files()
            ->name(["$configFolder.*", $configFolder])
            ->in(File::path(self::FOLDER_PATH))
        ;

        # Check not multiple file
        if($finder->count() === 0)

            # New Exception
            throw new CrazyException(
                "No config file found for \"$configFolder\".", 
                500,
                [
                    "custom_code"   =>  "config-004",
                ]
            );

        # Check not multiple file
        if($finder->count() > 1)

            # New Exception
            throw new CrazyException(
                "Conflict of config file with the same name for \"$configFolder\".", 
                500,
                [
                    "custom_code"   =>  "config-003",
                ]
            );


        # Iteration of files
        foreach ($finder as $file){

            # Get path file
            $filePath = $file->getPathname();

            # Get mime type
            $fileMime = File::guessMime($filePath);

            # break;
            break;

        }

        # Set file instance class
        $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];

        # Create content
        $content = [];

        # Fill value in content
        Arrays::fill($content, $input, $data);

        # Update config
        $fileInstance::update($filePath, $content, true);

    }

    /**
     * Update
     * 
     * Update value in config
     * 
     * @param string $input Name of config(s)
     * @param any $data Data to put inside parameter
     * @param bool $setValueIFNotExits Set value if not exists
     * 
     * @return void
     */
    public static function update(string $input = "", $data = null, bool $setValueIFNotExits = false) :void {

        # Return result
        return;

    }

    /**
     * Delete
     * 
     * Delete value in config
     * 
     * @param string $input Name of config(s) to delete
     * 
     * @return void
     */
    public static function delete(string $input = "") :void {

        # Return result
        return;

    }

    /**
     * Exists
     * 
     * Check config file exists
     * 
     * @param string $input Name of config
     * @param string $configFolder Folder of configs
     * @return bool
     */
    public static function exists(string $input = "", $configFolder = self::FOLDER_PATH):bool {

        # Set result
        $result = false;

        # Check input
        if(!$input)

            # Return result
            return $result;

        # Process path
        $configFolder = File::path($configFolder);

        # Check process file exists
        if(!is_dir($configFolder))

            # Return false
            return $result;

        # New finder
        $finder = new Finder();

        # Search files
        $finder->files()->in($configFolder);

        # Update result
        $result = $finder->hasResults();

        # Return result
        return $result;

    }

    /** Private static key
     ******************************************************
     */

    /**
     * get Key
     * 
     * Get key for cache
     */
    private static function _getKey(string $input = "", $prefix = ""):string {

        # Declare result
        $result = "";

        # Check $inputs
        if($input)

            # Push in result
            $result = $input;

        # Replace {}()/\@: by dot
        $result = str_replace(["{", "}", "(", ")", "/", "\\", "@", ":"], ".", $prefix.$result);

        # Return result
        return $result;

    }

    /** Public Constants
     ******************************************************
     */

    /* @var config path */
    public const FOLDER_PATH = "@app_root/config";

    /* @var separator */
    public const SEPARATOR = ["/", "."];

}