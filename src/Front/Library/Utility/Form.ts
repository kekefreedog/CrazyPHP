/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
// @ts-ignore
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
// @ts-ignore
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import { TomSettings, RecursivePartial } from 'tom-select/dist/types/types';
// @ts-ignore
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import AirDatepicker, { AirDatepickerOptions } from 'air-datepicker';
import IMask, { MaskedOptions, MaskedNumberOptions } from 'imask';
import airDatePickerLocaleFr from 'air-datepicker/locale/fr'
import { IPickerConfig } from '@easepick/core/dist/types';
import {default as PageError} from './../Error/Page';
import {default as UtilityStrings} from './Strings';
import UtilityDateTime from '../Utility/DateTime';
import { AmpPlugin } from '@easepick/amp-plugin';
import UtilityBoolean from '../Utility/Boolean';
import { RangePlugin } from '@easepick/bundle';
import { easepick } from '@easepick/bundle';
import Crazyrequest from '../Crazyrequest';
import fr_FR from 'filepond/locale/fr-fr';
import * as FilePond from 'filepond';
import Pickr from '@simonwep/pickr';
import Crazyurl from '../Crazyurl';
import TomSelect from 'tom-select';
import Objects from './Objects';
import Root from '../Dom/Root';
import { key } from 'localforage';


/**
 * Form
 *
 * Methods for retrieve value from form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Form {

    /** Parameters
     ******************************************************
     */

    /** @var _formEl */
    private _formEl:HTMLFormElement;

    /** @var _options */
    private _options:Partial<FormOptions>;

    /** Parameters | on change
     ******************************************************
     */

    /** @var _onChangeCallable */
    private _onChangeCallable:null|((result:formOnChangeResult)=>void) = null;

    /** @var _onSubmitCallable */
    private _onSubmitCallable:null|((result:formOnSubmitResult)=>void) = null;

    /** @var _onSubmitCallable */
    private _onResetCallable:null|((result:formOnResetResult)=>void) = null;

    /** @var _onChangeCallable */
    private _onChangeOptions:Partial<formOnChangeOptions> = {
        eventType: "change",
    };

    /** Construct
     ******************************************************
     */

    /**
     * Constructor
     * 
     * @param form 
     * @param options 
     */
    constructor(form:string|HTMLFormElement, options:Partial<FormOptions> = {}){

        // Ingest options
        this._options = options;

        // Scan current form
        this._ingestForm(form)
            .then(
                this._initFilter
            ).then(
                this._initForm
            ).then(
                this._initOnReady
            ).then(
                this._initEventOnSubmit
            ).then(
                this._initEventOnReset
            ).then(
                this._initEventOnChange
            )
        ;

    }

    /** Public method
     ******************************************************
     */

    /**
     * Scan
     * 
     * Scan form in crazy root
     * 
     * @deprecated
     * @return number
     */
    public scan = ():number => {

        // Declare result
        let result:number = 0;

        // Get root
        let rootEl = Root.getEl();

        // Check root
        if(rootEl === null)

            // Return
            return result;

        // Searh crazy form 
        let searchEls = rootEl.querySelectorAll("form.crazy-form");

        // Set result
        result = searchEls.length;

        // Check result
        if(result > 0)
        
            // Iteration of searchls
            for(let i = 0; i < searchEls.length; i++) {

                // Add event on them
                searchEls[i].addEventListener(
                    "submit",
                    this.eventOnSubmit
                );

            }

        // Return
        return result;

    }

    /**
     * Set On Change
     * 
     * @param callable
     * @param options
     * @return void
     */
    public setOnChange = (callable:(result:formOnChangeResult)=>void, options:Partial<formOnChangeOptions>) => {

        // Set on change
        this._onChangeOptions = {...this._onChangeOptions, ...options};

        // Set on change event
        this._onChangeCallable = callable;

    }

    /**
     * Set On Submit
     * 
     * @param callable
     * @param options
     * @return void
     */
    public setOnSubmit = (callable:(result:formOnSubmitResult)=>void) => {

        // Set on change event
        this._onSubmitCallable = callable;

    }

    /**
     * Set On Reset
     * 
     * @param callable
     * @param options
     * @return void
     */
    public setOnReset = (callable:(result:formOnResetResult)=>void) => {

        // Set on change event
        this._onResetCallable = callable;

    }

    /** Punlic methods
     ******************************************************
     */

    /**
     * Get Schema
     * 
     * Get schema by name
     * 
     * @param formName:
     * @return HTMLElement|null
     */
    public getForm = (formName:string):HTMLElement|null => {

        // Declare result
        let result:HTMLElement|null = null;

        // Check formname
        if(formName)

            // Return result
            return result;

        // Search form
        let searchEl = document.querySelector(`form#${formName}`);

        // Check search
        if(searchEl === null)

            // Return result
            return result;

        // Set result
        result = searchEl as HTMLElement;

        // Return result
        return result;

    }

    /**
     * Set Value
     * 
     * Set value of form
     * 
     * @param values 
     * @param valuesID
     * @returns void
     */
    public setValue = (values:Object, valuesID:string|Object|null = null):void => {

        // Declare var
        let currentName:string;
        let currentType:string;

        // Get all select and input on form el
        let items = this._formEl.querySelectorAll("select[name], input[name]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check if name
                if("name" in items[i] && items[i]["name"] !== ""){

                    // Get name
                    currentName = items[i]["name"]/* .replace(`${formName}_`, "") */;

                    // Get type
                    currentType = items[i]["type"] ?? "";

                    // Check if data type 
                    // @ts-ignore
                    if("type" in items[i].dataset && items[i].dataset.type)

                        // Override type
                        // @ts-ignore
                        currentType = items[i].dataset.type;

                    // Check if in values
                    if(Object.keys(values).includes(currentName)){

                        // Check itemEl
                        if(typeof this[`${currentType}Set`] === "function"){

                            // Set result
                            this[`${currentType}Set`](items[i], values[currentName], valuesID);

                        }

                    }

                }

            }

    }

    /**
     * Reset Value
     * 
     * Set value of form
     * 
     * @param clear Do not use default and just clear value
     * @returns void
     */
    public resetValue = (clear:boolean = false):void => {

        // Get all select and input on form el
        let items = this._formEl.querySelectorAll("select, input");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check if name
                if((items[i] instanceof HTMLSelectElement || items[i] instanceof HTMLInputElement ) && "name" in items[i] && items[i]["name"] !== ""){

                    /**
                     * Retrieve default value
                     */

                    // Get default value
                    let defaultValue = clear 
                        ? null
                        : this._getDefaultOfInput(items[i] as (HTMLSelectElement|HTMLInputElement))
                    ;

                    /**
                     * Clean current value
                     */

                    // Check is tom select
                    if("tomselect" in items[i]){

                        // Get tome select instance
                        // @ts-ignore
                        let tomSelectInstance = items[i].tomselect;

                        // Check value
                        if(defaultValue){

                            // Set value
                            tomSelectInstance.setValue(defaultValue);

                        }else{

                            // Clear
                            tomSelectInstance.clear();

                        }

                        // Continue iteration
                        continue;

                    }

                    // Check if item has value
                    if(items[i].hasAttribute("value")){

                        // Check if defaultValue
                        if(defaultValue === null)

                            // Reset value
                            items[i].removeAttribute("value");

                        else
                        // Check default value is string
                        if(defaultValue){

                            // Check if date
                            if(defaultValue instanceof Date){

                                // Check html inpit
                                if(items[i] instanceof HTMLInputElement)

                                    // Set value
                                    // @ts-ignore
                                    items[i].valueAsDate = defaultValue;

                            }else{

                                // Set value
                                items[i].setAttribute("value", defaultValue.toString());

                                // Check html inpit
                                if(items[i] instanceof HTMLInputElement)

                                    // Set value
                                    // @ts-ignore
                                    items[i].value = defaultValue.toString();

                            }

                        }

                    }

                    // Check if item has value_id
                    if(items[i].hasAttribute("value_id"))

                        // Reset value
                        items[i].removeAttribute("value_id");

                    // Check if item has checked
                    // @ts-ignore
                    if(items[i].hasAttribute("checked") || items[i].type == "checkbox" ){

                        // Check if defaultValue
                        if(defaultValue === null){

                            // Remove checked
                            items[i].removeAttribute("checked");

                            // Remove check
                            // @ts-ignore
                            items[i].checked = false;

                        }else
                        // Check default value is string
                        if(defaultValue){

                            // Check html inpit
                            if(items[i] instanceof HTMLInputElement)

                                // Set value
                                // @ts-ignore
                                items[i].checked = defaultValue;

                        }

                    }

                }

            }

        // Check if form has value_id
        if(this._formEl.hasAttribute("value_id"))

            // Reset value
            this._formEl.removeAttribute("value");

    }

    /**
     * Get Form Data
     * 
     * Get formdata by name
     * 
     * @param formName:string
     * @param processAndValidate:boolean
     * @return null|Arrays<null>
     */
    public getFormData = (formName:string|HTMLElement):FormData => {

        // Declare var
        let formEl:HTMLElement|null;
        let itemResult:Array<string|Blob>|null;
        let itemResults:[Array<string|Blob>]|null;

        // Check form name
        if(typeof formName == "string"){

            // Get form
            formEl = this.getForm(formName);

            // Check form el
            if(formEl === null)

                // New error
                throw new Error("Form do not exists");

        }else

            // Set form el
            formEl = formName;

        // Set formdata
        let result:FormData = new FormData();

        // Get all select and input on form el
        let items = formEl.querySelectorAll("select[name], input[name]");

        // Radio already checked
        let radioAlreadyChecked:string[] = [];

        // Check items
        if(items.length)

            // Iteration items
            iterationItems:for (let i = 0; i < items.length; i++){

                // Get current item
                let currentItem = items[i];

                // Check if radio
                if(currentItem instanceof HTMLInputElement && currentItem.name && currentItem.type === "radio"){

                    // Check if radio already checked
                    if(radioAlreadyChecked.includes(currentItem.name))

                        // Continue
                        continue iterationItems;

                    // Else
                    else{

                        // Add item in checked list
                        radioAlreadyChecked.push(currentItem.name);

                    }

                }

                // Get multiple
                let mutliple = (currentItem instanceof HTMLInputElement || currentItem instanceof HTMLSelectElement) && currentItem.multiple
                    ? currentItem.getAttribute("multiple") ? currentItem.getAttribute("multiple") : true
                    : false
                ;

                // Check if multiple
                if(!mutliple){

                    // Get result
                    itemResult = this.extractKeyValue(items[i] as HTMLElement);

                    // Check itemResult
                    if(itemResult !== null && itemResult[0] !== "")

                        // Push value of current input
                        result.append(itemResult[0] as string, itemResult[1]);

                }else
                // If multiple
                {

                    // Get result
                    itemResults = this.extractKeyMultipleValue(items[i] as HTMLElement);

                    // Check itemResult
                    if(itemResults !== null && Array.isArray(itemResults) && itemResults.length)

                        // Iteration result
                        for(let itemResult of itemResults) if(itemResult !== null && itemResult[0] !== ""){

                            // Get name
                            let name = itemResult[0] as string;

                            // Push value of current input
                            result.append(name, itemResult[1]);

                        }

                }

            }

        // Return result
        return result;

    }

    /**
     * Is Valid
     * 
     * Is Form Valid
     * 
     * @param formName:string
     * @returns {boolean}
     */
    public isValid = (formName:string|HTMLElement, formData?:FormData):boolean => {

        // Declare var
        let formEl:HTMLElement|null;

        // Declare form data
        let formDataTmp:FormData;

        // Check form name
        if(typeof formName == "string"){

            // Get form
            formEl = this.getForm(formName);

            // Check form el
            if(formEl === null)

                // New error
                throw new Error("Form do not exists");

        }else

            // Set form el
            formEl = formName;

        // Check form data
        if(!formData){

            // Set formdata
            formDataTmp = this.getFormData(formEl);

        }else

            // Set with given value
            formDataTmp = formData;

        // Set result
        let result = true;

        // Get all select and input on form el
        let items = formEl.querySelectorAll("select[name][required], input[name][required]");

        // Check items
        if(items.length) for(let item of Array.from(items)) if(item instanceof HTMLInputElement || item instanceof HTMLSelectElement){

            // Let name
            let name = `${item.name}${item.multiple ? "[]" : ""}`;

            // Check formdata has value and is not empty
            if(!formDataTmp.has(name) || !formDataTmp.get(name)){

                // Set result
                result = false;

                // Break
                break;

            }

        }

        // Return result
        return result;

    }

    /**
     * Clear values
     * 
     * Clear values from form
     * @param formName:string
     * @return void
     */

    /** Private methods | Event
     ******************************************************
     */

    /**
     * Event On Submit
     * 
     * Event on submit form
     * 
     * @param e:Event
     * @return void
     */
    private eventOnSubmit = (e:Event):void => {

        // Prevent default action
        e.preventDefault();

        // Check target
        if(e.target === null)

            // Stop
            return;

        // Set target
        let target:HTMLFormElement = e.target as HTMLFormElement;

        // Get formdata
        let formData:FormData = this.getFormData(target);

        // Get entity
        let entity:Attr|null = target.attributes.getNamedItem("entity");

        // Get value_id
        let valueID:Attr|null = target.attributes.getNamedItem("value_id");

        // Get post
        let postUrl:Attr|null = target.attributes.getNamedItem("post");

        // Call callable
        this._onSubmitCallable && this._onSubmitCallable({
            formEl: target,
            formData: formData,
            type: "submit"
        });

        // Check entity or value id
        if(entity !== null && valueID !== null){

            // Lock form
            this.lock();

            // Create item
            this._onSubmitUpdate(entity.value, valueID.value, formData)
                // Check errors
                .then(
                    value => {

                        // Parse errors
                        value && "errors" in value && window.Crazyobject.alert.parseErrors(value.errors as CrazyError|CrazyError[], {
                            postAction: ():void => {

                                // Stop event
                                throw "";

                            }
                        });

                        // Check if not results
                        if(!("results" in value)){

                            // Unlock target
                            this.unlock();

                            // Stop
                            throw "";

                        }

                        // Return value
                        return value;

                    }
                )
                // Set data
                .then(
                    value => {

                        // Check submit done
                        if(this._options.onSubmitDone)

                            // Call it
                            this._options.onSubmitDone(value, entity.value, formData);

                        // Unlock target
                        this.unlock();

                    }
                );

        }else
        // Check entity
        if(entity !== null){

            // Lock form
            this.lock();

            // Create item
            this._onSubmitCreate(entity.value, formData)
                // Check errors
                .then(
                    value => {

                        // Check error
                        if("errors" in value && Array.isArray(value.errors) && value.errors.length){

                            // Parse errors
                            window.Crazyobject.alert.parseErrors(value.errors as CrazyError|CrazyError[], {
                                postAction: ():void => {

                                    // Stop event
                                    throw "";

                                }
                            });

                        }

                        // Check if not results
                        if(!("results" in value)){

                            // Unlock target
                            this.unlock();

                            // Stop
                            throw "";

                        }

                        // Return value
                        return value;

                    }
                )
                .then(
                    value => {

                        // Check v
                        if("results" in value && value.results.length)
                            
                            // Set values
                            this.setValue(value.results[0], value.results[0]._id);

                        // Check submit done
                        if(this._options.onSubmitDone)

                            // Call it
                            this._options.onSubmitDone(value, entity.value, formData);

                        // Unlock target
                        this.unlock();
    
                    }
                );

        }else
        // Check post url
        if(postUrl !== null){

            // Lock form
            this.lock();

            // Create item
            this._onSubmitSend(postUrl.value, formData, "post")
                // Check errors
                .then(
                    value => {

                        // Check error
                        if("errors" in value && Array.isArray(value.errors) && value.errors.length){

                            // Parse errors
                            window.Crazyobject.alert.parseErrors(value.errors as CrazyError|CrazyError[], {
                                postAction: ():void => {

                                    // Stop event
                                    throw "";

                                }
                            });

                        }

                        // Check if not results
                        if(!("results" in value)){

                            // Unlock target
                            this.unlock();

                            // Stop
                            throw "";

                        }

                        // Return value
                        return value;

                    }
                )
                .then(
                    value => {

                        console.log("youyou");
                        console.log(value);

                        // Unlock target
                        this.unlock();
    
                    }
                );

        // Else if just a form where retreive data and that's all
        }else{

            // Unlock target
            this.lock();

            // Check submit done
            if(this._options.onSubmitDone)

                // Call it
                this._options.onSubmitDone({}, "", formData);

            // Unlock target
            this.unlock();

        }

    }

    /**
     * Event On Reset
     * 
     * Event on reset
     * 
     * @param e:Event
     * @return void
     */
    private eventOnReset = (e:Event):void => {

        // Prevent default
        e.preventDefault();

        // Lock form
        this.lock();

        // Check form in e.target
        if(e.currentTarget instanceof HTMLFormElement){

            // Get target
            let formEl:HTMLFormElement = e.currentTarget;

            // Get value_id
            let valueID:Attr|null = formEl.attributes.getNamedItem("value_id");

            // Get value_id
            let entity:Attr|null = formEl.attributes.getNamedItem("entity");

            // Call callable
            if(this._onResetCallable){

                // Get form data
                let formData = this.getFormData(formEl);
                
                this._onResetCallable({
                    formEl: formEl,
                    formData: formData,
                    type: "reset"
                });

            }

            // Check valueID
            if(valueID && entity){

                this._onSubmiDelete(entity.value, valueID.value)
                    .then(v => {

                    }).then(v => {

                        // Retrive other value
                        this._initOnReady()

                    })

            }else{

                // Reset value
                this.resetValue();

                // Unlock
                this.unlock();

            }

        }

    }

    /** Private methods | Event Specific Action
     ******************************************************
     */

    /**
     * On Submit Create
     * 
     * Create item in back
     * 
     * @param entityValue
     * @param formData
     * @returns {Promise<any>}
     */
    private _onSubmitCreate = async (entityValue:string, formData:FormData):Promise<any> => {

        // Check submit before
        this._options.onBeforeSubmit && this._options.onBeforeSubmit(entityValue, formData);

        // Prepare request
        let request = new Crazyrequest(`/api/v2/${entityValue}/create`, {
            method: "POST",
            header:{
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: false
        });

        // Run request
        return request.fetch(formData);

    }

    /**
     * On Submit Update
     * 
     * Update item in back
     * 
     * @param entityValue
     * @param valueID
     * @param formData
     * @returns {Promise<any>}
     */
    private _onSubmitUpdate = async (entityValue:string, valueID:string, formData:FormData):Promise<any> => {

        // Check submit before
        this._options.onBeforeSubmit && this._options.onBeforeSubmit(entityValue, formData);

        // Prepare request
        let request = new Crazyrequest(`/api/v2/${entityValue}/update/${valueID}`, {
            method: "PUT",
            header:{
                'Cache-Control': 'no-cache, no-store, must-revalidate',
            },
            cache: false,
            responseType: "json"
        });

        // Run request
        return request.fetch(formData);

    }

    /**
     * On Submit Delete
     * 
     * Delete item in back
     * 
     * @param entityValue
     * @param valueID
     * @returns {Promise<any>}
     */
    private _onSubmiDelete = async (entityValue:string, valueID:string):Promise<any> => {

        // Check event
        if(this._options.onBeforeSubmit){

            // New formdata
            let formData = new FormData();

            // Append if to formdata
            formData.append("id", valueID);

            // Check submit before
            this._options.onBeforeSubmit(entityValue, formData);

        }

        // Prepare request
        let request = new Crazyrequest(`/api/v2/${entityValue}/delete/${valueID}`, {
            method: "DELETE",
            header:{
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: false
        });

        // Run request
        return request.fetch();

    }

    /**
     * On Submit Send
     * 
     * Send form data to url
     * 
     * @param url
     * @param formdata
     * @param method
     * @returns {Promise<any>}
     */
    private _onSubmitSend = async (url:string, formData:FormData, method:CrazyFetchOption["method"] = "get"):Promise<any> => {

        // Prepare request
        let request = new Crazyrequest(url, {
            method: method,
            header:{
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: false
        });

        // Run request
        return request.fetch(formData);

    }

    /** Public methods | UI
     ******************************************************
     */

    /**
     * Lock
     * 
     * Lock form
     * 
     * @returns void
     */
    public lock = ():void => {

        // Get all select and input on form el
        let items = this._formEl.querySelectorAll("select[name], input[name], button[type=\"submit\"], button[type=\"reset\"]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check tag name
                if(items[i].tagName == "INPUT"){

                    // Set Read only
                    items[i].setAttribute("readonly", "loading");

                }else
                if(items[i].tagName == "SELECT"){

                    // Set Read only
                    items[i].setAttribute("disabled", "loading");

                }else
                // Chech button
                if(items[i].tagName == "BUTTON"){

                    // Set Read only
                    items[i].setAttribute("disabled", "loading");

                }


            }

    }

    /**
     * Lock
     * 
     * Lock form
     * 
     * @returns void
     */
    public unlock = ():void => {

        // Get all select and input on form el
        let items = this._formEl.querySelectorAll("select[name], input[name], button[type=\"submit\"], button[type=\"reset\"]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check tag name
                if(items[i].tagName == "INPUT" && items[i].hasAttribute("readonly") && items[i].getAttribute("readonly") == "loading"){

                    // Set Read only
                    items[i].removeAttribute("readonly");

                }else
                // Check tag name
                if(items[i].tagName == "INPUT" && items[i].hasAttribute("disabled") && items[i].getAttribute("disabled") == "loading"){

                    // Set Read only
                    items[i].removeAttribute("disabled");

                }else
                // Check tag name
                if(items[i].tagName == "SELECT" && items[i].hasAttribute("disabled") && items[i].getAttribute("disabled") == "loading"){

                    // Set Read only
                    items[i].removeAttribute("disabled");

                }else{

                    // Chech button
                    if(items[i].tagName == "BUTTON" && items[i].hasAttribute("disabled") && items[i].getAttribute("disabled") == "loading"){

                        // Set Read only
                        items[i].removeAttribute("disabled");

                    }

                }

            }

    }

    /** Private methods | Form
     ******************************************************
     */

    /**
     * Ingest Form
     * 
     * Ingest form on instance
     * 
     * @param form
     * @return void
     */
    private _ingestForm = async(form:string|HTMLFormElement):Promise<void> => {

        // Declare variable
        let formEl:HTMLFormElement|null;

        // Check form is string
        if(typeof form === "string"){

            // Check 
            if(!form)

                // New error
                throw new PageError(`Form is not valid`);

            // Search el
            formEl = Root.getEl().querySelector(form);

            // Check 
            if(formEl === null)

                // New error
                throw new PageError(`Form "#${form}" can't be found on the page`);


        }else
        // El
        if(form.nodeName === "FORM"){

            // Set form EL
            formEl = form;

        // If not form el
        }else

            // New error
            throw new PageError(`Element given in form instance is not a form node`);

        // Push form
        this._formEl = formEl;

    }

    /**
     * Init Filter
     * 
     * Check if form el is filter
     * 
     * @returns {Promise<void>}
     */
    private _initFilter = async():Promise<void> => {

        // Check if filter
        if(typeof this._formEl.dataset.formFilter === "string"){

            // Set filter option
            this._options.filter = true;

            // Ingest Filter From Query
            this._ingestFilterFromQuery(this._formEl);

            // Process filter
            this._processForFilter(this._formEl);

        }

    }

    /**
     * Init Form
     * 
     * Prepare form input
     * 
     * @returns {Promise<void>}
     */
    private _initForm = async():Promise<void> => {

        // Get all input
        let allInputEls = this._formEl.querySelectorAll("input, select");

        // Check inputs
        if(allInputEls.length)

            // Iteration
            for(let inputEl of Array.from(allInputEls)){

                // Check if item given is input or select
                if(inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement){

                    // Check is validate is enable
                    if(inputEl.classList.contains("validate"))

                        // Init validate on input
                        await this._initEventValidateOnInput(inputEl, allInputEls);

                    // Check if required is enable
                    if(inputEl.required == true)

                        // Init required on input
                        await this._initEventRequiredOnInput(inputEl, allInputEls);

                    // Get input type
                    let inputType:string = "type" in inputEl.dataset && typeof inputEl.dataset.type === "string" && inputEl.dataset.type
                        ? inputEl.dataset.type
                        : inputEl.type
                    ;

                    // Check depends
                    inputEl.dataset.depends && this._addDependencies(inputEl, inputEl.dataset.depends);

                    // Get init method name
                    let initMethodName:string = `_init${UtilityStrings.ucfirst(inputType.toLowerCase())}Input`;

                    // Check initMethodName in this 
                    if(initMethodName in this){

                        // Run method
                        await this[initMethodName](inputEl);

                    }else

                        // Check init
                        console.info(`Need to implement "${initMethodName}"`);

                }

            }

    }

    /**
     * Init On Ready
     * 
     * Check action to accompish before loading
     * 
     * @returns {Promise<void>}
     */
    private _initOnReady = async():Promise<void> => {

        // Get attribute on ready and entity
        let onreadyAttr = this._formEl.getAttribute("onready");

        // Get attribute entity
        let entityAttr = this._formEl.getAttribute("entity");

        // Check if last
        if(entityAttr && onreadyAttr && /^last\/[0-9]+$/.test(onreadyAttr)){

            // Prepare request 
            let request = new Crazyrequest(
                `/api/v2/${entityAttr}/${onreadyAttr}`,
                {
                    method: "get",
                    cache: false,
                    responseType: "json",
                    from: "internal"
                }
            );

            // Fetch request
            request.fetch()
                .then(value => {
                    
                    // Unlock
                    this.unlock();

                    // Check result
                    if("results" in value && value.results.length)

                        // Set values
                        this.setValue(value.results[0], value.results[0]._id);

                })
            ;

        }else
        // Check if id
        if(entityAttr && onreadyAttr && /^id\/[a-zA-Z0-9]+$/.test(onreadyAttr)){

            // Get id
            let id = onreadyAttr.replace("id/", "");

            // Set value id if not already done
            !this._formEl.hasAttribute("value_id") && this._formEl.setAttribute("value_id", id.toString());

            // Prepare request 
            let request = new Crazyrequest(
                `/api/v2/${entityAttr}/${id}`,
                {
                    method: "get",
                    cache: false,
                    responseType: "json",
                    from: "internal"
                }
            );

            // Fetch request
            request.fetch()
                .then(value => {
                    
                    // Unlock
                    this.unlock();

                    // Check result
                    if("results" in value && value.results.length)

                        // Set values
                        this.setValue(value.results[0], value.results[0]._id);

                })
            ;

        // If not action, unlock form
        }else
            
            // Unlock
            this.unlock();

    }

    /**
     * Init Event on submit
     * 
     * Event On submit on form
     * 
     * @return Promise<void>
     */
    private _initEventOnSubmit = async():Promise<void> => {

        // Add event on them
        this._formEl.addEventListener(
            "submit",
            this.eventOnSubmit
        );

    }

    /**
     * Init Event on reset
     * 
     * Event On reset on form
     * 
     * @return Promise<void>
     */
    private _initEventOnReset = async():Promise<void> => {

        // Add event on reset
        this._formEl.addEventListener(
            "reset",
            this.eventOnReset
        );

    }

    /**
     * Init Event on change
     * 
     * Event On Change on form
     * 
     * @returns {void}
     */
    private _initEventOnChange = async():Promise<void> => {

        // Iteration evenType
        for(let eventType of ["change", "input"])

            // Change
            this._formEl.addEventListener(eventType, (event) => {

                // Set current target
                const currentTarget = event.currentTarget;

                // Get target
                const target = event.target;

                // Process Filter
                if(currentTarget instanceof HTMLFormElement && this._options.filter){

                    // Process for filter
                    this._processForFilter(currentTarget)

                }

                // Check options
                if(this._onChangeCallable && currentTarget && target && currentTarget instanceof HTMLFormElement && ( target instanceof HTMLInputElement || target instanceof HTMLSelectElement ) && this._onChangeOptions.eventType === eventType){

                    // Call callable
                    this._onChangeCallable({
                        formEl: currentTarget,
                        formData: this.getFormData(currentTarget),
                        itemEl: target,
                        type: eventType,
                        valid: this.isValid(currentTarget),
                    });

                }

            });

    }

    /**
     * Init Event Validate On Input
     * 
     * @param inputEl 
     * @param allInputEls 
     */
    private _initEventValidateOnInput = async(inputEl:HTMLInputElement|HTMLSelectElement, allInputEls:NodeListOf<Element>):Promise<void> => {

        // Add event on input els
        inputEl.addEventListener("invalid", (e:Event) => {

            // Prevent default
            e.preventDefault();

            // Check currentTarget and its parent
            if(e.target && e.target instanceof HTMLElement && e.target.closest("div.input-field")){

                // Get parent input field
                let inputFieldEl = e.target.closest("div.input-field");

                // Add error
                inputFieldEl?.classList.add("error");

                // Suffix

                // Check if already suffix
                let suffixEl = inputFieldEl?.querySelector("div.suffix");

                // Check suffix el
                if(suffixEl){

                    // Replace class
                    suffixEl.classList.replace("suffix", "suffix-hidden");

                }

                // Create error suffix
                let suffixErrorEl = this._newSuffixErrorEl();

                // Add suffix
                inputEl.before(suffixErrorEl);

                // supporting-text

                // Check if already suffix
                let supportingTextEl = inputFieldEl?.querySelector("span.supporting-text");

                // Check suffix el
                if(supportingTextEl){

                    // Replace class
                    supportingTextEl.classList.replace("supporting-text", "suffix-hidden");

                }

                // Create error suffix
                let supportingTextErrorEl = this._newSupportingTextErrorEl(inputEl, allInputEls);

                // Add suffix
                inputFieldEl?.append(supportingTextErrorEl);

            }

        });

    }

    /**
     * Init Event Required On Input
     * 
     * With delay when key up
     * 
     * @param inputEl 
     * @param allInputEls 
     */
    private _initEventRequiredOnInput = async(inputEl:HTMLInputElement|HTMLSelectElement, allInputEls:NodeListOf<Element>):Promise<void> => {

        // Check if input
        if(inputEl instanceof HTMLInputElement){

            // Event function
            let eventRequiredOnInput = (e:Event) => {
    
                // Prevent default
                e.preventDefault();
    
            };

            // Set timer
            let timeout:ReturnType<typeof setTimeout>|null = null;
    
            // Set wait time
            let waitTime:number = 500;

            // Add event on change
            inputEl.addEventListener('keyup', (e:Event) => {    
                
                // Check timeout
                if(timeout !== null){

                    // Clear timeout
                    clearTimeout(timeout);

                }

                // Set timeout
                timeout = setTimeout(() => {

                    // call event
                    eventRequiredOnInput(e);

                }, waitTime);

            });

        }

    }

    /** Private methods | Retrieve value
     ******************************************************
     */
    
    /**
     * Extract Key Value
     * 
     * @param itemEl:HTMLElement
     * @return FormObjectForFormDataAppend
     */
    private extractKeyValue = (itemEl:HTMLElement):null|Array<any> => {

        // Declare result
        let result = null;

        // Get type
        let type:string|null = null;

        // Get type of input el
        if("type" in itemEl && itemEl.type && typeof itemEl.type === "string")

            // Set type
            type = itemEl.type;

        // Get type of input el
        if("type" in itemEl.dataset && itemEl.dataset.type && typeof itemEl.dataset.type === "string")

            // Set type
            type = itemEl.dataset.type;


        // Check type
        if(typeof type === "string" && typeof this[`${type}Retrieve`] === "function")

            // Set result
            result = this[`${type}Retrieve`](itemEl);

        // Return null
        return result;

    }
    
    /**
     * Extract Key Multiple Value
     * 
     * @param itemEl:HTMLElement
     * @param multiple Multiple item
     * @return FormObjectForFormDataAppend
     */
    private extractKeyMultipleValue = (itemEl:HTMLElement, multiple:boolean = false):null|[Array<any>] => {

        // Declare result
        let result = null;

        // Get type
        let type:string|null = null;

        // Get type of input el
        if("type" in itemEl && itemEl.type && typeof itemEl.type === "string")

            // Set type
            type = itemEl.type;

        // Get type of input el
        if("type" in itemEl.dataset && itemEl.dataset.type && typeof itemEl.dataset.type === "string")

            // Set type
            type = itemEl.dataset.type;


        // Check type
        if(typeof type === "string" && typeof this[`${type}RetrieveMultiple`] === "function")

            // Set result
            result = this[`${type}RetrieveMultiple`](itemEl);

        // Return null
        return result;

    }

    /** Private methods | Retrieve Hidden
     ******************************************************
     */

    /**
     * Retrieve Hidden
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private hiddenRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Hidden
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private hiddenRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve Text
     ******************************************************
     */

    /**
     * Retrieve Text
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private textRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Text
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private textRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve checkbox
     ******************************************************
     */

    /**
     * Retrieve checkbox
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private checkboxRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple checkbox
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private checkboxRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve Select
     ******************************************************
     */

    /**
     * Retrieve Select
     * 
     * - Prefix : OK
     * - Suffix : OK
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private selectRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Select
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private selectRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve checkbox
     ******************************************************
     */

    /**
     * Retrieve Date
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private dateRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Date
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private dateRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve number
     ******************************************************
     */

    /**
     * Retrieve Number
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private numberRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Number
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private numberRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve number
     ******************************************************
     */

    /**
     * Retrieve Email
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private emailRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Email
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private emailRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve color
     ******************************************************
     */

    /**
     * Retrieve Color
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private colorRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Color
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private colorRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve password
     ******************************************************
     */

    /**
     * Retrieve Password
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private passwordRetrieve = (itemEl:HTMLElement):null|Array<any> => {

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
     * Retrieve Multiple Password
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private passwordRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

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

    /** Private methods | Retrieve file
     ******************************************************
     */

    /**
     * File Retrieve
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private fileRetrieve = (itemEl:HTMLElement):null|Array<any> => {

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("name" in itemEl && itemEl instanceof HTMLInputElement && itemEl.files?.length){

            let key:string = itemEl.name as string;

            // Set result
            let files:FileList = itemEl.files;

            // Push in result
            result = [key, Array.from(files).at(0)];

        }else
        // Check if pond instance
        if(itemEl.dataset.pondId && itemEl instanceof HTMLInputElement){

            // Search el
            let pondEl = itemEl.closest("form")?.querySelector(`div#${itemEl.dataset.pondId}`);

            // Check pond el
            if(pondEl instanceof HTMLDivElement){

                let key:string = itemEl.name as string;

                // Get pond instance
                let pondInstance = FilePond.find(pondEl);

                // Get files
                let files = pondInstance.getFiles();

                // Set result
                result = [key, files.length ? files[0].file as File : ""];

            }

        }

        // Return result
        return result;

    }

    /**
     * File Retrieve Multiple
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private fileRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

        // Set result
        let result:null|Array<any>[] = null;

        // Check value
        if("name" in itemEl && itemEl instanceof HTMLInputElement && itemEl.files?.length){

            let key:string = itemEl.name as string;

            // Set result
            let files = Array.from(itemEl.files);

            // Check files
            if(files.length) for(let currentFile of files) if(currentFile instanceof File){

                // Check result
                if(result === null) result = [];

                // Push in result
                result.push([key, currentFile]);

            }

        }else
        // Check if pond instance
        if(itemEl.dataset.pondId && itemEl instanceof HTMLInputElement){

            // Search el
            let pondEl = itemEl.closest("form")?.querySelector(`div#${itemEl.dataset.pondId}`);

            // Check pond el
            if(pondEl instanceof HTMLDivElement){

                let key:string = itemEl.name as string;

                // Get pond instance
                let pondInstance = FilePond.find(pondEl);

                // Get files
                let files = pondInstance.getFiles();

                // Check files
                if(files.length) for(let currentFile of files) if(currentFile instanceof File){
    
                    // Check result
                    if(result === null) result = [];
    
                    // Push in result
                    result.push([key, currentFile]);
    
                }

            }

        }

        // Return result
        return result;

    }

    /** Private methods | Retrieve Radio
     ******************************************************
     */

    /**
     * Retrieve radio
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    private radioRetrieve = (itemEl:HTMLElement):null|Array<any> => {

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("value" in itemEl && "name" in itemEl && itemEl instanceof HTMLInputElement){

            // Declare value
            let value:string = "";

            // Declare key
            let key:string = itemEl.name as string;

            // Check form
            if(itemEl.form && itemEl.name){

                // Get all similar radio
                let radioEls = itemEl.form.querySelectorAll(`input[name=${itemEl.name}]`);

                // Iteration
                if(radioEls.length) for(let radioEl of Array.from(radioEls)) if(radioEl instanceof HTMLInputElement && radioEl.checked){

                    // Set value
                    value = radioEl.value as string;

                    // Break
                    break;

                }

            }

            // Push in result
            result = [key, value];

        }

        // Return result
        return result;

    }

    /**
     * Retrieve Radio Multiple
     * 
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    private radioRetrieveMultiple = (itemEl:HTMLElement):null|Array<any>[] => {

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("value" in itemEl && "name" in itemEl && itemEl instanceof HTMLInputElement){

            // Declare value
            let value:string = "";

            // Declare key
            let key:string = itemEl.name as string;

            // Check form
            if(itemEl.form && itemEl.name){

                // Get all similar radio
                let radioEls = itemEl.form.querySelectorAll(`input[name=${itemEl.name}]`);

                // Iteration
                if(radioEls.length) for(let radioEl of Array.from(radioEls)) if(radioEl instanceof HTMLInputElement && radioEl.checked){

                    // Set value
                    value = radioEl.value as string;

                    // Break
                    break;

                }

            }

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /** Private methods | Set value
     ******************************************************
     */

    /**
     * Set Text
     * 
     * Set text in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private textSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

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
     * Set Email
     * 
     * Set text in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private emailSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

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
     * Set Hidden
     * 
     * Set hidden in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private hiddenSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

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

            // Dispatch event change
            itemEl.dispatchEvent(new Event('change', { bubbles: true }));

        }

    }

    /**
     * Set Password
     * 
     * Set text in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private passwordSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

        // Check itemEl 
        if(itemEl.tagName == "INPUT" && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Set id
            this._setID(valuesID, itemEl);

        }

    }

    /**
     * Set Color
     * 
     * Set text in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private colorSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

        // Check itemEl 
        if(itemEl.tagName == "INPUT" && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            this._setID(valuesID, itemEl);

        }

    }

    /**
     * Set Select 
     * 
     * Set select in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private selectSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

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
                            this._setID(valuesID, itemEl);

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
                    this._setID(valuesID, itemEl);

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
                this._setID(valuesID, itemEl);

            }

        }

    }

    /**
     * Set Number
     * 
     * Set number in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private numberSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

        // Check itemEl 
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            this._setID(valuesID, itemEl);

        }

    }

    /**
     * Set Date
     * 
     * Set date in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private dateSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null):void => {

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
                this._setID(valuesID, itemEl);

            }

        }

    }

    /**
     * Set File
     * 
     * Set file in item
     * 
     * @param itemEl:HTMLElement
     * @param value:string
     * @return void
     */
    private fileSet = (itemEl:HTMLElement, value:formFilePondValue|formFilePondValue[], valuesID:string|Object|null):void => {

        // Check itemEl 
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Check if filepont
            if(itemEl.classList.contains("filepond--browser") && itemEl.parentElement instanceof HTMLElement){

                // Get pond instance
                let pondInstance = FilePond.find(itemEl.parentElement);

                // Check pondInstance
                if(pondInstance){

                    // Set files
                    let files:File[] = [];

                    // Check if value is array
                    if(Array.isArray(value)){

                        // Iteration value
                        for(let item of value){

                            // Set fake content
                            let fakeContent = "";
                            
                            // Set fake content
                            if(item.options.file.type == "application/json"){
    
                                // Set fakeContent
                                fakeContent = JSON.stringify({});
    
                            }
    
                            // New blob
                            const blob = new Blob(
                                [fakeContent], 
                                { type: item.options.file.type }
                            );
    
                            // New file
                            const currentFile = new File(
                                [blob], 
                                item.source, 
                                { type: item.options.file.type }
                            );

                            // Push files
                            files.push(currentFile);

                        }

                        // Add Files
                        pondInstance.addFiles(files);

                    }else{

                        // Set fake content
                        let fakeContent = "";
                        
                        // Set fake content
                        if(value.options.file.type == "application/json"){

                            // Set fakeContent
                            fakeContent = JSON.stringify({});

                        }

                        // New blob
                        const blob = new Blob(
                            [fakeContent], 
                            { type: value.options.file.type }
                        );

                        // New file
                        const file = new File(
                            [blob], 
                            value.source, 
                            { type: value.options.file.type }
                        );

                        // Add file
                        pondInstance.addFile(file);

                    }

                    // Set value
                    /* itemEl.setAttribute("value", value);
    
                    // Dispatch event change
                    itemEl.dispatchEvent(new Event("change"));
    
                    // Set id
                    this._setID(valuesID, itemEl); */

                }

            }

        }

    }

    /** Private methods | Set value | Set Custom Data
     ******************************************************
     */

    /**
     * Set ID
     * 
     * Set ID of the item of the from
     * 
     * @param valueID
     * @param itemEl
     * @returns void 
     */
    private _setID(valueID:string|Object|null, itemEl:HTMLElement):void {

        // Declare key collection
        let keysCollection:Array<string> = [];
        let currentValueID:string|null;

        // Check values id is string
        if(typeof valueID === "string"){

            // Set entity_id
            itemEl.setAttribute("value_id", valueID);

        }else
        // Check value is object
        if(valueID !== null && Object.keys(valueID).includes(itemEl["name"])){

            // Set entity_id
            itemEl.setAttribute("value_id", valueID[itemEl["name"]]);

        }else
        // Check if $oid
        if(valueID && typeof valueID === "object" && "$oid" in valueID){

            // Set entity_id
            itemEl.setAttribute("value_id", valueID["$oid"] as string);

        }

        // Get items of form
        let itemsEls = this._formEl.querySelectorAll("select, input");

        // Iterations items
        for(let i = 0; i < itemsEls.length; i++) {

            // Get value ID
            currentValueID = itemsEls[i].getAttribute("value_id");

            // Check if attribute _ID
            if(currentValueID === null)

                // Stop method
                return;

            // Check if key in keysCollection
            if(!keysCollection.includes(currentValueID))

                // Push key in collection
                keysCollection.push(currentValueID);
            

        }

        // Check if multiple keus in key collection
        if(keysCollection.length > 1)

            // Stop method
            return;

        // Iteration of items
        for(let i = 0; i < itemsEls.length; i++)

            // Remove attribute value id
            itemsEls[i].removeAttribute("value_id");

        // Push value id in form
        this._formEl.setAttribute("value_id", keysCollection.pop() as string);

    }

    /** Private methods | Default
     ******************************************************
     */

    /**
     * Get Default
     * 
     * @param inputEl
     * @returns {any}
     */
    private _getDefaultOfInput = (inputEl:HTMLInputElement|HTMLSelectElement):null|string|boolean|number|Date|number[]|string[] => {

        // Set result
        let result:null|string|boolean|number|Date|number[]|string[] = null;

        // Get type
        let type = inputEl.type;

        // Check if data type 
        if("type" in inputEl.dataset && inputEl.dataset.type)

            // Override type
            type = inputEl.dataset.type;

        // Get name
        let name = inputEl.name;

        // Is multiple
        let isMultiple = name && name.slice(-2) == "[]";

        // Check if input
        if(inputEl instanceof HTMLInputElement){

            // Check if default in current insput
            if(inputEl.hasAttribute("default") && typeof inputEl.getAttribute("default") === "string"){

                // Set result
                result = inputEl.getAttribute("default") as string;

                // Check type is checkbox
                if(!isMultiple && type == "checkbox"){

                    // Check result
                    result = ['1', 'true', 'on', 'yes'].includes(result) 
                        ? true
                        : false
                    ;

                }else 

                // Check type is range
                if(!isMultiple && ["range", "number"].includes(type)){

                    // Get number of result
                    result = Number(result);

                }else
                
                // Check is date (to implement datetime)
                if(!isMultiple && ["date"].includes(type)){

                    // Convert to date
                    result = new Date(result);

                }else

                // If radio
                if(type === "radio"){

                    // Get parent
                    let parent = inputEl.closest(`[data-radio-name="${name.replace("[]", "")}"]`);

                    // Check parent
                    if(parent){

                        // Search all radioEls in parent
                        let radioInputEls = parent.querySelectorAll(`input[name="${name}"]`);

                        // Check radioInputEls
                        if(radioInputEls?.length){

                            // Iteration radioInputEls
                            radioInputEls.forEach((radioInputEl) => {

                                // Check radioInputEl
                                if(radioInputEl instanceof HTMLInputElement && radioInputEl.hasAttribute("default") && radioInputEl.hasAttribute("value")){

                                    // Check multiple
                                    if(isMultiple){

                                        // Check result
                                        if(!Array.isArray(result))

                                            // Set result
                                            result = [];

                                        // Set result
                                        // @ts-ignore
                                        result.push(radioInputEl.value);

                                    }else{

                                        // Set result
                                        result = radioInputEl.value;

                                    }

                                }

                            });
                        }

                    }

                }else
                
                // Check is date (to implement datetime)
                if(!isMultiple && ["date"].includes(type)){

                    // Convert to date
                    result = new Date(result);

                }else

                // If radio
                if(type === "select"){

                    // Search all radioEls in parent
                    let optionInputEls = inputEl.querySelectorAll("option");

                    // Check radioInputEls
                    if(optionInputEls?.length){

                        // Iteration radioInputEls
                        optionInputEls.forEach((optionInputEl) => {

                            // Check radioInputEl
                            if(optionInputEl instanceof HTMLOptionElement && optionInputEl.hasAttribute("default") && optionInputEl.hasAttribute("value")){

                                // Check multiple
                                if(isMultiple){

                                    // Check result
                                    if(!Array.isArray(result))

                                        // Set result
                                        result = [];

                                    // Set result
                                    // @ts-ignore
                                    result.push(optionInputEl.value);

                                }else{

                                    // Set result
                                    result = optionInputEl.value;

                                }

                            }

                        });
                    }

                    // Check if json
                    if(UtilityStrings.isJson(result)){

                        // Decode json
                        result = JSON.parse(result);

                    }

                }

            }


        }else
        // Check if select
        if(inputEl instanceof HTMLSelectElement){

            // If radio
            if(["select", "select-multiple", "select-one"].includes(type)){

                // Check default as attribute
                if(inputEl.hasAttribute("default")){

                    // Get default
                    result = inputEl.getAttribute("default");

                }else
                // Check options
                if(inputEl.querySelectorAll("option").length){

                    // Search all radioEls in parent
                    let optionInputEls = inputEl.querySelectorAll("option");

                    // Search first title
                    let firstTitle:null|string = null;

                    // Check radioInputEls
                    if(optionInputEls?.length){

                        // Iteration radioInputEls
                        optionInputEls.forEach((optionInputEl) => {

                            // Check radioInputEl
                            if(optionInputEl instanceof HTMLOptionElement && optionInputEl.hasAttribute("default") && optionInputEl.hasAttribute("value")){

                                // Check multiple
                                if(isMultiple){

                                    // Check result
                                    if(!Array.isArray(result))

                                        // Set result
                                        result = [];

                                    // Set result
                                    // @ts-ignore
                                    result.push(optionInputEl.value);

                                }else{

                                    // Set result
                                    result = optionInputEl.value;

                                }

                            }else
                            // Check if title
                            if(!optionInputEl.hasAttribute("value") && optionInputEl.innerText && firstTitle === null){

                                // Set first title
                                firstTitle = optionInputEl.innerText;

                            }

                        });
                    }

                    // Check if result empty
                    if(!result || result === null){

                        // Set result with first title
                        result = firstTitle;

                    }
                
                }

            }

        }

        // Return result 
        return result;

    }

    /** Private methods | Init input
     ******************************************************
     */

    /**
     * Init Color Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initColorInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

        // Check pickr
        if(inputEl instanceof HTMLInputElement && "colorPicker" in inputEl.dataset && inputEl.dataset.colorPicker == "pickr"){

            // Create divEl
            let divEl = document.createElement("div");

            // Append el after el
            inputEl.after(divEl);

            // Hide input
            inputEl.classList.add("hide");

            // Prepare options
            let options:Partial<Pickr.Options> = {
                el: divEl,
                theme: "colorTheme" in inputEl.dataset && ['classic', 'monolith', 'nano'].includes(inputEl.dataset.colorTheme as string)
                    ? inputEl.dataset.colorTheme as Pickr.Theme
                    : 'classic'
                ,
                lockOpacity: "colorOpacity" in inputEl.dataset && ['false', '0', '', 'null'].includes(inputEl.dataset.colorOpacity as string)
                    ? true
                    : false
                ,
                swatches: [
                    'rgba(244, 67, 54, 1)',
                    'rgba(233, 30, 99, 0.95)',
                    'rgba(156, 39, 176, 0.9)',
                    'rgba(103, 58, 183, 0.85)',
                    'rgba(63, 81, 181, 0.8)',
                    'rgba(33, 150, 243, 0.75)',
                    'rgba(3, 169, 244, 0.7)',
                    'rgba(0, 188, 212, 0.7)',
                    'rgba(0, 150, 136, 0.75)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(139, 195, 74, 0.85)',
                    'rgba(205, 220, 57, 0.9)',
                    'rgba(255, 235, 59, 0.95)',
                    'rgba(255, 193, 7, 1)'
                ],
                components: {
                    // Main components
                    preview: true,
                    opacity: true,
                    hue: true,
                    // Input / output Options
                    interaction: {
                        hex: true,
                        rgba: true,
                        hsla: true,
                        hsva: true,
                        cmyk: true,
                        input: true,
                        clear: true,
                        save: true
                    }
                }
            };

            // Check locale
            if(inputEl.dataset.colorLocale && ["fr-fr"].includes(inputEl.dataset.colorLocale)){

                // Set i18n
                options.i18n = {

                    // Strings visible in the UI
                   'ui:dialog': 'boîte de dialogue du sélecteur de couleur',
                   'btn:toggle': 'basculer la boîte de dialogue du sélecteur de couleur',
                   'btn:swatch': 'échantillon de couleur',
                   'btn:last-color': 'utiliser la couleur précédente',
                   'btn:save': 'Enregistrer',
                   'btn:cancel': 'Annuler',
                   'btn:clear': 'Effacer',
                
                   // Strings used for aria-labels
                   'aria:btn:save': 'enregistrer et fermer',
                   'aria:btn:cancel': 'annuler et fermer',
                   'aria:btn:clear': 'effacer et fermer',
                   'aria:input': 'champ de saisie de couleur',
                   'aria:palette': 'zone de sélection des couleurs',
                   'aria:hue': 'curseur de sélection de teinte',
                   'aria:opacity': 'curseur de sélection d\'opacité'

                }

            }

            // Check if input has default
            if(inputEl.hasAttribute("value")){

                // Get default
                var currentValue = inputEl.getAttribute("value");

                // Check current value
                if(currentValue && currentValue != "randomHex()"){

                    // Set default on option
                    options.default = currentValue;

                }else
                // Check if input has default
                if(inputEl.hasAttribute("default")){
    
                    // Get default
                    var currentDefault = inputEl.getAttribute("default");

    
                    // Check current value
                    if(currentDefault === "randomHex()"){

                        // Generate a random integer between 0 and 16777215 (0xFFFFFF)
                        const randomColor = Math.floor(Math.random() * 16777216);
                        
                        // Convert to hexadecimal and pad with leading zeros if necessary
                        options.default = `#${randomColor.toString(16).padStart(6, '0')}`;

                    }else
                    // Check currentValue
                    if(currentDefault)

                        // Set default on option
                        options.default = currentDefault;
    
    
                }



            }

            // Simple example, see optional options for more configuration.
            const pickr = Pickr.create(options as Pickr.Options);

            // Add event on save
            pickr.on("save", (color, instance) => {

                // Get color
                let hexa:string = color.toHEXA();

                // Set value on inputEl
                inputEl.value = hexa

                // Close instance
                instance.hide();

            });

            // Add event on input
            inputEl.addEventListener(
                "change",
                (e:Event) => {

                    // Get targelt
                    let el = e.currentTarget;

                    // Check el
                    if(el instanceof HTMLInputElement){

                        // Get value
                        let value = el.value;

                        // Set value in pickr
                        pickr.setColor(value);

                    }

                }
            )

        }

    }
    
    /**
     * Init Number Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initNumberInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

        // Declare options
        let options:MaskedNumberOptions = {
            mask: Number,
            skipInvalid: true,
            thousandsSeparator: " ",
            radix: ".",
            mapToRadix: [','],
            autofix: true,
        };

        // Check if max
        inputEl.hasAttribute("max") && inputEl.getAttribute("max") && (options.max = Number(inputEl.getAttribute("max")));

        // Check if min
        inputEl.hasAttribute("min") && inputEl.getAttribute("min") && (options.min = Number(inputEl.getAttribute("min")));

        // check if decimal
        inputEl.hasAttribute("step") && inputEl.getAttribute("step")?.includes(".") && (options.scale = inputEl.getAttribute("step")?.split(".").at(-1)?.length);

        // Set instance
        IMask(inputEl, options);

    }

    /**
     * Init Date Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initDateInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

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

            console.log("toto");
            
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

                console.log(date);

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
     * Init Password Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initPasswordInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

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
     * Init Number Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initSelectInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

        // Promise wrap
        return new Promise<void>((resolve) => {

            // Check maska
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
                if(inputEl.dataset && "selectClear" in inputEl.dataset)

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
                    if(remoteData.label.includes("{{") && remoteData.label.includes("}}")){

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
                        let formEl = inputEl.closest(`form[partial="form"]`);

                        // Check param
                        if(Object.keys(queryParam).length)

                            // Update query
                            queryParam = this._processQueryParams(queryParam, formEl instanceof HTMLFormElement ? formEl : null);

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
                                this.selectSet(inputEl, value, valueId);

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
                                            this.selectSet(inputEl, valueParsed.value, valueParsed.valuesID);

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
                        let formEl = inputEl.closest(`form[partial="form"]`);

                        // Check param
                        if(Object.keys(queryParam).length)

                            // Update query
                            queryParam = this._processQueryParams(queryParam, formEl instanceof HTMLFormElement ? formEl : null);

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
                                this.selectSet(inputEl, value, valueId);

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
                                            this.selectSet(inputEl, valueParsed.value, valueParsed.valuesID);

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
     * Init File Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initFileInput = async (inputEl:HTMLSelectElement|HTMLInputElement):Promise<void> => {

        // Check maska
        if(inputEl instanceof HTMLInputElement){

            // Check if input has filepond
            if(inputEl.classList.contains("filepond")){

                // Check if preview
                if(typeof inputEl.dataset.filePreview === "string")

                    // Register plugin
                    FilePond.registerPlugin(FilePondPluginImagePreview);

                // Register exif plugin
                FilePond.registerPlugin(FilePondPluginImageExifOrientation);

                // Prepare options
                let options:FilePond.FilePondOptions = {
                    
                    // Set on init
                    oninit: () => {

                        // Check element
                        if(pondInstance.element){

                            // Apply margin
                            pondInstance.element.classList.add("mb-0", "mt-6");

                            // Remove credits

                            // Search credit
                            let els = pondInstance.element.querySelectorAll(".filepond--credits");

                            // Iteration and delete
                            els?.forEach((value:Element):void => value.remove());

                            // Search input with name
                            let el = pondInstance.element.querySelector("input[name]");
        
                            // Check el
                            if(el instanceof HTMLInputElement){
        
                                // Check id
                                if(pondInstance.id)
        
                                    // Add data type
                                    el.dataset.pondId = pondInstance.id;
        
                                // Set type
                                el.dataset.type = "file";
        
                            }

                        }

                    }

                }

                // Set name
                // if(inputEl.name)

                    // Set name

                // Check if max file
                if(typeof inputEl.dataset.maxFiles === "string")

                    // set max files
                    options.maxFiles = Number(inputEl.dataset.maxFiles);

                // Check if multiple
                if(inputEl.multiple)

                    // set max files
                    options.allowReorder = true;

                // Check if accept
                if(inputEl.accept){

                    // Register validate type
                    FilePond.registerPlugin(FilePondPluginFileValidateType);

                }

                // Check lang
                if(typeof inputEl.dataset.fileLocale === "string")

                    // Check if fr
                    if(["fr-fr", "fr_FR"].includes(inputEl.dataset.fileLocale)){

                        // Register lang
                        options = {...options, ...fr_FR};

                    }

                // Create pond instance
                let pondInstance = FilePond.create(inputEl, options);

                // Add event
                pondInstance.on("updatefiles", () => {

                    // Search input with name
                    let el = pondInstance.element?.querySelector("input[name]");

                    // Check el
                    if(el instanceof HTMLInputElement){

                        // Check id
                        if(pondInstance.id)

                            // Add data type
                            el.dataset.pondId = pondInstance.id;

                        // Set type
                        el.dataset.type = "file";

                    }
                        

                });

                // Push it into el
                // @ts-ignore
                inputEl.pondInstance = pondInstance;

            }

        }

    }

    /** Private methods | Depends
     ******************************************************
     */

    /**
     * Add Dependencies
     * 
     * @param inputEl 
     * @param dependencies 
     */
    private _addDependencies = (
        inputEl:HTMLInputElement|HTMLSelectElement,
        dependencies:string|string[],
    ):void => {
        
        // Check depends
        if(typeof dependencies === "string")

            // Convert it to array
            dependencies = dependencies.split(",");

        // Remove duplicates
        dependencies = <string[]>[...new Set(dependencies)];

        // Check dependency
        /* if(dependencies.length == 1){

            // Get type of input el
            let inputType = inputEl.dataset.type
                ? inputEl.dataset.type
                : inputEl.type
            ;

            // Check inputType
            if(inputType){

                // Iteration of dependencies
                for(let dependency of dependencies){

                    // Search el
                    let dependencyEl:HTMLInputElement|HTMLSelectElement|null = !dependency
                        ? null
                        : this._formEl.querySelector(`input[name="${dependency}"], select[name="${dependency}"`)
                    ;

                    // Check dependency
                    if(dependencyEl){

                        // Get type of input el
                        let dependencyType = dependencyEl.dataset.type
                            ? dependencyEl.dataset.type
                            : dependencyEl.type
                        ;

                        // Check if method to retrieve value is set
                        if(dependencyType && typeof this[`${dependencyType}Retrieve`] === "function"){
                    
                            // Set result
                            let retrieveMethold = this[`${dependencyType}Retrieve`];

                            // Add event change on dependencyEl
                            dependencyEl.addEventListener(
                                "change",
                                (e:Event):void => {

                                    // Get current element
                                    let currentTarget = e.currentTarget;

                                    // Check if select or input
                                    if(currentTarget instanceof HTMLSelectElement || currentTarget instanceof HTMLInputElement){

                                        // Retrieve value of the current target
                                        let result:null|Array<any> = retrieveMethold(currentTarget);

                                        // Check if already disabled
                                        if(
                                            inputEl.disabled &&
                                            inputEl.hasAttribute("disabled") && 
                                            inputEl.getAttribute("disabled") != "depends"
                                        ){

                                            // Stop
                                            return;

                                        }else
                                        // Check if result is null
                                        if(
                                            result === null
                                        ){

                                            // Remove disabled
                                            if(inputEl.disabled){

                                                // Remove disabled
                                                inputEl.disabled = false;

                                                // Remove attribute
                                                inputEl.removeAttribute("disabled");

                                            }

                                            // Stop
                                            return;

                                        }

                                        // Check result is false
                                        if(
                                            result.length !== 2 ||
                                            // Case checkbox
                                            (
                                                inputType === "checkbox" &&
                                                UtilityBoolean.check(result[1]) == false
                                            )
                                            // ...
                                        ){

                                            // Disable input El
                                            inputEl.disabled = true;

                                            // Add value to attribute
                                            inputEl.setAttribute("disabled", "depends")

                                            // Unchecked check box
                                            inputEl instanceof HTMLInputElement && inputType === "checkbox" && (inputEl.checked = false);

                                        }else{

                                            // Disable input El
                                            inputEl.disabled = false;
                                            
                                        }

                                    }

                                }
                            )

                        }

                    }

                }

            }

        }else */
        // Check dependencies
        if(dependencies.length > 0){

            // Get type of input el
            let inputType = inputEl.dataset.type
                ? inputEl.dataset.type
                : inputEl.type
            ;

            // Check inputType
            if(inputType){

                // Get dependencyCollections
                let dependencyCollections:{
                    el:HTMLInputElement|HTMLSelectElement,
                    type:string,
                    method:any
                }[] = [];

                // Iteration of dependencies
                for(let dependency of dependencies){

                    // Search el
                    let dependencyEl:HTMLInputElement|HTMLSelectElement|null = !dependency
                        ? null
                        : this._formEl.querySelector(`input[name="${dependency}"], select[name="${dependency}"`)
                    ;

                    // Check dependency
                    if(dependencyEl){

                        // Get type of input el
                        let dependencyType = dependencyEl.dataset.type
                            ? dependencyEl.dataset.type
                            : dependencyEl.type
                        ;

                        // Check if multiple
                        if(dependencyEl.multiple){

                            // Check if method to retrieve value is set
                            if(dependencyType && typeof this[`${dependencyType}Retrieve`] === "function"){
                        
                                // Set result
                                let retrieveMethold = this[`${dependencyType}Retrieve`];

                                // Fill dependencyCollections
                                dependencyCollections.push({
                                    el: dependencyEl,
                                    type: dependencyType,
                                    method: retrieveMethold
                                });

                            }

                        }else{

                            // Check if method to retrieve value is set
                            if(dependencyType && typeof this[`${dependencyType}RetrieveMultiple`] === "function"){
                        
                                // Set result
                                let retrieveMethold = this[`${dependencyType}RetrieveMultiple`];

                                // Fill dependencyCollections
                                dependencyCollections.push({
                                    el: dependencyEl,
                                    type: dependencyType,
                                    method: retrieveMethold
                                });

                            }

                        }

                    }

                }

                // Check dependencyCollections
                if(dependencyCollections.length){

                    // Prepare function
                    let dependenciesCheckFunction = ():void => {

                        // Set new state
                        let newState = true; 

                        // Check dependencyCollections
                        if(dependencyCollections.length) for(let dependency of dependencyCollections){

                            // Retrieve value
                            let result = dependency.method(dependency.el);

                            /**
                             * Multiple case
                             */

                            // Check value
                            if(
                                // Case checkbox
                                (
                                    dependency.type === "checkbox" &&
                                    UtilityBoolean.check(result[0][1]) == false
                                ) ||
                                // Case select
                                (
                                    dependency.type === "select" &&
                                    (result[0][1][0] ?? "") === ""
                                )
                            ){

                                // Set new state
                                newState = false;

                                // Break
                                break;

                            }

                        }

                        // Check new state
                        if(!newState){

                            // Check disabled
                            if(!inputEl.disabled || (inputEl.disabled && inputEl.getAttribute("disabled") && inputEl.getAttribute("disabled") != "")){

                                // Disable input El
                                inputEl.disabled = true;

                                // Add value to attribute
                                inputEl.setAttribute("disabled", "depends");

                                // Set data
                                inputEl.dataset.dependsDisabled = "true";

                            }

                            // Enable dependancy button
                            this._dependancySuffixEnable(inputEl);

                            // Unchecked check box
                            inputEl instanceof HTMLInputElement && inputType === "checkbox" && (inputEl.checked = false);

                            // Select
                            if((inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement) && inputType === "select" && "tomselect" in inputEl){

                                // Clear value
                                // @ts-ignore
                                inputEl.tomselect.clear();

                                // Disable tomselect
                                // @ts-ignore
                                inputEl.tomselect.disable()

                            }

                        }else{

                            // Check disabled
                            if(
                                (inputEl.disabled && inputEl.hasAttribute("disabled") && inputEl.getAttribute("disabled") != "") ||
                                (inputEl.disabled && inputEl.dataset.dependsDisabled == "true")
                            ){

                                // Disable input El
                                inputEl.disabled = false;

                                // Add value to attribute
                                inputEl.removeAttribute("disabled");

                                // Delete dataset
                                delete inputEl.dataset.dependsDisabled;

                            }

                            // Enable dependancy button
                            this._dependancySuffixDisable(inputEl);

                            // Select
                            if((inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement) && inputType === "select" && "tomselect" in inputEl){

                                // Check if not already disabled
                                if(!inputEl.disabled || (inputEl.disabled && inputEl.getAttribute("disabled") == "depends")){

                                    // Enable tom select
                                    // @ts-ignore
                                    inputEl.tomselect.enable();

                                }

                                // Check select remote
                                if(inputEl.dataset.selectRemote){

                                    // Destory tom select
                                    // @ts-ignore
                                    inputEl.tomselect.destroy();

                                    // Setup
                                    this._initSelectInput(inputEl);

                                    // Attach event
                                    // inputEl.dataset.depends && this._addDependencies(inputEl, inputEl.dataset.depends.includes(",") ? inputEl.dataset.depends.split(",") : inputEl.dataset.depends);
                                    
                                }

                            }

                        }


                    }

                    // Apply function to dependies 
                    if(dependencyCollections) for(let dependency of dependencyCollections)

                        // Apply
                        dependency.el.addEventListener(
                            "change",
                            dependenciesCheckFunction
                        );

                    // Run first time
                    dependenciesCheckFunction();

                }

            }

        }

    }

    /** Private methods | Error
     ******************************************************
     */

    /**
     * New Suffix Error Element
     * 
     * @param classContent 
     * @param textContent 
     */
    private _newSuffixErrorEl = (classContent:string = "material-icons", textContent:string = "error"):HTMLDivElement => {

        // Create icon
        let iconEl = document.createElement("i");

        // Add class on icon
        iconEl.classList.add(
            classContent 
                ? classContent
                : "material-icons"
        );

        // Add text
        iconEl.innerText = textContent
            ? textContent
            : "error"
        ;

        // Create suffix el
        let suffixEl = document.createElement("div");

        // Add class on suffix el
        suffixEl.classList.add("suffix");

        // Append icon el
        suffixEl.appendChild(iconEl);

        // Return el
        return suffixEl;

     }

    /**
     * 
     * New Supporting Text Error El
     * 
     * @param inputEl 
     * @param allInputEls 
     */
    private _newSupportingTextErrorEl = (inputEl:HTMLInputElement|HTMLSelectElement, allInputEls:NodeListOf<Element>):HTMLSpanElement => {

        // Create icon
        let spanEl = document.createElement("span");

        // Add class on suffix el
        spanEl.classList.add("supporting-text");

        // Add text
        spanEl.innerText = inputEl.dataset && "error" in inputEl.dataset && inputEl.dataset.error
            ? inputEl.dataset.error
            : "Invalid entry"
        ;

        // Return el
        return spanEl;

    }

    /** Private methods | Filter
     ******************************************************
     */

    /**
     * Ingest Filter From Query
     * 
     * @param currentTarget 
     * @returns {void}
     */
    private _ingestFilterFromQuery = (currentTarget:HTMLFormElement):void => {

        // Get id
        let currentId = currentTarget.id;

        // Check id
        if(currentId){

            // Set root
            let root = `filters.${currentId}`;

            // Get getQueryParameters 
            let currentQueryParameters = Crazyurl.getQueryParameters(root);

            // Check querys
            if(
                (currentQueryParameters[root] ?? false) && 
                typeof currentQueryParameters[root] === "object" && 
                Object.keys(currentQueryParameters[root]).length
            ){

                // Set querys
                let querys = currentQueryParameters[root];

                // Set values
                this.setValue(querys);

            }

        }

    }

    /**
     * Precess For Filter
     * 
     * @param currentTarget 
     */
    private _processForFilter = (currentTarget:HTMLFormElement):void => {

        // Set root
        let root = `filters.${currentTarget.id}`;

        // Parse root
        const parsedRoot = root
            ? root.split('.').reduce((acc, part, index) => {
                return index === 0 ? part : `${acc}[${part}]`;
            }, '')
            : ''
        ;

        // Get formdata from current target
        let formData = this.getFormData(currentTarget);

        // New search params
        let params = new URLSearchParams();

        // Iteration formdata
        formData.forEach((value, key, parent) => {

            // Build the full key, e.g. "root[key]" or "root[user.name]"
            const fullKey = parsedRoot 
                ? `${parsedRoot}[${key}]` 
                : key
            ;

            // Set value
            const strValue = value instanceof File ? value.name : value;

            if(value){

                // Append value to params
                params.append(fullKey, strValue);

            }

        });

        // Update
        Crazyurl.updateQueryParameters(params);

    }

    /** Private methods | Dependancy Suffix
     ******************************************************
     */

    /**
     * Dependancy Suffix Enable
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _dependancySuffixEnable = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

        // Search parent input-field
        let parentEl = inputEl.closest(".input-field");

        // check parent el
        let dependencySuffixEl = parentEl?.querySelector("#dependency");

        // Check dependencySuffixEl
        dependencySuffixEl && dependencySuffixEl.classList.remove("hide");

    }

    /**
     * Dependancy Suffix Disable
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _dependancySuffixDisable = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

        // Search parent input-field
        let parentEl = inputEl.closest(".input-field");

        // check parent el
        let dependencySuffixEl = parentEl?.querySelector("#dependency");

        // Check dependencySuffixEl
        dependencySuffixEl && dependencySuffixEl.classList.add("hide");

    }

    /**
     * Process Query Params
     * 
     * @param query 
     * @returns {Record<string,string>}
     */
    private _processQueryParams = (query:Record<string,string>, formEl:HTMLFormElement|null = null):Record<string,string> => {

        // Iteration query
        if(formEl) for(let key in query) if(typeof query[key] === "string" && query[key]){

            // Search string between "{{" "}}"
            const matches = [...query[key].matchAll(/\{\{(.*?)\}\}/g)];

            // Check matched
            if(matches.length) for(let item of matches){

                // Let valueToFound 
                let valueToFound = item[1];

                // Let value to replace
                let valueToReplace = item[0];

                // Seatch in form
                let itemEl = formEl.querySelector(`[name="${valueToFound}"]`);

                // Set result
                let result = "";

                // Check item
                if(itemEl instanceof HTMLSelectElement || itemEl instanceof HTMLInputElement){

                    // Get type
                    let type = itemEl.dataset.type 
                        ? itemEl.dataset.type
                        : itemEl.type
                    ;

                    // Prepare Retrieve method
                    if(type && typeof this[`${type}Retrieve`] === "function"){

                        // Retrieve value of the current target
                        let resultForm:null|Array<any> = this[`${type}Retrieve`](itemEl);

                        // Set result
                        result = Array.isArray(resultForm) 
                            ? resultForm[1]
                            : ""
                        ;

                    }

                }

                // Set result
                query[key] = query[key].replace(valueToReplace, result);

            }

        }

        // Return result
        return query;

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Is Html Form Element
     * 
     * Check is element is Html Form Element
     * 
     * @param element 
     * @returns 
     */
    public static isHTMLFormElement = (element: Element):element is HTMLFormElement => {
        return element instanceof HTMLFormElement;
    }

}
    
/** Interface
 ******************************************************
 */

/**
 * Form On Change Options
 */
export interface formOnChangeResult {
    formEl:HTMLFormElement,
    itemEl:HTMLInputElement|HTMLSelectElement,
    formData:FormData,
    type:formOnChangeOptions["eventType"],
    valid:boolean
}

/**
 * Form On Change Options
 */
export interface formOnChangeOptions {
    eventType:"change"|"input",
}

/**
 * Form On Submit Result
 */
export interface formOnSubmitResult {
    formEl:HTMLFormElement,
    formData:FormData,
    type: "submit"
}

/**
 * Form On Reset Result
 */
export interface formOnResetResult {
    formEl:HTMLFormElement,
    formData:FormData,
    type: "reset"
}

/**
 * Form On Reset Result
 */
export interface formFilePondValue {
    source:string,
    options:{
        type: "local",
        file: {
            name: string,
            size?: number,
            type: string
        }
    }
}