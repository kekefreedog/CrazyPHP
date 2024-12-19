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
 * Is String
 * 
 * Return true if `value` is a string.
 *
 * ```handlebars
 * {{isString "foo"}}
 * <!-- results in:  'true' -->
 * ``
 * 
 * @param input array
 * @param options options
 * 
 * @return
 */
module.exports = function(input, options) { 
    return typeof input === "string" 
        ? options.fn(this) 
        : options.inverse(this)
};