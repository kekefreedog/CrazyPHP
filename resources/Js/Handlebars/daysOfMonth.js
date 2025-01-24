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
 * Days of month
 * 
 * Handlebars helper to get all days of a given month and year 
 *
 * @param {number} year - The year (e.g., 2025).
 * @param {number} month - The month (1 for January, 12 for December).
 * @param {object} options - Handlebars options object.
 * @returns {string} - Rendered HTML of all dates.
 */
module.exports = function(year, month, options) {

    // New date
    const now = new Date();

    // Check year
    year = Number.isInteger(year) && year > 0 
        ? year 
        : now.getFullYear();

    // Check month
    month = Number.isInteger(month) && month >= 1 && month <= 12 
        ? month 
        : now.getMonth() + 1;

    // Adjust for JavaScript's 0-based months
    const adjustedMonth = month - 1;

    // Get number of days in the given month
    const daysInMonth = new Date(year, adjustedMonth + 1, 0).getDate();

    // Get days
    const days = [];

    // Iterate through all days in the month
    for (let day = 2; day <= daysInMonth + 1; day++) {
        // Create a date object
        const date = new Date(year, adjustedMonth, day);

        // Format the date as YYYY-MM-DD
        const formattedDate = date.toISOString().split('T')[0];

        // Push the formatted date to the array
        days.push(formattedDate);
    }

    // Return the list of days
    return days;

}