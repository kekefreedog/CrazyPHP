/**
 * Handlebars Array Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Last
 * 
 * Returns the last item, or last `n` items of an array or string.
 * Opposite of [first](#first).
 *
 * ```handlebars
 * <!-- var value = ['a', 'b', 'c', 'd', 'e'] -->
 *
 * {{last value}}
 * <!-- results in: ['e'] -->
 *
 * {{last value 2}}
 * <!-- results in: ['d', 'e'] -->
 *
 * {{last value 3}}
 * <!-- results in: ['c', 'd', 'e'] -->
 * ```
 *
 * @param any value
 * @param Object options
 * 
 * @return number
 */
module.exports = (value, n, options) => {

    // Check if array or string
    if(Array.isArray(value))

        // Return lenght
        return value.slice(-Math.abs(n));
    
    // Return 0
    return "";

}