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
 * Date Status Week
 * 
 * Define if date given is in the past, present or futur based on week
 * Date given YYYY-MM-DD
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = (date, options) => {

    // Check date given
    if (typeof date !== 'string' || isNaN(Date.parse(date)))

        // Return the input if it is not a valid date string
        return date;

    // Date instance for given date
    const givenDate = new Date(date);

    // Set hours
    givenDate.setHours(0, 0, 0, 0);

    // Today instance
    const today = new Date();

    // Set hours
    today.setHours(0, 0, 0, 0);

    // Calculate start of the current week (Sunday)
    const startOfWeek = new Date(today);

    // Define start of the week
    startOfWeek.setDate(today.getDate() - today.getDay());

    // Calculate end of the current week (Saturday)
    const endOfWeek = new Date(startOfWeek);

    // Define end of the week
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    // Compare given date with start and end of the current week
    if(givenDate < startOfWeek)

        // Date is before the current week
        return -1;

    else
    if (givenDate > endOfWeek)

        // Date is after the current week
        return 1;

    else

        // Date is in the current week
        return 0;

}
