/**
 * Handlebars Array Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Add
 * 
 * Return the sum of `a` plus `b`.
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = (a, b, options) => {

    // Check if number
    if(isNumber(a) && isNumber(b))

        // Return sum 
        return Number(a) + Number(b);

    // Check if string
    if(typeof a === 'string' && typeof b === 'string')
        
        // Return sum
        return a + b;

    // Return empty
    return '';

}