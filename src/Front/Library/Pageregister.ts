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
import {default as LoaderScript} from './Loader/Script';
import Crazyrequest from './Crazyrequest';
import Crazycache from './Crazycache';
import Crazypage from './Crazypage';
import Crazyurl from './Crazyurl';

/**
 * Page Register
 *
 * Methods for manage page loaded and to load
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
 export default class Pageregister {

    /** Public Parameters
     ******************************************************
     */

    /** @var routerAction:Object */
    public routerAction:Object = [];

    /** Private Parameters
     ******************************************************
     */

    /** @var cacheInstance:Crazycache|null */
    private cacheInstance:Crazycache|null = null;

    /** @var customEvent:Event|null */
    private customEvent:Event;

    /** @var currentPage */
    private currentPage:Crazypage|null = null;

    /** @var history */
    private history:Array<Object> = [];

    /**
     * Constructor
     */
    public constructor(){

        // Init cache
        this.cacheInstance = new Crazycache("router");

        // Cache instance
        this.cacheInstance
            .get("dateUpdated")
            .then(this.prepareCache)
        ;

    }

    /** Methods | private
     ******************************************************
     */

    /**
     * Prepare Cache
     * 
     * @param value:any
     */
    private prepareCache = (value:any) => {

        // Decalre option
        let option = {};

        // Check value
        if(value !== null){

            // Set date
            let date = new Date(value).toUTCString();

            // Prepare option
            option = {
                header: {"If-Modified-Since": date}
            }

        }

        // New Request
        let request = new Crazyrequest("/api/v1/config/Router", option);

        // Get date updated
        request.fetch()
            // Check fetch result
            .then(value => {

                // Check if data received
                if(request.lastResponse?.status === 200){

                    // Set app in cache
                    return this.cacheInstance?.set('app', value.results.config.Router.app);

                }else
                // Check if server approced internal cache
                if(request.lastResponse?.status === 304){

                    // Set app in cache
                    return this.cacheInstance?.get('app');

                }else

                    // Error
                    throw new Error("Error when loading config router : " + request.lastResponse?.statusText + "(" + request.lastResponse?.status + ")" )
    
            })
            // Dispatch event on ready
            .then(value => {

                console.log("__value___");
                console.log(value);

                // New event
                this.customEvent = new CustomEvent(
                    "routerReady",
                    {"detail": value}
                );

                // Dispatch custom event
                document.dispatchEvent(this.customEvent);

            })
        ;
            
    }

    /** Methods | public
     ******************************************************
     */

    /**
     * Register
     * 
     * Register page in current context
     * 
     * @deprecated
     * 
     * @param page:Crazypage
     * @return void
     */
    public register(page:typeof Crazypage):void {

        let registerFunction = value => {

            // Check detail in value
            if(
                "detail" in value && 
                Array.isArray(value.detail) && 
                "className" in page && 
                typeof page.className === "string"
            ){

                // Check if page in detail
                let currentContextCollection:Array<any> = this.filterArrayByKeyValue(value.detail, "name", page.className);
    
                // Check current context
                if(currentContextCollection.length){

                    // Push class in instance
                    this.routerAction[page.className] = {
                        instance: page,
                        file: "",
                        date: new Date()
                    }

                }

                // Check current page, if null it means it's the first page loaded
                if(this.currentPage === null){

                    // Execute page
                    let currentPage:any = new (page as any)();

                    // Add it to current page
                    this.currentPage = currentPage;

                    // Add it to history
                    let newHistoryItem:object = {
                        instance: page,
                        date: new Date()
                    };

                    // Push in history
                    this.history.push(newHistoryItem);


                }else{

                    // Execute page
                    let currentPage:any = new (page as any)();

                    // Remove old css specific
                    let stylePastPageEl = document.querySelector("[specific-to-page]");

                    // Check if exists
                    if(stylePastPageEl !== null)

                        // Remove it
                        stylePastPageEl.remove();

                    // Load css
                    if(page.css !== null && "default" in page.css){

                        // Css string
                        let cssString = page.css.default?.toString();

                        // Create style element
                        let styleEl = document.createElement("style");

                        // Set content
                        styleEl.innerText = cssString ? cssString : "";

                        // Set attribute
                        styleEl.setAttribute("specific-to-page", page.className);

                        // Add to document
                        document.head.appendChild(styleEl);

                    }

                    // Load html
                    if(typeof page["html"] === "function"){

                        // Get html string
                        let htmlString:string = page["html"]();

                        // Convert to dom
                        let htmlObject:Document = new DOMParser().parseFromString(htmlString, "text/html");
                    
                        // Get crazy-root
                        let crazyRootEl = document.getElementById("crazy-root");

                        // Get new content
                        let newContent = htmlObject.getElementById("crazy-root");

                        // Check el
                        if(crazyRootEl !== null && newContent !== null){

                            // Replace with new content
                            crazyRootEl.replaceWith(newContent);

                        }else{



                        }

                    }

                    // Add it to current page
                    this.currentPage = currentPage;

                }

            }

            // Remove event listener
            document.removeEventListener(
                "routerReady",
                registerFunction
            );

        }

        // Event listener on router ready
        document.addEventListener(  
            "routerReady",
            registerFunction
        );

    }

    /**
     * Redirect
     * 
     * Redirect to another page
     * 
     * @return void
     */
    public redirect(path:string = "/"):void {

        // Option for request
        let option = {};

        // Body request
        let body = {
            filters: {
                path: path 
            },
            fields: [
                "name"
            ]
        };

        console.log(path);

        // New Request
        let request = new Crazyrequest("/api/v2/Router/filter", option);

        return;

        /* // Fetch request
        request.fetch(body)
            // Check fetch result
            .then(value => {
                
            })
        ;

        // Read cache
        this.cacheInstance && this.cacheInstance?.get("app").then(value => {

            let name = "Home";
        
            // Get router by name
            let routersFiltered:Array<Object> = this.filterArrayByKeyValue(value, "name", name);

            // Check filter
            if(!routersFiltered.length)

                throw new Error("Path given isn't valid");

            console.log(routersFiltered.shift());

            console.log("toto");

            console.log(this.routerAction);

        }); */

    }

    /**
     * Get current
     * 
     * Get current page
     * 
     * @return object
     */
    public getCurrent = ():Object => {

        // Return object
        return Object;

    }

    /**
     * Get Set Current
     * 
     * Get current page 
     *  - User give url
     *  - Script send request to cache / server to know wich page match with the current url given
     *  - Then script check if the script of the page is available / or if it has to be loaded from server 
     * 
     * @return object
     */
    public setCurrent = (name:string = ""):void => {

        // Check name
        if(!name)

            // Stop function
            return;

        // 

    }

    /**
     * Load Internal Page
     * 
     * Receive as parameter input : {name: pageName, path: path}
     * 
     * @param input:RouterResponseSchema
     */
    public loadInternalPage = (input:RouterResponseSchema) => {

        // Check if input given has a key in router action
        if(input.name && input.name in this.routerAction){

            // 
            console.log("In the router action collection");

        }else{

            //
            console.log("Router action has to be loaded");
            
            // Check input.name
            if(input.name)

                // Load action of page missing
                Crazypage.loadAction(input.name)
                    .then(
                        
                        // Get result of load
                        value => {

                            // Check status
                            if(!value.status)

                                // Error
                                throw new Error("Failed to load action of page");

                            console.log("Refactor");
                            console.log(this.cacheInstance?.get('app'));
                            console.log("End Refactor");

                            // Load app in cache instance
                            return this.cacheInstance?.get('app');

                        }
                    ).then(

                        value => {

                            // New event
                            this.customEvent = new CustomEvent(
                                "routerReady",
                                {"detail": value}
                            );

                            // Dispatch custom event
                            document.dispatchEvent(this.customEvent);

                        }

                    )

        }

        // Set new url
        Crazyurl.set(input.path);

    }



    /** Private Methods | Scripts 
     ******************************************************
     */

    /**
     * Load Action
     * 
     * Load Action if js file using the page name and the hash stored.
     * Template use : `/dist/page/app/${name}.${hash}.js`
     * 
     * @return Promise
     */
    public static loadAction = (name:string, async:boolean = true):Promise<any> => {

        // Get hash
        let hash = window.Crazyobject["getHash"]();

        // Check hash
        if(!hash){

            // Get meta
            let metaTagEl = document.querySelector('meta[name="application-hash"]');

            // Check meta
            if(metaTagEl === null || !("content" in metaTagEl) || !metaTagEl.content)

                // New error
                throw new Error(`Hash is empty...`);

            // Set hash
            hash = (metaTagEl.content as string);

        }

        // Set url
        let url:string = `/dist/page/app/${name}.${hash}.js`;

        // Load script
        return LoaderScript.load(url, name, async);

    }
    

    /** Private Methods | Utilities 
     ******************************************************
     */

    /**
     * Array Filter
     * 
     * @return any
     */
    private filterArrayByKeyValue = (array:Array<any> = [], key:string, keyValue:string) => array.filter(
        (aEl) => aEl[key] == keyValue
    );

}