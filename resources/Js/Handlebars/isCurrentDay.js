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
 * Is Current Day
 * 
 * Returns true if value (2025-01-05) is the current day
 * 
 * @param input like 2025-01-12
 * @return boolean
 */
module.exports = function(input, options) {

    // Return result
    return typeof input === "string" && input && inputDate === (new Date().toISOString().split('T')[0])
        ? options.fn(this) 
        : options.inverse(this)
    ;
    
};