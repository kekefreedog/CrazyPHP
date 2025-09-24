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

        // Get url
        let url: string;

        // Check path
        if(newPathname instanceof URL){

            // Set url
            url = newPathname.toString();
        
        // Else
        } else {

            // Make sure it's relative to current origin
            url = new URL(newPathname, window.location.origin).toString();

        }

        // Remove origin
        if(url.startsWith(window.origin)) url = url.replace(window.origin, "")

        // Update
        window.history.pushState({}, "", url);

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
     * Qet Query Parameters
     * 
     * Get all query parameters from the current URL.
     * 
     * @returns {Record<string, string>} An object with key-value pairs representing query parameters.
     */
    public static getQueryParameters = (names?:string[]):Record<string, string> => {

        // Set result
        let result:Record<string, string> = {};

        // Get params
        const searchParams = new URLSearchParams(window.location.search);

        // Iteration params
        if(!names || !Array.isArray(names)) searchParams.forEach((value, key) => {

                // Fill result
                result[key] = value;

            });
    
        // Iteration params
        else {

            // Check names
            if(names.length)
            
                // Iteration params
                searchParams.forEach((value, key) => {

                    // Check if key in names
                    if(names.includes(key))

                        // Fill result
                        result[key] = value;

                });

        }

        // Return result
        return result;

    }


    /**
     * Add Query Parameters
     * 
     * Add Query Parameters with support for booleans, null values, empty strings, and keys without values.
     * 
     * @param params Parameters to add 
     * @param prefix Optional Prefix (for recursive loop)
     * @return string
     */
    public static addQueryParameters = (params: Record<string, any>, prefix?: string): string => {

        // Search Parameters instances
        const searchParams: URLSearchParams = new URLSearchParams(window.location.search);

        // Loop entries
        for (const [key, value] of Object.entries(params)) {

            // Get current key
            const paramKey: string = prefix ? `${prefix}[${key}]` : key;

            // Check if value is an object (not null) for recursion
            if (typeof value === "object" && value !== null) {

                // Recursive call for nested objects
                Crazyurl.addQueryParameters(value, paramKey);

            } else if (typeof value === "boolean") {

                // Check already has param key
                !paramKey.includes("[]") && searchParams.has(paramKey)
                    // Set 
                    ? searchParams.set(paramKey, value ? "true" : "false")
                    // Append
                    : searchParams.append(paramKey, value ? "true" : "false")
                ;

            } else if (value === null || value === "") {

                // Check already has param key
                !paramKey.includes("[]") && searchParams.has(paramKey)
                    // Set 
                    ? searchParams.set(paramKey, "")
                    // Append 
                    : searchParams.append(paramKey, "")
                ;

            } else {

                // Check already has param key
                !paramKey.includes("[]") && searchParams.has(paramKey)
                    // Set 
                    ? searchParams.set(paramKey, value)
                    // Append 
                    : searchParams.append(paramKey, value)
                ;

            }
        }

        // Generate new URL with query parameters
        const newUrl: string = `${window.location.origin}${window.location.pathname}?${searchParams.toString()}`;

        // Set the URL (you may have a function for this)
        Crazyurl.set(newUrl);

        // Return the new URL
        return newUrl;
    }



    /** Public static methods
     ******************************************************
     */

    /**
     * Remove Query Parameters
     * 
     * Remove Query Parameters
     * 
     * @param params:string|string[
     * @returns {void}
     */
    public static removeQueryParameters = (params:string|string[]):void => {

        // Check params
        if(params && params.length){

            // Check if string
            if(typeof params === "string")

                // Convert to array
                params = [params];

            // New search param instance
            var searchParams = new URLSearchParams(window.location.search);

            // Set any change
            let anyChange = false;

            // Iteration params
            for(let paramNameToRemove of params)

                // Check params is the url
                if(searchParams.has(paramNameToRemove)){

                    // Remove param
                    searchParams.delete(paramNameToRemove);
                    
                    // Set any change
                    anyChange = true;

                }

            // Check any change 
            if(anyChange){

                // Set new url
                const newUrl = `${window.location.pathname}?${searchParams.toString()}`;

                // Push in browser
                window.history.pushState(null, "", newUrl);

            }


        }
        

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

    /**
     * Extract Query And Url
     * 
     * @param inputUrl 
     * @returns 
     */
    public static extractQueryAndUrl = (inputUrl: string):{url:string; query:Record<string,string>} => {

        // Set result
        let result:{url:string; query:Record<string,string>} = {
            url: "",
            query: {}
        };

        // Get url
        const url = new URL(inputUrl);

        // Prepare query
        const query: Record<string, string> = {};
      
        // Iteration params
        url.searchParams && url.searchParams.forEach((value, key) => {

            // Append to query
            query[key] = value;

        });
      
        // Reconstruct URL without query string
        const baseUrl = `${url.origin}${url.pathname}`;
      
        // Set url
        result.url = baseUrl;

        // Set query
        result.query = query;

        return result;

    }

}