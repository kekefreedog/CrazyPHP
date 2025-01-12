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
 * Date To Local Format
 * 
 * Return date to local format Vendredi 13 Janvier
 * 
 * @param input
 * @param local ()
 * 
 * @return string
 */
module.exports = function(input, locale, options) {
    
    // Set result
    let result = input;

    // Check input
    if(typeof input === "string" && input && typeof locale === "string" && locale){

        // convert to date
        const date = new Date(input);

        // Invalid date fallback
        if(!isNaN(date)){

            // Set options
            const options = { weekday: 'long', day: 'numeric', month: 'long' };
        
            // Set result
            result = date.toLocaleDateString(locale, options);

        }

}

};