/**
 * Handlebars Strings Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * To Hex Color
 * 
 * Returns color string in hex
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = function(input, options) {  
  
  try {
    let hex;

    // Case: rgb(r,g,b) or raw "r,g,b"
    if (
      input.startsWith("rgb") ||
      input.match(/^\s*\d+\s*,\s*\d+\s*,\s*\d+\s*$/)
    ) {
      const rgbValues = input
        .replace(/[^\d,]/g, "") // strip non-digits/commas
        .split(",")
        .map((num) => parseInt(num.trim(), 10));

      if (
        rgbValues.length !== 3 ||
        rgbValues.some((v) => isNaN(v) || v < 0 || v > 255)
      ) {
        return "";
      }

      hex =
        "#" +
        rgbValues
          .map((v) => v.toString(16).padStart(2, "0"))
          .join("")
          .toUpperCase();
    } else if (input.startsWith("#")) {
      // Case: hex
      hex = input.toUpperCase();

      // Expand shorthand #FFF → #FFFFFF
      if (hex.length === 4) {
        hex =
          "#" +
          hex
            .slice(1)
            .split("")
            .map((c) => c + c)
            .join("");
      }

      if (!/^#[0-9A-F]{6}$/i.test(hex)) {
        return "";
      }
    } else {
      return "";
    }

    return hex;
  } catch {
    return "";
  }

};