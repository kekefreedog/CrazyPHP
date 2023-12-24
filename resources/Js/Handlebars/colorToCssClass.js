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

  // Declare color suffix function (do same thing than ./colorSuffix.js)
  colorSuffix = (a, suffix) => (a && suffix) ? (a.includes(" ") ? a.trim().replace(" ", `-${suffix} ${suffix}-`) : a + `-${suffix}`) : a;

  // Declare color prefix function (do same thing than ./colorPrefix.js)
  colorPrefix = (a, prefix) => (a && prefix) ? (a.includes(" ") ? `${prefix}-${a.trim()}-${prefix}-` : `${prefix}-${a.trim()}`) : a;
    
  // Declare result
  let result = "";

  // Check if color is not an object
  if (typeof color !== "object" || color === null || Array.isArray(color))
      
    // Return result
    return result;

  // Default color
  let defaultFill = "grey darken-1";
  let defaultText = "white";

  // Set text
  let text = color.hasOwnProperty("text") ? (color.text ? color.text : defaultText) : defaultText;

  // Set fill
  let fill = color.hasOwnProperty("fill") ? (color.fill ? color.fill : defaultFill) : defaultFill;

  // Process inverse
  let isInverse = Boolean(inverse);

  // If is not inverse
  if (!isInverse) {

    // Set fill as fill for light mode
    result += colorPrefix(fill, "", "light-mode") + " ";

    // Set text as text for light mode
    result += colorPrefix(colorSuffix(text, "text"), "light-mode") + " ";

    // Set fill as text for dark mode
    result += colorSuffix(colorSuffix(fill, "text"), "dark-mode") + " ";

    // Set text as fill for dark mode
    result += colorPrefix(text, "dark-mode") + " ";

  // If is inverse
  } else {

    // Set fill as text for light mode
    result += colorPrefix(colorSuffix(fill, "text"), "light-mode") + " ";

    // Set text as fill for light mode
    result += colorPrefix(text, "light-mode") + " ";

    // Set fill as fill for dark mode
    result += colorPrefix(fill, "dark-mode") + " ";

    //Set text as fill for light mode
    result += colorPrefix(colorSuffix(text, "text"), "dark-mode") + " ";
      
  }

  // Return result
  return result.trim() + " ";
    
}