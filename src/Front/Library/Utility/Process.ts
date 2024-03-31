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
 * Process
 *
 * Methods for process data
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Process {

    /**
     * Space Before Capital
     * 
     * Insert a space before each capital letter, like this:
     * - HelloWorld <=> Hello World
     * 
     * > Exception if it is the first character
     * 
     * @param input The input string
     * @return {string} The resulting string with spaces before capital letters
     */
    public static spaceBeforeCapital(input:string):string {

        // Set result
        let result:string = input;

        // Check input
        if(input)
           
            // Using JavaScript's string replace method with a regex pattern
            result = input.replace(/(?<!^)([A-Z])/g, ' $1');

        // Return result
        return result;

    }

    /**
     * Capitalize
     * 
     * @param input
     * @return {string}
     */
    public static capitalize = (input:string):string => {
        
        // Capitalize    
        return input 
            ? input[0].toUpperCase() + input.slice(1)
            : input
        ;
    
    }

}