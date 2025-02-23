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
    private _events:Array<LoaderEventRedirection|LoaderEventAlert|LoaderEventWait> = [];

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(response:{
        _events?: Array<LoaderEventRedirection|LoaderEventAlert|LoaderEventWait>
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
    private _ingestEvents = (events:Array<LoaderEventRedirection|LoaderEventAlert|LoaderEventWait>):void => {

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

        // Check length
        if(this._events.length){

            // New promise
            let promiseChain = Promise.resolve();
        
            // Iteration events
            for(const event of this._events) {

                // Set event
                promiseChain = promiseChain.then(() => {

                    // Check redirect
                    if(event.type === "redirect")

                        // Return run redirection
                        return this._runRedirection(event).then(() => Promise.reject("stop"));

                    else
                    // Check alert
                    if (event.type === "alert")

                        // Return run alert
                        return this._runAlert(event);

                    else
                    // Check wait
                    if(event.type === "wait")

                        // Return wait
                        return this._runWait(event);

                // Catch error
                }).catch(error => {

                    // Check stop
                    if(error === "stop")

                        // Stop further execution
                        return Promise.reject(error); 
                    
                    // Continue execution
                    return Promise.resolve(); 

                });

            }
        
            // Handle any final promise rejection to prevent unhandled errors
            promiseChain.catch(() => {});

        }

    }

    /** Private methods | Run
     ******************************************************
     */

    /**
     * Run redirection
     * 
     * @param event:LoaderEventRedirection
     * @returns {Promise<void>}
     */
    private _runRedirection = async (event:LoaderEventRedirection):Promise<void> => {

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

    /**
     * Run Alert
     * 
     * @param event:LoaderEventAlert
     * @returns {Promise<void>}
     */
    private _runAlert = async (event:LoaderEventAlert):Promise<void> => {

        // Get alert instance
        let alertInstance = window.Crazyobject.alert;

        // Check message
        if(event.message && event.messageType){

            // Run event
            // @ts-ignore
            alertInstance[event.messageType]({
                detail: event.message,
                type: event.messageType, 
            });

        }

    }

    /**
     * Run Wait
     * 
     * @param event:LoaderEventAlert
     * @returns {Promise<void>}
     */
    private _runWait = async (event:LoaderEventWait):Promise<void> => {

        // Set duration
        let duration = 0;

        // Check second
        if(event.second)

            // Set duration
            duration = Number(event.second) * 1000;

        // check duration
        if(duration > 0)

            // Set start
            await new Promise(resolve => setTimeout(resolve, 5000));

    }

}