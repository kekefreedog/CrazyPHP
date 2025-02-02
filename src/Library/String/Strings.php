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

    /**
     * Generate Secure Password
     * 
     * @param int $length
     * @return string
     */
    public static function generateSecurePassword($length = 12):string {

        # Define character pools
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $specialChars = '!@#$%^&*()-_=+<>?';
    
        # Ensure at least one character from each category
        $password = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specialChars[random_int(0, strlen($specialChars) - 1)]
        ];
    
        # Merge all character sets
        $allCharacters = $uppercase . $lowercase . $digits . $specialChars;
    
        # Generate the remaining characters
        for($i = 4; $i < $length; $i++) $password[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
    
        # Shuffle the password to ensure randomness
        shuffle($password);
    
        # Convert array to string and return
        return implode('', $password);

    }

}