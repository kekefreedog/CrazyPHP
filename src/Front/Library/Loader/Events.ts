/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import LoaderPage from "../Loader/Page";

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
    private _runRedirection = async(event:LoaderEventRedirection):Promise<void> => {

        // Check for a named application route
        if(event.name){

            // Collect global arguments independently of nullable loader options
            const globalArguments:Record<string, unknown> = {};

            // Prepare the redirect independently of persistent storage contents
            const options:LoaderPageOptions = {
                name: event.name,
                arguments: {},
                openInNewTab: event.target === "_blank",
            };

            // Read every stored route argument without ending iteration early
            await window.Crazyobject.state.globalStore?.iterate((value, key) => {

                // Copy the global argument
                globalArguments[key] = value;

            });

            // Let explicit redirect arguments override global defaults
            options.arguments = {...globalArguments, ...event.arguments};

            // Start the redirect even when persistent storage is empty
            new LoaderPage(options);

        }else if(event.url){

            // Resolve explicit redirects against the current document
            const url = new URL(event.url, window.location.href);

            // Check for a new-tab redirect
            if(event.target === "_blank"){

                // Open the target URL
                window.open(url.href, "_blank");

            }else{

                // Navigate the current document
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