/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Pageregister from "./Pageregister";
import Crazyrequest from "./Crazyrequest";
import Arrays from "./Utility/Arrays";
import Crazyurl from './Crazyurl';
import State from "./State";

/**
 * Crazy Page
 *
 * Methods for build your page script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
     * @param parameters:string 
     * List of parameters needed for this page
     */
    public static readonly parameters:string[] = [];

    /**
     * @param options:LoaderPageOptions
     */
    public options:LoaderPageOptions|null;

    /**
     * @param statePageEvents
     * List of event attach to the current page state
     */
    public statePageEvents?:stateEvent[];

    /**
     * @param pageState:any
     * State of the page
     */
    public pageState:any = null;

    /**
     * Constructor
     */
    public constructor(options:LoaderPageOptions|null = null){

        /**
         * Set Options
         */
        this.options = options;

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

        // Check name
        if(this.options?.name){

            // Set page state
            this.pageState = State.get().page(this.options.name);

        }

    }

    /** Private methods | Utilities
     ******************************************************
     */

    /**
     * Register Events
     * 
     * @returns {void}
     */
    public registerEvents = (events:stateEvent[]):void => {

        // Check page events
        if(this.options?.name && this.statePageEvents && this.statePageEvents.length)

            // Iteration of page event
            for(let event of this.statePageEvents){

                // Push event into state
                State.set().event(
                    `${this.options.name}_${event.name}`,
                    event.callback,
                    `_page.${this.options.name}.${event.selector}`,
                    {
                        context: 'page',
                        target: this.options.name 
                    }
                );

                console.log(`${event.name} (${this.options.name}_${event.name}) registered`);

            }

    }

    /**
     * Dispatch Events
     *  
     * Dispatch all new events
     *  
     * @return void
     */
    public dispatchEvents = ():void => {

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

    /** Public methods partial
     ******************************************************
     */

    /**
     * Get All Partials
     * 
     * @returns {Array<Object>}
     */
    public getAllPartials = ():Array<RegisterPartialScanned> => {

        // Declare result
        let result:Array<RegisterPartialScanned> = [];

        // Check partials
        if(Array.isArray(this.options?.partials))

            // Fill result
            result = this.options?.partials as Array<RegisterPartialScanned>

        // Return result
        return result;

    }

    /**
     * Get Partial By Name
     * 
     * @returns {Object|null}
     */
    public getPartial = (name:string):RegisterPartialScanned|null => {

        // Prepare result
        let result:RegisterPartialScanned|null = null;

        // Check name
        if(name){

            // Get partials
            let partials = this.getAllPartials();

            // Filter
            let filtered = Arrays.filterByKey(partials, "name", name);

            // Check filtered
            if(Array.isArray(filtered) && filtered.length)

                // Get first element found
                result = filtered.shift();

        }

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

    /** Public methods | State
     ******************************************************
     */

    /**
     * Get Page State
     * 
     * Load current page state
     * 
     * @param forceRefresh:boolean
     * @return Promise(Object|Array<any>)
     */
    public getPageState = async (forceRefresh:boolean = false):Promise<Object|Array<any>> => {

        // Set result
        let result:Object = {};

        // Check if state in options
        if(
            forceRefresh ||
            this.options === null ||
            this.options.state === undefined
        ){      
            
            // Get current url
            let url = Crazyurl.get(true);

            // New query
            let query = new Crazyrequest(
                `${url}?catch_state=true`,
                {
                    method: "get",
                    cache: false,
                    responseType: "json",
                    from: "internal"
                }
            );
    
            // Rerurn result
            result = query.fetch()
                .then(
                    (data) => {

                        // Check options
                        if(this.options && this.options.status){
            
                            // Set state
                            this.options.state = data;
            
                            // Set state status
                            this.options.status.hasState = true;

                            // Return result
                            return this.options.state;
            
                        }

                        // Else return data
                        return data

                    }
                );

        }else{

            // Set result
            // @ts-ignore
            result = this.options.state as object;

        }

        // Return result
        return result;

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

    /**
     * Load Page State
     * 
     * Load current page state
     * 
     * @param url:string
     * @param updateState:boolean
     * @return Promise(Object|Array<any>)
     */
    public static loadPageState = async (url:string = "", updateState:boolean = true):Promise<PageState> => { 

        // Check state stored
        /* let internStoreState = State.get().page();

        // Check internStoreState
        if(internStoreState){

            // Return
            return internStoreState;

        }else{ */
            
            // Check url
            if(!url)

                // Get current url
                url = Crazyurl.get(true);

            // New query
            let query = new Crazyrequest(
                `${url}?catch_state=true`,
                {
                    method: "get",
                    cache: false,
                    responseType: "json",
                    from: "internal"
                }
            );

            // Rerurn result
            return query.fetch()
                .then((value:any) => {

                    // Check value
                    if(updateState && value && Object.keys(value))

                        // Set page state
                        State.set().page(value);

                    // Return value
                    return value;

                })
            ;

        /* } */
        
    }

}