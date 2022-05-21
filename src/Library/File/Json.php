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

    /** Is Convertible in Json
     * 
     * @return bool
     */
    public static function isConvertible($input):bool {

        # Declare Reponse
        $reponse = true;

        # Check if is string
        if(is_string($input) || is_numeric($input)):

            # Set exception
            # throw new Exception("You are trying to convert a non-array element to Json", 500);

            # Set reponse
            $reponse = false;

        endif;
        
        # Retorune reponse
        return $reponse;

    }

    /** Check if input is json
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

    /** Open Json File
     * Open json file and return its content decodes
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
            return $result;

            # Set exception
            # throw new Exception("Json file \"$filename\" doesn't exists.", 404);

        # Get content of file
        $content = file_get_contents($filename);

        # Check if content is json
        if(!Json::check($content))
            return $result;

            # Set exception
            # throw new Exception("Content of \"$filename\" is not a valid Json.", 500);

        # Decode content
        $result = json_decode($content, $arrayFormat);

        # Return result
        return $result;
        
    }

}