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
 * Is
 * 
 * Block helper that renders a block if index gien is the last of the list given
 * 
 * @param index Value to compare
 * @param list Value to compare with
 * 
 * @return boolean
 */
module.exports = (index, list, options) => (
    typeof index === "number" && 
    (
        (
            Array.isArray(list) && 
            index === (list.length - 1)
        ) ||
        (
            typeof list === "object" &&
            index === (Object.keys(list).length - 1)
        )
    )
) 
    ? options.fn(this) 
    : options.inverse(this)
;