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
import { DateTime as Luxon, Duration } from 'luxon';
import type {IntRange} from 'type-fest';

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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("toUTCString", ...)
     */
    public static toUTCString = (date:Date|Luxon):string => {

        // Return
        return (date instanceof Date ? Luxon.fromJSDate(date) : date)
            .toUTC()
            .toFormat("yyyy-LL-dd HH:mm:ss 'UTC'")
        ;

    }

    /**
     * To Iso String
     *
     * @param date
     * @returns {string}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("toISOString", ...)
     */
    public static toISOString = (date:Date|Luxon):string => {

        // Return
        return (date instanceof Date ? Luxon.fromJSDate(date) : date)
            .toUTC()
            .toFormat("yyyy-LL-dd'T'HH:mm:ss.SSS'Z'")
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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("toYYYYMMDDFormat", ...)
     */
    public static toYYYYMMDDFormat = (date:Date|Luxon, separator:string = "/"):string => {

        // Convert to Luxon (local zone, same as the native getters used before)
        const luxonDate = date instanceof Date ? Luxon.fromJSDate(date) : date;

        // Return result
        return `${luxonDate.toFormat('yyyy')}${separator}${luxonDate.toFormat('LL')}${separator}${luxonDate.toFormat('dd')}`;

    }

    /**
     * Get Next Day
     *
     * Return in format YYYY/MM/DD
     *
     * @param weekday
     * @returns {string}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getNextDay", ...)
     */
    public static getNextDay = (weekday:1|2|3|4|5|6|7, returnDate:boolean = false):string|Date => {

        // Check week day
        if (weekday < 1 || weekday > 7)

            // New error
            throw new Error("Invalid weekday number. Please enter a number between 1 (Monday) and 7 (Sunday).");

        // New date (UTC, since the original computation is entirely UTC-based)
        let today = Luxon.fromJSDate(new Date()).toUTC();

        // Sunday - 0, Monday - 1, etc. (Luxon's weekday is 1=Monday..7=Sunday)
        const todayDayOfWeek = today.weekday % 7;

        // Calculate days until the next desired weekday
        const daysUntilNext = (weekday - todayDayOfWeek + 7) % 7;

        // Set the date to the next desired weekday
        today = today.plus({days: daysUntilNext});

        // Check return date
        if(returnDate){

            // Return date
            return today.toJSDate();

        }else{

            // Return date formatted as YYYY/MM/DD
            return today.toFormat('yyyy/LL/dd');

        }
    }

    /**
     * Get Today Date YYMMDD
     *
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getTodayDateYYMMDD", ...)
     */
    public static getTodayDateYYMMDD = ():string => {

        // Set now
        const now = Luxon.now();

        // Return result
        return `${now.toFormat('yy')}${now.toFormat('LL')}${now.toFormat('dd')}`;

    }

    /**
     * Get Today Date YYYYMMDD
     *
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getTodayDateYYYYMMDD", ...)
     */
    public static getTodayDateYYYYMMDD = ():string => {

        // Set now
        const now = Luxon.now();

        // Return result
        return `${now.toFormat('yyyy')}${now.toFormat('LL')}${now.toFormat('dd')}`;

    }

    /**
     * Get Today Date YYYY-MM-DD
     *
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getTodayDateYYYY_MM_DD", ...)
     */
    public static getTodayDateYYYY_MM_DD = ():string => {

        // Set now
        const now = Luxon.now();

        // Return result
        return `${now.toFormat('yyyy')}-${now.toFormat('LL')}-${now.toFormat('dd')}`;

    }

    /**
     * Convert Date Fromat
     *
     * To D/M/YYYY
     *
     * @param dateStr
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("convertDateFormat", ...)
     */
    public static convertDateFormat = (dateStr:string):string => {

        // Set result
        let result = "";

        // Check date
        if(!dateStr)

            // Return result
            return result;

        // Parse the input string into a Luxon DateTime (local zone, same as the native getters used before)
        const date = Luxon.fromJSDate(new Date(dateStr));

        // Format the date as M/D/YYYY
        // Note: this has always actually produced M/D/YYYY, not the D/M/YYYY the docstring above claims.
        result = `${date.month}/${date.day}/${date.year}`;

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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getTodayDate", ...)
     */
    public static getTodayDate = (separator:string = "-"):string => {

        // Set now
        const now = Luxon.now();

        // Return result
        return `${now.toFormat('yyyy')}${separator}${now.toFormat('LL')}${separator}${now.toFormat('dd')}`;

    }

    /**
     * Get the first date of a week based on an offset from the current week.
     *
     * @param weekOffset - The week offset from the current week (0 for this week, -1 for previous week, 1 for next week).
     * @param format - Optional format for the returned date. Default is 'YYYY-MM-DD'.
     * @returns {string} The first date of the specified week in the given format.
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getFirstDateOfWeek", ...)
     */
    public static getFirstDateOfWeek = (weekOffset: number, format:'YYYY-MM-DD'|'YYYYMMDD'|'MM/DD/YY'|'DD/MM/YYYY' = 'YYYY-MM-DD'):string => {

        // Get current date
        const today = Luxon.now();

        // Days since the last Monday (Luxon's weekday is already 1=Monday..7=Sunday)
        const daysSinceMonday = today.weekday - 1;

        // Calculate the first date of the target week
        const targetMonday = today
            .minus({days: daysSinceMonday})
            .plus({days: weekOffset * 7})
        ;

        // Format the date
        return DateTime.formatDate(targetMonday.toJSDate(), format);

    }

    /**
     * Is Date Current Week
     *
     * Check if a given date is in the current week.
     *
     * @param date - The date to check.
     * @returns True if the date is in the current week, false otherwise.
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("isDateInCurrentWeek", ...)
     */
    public static isDateInCurrentWeek = (date:Date|Luxon|string):boolean => {

        // Set target as a Luxon DateTime
        const target = typeof date === "string"
            ? Luxon.fromJSDate(new Date(date))
            : date instanceof Date
                ? Luxon.fromJSDate(date)
                : date
        ;

        // Set today
        const today = Luxon.now();

        // Compare the given date with the start (Monday 00:00) and end (Sunday 23:59:59.999) of the current week
        return target >= today.startOf('week') && target <= today.endOf('week');

    }

    /**
     * Is Date Next Week
     *
     * Check if a given date is in the next week.
     *
     * @param date - The date to check.
     * @returns True if the date is in the next week, false otherwise.
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("isDateInNextWeek", ...)
     */
    public static isDateInNextWeek = (date: Date | Luxon | string): boolean => {

        // Set target as a Luxon DateTime
        const target = typeof date === "string"
            ? Luxon.fromJSDate(new Date(date))
            : date instanceof Date
                ? Luxon.fromJSDate(date)
                : date
        ;

        // Set today
        const today = Luxon.now();

        // Sunday - 0, Monday - 1, etc. (Luxon's weekday is 1=Monday..7=Sunday)
        const dayOfWeek = today.weekday % 7;

        // Calculate the start of the next week (Monday)
        const startOfNextWeek = today.set({day: today.day + (dayOfWeek === 0 ? 1 : 8 - dayOfWeek)});

        // Calculate the end of the next week (Sunday)
        const endOfNextWeek = today.set({day: startOfNextWeek.day + 6});

        // Set hours to 0 to compare only date parts
        const start = startOfNextWeek.set({hour: 0, minute: 0, second: 0, millisecond: 0});

        // Set hour
        const end = endOfNextWeek.set({hour: 23, minute: 59, second: 59, millisecond: 999});

        // Compare the given date with the start and end of the next week
        return target >= start && target <= end;

    }

    /**
     * Is Valide Date
     *
     * Validate a simple date string
     *
     * @param dateString
     * @returns {boolean}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("isValidDate", ...)
     */
    public static isValidDate = (dateString:string):boolean => {

        // Return bool
        return Luxon.fromJSDate(new Date(dateString)).isValid;

    }

    /**
     * Format a Date object into a string based on the given format.
     *
     * @param date - The Date object to format.
     * @param format - The format string :
     * - YYYY-MM-DD
     * - YYYY/MM/DD
     * - YYYYMMDD
     * - MM/DD/YY
     * - DD/MM/YYYY
     * @returns {string} The formatted date string.
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("formatDate", ...)
     */
    public static formatDate = (date:Date|Luxon|string, format:'YYYY-MM-DD'|'YYYYMMDD'|'MM/DD/YY'|'DD/MM/YYYY'|'YYYY/MM/DD'):string => {

        // Check date
        const luxonDate = typeof date === "string"
            ? Luxon.fromJSDate(new Date(date))
            : date instanceof Date
                ? Luxon.fromJSDate(date)
                : date
        ;

        // Swtich
        switch (format) {
            case 'YYYY-MM-DD':
                return luxonDate.toFormat('yyyy-LL-dd');
            case 'YYYY/MM/DD':
                return luxonDate.toFormat('yyyy/LL/dd');
            case 'YYYYMMDD':
                return luxonDate.toFormat('yyyyLLdd');
            case 'MM/DD/YY':
                return luxonDate.toFormat('LL/dd/yy');
            case 'DD/MM/YYYY':
                return luxonDate.toFormat('dd/LL/yyyy');
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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("getAllDaysBetween", ...)
     */
    public static getAllDaysBetween = (startDate:Date|Luxon, endDate:Date|Luxon):Date[] => {

        // Prepare dates
        const dates: Date[] = [];

        // Get current date
        let currentDate = (startDate instanceof Date ? Luxon.fromJSDate(startDate) : startDate).startOf('day');

        // GEt last date
        const lastDate = (endDate instanceof Date ? Luxon.fromJSDate(endDate) : endDate).startOf('day');

        // Loop through and add each day to the dates array
        while (currentDate <= lastDate) {

            // Push a clone (native Date) of currentDate
            dates.push(currentDate.toJSDate());

            // Move to the next day
            currentDate = currentDate.plus({days: 1});

        }

        // Return dates
        return dates;

    }

    /**
     * Is Weekend
     *
     * @param date
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("isWeekend", ...)
     */
    public static isWeekend = (date:Date|Luxon):boolean => {

        // Set day (Luxon's weekday is 1=Monday..7=Sunday)
        const day = (date instanceof Date ? Luxon.fromJSDate(date) : date).weekday;

        // Returns true if Saturday(6) or Sunday(7)
        return day === 6 || day === 7;

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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("mergeDate", ...)
     */
    public static mergeDate = (year:string|number, month:string|number, day:string|number):string => {

        // Check year is string
        typeof year === "string" && (year = Number(year));
        typeof month === "string" && (month = Number(month));
        typeof day === "string" && (day = Number(day));

        // Validate inputs to ensure they create a valid date
        const date = Luxon.local(year, month, day);

        // Check date is valid
        if(!date.isValid)

            // New error
            throw new Error("Invalid date provided.");

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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("explodeDate", ...)
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
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("toLocalFormat", ...)
     */
    public static toLocalFormat = (input:string, locale:string):string => {

        // Initialize the result with the input value
        let result = input;

        // Check if input and locale are valid strings
        if (typeof input === "string" && input && typeof locale === "string" && locale) {

            // Convert input to a Luxon DateTime (local zone, same as the native getters used before)
            const timestamp = Luxon.fromJSDate(new Date(input));

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

            // Get the day of the week (Luxon's weekday is 1=Monday..7=Sunday; map to the Sunday-first index used above)
            const weekday = localeData.weekdays[timestamp.weekday % 7];

            // Day of the month without leading zeros
            const day = timestamp.day;

            // Month name (Luxon's month is 1-12; map to the 0-based index used above)
            const month = localeData.months[timestamp.month - 1];

            // Combine and format the result
            result = `${weekday} ${day} ${month}`;
        }

        // Return the result
        return result;

    };

    /**
     *
     * Is Before Today
     *
     * @param dateToCheck
     * @returns
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("isBeforeToday", ...)
     */
    public static isBeforeToday = (dateToCheck: Date|Luxon):boolean => {

        // Normalize today's date to 00:00:00
        const today = Luxon.now().startOf('day');

        // Normalize the input date
        const inputDate = (dateToCheck instanceof Date ? Luxon.fromJSDate(dateToCheck) : dateToCheck).startOf('day');

        // Return diff
        return inputDate < today;

    };

    /** Public static methods | Luxon
     ******************************************************
     */

    /**
     * Normalize
     * 
     * Normalize date range given
     * 
     * @param from 
     * @param to 
     * @returns {{start:Luxon,end:Luxon}}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("normalize", ...)
     */
    public static normalize = (from:Date|Luxon,to:Date|Luxon):{start:Luxon,end:Luxon} => {

        // Set start
        let start = from instanceof Date
            ? Luxon.fromJSDate(from)
            : from
        ;

        // Set end
        let end = to instanceof Date
            ? Luxon.fromJSDate(to)
            : to
        ;

        // Ensure start <= end
        if(start > end)

            // Set start and end
            [start, end] = [end, start];

        // Set result
        let result = {start,end};

        // Return result
        return result;

    };

    /**
     * Offset Range
     * 
     * Offset date range based on ration and direction given
     * 
     * @param from 
     * @param to 
     * @param direction 
     * @param ratio 
     * @returns {{from:Date, to:Date}}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("offsetRange", ...)
     */
    public static offsetRange = (from:Date|Luxon,to:Date|Luxon,direction:"past"|"future"="future",ratio:number = 0.5):{from:Luxon, to:Luxon} => {

        // Normalize start / end
        const {start,end} = DateTime.normalize(from, to);

        // Get delta using ratio
        const delta = end
            .diff(start)
            .mapUnits(v => v * ratio)
        ;

        // Get direction
        const directionInt = direction === 'future' ? 1 : -1;

        // Set result
        let result = {
            from: start.plus(delta.mapUnits(v => v * directionInt)),
            to: end.plus(delta.mapUnits(v => v * directionInt)),
        };

        // Return result
        return result;

    };

    /**
     * Align Range
     * 
     * Align range to have target date on the center
     * 
     * @param from 
     * @param to 
     * @param target 
     * @returns {{from:Luxon, to:Luxon}}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("alignRange", ...)
     */
    public static alignRange = (from:Date|Luxon,to:Date|Luxon,target:Date|Luxon = Luxon.now()):{from:Luxon, to:Luxon} => {
        
        // Normalize start / end
        const {start,end} = DateTime.normalize(from, to);

        // Set end
        let targetDate = target instanceof Date
            ? Luxon.fromJSDate(target)
            : target
        ;

        // Get duration
        const duration = end.diff(start);

        // Set half
        const half = duration.mapUnits(v => v / 2);

        // Set result
        let result = {
            from: targetDate.minus(half),
            to: targetDate.plus(half)
        };

        // Retrun result
        return result;

        }
        
    /**
    * Align Range By Ratio
    * 
    * Align range based on a ratio between two dates
    * 
    * @param from 
    * @param to 
    * @param range 
    * @param ratio 
    * @returns {{from:Luxon, to:Luxon}}
     * @see {@link ../../../../tests/Front/Library/Utility/DateTime.test.ts} - describe("alignRangeByRatio", ...)
    */
    public static alignRangeByRatio = (from:Date|Luxon, to:Date|Luxon, range:'week'|'month'|'semester'|'year', ratio:number = 0.5):{from:Luxon, to:Luxon} => {

        // Normalize start / end
        const {start, end} = this.normalize(from, to);

        // Clamp ratio
        const safeRatio = Math.min(1, Math.max(0, ratio));

        // Compute pivot date
        const pivotMillis =
            start.toMillis() +
            (
                end.toMillis() - start.toMillis()
            ) * 
            safeRatio
        ;

        // Set pivot
        let pivot = Luxon.fromMillis(pivotMillis, {zone: start.zone});

        // Let result
        let result:{from:Luxon, to:Luxon};

        // Switch range
        switch(range){

            // Case week
            case 'week': {

                // ISO week (Monday → Sunday)
                let from = pivot.startOf('week');

                // Set result
                result = {
                    from,
                    to: from.endOf('week')
                };

            } break;

            // Case month
            case 'month': {

                let from = pivot.startOf('month');

                // Set result
                result = {
                    from,
                    to: from.endOf('month')
                };

            } break;

            // Case semester
            case 'semester': {

                // Jan–Jun or Jul–Dec
                const semesterStartMonth = pivot.month <= 6 ? 1 : 7;

                let from = pivot
                    .set({month: semesterStartMonth})
                    .startOf('month')
                ;

                // Set result
                result = {
                    from,
                    to: from.plus({months: 6}).minus({milliseconds: 1})
                };

            } break;

            // Case year
            case 'year': {

                let from = pivot.startOf('year');

                // Set result
                result = {
                    from,
                    to: from.endOf('year')
                };

            } break;

        }

        // Return result
        return result;

    };


}