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
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlS;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;

/**
 * Yaml
 *
 * Methods for interacting with Yaml files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Yaml{

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Is Convertible in Yaml
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
        )

            # New Exception
            throw new CrazyException(
                "Yaml file \"".array_pop(explode("/", $path))."\" can't be created...",
                403,
                [
                    "custom_code"   =>  "yaml-002",
                ]
            );

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
        $result = null;

        # Check filename
        if(!$filename)
            return $result;
        
        # Check if file exist
        if(!file_exists($filename))

            # New Exception
            throw new CrazyException(
                "Yaml \"$filename\" doesn't exists...",
                500,
                [
                    "custom_code"   =>  "yaml-001",
                ]
            );

        # Try to...
        try {

            # Reading YAML files and parse content
            $result = YamlS::parseFile($filename);
            
        # If it doesn't work...
        } catch (ParseException $exception) {

            # New Exception
            throw new CrazyException(
                ( 
                    $exception->getMessage() ?
                        PHP_EOL.$exception->getMessage().PHP_EOL :
                            ""
                ).
                "Content of  \"$filename\" isn't yaml...",
                500,
                [
                    "custom_code"   =>  "yaml-001",
                ]
            );

        }

        # Return result
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
            $input = [$input];

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
    

}