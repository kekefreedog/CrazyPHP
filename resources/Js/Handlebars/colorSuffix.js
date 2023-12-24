/**
 * Handlebars Crazy Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Color Suffix
 * 
 * Add color suffix for convert material color to specific color propoerty
 * 
 * @param a Value to compare
 * @param suffix Value to compare with
 * @param options Not used
 * @return string
 */
module.exports = (a, suffix, options) => (a && suffix) ? (a.includes(" ") ? a.trim().replace(" ", `-${suffix} ${suffix}-`) : a + `-${suffix}`) : a;