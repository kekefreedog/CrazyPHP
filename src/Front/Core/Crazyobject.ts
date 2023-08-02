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
import {default as UtilityEvents} from "./../Library/Utility/Events";
import {default as RegisterPage} from "./../Library/Register/Page";
import Componentregister from "./../Library/Componentregister";
import {default as PageError} from './../Library/Error/Page';
import Crazyconsole from "./../Library/Crazyconsole";
import CurrentPage from "./../Library/Current/Page";
import HistoryPage from "./../Library/History/Page";
import Crazyevents from "./../Library/Crazyevents";
import Crazypage from "./../Library/Crazypage";
import Hash from './../Library/Utility/Hash';
/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Crazyobject {

    /** Parameters
     ******************************************************
     */

    /** @var components Components class */
    public components:Componentregister;

    /** @var currentPage Pages class */
    public registerPage:RegisterPage;

    /** @var currentPage Pages class */
    public currentPage:CurrentPage;

    /** @var console Configs class */
    public console:Crazyconsole;

    /** @var hash Hash of the current build */
    public hash:Hash;

    /** @var history History Page instance */
    public historyPage:HistoryPage;

    /** @var events Configs class */
    public events:UtilityEvents;

    /**
     * Constructor
     */
    public constructor(input:CrazyObjectInput){

        // Register Global Web Components give by the app
        this.components = new Componentregister(input);

        // Init of the app
        this.hashInit()                     // Init Hash
            .then(this.historyPageInit)     // Init History Page
            .then(this.eventInit)           // Init Event
            .then(this.registerPageInit)    // Init Register Page
            .then(this.currentPageInit)     // Init Current Page
            .then(this.consoleInit)         // Init Current Page
        ;

    }

    /** Private Async Methods Init
     ******************************************************
     */

    /**
     * Hash Init
     * 
     * Prepare hash in your crazy page
     * Hash allow app to load the script from back when you are loading a new page
     * 
     * @returns Promise<void>
     */
    private hashInit = async():Promise<void> => {

        // Hash instance
        this.hash = new Hash();

        // Set hash from meta tag
        if(!this.hash.setFromMetaTag()){

            // Set hash from request
            let resultFromRequest = await this.hash.setFromRequest();

            // Check result not null
            if(resultFromRequest === null)

                // New error
                throw new PageError("Can't retrieve the hash from back.")

        }

    }

    /**
     * History Page Init
     * 
     * Prepare History Instance
     * 
     * @returns Promise<void>
     */
    private historyPageInit = async():Promise<void> => {

        // New instance
        this.historyPage = new HistoryPage();

    }

    /**
     * Register Page Init
     * 
     * Prepare History Instance
     * 
     * @returns Promise<void>
     */
    private registerPageInit = async():Promise<void> => {

        // New instance
        this.registerPage = new RegisterPage();

    }

    /**
     * Current Page Init
     * 
     * Init current page
     * 
     * @returns Promise<void>
     */
    private currentPageInit = async():Promise<void> => {

        // New current page instance
        this.currentPage = new CurrentPage();

    }

    /**
     * Events Init
     * 
     * Init Events
     * 
     * @returns Promise<void>
     */
    private eventInit = async():Promise<void> => {

        // New Config Register
        this.events = new UtilityEvents();

        // Create default custom events
        this.events.add("onRegisterPageOpen"); //--> Event to register new page
        this.events.add("onFirstPageRegistered");   //--> Event to first page is registered

    }

    /**
     * Console Init
     * 
     * Init console
     * 
     * @returns Promise<void>
     */
    private consoleInit = async():Promise<void> => {

        // New Config Register
        this.console = new Crazyconsole();

    }

    /** Public Methods | Register
     ******************************************************
     */

    public register = (page:typeof Crazypage):void => {

        /**
         * On Register Page Open Event
         * 
         * @param e
         * @returns void
         */
        let onRegisterPageOpenEvent = (e:Event):void => {

            // Register current page
            this.registerPage.register(page);

            // Add Event listener
            document.removeEventListener(
                "onRegisterPageOpen",
                onRegisterPageOpenEvent
            );
    
        }

        // Check if register has been init
        if(
            window.Crazyobject.registerPage !== undefined && 
            "register" in window.Crazyobject.registerPage &&
            typeof window.Crazyobject.registerPage.register === "function"
        )

            // Register current page
            this.registerPage.register(page);

        else

            // Add Event listener and wait page register is ready
            document.addEventListener(
                "onRegisterPageOpen",
                onRegisterPageOpenEvent
            );

    }

    /** Methods | Page Register
     ******************************************************
     */

    public getRegisteredPage = (name:string):RegisterPageRegistered|null => {

        // Set result
        let result:RegisterPageRegistered|null = null;

        // Check function
        /* if("getRegistered" in this._registerPage && typeof this._registerPage.getRegistered === "function")

            // Call register in window
            result = this._registerPage.getRegistered(name); */

        // Return result
        return result;

    }

    /** Private Methods | Events
     ******************************************************
     */

    /** Constants
     ******************************************************
     */

    /**
     * Global Variable
     */
    public readonly GLOBAL_VARIABLE:string = "Crazyobject";


}