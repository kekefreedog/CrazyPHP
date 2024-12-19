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
 * Color Hex Random
 * 
 * Return random color hex format
 * 
 * @param option.
 */
module.exports = function(value, options) {

    // Generate a random integer between 0 and 16777215 (0xFFFFFF)
    const randomColor = Math.floor(Math.random() * 16777216);
    
    // Convert to hexadecimal and pad with leading zeros if necessary
    return `#${randomColor.toString(16).padStart(6, '0')}`;

}