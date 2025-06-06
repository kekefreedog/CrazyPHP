/**
 * Handlebars Comparaison Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Gt
 * 
 * Block helper that renders a block if a is greater than b.
 * If an inverse block is specified it will be rendered when falsy. You may optionally use the compare="" hash argument for the second value.
 * 
 * @param a Value to compare
 * @param b Value to compare with
 * 
 * @return boolean
 */
module.exports = function(a, b, options) {
    
    // Return result
    return (a > b) 
        ? options.fn(this) 
        : options.inverse(this)
    ;
    
};