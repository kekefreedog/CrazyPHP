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
    
    // Initialize the result with the input value
    let result = input;

    // Check if input and locale are valid strings
    if (typeof input === "string" && input && typeof locale === "string" && locale) {
        
        // Convert input to a Date object
        const timestamp = new Date(input);

        // Define weekday and month names for supported locales
        const locales = {
            en_US: {
                weekdays: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
            },
            fr_FR: {
                weekdays: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"]
            }
            // Add more locales as needed
        };

        // Fallback to 'en_US' if the locale is not supported
        const localeData = locales[locale] || locales["en_US"];

        // Get the day of the week, day of the month, and month name
        const weekday = localeData.weekdays[timestamp.getDay()];
        const day = timestamp.getDate(); // Day of the month without leading zeros
        const month = localeData.months[timestamp.getMonth()]; // Months are zero-based

        // Combine and format the result
        result = `${weekday} ${day} ${month}`;
    }

    // Return the result
    return result;

};