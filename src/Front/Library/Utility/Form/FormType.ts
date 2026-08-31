/**
 * Form
 *
 * Front TS Scrips for form type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Form Type
 *
 * Base class extended by each per-input-type handler (Text, Select, Date, ...),
 * carrying the helpers shared across all of them as static methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class FormType {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Filter Operator Value
     *
     * @param formEl
     * @param key
     * @returns {string}
     */
    public static getFilterOperatorValue(formEl:HTMLFormElement, key:string):string {

        // Set result
        let result = "";

        // Get operator name (remove [] if multiple)
        let operatorEl = formEl.querySelector(`[data-operator-name="${key.replace("[]", "")}"]`);

        // Check operator
        if(operatorEl instanceof HTMLSelectElement)
            
            // Update result 
            result = operatorEl.value;

        // Return result
        return result;

    }

    /**
     * Combine Filter Operator Value
     *
     * @param operator
     * @param value
     * @param key
     * @param options
     * @param wrapWildcard
     * @returns {any}
     */
    public static combineFilterOperatorValue(operator:string, value:any, key:string, options:Partial<FormOptions> = {}, wrapWildcard:boolean = false):any {

        // Set result
        let result:any;

        // Check if multiple value
        if(Array.isArray(value))

            // Set result : always lead with the operator so downstream (SgFilterOperation) can decode it,
            // regardless of which operator is selected — not just "!="
            result = (operator && value.length)
                ? [operator, ...value]
                : value
            ;

        else
        // No operator selected, or nothing to combine it with
        if(!operator || !value)

            // Set result
            result = value;

        else
        // Check custom operator handler
        if(options.filterOperators && typeof options.filterOperators[operator] === "function")

            // Run custom handler
            result = options.filterOperators[operator](value, key);

        else
        // Check wildcard wrap
        if(wrapWildcard && operator === "*")

            // Wrap on both sides
            result = `*${value}*`;

        // Simply add prefix
        else
        
            // Push as prefix
            result = `${operator}${value}`;

        // Return result
        return result;

    }

}

/** Public Interface
 ******************************************************
 */

/**
 * Form Input Type
 *
 * Contract implemented by each per-input-type handler (Text, Select, Date, ...)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export interface FormInputType {

    /**
     * Init
     *
     * Optional: types with no special wiring (text, email, password, hidden) can omit it
     *
     * @param inputEl
     * @param formEl
     * @param helpers
     * @param options
     * @returns {Promise<void>}
     */
    init?(inputEl:HTMLInputElement|HTMLSelectElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions>):Promise<void>;

    /**
     * Get
     *
     * @param itemEl
     * @param options
     * @returns {null|Array<any>}
     */
    get(itemEl:HTMLElement, options:Partial<FormOptions>):null|Array<any>;

    /**
     * Get Multiple
     *
     * @param itemEl
     * @param options
     * @returns {null|Array<any>[]}
     */
    getMultiple(itemEl:HTMLElement, options:Partial<FormOptions>):null|Array<any>[];

    /**
     * Filter Get
     *
     * Same as `get()`, but with the field's paired filter operator combined
     * into the returned value (e.g. `!=value`)
     *
     * @param itemEl
     * @param formEl
     * @param options
     * @returns {null|Array<any>}
     */
    filterGet(itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions>):null|Array<any>;

    /**
     * Filter Get Multiple
     *
     * @param itemEl
     * @param formEl
     * @param options
     * @returns {null|Array<any>[]}
     */
    filterGetMultiple(itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions>):null|Array<any>[];

    /**
     * Set
     *
     * Optional: types with no write-back support (checkbox, radio) can omit it
     *
     * @param itemEl
     * @param value
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    set?(itemEl:HTMLElement, value:any, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions>):void;

    /**
     * Set Filter
     *
     * Same as `set()`, but for the field's filter-mode widget when it differs
     * from its normal one (e.g. "radio"/"select" become a TomSelect `<select>`)
     *
     * Optional: types whose filter widget is identical to the normal one can
     * omit it and just fall back to `set()` (see `Form.ts`'s `setValue()`)
     *
     * @param itemEl
     * @param value
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    filterSet?(itemEl:HTMLElement, value:any, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions>):void;

}

/**
 * Form Input Type Helpers
 */
export interface FormInputTypeHelpers {

    /** @var processQueryParams Resolve "{{fieldName}}" placeholders against the form's current values */
    processQueryParams:(query:Record<string,string>, formEl:HTMLFormElement|null) => Record<string,string>;

}
