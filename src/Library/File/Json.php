<?php declare(strict_types=1);
/**
 * Json
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Form\Process;

/**
 * Json
 *
 * Methods for interacting with Composer files
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
    public static function check(mixed $string):bool {
        
        # Check if is string
        if(!is_string($string)) return false;

        # Decode string
        json_decode($string);

        # Return error or not
		return (json_last_error() == JSON_ERROR_NONE);
	}

    /** 
     * Open Json File
     * 
     * Open json file and return its content decodes
     * 
     * @param string $filename
     * @param bool $arrayFormat decode as array (else as object)
     */
    public static function open(string $filename = "", bool $arrayFormat = true):array|null{

        # Set result
        $result = null;

        # Check filename
        if(!$filename)
            return $result;
        
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
     * @return array
     */
    public static function set(string $path = "", array $values = []):array{

        # Set result
        $result = [];

        # Open json
        $old_value = $result = self::open($path);

        # Get result
        $result = self::_loopSet($values, $result);

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new json content in file
            file_put_contents($path, json_encode($result));

        # Return result
        return $result;

    }
    
    /**
     * Read value in json
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
        $result = self::_loopSet($values, $result, $createIfNotExists);

        # Check difference between old value & result
        if($old_value !== $result)

            # Put new json content in file
            file_put_contents($path, json_encode(json_encode($result)));

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
            file_put_contents($path, json_encode(json_encode($result)));

        # Return result
        return $result;

    }

    /** Public Static Methods | Loop
     ******************************************************
     */

    /**
     * Loop update
     * 
     * @private
     * 
     * @param any $input
     * @param any $output
     * @param bool $createIfNotExists
     * 
     * @return any
     */
    public static function _loopSet($input = [], $output = [], bool $createIfNotExists = false) {

            # Check input
            if(!is_array($input) || empty($input))

                # Set output
                $output = $input;

            # If filled array
            else

                # Iteration des inputs
                foreach($input as $key => $value)

                    # Check output is existing or create folder is allowed
                    if($output[$key] ?? false || $createIfNotExists)

                        # Continue loop
                        $output[$key] = self::_loopSet($value, $output[$key] ?? [], $createIfNotExists); 

            # Retourne output
            return $output;

        }

}