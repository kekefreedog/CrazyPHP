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
 * Normalize
 * 
 * Returns the string in uppercase
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = function(a, options) {
    
    return (typeof a === "string") 
        ? a
            .normalize("NFD")                       // Decompose accents
            .replace(/[\u0300-\u036f]/g, "")       // Remove diacritics
            .replace(/[^a-zA-Z0-9 ]/g, "")         // Remove non-alphanumeric (excluding space)
            .trim()                                // Trim spaces at ends
            .replace(/\s+/g, "_")                  // Replace spaces with _
            .toLowerCase()                        // Optional: make lowercase
        : (
            Array.isArray(a)
                ? a.map(item => {
                    if (typeof item === 'string') {
                        return item            
                            .normalize("NFD")                       // Decompose accents
                            .replace(/[\u0300-\u036f]/g, "")       // Remove diacritics
                            .replace(/[^a-zA-Z0-9 ]/g, "")         // Remove non-alphanumeric (excluding space)
                            .trim()                                // Trim spaces at ends
                            .replace(/\s+/g, "_")                  // Replace spaces with _
                            .toLowerCase()                        // Optional: make lowercase : 
                        ;
                    }
                    return item;
                })
                : a
        )
    ;

}