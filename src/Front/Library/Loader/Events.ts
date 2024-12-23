/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

import LoaderPage from "../Loader/Page";

/**
 * Dependances
 */

/**
 * Events
 *
 * Loader of events coming from api response
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Events {

    /** Private parameters
     ******************************************************
     */

    /** @var _events:Array<LoaderEventRedirection> */
    private _events:Array<LoaderEventRedirection> = [];

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(response:{
        _events?: Array<LoaderEventRedirection>
    }){

        // Check events
        if(response._events){

            // Ingest events
            this._ingestEvents(response._events);

            // Run events
            this._runEvents();

        }

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest events
     * 
     * @param events 
     * @returns {void}
     */
    private _ingestEvents = (events:Array<LoaderEventRedirection>):void => {

        // Iteration of events
        for(let event of events)

            // Push it on events
            this._events.push(event);

    }

    /**
     * Run Events
     * 
     * @returns {void}
     */
    private _runEvents = ():void => {

        // Check events
        if(this._events.length)

            // Iteration of events
            for(let event of this._events)

                // Check if redirection
                if(event.type == "redirect"){

                    // Run redirection
                    this._runRedirection(event);

                    // Stop method
                    break;

                }

    }

    /** Private methods | Run
     ******************************************************
     */

    /**
     * Run redirection
     * 
     * @param event:LoaderEventRedirection
     */
    private _runRedirection = (event:LoaderEventRedirection):void => {

        // Prepare options
        let options:LoaderPageOptions = {};

        // Check name
        if(event.name){

            // Set name
            options.name = event.name;

            // Check target
            if(event.target == "_blank")
    
                // Add openInNewTab in options
                options["openInNewTab"] = true;

            // Get global attributes
            window.Crazyobject.state.globalStore?.iterate((value, key)=>{

                // Check arguments
                if(typeof options.arguments !== "object" || !options.arguments)

                    // Create object
                    options.arguments = {};

                // Set as object
                options.arguments[key] = value;

                // Return options
                return options;

            }).then((options) => {
                    
                // Load page (redirection)
                new LoaderPage(options);

            })

        }else
        // Check url
        if(event.url){

            // Set url
            let url = new URL(event.url);

            // Check target
            if(event.target == "_blank"){

                // Open url in new tab
                window.open(url); 

            }
            // Change current page
            else{

                // Change location
                window.location.href = url.href;

            }

        }
            

    }

}