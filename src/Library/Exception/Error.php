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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Exception;

/**
 * Dependances
 */

use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use Exception;

/**
 * Header
 *
 * Methods for get header depending of type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Error{

    /** Public static methods
     ******************************************************
     */

    /**
     * From Exception
     * 
     * @param Exception $exception
     * @return array
     */
    public static function fromException(Exception $exception):array {

        # Set result
        $result = static::DEFAULT;

        # Iteration of options
        foreach([
            "message"   =>  "detail",
            "code"      =>  "code",
            "file"      =>  "file",
            "line"      =>  "line",
            "trace"     =>  "trace"
        ] as $name  =>  $target){

            # Get method
            $method = "get".ucfirst($name);

            # Check method
            if(method_exists($exception, $method)){

                # Get value
                $value = $exception->{$method}();

                # Set value in result
                $result[$target] = $value;

            }

        }

        # Get public folder
        $publicFolder = Config::getValue("App.public") ?: "public";

        # Get current dir of script
        $projectDir = str_replace($publicFolder, "", getcwd());

        # Clean path of project
        Arrays::stringReplaceRecursively($projectDir, "/", $result);

        # Check code
        if(
            !isset($result["code"]) || 
            !is_numeric($result["code"]) ||
            $result["code"] < 100
        )

            # Set code
            $result["code"] = static::DEFAULT["code"];

        # Return result
        return $result;

    }

    /** Public constant
     ******************************************************
     */

    /** @const array DEFAULT */
    public const DEFAULT = [
        "code"      =>  500,
        "type"      =>  "error",
        "detail"    =>  "Internal server error"
    ];

}