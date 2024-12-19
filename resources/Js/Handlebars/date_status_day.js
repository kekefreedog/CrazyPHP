/**
 * Handlebars Array Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Date Status Day
 * 
 * Define if date given is in the past, present or futur based on day
 * Date given YYYY-MM-DD
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = function(date, options) {

    // Check date given
    if (typeof date !== 'string' || isNaN(Date.parse(date)))
        
        // Return the input if it is not a valid date string
        return date;

    // Date instance
    const givenDate = new Date(date);

    // Today instance
    const today = new Date();

    // Set time to 00:00:00 to only compare dates
    today.setHours(0, 0, 0, 0);

    // In past
    if(givenDate < today)

        //Date is in the past
        return -1;

    else 
    // Future
    if(givenDate > today)

        // Date is in the future
        return 1;
    
    else

        // Date is today
        return 0;

};