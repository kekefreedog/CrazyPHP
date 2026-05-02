/**
 * Module
 *
 * Front TS Scrips for module
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Map
 *
 * Map utilites class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Map {

    /**
     * Get Methods Alias
     */
    public static getMethodsAlias():Record<string, typeof Map> {

        // Late static binding equivalent
        const cls = this;

        // Get all static property names (including non-enumerable)
        const props = Object.getOwnPropertyNames(cls);

        // Get result
        const result:Record<string, typeof Map> = {};

        // Iteration
        for(const name of props) {

            // Skip constructor & non-functions
            if (name === "length" || name === "name" || name === "prototype") continue;

            // Set descriptor
            const descriptor = Object.getOwnPropertyDescriptor(cls, name);

            // Check description
            if (!descriptor) continue;

            // Set value
            const value = descriptor.value;

            // Only methods (functions)
            if (typeof value !== "function") continue;

            // Only methods declared on THIS class (not inherited)
            if (!Object.prototype.hasOwnProperty.call(cls, name)) continue;

            // Add to result
            result[name] = cls[name];

        }

        // Return result
        return result;
        
    }

}