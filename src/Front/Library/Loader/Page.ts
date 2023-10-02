/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as LoaderScript} from './../Loader/Script';
import {default as PageError} from './../Error/Page';
import Crazyrequest from './../Crazyrequest';
import Pageregister from './../Pageregister';
import Crazyurl from '../Crazyurl';
import DomRoot from '../Dom/Root';

/**
 * Crazy Page Loader
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Page {

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(options:LoaderPageOptions){

        // Load page detail
        return Page.loadPageDetail(options)
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
                // Update Title
                Page.updateTitle
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
                Page.registerInHistory
            )
            .then(
                (options:LoaderPageOptions) => {
                    // Check ready state
                    if(document.readyState !== 'loading') {
                        // Load Content
                        Page.loadOnReadyScript(options)
                            .then(
                                // Load Post Action
                                Page.loadPostAction
                            ).catch(
                                Page.catchError
                            );
                    }else{
                        // Event listener
                        document.addEventListener('DOMContentLoaded', () => {
                            // Load Content
                            Page.loadOnReadyScript(options)
                                .then(
                                    // Load Post Action
                                    Page.loadPostAction
                                ).catch(
                                    Page.catchError
                                );
                        });
                    }

                }
            ).catch(
                Page.catchError
            );

    }

    /** Public methods 
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

        // Let keys
        let keys:Array<keyof LoadPageOptionsStatus> = ["isCurrentPage", "scriptRegistered", "urlLoaded", "preActionExecuted", "urlUpdated", "titleUpdated", "styleLoaded", "contentLoaded", "onReadyExecuted", "historyRegistered","postActionExecuted"];

        // Prepare options
        for(let currentKey of keys){

            // Check key is in options status
            if(options.status !== undefined && options.status !== null && currentKey in options.status)

                // Continue
                continue;
            
            // Set status
            options = Page.setStatus(options, currentKey, false);

        }

        // Return options
        return options;

    }

    /**
     * Load Pre Action
     * 
     * Execute custom pre actions
     * 
     * > Executed only the first time that the page is loaded
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static loadPreAction = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check status
        if(options.status?.preActionExecuted === true)

            // Stop function
            return options;

        // Check if preAction is callable
        if(typeof options.preAction === "function")

            // Call preaction
            options = options.preAction(options);

        // Set status
        options = Page.setStatus(options, "preActionExecuted", true);

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

        // Check status
        if(options.status?.scriptRegistered === false && options.name){

            // Get hash
            let hash:string|null = window.Crazyobject.hash.get();
    
            // Set url
            let url:string = `/dist/page/app/${options.name}.${hash}.js`;
    
            // Load script
            let script = await LoaderScript.load(url, options.name, true);

            // Get registered
            let registered = window.Crazyobject.registerPage.getRegistered(options.name ? options.name : "");
    
            // Check html
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
                    .replace(/\/\*[\s\S]*?\*\//g, "")       // Remove /* Comment */
                ;
    
            }
    
            // Set script loaded
            options.scriptLoaded = registered?.classReference;
    
            // Set options
            options = this.setStatus(options, "scriptRegistered", true);

        }

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

        // Check status
        if(options.status?.urlLoaded === true)

            // Stop function
            return options;

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

        // Check status
        if(options.status?.urlUpdated === true)

            // Stop function
            return options;

        // Check url in options
        if("url" in options && options.url !== null)

            // Set new url
            Crazyurl.set(options.url);

        // Return options
        return options;
    }

    /**
     * Update Title
     * 
     * Update title of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static updateTitle = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check status
        if(options.status?.titleUpdated === true)

            // Stop function
            return options;

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

        // Check status
        if(options.status?.styleLoaded === true)

            // Stop function
            return options;

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

        // Check status
        if(options.status?.contentLoaded === true)

            // Stop function
            return options;
    

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

    public static registerInHistory = async(options:LoaderPageOptions):Promise<LoaderPageOptions> => {

        // Check status
        if(options.status?.historyRegistered === true)

            // Stop function
            return options;

        // Get url
        let urlString:string = options.url ? options.url?.toString() : "";

        // Check history in window Crazyobject
        window.Crazyobject.historyPage.register({
            href: urlString,
            loader: Page.resetOptions(options),
            state: {}
        })

        // Return options
        return options

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

        // Check status
        if(options.status?.onReadyExecuted === true)

            // Stop function
            return options;

        // Check script loaded
        if("scriptLoaded" in options && options.scriptLoaded && "constructor" in options.scriptLoaded){
            
            // Set current class
            let currentClass:any = options.scriptLoaded;

            // New instance of this class
            let instance = new currentClass();

            // Set scriptRunning
            options.scriptRunning = instance;

            // Set status
            options = Page.setStatus(options, "onReadyExecuted", true);
            
        }
        
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
    public static loadPostAction = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check status
        if(options.status?.postActionExecuted === true)

            // Stop function
            return options;

        // Check if preAction is callable
        if(typeof options.postAction === "function")

            // Call postAction
            options = options.postAction(options);

        // Set options is loaded
        options = Page.setStatus(options, "postActionExecuted", true);

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

    /**
     * Reset option
     * 
     * 
     */
    private static resetOptions(options:LoaderPageOptions):LoaderPageOptions {

        // Set options status
        options = Page.setStatus(options, "isCurrentPage", false);
        options = Page.setStatus(options, "styleLoaded", false);
        options = Page.setStatus(options, "contentLoaded", false);
        options = Page.setStatus(options, "onReadyExecuted", false);
        options = Page.setStatus(options, "postActionExecuted", false);
        options = Page.setStatus(options, "urlUpdated", false);

        // Return options
        return options;

    }


}