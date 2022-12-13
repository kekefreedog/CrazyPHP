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

/**
 * Crazy Fetch
 *
 * Methods for fetch http request
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Crazyfetch {

    /**
     * Get
     * 
     * Get Request
     * 
     * @param url 
     * @returns 
     */
    public static get = (url:string):Promise<Response> => {

        // Return fetch result
        return fetch(url)
            .then(
                response => {
                    return response;
                }
            )

    }

}