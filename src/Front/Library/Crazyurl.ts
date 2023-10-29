/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazyrequest from "./Crazyrequest";

/**
 * Crazy Url
 *
 * Methods for manipulate URL of the current page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Crazyurl{

    /** Public methods
     ******************************************************
     */

    /**
     * Set
     * 
     * Set pathname
     * 
     * @param newPathname:string New pathname : exemple /home/page/
     * @return void
     */
    public static set = (newPathname:string|URL = ""):void => {

        // Convert path to url if is a string
        if(typeof newPathname === "string"){

            newPathname = new URL(newPathname);

        }

        // Push new pathname
        window.history.pushState({}, "", newPathname.toString());

    }

    /**
     * Add Query Parameters
     * 
     * Add Query Parameters
     * 
     * @source chatgpt
     * 
     * @param params Parameters to add 
     * @param prefix Optional Prefix (for recursive loop)
     * @return string
     */
    public static addQueryParameters = (params: Record<string, any>, prefix?: string):string => {

        // Search Parameters instances
        const searchParams:URLSearchParams = new URLSearchParams(window.location.search);
      
        // Loop entries
        for (const [key, value] of Object.entries(params)) {

            // Get current key
            const paramKey:string = prefix ? `${prefix}[${key}]` : key;
      
            // Check if object
            if (typeof value === "object") {

                // Recursive call
                const subParams = Crazyurl.addQueryParameters(value, paramKey);

                // Add to search param
                searchParams.append(subParams, "");

            } else

                // Add to search param
                searchParams.append(paramKey, value);

        }
      
        // New url
        const newUrl:string = `${window.location.pathname}?${searchParams.toString()}`;

        // Set url
        Crazyurl.set(newUrl);

        // Return new url
        return newUrl;

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Remove Query Parameters
     * 
     * Remove Query Parameters
     */
    public static removeQueryParameters = () => {

        const paramNameToRemove = "myParam";
        const searchParams = new URLSearchParams(window.location.search);
        searchParams.delete(paramNameToRemove);
        const newUrl = `${window.location.pathname}?${searchParams.toString()}`;
        window.history.pushState(null, "", newUrl);
        

    }

    /**
     * Is Same Domain
     * 
     * Check if given url is same domain
     * 
     * @param href 
     * @returns boolean
     */
    public static isSameDomain = (href:string):boolean => {

        // Declare result
        let result:boolean = false;

        // Check href
        if(!href)

            // Return result
            return result;

        // Try
        try {

            // URL object
            const hrefUrl = new URL(href);
    
            // Compare the origins of the two URLs
            result = window.location.origin === hrefUrl.origin;

        // Catch error
        } catch (error) {

            // Invalid URL

            result = false;
            
        }

        // Return result
        return result;

    }

}