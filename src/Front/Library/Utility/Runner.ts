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
import {default as UtilityProcess} from "./Process";

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

    /**
     * Constructor
     */
    public constructor() {

        // Set options
        this._options = {
            _info: {
                status: "Waiting",
                run: {
                    total: 0,
                    current: 0,
                    name: []
                }
            }
        }
        
    }

    /** Parameters
     ******************************************************
     */

    /** @param _options */
    private _options:RunnerOption;

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
    public execute = ():Promise<RunnerOption> => {

        // Get run methods
        let runMethods = this.getAllMethodsStartingByRun();

        // Load Options
        let options = this._loadOptions();

        // Chain the methods in sequence using Promises
        let chain = Promise.resolve(options);

        // Initial setup before any methods
        chain = chain.then(
            options => {

                // Set info
                options._info.run.total = runMethods.length;
                
                // Set current
                options._info.run.current = 0;

                // Set options > status
                options._info.status = "Ready";

                // Iteration methods
                for(let method of runMethods)

                    // Push info
                    options._info.run.name.push({
                        method: method,
                        label: UtilityProcess.spaceBeforeCapital(method)
                    });

                // Return "abstract" class
                return this.setUpBeforeClass(options);

            }
        );

        // Run each methods
        runMethods.forEach(method => {
            chain = chain
                .then(options => {
                    options._info.run.current++;
                    if(options._info.run.current>0)
                        options._info.status = "In Progress";
                    return this.setUpBeforeMethod(options)
                })
                .then(this[method])
                .then(this.tearDownAfterMethod);
        });

        // Final teardown after all methods
        chain = chain.then(options => {
            options._info.status = "Complete";
            return this.tearDownAfterClass(options)
        });

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
     * @return {Promise<RunnerOption>}
     */
    public setUpBeforeClass = async (options:RunnerOption):Promise<RunnerOption> => {

        // Return object
        return options;

    }

    /**
     * Set Up Before Method
     * 
     * Method call before every method
     * 
     * @return {Promise<RunnerOption>}
     */
    public setUpBeforeMethod = async (options:RunnerOption):Promise<RunnerOption> => {

        // Return object
        return options;

    }

    /**
     * Tear Down After Method
     * 
     * Class call at the end of the runner
     * 
     * @return {Promise<RunnerOption>}
     */
    public tearDownAfterMethod = async (options:RunnerOption):Promise<RunnerOption> => {

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
    public tearDownAfterClass = async (options:RunnerOption):Promise<RunnerOption> => {

        // Return object
        return options;

    }

    /** Public methods | Options
     ******************************************************
     */

    /**
     * Register Options
     * 
     * @param {RunnerOption}
     * @returns {void}
     */
    public registerOptions = (options:RunnerOption):void => {

        // Set options
        this._options = options;

    }

    /** Private methods | Options
     ******************************************************
     */

    /**
     * Load Options
     * 
     * @returns {RunnerOption}
     */
    private _loadOptions = ():RunnerOption => {

        return this._options;

    }

    /** Private methods | Utilities
     ******************************************************
     */

    /**
     * Get All Methods Starting By Run
     * 
     * @source https://stackoverflow.com/questions/31054910/get-functions-methods-of-a-class
     * 
     * @returns {Array<string>}
     */
    private function getAllMethodsStartingByRun = ():Array<string> => {

        const props = [];
        let obj = this;
        do {
            // @ts-expect-error
            props.push(...Object.getOwnPropertyNames(obj));
        } while (obj = Object.getPrototypeOf(obj));
        
        return props.sort().filter((e, i, arr) => { 
            // @ts-expect-error
            if (e!=arr[i+1] && typeof this[e] == 'function' && e.startsWith('run')) return true;
        });

    }

}