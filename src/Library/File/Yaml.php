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
use Phpfastcache\Exceptions\PhpfastcacheRootException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlS;
use CrazyPHP\Exception\CrazyException;
use Psr\SimpleCache\CacheException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;

/**
 * Yaml
 *
 * Methods for interacting with Yaml files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Yaml{

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Is Convertible in Yaml
     * 
     * @param mixed $input
     * @return bool
     */
    public static function isConvertible(mixed $input):bool {

        # Declare Reponse
        $reponse = true;

        # Check if is string
        if(is_string($input) || is_numeric($input))

            # Set reponse
            $reponse = false;
        
        # Retorune reponse
        return $reponse;

    }

    /** 
     * Check if input is json
     * 
     * @param string $string
     * @return bool
     */
    public static function check(string $string = ""):bool {
        
        # Declare result
        $result = true;

        # Try to...
        try {

            # Parse string
            YamlS::parse($string);

        # If it doesn't work...
        } catch (ParseException $exception) {

            $result = false;

        }

        # Return result
        return $result;

	}

    /** 
     * Create yaml
     * 
     * Create yaml file with data inside
     * 
     * @param string $path Path of the yaml file
     * @param array $data Data to put on the json file
     * @param string $header Custom header file
     * @return array|null
     */
    public static function create(string $path = "", array $data = [], string $header = ""):array|null {
        
        # Check path
        if(empty($path))

            # Stop function
            return null;

        # Check path
        $path = File::path($path);

        # Create json
        if(
            file_put_contents(
                $path, 
                $header.YamlS::dump($data, 10)
            ) === false
        ){

            # Set explode
            $explode = explode("/", $path);

            # New Exception
            throw new CrazyException(
                "Yaml file \"".array_pop($explode)."\" can't be created...",
                403,
                [
                    "custom_code"    =>  "yaml-002",
                ]
            );

        }

        # Return data
        return $data;

	}


    /** 
     * Open
     * 
     * Open Yaml file and return its content decodes
     * 
     * @param string $filename
     * @return array
     */
    public static function open(string $filename = ""):array{

        # Set result
        $result = [];

        # Check filename
        if(!$filename)

            # Return result
            return $result;

        # Convert filename
        $filename = File::path($filename);
        
        # Check if file exist
        if(!file_exists($filename))

            # New Exception
            throw new CrazyException(
                "Yaml \"$filename\" doesn't exists...",
                500,
                [
                    "custom_code"    =>  "yaml-001",
                ]
            );

        # Parse the file with the normal YAML diagnostics
        $result = self::_parseDocument($filename);

        # Return result
        return $result;

    }

    /**
     * Open Cached YAML
     *
     * Cache the raw array in the local Files pool during configuration bootstrap.
     *
     * @param string $filename Discovered YAML file
     * @param string|null $directory Optional Files pool base directory
     * @return array
     */
    public static function openCached(string $filename, ?string $directory = null):array {

        # Keep the parsed snapshot available if a cache write fails
        $result = null;

        # Read the bytes once for both validation and parsing
        $source = @file_get_contents($filename);

        # Preserve diagnostics for missing or unreadable sources
        if($source === false){

            # Delegate file errors to the regular reader
            $result = self::open($filename);

        }else{

            # Keep one entry per source across edits
            $key = "Yaml.v1.".hash("sha256", realpath($filename) ?: $filename);

            # Detect changes even if the file size and modification time match
            $hash = hash("sha256", $source);

            # Load the document without consulting database configuration during bootstrap
            try{

                # Use the framework's Files pool for storage and eviction
                $cache = Cache::files($directory);

                # Look up the latest document stored for this path
                $entry = $cache->get($key);

                # Reuse only a raw array parsed from the same source bytes
                if(($entry["hash"] ?? null) === $hash && is_array($entry["document"] ?? null)){

                    # Leave reference resolution to the caller
                    $result = $entry["document"];

                }else{

                    # Parse the exact snapshot that was hashed
                    $result = self::_parseDocument($filename, $source);

                    # Replace the previous version in one cache entry
                    $cache->set($key, [
                        "document"    =>  $result,
                        "hash"        =>  $hash
                    ]);

                }

            }catch(PhpfastcacheRootException|CacheException $exception){

                # Parse on a failed read or retain the parsed result on a failed write
                $result ??= self::_parseDocument($filename, $source);

            }

        }

        # Return the raw document
        return $result;

    }

    /**
     * Has
     * 
     * Check if yaml has key
     * > Exemple : yaml has "titi.toto"
     * 
     * @param string $path
     * @return bool
     */
    public static function has(string $path = "", $key = ""):bool {

        # Set result
        $result = false;

        # Check path and key
        if($path && $key){

            # Open file
            $jsonContent = static::open($path);

            # Check jsoncontent
            if(!empty($jsonContent))

                # Check if key is in
                $result = Arrays::has($jsonContent, $key);

        }

        # return result
        return $result;

    } 
    
    /**
     * Set value in yaml
     *
     * @param string $path Path of the json file
     * @param array $values Values to put on json
     * @param bool $invertMerge Invert merge sort
     * @param bool $mergeValues Merge value allowed with old data
     * @return array
     */
    public static function set(string $path = "", array $values = [], $invertMerge = false, bool $mergeValues = true):array{

        # Set result
        $result = [];

        # Open json
        $old_value = $result = self::open($path);

        # Extract header
        $header = static::extractHeader($path);

        # Get result
        $result = 
            $mergeValues ?
                (
                    $invertMerge ?
                        Arrays::mergeMultidimensionalArrays(true, $values, $result) :
                            Arrays::mergeMultidimensionalArrays(true, $result, $values)
                ) :
                    $values;

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new json content in file
            file_put_contents(
                $path,
                $header.YamlS::dump($result, 10)
            );

        # Return result
        return (array) $result;

    }
    
    /**
     * Update value in yaml
     *
     * @param string $path Path of the yaml file
     * @param array $values Values to update on yaml
     * @param bool $createIfNotExists Create parameter in yaml if doesn't exists
     * @return array
     */
    public static function update(string $path = "", array $values = [], bool $createIfNotExists = false):array{

        # Set result
        $result = [];

        # Open yaml
        $old_value = $result = self::open($path);

        # Extract header
        $header = static::extractHeader($path);

        # Get result
        $result = Arrays::mergeMultidimensionalArrays($createIfNotExists, $result, $values);

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new yaml content in file
            file_put_contents(
                $path,
                $header.PHP_EOL.YamlS::dump($result, 10, 4)
            );

        # Return result
        return $result;

    }
    
    /**
     * Delete value in yaml
     *
     * @param string $path Path of the yaml file
     * @param string|array $parameters Parameters to delete in yaml
     * @return array
     */
    public static function delete(string $path = "", string|array $parameters = []):array{

        # Set result
        $result = [];

        # Open yaml
        $old_value = $result = self::open($path);

        # Check parameters
        if(!empty($parameters))

            # Check parameter is a string and is set in result
            if(is_string($parameters) && ( $result[$parameters] ?? false ))

                # Unset parameter
                unset($result[$parameters]);

            # Check parameters is an array
            elseif(is_array($parameters))

                # Iteration des parameters
                foreach($parameters as $parameter)

                    # Delete parameter
                    unset($result[$parameter]);

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new yaml content in file
            file_put_contents(
                $path,
                YamlS::dump($result)
            );

        # Return result
        return $result;

    }
    
    /**
     * File Exists 
     * 
     * Check if yaml file exists
     *
     * @param string $input Path of the json file
     * @return bool
     */
    public static function fileExists(string $input = ""):bool {

        # Set result
        $result = File::exists($input);

        # Return result
        return $result;

    }

    /**
     * Encode
     * 
     * Encode data to yaml
     * 
     * @param string|bool|null|array $input
     * @return string
     */
    public static function encode(string|bool|null|array $input = "") {

        # Set result
        $result = [];

        # Check input
        if(!is_array($input))

            # Set result
            $input = [
                $input
            ];

        # Encode result
        $result = YamlS::dump($input, 10);

        # Return result
        return $result;

    }


    /**
     * Extract header
     * 
     * Extrat header as 
     * ###
     * #
     * ###
     * in yaml file
     * 
     * @param string $filename
     * @return string
     */
    public static function extractHeader(string $filename = ""):string {

        # Get content of file
        $string = File::read($filename);

        # Set result
        $result = "";

        # Check string
        if($string){

            # Explide string
            $lines = explode("\n", $string);
        
            # Iteration of lines
            foreach ($lines as $line)

                # Check string start with #
                if (strpos(trim($line), '#') === 0)

                    # Push to result
                    $result .= "$line\n";

                # Stop iteration
                else

                    break;

        }
        
        # Return result
        return $result;

    }
    
    /** Private Static Methods
     ******************************************************
     */

    /**
     * Parse Document
     *
     * @param string $filename Path for file parsing and diagnostics
     * @param string|null $source Already-read source snapshot
     * @return array
     */
    private static function _parseDocument(string $filename, ?string $source = null):array {

        # Parse with the same flags for file and snapshot reads
        try{

            # Preserve the original array contract for empty documents and scalars
            $parsed = $source === null
                ? YamlS::parseFile($filename)
                : YamlS::parse($source)
            ;

            # Normalize non-array YAML
            $result = is_array($parsed) ? $parsed : [];

        }catch(ParseException $exception){

            # Attach the path when parsing a snapshot without the parser's normal filename context
            if($source !== null)

                # Keep the same source location in snapshot parse errors
                $exception->setParsedFile($filename);

            # Report malformed YAML through the framework's existing exception
            throw new CrazyException(
                PHP_EOL.$exception->getMessage().PHP_EOL."Content of  \"$filename\" isn't yaml...",
                500,
                [
                    "custom_code"    =>  "yaml-001"
                ]
            );

        }

        # Return the parsed document
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /* Path */
    public const FILE_EXT = "yml";
    

}
