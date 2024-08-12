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

use CrazyPHP\Library\Array\Arrays;

/**
 * PythonCollection
 *
 * Methods for parsing Python Collection in PHP
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class PythonCollection {

    /**
     * Check
     * 
     * Check if input is a Python collection (dict or list)
     *
     * @param string $input
     * @return bool
     */
    public static function check(string $input):bool {

        # Set result
        $result = false;

        # Trim any leading or trailing whitespace
        $trimmedInput = trim($input);

        # Check for a dictionary (e.g., {'key': 'value'})
        $isDict = strpos($trimmedInput, '{') === 0 && strrpos($trimmedInput, '}') === (strlen($trimmedInput) - 1);

        # Check for a list (e.g., ['value1', 'value2'])
        $isList = strpos($trimmedInput, '[') === 0 && strrpos($trimmedInput, ']') === (strlen($trimmedInput) - 1);

        # A valid Python collection should also contain at least one colon (for dict) or comma (for both)
        $hasDictColon = $isDict && strpos($trimmedInput, ':') !== false;
        $hasListComma = $isList && strpos($trimmedInput, ',') !== false;

        # Ensure the string is not empty inside the braces
        $isNotEmpty = strlen($trimmedInput) > 2;

        # Return true if it is either a dictionary or a list
        $result = ($isDict && $hasDictColon && $isNotEmpty) || ($isList && $hasListComma && $isNotEmpty);

        # Return result
        return $result;

    }

    /**
     * Encode
     * 
     * Encode a PHP variable into a Python-like collection string
     *
     * @param mixed $input
     * @return string
     */
    public static function encode($input): string {

        # Set result
        $result = "";

        # If is null
        if(is_null($input))

            # Set result
            $result = 'None';
        
        else
        # If bool
        if(is_bool($input))

            # Set result
            $result = $input ? 'True' : 'False';

        else
        # Is numeric
        if(is_numeric($input))

            # Set result
            $result = (string)$input;
        
        else
        # If is string
        if(is_string($input)){

            // Escape single quotes and backslashes within strings
            $escapedStr = str_replace(['\\', '\''], ['\\\\', '\\\''], $input);

            # Set result
            $result = "'$escapedStr'";
        
        }else
        # If array
        if(is_array($input) && Arrays::areAllKeysNumeric($input)){

            # Set element
            $elements = array_map([self::class, 'encode'], $input);

            # Set result
            $result = '[' . implode(', ', $elements) . ']';

        }else
        # Is if array
        if(is_object($input) || is_array($input)) {
            
            # Set entries
            $entries = [];
            
                # Iteration input
            foreach($input as $key => $value) {

                # Ensure keys are properly formatted and escaped
                $escapedKey = str_replace(['\\', '\''], ['\\\\', '\\\''], (string)$key);
                $pythonKey = "'$escapedKey'";

                # Get python value
                $pythonValue = self::encode($value);

                # Set entries
                $entries[] = "$pythonKey: $pythonValue";

            }

            # Set result
            $result = '{' . implode(', ', $entries) . '}';

        }

        # Return result
        return $result;

    }

    /**
     * Decode a Python-like collection string into a PHP variable
     *
     * @param string $input
     * @return mixed
     */
    public static function decode(string $input) {

        # Check
        if (self::check($input)) {

            # Placeholder for \\'
            $input = str_replace("\\'", "@@@@@@@@", $input);

            // Escape double quotes inside single-quoted values
            $input = preg_replace_callback(
                '/\'[^\']*\"[^\']*\'/',
                function ($matches) {
                    // Escape double quotes inside single-quoted strings
                    return str_replace('"', '\\"', $matches[0]);
                },
                $input
            );

            // Convert Python-like collection string to JSON-compatible string
            $jsonString = preg_replace([
                '/([{,]\s*)\'([^\']+)\':\s*\'([^\']*)\'/',   // Handles key-value pairs
                '/([{,]\s*)\'([^\']+)\':\s*([^\'{][^,}]*)/', // Handles other key-value pairs
                '/\'/',                                       // Replaces single quotes with double quotes
                '/\\\\/',                                     // Escapes backslashes
            ], [
                '$1"$2": "$3"',
                '$1"$2": $3',
                '"',
                '\\\\'
            ], $input);

            # Replace placeholder by '
            $jsonString = str_replace("@@@@@@@@", "'", $jsonString);

            // Handle Python None, True, and False values
            $jsonString = str_replace(
                ['None', 'True', 'False'],
                ['null', 'true', 'false'],
                $jsonString
            );

            // Parse the string to JSON
            return json_decode($jsonString, true);
        }

        # Return null
        return null;

    }

}