/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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
 * @copyright  2022-2022 Kévin Zarshenas
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
    public static set = (newPathname:string = ""):void => {

        // Push new pathname
        window.history.pushState(null, "", newPathname);

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

}