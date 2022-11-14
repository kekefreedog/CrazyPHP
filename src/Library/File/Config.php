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
use CrazyPHP\Library\Form\Process;
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
    public static function get(string|array $inputs = ""):array {

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
                $configFolder = explode("___", $input, 2)[0];

                # Check if config is cached on global variables
                if(isset($GLOBALS[self::PREFIX]) && array_key_exists($configFolder, $GLOBALS[self::PREFIX])){

                    # Get content
                    $content = $GLOBALS[self::PREFIX][$configFolder];

                ### Else get it
                }else{

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
                                "custom_code"   =>  "config-001",
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

                    # Check file path and file mime
                    if(!$filePath || !$fileMime || !isset(File::MIMTYPE_TO_CLASS[$fileMime]))

                        # Return result
                        return $result;

                    # Set file instance class
                    $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];

                    # Read file path
                    $content = $fileInstance::open($filePath);

                    # Set content in global
                    # $GLOBALS[self::PREFIX][$configFolder] = $content;
                    self::_setGlobalCache($configFolder, $content);

                }

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
     * Get Value
     * 
     * Get value on config from key
     * 
     * @param string $input Name of config(s)
     * 
     * @return
     */
    public static function getValue(string $input = "") {


        # Declare result
        $result = [];

        # Check input
        if(!$input)

            # Return result
            return $result;

        # Check config has input
        if(self::has($input)){

            /* Get config file */

            # Replace separator
            $input = str_replace(self::SEPARATOR, "___", $input);

            # Explode to get first value
            $configFolder = explode("___", $input, 2)[0];

            # Check if config is cached on global variables
            if(isset($GLOBALS[self::PREFIX]) && array_key_exists($configFolder, $GLOBALS[self::PREFIX])){

                # Get content
                $content = $GLOBALS[self::PREFIX][$configFolder];

            # Get content and set cache
            }else{

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
                            "custom_code"   =>  "config-003",
                        ]
                    );

                # Check not multiple file
                if($finder->count() > 1)

                    # New Exception
                    throw new CrazyException(
                        "Conflict of config file with the same name for \"$configFolder\".", 
                        500,
                        [
                            "custom_code"   =>  "config-004",
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

                # Set content
                # $GLOBALS[self::PREFIX][$configFolder] = $content;
                self::_setGlobalCache($configFolder, $content);

            }

            # Get value
            $result = Arrays::parseKey($input, $content);

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
        $configFolder = explode("___", $input, 2)[0];



        # Check if config is cached on global variables
        if(isset($GLOBALS[self::PREFIX]) && array_key_exists($configFolder, $GLOBALS[self::PREFIX])){

            # Get content
            $content = $GLOBALS[self::PREFIX][$configFolder];

        # Check in files
        }else{

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
                        "custom_code"   =>  "config-006",
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

            # Set content
            # $GLOBALS[self::PREFIX][$configFolder] = $content;
            self::_setGlobalCache($configFolder, $content);

        }

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
                    "custom_code"   =>  "config-007",
                ]
            );

        # Check not multiple file
        if($finder->count() > 1)

            # New Exception
            throw new CrazyException(
                "Conflict of config file with the same name for \"$configFolder\".", 
                500,
                [
                    "custom_code"   =>  "config-008",
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
        $content = $fileInstance::update($filePath, $content, true);

        # Update or set global cache
        # $GLOBALS[self::PREFIX][$configFolder] = $content;
        static::_setGlobalCache($configFolder, $content);

    }

    /**
     * Set value
     * 
     * Set Value in Config file
     * 
     * @param string $key Parameter of config to set
     * @param $data to push in key parameter
     * @return void
     */
    public static function setValue(string $key = "", $data = null, $createIfNotExists = true, $path = self::FOLDER_PATH):void {

        # Prepare config folder
        $path = File::path(self::FOLDER_PATH);

        # Parse key
        $key = str_replace(self::SEPARATOR, "___", $key);

        # Explode keys 
        $keys = explode("___", $key);

        # Check config file
        if(!$path || empty($keys))

            # Stop script
            return;

        # New finder
        $finder = new Finder();

        # Search files
        $finder
            ->files()
            ->name([$keys[0].".*", $keys[0]])
            ->in($path)
        ;

        # Check not multiple file
        if($finder->count() === 0)

            # New Exception
            throw new CrazyException(
                "No config file found for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "config-009",
                ]
            );

        # Check not multiple file
        if($finder->count() > 1)

            # New Exception
            throw new CrazyException(
                "Conflict of config file with the same name for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "config-010",
                ]
            );

        # Iteration of files
        foreach ($finder as $file){

            # Get path file
            $filePath = $file->getPathname();

            # break;
            break;

        }

        # Get mime type
        $fileMime = File::guessMime($filePath);

        # Set file instance class
        $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];


        # Get mime type
        $fileData = $fileInstance::open($filePath);

        # Check if is array
        if(!is_array($fileData))

            # New Exception
            throw new CrazyException(
                "Config \"".$keys[0]."\" isn't valid... Array waited !", 
                500,
                [
                    "custom_code"   =>  "config-011",
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

        # Set result
        $fileInstance::set($filePath, $result);

        # Set cache
        # $GLOBALS[self::PREFIX][$keys[0]] = $result;
        self::_setGlobalCache($keys[0], $result);

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
    public static function update(string $input = "", $data = null, bool $setValueIfNotExits = false) :void {

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
     * Remove value
     * 
     * Set Value in Config file
     * 
     * @param string $key Parameter of config to set
     * @param $data to push in key parameter
     * @return void
     */
    public static function removeValue(string $key = "", $data = null, $path = self::FOLDER_PATH):void {

        # Prepare config folder
        $path = File::path(self::FOLDER_PATH);

        # Parse key
        $key = str_replace(self::SEPARATOR, "___", $key);

        # Explode keys 
        $keys = explode("___", $key);

        # Check config file
        if(!$path || empty($keys))

            # Stop script
            return;

        # New finder
        $finder = new Finder();

        # Search files
        $finder
            ->files()
            ->name([$keys[0].".*", $keys[0]])
            ->in($path)
        ;

        # Check not multiple file
        if($finder->count() === 0)

            # New Exception
            throw new CrazyException(
                "No config file found for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "config-009",
                ]
            );

        # Check not multiple file
        if($finder->count() > 1)

            # New Exception
            throw new CrazyException(
                "Conflict of config file with the same name for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "config-010",
                ]
            );

        # Iteration of files
        foreach ($finder as $file){

            # Get path file
            $filePath = $file->getPathname();

            # break;
            break;

        }

        # Get mime type
        $fileMime = File::guessMime($filePath);

        # Set file instance class
        $fileInstance = File::MIMTYPE_TO_CLASS[$fileMime];


        # Get mime type
        $fileData = $fileInstance::open($filePath);

        # Check if is array
        if(!is_array($fileData))

            # New Exception
            throw new CrazyException(
                "Config \"".$keys[0]."\" isn't valid... Array waited !", 
                500,
                [
                    "custom_code"   =>  "config-011",
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
        $fileInstance::set($filePath, $result, false, false);

        # Set cache
        $GLOBALS[self::PREFIX[$keys[0]]] = $result;

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

    /** Private static methods
     ******************************************************
     */

    /**
     * Set Gobal Cache
     * 
     * Set cached value of parameter in global variable
     * 
     * @param string $key Key in cache
     * @param array $value Value to cache
     * @return void
     */
    private static function _setGlobalCache(string $key = "", array $value = []):void {

        # Process value
        $value = Process::envAndCacheValues($value);

        # Check key
        if($key)

            # Set globals
            $GLOBALS[self::PREFIX][$key] = $value;

        # If key valid
        else

            # Set globals
            $GLOBALS[self::PREFIX] = $value;


    }

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

    /** @const config path */
    public const FOLDER_PATH = "@app_root/config";

    /** @const separator */
    public const SEPARATOR = ["/", "."];

    /** @const string PREFIX  */
    public const PREFIX = "__CRAZY_CONFIG";

}