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
    
}

interface Item {
    [key: string]: any;
}