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
import { FormSelect } from "@materializecss/materialize";
import type { FormInputType } from './Form/FormType';
import FormType from './Form/FormType';
import {default as PageError} from './../Error/Page';
import {default as UtilityStrings} from './Strings';
import UtilityBoolean from '../Utility/Boolean';
import CheckboxType from './Form/Checkbox';
import PasswordType from './Form/Password';
import Crazyrequest from '../Crazyrequest';
import SelectType from './Form/Select';
import HiddenType from './Form/Hidden';
import NumberType from './Form/Number';
import ColorType from './Form/Color';
import EmailType from './Form/Email';
import RadioType from './Form/Radio';
import Crazyurl from '../Crazyurl';
import DateType from './Form/Date';
import TextType from './Form/Text';
import FileType from './Form/File';
import Root from '../Dom/Root';
import State from '../State';

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

    /** @var _typeRegistry */
    private _typeRegistry:Record<string, FormInputType>;

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
                this._initOptions
            ).then(
                this._initRegistery
            ).then(
                this._initForm
            ).then(
                this._initOnReady
            ).then(
                this._initFilter
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

                    // Set currentItem
                    let currentItem = items[i] as HTMLSelectElement|HTMLInputElement;

                    // Get name
                    currentName = currentItem["name"].replace("[]", "")/* .replace(`${formName}_`, "") */;

                    // Get type
                    currentType = currentItem.type ?? "";

                    // Check if data type 
                    if("type" in currentItem.dataset && currentItem.dataset.type)

                        // Override type
                        currentType = currentItem.dataset.type;

                    // Check if in values
                    if(Object.keys(values).includes(currentName)){

                        // Check type handler
                        let currentTypeHandler = this._typeRegistry[currentType];

                        // Check if filter set
                        if(this._options.filter && currentTypeHandler?.filterSet){

                            // Set result
                            currentTypeHandler.filterSet(currentItem, values[currentName], valuesID, this._formEl, this._options);

                        }else
                        // Check itemEl to use set
                        if(currentTypeHandler?.set){

                            // Set result
                            currentTypeHandler.set(currentItem, values[currentName], valuesID, this._formEl, this._options);

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

        // Set result
        let result:FormData;

        // Declare var
        let formEl:HTMLElement|null;

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

        // Build form data
        result = this._buildFormData(formEl, this._options.filter === true);

        // Return
        return result;

    }

    /**
     * Build Form Data
     *
     * @param formEl
     * @param combineFilter
     * @returns {FormData}
     */
    private _buildFormData = (formEl:HTMLElement, combineFilter:boolean):FormData => {

        // Set formdata
        let result:FormData = new FormData();

        // Iteration items
        this._iterateFormItems(formEl, (currentItem, mutliple) => {

            // Check if multiple
            if(!mutliple){

                // Get result
                let itemResult = combineFilter
                    ? this.extractFilterKeyValue(currentItem, formEl as HTMLFormElement)
                    : this.extractKeyValue(currentItem)
                ;

                // Check itemResult
                if(itemResult !== null && itemResult[0] !== "")

                    // Push value of current input
                    result.append(itemResult[0] as string, itemResult[1]);

            }else
            // If multiple
            {

                // Get result
                let itemResults = combineFilter
                    ? this.extractFilterKeyMultipleValue(currentItem, formEl as HTMLFormElement)
                    : this.extractKeyMultipleValue(currentItem)
                ;

                // Check itemResult
                if(itemResults !== null && Array.isArray(itemResults) && itemResults.length)

                    // Iteration result
                    for(let itemResult of itemResults) if(itemResult !== null && itemResult[0] !== ""){

                        // Get name
                        let name = (itemResult[0] as string).replace("[]", "");

                        // Check if multiple value
                        if(Array.isArray(itemResult[1])) for(let temp of itemResult[1])

                            // Append value
                            result.append(name, temp);

                        // If single value
                        else

                            // Push value of current input
                            result.append(name, itemResult[1]);

                    }

            }

        });

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

        // Check entity or value id for update
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
        // Check entity for new
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

        // Prepare request
        let request = new Crazyrequest(`/api/v2/${entityValue}/create`, {
            method: "POST",
            header:{
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: false
        });

        // Check submit before
        if(this._options.onBeforeSubmit){

            // Get potential update
            // @ts-ignore
            let potentielUpdate = this._options.onBeforeSubmit(entityValue, formData);

            // Check potential update
            // @ts-ignore
            if(potentielUpdate){

                // Checi if is formdata
                // @ts-ignore
                if(potentielUpdate instanceof FormData){

                    // Run request
                    // @ts-ignore
                    return request.fetch(potentielUpdate);

                }else
                // If is array
                // @ts-ignore
                if(Array.isArray(potentielUpdate)){

                    // Set result
                    let result:any = null;
                    
                    // @ts-ignore
                    for(let item of potentielUpdate) if(item instanceof FormData) {

                        // Set batch
                        let temp:any = await request.fetch(item);

                        // Check result
                        if(!result) 

                            // Set temp
                            result = temp;

                        else{

                            // If array
                            if(Array.isArray(result.results ?? false)) result.results.push(...temp.results ?? []);

                            // If object
                            else if(typeof (result.results ?? false) === "object") result = {...result.results ?? {}, ...temp.results ?? {}};

                            // If string
                            else result += temp ? `,${temp ?? ""}` : "";

                        }


                    }

                    // Return result
                    return result;

                }

            } 
            

        }

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
        if(this._options.filter){

            // Set operator
            this._initOperator(this._formEl)

            // Call event
            this._options.onFilterReady && this._options.onFilterReady(this.getFormData(this._formEl));

            // Process filter
            this._processForFilter(this._formEl);

        }

    }

    /**
     * Init Options
     * 
     * Prepare form input
     * 
     * @returns {Promise<void>}
     */
    private _initOptions = async():Promise<void> => {

        // Check if filter
        if(typeof this._formEl.dataset.formFilter === "string")

            // Set filter option
            this._options.filter = true;

    }

    /**
     * Init Registery
     * 
     * Prepare registery
     * 
     * @returns {Promise<void>}
     */
    private _initRegistery = async():Promise<void> => {

        // Registery
        this._typeRegistry = {
            text: new TextType(this._options),
            email: new EmailType(this._options),
            password: new PasswordType(this._options),
            hidden: new HiddenType(this._options),
            number: new NumberType(this._options),
            color: new ColorType(this._options),
            date: new DateType(this._options),
            select: new SelectType(this._options),
            file: new FileType(this._options),
            checkbox: new CheckboxType(this._options),
            radio: new RadioType(this._options),
        };

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

                // Check if item given is input or select and skip `.filter-operator`
                if((inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement) && !inputEl.classList.contains("filter-operator")){

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

                    // Get type handler
                    let inputTypeHandler = this._typeRegistry[inputType.toLowerCase()];

                    // Check type handler has init
                    if(inputTypeHandler?.init){

                        // Run method
                        await inputTypeHandler.init(inputEl, this._formEl, { processQueryParams: this._processQueryParams }, this._options);

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

        
        }
        // Ingest from params
        else{

            // Unlock
            this.unlock();

            // Ingest values from query/state
            this._ingestFilterFromQuery(this._formEl);

        }

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

                // Delegate to the shared handler
                this._handleChangeEvent(eventType, event.currentTarget, event.target);

            });

    }

    /**
     * Handle Change Event
     *
     * Call "change"/"input" called by a `.filter-operator`
     *
     * @param eventType
     * @param currentTarget
     * @param target
     * @returns {void}
     */
    private _handleChangeEvent = (eventType:string, currentTarget:EventTarget|null, target:EventTarget|null):void => {

        // Check filter mode : a checkbox/switch/radio's "*" operator means "omit this
        // filter" (any value passes) — the moment the user actually picks a value,
        // that intent is gone, so snap the paired operator back to "=" first. Set
        // directly (no dispatchEvent) so this doesn't recurse into another change event.
        if(
            currentTarget instanceof HTMLFormElement &&
            this._options.filter &&
            target instanceof Element
        )

            // Auto switch wildcard operator
            this._autoSwitchWildcardOperator(currentTarget, target);

        // Process Filter
        if(currentTarget instanceof HTMLFormElement && this._options.filter)

            // Process for filter
            this._processForFilter(currentTarget);

        // Check options
        if(
            this._onChangeCallable && 
            currentTarget && 
            target && 
            currentTarget instanceof HTMLFormElement && 
            ( 
                target instanceof HTMLInputElement || 
                target instanceof HTMLSelectElement 
            ) && 
            this._onChangeOptions.eventType === eventType
        )

            // Call callable
            this._onChangeCallable({
                formEl: currentTarget,
                formData: this.getFormData(currentTarget),
                itemEl: target,
                type: eventType,
                valid: this.isValid(currentTarget),
            });

    }

    /**
     * Auto Switch Wildcard Operator
     *
     * A checkbox/switch (native `<input type="checkbox">`) or radio (TomSelect-backed
     * `<select data-type="radio">`) filter's "*" operator means "omit this filter" —
     * once the user actually touches the value, that intent no longer applies, so snap
     * the paired operator select back to "=". Skips `<select data-type="select">` on
     * purpose : a plain select's "*" only ever omits the filter when its value is empty,
     * which is already handled elsewhere, and doesn't need this auto-switch.
     *
     * Sets `.value` directly (no `dispatchEvent`) so this never fires another
     * "change"/"input" event and re-enters `_handleChangeEvent`.
     *
     * @param formEl
     * @param target
     * @returns {void}
     */
    private _autoSwitchWildcardOperator = (formEl:HTMLFormElement, target:Element):void => {

        // Skip the operator select itself, and anything that isn't a checkbox/switch/radio value control
        const isCheckboxLike = target instanceof HTMLInputElement && target.type === "checkbox";
        const isRadioLike = target instanceof HTMLSelectElement && target.dataset.type === "radio";

        // Check target is a value control we care about
        if(!isCheckboxLike && !isRadioLike) return;

        // Get name (strip the "[]" multi-value suffix, if any)
        const name = (target as HTMLInputElement|HTMLSelectElement).name?.replace("[]", "");

        // Check name
        if(!name) return;

        // Get paired operator select
        const operatorEl = formEl.querySelector(`[data-operator-name="${name}"]`);

        // Check operator currently on wildcard : switch it to equal
        if(operatorEl instanceof HTMLSelectElement && operatorEl.value === "*")

            // Set directly : no dispatchEvent, so no recursive change event
            operatorEl.value = "=";

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
     * Iterate Form Items
     *
     * @param containerEl
     * @param onItem Called once per resolved item, with its "multiple" flag
     * @return void
     */
    private _iterateFormItems = (containerEl:HTMLElement, onItem:(itemEl:HTMLElement, multiple:string|boolean|null) => void):void => {

        // Get all select and input on form el
        let items = containerEl.querySelectorAll("select[name], input[name]");

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
                let mutliple = (
                    currentItem instanceof HTMLInputElement || 
                    currentItem instanceof HTMLSelectElement
                ) && currentItem.multiple
                    ? currentItem.getAttribute("multiple") ? currentItem.getAttribute("multiple") : true
                    : false
                ;

                // Call back
                onItem(currentItem as HTMLElement, mutliple);

            }

    }

    /**
     * Extract Key Value
     * 
     * @param itemEl:HTMLElement
     * @return FormObjectForFormDataAppend
     */
    private extractKeyValue = (itemEl:HTMLElement):null|Array<any> => {

        // Declare result
        let result:any = null;

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
        if(typeof type === "string" && this._typeRegistry[type])

            // Set result
            result = this._typeRegistry[type].get(itemEl, this._options);

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
        let result:any = null;

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
        if(typeof type === "string" && this._typeRegistry[type])

            // Set result
            result = this._typeRegistry[type].getMultiple(itemEl, this._options);

        // Return null
        return result;

    }

    /**
     * Extract Filter Key Value
     *
     * Same as `extractKeyValue()`, but dispatches to the type handler's `filterGet()` instead of `get()`
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @return null|Array<any>
     */
    private extractFilterKeyValue = (itemEl:HTMLElement, formEl:HTMLFormElement):null|Array<any> => {

        // Declare result
        let result:any = null;

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
        if(typeof type === "string" && this._typeRegistry[type])

            // Set result
            result = this._typeRegistry[type].filterGet(itemEl, formEl, this._options);

        // Return result
        return result;

    }

    /**
     * Extract Filter Key Multiple Value
     *
     * Same as `extractKeyMultipleValue()`, but dispatches to the type handler's `filterGetMultiple()` instead of `getMultiple()`
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @return null|Array<any>[]
     */
    private extractFilterKeyMultipleValue = (itemEl:HTMLElement, formEl:HTMLFormElement):null|[Array<any>] => {

        // Declare result
        let result:any = null;

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
        if(typeof type === "string" && this._typeRegistry[type])

            // Set result
            result = this._typeRegistry[type].filterGetMultiple(itemEl, formEl, this._options);

        // Return result
        return result;

    }

    /** Public static methods | Set value
     ******************************************************
     */

    /**
     * Set ID
     *
     * Set ID of the item of the form
     *
     * @param formEl
     * @param valueID
     * @param itemEl
     * @returns void
     */
    public static setId(formEl:HTMLFormElement, valueID:string|Object|null, itemEl:HTMLElement):void {

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
        let itemsEls = formEl.querySelectorAll("select, input");

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
        formEl.setAttribute("value_id", keysCollection.pop() as string);

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
     *
     * Per-type init wiring (_init{Type}Input: Color, Number, Date,
     * Password, Select, File) has been migrated to Form/{Type}.ts (see
     * this._typeRegistry above) - _initForm() above dispatches to them
     * directly.
     */

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
                            if(dependencyType && this._typeRegistry[dependencyType]){

                                // Set result
                                let retrieveMethold = this._typeRegistry[dependencyType].get;

                                // Fill dependencyCollections
                                dependencyCollections.push({
                                    el: dependencyEl,
                                    type: dependencyType,
                                    method: retrieveMethold
                                });

                            }

                        }else{

                            // Check if method to retrieve value is set
                            if(dependencyType && this._typeRegistry[dependencyType]){

                                // Set result
                                let retrieveMethold = this._typeRegistry[dependencyType].getMultiple;

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
                            let result = dependency.method(dependency.el, this._options);

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
                                    this._typeRegistry.select?.init?.(inputEl, this._formEl, { processQueryParams: this._processQueryParams }, this._options);

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
     * Init Operator
     * 
     * @returns {void}
     */
    private _initOperator = (currentTarget:HTMLFormElement):void => {

        // Get operator els
        let operatorEls = currentTarget.querySelectorAll(".filter-operator");

        // Iteration
        if(operatorEls.length) for(let operatorEl of operatorEls) if(operatorEl instanceof HTMLSelectElement){

            // Init select
            let formInstance = FormSelect.init(operatorEl, {});

            // Attach event
            operatorEl.addEventListener("change", (event) => {

                // Stop propagation
                event.stopPropagation();

                // Run the shared handler directly
                this._handleChangeEvent("change", currentTarget, operatorEl);

            });

        }

    }

    /**
     * Ingest Filter From Query
     *
     * @param currentTarget
     * @returns {void}
     */
    private _ingestFilterFromQuery = (currentTarget:HTMLFormElement):void => {

        // Get id
        let currentId = currentTarget.getAttribute("id");

        // Set querys
        let querys:object = {};

        // Get current pghe name
        let pageName = window.Crazyobject.currentPage.get()?.name;

        // Check page name
        if(pageName){

            // Get current partail state
            let partialState = State.get().page(pageName, `_ui.partials.forms.${currentId}.values`);

            // Check page state
            if(partialState && typeof partialState === "object" && Object.keys(partialState).length)

                // Set querys
                querys = partialState;

        }

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
                querys = {
                    ...querys,
                    ...currentQueryParameters[root]
                };

            }

        }

        // Check querys
        if(Object.keys(querys).length){

            // Strip a field's operator prefix/wrap off one raw string value
            const stripOperator = (raw:string, options:string[]):{value:string, operator:string|null} => {

                // Set result
                let value = raw;
                let operator:string|null = null;

                // Iteration options
                for(let option of options) if(value.startsWith(option)){

                    // Clean value
                    value = value.slice(option.length);

                    // For the "*" (contains) operator
                    if(option === "*" && value.endsWith("*"))

                        value = value.slice(0, -1);

                    // Set operator found
                    operator = option;

                }

                // Return result
                return {value, operator};

            };

            // Iteration of querys
            for(let name in querys) if(typeof querys[name] === "string" || Array.isArray(querys[name])){

                // Check operator
                let operatorEl = currentTarget.querySelector(`[data-operator-name="${name}"]`);

                // Get optionEls
                let optionsEls = currentTarget.querySelectorAll(`[data-operator-name="${name}"] option[value]`);

                // Check options
                if(operatorEl instanceof HTMLSelectElement && optionsEls){

                    // Set options
                    let options:string[] = [];

                    // Iteratin els
                    for(let optionEl of optionsEls) if(optionEl instanceof HTMLOptionElement && optionEl.value && !options.includes(optionEl.value))

                        // Push into options
                        options.push(optionEl.value);

                    // Check
                    if(options.length){

                        // Sort by -length
                        options.sort((a, b) => b.length - a.length);

                        // Check if multi-value
                        if(Array.isArray(querys[name])){

                            // Set raw array
                            let rawArray = querys[name] as string[];

                            // Check the array leads with its operator (see combineFilterOperatorValue's
                            // array branch / _processForFilter : "[operator, ...values]"), matched
                            // exactly rather than as a prefix since a raw value could itself start
                            // with e.g. "=" here
                            if(rawArray.length && options.includes(rawArray[0])){

                                // Set operator select's value
                                operatorEl.value = rawArray[0];

                                // Drop the leading operator, values are already raw
                                querys[name] = rawArray.slice(1);

                            }else{

                                // Legacy shape : each entry carries its own operator prefix
                                let operatorFound:string|null = null;

                                // Strip every entry, keeping track of the operator
                                querys[name] = rawArray.map((rawValue:string) => {

                                    // Strip
                                    let stripped = stripOperator(rawValue, options);

                                    // Check operator found
                                    if(stripped.operator) operatorFound = stripped.operator;

                                    // Return cleaned value
                                    return stripped.value;

                                });

                                // Set operator select's value
                                if(operatorFound) operatorEl.value = operatorFound;

                            }

                        }else{

                            // Strip
                            let stripped = stripOperator(querys[name], options);

                            // Set value
                            querys[name] = stripped.value;

                            // Set value of option
                            if(stripped.operator) operatorEl.value = stripped.operator;

                        }

                    }

                }

            }

            // Set values
            this.setValue(querys);

        }

    }

    /**
     * Precess For Filter
     *
     * @param currentTarget 
     * @returns {void}
     */
    private _processForFilter = (currentTarget:HTMLFormElement):void => {

        // Set root
        let root = `filters.${currentTarget.getAttribute("id")}`;

        // Parse root
        const parsedRoot = root
            ? root.split('.').reduce((acc, part, index) => {
                return index === 0 ? part : `${acc}[${part}]`;
            }, '')
            : ''
        ;

        // Set state result
        let stateResult:any = {};

        // Get raw (uncombined) formdata from current target
        let formData = this._buildFormData(currentTarget, false);

        // New search params
        let params = new URLSearchParams();

        // Set key already use
        let keyAlreadyUsed:string[] = [];

        // Group raw formdata entries by key first : a multi-value field (e.g. a multi-select)
        // spans several FormData entries sharing the same key — combining the operator one
        // entry at a time (the old behaviour) only ever produces a scalar "<operator>value"
        // per entry (e.g. "=paris"), never the `[operator, ...values]` array SgFilterOperation
        // expects. The full set of raw values for a key has to be combined in one shot.
        let groupedByKey = new Map<string, FormDataEntryValue[]>();

        // Iteration formdata : group
        formData.forEach((value, key) => {

            // Get (or create) this key's group
            let group = groupedByKey.get(key) ?? [];

            // Push value into group
            group.push(value);

            // Set group
            groupedByKey.set(key, group);

        });

        // Iteration grouped formdata
        groupedByKey.forEach((values, key) => {

            // Build the full key, e.g. "root[key]" or "root[user.name]"
            let fullKey = parsedRoot
                ? `${parsedRoot}[${key}]`
                : key
            ;

            // Set raw values (File -> its name)
            let rawValues = values.map(value => value instanceof File ? value.name : value);

            // Combine operator with value
            let itemEl =
                currentTarget.querySelector(`[name="${key}"]`) ??
                currentTarget.querySelector(`[name="${key}[]"]`)
            ;

            // Set multiple
            let multiple = (
                itemEl instanceof HTMLInputElement ||
                itemEl instanceof HTMLSelectElement
            ) && itemEl.multiple;

            // Declare combined value(s)
            let combined:any = multiple ? rawValues : rawValues[0];

            // Check item el
            if(itemEl instanceof HTMLElement){

                // Check multiple
                if(multiple){

                    // Combine the full set of raw values at once, so the operator can lead the array
                    combined = FormType.combineFilterOperatorValue(
                        FormType.getFilterOperatorValue(currentTarget, key),
                        rawValues,
                        key,
                        this._options
                    );

                }else{

                    // Get filter result
                    let filterResult = this.extractFilterKeyValue(itemEl, currentTarget);

                    // Check result
                    if(filterResult)

                        // Set value
                        combined = filterResult[1];

                }

            }

            // Check value and key
            if(rawValues.length && rawValues.some(rawValue => rawValue !== "") && fullKey){

                // Check if multiple : append every element of the combined array under its own indexed key
                if(multiple || fullKey.endsWith("[]]")){

                    // Set combined array (combineFilterOperatorValue may return a scalar when there's nothing to combine)
                    let combinedArray = Array.isArray(combined) ? combined : [combined];

                    // Iteration combined array
                    for(let entryValue of combinedArray){

                        // Set i
                        let i = 0;

                        // Iteration number until not already used
                        while(keyAlreadyUsed.includes(`${fullKey}[${i}]`))

                            // Increment i
                            i++;

                        // Set indexed key
                        let indexedKey = `${fullKey}[${i}]`;

                        // Append key into keyAlreadyUsed
                        keyAlreadyUsed.push(indexedKey);

                        // Append value to params
                        params.append(indexedKey, entryValue);

                    }

                }else{

                    // Append value to params
                    params.append(fullKey, combined);

                }

                // Push value : raw (uncombined) values, one per key
                stateResult[key] = multiple ? rawValues : rawValues[0];

            }

        });

        // Update
        Crazyurl.updateQueryParameters(params);

        // Get current pghe name
        let pageName = window.Crazyobject.currentPage.get()?.name;

        // Check state — read via getAttribute(), not `.id` (see _ingestFilterFromQuery)
        if(pageName && State.get().page(pageName, `_ui.partials.forms.${currentTarget.getAttribute("id")}.values`)){

            // Push values
            State.set().page(`${pageName}._ui.partials.forms.${currentTarget.getAttribute("id")}.values`, stateResult);

        }

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
                    if(type && this._typeRegistry[type]){

                        // Retrieve value of the current target
                        let resultForm:null|Array<any> = this._typeRegistry[type].get(itemEl, this._options);

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