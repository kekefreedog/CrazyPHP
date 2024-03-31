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
 * Capitalize
 * 
 * Returns the string in capitalize
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = (str, options) => (typeof str === "string") 
    ? str.charAt(0).toUpperCase() + str.slice(1)
    : ""
;