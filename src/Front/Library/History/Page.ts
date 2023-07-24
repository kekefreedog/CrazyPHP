/**
 * History
 *
 * Front TS Scrips for manage history
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';

/**
 * Crazy Page History
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Page {

    /** Private parameters
     ******************************************************
     */

    /** @param _past Past collection */
    private _past:Array<LoaderPageOptions> = [];

    /** @param _future */
    private _future:Array<LoaderPageOptions> = [];

    /**
     * Constructor
     * 
     */
    public constructor(){

        

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Add To Back
     * 
     * Add page loader page to Back
     * 
     * @param pageOptions:LoaderPageOptions
     * @return void
     */
    public addToBack = (pageOptions:LoaderPageOptions):void => {

        
    }

    /**
     * Add To Foward
     * 
     * Add page loader page to Foward
     * 
     * @param pageOptions:LoaderPageOptions
     * @return void
     */
    public addToFoward = (pageOptions:LoaderPageOptions):void => {


    }

    /**
     * Get Back
     * 
     * Get Back page
     * 
     * @return LoaderPageOptions
     */
    public getBack = ():LoaderPageOptions|null => {

        // Prepare result
        let result:LoaderPageOptions|null = null;

        // Return page option
        return result;

    }

    /**
     * Get Forward
     * 
     * Get Forward page
     * 
     * @return LoaderPageOptions
     */
    public getForward = ():LoaderPageOptions|null => {

        // Prepare result
        let result:LoaderPageOptions|null = null;

        // Return page option
        return result;

    }

}