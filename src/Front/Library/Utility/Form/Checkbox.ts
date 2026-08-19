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

/**
 * Checkbox Type
 *
 * Handler for "checkbox" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class CheckboxType implements FormInputType {

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
     * @return null|Array<any>
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
     * @return null|Array<any>[]
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

}
