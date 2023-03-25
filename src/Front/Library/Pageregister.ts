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
import Crazyrequest from './Crazyrequest';
import Crazycache from './Crazycache';
import Crazypage from './Crazypage';

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

    /** Methods
     ******************************************************
     */

    /** @var cacheInstance:Crazycache|null */
    private cacheInstance:Crazycache|null = null;

    /** @var routerAction:Object */
    private routerAction:Object = [];

    /** @var customEvent:Event|null */
    private customEvent:Event;

    /** @var currentPage */
    private currentPage:Crazypage|null = null;

    /** @var history */
    private history:Array<Object> = [];

    /** Parameters
     ******************************************************
     */

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
     * @param page:Crazypage
     * @return void
     */
    public register(page:any):void {

        let registerFunction = value => {
            
            // Check detail in value
            if(
                "detail" in value && 
                Array.isArray(value.detail) && 
                "name" in page && 
                typeof page.name === "string"
            ){

                // Check if page in detail
                let currentContextCollection:Array<any> = this.filterArrayByKeyValue(value.detail, "name", page.name);
    
                // Check current context
                if(currentContextCollection.length){

                    // Push class in instance
                    this.routerAction[page.name] = {
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

                    // Remove event listener


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
        let request = new Crazyrequest("/api/v1/config/Router", option);

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

    /** Methods | Private
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