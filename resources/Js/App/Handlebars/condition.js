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
 * Condition
 * 
 * Exemple of condition
 * 
 * @param a Value to compare
 * @param v Value to compare with
 * 
 * @return boolean
 */
module.exports = function(a, b, options) {
    
    // Return result
    return (a == b) 
        ? options.fn(this) 
        : options.inverse(this)
    ;
    
};