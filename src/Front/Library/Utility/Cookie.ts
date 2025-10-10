/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Cookie
 *
 * Methods for manage cookie
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Cookie {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get cookie by name
     * 
     * @param name:string
     * @returns {string|null}
     */
    public static get = (name: string):string|null => {

        // Set result
        let result:string|null = null;

        // Check document
        if(typeof document !== 'undefined'){

            // Get value
            const value = `; ${document.cookie}`;

            // Get parts
            const parts = value.split(`; ${name}=`);

            // Check parts
            if(parts.length === 2)
                
                // Set result
                result = decodeURIComponent(parts.pop()!.split(';').shift() || '');

        }

        // Return reuslt
        return result;

    }

    /**
     * Get All
     * 
     * Get all cookies as an object
     * 
     * @returns {Record<string,string>}
     */
    public static getAll = ():Record<string,string> => {

        // Set result
        let result:Record<string,string> = {};

        // Check document
        if(typeof document !== 'undefined'){

            // Set result
            result = document.cookie
                .split(';')
                .map(v => v.trim().split('='))
                .reduce<Record<string, string>>((acc, [key, val]) => {
                    if (key) acc[key] = decodeURIComponent(val || '');
                    return acc;
                }, {})
            ;

        }

        // Return reuslt
        return result;

    }

    /**
     * Set
     * 
     * Set a cookie (default session cookie)
     * 
     * @param name Cookie name
     * @param value Cookie value
     * @param options Options
     */
    public static set = (name:string, value:string, options:CookieOptions = {}):void => {

        // Check document
        if (typeof document !== 'undefined'){

            // Set variables
            const {
                days,
                path = "/",
                domain,
                secure = false,
                sameSite = "Lax",
            } = options;

            // Set cookie
            let cookieStr = `${name}=${encodeURIComponent(value)}`;

            // Expiration
            if(days) {

                // Get date
                const date = new Date();

                // Set date 24h * 60m * 60s * 1000ms
                date.setTime(date.getTime() + days * 86400000);

                // Set cookie
                cookieStr += `; expires=${date.toUTCString()}`;

            }

            // Path
            if (path) cookieStr += `; path=${path}`;

            // Domain
            if (domain) cookieStr += `; domain=${domain}`;

            // Secure
            if (secure) cookieStr += `; Secure`;

            // SameSite
            if (sameSite) cookieStr += `; SameSite=${sameSite}`;

            // Set cookie
            document.cookie = cookieStr;

        }

    }

    /**
     * 
     * Clear
     * 
     * Clear a specific cookie or all cookies
     * 
     * @param name 
     * @returns 
     */
    public static clear = (name?:string, path:string = "/"):void => {

        // Check path
        if(!path) path = "/";

        // Check document
        if (typeof document !== 'undefined'){

            // Check name
            if(name)

                // Set cookie
                document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}`;

            else {

                // Get all
                const cookies = Cookie.getAll();

                // Clear cookie
                Object.keys(cookies).forEach(key => {
                    document.cookie = `${key}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}`;
                });

            }

        }
    }

}

/**
 * Cookie Options
 */
export interface CookieOptions {
    // Expiration in days
    days?: number;
    // Default '/'
    path?: string;
    // Example: ".example.com"
    domain?: string;
    // Use HTTPS only
    secure?: boolean;
    // SameSite policy
    sameSite?: "Strict"|"Lax"|"None";
}