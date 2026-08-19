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
import type { FormInputTypeHelpers } from './Type';
import type FormInputType from './Type';
import {default as Form} from '../Form';

/**
 * Password Type
 *
 * Handler for "password" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class PasswordType implements FormInputType {

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

        // Check maska
        if(inputEl instanceof HTMLInputElement && inputEl.dataset && "passwordVisible" in inputEl.dataset && inputEl.parentNode){

            // Get password visible
            let passwordVisible = inputEl.dataset.passwordVisible;

            // Get prefix
            let suffixEl = inputEl.parentNode.querySelector("div.suffix");

            // Add event on password
            typeof passwordVisible === "string" && suffixEl && suffixEl.addEventListener("click", (e:Event) =>{

                // Get password visible
                let passwordVisible = inputEl.dataset.passwordVisible;

                // Check e.target
                if(e.currentTarget && e.currentTarget instanceof HTMLDivElement){

                    // Change input type
                    inputEl.type = passwordVisible == "0"
                        ? "text"
                        : "password"
                    ;

                    // Get icon
                    let suffixIconEl = suffixEl && suffixEl.querySelector("i");

                    // Check suffix icon
                    if(suffixIconEl && suffixIconEl instanceof HTMLElement)

                        // Change icon
                        suffixIconEl.innerHTML = passwordVisible == "0"
                            ? "visibility_off"
                            : "visibility"
                        ;

                    // Change attribute
                    inputEl.dataset.passwordVisible = passwordVisible == "0"
                        ? "1"
                        : "0"
                    ;

                }

            }, true);

        }

    }

    /**
     * Get
     *
     * @param itemEl:HTMLElement
     * @return null|Array<any>
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
     * @return null|Array<any>[]
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
     * Set
     *
     * Set text in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @return void
     */
    public set = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(itemEl.tagName == "INPUT" && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Set id
            Form.setId(formEl, valuesID, itemEl);

        }

    }

}
