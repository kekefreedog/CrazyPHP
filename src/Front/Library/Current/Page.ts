/**
 * Current
 *
 * Front TS Scrips for manage current elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageLoader} from "./../Loader/Page";
import {default as PageError} from './../Error/Page';
import Crazypage from '../Crazypage';

/**
 * Crazy Page Current
 *
 * Methods for manage current page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Page {

    /** Private parameters
     ******************************************************
     */

    /** @param current:null|LoaderPageOptions */
    private current:null|LoaderPageOptions = null;

    /**
     * Constructor
     */
    public constructor(){

        // Check i it is the first page registered
        if(this.get() === null){

            // Get all page registered
            let allPageRegistered:Array<RegisterPageRegistered> = window.Crazyobject.registerPage.getAll();

            // Get registered page
            if(allPageRegistered.length === 0){

                // Function for event
                let registerFirstcurrentPageEvent = (e:Event) => {

                    // Get first page
                    let page:RegisterPageRegistered = window.Crazyobject.registerPage.getAll().slice()[0];

                    // Prepare loader page options
                    let pageOptions:LoaderPageOptions = {
                        name: page.className,
                        // @ts-ignore
                        scriptLoaded: page.classReference,
                        status: {
                            "scriptRegistered": true,
                            "contentLoaded": true,
                            "styleLoaded": true,
                            "urlLoaded": true,
                            "urlUpdated": true
                        }
                    }

                    // Page loader
                    new PageLoader(pageOptions);

                    // New event listener
                    document.removeEventListener(
                        "onFirstPageRegistered",
                        registerFirstcurrentPageEvent
                    );

                }

                // New event listener
                document.addEventListener(
                    "onFirstPageRegistered",
                    registerFirstcurrentPageEvent
                );

            }else{

                // Get first page
                let page:RegisterPageRegistered = window.Crazyobject.registerPage.getAll()[0];

                // Page loader
                new PageLoader({
                    name: page.className,
                    // @ts-ignore
                    scriptLoaded: page,
                    status: {
                        "scriptRegistered": true,
                        "contentLoaded": true,
                        "styleLoaded": true,
                        "urlLoaded": true,
                        "urlUpdated": true
                    }
                });

            }

        }

    }

    /** Methods | public
     ******************************************************
     */

    /**
     * Get
     * 
     * Get current page option
     * 
     * @return null|LoaderPageOptions
     */
    public get = ():null|LoaderPageOptions => {

        // Set result
        let result:null|LoaderPageOptions = this.current;

        // Return result
        return result;

    }

    /**
     * Set
     * 
     * Set current page option
     * 
     * @return void
     */
    public set = (page:LoaderPageOptions):void => {

        // Set current
        this.current = page;

    }

    /**
     * Execute
     * 
     * New instance of the current page
     * 
     * @return void
     */
    public execute = ():void => {

        // Check this current
        if(this.current === null)

            // New error
            throw new PageError("Pleae set a page before execute it");

        // Load current page
        new PageLoader(this.current);

    }

    /**
     * Reload Page
     */
    public reload = ():void => {



    }



}