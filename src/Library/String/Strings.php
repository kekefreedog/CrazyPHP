<?php declare(strict_types=1);
/**
 * String
 *
 * Usefull class for manipulate strings
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\String;

/**
 * Form
 *
 * Methods for manipulate strings
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Strings {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Remove last string
     * 
     * Remove last string separated by the separator
     * 
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function removeLastString(string $input = "", string $separator = "."):string {

        # Set result
        $result = $input;

        # Find the last occurrence of the backslash character
        $lastSeparator = strrpos($input, $separator);
        
        # If no backslash found, return the original string
        if ($lastSeparator !== false)
        
            # Remove the last part of the string after the last backslash
            $result = substr($input, 0, $lastSeparator);
        
        # Return result
        return $result;

    }

    /**
     * Get last string
     * 
     * Get last string separated by the separator
     * 
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function getLastString(string $input = "", string $separator = "."):string {

        # Set result
        $result = $input;

        # Find the last occurrence of the backslash character
        $lastSeparator = strrpos($input, $separator);
        
        # If no backslash found, return the original string
        if ($lastSeparator !== false)
        
            # Get the last part of the string after the last backslash
            $result = substr($input, $lastSeparator + 1);
        
        # Return result
        return $result;

      }

}