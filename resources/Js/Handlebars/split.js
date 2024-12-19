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
 * Split
 * 
 * Split string a by the given character b.
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = function(a, b, options){

    // Check if string
    if(typeof a === 'string' && typeof b === 'string' && a && b)
        
        // Return sum
        return a 
            ? a.split(b)
            : a
        ;

    // Return empty
    return '';

}