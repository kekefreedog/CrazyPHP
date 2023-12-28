/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Runner
 *
 * Methods for retrieve run and execute them one by one
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Runner {

    /** Public methods
     ******************************************************
     */

    /**
     * Execute
     * 
     * Method to execute all 'run' methods in sequence
     * 
     * @returns 
     */
    public execute = ():Promise<any> => {

        // Get all method names starting with 'run'
        const runMethods = Object.getOwnPropertyNames(Object.getPrototypeOf(this))
            .filter(method => method.startsWith('run') && typeof this[method] === 'function')
        ;

        // Chain the methods in sequence using Promises
        let chain = Promise.resolve();

        // Run each methods
        runMethods.forEach(method => {
            chain = chain.then(() => this[method]());
        });

        // Return chain
        return chain;
        
    }

}