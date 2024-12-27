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
import Crazycolor from '../Crazycolor';
import Crazyurl from '../Crazyurl';
import DomRoot from '../Dom/Root';
import State from '../State';

/**
 * Crazy Page Loader
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
                // Open New Tab (if needed)
                Page.openNewTab
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
                // Clean Potential Exisiting Partials
                Page.cleanPotentialExisitingPartials
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
                        Page.applyColorSchema(options)
                            .then(
                                // Scan Partials
                                Page.scanPartials
                            )
                            .then(
                                // Scan Partials
                                Page.loadOnReadyScript
                            )
                            .then(
                                // Load Post Action
                                Page.loadPostAction
                            )
                            .then(
                                // Set current page
                                Page.setCurrentPage
                            ).catch(
                                Page.catchError
                            );
                    }else{
                        // Event listener
                        document.addEventListener('DOMContentLoaded', () => {
                            // Load Content
                            Page.applyColorSchema(options)
                                .then(
                                    // Scan Partials
                                    Page.scanPartials
                                )
                                .then(
                                    // Scan Partials
                                    Page.loadOnReadyScript
                                )
                                .then(
                                    // Load Post Action
                                    Page.loadPostAction
                                )
                                .then(
                                    // Set current page
                                    Page.setCurrentPage
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
        let keys:Array<keyof LoadPageOptionsStatus> = ["isCurrentPage", "hasState", "scriptRegistered", "urlLoaded", "preActionExecuted", "urlUpdated", "titleUpdated", "styleLoaded", "contentLoaded", "onReadyExecuted", "historyRegistered","postActionExecuted","hasColor"];

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
                ignoreHash: true,
                cache: false,
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

                    // Check state
                    if("state" in result.results[0] && Object.keys(result.results[0].state).length){

                        // Set state in options
                        options.state = result.results[0].state;

                        // Switch status
                        Page.setStatus(options, "hasState", true);

                    }
                    
                }else{
                    
                    // Error
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
     * Open New Tab
     * 
     * Open new tab if needed
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static openNewTab = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check openInNewTab
        if("openInNewTab" in options && options.openInNewTab && options.status?.urlLoaded && "url" in options && typeof options.url === "string" && options.url){

            // Open 
            window.open(options.url, "_blank");

            // Stop promise chain
            throw 'Page open in new tab.';

        }

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

        // Declare new title
        let titleNew = options.name;

        // Check titleNew
        if(titleNew)

            // Set title
            document.title = titleNew;

        // Return options
        return options;        

    }

    /**
     * Clean Potential Exisiting Partials
     * 
     * Load Css styles of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static cleanPotentialExisitingPartials = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Set current page
        let currentPage = window.Crazyobject.currentPage.get();

        // Check current page
        if(currentPage && currentPage.partials && Array.isArray(currentPage.partials) && currentPage.partials.length)

            // Iteration partials
            for(let partialObject of currentPage.partials)

                // Check scriptRunning
                if(partialObject.scriptRunning)

                    // Execute destroy
                    partialObject.scriptRunning.onDestroy();

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
            stateObject = await options.scriptLoaded.loadPageState();

            // Set page state
            options.name && State.set().page(stateObject, options.name);

            // check options status
            if(options.status && options.status === null){

                // Set options status
                options.status = {};

                // Set status
                options.status.hasState = true;

            }

        }

        // Check content
        if("content" in options && typeof options.content === "function"){

            // Get content
            let content:string = options.content(stateObject);

            // Set content of dom root
            DomRoot.setContent(content);

        }

        // Return options
        return options;
        
    }


    /**
     * Scan Partials
     * 
     * Scan partials in html
     * 
     * @param options 
     * @returns 
     */
    public static scanPartials = async(options:LoaderPageOptions):Promise<LoaderPageOptions> => {

        // Check status
        if(options.status?.partialsScanned === true)

            // Stop function
            return options;

        // Scan partials and get result
        let partialsScanned:Object = window.Crazyobject.partials.scan("body");

        // Check partials scanned
        if(Object.keys(partialsScanned).length){

            // Set partials
            options.partials = partialsScanned;

            // Iteration partial script
            for(let partial in options.partials){

                // Run partial script
                let instance = new options.partials[partial].callable(options.partials[partial]);

                // Push instance in options
                options.partials[partial].scriptRunning = instance;

            }

        }
        
        // Set status
        options = Page.setStatus(options, "partialsScanned", true);

        // Return options
        return options

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
     * Apply Color Schema
     * 
     * Apply color schema
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static applyColorSchema = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Get document
        let doc = document;

        // Set state stored
        let pageState = options.name
            ? State.get().page(null, options.name)
            : null
        ;

        // Set state
        let state = pageState
            ? pageState
            : {...await options.scriptLoaded?.loadPageState("", false)}
        ;

        // Get potential overide source
        // @ts-ignore
        let newSource = state._ui?.materialDynamicColors?.source;

        // Get body
        let bodyEl = document.body;

        // Check new source
        if(typeof newSource === "string" && newSource){

            // Set dataset
            bodyEl.dataset.crazyColor = newSource;


        }else
        // Check if default
        if("defaultColor" in bodyEl.dataset){

            // Get default color
            let defaultSource = bodyEl.dataset.defaultColor;

            // Check defaultSource
            if(typeof defaultSource === "string" && defaultSource)

                // Set crazy color
                bodyEl.dataset.crazyColor = defaultSource;

        }

        // Scan crazy color
        let result = Crazycolor.scanCrazyColor(doc);
        
        // Check
        if(result?.length)

            // Set status
            options = this.setStatus(options, "hasColorApplied", true);
        
        // Check colors
        /* if(options.status?.hasColor && options.color){

            // Apply theme
            if(options.color.applyTheme())

                // Set status
                options = this.setStatus(options, "hasColorApplied", true);

        } */

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
            let instance = new currentClass(options);

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

    /**
     * set Current Page
     * 
     * Set current page
     * 
     * @param options:LoaderPageOptions Options with all page details
     * @return Promise<LoaderPageOptions>
     */
    public static setCurrentPage = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Check status
        if(options.status?.onReadyExecuted === true)

            // Set curret page
            window.Crazyobject.currentPage.set(options);

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
        options = Page.setStatus(options, "hasState", false);
        options = Page.setStatus(options, "styleLoaded", false);
        options = Page.setStatus(options, "contentLoaded", false);
        options = Page.setStatus(options, "onReadyExecuted", false);
        options = Page.setStatus(options, "postActionExecuted", false);
        options = Page.setStatus(options, "urlUpdated", false);

        // Return options
        return options;

    }


}