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
import Pageregister from "./Pageregister";
import Crazyrequest from "./Crazyrequest";

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

    /** Private Parameters
     ******************************************************
     */

    private pageRegister:Pageregister|null = null;

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
    private convertToUrl = (path:string = "", base:string = window.location.href):URL => {

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

        // Check page redirect
        if(this.pageRegister === null)

            // New page redirect
            this.pageRegister = new Pageregister();

        // New redirection
        this.pageRegister.redirect(path);

        // Return result
        return result;

    }

    /**
     * Redirect By Name
     * 
     * Redirect to page by name
     * 
     * @param name:string Name of the page -> script with ask to server the path
     * @param options:RedirectByNameOptions Option for define arguments / mimetype...
     * @param reloadPage:boolean Force a real reload of the page
     * @return
     */
    public redirectByName = (name:string, options:RedirectByNameOptions, reloadPage:boolean = false):void => {

        // Prepare query
        let query:Object = {
            filters: {
                name: name
            },
            options: {}
        };

        // Check options arguments
        if("arguments" in options && options.arguments !== null){

            // Add options
            query["options"].arguments = options.arguments;

        }

        // New request
        new Crazyrequest(
            "/api/v2/Router/filter",
            {
                method: "GET",
                responseType: options.mimetype,
                from: "internal"
            }
        ).fetch(query)
        .then(result => {

            // Check result
            if(
                "results" in result && 
                Array.isArray(result.results) &&
                result.results.length === 1 &&
                "name" in result.results[0] &&
                "path" in result.results[0]
            ){

                // Load new page
                window["Crazyobject"]["pages"].loadInternalPage(result.results[0]);
                
            }else{

                throw new Error(`No page corresponding to "${name}" in the router collection`)

            }

        });

    }

}