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
    public static filterByKey = (array:Array<any> = [], key:string, keyValue:string|Array<any>|null|Object) => array.filter(
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
        keyValue: string | Array<any> | null | Object
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
        return array.filter(obj => this.getNestedValue(obj, key.split('.')) !== value);
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