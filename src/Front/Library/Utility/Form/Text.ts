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
 * Dependances
 */
import type { FormInputTypeHelpers, FormInputType } from './FormType';
import FormType from './FormType';

/**
 * Text Type
 *
 * Handler for "text" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class TextType extends FormType implements FormInputType {

    /** Private Parameters
     ******************************************************
     */

    /** @var _options */
    private _options:Partial<FormOptions> = {
        filter: false
    };

    /**
     * Constructor
     *
     * @param options
     */
    constructor(options:Partial<FormOptions> = {}){

        // Call parent constructor
        super();

        // Ingest options
        this._options = {...this._options, ...options};

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Init
     *
     * Nothing to wire for a plain text input
     *
     * @param inputEl
     * @returns {void}
     */
    public init = async (inputEl:HTMLInputElement|HTMLSelectElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

    }

    /**
     * Get
     *
     * @param itemEl:HTMLElement
     * @returns {null|Array<any>}
     */
    public get = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            let key:string = itemEl.name as string;

            // Set result
            let value:string = itemEl.value as string;

            // Push in result
            result = [key, value];

        }

        // Return result
        return result;

    }

    /**
     * Get Multiple
     *
     * @param itemEl:HTMLElement
     * @returns {null|Array<any>[]}
     */
    public getMultiple = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any>[] = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            let key:string = itemEl.name as string;

            // Set result
            let value:string = itemEl.value as string;

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /**
     * Filter Get
     *
     * Same as `get()`, but the "*" operator wraps the value on both sides
     * (`*value*`, "contains") instead of just prefixing it
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @returns {null|Array<any>}
     */
    public filterGet = (itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Get plain key/value
        let result = this.get(itemEl, options);

        // Check result
        if(result){

            // Get operator paired with this field
            let operatorValue = FormType.getFilterOperatorValue(formEl, result[0]);

            // Combine, wrapping the "*" operator on both sides
            result = [result[0], FormType.combineFilterOperatorValue(operatorValue, result[1], result[0], options, true)];

        }

        // Return result
        return result;

    }

    /**
     * Filter Get Multiple
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @returns {null|Array<any>[]}
     */
    public filterGetMultiple = (itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Get plain key/values
        let results = this.getMultiple(itemEl, options);

        // Check results
        if(results) results = results.map(result => {

            // Get operator paired with this field
            let operatorValue = FormType.getFilterOperatorValue(formEl, result[0]);

            // Combine, wrapping the "*" operator on both sides
            return [result[0], FormType.combineFilterOperatorValue(operatorValue, result[1], result[0], options, true)];

        });

        // Return results
        return results;

    }

    /**
     * Set
     *
     * Set text in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @returns {void}
     */
    public set = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(itemEl.tagName == "INPUT" && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Check values id is string
            if(typeof valuesID === "string"){

                // Set entity_id
                itemEl.setAttribute("value_id", valuesID);

            }else
            // Check value is object
            if(valuesID !== null && Object.keys(valuesID).includes(itemEl["name"])){

                // Set entity_id
                itemEl.setAttribute("value_id", valuesID[itemEl["name"]]);

            }else
            // Check if $oid
            if(valuesID && typeof valuesID === "object" && "$oid" in valuesID){

                // Set entity_id
                itemEl.setAttribute("value_id", valuesID["$oid"] as string);

            }

        }

    }

    /**
     * Set Filter
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    public filterSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Delegate to the regular setter
        this.set(itemEl, value, valuesID, formEl, options);

    }

}
