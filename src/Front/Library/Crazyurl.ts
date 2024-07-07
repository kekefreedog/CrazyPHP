/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Crazyurl {

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
     * Get 
     * 
     * Get current url
     * 
     * @param clearParameters Clear get parameters
     * @return {string}
     */
    public static get = (clearParameters:boolean = false):string => {

        // Prepare result
        let result:string = window.location.href;

        // Check if clear parameters
        if(clearParameters){

            // New url object
            const urlObj = new URL(result);

            // Set result
            result = urlObj.origin + urlObj.pathname;

        }
        
        // Return result
        return result;
        
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

    /**
     * To Query String
     * 
     * Convert multidimensional object to query string
     * 
     * @source https://stackoverflow.com/questions/26084733/convert-multidimensional-object-to-query-string
     * @param obj 
     * @param prefix 
     * @returns {string}
     */
    public static toQueryString = (obj:object, prefix:string = ""):string => {

        var str = [], k, v;
        for(var p in obj) {
            if (!obj.hasOwnProperty(p)) {continue;} // skip things from the prototype
            if (~p.indexOf('[')) {
                k = prefix ? prefix + "[" + p.substring(0, p.indexOf('[')) + "]" + p.substring(p.indexOf('[')) : p;
            // only put whatever is before the bracket into new brackets; append the rest
            } else {
                k = prefix ? prefix + "[" + p + "]" : p;
            }
            v = obj[p];
            // @ts-ignore
            str.push(typeof v == "object" ?
              Crazyurl.toQueryString(v, k) :
              encodeURI(k) + "=" + encodeURIComponent(v)
            );
        }
        return str.join("&");

    }

}