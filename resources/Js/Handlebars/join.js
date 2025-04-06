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
 * Join
 * 
 * Join all elements of array into a string, optionally using a
 * given separator.
 *
 * ```handlebars
 * <!-- array: ['a', 'b', 'c'] -->
 * {{join array}}
 * <!-- results in: 'a, b, c' -->
 *
 * {{join array '-'}}
 * <!-- results in: 'a-b-c' -->
 * ```
 * @param {Array, Object} `array`
 * @param {String} `separator` The separator to use. Defaults to `, `.
 * @return {String}
 * @api public
 */
module.exports = function(array, separator) {

    // Set intern array
    let internArray = [];

    // Check is string
    if (typeof array === 'string') 
        
        // Return string
        internArray = [array];
    
    // Check is object
    if(typeof array == "object" && !Array.isArray(array))

        // Push to array
        internArray = Object.values(array);

    // Check if array
    if(Array.isArray(array))

        // Set intern array
        internArray = array;

    // Check separator
    separator = typeof separator === "string" 
        ? separator 
        : ', '
    ;

    // Return result
    return internArray.join(separator);

};