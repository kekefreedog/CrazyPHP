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

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Model\Env;


/**
 * File
 *
 * Methods for interacting with files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class File {

    /** Public static method | File type
     ******************************************************
     */

    /**
     * Guess Mime Type
     * 
     * Sometimes mime type isn't corresponding of the kind of file.
     * It just trying to check extension first, then mimetype
     */
    public static function guessMime(string $input = ""): string|false {

        # Declare result
        $result = false;

        # Try to get result by extension
        $result = self::getMimeByFileExtension($input, false);

        # Check result
        if($result)

            # Return result
            return $result;

        # Try to get result by mime type
        $result = self::getMime($input);

        # Return result
        return $result;

    }
    
    /**
     * Get Mime
     * 
     * Get Mime Type of file
     *
     * @param string $input Parameter to read
     * @return string
     */
    public static function getMime(string $input = ""): string|false {

        # Declare result
        $result = false;

        # Check input is valid
        if($input && file_exists($input))

            # Get Mime
            $result = mime_content_type($input);

        # Return result
        return $result;

    }    
    
    /**
    * Get File Extension
    * 
    * Get file Extension of the file given
    * 
    * @param string $input Parameter to read
    * @return string
    */
   public static function getFileExtension(string $input = ""): string|false {

    # Declare result
    $result = false;

    # Check input is valid
    if(!$input || !file_exists($input))

        # Return false
        return $result;

    $result = pathinfo($input, PATHINFO_EXTENSION);

    # Return result
    return $result;

   }

    /**
     * Get Mime By File Extension
     * 
     * Get Mime Type By file Extension of the file given
     * 
     * @param string $input Parameter to read
     * @param bool $enableException Eneble exception
     * @return string
     */
    public static function getMimeByFileExtension(string $input = "", bool $enableException = true): string|false {

        # Declare result
        $result = false;

        # Get extension of input
        $extension = self::getFileExtension($input);

        # Check extension
        if(!$extension)

            # Return result
            return $result;

        # Check extension is in collection
        if($enableException && !array_key_exists(strtolower($extension), self::EXTENSION_TO_MIMETYPE))

            # New Exception
            throw new CrazyException(
                "Extension \”$extension\” isn't existing in collection, just need to add it...",
                415,
                [
                    "custom_code"   =>  "file-001",
                ]
            );

        else

            # Set result
            $result = self::EXTENSION_TO_MIMETYPE[$extension] ?? false;

        # return result 
        return $result;

    }

    /**
     * Remove
     * 
     * Remove folder recursively
     * 
     * @source https://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
     * 
     * @param string $dir Directory to delete
     * @return bool
     */
    public static function remove(string $dir = ""):bool {

        # Check input
        if(!$dir || !file_exists($dir))
            return true;
    
        # Check if file
        if(!is_dir($dir)) 
            return unlink($dir);
    
        # Iteration of scan dir
        foreach (scandir($dir) as $item) {

            # Check not . or double ..
            if ($item == '.' || $item == '..')
                continue;
    
            # Recursive loop
            if (!self::remove($dir . DIRECTORY_SEPARATOR . $item))
                return false;
    
        }
    
        # Remove current current folder
        return rmdir($dir);

    }

    /**
     * Read
     * 
     * Read content of file
     * 
     * @param string|array $dir Directory to get content
     * @return string
     */
    public static function read(string|array $paths = ""):string {

        # Declare result
        $result = "";

        # Check path is not array
        if(!is_array($paths))

            # Convert to array
            $paths = [$paths];

        # Iteration of paths
        foreach($paths as $path){

            # Check path
            if(!$path || !file_exists($path))

                # Continue iteration
                continue;

            # Get file content
            $content = file_get_contents($path);

            # Check result
            if($content)

                # Set result
                $result .= $content;

        }

        # Return result
        return $result;

    }

    /**
     * Get Last Modified Date
     * 
     * Get last modified date of file or group of files
     * 
     * @param string|array $input Path to file or files to check
     * @return DateTime
     */
    public static function getLastModifiedDate(string|array $inputs = ""):DateTime|null {

        # Declare result
        $result = DateTime::kz();

        # Check input is aarray
        if(!is_array($inputs))

            # Convert string to array
            $inputs = [$inputs];

        # Iteration des inputs
        foreach($inputs as $input){

            # Result
            $newResult = DateTime::lastUpdateFile($input);

            # Check new result
            if(!$newResult)

                # Continue iteration
                continue;

            # Compare to result
            if(!$result || ( $newResult > $result ))

                # Set result
                $result = $newResult;

        }

        # Return result
        return $result;

    }
    
    /**
     * File Exists 
     * 
     * Check if file exists
     *
     * @param string $input Path of the json file
     * @return bool
     */
    public static function exists(string $input = ""):bool {

        # Set result
        $result = false;

        # Check input and file exists
        if($input && file_exists($input))

            # Toggle result
            $result = true;

        # Return result
        return $result;

    }

    /** Path
     * 
     * Get path (replace @app_root <> \_\_APP_ROOT\_\_, @crazyphp_root <> \_\_CRAZYPHP_ROOT\_\_)
     * 
     * @param string $input Path to process
     * 
     * @return string
     */
    public static function path(string $input = ""):string {

        # Set result
        $result = $input;

        # Check if at sign in input
        if(strpos($input, "@") === false)

            # Stop function
            return $input;

        # Regex expression for select word starting after @
        $regex = '/@[\w]+/';
        
        # Search values
        preg_match_all(
            $regex,
            $input,
            $results
        );

        # Check result
        if(!empty($results[0]))

            # Iteration des result
            foreach($results[0] as $k => $v){

                # Set v
                #$cleanV = "__".strtoupper((string)str_replace("@", "", $v))."__";
                $cleanV = strtoupper((string)str_replace("@", "", $v));
                
                # Check env exists
                if(Env::has($cleanV)){

                    # Replace
                    $replace = Env::get($cleanV);

                    # Replace in result
                    $result = str_replace($v, $replace, $result);

                # Env doesn't exists
                }else

                    # Replace in result
                    $result = str_replace($v, "", $result);

            }

        # Return result
        return $result;

    }

    /**
     * Copy
     * 
     * Copy with check of folder and check of path
     * 
     * @param string $source Source to copy
     * @param string $target Target where copy
     * @return bool
     */
    public static function copy(string $source = "", string $target = ""):bool {

        # Declare result
        $result = false;

        # Path source
        $path_source = self::path($source);

        # Path target
        $path_target = self::path($target);

        # Path target parent folder
        $path_folder = dirname($path_target);

        # Check folder target exists
        if(!is_dir($path_folder))

            # Create folder
            mkdir(dirname($path_folder, 0777, true));

        # Set result
        $result = copy($path_source, $path_target);

        # Return result
        return $result;

    }


    /** Public constant
     ******************************************************
     */

    /**
     * Correspondance Extension and Mimetype
     */
    public const EXTENSION_TO_MIMETYPE = [
        # Yml
        "yml"   =>  "text/yaml",
        "yaml"  =>  "text/yaml",
        # Json
        "json"  =>  "application/json",
        # Php
        "php"   =>  "text/php"
        # TBC ...
    ];

}