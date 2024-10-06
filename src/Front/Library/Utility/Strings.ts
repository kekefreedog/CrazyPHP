/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Arrays
 *
 * Methods for manage arrays
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Strings {

    /** Public static methods
     ******************************************************
     */

    /**
     * Remove duplicate lines from a string.
     * 
     * @param input - The input string.
     * @returns A string with duplicate lines removed.
     */
    public static removeDuplicateLines = (input: string):string => {

        // Split the input string into an array of lines
        const lines = input.split('\n');

        // Use a Set to filter out duplicate lines
        const uniqueLines = Array.from(new Set(lines));

        // Join the unique lines back into a single string
        return uniqueLines.join('\n');

    }

    /**
     * UCFirst
     * 
     * Capitalize the first character of a string.
     * 
     * @param str - The input string.
     * @returns The string with the first character capitalized.
     */
    public static ucfirst = (str:string):string => {

        // Check string given
        if(!str) 

            // Stop method
            return str;
        
        // Return result
        return str.charAt(0).toUpperCase() + str.slice(1);
    
    }

    /**
     * UCWords
     * 
     * Capitalize the first character of each word in a string.
     * 
     * @param str - The input string.
     * @returns The string with the first character of each word capitalized.
     */
    public static ucwords = (str:string):string => {

        // Check string given
        if(!str) 

            // Stop method
            return str;

        // Return result
        return str
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
        ;
    }

    /**
     * kebabToCamel
     * 
     * Convert a kebab-case string to PascalCase or camelCase
     * @param input - The kebab-case string to convert
     * @param capitalizeFirst - If true, the first letter will be uppercase (PascalCase), otherwise lowercase (camelCase)
     * @returns The converted string
     */
    public static kebabToCamel = (input:string, capitalizeFirst:boolean = false):string => input
        ? input
            .split('-') // Split by hyphen
            .map((word, index) => {
                if (index === 0 && !capitalizeFirst) {
                    // For camelCase, first word should be lowercase
                    return word.toLowerCase();
                }
                // Capitalize the first letter of each word
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            })
            .join('') // Join the parts together
        : input
    ;

}