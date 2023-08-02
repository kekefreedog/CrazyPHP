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
import { Crazyobject } from "../Types";

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

    /**
     * Page register
     */
    private pageRegister:Pageregister|null = null;

    /** 
     * @param className:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly className:string;

    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html:string|null|CallableFunction = null;

    /** 
     * @param css:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly css:null|Object = null;

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
    public abstract onReady:CallableFunction;

    /**
     * Get Class Name
     * 
     * @return string
     */
    public static getClassName = ():string => {

        // Return name
        return this.className;

    }

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
     * Load Script
     * 
     * Load JS Script after page loaded
     * 
     * @source https://www.educative.io/answers/how-to-dynamically-load-a-js-file-in-javascript
     * 
     * @param url:string Url to load
     * @param async 
     */
    private static loadScript = (url:string, async:boolean = true, type:string = "text/javascript"):Promise<any> => {

        // Return promise
        return new Promise((resolve, reject) => {

            // Try
            try {

                // Script
                const scriptEle:HTMLScriptElement = document.createElement("script");

                // Set type
                scriptEle.type = type;

                // Set async
                scriptEle.async = async;

                // Set url
                scriptEle.src = url;
    
                // Event if loaded
                scriptEle.addEventListener("load", (ev) => {

                    // Set resolve response
                    resolve({ status: true });

                });
    
                // Event if error
                scriptEle.addEventListener("error", (ev) => {

                    
                    // Set resolve response
                    reject({
                        status: false,
                        message: `Failed to load the script ${url}`
                    });

                });
    
                // Add element in body
                document.body.appendChild(scriptEle);

            // Error
            } catch (error) {

                // Reject
                reject(error);

            }

        });

    };

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
                from: "internal",
                ignoreHash: true
            }
        ).fetch(query)
        .then(result => {

            // Check result
            if(
                result &&
                "results" in result && 
                Array.isArray(result.results) &&
                result.results.length === 1 &&
                "name" in result.results[0] &&
                "path" in result.results[0]
            ){

                // Load new page
                window["Crazyobject"]["pages"]?.loadInternalPage(result.results[0]);
                
            }else{

                throw new Error(`No page corresponding to "${name}" in the router collection`)

            }

        });

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Load Action
     * 
     * Load Action if js file using the page name and the hash stored.
     * Template use : `/dist/page/app/${name}.${hash}.js`
     * 
     * @deprecated
     * 
     * @return Promise
     */
    public static loadAction = (name:string, async:boolean = true):Promise<any> => {

        // Get hash
        let hash:string = "";

        // Check global
        if("Crazyobject" in window && typeof window.Crazyobject == "object" && window.Crazyobject !== null)

            // Set hash
            hash = window.Crazyobject["getHash"]();

        else

            // Error
            new Error("Crazyobject isn't valid, can't retrieve the hash. Try to reload the page");

        // Set url
        let url:string = `/dist/page/app/${name}.${hash}.js`;

        // console.log(`Url loaded : "${url}"`);

        // Load script
        return this.loadScript(url, async);

    }

}