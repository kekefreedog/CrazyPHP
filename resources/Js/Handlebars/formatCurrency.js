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
 * Format Currency
 * 
 * Returns the string in formatted as currency
 * 
 * @param value Number to use
 * @param currency "dollar" or "euro"
 * @return string
 */
module.exports = function(value, currency, options) {

    // Set result
    let result = value;

    // Convert value to nimber
    let number = Number(value);

    // Check currency
    if(!currency || typeof currency !== "string")

        // Set currency
        currency = "dollar";

    // Swith between currency given
    switch(currency.toLowerCase()) {
        // Case euro
        case 'euro':
            result = number
                // Ensure 2 decimal places
                .toFixed(2)
                // Replace decimal point with comma
                .replace('.', ',')
                // Add space as thousand separator
                .replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €'
            ;
            break;
        // Default case dollar
        case 'dollar':
        default:
            result = '$ ' + number
                // Ensure 2 decimal places
                .toFixed(2)
                // Replace decimal point with comma
                .replace('.', ',')
                // Add dot as thousand separator
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            ;
            break;
    }

    // Return result
    return result;

};