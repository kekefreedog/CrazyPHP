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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use Symfony\Component\Yaml\Yaml as YamlS;

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

}