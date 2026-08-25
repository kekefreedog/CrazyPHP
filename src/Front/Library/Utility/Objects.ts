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
import Arrays from "./Arrays";

/**
 * Arrays
 *
 * Methods for manage arrays
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Objects {

    /** Public static methods
     ******************************************************
     */

    /**
     * Array Filter
     * 
     * @param obj Input object
     * @param separator Separator to nested (default ".")
     * @return any
     */
    public static convertToNestedObject = (obj:Object, separator:string = '.'):any => {

        // Declare result
        const result = {};
    
        // Iteration obj
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                if (key.includes(separator)) {
                    const keys = key.split(separator);
                    let current = result;
    
                    for (let i = 0; i < keys.length; i++) {
                        if (i === keys.length - 1) {
                            current[keys[i]] = obj[key];
                        } else {
                            current[keys[i]] = current[keys[i]] || {};
                            current = current[keys[i]];
                        }
                    }
                } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                    result[key] = this.convertToNestedObject(obj[key], separator);
                } else {
                    result[key] = obj[key];
                }
            }
        }
    
        // Return result
        return result;
    }

    /**
     * Flatten
     * 
     * Convert nested structure to a flat structure with separator
     * 
     * @param obj - The object to flatten
     * @param prefix - The prefix for keys (default is an empty string)
     * @param separator - The separator used between keys (default is ".")
     * @returns A flattened object
     */
    public static flatten = (
        obj: Record<string, any> = {}, 
        prefix: string = '', 
        separator: string = '.'
    ): Record<string, any> => {

        const result: Record<string, any> = {};

        for (const [key, value] of Object.entries(obj)) {
            const newKey = prefix ? `${prefix}${separator}${key}` : key;

            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                Object.assign(result, Objects.flatten(value, newKey, separator));
            } else {
                result[newKey] = value;
            }
        }

        return result;

    }
    
    /**
     * Unflatten
     * 
     * Convert a flat object to a nested structure using a separator
     * 
     * @param obj - The flat object to unflatten
     * @param separator - The separator used in the keys (default is ".")
     * @returns A nested object
     */
    public static unflatten = (
        obj: Record<string, any> = {}, 
        separator: string = '.'
    ): Record<string, any> => {

        const result: Record<string, any> = {};

        for (const [key, value] of Object.entries(obj)) {
            const parts = key.split(separator);
            let temp = result;

            for (let i = 0; i < parts.length; i++) {
                const part = parts[i];

                if (!temp[part] || typeof temp[part] !== 'object') {
                    temp[part] = {};
                }

                if (i === parts.length - 1) {
                    temp[part] = value;
                } else {
                    temp = temp[part];
                }
            }
        }

        return result;

    }
    
    /**
     * Deep merge
     * 
     * Deeply merge multiple objects.
     * 
     * @param createIfNotExists - Whether to create a new entry if the key does not exist in the merged object
     * @param inputs - All objects to merge
     * @returns The merged object
     */
    public static deepMergeOld = (
        createIfNotExists: boolean = false,
        ...inputs: Array<Record<string, any>>
    ): Record<string, any> => {

        // Base object to start merging into
        const merged: Record<string, any> = {};

        // Iterate over each input object
        for (const obj of inputs) {
            for (const [key, value] of Object.entries(obj)) {
                if (typeof value === 'object' && !Array.isArray(value) && value !== null) {
                    // If the value is an object and the key exists in the merged object, merge them recursively
                    merged[key] = Objects.deepMerge(createIfNotExists, merged[key] || {}, value);
                } else {
                    // Handle createIfNotExists flag
                    if (createIfNotExists && merged[key] !== undefined) {
                        merged[key] = Array.isArray(merged[key])
                            ? [...merged[key], value] 
                            : [merged[key], value];
                    } else {
                        // Otherwise, assign the value directly
                        merged[key] = value;
                    }
                }
            }
        }

        return merged;
    }

    /**
     * Deep Merge Alt
     * 
     * Deeply merge multiple objects.
     * 
     * @param createIfNotExists - Whether to create a new entry if the key does not exist in the merged object
     * @param inputs - All objects to merge
     * @returns The merged object
     */
    public static deepMerge = (createIfNotExists: boolean, ...inputs: any[]): any => {
        const merge = (target: any, source: any): any => {
            if (source === null || source === undefined) {
                return target;
            }

            if (Array.isArray(source)) {
                if (!Array.isArray(target)) {
                    target = [];
                }
                source.forEach((item, index) => {
                    target[index] = merge(target[index], item);
                });
            } else if (typeof source === 'object') {
                if (typeof target !== 'object' || Array.isArray(target)) {
                    target = {};
                }
                for (const key of Object.keys(source)) {
                    if (target[key] === undefined && !createIfNotExists) {
                        target[key] = source[key];
                    } else {
                        target[key] = merge(target[key], source[key]);
                    }
                }
            } else {
                // Primitive types (string, number, boolean, etc.)
                if (createIfNotExists || target !== undefined) {
                    target = source;
                }
            }

            return target;
        };

        return inputs.reduce((acc, obj) => merge(acc, obj), {});
    };

    /**
     * Deep merge with separator
     * 
     * Deeply merge multiple objects with an optional separator for string values.
     * 
     * @param createIfNotExists - Whether to create a new entry if the key does not exist in the merged object
     * @param separator - String used to concatenate values for the same key
     * @param inputs - All objects to merge
     * @returns The merged object
     */
    public static deepMergeWithSeparator = (createIfNotExists: boolean, separator: string, ...inputs: any[]): any => {
        const merge = (target: any, source: any): any => {
            if (source === null || source === undefined) {
                return target;
            }

            if (Array.isArray(source)) {
                if (!Array.isArray(target)) {
                    target = [];
                }
                source.forEach((item, index) => {
                    target[index] = merge(target[index], item);
                });
            } else if (typeof source === 'object') {
                if (typeof target !== 'object' || Array.isArray(target)) {
                    target = {};
                }
                for (const key of Object.keys(source)) {
                    if (createIfNotExists || target[key] !== undefined) {
                        target[key] = merge(target[key], source[key]);
                    } else {
                        target[key] = source[key];
                    }
                }
            } else if (typeof source === 'string') {
                if (typeof target === 'string') {
                    target += separator + source;
                } else {
                    target = source;
                }
            } else {
                // For other primitive types, just assign the source value
                target = source;
            }

            return target;
        };

        return inputs.reduce((acc, obj) => merge(acc, obj), {});
    };


    /**
     * Sort By Key 
     * 
     * @param input 
     * @param orderKey 
     * @param maxOrder 
     * @returns {Record<string, Item>}
     */
    public static sortByKey = (
        input: Record<string, Item>, 
        orderKey: string = 'order', 
    ): Record<string, Item> => {
        // Convert the object to an array of key-value pairs
        const entries = Object.entries(input);

        // Filter, handle missing `orderKey`, and sort based on the specified `orderKey` property
        const sortedEntries = entries
            .filter(([, value]) => typeof value[orderKey] === 'number' && value[orderKey] > 0)
            .sort(([, a], [, b]) => a[orderKey] - b[orderKey]);

        // Convert the sorted array back to an object
        const sortedObject: Record<string, Item> = {};
        for (const [key, value] of sortedEntries) {
            sortedObject[key] = value;
        }

        return sortedObject;
    }

    /**
     * Set Value
     * 
     * Set Value into object
     * 
     * @param obj 
     * @param path 
     * @param value 
     */
    public static setValue =(
      obj:UnknownObject,
      path:string|string[],
      value:any,
      separator:null|string = "."
    ):void => {

        // Check path
        if(!Array.isArray(path))

            // Set path
            path = separator 
                ? path.split(separator)
                : [path]
            ;

        // Reduce
        path.reduce((current, key, index) => {
            // If this is the last key
            if(index === path.length - 1)

                // set the value
                current[key] = value;

            else
            
            // If the key doesn't exist or isn't an object
            if(!current[key] || typeof current[key] !== 'object')

                // Initialize it as an object
                current[key] = {};
            
            // Return
            return current[key];

        }, obj);

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

        // Return eqal from array
        return Arrays.equal(value1, value2);

    }

    /**
     * Difference
     * 
     * Get object that represents difference between multiple objects
     * 
     * @param objects:any[]
     * @returns {any}
     */
    public static difference(...objects:any[]):any {

        // Initialize the final result.
        let result: any = {};

        // Check that at least two objects were provided.
        if(objects.length >= 2) {

            // Recursive function used to compare values.
            const diff = (values: any[]): any => {

                // Initialize the result for the current level.
                let currentResult: any = {};

                // Check whether all values are objects.
                let allObjects = true;

                // Check whether at least one value is an object.
                let hasObject = false;

                // Check whether at least one value is an array.
                let hasArray = false;

                // Check whether all values are equal.
                let allEqual = true;

                // Compare every value against the first value.
                for(let i = 1; i < values.length; i++){

                    // Compare the current value with the first value.
                    if (JSON.stringify(values[i]) !== JSON.stringify(values[0])) {
                        
                        // At least one value is different.
                        allEqual = false;

                    }
                }

                // Check every value to determine its type.
                for (const value of values) {

                    // Check if the value is an object.
                    if (
                        value !== null &&
                        typeof value === "object" &&
                        !Array.isArray(value)
                    ) {

                        // At least one object exists.
                        hasObject = true;

                    } else {
                        
                        // At least one value is not an object.
                        allObjects = false;

                    }

                    // Check if the value is an array.
                    if (Array.isArray(value)) {

                        // At least one array exists.
                        hasArray = true;

                    }

                }

                // If all values are equal, there is no difference.
                if (allEqual) {

                    // Keep the current result empty.

                // If at least one value is an object, compare its properties.
                } else if (hasObject) {

                    // Create a set containing every property name.
                    const keys = new Set<string>();

                    // Iterate through all values.
                    for(const value of values) {

                        // Only inspect objects.
                        if (
                            value !== null &&
                            typeof value === "object" &&
                            !Array.isArray(value)
                        ) {

                            // Add all properties to the set.
                            Object.keys(value).forEach(key => {
                                keys.add(key);
                            });
                        }

                    }

                    // Compare every property.
                    for (const key of keys) {

                        // Get the value of this property from every object.
                        const childValues = values.map(value => {

                            // Return the property when the value is an object.
                            if (
                                value !== null &&
                                typeof value === "object" &&
                                !Array.isArray(value)
                            ) {
                            
                                return value[key];

                            }

                            // Return undefined when the value is not an object.
                            return undefined;

                        });

                        // Recursively calculate the difference.
                        const childDiff = diff(childValues);

                        // Add the difference if one was found.
                        if (
                            childDiff !== undefined &&
                            (
                                typeof childDiff !== "object" ||
                                Object.keys(childDiff).length > 0
                            )
                        )

                            // Set current
                            currentResult[key] = childDiff;

                    }

                } else
                // If at least one value is an array, store all array values.
                if(hasArray) {

                    // Store the values of the arrays.
                    currentResult = {
                        values
                    };

                // Otherwise, the values are primitive values.
                } else {

                    // Store all different primitive values.
                    currentResult = {
                        values
                    };
                }

                // Return the result of this recursive level.
                return currentResult;

            };

            // Calculate the complete difference.
            result = diff(objects);

        }

        // Return the final result.
        return result;
        
    }

    /**
     * Map Headers
     * 
     * @param headers
     * @param row 
     * @returns {Record<T[number], any}
     */
    public static mapHeaders = <T extends readonly string[]>(headers: T,row: Record<number, any>): Record<T[number], any> => {
    
        // Return result
        return Object.fromEntries(
            Object.entries(row).map(([key, value]) => [headers[Number(key)], value])
        ) as Record<T[number], any>;

    }

    /**
     * Sort Key
     * 
     * @param obj 
     * @param asc 
     * @returns 
     */
    public static sortKey = <T>(obj:Record<string,T>, asc:boolean = true):Record<string,T> => {

        // Set entries
        const entries = Object.entries(obj);

        // Set sort
        entries.sort((a, b) => (a[0] > b[0] ? (asc ? 1 : -1) : (asc ? -1 : 1)));

        // Set result
        const result: Record<string, T> = {};

        // Iteration entires
        for (let i = 0; i < entries.length; i++) {

            // Set key value
            const [key, value] = entries[i];

            // Set result
            result[key] = value;

        }

        // Return result
        return result;

    }
    
}

export type UnknownObject = Record<string, any>;

interface Item {
    [key: string]: any;
}