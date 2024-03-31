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
 * Color To Css Class
 * 
 * Convert color collection to css class
 * 
 * @param color Color collection with fill and text
 * @param inverse Inverse color order
 * @param options Not used
 * @return string
 */
module.exports = (color, inverse = false, type = "", options = {}) => {

  // Declare result
  let result = "";

  // Default colors
  const defaultFill = "grey darken-1";
  const defaultText = "white";

  // Check if color is not an object or is null
  if (typeof color !== 'object' || color === null) {
      return result;
  }

  // Set text and fill with defaults if not set
  const text = color.text ? color.text : defaultText;
  const fill = color.fill ? color.fill : defaultFill;

  // Convert inverse to boolean
  const isInverse = Boolean(inverse);

  function processColor(segment, mode, type, isText = false) {
    const segmentSuffix = typeof segment === 'string' && segment && type ? (segment.includes(" ") ? segment.replace(/ /g, `-${type} ${type}-`) : segment + `-${type}`) : segment;
    return typeof segmentSuffix === 'string' && segmentSuffix ? (segmentSuffix.includes(" ") ? `${mode}-${segmentSuffix}-${mode}` : `${mode}-${segmentSuffix}`) : segmentSuffix;
  }

  if (isInverse) {

    // Inverse logic
    result += processColor(fill, "light-mode", type) + " ";
    result += processColor(text, "light-mode", type, true) + " ";
    result += processColor(fill, "dark-mode", type, true) + " ";
    result += processColor(text, "dark-mode", type) + " ";

  } else {

    // Non-inverse logic
    result += processColor(fill, "light-mode", type, true) + " ";
    result += processColor(text, "light-mode", type) + " ";
    result += processColor(fill, "dark-mode", type) + " ";
    result += processColor(text, "dark-mode", type, true) + " ";

  }

  // Trim the result to remove any trailing space
  return result.trim() + " ";

}