/**
 * Handlebars String Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Replace
 * 
 * Replace all occurrences of substring `a` with substring `b`.
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = (str, a, b, options) => (typeof str === "string" && typeof a === "string" && typeof b === "string") ? str.split(a).join(b) : str;