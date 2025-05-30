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
 * Length
 * 
 * Returns the length of the given string or array.
 *
 * @param any value
 * @param Object options
 * 
 * @return number
 */
module.exports = function(value, options) {

    // Check if array or string
    if(typeof value === 'string' || Array.isArray(value))

        // Return lenght
        return value.length;

    else
    // Is object
    if(typeof value === "object" && value)

        // Check is object
        return Object.keys(value).length
    
    // Return 0
    return 0;

};