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
 * Money
 *
 * Methods for manage money
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Money {

    /** Public static methods
     ******************************************************
     */

    /**
     * Dollar
     * 
     * Format number to US dollar
     * 
     * @param input 
     * @returns {string}
     */
    public static dollar = (input:number):string => {
           
        // Set result
        let result = `${input}`;

        // Check number
        if(input){

            // Set us dollat
            let USDollar = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });

            // Set result
            result = USDollar.format(input);

        }

        // Return result
        return result;

    }

    /**
     * Pounds
     * 
     * Format number to British pounds
     * 
     * @param input 
     * @returns {string}
     */
    public static pounds = (input:number):string => {
           
        // Set result
        let result = `${input}`;

        // Check number
        if(input){

            // Set us dollat
            let pounds = Intl.NumberFormat('en-GB', {
                style: 'currency',
                currency: 'GBP',
            });

            // Set result
            result = pounds.format(input);

        }

        // Return result
        return result;

    }

    /**
     * Rupee
     * 
     * Format number to Indian rupee
     * 
     * @param input 
     * @returns {string}
     */
    public static rupee = (input:number):string => {
           
        // Set result
        let result = `${input}`;

        // Check number
        if(input){

            // Set rupee
            let rupee = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
            });

            // Set result
            result = rupee.format(input);

        }

        // Return result
        return result;

    }

    /**
     * Euro
     * 
     * Format number to Euro
     * 
     * @param input 
     * @returns {string}
     */
    public static euro = (input:number):string => {
           
        // Set result
        let result = `${input}`;

        // Check number
        if(input){

            // Set euro
            let euro = Intl.NumberFormat('en-DE', {
                style: 'currency',
                currency: 'EUR',
            });

            // Set result
            result = euro.format(input);

        }

        // Return result
        return result;

    }
    
}