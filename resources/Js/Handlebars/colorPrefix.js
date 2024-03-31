/**
 * Handlebars Crazy Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Color Prefix
 * 
 * Add color suffix for convert material color to specific color propoerty
 * 
 * @param a Value to compare
 * @param prefix Prefix to set
 * @param options Not used
 * @return string
 */
module.exports = (a, prefix) => (a && prefix) ? (a.includes(" ") ? `${prefix}-${a.trim()}-${prefix}-` : `${prefix}-${a.trim()}`) : a;