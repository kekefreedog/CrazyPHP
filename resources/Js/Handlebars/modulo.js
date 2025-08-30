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
 * Add
 * 
 * Return the modulo of `a` % `b`.
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = function(a, b, options) {

    // Check if number
    if(isNumber(a) && isNumber(b))

        // Return sum 
        return Number(a) % Number(b);

    // Return empty
    return 1;

}