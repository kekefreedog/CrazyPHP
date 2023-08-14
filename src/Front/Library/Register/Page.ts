/**
 * Register
 *
 * Front TS Scrips for register elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Arrays from '../Utility/Arrays';
import Crazypage from '../Crazypage';

/**
 * Crazy Page Loader
 *
 * Methods for register a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Page {

    /** Private methods
     ******************************************************
     */

    /** @var registered Page registered */
    private registered:Array<RegisterPageRegistered> = [];

    /**
     * Constructor
     */
    public constructor(){

        // Open Register (init when the registered is creates)
        window.Crazyobject.events.dispatch("onRegisterPageOpen");

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Register Page
     *
     * @param page:Crazypage instance to register
     * @returns void
     */
    public register = (page:typeof Crazypage):void => {

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
                dateLoaded: new Date(),
                scriptUrl: new URL(`${window.location.origin}/dist/${page.className}.${window.Crazyobject.hash.get()}.js`)
            }

            // Push register im registered
            this.registered.push(newRegister);

        }

        // Check if first page loaded
        window.Crazyobject.events.has("onFirstPageRegistered");

            // Dispatch event
            window.Crazyobject.events.dispatch("onFirstPageRegistered");

    }

    /**
     * Get Registered
     * 
     * @param name Name of the page registered
     * @returns RegisterPageRegistered|null
     */
    public getRegistered = (name:string):RegisterPageRegistered|null => {

        // Set result
        let result:RegisterPageRegistered|null = null;

        // Check name
        if(name){

            // Search
            let search = Arrays.filterByKey(this.registered, "className", name);

            // Chech search
            if(search.length > 0)

                // Set result
                result = search[0];

        }

        // Return result
        return result;

    }

    /**
     * Get All
     * 
     * Get All Page registered
     * 
     * @returns Array<RegisterPageRegistered>
     */
    public getAll = ():Array<RegisterPageRegistered> => this.registered;

}