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
import { TomSettings, RecursivePartial } from 'tom-select/dist/types/types';
import {default as PageError} from './../Error/Page';
import {default as UtilityStrings} from './Strings';
import Crazyrequest from '../Crazyrequest';
import Pickr from '@simonwep/pickr';
import TomSelect from 'tom-select';
import { MaskInput } from "maska"
import Objects from './Objects';
import Root from '../Dom/Root';

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

    /** Construct
     ******************************************************
     */

    constructor(form:string|HTMLFormElement, options:Partial<FormOptions> = {}){

        // Ingest options
        this._options = options;

        // Scan current form
        this._ingestForm(form)
            .then(
                this._initForm
            ).then(
                this._initOnReady
            ).then(
                this._initEventOnSubmit
            ).then(
                this._initEventOnReset
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

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Get result
                itemResult = this.extractKeyValue(items[i] as HTMLElement);

                // Check itemResult
                if(itemResult !== null && itemResult[0] !== "")

                    // Push value of current input
                    result.append(itemResult[0] as string, itemResult[1]);

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
        let target:HTMLElement = e.target as HTMLElement;

        // Get formdata
        let formData:FormData = this.getFormData(target);

        // Get entity
        let entity:Attr|null = target.attributes.getNamedItem("entity");

        // Get value_id
        let valueID:Attr|null = target.attributes.getNamedItem("value_id");

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
                        window.Crazyobject.alert.parseErrors(value.errors as CrazyError|CrazyError[], {
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
            let formEl = e.currentTarget;

            // Get value_id
            let valueID:Attr|null = formEl.attributes.getNamedItem("value_id");

            // Get value_id
            let entity:Attr|null = formEl.attributes.getNamedItem("entity");

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
     * @return Promise<any>
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
     * @return Promise<any>
     */
    private _onSubmitUpdate = async (entityValue:string, valueID:string, formData:FormData):Promise<any> => {

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
     * @return Promise<any>
     */
    private _onSubmiDelete = async (entityValue:string, valueID:string):Promise<any> => {

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

                    console.log(items[i]);

                    // Set Read only
                    items[i].removeAttribute("disabled");

                }else{

                    if(items[i].tagName == "BUTTON")

                        console.log(items[i].tagName == "BUTTON" && items[i].hasAttribute("disabled") && items[i].getAttribute("disabled") == "loading");

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

                    // Get init method name
                    let initMethodName:string = `_init${UtilityStrings.ucfirst(inputType.toLowerCase())}Input`;

                    // Check initMethodName in this 
                    if(initMethodName in this){

                        // Run method
                        this[initMethodName](inputEl);

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

                    // Check error
                    if("errors" in value && Array.isArray(value.errors) && value.errors.length){

                        alert("error");
                    }
                    
                    // Unlock
                    this.unlock();

                    // Check result
                    if("results" in value && value.results.length)

                        // Set values
                        this.setValue(value.results[0], value.results[0]._id);

                })
            ;

        }else
        // Check if is
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

                    // Check error
                    if("errors" in value && Array.isArray(value.errors) && value.errors.length){

                        alert("error");

                    }
                    
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
        )

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
    
                console.log(e);
    
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
     * Retrieve Select
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

            // Push in result
            result = [key, value];

        }

        // Return result
        return result;

    }

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
     * Number Date
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
     * Password Text
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
        if(itemEl.tagName == "INPUT"){

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
        if(itemEl.tagName == "INPUT"){

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
        if(itemEl.tagName == "INPUT"){

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

        console.log("toto");

        // Check itemEl 
        if(["INPUT", "SELECT"].includes(itemEl.tagName)){

            // Check if tomselect in item
            if("tomselect" in itemEl && itemEl.tomselect instanceof TomSelect){

                // Set value
                itemEl.tomselect.setValue(value);

                // Set id
                this._setID(valuesID, itemEl);

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
        if(["INPUT", "SELECT"].includes(itemEl.tagName)){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            this._setID(valuesID, itemEl);

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
    private _initColorInput = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

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
    private _initNumberInput = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

        // Check maska
        if(inputEl instanceof HTMLInputElement && inputEl.dataset && "maska" in inputEl.dataset){

            // Init maska
            new MaskInput(inputEl);

        }else
        // check if decimal
        if(inputEl instanceof HTMLInputElement && inputEl.hasAttribute("step") && [0.01].includes(Number(inputEl.getAttribute("step")))){
            
            // Mask input
            new MaskInput(inputEl, {
                number: {
                    fraction: 2,
                },
            });

        }

    }

    /**
     * Init Password Input
     * 
     * @param inputEl 
     * @returns {void}
     */
    private _initPasswordInput = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

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
    private _initSelectInput = (inputEl:HTMLSelectElement|HTMLInputElement):void => {

        // Check maska
        if(inputEl instanceof HTMLInputElement || inputEl instanceof HTMLSelectElement){

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

                // Set label
                option.labelField = remoteData.label;

                // Set search
                option.searchField = remoteData.search;

                // option.allowEmptyOption = true;

                // Set load
                option.load = (selectQuery, callback) => {

                    // New query
                    let query = new Crazyrequest(
                        remoteData.url,
                        {
                            method: "get",
                            cache: false,
                            responseType: "json",
                            from: "internal"
                        }
                    );

                    // Rerurn result
                    query.fetch(
                        
                        // Fetch all
                        
                    ).then(
                        value => {
                            
                            // Check value results
                            if(
                                "results" in value && 
                                Array.isArray(value.results) && 
                                value.results.length
                            )

                                // Iteration value
                                for(let key in value.results)

                                    // Set key
                                    value.results[key] = Objects.flatten(value.results[key], "", ".");

                            // Callback with value retrieve
                            callback(value.results);

                        }
                    );

                };

                // Prepare add option
                addOption = () => {

                    // New query
                    let query = new Crazyrequest(
                        remoteData.url,
                        {
                            method: "get",
                            cache: false,
                            responseType: "json",
                            from: "internal"
                        }
                    );

                    // Rerurn result
                    query.fetch(

                        // Fetch all

                    // Add options found
                    ).then(
                        value => {

                            // Check value results
                            if(
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
                    );

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