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
 * JSON stringify
 * 
 * Stringify an object using JSON.stringify.
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = function(a, b, options) {

    // Return result
    return (typeof a === "object") 
        ? JSON.stringify(a) 
        : "Invalid object"
    ;

};