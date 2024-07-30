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
 * Multiply
 * 
 * Multiply a with b
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = (a, b, options) => {

    // Check if array or string
    if((!isNaN(parseFloat(a)) && isFinite(a)) && (!isNaN(parseFloat(b)) && isFinite(b)))

        // Return length
        return Number($a) * Number($b);

    else

        // Return string
        return `${a}*${b}`;

}