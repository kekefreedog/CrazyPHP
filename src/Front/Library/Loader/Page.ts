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
                // Load Style
                Page.loadStyle
            )/*
            .then(
                // Load Content
                Page.loadContent
            ).then(
                // Load Post Action
                Page.loadPostAction
            ).catch(
                err =>  {
                    console.log(PageError);
                }
            ) */

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
        if("html" in registered?.classReference && registered?.classReference.html){

            // Set html in options
            options.content = registered?.classReference.html;

            // Set options
            options = this.setStatus(options, "contentLoaded", true);

        }

        // Check html
        if("css" in registered?.classReference && registered?.classReference.css){

            // Set html in options
            options.style = registered?.classReference.css.default.toString();

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
     * Load Style
     * 
     * Load Css styles of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public static loadStyle = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        console.log(options);

        // Return options
        return options;
        
    }

    /**
     * Load Content
     * 
     * Load html Content of the page
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public static loadContent = async(options:LoaderPageOptions):Promise<LoaderPageOptions> =>  {

        // Return options
        return options;
        
    }

    /**
     * Load Post Action
     * 
     * Execute custom pre actions
     * 
     * @param options:LoaderPageOptions Options with all page details
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
     * 
     * @param key:string Key of the status
     * @param value 
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