/**
 * Operation
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Operation
 *
 * Process operations in string
 *
 * Parse operations into string
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Operation {

    /** Public static constant
     ******************************************************
     */

    /** List of operations available */
    public static readonly LIST: Record<string, OperationDefinition> = {
        "=": {
            name: "equal",
            operation: "=",
            regex: /^=(.*)$/
        },
        "!=": {
            name: "notEqual",
            operation: "!=",
            regex: /^!=(.*)$/
        },
        "<=": {
            name: "lessThanOrEqual",
            operation: "<=",
            regex: /^<=(.*)$/
        },
        ">=": {
            name: "greaterThanOrEqual",
            operation: ">=",
            regex: /^>=(.*)$/
        },
        "<": {
            name: "smaller",
            operation: "<",
            regex: /^<(.*)$/
        },
        ">": {
            name: "greater",
            operation: ">",
            regex: /^>(.*)$/
        },
        "![]": {
            name: "notBetween",
            operation: "![]",
            regex: /!\[([^:\[\]]*):([^\[\]]+)\]/
        },
        "[]": {
            name: "between",
            operation: "[]",
            regex: /\[([^\]:]+):([^\]]+)\]/
        },
        "*": {
            name: "like",
            operation: "*",
            // REGEX WORKING BUT CATCHING [10:10] or [!10:10] 🔴
            regex: /(?:\*([^*\n]+)\*|\*([^*\n]+)|([^*\n]+)\*)/
        },
    };

    /** Public parameters
     ******************************************************
     */

    /** Options */
    public options: OperationOptions = {
        prefix: "",
        suffix: "",
        key: "",
    };

    /** Private parameters
     ******************************************************
     */

    /** Current operations */
    private _currentOperations: Record<string, OperationDefinition> = {};

    /**
     * Constructor
     *
     * Construct and prepare instance
     *
     * @param operations Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "@all" (for all operations)
     * @param options
     */
    public constructor(operations:string|string[] = "@all", options:OperationOptions = {}) {

        // Ingest options
        this._ingestOptions(options);

        // Set operations
        this.set(operations);

    }

    /** Public methods | Operations
     ******************************************************
     */

    /**
     * Set
     *
     * Set Operations
     *
     * @param operations
     */
    public set(operations:string|string[] = "@all"):void {

        // Reset current operations
        this._currentOperations = {};

        // Check if empty
        if(operations == "@all"){

            // Set operation
            this._currentOperations = { ...Operation.LIST };

        }else
        // If string
        if(typeof operations === "string" && operations){

            // Check if key set
            if(Object.prototype.hasOwnProperty.call(Operation.LIST, operations))

                // Set operations
                this._currentOperations[operations] = Operation.LIST[operations];

            else

                // Iteration of operations
                for(const key in Operation.LIST)

                    // Check operations name
                    if(Operation.LIST[key]?.name == operations)

                        // Set current operations
                        this._currentOperations[key] = Operation.LIST[key];

        }else
        // If array
        if(Array.isArray(operations) && operations.length){

            // Iteration of operations
            for(const operation of operations)

                // Check if key set
                if(Object.prototype.hasOwnProperty.call(Operation.LIST, operation))

                    // Set operations
                    this._currentOperations[operation] = Operation.LIST[operation];

                else

                    // Iteration of operations
                    for(const key in Operation.LIST)

                        // Check operations name
                        if(Operation.LIST[key]?.name == operation)

                            // Set current operations
                            this._currentOperations[key] = Operation.LIST[key];

        }

    }

    /**
     * Get
     *
     * Get Operations
     *
     * @returns {Record<string, OperationDefinition>}
     */
    public get():Record<string, OperationDefinition> {

        // Set result
        const result = this._currentOperations;

        // Return result
        return result;

    }

    /**
     * Run
     *
     * Process input value
     *
     * @param input
     * @param options Override existing options
     * @returns {any}
     */
    public run(input:any, options:OperationOptions = {}):any {

        // Get options
        let runOptions = { ...this.options };

        // Check options
        if(options && Object.keys(options).length)

            // Set options
            runOptions = this._ingestOptions(options, runOptions);

        // Set result
        let result: any = null;

        // Is string
        let isString = false;

        // Set current value (used by parseDefault fallback)
        let lastValue: any = undefined;

        // Check input is array
        if(!Array.isArray(input)){

            // Convert to array
            input = input
                ? [input]
                : []
            ;

            // Set is string
            isString = true;

        }

        // Iteration of current operation
        if(input.length && Object.keys(this._currentOperations).length){

            // Operation found
            let operationFound = false;

            // Iterations inputs
            outer:
            for(const v of input){

                // Set last value
                lastValue = v;

                // Iteration of current operations
                for(const key in this._currentOperations){

                    // Set operation
                    const operation = this._currentOperations[key];

                    // Set matches
                    let matches: RegExpMatchArray | null = null;

                    // Check regex
                    if(
                        typeof v !== "number" &&
                        operation?.name &&
                        operation?.regex instanceof RegExp &&
                        (matches = String(v).match(operation.regex))
                    ){

                        // Set method name
                        const methodName = "parse" + operation.name.charAt(0).toUpperCase() + operation.name.slice(1);

                        // Process matches
                        const processedMatches = this._processMatches(Array.from(matches), operation);

                        // Check if method exists
                        if(typeof (this as any)[methodName] === "function")

                            // Check if isString
                            if(isString){

                                // Run method found
                                result = (this as any)[methodName](processedMatches, operation, runOptions);

                            }else{

                                // Run method found
                                result = result ?? [];
                                result.push((this as any)[methodName](processedMatches, operation, runOptions));

                            }

                        // Set operation found
                        operationFound = true;

                        // Continue
                        break outer;

                    }

                }

            }

            // Check operation found
            if(!operationFound)

                // Set result
                result = this.parseDefault(lastValue, runOptions);

        }else
        // check is string
        if(isString){

            // Set result
            result = this.parseDefault(input[0] ?? "", runOptions);

        }else{

            // Set result
            result = [];

            // Iteration input
            for(const v of input)

                // Set result
                result.push(this.parseDefault(v, runOptions));

        }

        // Return result
        return result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Process Matches
     *
     * Method to filter some specific anomaly produced by regex
     *
     * @param matches
     * @param operation
     * @returns {any[]}
     */
    private _processMatches(matches:any[], operation:OperationDefinition):any[] {

        // Set result
        let result = matches;

        // Check if like
        if(operation?.name == "like" && result?.length){

            // Filter empty / wildcard values
            result = result.filter((value) => value != "*" && value !== "");

        }

        // Return result
        return result;

    }

    /** Public methods | Parser
     ******************************************************
     */

    /**
     * Equal
     *
     * Exemple : `=value`
     * Description : Checks if a value is equal to `value`
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseEqual(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Not Equal
     *
     * Exemple : `!=value`
     * Description : Checks if a value is not equal to `value`
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseNotEqual(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Less Than or Equal
     *
     * Exemple : `<=10`
     * Description : Checks if a value is less than or equal to 10
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseLessThanOrEqual(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Greater Than or Equal
     *
     * Exemple : `>=10`
     * Description : Checks if a value is greater than or equal to 10
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseGreaterThanOrEqual(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Smaller
     *
     * Exemple : `<10`
     * Description : Checks if a value is smaller than 10.
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseSmaller(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Greater
     *
     * Exemple : `>10`
     * Description : Checks if a value is greater than 10
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseGreater(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Between
     *
     * Exemple : `[1:10]`
     * Description : Checks if a value is between 1 and 10 (inclusive)
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseBetween(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Not Between
     *
     * Exemple : `![1:10]`
     * Description : Checks if a value is not between 1 and 10
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseNotBetween(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Return input
        return result;

    }

    /**
     * Parse Default
     *
     * Description : No operations found
     *
     * @param input
     * @param options
     * @returns {OperationResult}
     */
    public parseDefault(input:any, options:OperationOptions = {}):any {

        // Push input in operations
        const operation: OperationResult = {
            name: "default",
            value: input
        };

        // Return input
        return operation;

    }

    /**
     * Like
     *
     * Exemple : `*value`
     * Description : Performs a pattern match (like SQL's LIKE)
     *
     * @param input
     * @param operation
     * @param options
     * @returns {OperationResult}
     */
    public parseLike(input:string|any[], operation:OperationDefinition, options:OperationOptions = {}):any {

        // Push input in operations
        const result: OperationResult = { ...operation, value: input };

        // Set value
        const value: string = Array.isArray(result.value) ? result.value[0] : result.value;

        // Set start
        let start = false;

        // Set end
        let end = false;

        // Check * at the start
        if(value?.indexOf("*") === 0)

            // Set start
            start = true;

        // Check * at the end
        if(value && value.lastIndexOf("*") === value.length - 1)

            // Set end
            end = true;

        // Check at start
        if(start && !end)

            // Set position
            result.position = "start";

        else
        // Check at the end
        if(!start && end)

            // Set position
            result.position = "end";

        else
        // Check at start and end
        if(start && end)

            // Set position
            result.position = "start,end";

        // If not found
        else

            // Set position
            result.position = null;

        // Set case sensitive
        result.case_sensitive = false;

        // Return input
        return result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest Options
     *
     * @param options
     * @param tempOptions (set options if is an object)
     * @returns {OperationOptions}
     */
    private _ingestOptions(options:OperationOptions = {}, tempOptions:OperationOptions|null = null):OperationOptions {

        // Check options
        if(options && Object.keys(options).length)

            // Iteration options
            for(const k in this.options)

                // Check key exists in options
                if(Object.prototype.hasOwnProperty.call(options, k)){

                    // Check temp options
                    if(tempOptions)

                        // Set temp options
                        tempOptions[k] = options[k];

                    // Fill instance option
                    else

                        // Push value in instance option
                        this.options[k] = options[k];

                }else
                // Check temp options
                if(tempOptions)

                    // Fill only temp options
                    tempOptions[k] = this.options[k];

        // Return temp options (or instance options)
        return tempOptions ?? this.options;

    }

}

/**
 * Interafaces & Types
 */
export type OperationName =
    | "equal"
    | "notEqual"
    | "lessThanOrEqual"
    | "greaterThanOrEqual"
    | "smaller"
    | "greater"
    | "notBetween"
    | "between"
    | "like"
;

export interface OperationDefinition {
    name: OperationName;
    operation: string;
    regex: RegExp;
    [key: string]: any;
}

export interface OperationOptions {
    prefix?: string;
    suffix?: string;
    key?: string;
    [key: string]: any;
}

export interface OperationResult {
    name: string;
    operation?: string;
    regex?: RegExp;
    value?: any;
    position?: string | null;
    case_sensitive?: boolean;
    [key: string]: any;
}
