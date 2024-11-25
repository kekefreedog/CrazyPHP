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
module.exports = (value, options) => {

    // Check if array or string
    if(typeof value === 'number')

        // Return lenght
        return parseFloat(value.toFixed(1));
    
    // Return 0
    return value;

}