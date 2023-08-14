/**
 * Error
 *
 * Front TS Scrips for manage error
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Crazy Page Loader
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Page extends Error {

    /**
     * Constructor
     * 
     * @param message:string  
     * @param return self
     */
    constructor(message:string) {

        // Call parent
        super(message);

        
    }

}