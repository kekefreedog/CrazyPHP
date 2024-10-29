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
import TomSelect from 'tom-select';
import Page from '../Loader/Page';
import { MaskInput } from "maska"
import Root from '../Dom/Root';
import Arrays from './Arrays';

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

    /** Construct
     ******************************************************
     */

    constructor(form:string|HTMLFormElement){

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
        let items = this._formEl.querySelectorAll("select, input");

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
     * @returns void
     */
    public resetValue = ():void => {

        // Get all select and input on form el
        let items = this._formEl.querySelectorAll("select, input");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check if name
                if("name" in items[i] && items[i]["name"] !== ""){

                    // Check if value
                    if(items[i].hasAttribute("value")){

                        // Reset value
                        items[i].removeAttribute("value");
                        

                    console.log(items[i].removeAttribute("value"));

                    }

                    // Check if value_id
                    if(items[i].hasAttribute("value_id"))

                        // Reset value
                        items[i].removeAttribute("value_id");

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
        let items = formEl.querySelectorAll("select, input");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Get result
                itemResult = this.extractKeyValue(items[i] as HTMLElement);

                // Check itemResult
                if(itemResult !== null)

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

        // Lock form
        this.lock();

        // Get entity
        let entity:Attr|null = target.attributes.getNamedItem("entity");

        // Get value_id
        let valueID:Attr|null = target.attributes.getNamedItem("value_id");

        // Check entity or value id
        if(entity !== null && valueID !== null)

            // Create item
            this._onSubmitUpdate(entity.value, valueID.value, formData)
                .then(
                    v => {

                        // Unlock target
                        this.unlock();

                    }
                );

        else
        // Check entity
        if(entity !== null){

            // Create item
            this._onSubmitCreate(entity.value, formData)
                .then(
                    value => {

                        // Check v
                        if(value.results.length)
                            
                            // Set values
                            this.setValue(value.results[0], value.results[0]._id);

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

        // Lock form
        this.lock();

        // Check form in e.target
        if(e.target !== null && "form" in e.target && e.target.form instanceof HTMLFormElement){

            // Get target
            let formEl = e.target.form;

            // Get value_id
            let valueID:Attr|null = formEl.attributes.getNamedItem("value_id");

            // Get value_id
            let entity:Attr|null = formEl.attributes.getNamedItem("entity");

            // Check valueID
            if(valueID && entity){

                this._onSubmiDelete(entity.value, valueID.value)
                    .then(v => {

                        // Reset value
                        this.resetValue();

                    }).then(v => {

                        // Retrive other value
                        this._initOnReady()

                    })

            }else{

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
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: false
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
        let items = this._formEl.querySelectorAll("select, input, button[type=\"submit\"], button[type=\"reset\"]");

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
        let items = this._formEl.querySelectorAll("select, input, button[type=\"submit\"], button[type=\"reset\"]");

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

                }else
                // Chech button
                if(items[i].tagName == "BUTTON" && items[i].hasAttribute("disabled") && items[i].getAttribute("disabled") == "loading"){

                    // Set Read only
                    items[i].removeAttribute("disabled");

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
                    
                    // Unlock
                    this.unlock();

                    // Check result
                    if(value.results.length)

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

        // Search if button reset
        let resetEls = this._formEl.querySelectorAll('button[type="reset"]');

        // Check result
        if(resetEls.length)

            // Iteration
            for(let i = 0; i < resetEls.length; i++)

                // Add event on them
                resetEls[i].addEventListener(
                    "click",
                    this.eventOnReset
                );

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


            console.log(e);

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

        // Check itemEl
        if("type" in itemEl && itemEl.type && typeof itemEl.type === "string" && typeof this[`${itemEl.type}Retrieve`] === "function")

            // Set result
            result = this[`${itemEl.type}Retrieve`](itemEl);

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

    /** Private methods | Init input
     ******************************************************
     */

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
                plugins: {}
            };

            // Check clear
            if(inputEl.dataset && "selectClear" in inputEl.dataset)

                // Set plugin
                // @ts-ignore
                option.plugins["clear_button"] = {
                    title: inputEl.dataset.selectClear
                };


            // Init maska
            let tomInstance = new TomSelect(inputEl, option);

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