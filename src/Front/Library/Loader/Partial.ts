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
import {default as LoaderScript} from './../Loader/Script';
import {default as PageError} from './../Error/Page';
import Crazyrequest from './../Crazyrequest';
import Pageregister from './../Pageregister';
import Crazypage from '../Crazypage';
import Crazyurl from '../Crazyurl';
import DomRoot from '../Dom/Root';

/**
 * Crazy Partial Loader
 *
 * Methods for load a partial
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Partial {

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(options:LoaderPartialOptions){

        // Load partial detail
        return Partial.loadPartialDetail(options)
            .then(
                // Load Pre Action
                Partial.loadPreAction
            )
            .then(
                // Load Script
                Partial.loadScript
            )
            .then(
                // Load Content
                Partial.loadContent
            )
            .then(
                // Scan Partials
                Partial.pushToDomEl
            )
            .then(
                // Load Script
                Partial.runScript
            )
            .then(
                // Load Post Action
                Partial.loadPostAction
            ).catch(
                Partial.catchError
            );
    }

    /** Public methods 
     ******************************************************
     */

    /**
     * Load Partial Detail
     * 
     * Load Detail of the page
     * Return an object following this schema 
     *  {
     *      pageName:string
     *      pageUrl:string
     *      instance:Page
     *      content:
     *      style:
     *      preAction:callable
     *      postAction:callable
     *      
     *  }
     * 
     * @param options:LoaderPartialOptions Options with all partial details
     * @return Promise<LoaderPartialOptions>
     */
    public static loadPartialDetail = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> => {

        // Let keys
        let keys:Array<keyof LoadPartialOptionsStatus> = ["hasState", "scriptRegistered", "preActionExecuted", "contentLoaded", "contentPushedToDom", "postActionExecuted"];

        // Prepare options
        for(let currentKey of keys){

            // Check key is in options status
            if(options.status !== undefined && options.status !== null && currentKey in options.status)

                // Continue
                continue;
            
            // Set status
            options = Partial.setStatus(options, currentKey, false);

        }

        // Return options
        return options;

    }

    /**
     * Load Pre Action
     * 
     * Execute custom pre actions
     * 
     * > Executed only the first time that the partial is loaded
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @return Promise<LoaderPartialOptions>
     */
    public static loadPreAction = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.preActionExecuted === true)

            // Stop function
            return options;

        // Check if preAction is callable
        if(typeof options.preAction === "function")

            // Call preaction
            options = options.preAction(options);

        // Set status
        options = Partial.setStatus(options, "preActionExecuted", true);

        // Return options
        return options;

    }

    /**
     * Load Script
     * 
     * Load Js scripts of the partial
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @return Promise<LoaderPartialOptions>
     */
    public static loadScript = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.scriptRegistered === false && options.name){

            // Get script from crazyobject
            let script = window.Crazyobject.partials.get(options.name);
    
            // Load script
            // let script = await LoaderScript.load(url, options.name, true, "text/javascript");

            // Get registered
            // let registered = window.Crazyobject.registerPage.getRegistered(options.name ? options.name : "");
    
            /* // Check html
            if(registered !== null && "classReference" in registered && "html" in registered?.classReference && registered?.classReference.html){
    
                // Set html in options
                options.content = registered?.classReference.html;
    
            }
    
            // Check html
            if(registered !== null && "classReference" in registered && "css" in registered?.classReference && registered?.classReference.css && "default" in registered?.classReference.css && typeof registered?.classReference.css.default === "string"){
    
                // Set html in options
                options.style = registered?.classReference.css.default
                    .toString()
                    .replace(/\r?\n|\r/g, "")               // Remove end of line
                    .replace(/\/\*[\s\S]*?\*\//g, "")       // Remove \/* Comment *\/
                ;
    
            } */

            // Check script
            if(script !== null){
        
                // Set script loaded
                options.scriptLoaded = script;
        
                // Set options
                options = this.setStatus(options, "scriptRegistered", true);

            }

        }

        // Return options
        return options;

    }

    /**
     * Load Content
     * 
     * Load html Content of the page
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @return Promise<LoaderPartialOptions>
     */
    public static loadContent = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.contentLoaded === true)

            // Stop function
            return options;

        // Declare state
        let stateObject:Object = {};

        // Prepare stateObject
        if(options.status?.hasState){

            // Set state
            stateObject = options.state as object;

        }else
        // Check options.scriptLoaded
        if(options.scriptLoaded && options.scriptLoaded !== null){
            
            // Get state
            // @ts-ignore
            stateObject = await Crazypage.loadPageState();

            // check options status
            if(options.status && options.status === null){

                // Set options status
                options.status = {};

                // Set status
                options.status.hasState = true;

            }

        }

        // Push stateObject into state
        options.state = stateObject;

        // Check name
        if(options.status?.scriptRegistered === true && options.scriptLoaded){

            // Push content into option
            // @ts-ignore
            options.content = options.scriptLoaded.html;
        
            // Set options
            options = this.setStatus(options, "contentLoaded", true);

        }

        // Return options
        return options;
        
    }

    /**
     * Push To Dom El
     * 
     * Push html Content into the dom
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @return Promise<LoaderPartialOptions>
     */
    public static pushToDomEl = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.contentPushedToDom === true)

            // Stop function
            return options;

        // Check dom target
        if(options.domTarget && options.content){

            // Search options.domTarget
            let els = document.querySelectorAll(options.domTarget);

            // Check els
            if(els.length == 0)

                // New error
                throw new Error(`Partial loader doesn't found into the dom element "${options.domTarget}"`);

            // Iteration els */
            els.forEach(el => {

                // Check content
                if(typeof options.content == "function"){
                    
                    // Convert content to dom
                    let node = new DOMParser().parseFromString(options.content(options.state), "text/html");

                    console.log(options.state);

                    // Check node.body.firstChild
                    if(node.body.firstChild)

                        // Replace content
                        el.replaceWith(node.body.firstChild);

                }else                    
                
                    // New error
                    throw new Error(`Partial content isn't callable`);

            
            });

            // Switch status
            Partial.setStatus(options, "contentPushedToDom", true);

        }

        // Return options
        return options;
        
    }

    /**
     * Run Script
     * 
     * Push html Content into the dom
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @return Promise<LoaderPartialOptions>
     */
    public static runScript = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.contentPushedToDom === false || options.status?.scriptRegistered === false)

            // Stop function
            return options;

        // Run script
        // @ts-ignore
        options.scriptRunning = new options.scriptLoaded();

        // Return options
        return options;
        
    }

    /**
     * Load Post Action
     * 
     * Execute custom pre actions
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadPostAction = async(options:LoaderPartialOptions):Promise<LoaderPartialOptions> =>  {

        // Check status
        if(options.status?.postActionExecuted === true)

            // Stop function
            return options;

        // Check if preAction is callable
        if(typeof options.postAction === "function")

            // Call postAction
            options = options.postAction(options);

        // Set options is loaded
        options = Partial.setStatus(options, "postActionExecuted", true);

        // Return options
        return options;
        
    }

    /** Private methods
     ******************************************************
     */

     private static catchError(error:any):void {

        // Display error
        console.error(error);

     }

    /** Private methods
     ******************************************************
     */

    /**
     * Set Status
     * 
     * Set status in options
     * 
     * @param options:LoaderPartialOptions Options with all page details
     * @param key:string Key of the status
     * @param value :boolean True of False
     * @return Promise<LoaderPartialOptions>
     */
    private static setStatus(options:LoaderPartialOptions, key:keyof LoadPartialOptionsStatus, value:boolean):LoaderPartialOptions {

        // Check status is defined in options
        if(!("status" in options) || options.status === null || !options.status)

            // Set status
            options.status = {};

        // Set key in status
        options.status[key] = value;

        // Return options
        return options;

    }

}