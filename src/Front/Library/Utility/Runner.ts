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
import {default as NavigatorClient} from "./../Navigator/Client";
import {default as PartialRegister} from "../Register/Partial";
import {default as UtilityProcess} from "./Process";
import {default as UtilityArrays} from "./Arrays"
import RunnerError from "../Error/RunnerError";

/**
 * Runner
 *
 * Methods for retrieve run and execute them one by one
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Runner {

    /** Public parameters
     ******************************************************
     */

    /**
     * Viewer
     */
    public viewer:RunnerViewer|null = null;

    /** Private parameters
     ******************************************************
     */


    /**
     * Partials
     */
    private _partials:RegisterPartialScanned[] = []; 

    /**
     * Constructor
     * 
     * @param extra
     */
    public constructor(extra:any = null, viewer:RunnerViewerConstructor|null = null) {

        // Set options
        let options:RunnerOption = {
            result: {},
            _info: {
                status: "Waiting",
                name: "",
                run: {
                    total: 0,
                    current: 0,
                    name: []
                }
            }
        }

        // Check viewer
        if(viewer)

            // Setup viewer
            this.viewer = new viewer(options);

        // Check extra
        if(extra && extra !== null)

            options.extra = extra;

        // Push options
        this._options = options;
        
    }

    /** Public methods | Runner Info
     ******************************************************
     */

    /**
     * Set Name
     * 
     * Set the name of the runner
     * 
     * @param name Name
     */
    public setName = (name:string):void => {

        // Set name
        this._options._info.name = name;

    }

    /** Parameters
     ******************************************************
     */

    /** @param _options */
    private _options:RunnerOption;

    /** @param _navigatorClient */
    private _navigatorClient:NavigatorClient;

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

        // New navigator client
        this._navigatorClient = new NavigatorClient();

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
                        label: UtilityProcess.capitalize(UtilityProcess.spaceBeforeCapital(method))
                    });

                // Start prevent close
                this._navigatorClient.preventClose();

                // Check viewer
                if(this.viewer){

                    // Open viewer
                    this.viewer.open({
                        progression: `${options._info.run.current}/${options._info.run.total}`,
                        text: `Starting ${options._info.name.toLowerCase()}`
                    });

                }

                // Return "abstract" class
                return this.setUpBeforeClass(options);

            }
        );

        // Run each methods
        runMethods.forEach(method => {
            chain = chain
                .then(options => {

                    // Increment current
                    options._info.run.current++;

                    // Set in progress
                    if(options._info.run.current>0)
                        options._info.status = "In Progress";

                    // Check viewer
                    if(this.viewer){
            
                        // Open viewer
                        this.viewer.update({
                            progression: `${options._info.run.current}/${options._info.run.total}`,
                            text: `${options._info.run.name[options._info.run.current-1]?options._info.run.name[options._info.run.current-1].label:"Oups"}`
                        });
            
                    }

                    // Let result
                    let result = this.setUpBeforeMethod(options);

                    // Run setup
                    return result
                })
                .then(this[method])
                .then(
                    options => {

                        // Return last method
                        let result = this.tearDownAfterMethod(options);

                        // Close prevent close
                        this._navigatorClient.disablePreventClose();

                        // Return result
                        return result;

                    }
                );
        });

        // Final teardown after all methods
        chain = chain.then(options => {

            // Check viewer
            if(this.viewer){
    
                // Open viewer
                this.viewer.close();
    
            }

            // Set status
            options._info.status = "Complete";

            // Return custom last method
            return this.tearDownAfterClass(options)

        });

        // Catch any errors
        chain.catch(error => {

            // Check error instance of Runner error
            if(error instanceof RunnerError){

                // Check message
                if(error.message){

                    // Display message
                    console.log(error.message);

                }

            }else{

                // Display error
                error instanceof Error && this._displayError(error, options);
            
            }

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

    /** Public methods | Partials
     ******************************************************
     */

    /**
     * Clean Partials
     * 
     * @returns {void}
     */
    public cleanPartials = ():void => {

        // Clean partials
        this._partials = [];

    }

    /**
     * Load Partials
     * 
     * Load and run partials found into a given parent el
     * 
     * @param parent Parent element where search partials
     * @param runScriptsFound Run script found or not
     * @returns {RegisterPartialScanned[]}
     */
    public loadPartials = (parent:Element, runScriptsFound:boolean = true):RegisterPartialScanned[] => {

        // Set partials scanned
        let partialsScanned:RegisterPartialScanned[] = [];

        // New partials instance
        let partialRegister = new PartialRegister();

        // Scan in parent
        partialsScanned = partialRegister.scan(parent);

        // Check partial
        if(partialsScanned.length && runScriptsFound)

            // Iteration of partialsScanned
            for(let key in partialsScanned){

                // New run script
                // @ts-ignore
                partialsScanned[key].scriptRunning = new partialsScanned[key].callable(partialsScanned[key]);

            }

        // Append partials into _partials
        this._partials.push(...partialsScanned);

        // Return result
        return partialsScanned;

    }

    /**
     * Get All Partials
     * @returns {RegisterPartialScanned[]}
     */
    public getAllPartials = ():RegisterPartialScanned[] => {

        // Set result
        let result = this._partials;

        // Return result
        return result;

    }

    /**
     * Get Partial By Name
     * 
     * @param name
     * @returns {RegisterPartialScanned[]|null}
     */
    public getPartialName = (name:string):RegisterPartialScanned[]|null => {

        // Set result
        let result:RegisterPartialScanned[]|null = null;

        // Check name
        if(name){

            // Search
            let search = UtilityArrays.filterByKey(this._partials, "name", name);

            // Get search
            if(search.length){

                // Set result
                result = search as RegisterPartialScanned[];

            }

        }

        // Return search
        return result;

    }


    /** Public methods | Exit
     ******************************************************
     */

    /**
     * Stop
     * 
     * Stop runner
     * 
     * @param message 
     * @param options 
     * @param callback 
     * @returns {void}
     */
    public stop = (options:RunnerOption, message:string = "Runner stopped", callback?:(options:RunnerOption)=>void):void => {

        // Check viewer
        if(this.viewer)

            // Close viewer
            this.viewer.close();

        // Check callback
        if(callback)

            // Execute callback
            callback(options);

        // Run tearDownAfterClass
        this.tearDownAfterClass(options);

        // Stop runner
        throw new RunnerError(message);

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

    /**
     * Display Error
     * 
     * @param error 
     * @param options
     * @returns {void}
     */
    private _displayError = (error:Error, options:RunnerOption):void => {

        // Check viewer
        if(this.viewer){

            // Update with error
            this.viewer.update({error});

        }else{

            // Display error
            console.error(error.message);
            console.error('An error occurred during execution:', error);

        }
        
        // Return custom last method
        this.tearDownAfterClass(options)

    }

    /** Private methods | Utilities
     ******************************************************
     */

    /**
     * Get All Methods Starting By Run
     * 
     * @source https://stackoverflow.com/questions/31054910/get-functions-methods-of-a-class
     * 
     * @returns
     */
    private getAllMethodsStartingByRun = ():string[] => {

        const props = [];
        let obj = this;
        do{
            const properties = Object.getOwnPropertyNames(obj);
            // @ts-expect-error
            props.push(...properties);
            obj = Object.getPrototypeOf(obj);
        }while(obj)

        let propsAlt = Object.keys(this);
        
        let result = props.filter((e, i, arr) => { 
            // @ts-expect-error
            if (e!=arr[i+1] && typeof this[e] == 'function' && e.startsWith('run')) return true;
        });

        return result;

    }

}