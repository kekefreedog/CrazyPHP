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
 * Dependances
 */

/**
 * Expand Color Text
 * 
 * Convert color collection to css class
 * 
 * @param color Color collection with fill and text
 * @param options Not used
 * @return string
 */
module.exports = function(color, type = "", options = {}) {

    // Declare result
    let result = "";

    // Default colors
    const defaultFill = "grey darken-1";
    const defaultText = "white";

    // Check type
    if(typeof type === "object"){

        // Set options
        options = type;

        // Set type
        type = "";

    }

    // Check if color is not an object or is null
    if (typeof color !== 'object' || color === null) {
        return result;
    }

    // Set text and fill with defaults if not set
    const text = color.text ? color.text : defaultText;
    const fill = color.fill ? color.fill : defaultFill;

    // Process text with suffix then prefix for light mode
    const textSuffix = typeof text === 'string' && typeof type === 'string' && text && type ? (text.includes(" ") ? text.replace(/ /g, `-${type} ${type}-`) : text + `-${type}`) : text;
    result += typeof textSuffix === 'string' && textSuffix && "light-mode" ? (textSuffix.includes(" ") ? `light-mode-${textSuffix}-light-mode` : `light-mode-${textSuffix}`) : textSuffix;
    result += " ";

    // Process fill with suffix then prefix for dark mode
    const fillSuffix = typeof fill === 'string' && typeof type === 'string' && fill && type ? (fill.includes(" ") ? fill.replace(/ /g, `-${type} ${type}-`) : fill + `-${type}`) : fill;
    result += typeof fillSuffix === 'string' && fillSuffix && "dark-mode" ? (fillSuffix.includes(" ") ? `dark-mode-${fillSuffix}-dark-mode` : `dark-mode-${fillSuffix}`) : fillSuffix;
    result += " ";

    // Return result
    return result;

};