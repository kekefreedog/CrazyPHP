/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Crazy alert
 *
 * Methods for manage news, info, warning, error... on front
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Crazyalert {

    /** Private Parameters
     ******************************************************
     */

    /** @var _drivers */
    private _drivers:Record<string, CrazyAlertDriver> = {};

    /**
     * Constructor
     * 
     * @param globalAlerts
     */
    public constructor(globalAlerts:Record<string, CrazyAlertDriverConstructor>){

        // Check globalAlerts
        if(Object.keys(globalAlerts).length)

            // Iteration globalAlerts
            for(let key in globalAlerts)

                // Set driver
                this.setDriver(globalAlerts[key], key);

    }

    /** Public methods | Driver
     ******************************************************
     */

    /**
     * Set Driver
     * 
     * @param alertDriver
     */
    public setDriver = (instance:CrazyAlertDriverConstructor, name:string):void => {

        // Check if is first
        var isFirst = (Object.keys(this._drivers).length == 0);

        // Check name if
        if(name){

            // Set instance into drivers
            this._drivers[name] = new instance();

            // Define new poperty
            Object.defineProperty(this, name, {

                // Set getter
                get: () => this._drivers[name],

                // Set setter
                set: () => {},

            });

            // Check if is first
            if(isFirst){

                // Set default instance into drivers
                this._drivers["default"] = new instance();
    
                // Define new poperty
                Object.defineProperty(this, "default", {
    
                    // Set getter
                    get: () => this._drivers["default"],
    
                    // Set setter
                    set: () => {},
    
                });

            }

        }

    }

    /**
     * Get Driver
     * 
     * @returns {CrazyAlertDriver|null}
     */
    public getDriver = (name:string):CrazyAlertDriver|null => {

        // Set result
        let result = name && Object.keys(this._drivers).length && name in this._drivers
            ? this[name]
            : null
        ;

        // Return result
        return result;

    }

    /** Public methods | Parse
     ******************************************************
     */

    /**
     * Parse Errors
     * 
     * Parse errors objects, coming from back response for exemple
     * 
     * @param errors:CrazyError|CrazyError[]
     * @param options:CrazyAlertOptions
     * @returns {void}
     */
    public parseErrors = (errors:CrazyError|CrazyError[], options?:Partial<CrazyAlertParserOptions>):void => {

        // Check if errors is array
        if(!Array.isArray(errors))

            // Convert errors ot array
            errors = [errors];

        // Check error
        if(errors.length)

            // Iteration errors
            for(let error of errors){

                // Get type
                var errorType = error.type && error.type in this.type
                    ? error.type 
                    : "error"
                ;

                // Run correct instance
                // @ts-ignore
                this[errorType](error);

            }

    }

    /** Public methods | Display
     ******************************************************
     */

    /**
     * News
     * 
     * Send news toast on front
     * 
     * @returns {void}
     */
    public news = (input?:CrazyAlertNewsInput, options?:null|Partial<CrazyAlertOptions>):void => {

        // Set driverName
        let driverName = options && options.driver 
            ? options.driver
            : "default"
        ;

        // Check drivername
        let driver = this.getDriver(driverName)

        // Check driver
        if(driver){

            // Run action
            driver.news(input, options);

        }else

            // Check not driver
            options?.postAction && options?.postAction();

    }

    /**
     * Info
     * 
     * Send info toast on front
     * 
     * @returns {void}
     */
    public info = (input?:CrazyAlertInfoInput, options?:null|Partial<CrazyAlertOptions>):void => {

        // Set driverName
        let driverName = options && options.driver 
            ? options.driver
            : "default"
        ;

        // Check drivername
        let driver = this.getDriver(driverName)

        // Check driver
        if(driver){

            // Run action
            driver.info(input, options);

        }else

            // Check not driver
            options?.postAction && options?.postAction();

    }

    /**
     * Success
     * 
     * Send success toast on front
     * 
     * @returns {void}
     */
    public success = (input?:CrazyAlertSuccessInput, options?:null|Partial<CrazyAlertOptions>):void => {

        // Set driverName
        let driverName = options && options.driver 
            ? options.driver
            : "default"
        ;

        // Check drivername
        let driver = this.getDriver(driverName)

        // Check driver
        if(driver){

            // Run action
            driver.success(input, options);

        }else

            // Check not driver
            options?.postAction && options?.postAction();

    }

    /**
     * Warning
     * 
     * Send warning toast on front
     * 
     * @returns {void}
     */
    public warning = (input?:CrazyAlertWarningInput, options?:null|Partial<CrazyAlertOptions>):void => {

        // Set driverName
        let driverName = options && options.driver 
            ? options.driver
            : "default"
        ;

        // Check drivername
        let driver = this.getDriver(driverName)

        // Check driver
        if(driver){

            // Run action
            driver.warning(input, options);

        }else

            // Check not driver
            options?.postAction && options?.postAction();

    }

    /**
     * Error
     * 
     * Send error toast on front
     * 
     * @returns {void}
     */
    public error = (input?:CrazyAlertErrorInput, options?:null|Partial<CrazyAlertOptions>):void => {

        // Set driverName
        let driverName = options && options.driver 
            ? options.driver
            : "default"
        ;

        // Check drivername
        let driver = this.getDriver(driverName)

        // Check driver
        if(driver){

            // Run action
            driver.error(input, options);

        }else

            // Check not driver
            options?.postAction && options?.postAction();

    }

    /** Public readonly
     ******************************************************
     */

    public readonly type = ["news", "info", "success", "warning", "error"];

}