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
/*             .then(
                // Load Pre Action
                Page.loadPreAction
            ) */
            .then(
                // Load Pre Action
                Page.loadUrl
            )
/*             .then(
                // Load Script
                Page.loadScript
            )
            .then(
                // Load Style
                Page.loadStyle
            )
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
        options.status.preActionLoaded = true;

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

                    throw new Error(`No page corresponding to "${name}" in the router collection`)x

                }

            })
        ;

        console.log(options);

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
        options.status.postActionLoaded = true;

        // Return options
        return options;
        
    }


}