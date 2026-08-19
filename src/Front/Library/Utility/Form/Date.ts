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
import AirDatepicker, { AirDatepickerOptions } from 'air-datepicker';
import airDatePickerLocaleFr from 'air-datepicker/locale/fr'
import { IPickerConfig } from '@easepick/core/dist/types';
import { AmpPlugin } from '@easepick/amp-plugin';
import { RangePlugin } from '@easepick/bundle';
import { easepick } from '@easepick/bundle';
import UtilityDateTime from '../DateTime';
import type { FormInputTypeHelpers } from './Type';
import type FormInputType from './Type';
import {default as Form} from '../Form';

/**
 * Date Type
 *
 * Handler for "date" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class DateType implements FormInputType {

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

        // Check input is easepick
        if(inputEl instanceof HTMLInputElement && "datePicker" in inputEl.dataset && inputEl.dataset.datePicker == "easepick"){

            // Prepare options
            let options:IPickerConfig = {
                element: inputEl,
                css: [
                  'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css',
                ],
                zIndex: 1,
                plugins: [AmpPlugin],
                AmpPlugin: {
                    dropdown: {
                        months: true,
                        years: true,
                    },
                },
                setup: (picker) => {

                    // Init event input
                    picker.on("preselect", () => {

                        // Dispatch
                        inputEl.dispatchEvent(new Event("input", {bubbles: true}));

                    })

                    // Init event change
                    picker.on("select", () => {

                        // Dispatch
                        inputEl.dispatchEvent(new Event("change", {bubbles: true}));

                    })

                }
            }

            // Check format
            if("dateFormat" in inputEl.dataset && inputEl.dataset.dateFormat)

                // Push in options format
                options.format = inputEl.dataset.dateFormat;

            // Check lang
            if("dateLang" in inputEl.dataset && inputEl.dataset.dateLang)

                // Push in options format
                options.lang = inputEl.dataset.dateLang;

            // Check if required
            if(!inputEl.required && options.AmpPlugin){

                // Enable reset btn
                options.AmpPlugin.resetButton = () => {

                    // Clear value
                    inputEl.value = "";

                    // Dispatch input event
                    inputEl.dispatchEvent(new Event("input", {bubbles: true}));

                    // Dispatch change event
                    inputEl.dispatchEvent(new Event("change", {bubbles: true}));

                    // Return true
                    return true;

                };

            }

            // Check if input multiple
            if(inputEl.multiple){

                // Enable Range Plugin
                options.plugins?.push(RangePlugin);

                // Push config
                options.RangePlugin = {
                    repick: true
                }

            }

            // New picker instance
            const picker = new easepick.create(options);

        }else
        // Check input is airdatepicker
        if(inputEl instanceof HTMLInputElement && "datePicker" in inputEl.dataset && inputEl.dataset.datePicker == "airdatepicker"){

            // Set options
            let options:AirDatepickerOptions = {
                dateFormat: "dateFormat" in inputEl.dataset && inputEl.dataset.dateFormat
                    ? inputEl.dataset.dateFormat
                    : "yyyy-MM-dd"
                ,
                autoClose: true,
            };

            // Set multiple
            let multiple = false;

            // Check if input multiple
            if(inputEl.multiple){

                // Set range
                options.range = true,

                // Set separator
                options.multipleDatesSeparator = ' - ';

                // Set multiple
                multiple = true;

            }

            // Check lang
            if("dateLang" in inputEl.dataset && inputEl.dataset.dateLang == "fr-FR"){

                // Push in options format
                options.locale = airDatePickerLocaleFr;

            }

            // Check min
            if(inputEl.hasAttribute("min") && inputEl.getAttribute("min") && typeof inputEl.getAttribute("min") === "string"){

                // Get date
                let dateMin = new Date(inputEl.getAttribute("min") as string);

                // Set min
                options.minDate = dateMin;

            }

            // Check max
            if(inputEl.hasAttribute("max") && inputEl.getAttribute("max") && typeof inputEl.getAttribute("max") === "string"){

                // Get date
                let dateMax = new Date(inputEl.getAttribute("max") as string);

                // Set max
                options.maxDate = dateMax;

            }

            // Check not requierd
            if(!inputEl.required){

                options.buttons = ['clear'];

            }


            // Set on select
            options.onSelect = ({date, formattedDate, datepicker}):void => {

                // Get input el
                let inputEl = datepicker.$el;

                // Check multiple and date is 2
                if(multiple && Array.isArray(date) && date.length == 1) return;

                // Check input el
                if(inputEl instanceof HTMLInputElement){

                    // Dispatch event input
                    inputEl.dispatchEvent(new Event('input',{bubbles:true}));

                    // Dispatch event change
                    inputEl.dispatchEvent(new Event('change',{bubbles:true}));

                }

            }

            // New picker instance
            const picker = new AirDatepicker(inputEl, options);

            // Add event
            document.addEventListener('click', (e) => {

                // Get calendat el
                const calendarEl = document.querySelector('.air-datepicker');

                // Check if click is outside the datepicker and input
                if(
                    // Check calendar
                    calendarEl instanceof HTMLElement &&
                    !calendarEl.contains(e.target as Node) &&
                    !inputEl.contains(e.target as Node)
                ){

                    // Hide picker
                    picker.visible && picker.hide();

                }

            });

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

            // Get key
            let key:string = itemEl.name as string;

            // Set result
            let value:string = itemEl.value as string;

            // Check filter
            if(!this._options.filter || (this._options.filter && value)){

                if(itemEl instanceof HTMLInputElement && "datePicker" in itemEl.dataset && itemEl.dataset.datePicker && ["easepick", "airdatepicker"].includes(itemEl.dataset.datePicker)){

                    // Split by separator
                    let splitedValue = value.split(" - ");

                    // Iteration
                    if(splitedValue.length){

                        // check if two value, meaning range
                        if(splitedValue.length == 2){

                            // Set range value
                            let rangeValue:string = `[${splitedValue[0]}:${splitedValue[1]}]`

                            // Check result
                            if(result === null) result = [];

                            // Push to result
                            result.push([key.replace("[]", ""), rangeValue])

                        // Iteration value
                        }else for(let splitValue of splitedValue) if(splitValue !== null) {

                            // Check result
                            if(result === null) result = [];

                            // Push to result
                            result.push([key, splitValue])

                        }

                    }

                }else{

                    // Push in result
                    result = [[key, value]];

                }

            }

        }

        // Return result
        return result;

    }

    /**
     * Set
     *
     * Set date in item
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
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Get date
            let dateClean = UtilityDateTime.toYYYYMMDDFormat(new Date(value), "-");

            // Check date
            if(dateClean){

                // Set value
                itemEl.setAttribute("value", dateClean);

                // Dispatch event change
                itemEl.dispatchEvent(new Event("change"));

                // Set id
                Form.setId(formEl, valuesID, itemEl);

            }

        }

    }

}
