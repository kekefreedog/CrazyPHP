/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class DateTime {

    /** Publis static methods
     ******************************************************
     */

    /**
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

}