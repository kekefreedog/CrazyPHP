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
 * Date To Local Format (Month Year)
 * 
 * Return date to local format Vendredi 13 Janvier
 * 
 * @param input
 * @param local ()
 * 
 * @return string
 */
module.exports = function(year, month, day, options) {
    
    const date = new Date();
    date.setFullYear(year);
    date.setMonth(month);
    date.setDate(day);
    const dyear = date.getFullYear().toString(); // Get last two digits of the year
    const dmonth = (date.getMonth()).toString().padStart(2, '0'); // Months are 0-indexed, pad with zero
    const dday = date.getDate().toString().padStart(2, '0'); // Pad with zero if needed
    return `${dyear}-${dmonth}-${dday}`;

};