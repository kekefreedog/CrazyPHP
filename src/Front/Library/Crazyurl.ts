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
     * Get Query Parameters
     * 
     * Get all query parameters from the current URL.
     * Supports multidimensional access using PHP-style brackets or dot notation (e.g. "filters[simple_filter][color_input]" or "filters.simple_filter.color_input").
     * Can retrieve one or several parameter names.
     * 
     * @param {string | string[]} [names] Optional parameter name or list of names/paths to retrieve.
     * @returns {Record<string, any>} An object with key-value pairs representing query parameters.
     */
    public static getQueryParameters = (names?: string | string[]): Record<string, any> => {

        // Set result
        let result: Record<string, any> = {};

        // Get params
        const searchParams = new URLSearchParams(window.location.search);

        // Helper to set nested values like filters[simple_filter][color_input]
        const setNestedValue = (obj: any, path: string, value: any) => {

            // Get parts
            const parts = path
                // remove closing brackets
                .replace(/\]/g, '')
                // split by [ or .
                .split(/\[|\./g)
                // remove empty
                .filter(Boolean)
            ;

            // Set current
            let current = obj;

            // Iteration of parts
            parts.forEach((part, index) => {

                // Check index
                if (index === parts.length - 1)

                    // Set current
                    current[part] = value;

                // Else
                else{

                    // Check part
                    if (!(part in current) || typeof current[part] !== 'object')

                        // Set current part
                        current[part] = {};

                    // Set current part
                    current = current[part];

                }

            });

        };

        // Fill the main result object
        searchParams.forEach((value, key) => {

            // Try parse json
            try {

                // Try parse JSON-like values
                const parsedValue = JSON.parse(value);

                // Use nested value
                setNestedValue(result, key, parsedValue);

            // If not working
            } catch {

                // Use nested value
                setNestedValue(result, key, value);

            }

        });

        // Helper to get nested value by path "a.b.c"
        const getNestedValue = (obj: any, path: string): any => {

            // Set parts
            const parts = path
                .replace(/\]/g, '')
                .split(/\[|\./g)
                .filter(Boolean)
            ;

            // Return reduce
            return parts.reduce((acc, part) => {

                // Check acc is object
                if (acc && typeof acc === 'object' && part in acc)

                    // Return acc
                    return acc[part];

                // Return undifined
                return undefined;

            }, obj);

        };

        // Normalize names to array
        let nameList: string[] = [];

        // check names are string
        if (typeof names === 'string') 
            
            // Convert to array
            nameList = [names];

        else
        // Check if array
        if(Array.isArray(names)) 
            
            // Set name list
            nameList = names;

        // If no specific names provided → return all
        if (!nameList.length) 
            
            // Return result
            return result;

        // Filtered result
        let filtered:Record<string, any> = {};

        // Iterate through requested names (can include nested)
        nameList.forEach(name => {

            // Get nested
            const nested = getNestedValue(result, name);

            // Check nested
            if (nested !== undefined) filtered[name] = nested;

        });

        // Return filtered result
        return filtered;

    }


    /**
     * Qet Query Parameters (legacy)
     * 
     * Get all query parameters from the current URL.
     * 
     * @deprecated
     * @returns {Record<string, string>} An object with key-value pairs representing query parameters.
     */
    public static getQueryParametersLegacy = (names?:string[]):Record<string, string> => {

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
    public static addQueryParameters = (params: Record<string, any>|URLSearchParams, prefix?: string): string => {

        // Search Parameters instances
        const searchParams: URLSearchParams = new URLSearchParams(window.location.search);

        // Set values
        let values = params instanceof URLSearchParams 
            ? params.entries()
            : Object.entries(params)
        ;

        // Loop entries
        for (const [key, value] of values) {

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

    /**
     * Update Query Parameters
     * 
     * Unified method to add, update, delete, or keep query parameters.
     * - Adds parameters not present.
     * - Updates existing parameters.
     * - Removes parameters if value === undefined or value === null.
     * - Keeps parameters if value === "__KEEP__".
     * 
     * Supports nested objects, booleans, empty strings, and arrays.
     * 
     * @param params Parameters to apply.
     * @param prefix Optional prefix (used internally for recursion).
     * @returns string The updated URL.
     */
    public static updateQueryParameters = (
        params: Record<string, any> | URLSearchParams,
        prefix?: string
    ): string => {

        // Initialize current query
        const searchParams = new URLSearchParams(window.location.search);

        // Convert to iterable entries
        const entries = params instanceof URLSearchParams
            ? params.entries()
            : Object.entries(params);

        // Iteration entries
        for(const [key, value] of entries) {

            // Resolve full key (support nested)
            const paramKey = prefix ? `${prefix}[${key}]` : key;

            // Recursive support for objects
            if (typeof value === "object" && value !== null && !Array.isArray(value)) {
                Crazyurl.updateQueryParameters(value, paramKey);
                continue;
            }

            // Handle removal
            if (value === undefined || value === null) {
                searchParams.delete(paramKey);
                continue;
            }

            // Keep existing value
            if (value === "__KEEP__") {
                continue;
            }

            // Handle arrays
            if (Array.isArray(value)) {
                searchParams.delete(paramKey);
                for (const v of value) {
                    searchParams.append(paramKey, v ?? "");
                }
                continue;
            }

            // Convert booleans
            if (typeof value === "boolean") {
                searchParams.set(paramKey, value ? "true" : "false");
                continue;
            }

            // Normal values (string, number, empty)
            searchParams.set(paramKey, value === "" ? "" : String(value));

        }

        // Build new URL
        const newUrl = `${window.location.origin}${window.location.pathname}?${searchParams.toString()}`;

        // Push state (replace or push depending on your policy)
        window.history.pushState(null, "", newUrl);

        // Return new url
        return newUrl;

    };


    /**
     * Remove Query Parameters
     * 
     * Remove all query parameters by name or under a root (supports dot notation)
     * 
     * @param params {string|string[]} Parameter name(s) or root (e.g. "filters.simple_filter")
     * @returns {void}
     */
    public static removeQueryParameters = (params:string|string[]):void => {

        // Check params
        if(params && params.length){

            // Convert to array if string
            if(typeof params === "string") params = [params];

            // Create new search params instance
            const searchParams = new URLSearchParams(window.location.search);

            // Set any change
            let anyChange = false;

            // Iterate params
            for(let paramNameToRemove of params){

                // Check if root (dot notation supported)
                const root = paramNameToRemove.split('.').reduce((acc, part, index) => {
                    return index === 0 ? part : `${acc}[${part}]`;
                }, '');

                // Collect keys to delete
                const keysToDelete:string[] = [];

                // Iterate existing params
                for(const key of searchParams.keys()){

                    // Match exact name or under root (e.g. filters[simple_filter][...])
                    if(key === root || key.startsWith(`${root}[`))

                        // Pusg key
                        keysToDelete.push(key);

                }

                // Delete matched keys
                for(const key of keysToDelete){
                    searchParams.delete(key);
                    anyChange = true;
                }

            }

            // If any change, push new URL
            if(anyChange){

                // Get query string
                const queryString = searchParams.toString();

                // Set new url
                const newUrl = queryString
                    ? `${window.location.pathname}?${queryString}`
                    : window.location.pathname;

                // Push into window
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