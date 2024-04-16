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
use CrazyPHP\Library\Array\Arrays;
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

        # Check input
        $input = static::path($input);

        # Check input is valid
        if($input && static::exists($input))

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

        # Convert path
        $dir = self::path($dir);

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

            # Prepare path
            $path = self::path($path);

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
     * Open
     * 
     * Open file, depending of file mimetype
     * 
     * @param string $path path of file to open
     * @param string|false $customInstance Custom Instance for open file
     * @return
     */
    public static function open(string $path = "", string|false $customInstance = false){

        # Get path
        $path = self::path($path);

        # Declare result
        $result = null;

        # Check file exists
        if(!File::exists($path))

            # Return result
            return $result;

        # Check if custom instance
        if($customInstance){

            # Open file with it
            $result = $customInstance::open($path);

        }else{
            
            # Guess mimetype
            $mime = File::guessMime($path);

            # Check instance is available
            if(isset(self::MIMTYPE_TO_CLASS[$mime])){

                # Get instance for read file
                $instance = self::MIMTYPE_TO_CLASS[$mime];

                # Set result
                $result = $instance::open($path);

            }else{

                # Set result
                $result = self::read($path);

            }
            
        }

        # Return result
        return $result;

    }

    /**
     * Has Intance
     * 
     * Check if file has instance to open it (exemple json & yaml file will return true)
     * 
     * @param string $path path of file to open
     * @return bool
     */
    public static function hasIntance(string $path = ""):bool {

        # Declare result
        $result = false;

        # Get path
        $path = static::path($path);

        # Check file exists
        if(!static::exists($path))

            # Return result
            return $result;
        
        # Guess mimetype
        $mime = static::guessMime($path);

        # Check instance is available
        if(isset(self::MIMTYPE_TO_CLASS[$mime]))

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Has Key
     * 
     * Check if given file has key
     * 
     * @param string $path
     * @param string $key
     * @return bool
     */
    public static function hasKey(string $path = "", string $key = ""):bool {

        # Set result
        $result = false;

        # Check path and key
        if(!$path || !static::exists($path) || !static::hasIntance($path) || !$key)

            # Stop function
            return $result;

        # Get content of file
        $fileContent = static::open($path);

        # Check file content
        if($fileContent)

            # Check if has key
            $result = Arrays::has($fileContent, $key);

        # Return result
        return $result;

    }

    /**
     * Has Key
     * 
     * Check if given file has key
     * 
     * @param string $path
     * @param string $key
     * @return mixed
     */
    public static function getKey(string $path = "", string $key = ""):mixed {

        # Set result
        $result = false;

        # Check path and key
        if(!$path || !static::exists($path) || !static::hasIntance($path) || !$key)

            # Stop function
            return $result;

        # Get content of file
        $fileContent = static::open($path);

        # Check file content
        if($fileContent && !empty($fileContent))

            # Check if has key
            $result = Arrays::getKey($fileContent, $key);

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

            # Ger file
            $input = File::path($input);

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
     * Check if file or folder exists
     *
     * @param string $input Path of the file
     * @return bool
     */
    public static function exists(string $input = ""):bool {

        # Set result
        $result = false;

        # Get path of input
        $input = self::path($input);

        # Check input and file exists
        if(
            (
                $input && 
                file_exists($input) 
            ) || (
                $input && 
                is_dir($input) 
            )
        )

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
        $regex = Env::REGEX;
        
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
     * Path Reverse
     * 
     * Reverse path by they env name
     * Exemple : 
     * - "/etc/app/project" => "@app_root"
     * - "/etc/app/project/vendor/../crazy/php" => "@crazyphp_root"
     * 
     * @param string $input
     * @param string|array $env Env to reverse
     * @return string
     */
    public static function pathReverse(string $input, string|array $env):string {

        # Set result
        $result = $input;

        # Check inputs
        if($input && ($env || !empty($env))){

            # Check env is array
            if(!is_array($env))

                # Convert to array
                $env = [$env];

            # Check env
            if(!empty($env))

                # Iteration env
                foreach($env as $currentEnv){

                    $currentEnv = strtoupper((string)str_replace("@", "", $currentEnv));

                    # Check env exists
                    if($env && Env::has($currentEnv)){

                        # Get env
                        $currentEnvValue = Env::get($currentEnv);

                        # Set alt
                        $alts = [$currentEnvValue];

                        # Check if valid folder
                        if(File::exists($currentEnvValue) && ($realpath = realpath($currentEnvValue)) != $currentEnvValue)

                            # Set second alt
                            $alts[] = $realpath;

                        # Iteration alt
                        foreach($alts as $alt){

                            # Check if is in string
                            if(strpos($result, $alt) !== false)

                                # Replace str
                                $result = str_replace($alt, "@".strtolower($currentEnv), $result);

                        }

                    }

                }

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
            mkdir($path_folder, 0777, true);

        # Check if path_source is dir
        if(is_dir($path_source)){
   
            # open the source directory
            $dir = opendir($path_source);
            
            # Loop through the files in source directory
            foreach(scandir($path_source) as $file)
            
                # Check not parent of current folder
                if($file != '.' && $file != '..' )
            
                    // Recursively calling self function
                    $result = self::copy("$path_source/$file", "$path_target/$file");
            
            # Close directory
            closedir($dir);

        }else

            # Set result
            $result = copy($path_source, $path_target);

        # Return result
        return $result;

    }

    /**
     * Create
     * 
     * Create only file
     * 
     * @param string $target Path of the file
     * @param string|int|array|null $data Data to write on the file
     * @param int $permission Permission of the file
     * @return bool
     */
    public static function create(string $target = "", string|int|array|null $data = null, int $permission = 0777):bool {

        # Declare result
        $result = false;

        # Path target
        $path_target = self::path($target);

        # Path target parent folder
        $path_folder = dirname($path_target);

        # Check folder target exists
        if(!is_dir($path_folder))

            # Create folder
            mkdir($path_folder, $permission, true);

        # Set result
        $result = file_put_contents($path_target, $data ?: "");

        # Return result
        return $result ? true : false;

    }

    /**
     * Create Directory
     * 
     * Create a folder
     * 
     * @param string $target Path of the folder
     * @return bool
     * @param int $permission Permission of the file
     */
    public static function createDirectory(string $target = "", int $permission = 0777):bool {

        # Set result
        $result = true;

        # Get path of target
        $target = self::path($target);

        # Check target exits
        if(!self::exists($target))

            # Create folder
            $result = mkdir($target, $permission, true);

        # Return result
        return $result;

    }

    /**
     * Is Empty
     * 
     * Check if folder is empty
     * 
     * @param string $path Path to process
     * @return bool
     */
    public static function isEmpty(string $path = ""):bool {

        # Set result
        $result = false;

        # Get real path
        $path = self::path($path);

        # Check folder exists
        if(!self::exists($path))

            return $result;

        # Check folder is empty
        $result = !(new \FilesystemIterator($path))->valid();

        # Return result
        return $result;

    }

    /**
     * Remove All
     * 
     * Remove recursively all content in folder
     * 
     * @source https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
     * 
     * @param string $path Path to remove
     * @return void
     */
    public static function removeAll(string $path = ""):void {

        # Get path
        $path = self::path($path);

        # Check path
        if(!$path)

            # Stop script
            return;

        # Remove 
        $rrmdir = function ($dir, $call) { 
            if (is_dir($dir)) { 
              $objects = scandir($dir);
              foreach ($objects as $object) { 
                if ($object != "." && $object != "..") { 
                  if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    $call($dir. DIRECTORY_SEPARATOR .$object, $call);
                  else
                    unlink($dir. DIRECTORY_SEPARATOR .$object); 
                } 
              }
              rmdir($dir); 
            } 
        };

        $rrmdir($path, $rrmdir);

    }

    /**
     * Resolve
     * 
     * Resolve real path / absolute path of the given path
     * 
     * @param string $path
     * @return string
     */
    public static function resolve(string $path = ""):string {

        # Set result
        $result = static::path($path);

        # Check path exits
        if(static::exists($result))

            # Set realpath
            $result = realpath($result);

        # Return result
        return $result;

    }

    /**
     * Download file online to tmp
     * 
     * Downloads an image from a given URL and saves it to a temporary file.
     * 
     * @param string $url The URL of the image to download.
     * @param string $tempFileName The prefix for the temporary file name.
     * @return string The path to the saved temporary file or an error message.
     */
    public static function downloadToTmp(string $url = "", string $tempFileName = "tmp"):string {

        # Set result
        $result = "";

        # Fetch the image content from the URL
        $content = file_get_contents($url);

        # Check content
        if($content === false)

            # New Exception
            throw new CrazyException(
                "Could not download the image from the URL: $url",
                500,
                [
                    "custom_code"   =>  "file-002",
                ]
            );

        # Create a temporary file in the system's temp directory
        $tempFile = tempnam(sys_get_temp_dir(), "__".($tempFileName ? $tempFileName : "tmp"));

        # Check tempfile
        if($tempFile === false)

            # New Exception
            throw new CrazyException(
                "Could not create a temporary file.",
                500,
                [
                    "custom_code"   =>  "file-003",
                ]
            );

        # Write the image content to the temporary file
        $result = file_put_contents($tempFile, $content);

        # Check file created
        if($result === false)

            # New Exception
            throw new CrazyException(
                "Could not write to the temporary file: $tempFile",
                500,
                [
                    "custom_code"   =>  "file-003",
                ]
            );

        # Return the path to the temporary file
        return $tempFile;

    }


    /** Public constant
     ******************************************************
     */

    /**
     * Correspondance Extension and Mimetype
     */
    public const EXTENSION_TO_MIMETYPE = [
        # Html
        "html"  =>  "text/html",
        "htm"   =>  "text/html",
        # Yml
        "yml"   =>  "text/yaml",
        "yaml"  =>  "text/yaml",
        # Json
        "json"  =>  "application/json",
        # Php
        "php"   =>  "text/php",
        # Csv
        "csv"   =>  "text/csv",
        # Xlsx
        "xlsx"  =>  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        # Pdf
        "pdf"   =>  "application/pdf",
        # Zip
        "zip"   =>  "application/zip",
        # Js
        "js"    =>  "application/javascript",
        ## Media
        # Jpg
        "jpg"   =>  "image/jpeg",
        # Jpeg
        "jpeg"  =>  "image/jpeg",
        # Png
        "png"   =>  "image/png",
        # WebP
        "webp"  =>  "image/webp",
        # Env
        "env"   =>  "text/plain",
        # TBC ...
    ];

    /* @var array MIMTYPE_TO_CLASS */
    public const MIMTYPE_TO_CLASS = [
        # Yaml
        "text/yaml"         =>  "CrazyPHP\\Library\\File\\Yaml",
        # Json
        "application/json"  =>  "CrazyPHP\\Library\\File\\Json",
    ];

}