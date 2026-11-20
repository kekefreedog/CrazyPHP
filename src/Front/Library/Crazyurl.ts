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
     * Parse bracket or dot paths using only own properties. Discard prototype-control keys
     * throughout paths and JSON values before exposing parameters to page state or forms.
     *
     * @param names Optional parameter names or nested paths to retrieve
     * @returns Parsed parameters, preserving JSON values and dense numeric arrays
     */
    public static getQueryParameters = (names?:string|string[]):Record<string, any> => {

        // Prepare result with the established plain-object contract.
        let result:Record<string, any> = {};

        // Decode the actual current URL through the browser's native parser.
        const searchParams = new URLSearchParams(window.location.search);

        // Keep object-model control keys out of subsequent state and form merges.
        const unsafeKeys = new Set(["__proto__", "constructor", "prototype"]);

        /**
         * Split Query Path
         *
         * @param path Bracket or dot notation
         * @returns Literal nonempty path components
         */
        const splitPath = (path:string):string[] => {

            // Preserve the established bracket, dot and empty-component normalization.
            const result = path.replace(/\]/g, "").split(/\[|\./g).filter(Boolean);

            // Return result.
            return result;

        };

        /**
         * Set Query Value
         *
         * @param path Decoded parameter path
         * @param value Parsed JSON or literal string
         * @returns void
         */
        const setNestedValue = (path:string, value:any):void => {

            // Split before touching any result branches.
            const parts = splitPath(path);

            // Ignore complete unsafe paths rather than retaining a misleading partial filter.
            if(parts.length && !parts.some(part => unsafeKeys.has(part))){

                // Start at the current request's own result object.
                let current = result;

                // Create only local data properties for this parameter.
                for(let index = 0; index < parts.length; index++){

                    // Read the current literal component.
                    const part = parts[index];

                    // Store leaves without invoking inherited setters.
                    if(index === parts.length - 1){

                        // Preserve the existing last-value-wins query behavior.
                        Object.defineProperty(current, part, {
                            value,
                            enumerable: true,
                            writable: true,
                            configurable: true,
                        });

                    }else{

                        // Inspect only an explicit own data property.
                        const child = Object.getOwnPropertyDescriptor(current, part)?.value;

                        // Replace scalar/null prefixes and expand array prefixes as numeric data keys.
                        if(child === null || typeof child !== "object" || Array.isArray(child)){

                            // Avoid inherited branches and special array length setters on colliding paths.
                            Object.defineProperty(current, part, {
                                value: Array.isArray(child) ? {...child} : {},
                                enumerable: true,
                                writable: true,
                                configurable: true,
                            });

                        }

                        // Follow only the explicitly stored local branch.
                        current = current[part];

                    }

                }

            }

        };

        // Preserve parameter ordering and native URL decoding.
        for(const [key, value] of searchParams){

            // Default to the exact decoded string.
            let parsedValue:any = value;

            // Parse JSON independently from object construction.
            try{

                // Retain booleans, numbers, nulls, JSON arrays and JSON objects.
                parsedValue = JSON.parse(value);

            }catch{

                // Preserve ordinary search text when it is not JSON.
                parsedValue = value;

            }

            // Write only approved paths to the local result.
            setNestedValue(key, parsedValue);

        }

        /**
         * Normalize Query Value
         *
         * @param value Parsed query subtree
         * @param convertDense Whether numeric objects should become arrays
         * @returns Clean subtree with the established array conversion behavior
         */
        const normalizeValue = (value:any, convertDense:boolean = true):any => {

            // Preserve primitive values without coercion.
            let result = value;

            // Clean JSON arrays recursively while preserving their explicit structure.
            if(Array.isArray(value)){

                // Keep numeric objects inside explicit JSON arrays as objects, as before.
                result = Array.from(value, item => normalizeValue(item, false));

            }else if(value !== null && typeof value === "object"){

                // Prepare an independent collection with ordinary object semantics.
                const clean:Record<string, any> = {};

                // Visit only own entries, including objects decoded from JSON.
                for(const [key, child] of Object.entries(value)){

                    // Remove prototype-control keys at every object depth.
                    if(!unsafeKeys.has(key)){

                        // Rebuild own data without inherited setters or properties.
                        Object.defineProperty(clean, key, {
                            value: normalizeValue(child, convertDense),
                            enumerable: true,
                            writable: true,
                            configurable: true,
                        });

                    }

                }

                // Inspect only the retained keys for dense zero-based numeric indexing.
                const keys = Object.keys(clean);

                // Preserve sparse objects, empty objects and explicit JSON-array structure.
                result = convertDense && keys.length > 0 && keys.every((key, index) => key === String(index))
                    ? keys.map(key => clean[key])
                    : clean
                ;

            }

            // Return result.
            return result;

        };

        // Sanitize every parameter before selected names reach the caller.
        result = normalizeValue(result);

        /**
         * Get Query Value
         *
         * @param path Requested bracket or dot path
         * @returns Own value or undefined when the path is absent or unsafe
         */
        const getNestedValue = (path:string):any => {

            // Prepare result from the sanitized query collection.
            let value:any = result;

            // Traverse the same normalized path format used during assignment.
            for(const part of splitPath(path)){

                // Refuse unsafe names and traversal through missing or scalar parents.
                if(unsafeKeys.has(part) || value === null || typeof value !== "object"){

                    // Mark the requested path absent.
                    value = undefined;

                    // Stop without accessing an inherited value.
                    break;

                }

                // Read only a stored own data property, never a prototype member.
                value = Object.getOwnPropertyDescriptor(value, part)?.value;

            }

            // Return result.
            return value;

        };

        // Preserve string, list and unrestricted selection contracts.
        const nameList = typeof names === "string" ? [names] : (Array.isArray(names) ? names : []);

        // Restrict output only when the caller actually supplied selected names.
        if(nameList.length){

            // Keep selected path names as literal own keys.
            const filtered:Record<string, any> = {};

            // Retain requested values without interpreting missing inherited names.
            for(const name of nameList){

                // Resolve this requested path against sanitized data.
                const nested = getNestedValue(name);

                // Preserve present false, zero, null and empty-string values.
                if(nested !== undefined){

                    // Avoid prototype setters even on the final selected-name result.
                    Object.defineProperty(filtered, name, {
                        value: nested,
                        enumerable: true,
                        writable: true,
                        configurable: true,
                    });

                }

            }

            // Publish the selected-name collection after every lookup is complete.
            result = filtered;

        }

        // Return result.
        return result;

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
            // Ignore inherited keys even when query data contains a literal hasOwnProperty field.
            if(!Object.prototype.hasOwnProperty.call(obj, p)){

                // Continue with the next own query field.
                continue;

            }
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