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
import IMask, { MaskedNumberOptions } from 'imask';
import {default as Form} from '../Form';
import FormType from './FormType';

/**
 * Number Type
 *
 * Handler for "number" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class NumberType extends FormType implements FormInputType {

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
     * @param inputEl
     * @returns {void}
     */
    public init = async (inputEl:HTMLSelectElement|HTMLInputElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Declare mask options
        let maskOptions:MaskedNumberOptions = {
            mask: Number,
            skipInvalid: true,
            thousandsSeparator: " ",
            radix: ".",
            mapToRadix: [','],
            autofix: true,
        };

        // Check if max
        inputEl.hasAttribute("max") && inputEl.getAttribute("max") && (maskOptions.max = Number(inputEl.getAttribute("max")));

        // Check if min
        inputEl.hasAttribute("min") && inputEl.getAttribute("min") && (maskOptions.min = Number(inputEl.getAttribute("min")));

        // check if decimal
        inputEl.hasAttribute("step") && inputEl.getAttribute("step")?.includes(".") && (maskOptions.scale = inputEl.getAttribute("step")?.split(".").at(-1)?.length);

        // Set instance
        IMask(inputEl, maskOptions);

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
            let value:number = itemEl.value as number;

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
            let value:number = itemEl.value as number;

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
        if(result) result = [result[0], FormType.combineFilterOperatorValue(FormType.getFilterOperatorValue(formEl, result[0]), String(result[1]), result[0], options)];

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
        if(results) results = results.map(result => [result[0], FormType.combineFilterOperatorValue(FormType.getFilterOperatorValue(formEl, result[0]), String(result[1]), result[0], options)]);

        // Return results
        return results;

    }

    /**
     * Set
     *
     * Set number in item
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
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            Form.setId(formEl, valuesID, itemEl);

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
