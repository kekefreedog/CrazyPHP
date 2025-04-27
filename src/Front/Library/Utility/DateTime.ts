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
     * @param separator
     * @returns 
     */
    public static toYYYYMMDDFormat = (date:Date, separator:string = "/"):string => {

        // Gets the full year (e.g., 2024)
        const year = date.getFullYear();

        // Months are 0-indexed; pad with leading zero
        const month = String(date.getMonth() + 1).padStart(2, '0');
        
        // Pad with leading zero if necessary
        const day = String(date.getDate()).padStart(2, '0'); 
    
        // Return result
        return `${year}${separator}${month}${separator}${day}`;

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
     * Get Today Date YYYY-MM-DD
     * 
     * @returns 
     */
    public static getTodayDateYYYY_MM_DD = ():string => {
        const date = new Date();
        const year = date.getFullYear().toString(); // Get last two digits of the year
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed, pad with zero
        const day = date.getDate().toString().padStart(2, '0'); // Pad with zero if needed
        return `${year}-${month}-${day}`;
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
    
    /**
     * Get the first date of a week based on an offset from the current week.
     * 
     * @param weekOffset - The week offset from the current week (0 for this week, -1 for previous week, 1 for next week).
     * @param format - Optional format for the returned date. Default is 'YYYY-MM-DD'.
     * @returns {string} The first date of the specified week in the given format.
     */
    public static getFirstDateOfWeek = (weekOffset: number, format:'YYYY-MM-DD'|'YYYYMMDD'|'MM/DD/YY'|'DD/MM/YYYY' = 'YYYY-MM-DD'):string => {

        // Get current date
        const today = new Date();

        // Get day number 0 (Sunday) to 6 (Saturday)
        const currentDay = today.getDay();

        // Days since the last Monday
        const daysSinceMonday = (currentDay + 6) % 7;

        // Calculate the date of the last Monday
        const lastMonday = new Date(today);
        lastMonday.setDate(today.getDate() - daysSinceMonday);

        // Calculate the first date of the target week
        const targetMonday = new Date(lastMonday);
        targetMonday.setDate(lastMonday.getDate() + weekOffset * 7);

        // Format the date
        return DateTime.formatDate(targetMonday, format);

    }

    /**
     * Is Date Current Week
     * 
     * Check if a given date is in the current week.
     * 
     * @param date - The date to check.
     * @returns True if the date is in the current week, false otherwise.
     */
    public static isDateInCurrentWeek = (date:Date|string):boolean => {

        // check date
        if(typeof date == "string")

            // Set date
            date = new Date(date);

        const today = new Date();
        const dayOfWeek = today.getDay(); // 0 (Sunday) to 6 (Saturday)
        const startOfWeek = new Date(today);
        const endOfWeek = new Date(today);

        // Calculate the start of the current week (Monday)
        startOfWeek.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));

        // Calculate the end of the current week (Sunday)
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        // Set hours to 0 to compare only date parts
        startOfWeek.setHours(0, 0, 0, 0);
        endOfWeek.setHours(23, 59, 59, 999);
        
        // Compare the given date with the start and end of the current week
        return date >= startOfWeek && date <= endOfWeek;

    }

    /**
     * Is Date Next Week
     * 
     * Check if a given date is in the next week.
     * 
     * @param date - The date to check.
     * @returns True if the date is in the next week, false otherwise.
     */
    public static isDateInNextWeek = (date: Date | string): boolean => {

        // Check date
        if (typeof date === "string") {
            date = new Date(date);
        }

        const today = new Date();
        const dayOfWeek = today.getDay(); // 0 (Sunday) to 6 (Saturday)
        const startOfNextWeek = new Date(today);
        const endOfNextWeek = new Date(today);

        // Calculate the start of the next week (Monday)
        startOfNextWeek.setDate(today.getDate() + (dayOfWeek === 0 ? 1 : 8 - dayOfWeek));

        // Calculate the end of the next week (Sunday)
        endOfNextWeek.setDate(startOfNextWeek.getDate() + 6);

        // Set hours to 0 to compare only date parts
        startOfNextWeek.setHours(0, 0, 0, 0);
        endOfNextWeek.setHours(23, 59, 59, 999);

        // Compare the given date with the start and end of the next week
        return date >= startOfNextWeek && date <= endOfNextWeek;

    }

    /**
     * Is Valide Date
     * 
     * Validate a simple date string
     * 
     * @param dateString 
     * @returns {boolean}
     */
    public static isValidDate = (dateString:string):boolean => {

        // Return bool
        return !isNaN(Date.parse(dateString));

    }

    /**
     * Format a Date object into a string based on the given format.
     * 
     * @param date - The Date object to format.
     * @param format - The format string :
     * - YYYY-MM-DD
     * - YYYYMMDD
     * - MM/DD/YY
     * - DD/MM/YYYY
     * @returns {string} The formatted date string.
     */
    public static formatDate = (date:Date|string, format:'YYYY-MM-DD'|'YYYYMMDD'|'MM/DD/YY'|'DD/MM/YYYY'):string => {

        // Check date
        if(typeof date === "string")

            // Set date
            date = new Date(date);

        // Get full year
        const year = date.getFullYear();

        // Get month
        const month = ('0' + (date.getMonth() + 1)).slice(-2);

        // Get day
        const day = ('0' + date.getDate()).slice(-2);

        // Swtich
        switch (format) {
            case 'YYYY-MM-DD':
                return `${year}-${month}-${day}`;
            case 'YYYYMMDD':
                return `${year}${month}${day}`;
            case 'MM/DD/YY':
                return `${month}/${day}/${year.toString().slice(-2)}`;
            case 'DD/MM/YYYY':
                return `${day}/${month}/${year}`;
            default:
                throw new Error('Unsupported date format');
        }

    }

    /**
     * Get All Days Between
     * 
     * @param startDate 
     * @param endDate 
     * @returns 
     */
    public static getAllDaysBetween = (startDate:Date, endDate:Date):Date[] => {

        // Prepare dates
        const dates: Date[] = [];
    
        // Reset the time to midnight for both dates to avoid time discrepancies
        const resetTime = (d: Date) => {
            d.setHours(0, 0, 0, 0);
            return d;
        };
    
        let currentDate = resetTime(new Date(startDate)); // Clone startDate and reset time
        const lastDate = resetTime(new Date(endDate));    // Clone endDate and reset time
    
        // Loop through and add each day to the dates array
        while (currentDate <= lastDate) {
            dates.push(new Date(currentDate)); // Push a clone of currentDate
            currentDate.setDate(currentDate.getDate() + 1); // Move to the next day
        }
    
        // Return dates
        return dates;

    }

    /**
     * Is Weekend
     * 
     * @param date 
     * @returns 
     */
    public static isWeekend = (date:Date):boolean => {
        const day = date.getDay(); // 0 is Sunday, 6 is Saturday
        return day === 0 || day === 6; // Returns true if Saturday or Sunday
    }

    /**
     * Merge date
     * 
     * Merge year, month and day as YYYY-MM-DD
     * 
     * @param year:int|string
     * @param month:int|string
     * @param day:int|string
     * @return string
     */
    public static mergeDate = (year:string|number, month:string|number, day:string|number):string => {

        // Check year is string
        typeof year === "string" && (year = Number(year));
        typeof month === "string" && (month = Number(month));
        typeof day === "string" && (day = Number(day));

        // Validate inputs to ensure they create a valid date
        const date = new Date(year, month - 1, day); // JavaScript months are 0-indexed
    
        if (
            date.getFullYear() !== year ||
            date.getMonth() + 1 !== month ||
            date.getDate() !== day
        ) {
            throw new Error("Invalid date provided.");
        }
    
        // Format the date to YYYY-MM-DD
        return `${year.toString().padStart(4, '0')}-${month
            .toString()
            .padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

    }

    /**
     * Explode Date
     * 
     * Explode YYYY-MM-DD to array with [year, month, day]
     * 
     * @param date 
     * @returns {string[]}
     */
    public static explodeDate = (date:string):number[] => {

        // Split the date by hyphens
        const parts = date.split("-");
    
        // Ensure the parts array has exactly 3 elements
        if (parts.length !== 3)
            
            throw new Error("Invalid date format. Expected YYYY-MM-DD.");
    
        // Convert parts to integers and return as an array
        return parts.map(part => parseInt(part, 10));
    }

    /**
     * To Local Format
     * 
     * @param input 
     * @param locale 
     * @returns 
     */
    public static toLocalFormat = (input:string, locale:string):string => {
    
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

}