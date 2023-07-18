/**
 * Register
 *
 * Front TS Scrips for register elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Crazypage from '../Crazypage';
import Arrays from '../Utility/Arrays';

/**
 * Crazy Page Loader
 *
 * Methods for register a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Page {

    /** Private methods
     ******************************************************
     */

    /** @var registered Page registered */
    private static registered:Array<RegisterPageRegistered> = [];

    /** Public static methods
     ******************************************************
     */

    /**
     * Register Page
     *
     * @param page:Crazypage instance to register
     */
    public static register = (page:typeof Crazypage) => {

        // Check if page already in registered
        let result = Arrays.filterByKey(this.registered, "className", page.className);

        // Check if result is empty
        if(result.length !== 0){

            // Return

        }
        // Register the page
        else{

            // Set new register
            let newRegister:RegisterPageRegistered = {
                className : page.className,
                classReference: page,
                dateLoaded: new Date()
            }

            // Push register im registered
            Page.registered.push(newRegister);

        }

    }

    public static getRegistered = (name:string):RegisterPageRegistered|null => {

        // Set result
        let result:RegisterPageRegistered|null = null;

        // Check name
        if(name){

            // Search
            let search = Arrays.filterByKey(this.registered, "className", name);

            // Chech search
            if(search.length > 0){

                // Set result
                result = search[0];

            }

        }

        // Return result
        return result;

    }

}