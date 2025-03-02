/**
 * Handlebars Strings Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Process
 * 
 * Exemple of custome helpers
 * 
 * @param a Input to process
 * 
 * @return string
 */
module.exports = function(a, options) {
    
    // Return result
    return (typeof a === "string") 
        ? a ? `${a} process` : "process"
        : a

};