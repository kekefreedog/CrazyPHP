/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Crazy Page
 *
 * Methods for build your page script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default abstract class Crazypage {

    /**
     * Constructor
     */
    public constructor(){

        /**
         * Set Current Page
         */
        this.setCurrentPage();

        /**
         * Dispatch Events
         */
        this.dispatchEvents();

    }

    /** Abstract methods
     ******************************************************
     */

    /**
     * On Ready
     *
     * @return void
     */
    public onReady:CallableFunction;

    /** Private methods
     ******************************************************
     */

    /**
     * Set current page
     * 
     * @return void
     */
    private setCurrentPage = ():void => {

        

    }

    /**
     * Dispatch Events
     *  
     * Dispatch all new events
     *  
     * @return void
     */
    private dispatchEvents = ():void => {



    }

    /**
     * Dispatch Event
     *  
     * Dispatch one event
     *  
     * @param event:string
     * @param callable:CallableFunction
     * @return void
     */
    private dispatchEvent = (event:string, callable:CallableFunction):void => {



    }

    /** Private methods | Utilities
     ******************************************************
     */

    /**
     * convertToUrl
     * 
     * @param path Path to the url
     * @param base Base of the path
     * @return URL
     */
    private convertToUrl = (path:string = "", base:string = window.location.href):URL {

        // Declare variables
        let result:URL;

        // Check path start with /
        if(path.startsWith("/"))

            // New url with base
            result = new URL(path, base);

        else

            // New url
            result = new URL(path);

        // Return result
        return result;

    }


    /** Public methods
     ******************************************************
     */

    /**
     * Redirect To
     * 
     * Redirect to another page
     * @param path:string Name of the page to redirect to
     * @param reloadPage:boolean Force a real reload of the page
     * @return result
     */
    public redirectTo = (path:string = "", reloadPage:boolean = false):boolean => {
        
        // Declare variables
        let url:URL = this.convertToUrl(path);

        // Set result
        let result:boolean = false;

        // Check if reload page
        if(reloadPage){

            // Set new location
            window.location.assign(url);

            // Return result
            return result;

        }

        // Return result
        return result;

    }

}