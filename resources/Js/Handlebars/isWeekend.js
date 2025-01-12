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
 * Is Weekend
 * 
 * Returns true if value (2025-01-05) is the weekend
 * 
 * @param input like 2025-01-12
 * @return boolean
 */
module.exports = function(input, options) {

    // Set result
    let result = false;

    // Check date
    if(typeof input === "string" && input){

        // Set date
        const date = new Date(inputDate);

        // Get day in week 0 = Sunday, 6 = Saturday
        const day = date.getDay();

        // Check if weekend
        if(day === 0 || day === 6)

            // Set result
            result = true;

    }

    // Return result
    return result
        ? options.fn(this) 
        : options.inverse(this)
    ;
    
};