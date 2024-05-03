/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Date Time
 *
 * Class for manipulate time
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class DateTime {

    /** Publis static methods
     ******************************************************
     */

    /**
     * Get Utc string
     * 
     * @param date 
     * @returns {string}
     */
    public static toUTCString = (date:Date):string => {

        // Define pad
        const pad = (n:number) => n < 10 ? '0' + n : n;
    
        // Return
        return date.getUTCFullYear() + '-' +
               pad(date.getUTCMonth() + 1) + '-' + // Months are 0-indexed
               pad(date.getUTCDate()) + ' ' +
               pad(date.getUTCHours()) + ':' +
               pad(date.getUTCMinutes()) + ':' +
               pad(date.getUTCSeconds()) + ' UTC'
        ;

    }

    /**
     * To Iso String
     * 
     * @param date 
     * @returns {string}
     */
    public static toISOString = (date:Date):string => {

        // Define pad
        const pad = (n:number) => n < 10 ? '0' + n : n;
    
        // Return
        return date.getUTCFullYear() +
            '-' + pad(date.getUTCMonth() + 1) +
            '-' + pad(date.getUTCDate()) +
            'T' + pad(date.getUTCHours()) +
            ':' + pad(date.getUTCMinutes()) +
            ':' + pad(date.getUTCSeconds()) +
            '.' + String((date.getUTCMilliseconds() / 1000).toFixed(3)).slice(2, 5) +
            'Z';
        ;

    }

    /**
     * To YYYYMMDD format
     * 
     * To YYYY/MM/DD format
     * 
     * @param date
     * @returns 
     */
    public static toYYYYMMDDFormat = (date:Date):string => {

        // Gets the full year (e.g., 2024)
        const year = date.getFullYear();

        // Months are 0-indexed; pad with leading zero
        const month = String(date.getMonth() + 1).padStart(2, '0');
        
        // Pad with leading zero if necessary
        const day = String(date.getDate()).padStart(2, '0'); 
    
        // Return result
        return `${year}/${month}/${day}`;

    }

    /**
     * Get Next Day
     * 
     * Return in format YYYY/MM/DD
     * 
     * @param weekday
     * @returns string
     */
    public static getNextDay = (weekday:1|2|3|4|5|6|7) => {

        // Check week day
        if (weekday < 1 || weekday > 7)

            // New error
            throw new Error("Invalid weekday number. Please enter a number between 1 (Monday) and 7 (Sunday).");
    
        // New date
        const today = new Date();
        const todayDayOfWeek = today.getUTCDay(); // Sunday - 0, Monday - 1, etc.
        const daysUntilNext = (weekday - todayDayOfWeek + 7) % 7; // Calculate days until the next desired weekday
    
        // Set the date to the next desired weekday
        today.setUTCDate(today.getUTCDate() + daysUntilNext);
    
        // Format the date as YYYY/MM/DD
        const year = today.getUTCFullYear();
        const month = (today.getUTCMonth() + 1).toString().padStart(2, '0'); // Months are zero-indexed
        const day = today.getUTCDate().toString().padStart(2, '0');
    
        // Return date
        return `${year}/${month}/${day}`;
    }

    /**
     * Get Today Date YYMMDD
     * 
     * @returns 
     */
    public static getTodayDateYYMMDD = ():string => {
        const date = new Date();
        const year = date.getFullYear().toString().slice(-2); // Get last two digits of the year
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed, pad with zero
        const day = date.getDate().toString().padStart(2, '0'); // Pad with zero if needed
        return `${year}${month}${day}`;
    }

    /**
     * Get Today Date YYYYMMDD
     * 
     * @returns 
     */
    public static getTodayDateYYYYMMDD = ():string => {
        const date = new Date();
        const year = date.getFullYear().toString(); // Get last two digits of the year
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed, pad with zero
        const day = date.getDate().toString().padStart(2, '0'); // Pad with zero if needed
        return `${year}${month}${day}`;
    }

    /**
     * Convert Date Fromat
     * 
     * To D/M/YYYY
     * 
     * @param dateStr 
     * @returns 
     */
    public static convertDateFormat = (dateStr:string):string => {

        // Set result
        let result = "";

        // Check date
        if(!dateStr)

            // Return result
            return result;

        // Parse the input string into a Date object
        const date = new Date(dateStr);
    
        // Format the date as D/M/YYYY
        // Note: getMonth() returns 0 for January, 1 for February, etc., so we add 1.
        result = `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
        
        // Return result
        return result;
    }

    /**
     * Get Today Date 
     * 
     * As YYYY-MM-DD
     * 
     * @param separator By default "-"
     * @returns {string}
     */
    public static getTodayDate = (separator:string = "-"):string => {

        const today = new Date();
        const year = today.getFullYear(); // Gets the full year (e.g., 2024)
        const month = (today.getMonth() + 1).toString().padStart(2, '0'); // Month is 0-indexed, add 1 to get 1-12
        const day = today.getDate().toString().padStart(2, '0'); // Day of the month
    
        return `${year}${separator}${month}${separator}${day}`;

    }

}