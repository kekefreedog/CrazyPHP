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
export default class Arrays {

    /** Public static methods
     ******************************************************
     */

    /**
     * Array Filter
     * 
     * @param array
     * @param key
     * @param keyValue
     * @return any
     */
    public static filterByKey = (array:Array<any> = [], key:string, keyValue:string|Array<any>|null|Object|number) => array.filter(
        (aEl) => aEl[key] == keyValue
    );

    /**
     * Array Filter Multi Dimensional
     * 
     * @param array
     * @param key
     * @param keyValue
     * @return any
     */
    public static filterByKeyMD = (
        array: Array<any> = [], 
        key: string, 
        keyValue: string|Array<any>|null|Object|number
    ) => array.filter(
        (aEl) => this.getNestedValue(aEl, key.split('.')) == keyValue
    );

    /**
     * Remove Objects By Key Value
     * 
     * Removes all objects from the array where the specified parameter is equal to the given value.
     * 
     * @param array The array of objects.
     * @param key The key to check in each object, supports nested keys with "." separator.
     * @param value The value to match for removal.
     * @return A new array with the objects removed.
     */
    public static removeObjectsByKeyValue = (array: Array<any>, key: string, value: string|number):Array<any> => {
        return array.filter(obj => this.getNestedValue(obj, key.includes('.') ? key.split('.') : [key]) !== value);
    }

    /**
     * Remove by key
     * 
     * @param arr 
     * @param key 
     * @param value 
     * @returns 
     */
    public static removeByKey = (arr: any[], key: string, value: any) => arr.filter(obj => obj[key] !== value);

    /**
     * Flattens a multi-dimensional object into a single-level object with a custom separator.
     * @param obj - The input object to flatten.
     * @param separator - The separator used to join keys.
     * @param parentKey - The base key for recursion (used internally).
     * @param result - The resulting flattened object (used internally).
     * @returns A flattened object.
     */
    public static flatten = (
        obj: {[key:string]:any},
        separator: string = '.',
        parentKey: string = '',
        result: {[key:string]:any} = {}
    ): {[key:string]:any} => {

        // Iteration entries
        for (const [key, value] of Object.entries(obj)) {

            // New key
            const newKey = parentKey ? `${parentKey}${separator}${key}` : key;

            // Check type
            if(typeof value === 'object' && value !== null && !Array.isArray(value))

                // Recursive call for nested objects
                Arrays.flatten(value, separator, newKey, result);

            else

                // Add non-object values directly
                result[newKey] = value;
        
        }

        // Return result
        return result;

    }

    /**
     * Unflattens a flattened object with a custom separator into a multi-dimensional object.
     * @param obj - The flattened input object.
     * @param separator - The separator used to split keys.
     * @returns An unflattened multi-dimensional object.
     */
    public static unflatten = (obj:{[key:string]:any}, separator: string = '.'):{[key:string]:any} => {
        
        // Declare result
        const result:{[key:string]:any} = {};

        // Iteration entries
        for (const [key, value] of Object.entries(obj)) {

            // Declare keys
            const keys = key.split(separator);

            // Set current
            let current = result;

            // Iteration
            for (let i = 0; i < keys.length; i++) {

                // Set key
                const k = keys[i];

                // Check lenght
                if (i === keys.length - 1)

                    // Set current
                    current[k] = value;

                else

                    // Set current
                    current[k] = current[k] || {};

                    // Set current
                    current = current[k];

            }

        }

        return result;

    }

    /**
     * Equals 
     * 
     * Check if two array are equals
     * 
     * @param value1
     * @param value2
     * @returns {boolean}
     */
    public static equal = (value1:any, value2:any):boolean => {

        // Direct match (including primitives)
        if(value1 === value2) return true;
        
        // If either is not an object or is null, they must be strictly equal
        if(typeof value1 !== "object" || typeof value2 !== "object" || value1 === null || value2 === null) return false; 
      
        // If are arrays
        if(Array.isArray(value1) && Array.isArray(value2)){

            // Different lengths
            if (value1.length !== value2.length) return false; 
            
            // Recursively compare elements
            return value1.every((el, i) => Arrays.equal(el, value2[i]));

        }
      
        // If object
        if(Object.prototype.toString.call(value1) === "[object Object]" && Object.prototype.toString.call(value2) === "[object Object]"){

            // Get key 1
            const keys1 = Object.keys(value1);

            // Get key 2
            const keys2 = Object.keys(value2);
      
            // Different number of keys
            if (keys1.length !== keys2.length) return false; 

            // Recursively compare object properties
            return keys1.every((key) => Arrays.equal(value1[key], value2[key]));

        }
      
        // If one is an array and the other isn't, or different types
        return false;
        
      }

    /** Private static methods
     ******************************************************
     */

    /**
     * Get Nested Values
     * 
     * Helper function to get nested value from an object
     * 
     * @param obj The object to traverse
     * @param keys Array of keys representing the path to the nested value
     * @return The nested value or undefined if any key is not found
     */
    private static getNestedValue = (obj: any, keys: string[]): any => {
        return keys.reduce((acc, key) => acc && acc[key], obj);
    };
    
}