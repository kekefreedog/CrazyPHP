<?php declare(strict_types=1);
/**
 * Exception
 *
 * Manipulate Exception
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Exception;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Header
 *
 * Methods for get Http Status Code
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class HttpStatusCode {

    /** Public static methods
     ******************************************************
     */

    /**
     * Get All
     * 
     * Get All Http Status Code
     * 
     * @return array
     */
    public static function getAll():array {

        # Set result
        $result = static::_retrieveCollection();

        # Return result
        return $result;

    }

    /**
     * Get
     * 
     * Get Http Status Code by code
     * 
     * @param $code Http Status Code
     * @param $option Option to merge with result
     * @return array
     */
    public static function get(int $code, array $option = []):array {

        # Set result
        $result = [];

        # Get collection
        $collection = static::_retrieveCollection();

        # Check code in collection
        if(array_key_exists($code, $collection)){
            
            # Set result
            $result = $collection[$code];

            # Check option
            if(!empty($option))

                # Merge values
                $result = Arrays::mergeMultidimensionalArraysBis(false, $result, $option);

        # Else get default
        }else

            # Get default
            $result = static::getDefault($option);

        # Return result
        return $result;

    }

    /**
     * Get Default
     * 
     * Get the default Http Status Code
     * 
     * @param array $mixOption Mix option given on the default array
     * @return array
     */
    public static function getDefault(array $mixOption = []):array {

        # Set result
        $result = [];

        # Get collection
        $collection = static::_retrieveCollection();

        # Set result
        $result = $collection["default"] ?? [];

        # Check result
        if(empty($result))

            # New Exception
            throw new CrazyException(
                "Default does not exist in Http Status Code collection...",
                500,
                [
                    "custom_code"   =>  "http_status_code-020",
                ]
            );

        # Check mix option
        if(!empty($mixOption)){

            # Mege
            $result = Arrays::mergeMultidimensionalArraysBis(false, $result, $mixOption);

        }

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Retrieve Collection
     * 
     * Retrieve Http Status Code collection
     * 
     * @return array
     */
    private static function _retrieveCollection():array {

        # Set result
        $result = [];

        # Get path
        /* $filePath = (Env::has("phpunit_test") && Env::get("phpunit_test"))
            ? "@crazyphp_root/resources/Yml/HttpStatusCode.yml"
            : "@crazyphp_root/resources/Yml/HttpStatusCode.yml"
        ; */
        $filePath = "@crazyphp_root/resources/Yml/HttpStatusCode.yml";

        # Get file content
        $fileContent = File::open($filePath);

        # Check file content
        if(!is_array($fileContent) || !isset($fileContent["HttpStatusCode"]))

            # New Exception
            throw new CrazyException(
                "Http Status Code collection file is not valid...",
                500,
                [
                    "custom_code"   =>  "http_status_code-010",
                ]
            );

        # Get collection
        $result = $fileContent["HttpStatusCode"];

        # Return result
        return $result;

    }

}