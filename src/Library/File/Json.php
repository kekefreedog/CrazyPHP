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
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;

/**
 * Json
 *
 * Methods for interacting with Json files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Json{

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Is Convertible in Json
     * 
     * @return bool
     */
    public static function isConvertible($input):bool {

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
     * @param mixed $string
     * @return bool
     */
    public static function check(string $string = ""):bool {
        
        # Check if is string
        if(!is_string($string))
            return false;

        # Decode string
        json_decode($string);

        # Return error or not
		return (json_last_error() == JSON_ERROR_NONE);

	}

    /** 
     * Create json
     * 
     * Create json file with data inside
     * 
     * @param string $path Path of the json file
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
                $header.json_encode(
                    $data, 
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES
                )
            ) === false
        )

            # New Exception
            throw new CrazyException(
                "Json file \"".array_pop(explode("/", $path))."\" can't be created...",
                403,
                [
                    "custom_code"   =>  "json-002",
                ]
            );

        # Return data
        return $data;

	}

    /** 
     * Open Json File
     * 
     * Open json file and return its content decodes
     * 
     * @param string $filename
     * @param bool $arrayFormat decode as array (else as object)
     * @return array
     */
    public static function open(string $filename = "", bool $arrayFormat = true):array{

        # Set result
        $result = null;

        # Check filename
        if(!$filename)
            return $result;

        # Check tokken in filename
        $filename = File::path($filename);
        
        # Check if file exist
        if(!file_exists($filename))

            # New Exception
            throw new CrazyException(
                "Json \"$filename\" doesn't exists...",
                500,
                [
                    "custom_code"   =>  "json-001",
                ]
            );

        # Get content of file
        $content = file_get_contents($filename);

        # Check if content is json
        if(!Json::check($content))

            # New Exception
            throw new CrazyException(
                "Content of  \"$filename\" isn't json...",
                500,
                [
                    "custom_code"   =>  "json-001",
                ]
            );

        # Decode content
        $result = json_decode($content, $arrayFormat);

        # Return result
        return $result;
        
    }
    
    /**
     * Set value in json
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
                json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
            );

        # Return result
        return (array) $result;

    }
    
    /**
     * Update value in json
     *
     * @param string $path Path of the json file
     * @param array $values Values to update on json
     * @param bool $createIfNotExists Create parameter in json if doesn't exists
     * @return array
     */
    public static function update(string $path = "", array $values = [], bool $createIfNotExists = false):array{

        # Set result
        $result = [];

        # Open json
        $old_value = $result = self::open($path);

        # Get result
        $result = Arrays::mergeMultidimensionalArrays($createIfNotExists, $result, $values);

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new json content in file
            file_put_contents($path, json_encode(json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)));

        # Return result
        return $result;

    }
    
    /**
     * Delete value in json
     *
     * @param string $path Path of the json file
     * @param string|array $parameters Parameters to delete in json
     * @return array
     */
    public static function delete(string $path = "", string|array $parameters = []):array{

        # Set result
        $result = [];

        # Open json
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

            # Put new json content in file
            file_put_contents($path, json_encode(json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)));

        # Return result
        return $result;

    }
    
    /**
     * File Exists 
     * 
     * Check if json file exists
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
     * Encode data to json
     * 
     * @param string|bool|null|array $input Input to encode in Json
     * @param bool $prettyPrint Give a pretty print result
     * @return string
     */
    public static function encode(string|bool|null|array $input = "", bool $prettyPrint = false):string {

        # Set result
        $result = [];

        # Check input
        if(!is_array($input))

            # Set result
            $input = [$input];

        # Encode result
        $result = $prettyPrint ? json_encode($input, JSON_PRETTY_PRINT) : json_encode($input);

        # Return result
        return $result;

    }


    /**
     * Extract header
     * 
     * Extrat header as 
     * /*
     *  *
     *  *\/
     * in yaml file
     * 
     * @param string $filename
     * @return string
     */
    public static function extractHeader(string $filename = ""):string {

        # Set result
        $result = "";

        # Return result
        return $result;

    }

}