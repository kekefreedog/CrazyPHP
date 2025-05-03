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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use MessagePack\BufferUnpacker;
use MessagePack\Packer;
use Throwable;
use Closure;

/**
 * MessagePack
 *
 * Methods for interacting with MessagePack files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MessagePack {

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Is Convertible in Json
     * 
     * @return bool
     */
    public static function isConvertible($input):bool {

        # Check not is resource  or Closure
        if (is_resource($input) || $input instanceof Closure)

            # Return false
            return false;
    
        # Check is array
        if(is_array($input) || is_object($input))

            # Iteration input
            foreach ((array) $input as $item)

                # Check is convertible
                if (!static::isConvertible($item))

                    # Retrun false
                    return false;
    
        # Return true
        return true;

    }

    /** 
     * Check if input is message pack
     * 
     * @param mixed $string
     * @return bool
     */
    public static function check(mixed $string = ""):bool {
        
        # If PECL extension is available
        if(function_exists('msgpack_unpack')) {

            # Try
            try {

                # Unpack
                msgpack_unpack($string);

                # Return true
                return true;

            # Catch error
            } catch (Throwable $e) {

                # Return false
                return false;

            }
        }else
        // If using rybakit/msgpack
        if(class_exists(BufferUnpacker::class)) {

            # Try
            try {

                # New unpacker
                $unpacker = new BufferUnpacker();
                
                # New reset
                $unpacker->reset($string);

                # Try to decode
                $unpacker->unpack(); 

                # Return true
                return true;

            # Catch error
            } catch (Throwable $e) {

                # Return false
                return false;

            }
        }

        # throw new \RuntimeException('No MessagePack unpacker available.');
        return false;

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
    public static function create(string $path = "", array $data = []):array|null {
        
        # Check path
        if(empty($path))

            # Stop function
            return null;

        # Check path
        $path = File::path($path);
        
        # Encode the data
        try {

            # Get binary
            $binary = self::encode($data);

        # Catch error
        } catch (Throwable $e) {

            # Return null
            return null;

        }

        # Attempt to write the binary data to the file
        $created = File::create($path, $binary);

        # Check created
        if(!$created)

            # Return null
            return null;

        # Return result
        return [
            'path'      =>  $path,
            'size'      =>  filesize($path),
            'data'      =>  $data,
            'encoded'   =>  true,
        ];

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
    public static function open(string $path = "", bool $arrayFormat = true):array|null{

        # Set result
        $result = null;

        # Set path
        $path = File::path($path);

        # Check path
        if(File::exists($path)){

            # Get binary
            $binary = File::read($path);

            # Check binary
            if ($binary !== false)

                # Decode binary
                $result = self::decode($binary);

        }

        # Return result
        return $result;
        
    }

    /**
     * Has
     * 
     * Check if json has key
     * > Exemple : json has "titi.toto"
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
     * Set value in json
     *
     * @param string $path Path of the json file
     * @param array $values Values to put on json
     * @param bool $invertMerge Invert merge sort
     * @param bool $mergeValues Merge value allowed with old data
     * @return array
     */
    public static function set(string $path = "", array $values = [], bool $invertMerge = false, bool $mergeValues = true):array{

        # Set result
        $result = [];

        # Get path
        $path = File::path($path);

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
            self::create($path, $result);

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
            self::create($path, $result);

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
            self::create($path, $result);

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
     * Encode data to message pack
     * 
     * @param mixed $input Input to encode in Json
     * @return string
     */
    public static function encode(mixed $input = ""):string {

        # Set result
        $result = "";

        # Check msgpack_pack
        if(function_exists('msgpack_pack')) {

            # Pack
            $result = msgpack_pack($input);

        }else
        # Check packer
        if(class_exists(Packer::class)) {

            # New pack
            $packer = new Packer();

            # Pack input
            return $packer->pack($input);
        }

        # Return result
        return $result;

    }

    /**
     * Decode
     * 
     * Decode json
     * 
     * @param string $jsonString
     * @param bool $decodeAsObject Decode as object, else as array
     * @return mixed
     */
    public static function decode(string $input, bool $decodeAsObject = false):mixed {

        # Set result
        $result = "";

        # Check msgpack_pack
        if(function_exists('msgpack_unpack')){

            # Set result
            $result = msgpack_unpack($input);

        }else
        # Check unpacker
        if(class_exists(BufferUnpacker::class)) {

            # New unpacker
            $unpacker = new BufferUnpacker();

            # Reset
            $unpacker->reset($input);

            # Unpack
            $result = $unpacker->unpack();
        }

        # Return result
        return $result;

    }
    
    /** Public constants
     ******************************************************
     */

    /* Path */
    public const FILE_EXT = "msgpack";

}