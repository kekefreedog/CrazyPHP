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
 * Dependances
 */
import colorSuffix from "./colorSuffix";

/**
 * Color To Css Class
 * 
 * Convert color collection to css class
 * 
 * @param color Color collection with fill and text
 * @param inverse Inverse color order
 * @return string
 */
module.exports = (color, inverse = false, options = null) => {
    
    // Declare result
    let result = "";

    // Check if color is not an object
    if (typeof color !== "object" || color === null || Array.isArray(color)) {
      return color;
    }

    let defaultFill = "grey darken-1";
    let defaultText = "white";

    // Set text
    let text = color.hasOwnProperty("text") ? (color.text ? color.text : defaultText) : defaultText;

    // Set fill
    let fill = color.hasOwnProperty("fill") ? (color.fill ? color.fill : defaultFill) : defaultFill;

    // Process inverse
    let isInverse = Process.bool(inverse);

    if (!isInverse) {
      result += colorSuffix(fill, "", "light-mode") + " ";
      result += colorSuffix(text, "text", "light-mode") + " ";
      result += colorSuffix(fill, "text", "dark-mode") + " ";
      result += colorSuffix(text, "", "dark-mode") + " ";
    } else {
      result += colorSuffix(fill, "text", "light-mode") + " ";
      result += colorSuffix(text, "", "light-mode") + " ";
      result += colorSuffix(fill, "", "dark-mode") + " ";
      result += colorSuffix(text, "text", "dark-mode") + " ";
    }

    // Return result
    return result;
    
}