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
import UtilityBoolean from '../Boolean';
import FormType from './FormType';

/**
 * Checkbox Type
 *
 * Handler for "checkbox" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class CheckboxType extends FormType implements FormInputType {

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
     * Nothing to wire for a plain checkbox input
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
        if("value" in itemEl && "name" in itemEl && itemEl instanceof HTMLInputElement){

            // Declare value
            let value:string = "";

            // Declare key
            let key:string = itemEl.name as string;

            // Set result
            let rawValue:boolean = itemEl.checked;

            // Check raw value is on
            if(rawValue){

                // Set value
                value = "true"

            }else{

                // Set value
                value = "false";

            }

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
        if("value" in itemEl && "name" in itemEl && itemEl instanceof HTMLInputElement){

            // Declare value
            let value:string = "";

            // Declare key
            let key:string = itemEl.name as string;

            // Set result
            let rawValue:boolean = itemEl.checked;

            // Check raw value is on
            if(rawValue){

                // Set value
                value = "true"

            }else{

                // Set value
                value = "false";

            }

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /**
     * Filter Get
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @returns {null|Array<any>}
     */
    public filterGet = (itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Get plain key/value
        let result = this.get(itemEl, options);

        // Combine with operator
        if(result) result = [result[0], FormType.combineFilterOperatorValue(FormType.getFilterOperatorValue(formEl, result[0]), result[1], result[0], options)];

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

        // Combine with operator
        if(results) results = results.map(result => [result[0], FormType.combineFilterOperatorValue(FormType.getFilterOperatorValue(formEl, result[0]), result[1], result[0], options)]);

        // Return results
        return results;

    }

    /**
     * Set
     *
     * Set checked state in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @returns {void}
     */
    public set = (itemEl:HTMLElement, value:any, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(itemEl instanceof HTMLInputElement && value !== null){

            // Set checked state
            itemEl.checked = UtilityBoolean.check(value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

        }

    }

    /**
     * Set Filter
     *
     * A checkbox's filter widget is the same as its normal one
     * (see filter_checkbox.hbs), so setting it in filter mode is no different
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    public filterSet = (itemEl:HTMLElement, value:any, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Delegate to the regular setter
        this.set(itemEl, value, valuesID, formEl, options);

    }

}
