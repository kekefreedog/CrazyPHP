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

    /** Parameters
     ******************************************************
     */

    /**
     * Constructor
     */
    public constructor(){

        // Init cache
        this.cacheInstance = new Crazycache("router");

        this.cacheInstance
            .get("dateUpdated")
            .then(value => {

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
                    
            })

    }

    /** Methods
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
    public register(page:Crazypage):void {

        // Event listener on router ready
        document.addEventListener(  
            "routerReady",
            value => {
                
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
                            date: new Date()
                        }

                    }

                }

            }

        );

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