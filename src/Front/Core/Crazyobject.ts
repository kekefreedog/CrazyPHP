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
import {default as RegisterPartial} from "./../Library/Register/Partial";
import {default as UtilityEvents} from "./../Library/Utility/Events";
import {default as RegisterPage} from "./../Library/Register/Page";
import Componentregister from "./../Library/Componentregister";
import {default as PageError} from './../Library/Error/Page';
import ColorSchema from "./../Library/Utility/ColorSchema";
import Crazyconsole from "./../Library/Crazyconsole";
import CurrentPage from "./../Library/Current/Page";
import HistoryPage from "./../Library/History/Page";
import Crazyevents from "./../Library/Crazyevents";
import Crazyalert from "./../Library/Crazyalert";
import Crazystate from "./../Library/Crazystate";
import Crazypage from "./../Library/Crazypage";
import Websocket from 'reconnecting-websocket';
import Hash from './../Library/Utility/Hash';

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Crazyobject {

    /** Parameters
     ******************************************************
     */

    /** @var components Components class */
    public components:Componentregister;

    /** @var partials Partials class */
    public partials:RegisterPartial;

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

    /** @var colorSchema Color Schema */
    public colorSchema:ColorSchema;

    /** @var state State */
    public state:Crazystate;

    /** @var alert State */
    public alert:Crazyalert;

    /** @var forcedTheme State */
    public forcedTheme:"light"|"dark"|null = null;

    /**
     * Constructor
     */
    public constructor(input:CrazyObjectInput){

        // Register Global Web Components give by the app
        this.components = new Componentregister(input);

        // Check force theme
        input.forceTheme && (this.forcedTheme = input.forceTheme);

        // Color schema
        this.colorSchema = new ColorSchema();

        // Init of the app
        this.hashInit(input)                // Init Hash
            .then(this.stateInit)           // Init state
            .then(this.alertInit)           // Init alert
            .then(this.partialsInit)        // Init Partials
            .then(this.historyPageInit)     // Init History Page
            .then(this.eventInit)           // Init Event
            .then(this.registerPageInit)    // Init Register Page
            .then(this.currentPageInit)     // Init Current Page
            .then(this.consoleInit)         // Init Current Page
            .then(this.websocketInit)       // Init Websocket
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
    private hashInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

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

        // Return input
        return input;

    }

    /**
     * State Init
     * 
     * Prepare state instances in your crazy page
     * 
     * @returns Promise<void>
     */
    private stateInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New crazy state instance
        this.state = new Crazystate(input);

        // Return input
        return input;

    }

    /**
     * Alert Init
     * 
     * Prepare alert instances in your crazy page
     * 
     * @returns Promise<void>
     */
    private alertInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // Check global alerts
        if(input.glabalAlerts)

            // Prepare alert
            this.alert = new Crazyalert(input.glabalAlerts);

        // Return input
        return input;

    }

    /**
     * Partials Init
     * 
     * Prepare partial in your crazy page
     * 
     * @returns Promise<void>
     */
    private partialsInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // Register Partials
        this.partials = new RegisterPartial();

        // Register global partials
        this.partials.register(input.globalPartials);

        // Return input
        return input;

    }

    /**
     * History Page Init
     * 
     * Prepare History Instance
     * 
     * @returns Promise<void>
     */
    private historyPageInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New instance
        this.historyPage = new HistoryPage();

        // Return input
        return input;

    }

    /**
     * Register Page Init
     * 
     * Prepare History Instance
     * 
     * @returns Promise<void>
     */
    private registerPageInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New instance
        this.registerPage = new RegisterPage();

        // Open Register (init when the registered is creates)
        window.Crazyobject.events.dispatch("onRegisterPageOpen");

        // Return input
        return input;

    }

    /**
     * Current Page Init
     * 
     * Init current page
     * 
     * @returns Promise<void>
     */
    private currentPageInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New current page instance
        this.currentPage = new CurrentPage();

        // Return input
        return input;

    }

    /**
     * Events Init
     * 
     * Init Events
     * 
     * @returns Promise<void>
     */
    private eventInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New Config Register
        this.events = new UtilityEvents();

        // New crazy event
        /* new Crazyevents(); */

        // Create default custom events
        this.events.add("onRegisterPageOpen");      //--> Event to register new page
        this.events.add("onFirstPageRegistered");   //--> Event to first page is registered

        // Return input
        return input;

    }

    /**
     * Console Init
     * 
     * Init console
     * 
     * @returns Promise<void>
     */
    private consoleInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // New Config Register
        this.console = new Crazyconsole();

        // Return input
        return input;

    }

    /**
     * Websocket Init
     * 
     * Init console
     * 
     * @returns Promise<void>
     */
    private websocketInit = async(input:CrazyObjectInput):Promise<CrazyObjectInput> => {

        // Check websocket into input
        if(input.websocket){

            // Dev
            console.log(`[DEV] Weboscket init ${input.websocket}`)

            // New repeated websocket instance
            const rws = new Websocket(input.websocket);

            // Listen for messages
            rws.addEventListener('message', (event) => {
                console.log('Websocket | Message from server:', event.data);
            });

            // Send a message
            rws.addEventListener('open', () => {
                rws.send('Websocket | Hello Server!');
            });

            // Handle errors
            rws.addEventListener('error', (error) => {
                console.error('Websocket | WebSocket error:', error);
            });


        }

        // Return input
        return input;

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
        var onRegisterPageOpenEvent = (e:Event):void => {

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