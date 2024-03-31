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
 * In
 * 
 * Block helper that renders the block if an array has the
 * given `value`. Optionally specify an inverse block to render
 * when the array does not have the given value.
 * 
 * @param any array
 * @param any value
 * @param Object options
 * 
 * @return
 */
module.exports = (array, value, options) => {

    // Check array
    if(Array.isArray(array) && value && array.indexOf(value) > -1)

        // Return fn
        return options.fn(this);

    // Else 
    return options.inverse(this);

}