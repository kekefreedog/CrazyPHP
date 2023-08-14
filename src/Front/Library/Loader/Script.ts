/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';

/**
 * Crazy Script Loader
 *
 * Methods for load JS script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Script {


    /**
     * Load
     *
     * Load JS Script after page loaded
     *
     * @source https://www.educative.io/answers/how-to-dynamically-load-a-js-file-in-javascript
     *
     * @param url:string Url to load
     * @param async
     */
    public static load = (url:string, id:string = "", async:boolean = true, type:string = "text/javascript", target:"body"|"head" = "body"): Promise<any> => {
        
        // Return a new promise
        return new Promise((resolve, reject) => {

            // Try
            try {

                // Create a htm element
                const scriptEle: HTMLScriptElement = document.createElement("script");

                // Define the type
                scriptEle.type = type;

                // Set async
                scriptEle.async = async;

                // Define utl
                scriptEle.src = url;

                // Check if id
                if(id)

                    // Set id in scriptEle
                    scriptEle.id = `script-${id}`;

                // Event load
                scriptEle.addEventListener("load", e => {

                    // Resolve status
                    resolve({ 
                        status: true
                    });

                });

                // Event error
                scriptEle.addEventListener("error", e => {

                    // Set reject
                    reject({
                        
                        // Set failed status
                        status: false,

                        // Set message
                        message: `Failed to load the script ${url}`,

                    });

                });


                // Add script in body of head
                document[target].appendChild(scriptEle);

            // Catch error
            } catch (error) {

                // Set reject
                reject(error);

            }

        });

    }

}
  