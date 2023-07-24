/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Crazyrequest from '../Crazyrequest';
import Pageregister from '../Pageregister';
import DomRoot from '../Dom/Root';
import Crazyurl from '../Crazyurl';

/**
 * Crazy Page Loader
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Page {

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(options:LoaderPageOptions){

        // Load page detail
        Page.loadPageDetail(options)
            .then(
                // Load Pre Action
                Page.loadPreAction
            )
            .then(
                // Load Pre Action
                Page.loadUrl
            )
            .then(
                // Load Script
                Page.loadScript
            )
            .then(
                // Load Script
                Page.updateUrl
            )
            .then(
                // Load Style
                Page.loadStyle
            )
            .then(
                // Load Content
                Page.loadContent
            )
            .then(
                // Load Content
                Page.loadOnReadyScript
            ).then(
                // Load Post Action
                Page.loadPostAction
            ).catch(
                err =>  {
                    console.error(err);
                }
            );

    }

    /** Punlic methods
     ******************************************************
     */

    /**
     * Load Page Detail
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
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadPageDetail = async(options:LoaderPageOptions):Promise<LoaderPageOptions> => {

        // Return options
        return options;

    }

    /**
     * Load Pre Action
     * 
     * Execute custom pre actions
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadPreAction = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check if preAction is callable
        if(typeof options.preAction === "function")

            // Call preaction
            options = options.preAction(options);

        // Set status
        options = Page.setStatus(options, "preActionLoaded", true);

        // Return options
        return options;

    }

    /**
     * Load Script
     * 
     * Load Js scripts of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadScript = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check if script already registered
        if(
            options.name && 
            window.Crazyobject.pages?.routerAction &&
            options.name in window.Crazyobject.pages.routerAction
        ){

            // Get current router action
            let currentRouterAction = window.Crazyobject.pages.routerAction[options.name];

            // Set instance in options
            options.instance = currentRouterAction.instance;

        }else
        // Register the script 
        if(options.name){

            // Load action
            await Pageregister.loadAction(options.name)
                .then(script => {
                    
                })
            ;

        }

        // Get registered
        let registered = window.Crazyobject.getRegisteredPage(options.name ? options.name : "");

        // Check html
        if(registered !== null && "classReference" in registered && "html" in registered?.classReference && registered?.classReference.html){

            // Set html in options
            options.content = registered?.classReference.html;

            // Set options
            options = this.setStatus(options, "contentLoaded", true);

        }

        // Check html
        if(registered !== null && "classReference" in registered && "css" in registered?.classReference && registered?.classReference.css && "default" in registered?.classReference.css && typeof registered?.classReference.css.default === "string"){

            // Set html in options
            options.style = registered?.classReference.css.default
                .toString()
                .replace(/\r?\n|\r/g, "")               // Remove end of line
                .replace(/\/\*[\s\S]*?\*\//g, "")       // Remove /* Comment */
            ;

            // Set options
            options = this.setStatus(options, "styleLoaded", true);

        }

        // Set script loaded
        options.scriptLoaded = registered?.classReference;

        // Set options
        options = this.setStatus(options, "scriptLoaded", true);

        // Return options
        return options;

    }

    /**
     * Load Url
     * 
     * Load url from name and arguments
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadUrl = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check url is empty
        if("url" in options && options.url !== null)

            // Return options
            return options;

        // Prepare query
        let query:Object = {
            filters: {
                name: options.name
            },
            options: {}
        };

        // Check options arguments
        if("arguments" in options && options.arguments !== null){

            // Add options
            query["options"].arguments = options.arguments;

        }

        // New request
        let request = new Crazyrequest(
            "/api/v2/Router/filter",
            {
                method: "GET",
                responseType: "json",
                from: "internal",
                ignoreHash: true
            }
        );

        // Fetch request
        await request
            .fetch(query)
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
                    options.url = result.results[0].path;
                    
                }else{

                    throw new Error(`No page corresponding to "${options.name}" in the router collection`);

                }

            })
        ;
        
        // Set status
        options = Page.setStatus(options, "urlLoaded", true);

        // Return options
        return options;

    }

    /**
     * Update Url
     * 
     * Update url of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static updateUrl = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check url in options
        if("url" in options && options.url !== null)

            // Set new url
            Crazyurl.set(options.url);

        // Return options
        return options;
    }

    /**
     * Load Style
     * 
     * Load Css styles of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadStyle = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check if css is set
        if("style" in options && typeof options.style == "string"){

            // Create style element
            let styleEl = document.createElement("style");

            // Add id to style el
            styleEl.setAttribute("id", `style-${options.name}`);

            // Set inner text
            styleEl.innerText = options.style;

            // Set status
            Page.setStatus(options, "styleLoaded", true);

            // Append the style element to the head of the document
            document.head.appendChild(styleEl);

        }

        // Return options
        return options;
        
    }

    /**
     * Load Content
     * 
     * Load html Content of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadContent = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check content
        if("content" in options && typeof options.content === "function"){

            // Get content
            let content:string = options.content({});

            // Set content of dom root
            DomRoot.setContent(content);

        }

        // Return options
        return options;
        
    }

    /**
     * Load On Ready Script Action
     * 
     * Execute on ready script
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadOnReadyScript = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check script loaded
        if("scriptLoaded" in options && options.scriptLoaded && "constructor" in options.scriptLoaded){

            // Set current class
            let currentClass:any = options.scriptLoaded;

            // New instance of this class
            let instance = new currentClass();

            // Set scriptRunning
            options.scriptRunning = instance;

        }

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
    public static loadPostAction = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check if preAction is callable
        if(typeof options.postAction === "function")

            // Call postAction
            options = options.postAction(options);

        // Set options is loaded
        options = Page.setStatus(options, "postActionLoaded", true);

        // Return options
        return options;
        
    }

    /** Punlic methods
     ******************************************************
     */

    /**
     * Set Status
     * 
     * Set status in options
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @param key:string Key of the status
     * @param value :boolean True of False
     * @return Promise<LoaderPageOptions>
     */
    private static setStatus(options:LoaderPageOptions, key:keyof LoadPageOptionsStatus, value:boolean):LoaderPageOptions {

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