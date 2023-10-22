/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Crazyrequest from '../Crazyrequest';
import Root from '../Dom/Root';
import Arrays from './Arrays';
import Page from '../Loader/Page';

/**
 * Form
 *
 * Methods for retrieve value from form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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
                this._initOnReady
            ).then(
                this._initEventOnSubmit
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

        // Check entity
        if(entity !== null){

            // Get entity
            let entityValue:string = entity.value;

            // Prepare request
            let request = new Crazyrequest(`/api/v2/${entityValue}/create`, {
                method: "POST",
                header:{
                    'Cache-Control': 'no-cache, no-store, must-revalidate'
                },
                cache: false
            });

            // Run request
            request.fetch(formData).then(
                v => {

                    // Unlock target
                    this.unlock();

                }
            );

        }

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
        let items = this._formEl.querySelectorAll("select, input, button[type=\"submit\"]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check tag name
                if(items[i].tagName == "INPUT"){

                    // Set Read only
                    items[i].setAttribute("readonly", "");

                }else
                // Chech button
                if(items[i].tagName == "BUTTON"){

                    // Set Read only
                    items[i].setAttribute("disabled", "");

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
        let items = this._formEl.querySelectorAll("select, input, button[type=\"submit\"]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check tag name
                if(items[i].tagName == "INPUT" && items[i].hasAttribute("readonly")){

                    // Set Read only
                    items[i].removeAttribute("readonly");

                }else
                // Check tag name
                if(items[i].tagName == "INPUT" && items[i].hasAttribute("disabled")){

                    // Set Read only
                    items[i].removeAttribute("disabled");

                }else
                // Chech button
                if(items[i].tagName == "BUTTON" && items[i].hasAttribute("disabled")){

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
     * Init On Ready
     * 
     * Check action to accompish before loading
     * 
     * @return Promise<void>
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

                    // Set values
                    this.setValue(value.results[0], value.results[0]._id);

                })
            ;

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