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

    /** Parameters
     ******************************************************
     */

    /** @param _options */
    private _options = {};

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

        // Load Options
        let options = this._loadOptions();

        // Chain the methods in sequence using Promises
        let chain = Promise.resolve(options);

        // Initial setup before any methods
        chain = chain.then(
           this.setUpBeforeClass
        );

        // Run each methods
        runMethods.forEach(method => {
            chain = chain
                .then(this.setUpBeforeMethod)
                .then(this[method])
                .then(this.tearDownAfterMethod);
        });

        // Final teardown after all methods
        chain = chain.then(this.tearDownAfterClass);

        // Catch any errors
        chain.catch(error => {
            console.error('An error occurred during execution:', error);
        });

        // Return chain
        return chain;

    }

    /** Public methods | Runner Preparation
     ******************************************************
     */


    /**
     * Set Up Before Class
     * 
     * Method call at the very begin of the runner
     * 
     * @return {Promise<Object>}
     */
    public setUpBeforeClass = async (options:Object):Promise<Object> => {

        // Return object
        return options;

    }

    /**
     * Set Up Before Method
     * 
     * Method call before every method
     * 
     * @return {Promise<Object>}
     */
    public setUpBeforeMethod = async (options:Object):Promise<Object> => {

        // Return object
        return options;

    }

    /**
     * Tear Down After Method
     * 
     * Class call at the end of the runner
     * 
     * @return {Promise<Object>}
     */
    public tearDownAfterMethod = async (options:Object):Promise<Object> => {

        // Return object
        return options;

    }

    /**
     * Tear Down After Class
     * 
     * Class call at the end of the runner
     * 
     * @return {Promise<Object>}
     */
    public tearDownAfterClass = async (options:Object):Promise<Object> => {

        // Return object
        return options;

    }

    /** Public methods | Options
     ******************************************************
     */

    /**
     * Register Options
     * 
     * @param {Object}
     * @returns {void}
     */
    public registerOptions = (options:Object):void => {

        // Set options
        this._options = options;

    }

    /** Private methods | Options
     ******************************************************
     */

    /**
     * Load Options
     * 
     * @returns {Object}
     */
    private _loadOptions = ():Object => {

        return this._options;

    }

}