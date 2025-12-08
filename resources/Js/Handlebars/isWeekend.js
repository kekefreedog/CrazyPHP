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
    if(input){
            
        // Set result
        result = (new Date(`${input}`).getDay() == 0 || new Date(`${input}`).getDay() == 6);

    }

    // Return result
    return result
        ? options.fn(this) 
        : options.inverse(this)
    ;
    
};