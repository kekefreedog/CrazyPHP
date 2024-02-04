/**
 * Handlebars Strings Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Uppercase
 * 
 * Returns the string in uppercase
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = (a, options) => (typeof a === "string") 
    ? a.toUpperCase() : 
    (
        Array.isArray(a)
            ? a.map(item => {
                if (typeof item === 'string') {
                    return item.toUpperCase();
                }
                return item;
            })
            : a
    )
;