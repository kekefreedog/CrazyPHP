/**
 * Error
 *
 * Front TS Scrips for manage error
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Runner Error
 *
 * Methods for stop runner
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class RunnerError extends Error {

    /**
     * Constructor
     * 
     * @param message:string
     */
    constructor(message:string) {

        // Call parent
        super(message);

        
    }

}