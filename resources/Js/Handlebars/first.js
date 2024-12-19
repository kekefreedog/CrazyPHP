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
 * First
 * 
 * Returns the first item, or first `n` items of an array.
 *
 * ```handlebars
 * {{first "['a', 'b', 'c', 'd', 'e']" 2}}
 * <!-- results in: '["a", "b"]' -->
 * ```
 *
 * @param any value
 * @param Object options
 * 
 * @return number
 */
module.exports = function(value, n, options) {

    // Check if array or string
    if(Array.isArray(value))

        // Return lenght
        return value.slice(0, n);
    
    // Return 0
    return "";

}