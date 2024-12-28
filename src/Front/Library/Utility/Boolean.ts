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
 * Boolean
 *
 * Methods for manage boolean
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Boolean {

    /** Public static methods
     ******************************************************
     */

    /**
     * Check
     * 
     * Check in given input of any type is true or false
     * 
     * @param input - The input string.
     * @returns A string with duplicate lines removed.
     */
    public static check = (value:any):boolean => {

        // Set result
        let result:boolean = true;

        // Check for falsy values or specific strings you want to treat as false
        if(
            value === false ||
            value === null ||
            value === undefined ||
            value === 0 ||
            value === "" ||
            value === "false" ||
            value === "0" ||
            value === "off"
        )
            // set result false
            result = false;
        
        // Return result
        return result;

    }

}