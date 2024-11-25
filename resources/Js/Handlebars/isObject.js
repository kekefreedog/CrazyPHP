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
 * Is Object
 * 
 * Return true if value is an object.
 * 
 * @param any array
 * @param object options
 * 
 * @return
 */
module.exports = (object, options) => typeof object === "object" 
    ? options.fn(this) 
    : options.inverse(this)
;