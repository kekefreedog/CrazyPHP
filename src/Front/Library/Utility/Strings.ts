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

    /**
     * Is Json
     * 
     * Check if string given is json
     * @param input
     * @returns {boolean}
     */
    public static isJson = (input:string):boolean => {

        // Set result
        let result = false;

        // Check input
        if(input){

            // Try
            try {

                // Parse json
                JSON.parse(input);

            // Catch exception
            } catch (e) {

                // Return false
                result = false;
            }

            // Set result
            result = true;

        }

        // Return result
        return result;

    }

    /**
     * Decode Html
     * 
     * HTML Entity Decoder
     * 
     * @param html
     * @returns {string}
     */
    public static decodeHTML = (html:string):string => {

        // Set dom parser
        const parser = new DOMParser();

        // Parse from string
        const dom = parser.parseFromString(html, 'text/html');

        // Return text result
        return dom.documentElement.textContent ?? "";

    }

    /**
     * Get Data Attriute Name
     * 
     * @param input
     * @returns {string}
     */
    public static getDataAttributeName = (input:string):string => {

        // Set result
        let result = "";

        // Check input
        if(input.startsWith("data-")){

            // Set result
            result = input
                // Remove "data-" prefix
                .replace(/^data-/, '')
                // Process oher value
                .replace(/-([a-z])/g, (_, letter) => letter.toUpperCase())
            ;

        }

        // Return result
        return result;

    }

    /**
     * Clean
     * 
     * Clean a string by replacing accents, punctuation, spaces, etc.
     * Equivalent of the PHP version using preg_replace.
     * 
     * @param input The string to clean
     * @returns A cleaned, lowercase version of the input
     */
    public static clean = (input: string = ''):string => {

        // Set rules
        const rules: [RegExp, string][] = [
            [/[áàâãªä]/gu, 'a'],
            [/[ÁÀÂÃÄ]/gu, 'a'],
            [/[íìîï]/gu, 'i'],
            [/[ÍÌÎÏ]/gu, 'i'],
            [/[éèêë]/gu, 'e'],
            [/[ÉÈÊË]/gu, 'e'],
            [/[óòôõºö]/gu, 'o'],
            [/[ÓÒÔÕÖ]/gu, 'o'],
            [/[úùûü]/gu, 'u'],
            [/[ÚÙÛÜ]/gu, 'u'],
            [/ç/gu, 'c'],
            [/Ç/gu, 'c'],
            [/ñ/gu, 'n'],
            [/Ñ/gu, 'n'],
            [/\s+/g, '_'],
            [/–/g, '-'],                    // UTF-8 hyphen to ASCII hyphen
            [/[’‘‹›‚]/gu, ' '],             // Single quotes variations to space
            [/[“”«»„]/gu, ' '],             // Double quotes variations to space
            [/[']/gu, ''],                  // Apostrophes to nothing
            [/["“”‘’„«»]/gu, ''],           // Quotes to nothing
            [/[()]/g, ''],                  // Remove round brackets
            [/(_-_)/g, '_'],                // Normalize special underscores
        ];
    
        // Set result
        let result = input;
    
        // Iteration utiles
        for (const [pattern, replacement] of rules) {

            // Replace result
            result = result.replace(pattern, replacement);

        }
    
        // Return result
        return result.toLowerCase();
        
    }

    /**
     * Increment Character
     * 
     * @param input 
     * @returns {string}
     */
    public static incrementCharacter = (input:string) => {

        // Return next character
        let result = String.fromCharCode(input.charCodeAt(0) + 1);

    }

    /**
     * Increment Character (multi dimension)
     * 
     * Increment Character String (like Excel columns: A, B, ..., Z, AA, AB, ...)
     *
     * @param input
     * @returns {string}
     */
    public static incrementCharacterMd = (input: string): string => {
        const A_CODE = 'A'.charCodeAt(0);
        const Z_CODE = 'Z'.charCodeAt(0);

        let chars = input.toUpperCase().split('').map(char => char.charCodeAt(0) - A_CODE);
        
        for (let i = chars.length - 1; i >= 0; i--) {
            if (chars[i] < 25) {
                chars[i]++;
                for (let j = i + 1; j < chars.length; j++) {
                    chars[j] = 0;
                }
                return chars.map(n => String.fromCharCode(A_CODE + n)).join('');
            }
        }

        // If all characters are 'Z', prepend 'A' and set rest to 'A'
        return 'A'.repeat(chars.length + 1);
    };

  

}