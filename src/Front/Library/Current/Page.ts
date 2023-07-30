/**
 * Current
 *
 * Front TS Scrips for manage current elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as LoaderPage} from './../Loader/Page';
import {default as PageError} from './../Error/Page';
import Crazypage from '../Crazypage';

/**
 * Crazy Page Current
 *
 * Methods for manage current page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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
        new LoaderPage(this.current);

    }

    /**
     * Reload Page
     */
    public reload = ():void => {



    }



}