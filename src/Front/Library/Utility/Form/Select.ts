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
import { TomSettings, RecursivePartial } from 'tom-select/dist/types/types';
import {default as UtilityStrings} from '../Strings';
import type { FormInputTypeHelpers } from './Type';
import Crazyrequest from '../../Crazyrequest';
import type FormInputType from './Type';
import {default as Form} from '../Form';
import Crazyurl from '../../Crazyurl';
import TomSelect from 'tom-select';
import Objects from '../Objects';

/**
 * Select Type
 *
 * Handler for "select" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class SelectType implements FormInputType {

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
     * @param formEl
     * @param helpers
     * @returns {void}
     */
    public init = async (inputEl:HTMLSelectElement|HTMLInputElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Promise wrap
        return new Promise<void>((resolve) => {

            // Check input el
            if(inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement){

                // Pending Requests
                const pendingRequests:Promise<void>[] = [];

                // Get progress
                let progressEl:HTMLElement|null = inputEl.id
                    ? inputEl.parentElement?.querySelector(`.progress[data-select-id="${inputEl.id}"]`) as HTMLElement|null
                    : null
                ;

                // Set option
                let option:RecursivePartial<TomSettings> = {
                    persist: false,
                    createOnBlur: true,
                    create: true,
                    dropdownParent: "body",
                    plugins: {}
                };

                // Append create
                option.create = false;

                // Check clear
                if(this._options.filter || (inputEl.dataset && "selectClear" in inputEl.dataset))

                    // Set plugin
                    // @ts-ignore
                    option.plugins["clear_button"] = {
                        title: inputEl.dataset.selectClear
                    };

                // Check clear
                if((inputEl.dataset && "selectTag" in inputEl.dataset) || ("multiple" in inputEl && inputEl.multiple)){

                    // Set plugin caret position
                    // @ts-ignore
                    option.plugins["caret_position"] = {};

                    // Set plugin drag drop
                    // @ts-ignore
                    option.plugins["drag_drop"] = {};

                }

                // Declare potential add option
                let addOption:(()=>void)|null = null;

                // Check if
                if(inputEl.dataset && "selectRemote" in inputEl.dataset && inputEl.dataset.selectRemote && UtilityStrings.isJson(inputEl.dataset.selectRemote)){

                    // Decode selectRemote
                    let remoteData = JSON.parse(inputEl.dataset.selectRemote);

                    // Set value
                    option.valueField = remoteData.value;

                    // Check label has {{}}
                    if(remoteData.label && remoteData.label.includes("{{") && remoteData.label.includes("}}")){

                        // Set render
                        option.render = {
                            option: (data:Record<string,any>, escape:(input:string)=>string):string => {

                                // Declare result
                                let result:string = remoteData.label.replace(/\{\{(.*?)\}\}/g, (i:any, match:any) => escape(data[match]))

                                // Append div after and before
                                return `<div>${result}</div>` as string;

                            },
                            item: (data:Record<string,any>, escape:(input:string)=>string):string => {

                                // Declare result
                                let result:string = remoteData.label.replace(/\{\{(.*?)\}\}/g, (i:any, match:any) => escape(data[match]))

                                // Append div after and before
                                return `<div>${result}</div>` as string;

                            }
                        };

                    }else{

                        // Set label
                        option.labelField = remoteData.label;

                    }

                    // Set search
                    option.searchField = remoteData.search;

                    // option.allowEmptyOption = true;

                    // Set load
                    option.load = (selectQuery, callback) => {

                        // Open progression
                        progressEl?.removeAttribute("disabled");

                        // Let result
                        let result = Crazyurl.extractQueryAndUrl(`${window.location.origin}${remoteData.url}`);

                        // Set queryParam
                        let queryParam = result.query;

                        // Get parent form
                        let queryFormEl = inputEl.closest(`form[partial="form"]`);

                        // Check param
                        if(Object.keys(queryParam).length)

                            // Update query
                            queryParam = helpers.processQueryParams(queryParam, queryFormEl instanceof HTMLFormElement ? queryFormEl : null);

                        // New query
                        let query = new Crazyrequest(
                            result.url,
                            {
                                method: "get",
                                cache: false,
                                responseType: "json",
                                from: "internal"
                            }
                        ).fetch(queryParam).then(
                            value => {

                                // Check if dataKey
                                if(typeof remoteData.dataKey === "string" && remoteData.dataKey)

                                    // Set right value
                                    value.results = remoteData.dataKey.split('.').reduce((acc:any, key:any) => acc && acc[key], value.results);

                                // Check value results
                                if(
                                    value &&
                                    "results" in value &&
                                    Array.isArray(value.results) &&
                                    value.results.length
                                )

                                    // Iteration value
                                    for(let key in value.results)

                                        // Set key
                                        value.results[key] = Objects.flatten(value.results[key], "", ".");

                                // Callback with value retrieve
                                let call = callback(value.results);

                            }
                        )
                        .then(() => {

                            // Open progression
                            progressEl?.setAttribute("disabled", "");

                            // Get value to set
                            let selectValueToSet = inputEl.dataset.selectValueToSet;

                            // Check value to set
                            if(selectValueToSet){

                                // Parse value
                                let parsedValueToSet = JSON.parse(selectValueToSet);

                                // Get value
                                let value = parsedValueToSet.value;

                                // Get value id
                                let valueId = parsedValueToSet.valuesID;

                                // Remove value to set
                                delete inputEl.dataset.selectValueToSet;

                                // Set value
                                this.set(inputEl, value, valueId, formEl);

                            }

                        })
                        .then(() => {

                            // Check depends
                            if(inputEl.dataset.depends && inputEl.dataset.dependsValue){

                                // Read inputEl.dataset.dependsValue
                                let valueParsed = JSON.parse(inputEl.dataset.dependsValue);

                                // Check parsed
                                if(valueParsed.value && valueParsed.valuesID){

                                    // Check if multiple
                                    if(inputEl.multiple){


                                    }
                                    // Check if single
                                    else{

                                        // Check value already set
                                        if(!inputEl.value){

                                            // Set value
                                            this.set(inputEl, valueParsed.value, valueParsed.valuesID, formEl);

                                        }

                                    }

                                }

                            }

                        })
                        .catch(() => callback([]))
                        .finally(() => {

                            // Remove completed request from pendingRequests
                            pendingRequests.splice(pendingRequests.indexOf(query), 1);

                        });

                        // Track the request
                        pendingRequests.push(query);

                    };

                    // Prepare add option
                    addOption = () => {

                        // Open progression
                        progressEl?.removeAttribute("disabled");

                        // Let result
                        let result = Crazyurl.extractQueryAndUrl(`${window.location.origin}${remoteData.url}`);

                        // Set queryParam
                        let queryParam = result.query;

                        // Get parent form
                        let queryFormEl = inputEl.closest(`form[partial="form"]`);

                        // Check param
                        if(Object.keys(queryParam).length)

                            // Update query
                            queryParam = helpers.processQueryParams(queryParam, queryFormEl instanceof HTMLFormElement ? queryFormEl : null);

                        // New query
                        let query = new Crazyrequest(
                            result.url,
                            {
                                method: "get",
                                cache: false,
                                responseType: "json",
                                from: "internal"
                            }
                        ).fetch(queryParam)
                        // Add options found
                        .then(
                            value => {

                                // Check if dataKey
                                if(typeof remoteData.dataKey === "string" && remoteData.dataKey)

                                    // Set right value
                                    value.results = remoteData.dataKey.split('.').reduce((acc:any, key:any) => acc && acc[key], value.results);

                                // Check value results
                                if(
                                    value &&
                                    "results" in value &&
                                    Array.isArray(value.results) &&
                                    value.results.length
                                )

                                    // Iteration value
                                    for(let key in value.results)

                                        // Set key
                                        value.results[key] = Objects.flatten(value.results[key], "", ".");

                                // Add options to tom
                                selectInstance.addOptions(value.results);

                            }
                        // Check default and set it
                        ).then(
                            () => {

                                // Check default in input el
                                if(inputEl.hasAttribute("default")){

                                    // Get default
                                    let defaultValue = inputEl.getAttribute("default");

                                    // Check type of default value
                                    if(typeof defaultValue === "string")

                                        // Set value
                                        selectInstance.setValue(defaultValue);

                                }

                            }
                        )
                        .then(() => {

                            // Open progression
                            progressEl?.setAttribute("disabled", "");

                            // Get value to set
                            let selectValueToSet = inputEl.dataset.selectValueToSet;

                            // Check value to set
                            if(selectValueToSet){

                                // Parse value
                                let parsedValueToSet = JSON.parse(selectValueToSet);

                                // Get value
                                let value = parsedValueToSet.value;

                                // Get value id
                                let valueId = parsedValueToSet.valuesID;

                                // Remove value to set
                                delete inputEl.dataset.selectValueToSet;

                                // Set value
                                this.set(inputEl, value, valueId, formEl);

                            }

                        })
                        .then(() => {

                            // Check depends
                            if(inputEl.dataset.depends && inputEl.dataset.dependsValue){

                                // Read inputEl.dataset.dependsValue
                                let valueParsed = JSON.parse(inputEl.dataset.dependsValue);

                                // Check parsed
                                if(valueParsed.value && valueParsed.valuesID){

                                    // Check if multiple
                                    if(inputEl.multiple){


                                    }
                                    // Check if single
                                    else{

                                        // Check value already set
                                        if(!inputEl.value){

                                            // Set value
                                            this.set(inputEl, valueParsed.value, valueParsed.valuesID, formEl);

                                        }

                                    }

                                }

                            }

                        })
                        .finally(() => {

                            // Remove completed request from pendingRequests
                            pendingRequests.splice(pendingRequests.indexOf(query), 1);

                        });

                    }

                }

                // Init maska
                let selectInstance = new TomSelect(inputEl, option);

                // Check addOption is callable
                if(addOption !== null && typeof addOption === "function"){

                    // Run function
                    // @ts-ignore
                    addOption();

                }

                // Wait for all pending requests to complete before resolving
                Promise.all(pendingRequests).then(() => resolve());

            }else{

                // Resolve immediately if element is missing
                resolve();

            }

        });

    }

    /**
     * Get
     *
     * - Prefix : OK
     * - Suffix : OK
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

            // Check filter
            if(!this._options.filter || (this._options.filter && value)){

                // Check prefix
                if(typeof itemEl.dataset.formValuePrefix && itemEl.dataset.formValuePrefix){

                    // Update value
                    value = `${itemEl.dataset.formValuePrefix}${value}`;

                }

                // Check prefix
                if(typeof itemEl.dataset.formValueSuffix && itemEl.dataset.formValueSuffix){

                    // Update value
                    value = `${value}${itemEl.dataset.formValueSuffix}`;

                }

                // Push in result
                result = [key, value];

            }

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

        // Declare value
        let value:any[] = [];

        // Check value
        if("value" in itemEl && "name" in itemEl){

            // Set key
            let key:string = itemEl.name as string;

            // Check if tomselect
            if("tomselect" in itemEl && itemEl.tomselect instanceof TomSelect){

                // Get values
                let valueRaw = itemEl.tomselect.getValue();

                // Set value
                if(typeof valueRaw === "string") valueRaw = [valueRaw];

                // Check value raw
                if(valueRaw.length) for(let currentValue of valueRaw) if(currentValue !== null){

                    // Push value
                    value.push(currentValue);

                }

            }else
            // Check if from materialize
            if("M_Dropdown" in itemEl && itemEl.M_Dropdown){

                // Get valueRaw
                let valueRaw = itemEl.value as string;

                // Check value raw
                if(valueRaw){

                    // Explode value
                    let explodedValue = valueRaw.split(", ");

                    // Iteration exploded value
                    if(explodedValue.length) for(let explodValue of explodedValue) if(explodValue !== null){

                        // Push value
                        value.push(explodValue);

                    }

                }


            // Default case
            }else{

                // Set result
                value = Array.isArray(itemEl.value)
                    ? itemEl.value
                    : [itemEl.value as string]
                ;

            }

            // Iteration value
            if(value.length) for(let currentValue of value) if(currentValue !== null){

                // Check result
                if(result === null) result = [];

                // Push value
                result.push([key, currentValue]);

            }

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /**
     * Set
     *
     * Set select in item
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

            // Check if tomselect in item
            if("tomselect" in itemEl && itemEl.tomselect instanceof TomSelect){

                // Let disabled
                let disabled = false;

                // Check if item disabled
                if(itemEl instanceof HTMLSelectElement && itemEl.disabled){

                    // Set disabled
                    disabled = true;

                    // Disabled Disabled
                    itemEl.disabled = false;

                }

                // Check if depends
                if(itemEl.dataset.depends){

                    // Set value into depends value
                    itemEl.dataset.dependsValue = JSON.stringify({
                        value: value,
                        valuesID: valuesID
                    });

                }

                // Get progress bar
                let progressEl:HTMLElement|null = itemEl.id
                    ? itemEl.parentElement?.querySelector(`.progress[data-select-id="${itemEl.id}"]`) as HTMLElement|null
                    : null
                ;

                // Attribute to observe
                let attributeToObserve = "disabled";

                // Check if progress bar is not null and have disabled attribute
                if(progressEl && progressEl.hasAttribute(attributeToObserve)){

                    // Max iteration
                    var maxIteration = 5;

                    // Interval (ms)
                    var interval = 200;

                    // Tries
                    let tries = 0;

                    // Prepare function
                    const check = () => {

                        // Increment tries
                        tries++;

                        // Check disabled disparead
                        if(!itemEl.hasAttribute('disabled')){

                            // Set value
                            itemEl.tomselect instanceof TomSelect && itemEl.tomselect.setValue(value);

                            // Set id
                            Form.setId(formEl, valuesID, itemEl);

                            // Top function
                            return;

                        }

                        if(tries < maxIteration)

                            // Set timeout
                            setTimeout(check, interval);

                    };

                    // Run check
                    check();

                }else{

                    // Set value
                    itemEl.tomselect.setValue(value);

                    // Set id
                    Form.setId(formEl, valuesID, itemEl);

                }

                // Get setted value
                let valueSet = itemEl.tomselect.getValue();

                // Check if is expected value
                if(valueSet != value){

                    // Set value in attribute
                    itemEl.dataset.selectValueToSet = JSON.stringify({
                        value: value,
                        valuesID: valuesID
                    });

                }

                // Check if item disabled
                if(itemEl instanceof HTMLSelectElement && disabled){

                    // Disabled Disabled
                    itemEl.disabled = true;

                }

            }else{

                // Set value
                itemEl.setAttribute("value", value);

                // Dispatch event change
                itemEl.dispatchEvent(new Event("change"));

                // Set id
                Form.setId(formEl, valuesID, itemEl);

            }

        }

    }

}
