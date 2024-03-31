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
    
}