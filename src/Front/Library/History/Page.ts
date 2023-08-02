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
import Arrays from '../Utility/Arrays';
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

    /** @param _currentHref  */
    private _currentHref:string;

    /** @param _collection Collection of url with state and LoaderPagOptions */
    private _collection:Array<HistoryPageItem> = [];



    /**
     * Constructor
     * 
     */
    public constructor(){

        // Pop state init
        this._popStateInit();

        // Set current href
        this._currentHref = window.location.href;


    }

    /** Public methods
     ******************************************************
     */

    /**
     * Register
     * 
     * Register in collection new item
     * 
     * @param item 
     * @return void
     */
    public register = (item:HistoryPageItem):void => {

        // Check href
        if(!item.href)

            // Stop function\
            return;

        // Check state
        if(!("state" in item) && item.state)

            // Set state
            item.state = null;

        // Check if href in collection
        let searchInCollection:Array<HistoryPageItem> = Arrays.filterByKey(this._collection, "href", item.href);

        // Check searchInCollection
        if(searchInCollection.length){

            // Second search
            let secondSearchInCollection:Array<HistoryPageItem> = Arrays.filterByKey(searchInCollection, "state", item.state ?? null);

            // Check secondSearchInCollection
            if(secondSearchInCollection.length)

                // Stop register because is already exists
                return;

        }

        // Push item in collection
        this._collection.push(item);

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

    /** Private methods
     ******************************************************
     */

    private _popStateInit = ():void => {

        // Catch event
        window.addEventListener("popstate", this._popStateEvent);

    }

    private _popStateEvent = (e:PopStateEvent):void => {

        // Prevent default
        e.defaultPrevented;

        // Get new href
        let newHref = window.location.href;

        // Seatch new href in collection
        let search = Arrays.filterByKey(this._collection, "href", newHref);

        // Check search
        /* if(search.length){

            // Load


            // Stop function
            return;

        }

        // Seatch new href in collection
        let searchBack = Arrays.filterByKey(this._collection, "href", newHref);

        // Check search
        if(searchBack.length){



        } */

    }

}