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

    /** Construct
     ******************************************************
     */

    constructor(){

        // Scan current form
        this.scan();

    }

    /** Public method
     ******************************************************
     */

    /**
     * Scan
     * 
     * Scan form in crazy root
     * 
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
     * @param formName 
     * @param values 
     */
    public setValue = (formName:string, values:Object, valuesID:string|Object|null = null):void => {

        // Declare var
        let currentName:string;
        let currentType:string;

        // Check formname
        if(!formName || Object.keys(values).length)

            // Return result
            return;

        // Search form
        let searchEl = document.querySelector(`form#${formName}`);

        // Check search
        if(searchEl === null)

            // Return result
            return;

        // Get all select and input on form el
        let items = searchEl.querySelectorAll("select, input");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check if name
                if("name" in items[i] && items[i]["name"]){

                    // Get name
                    currentName = items[i]["name"]/* .replace(`${formName}_`, "") */;

                    // Get type
                    currentType = items[i]["type"] ?? "";

                    // Check if in values
                    if(currentName in Object.keys(values)){

                        // Check itemEl
                        if(typeof this[`${currentType}Set`] === "function")

                            // Set result
                            this[`${currentType}Set`](items[i], values[currentName], valuesID);

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
        this.lock(target);

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
                    this.unlock(target);

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
     * @param formName 
     */
    public lock = (formName:string|HTMLElement):void => {

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

        // Get all select and input on form el
        let items = formEl.querySelectorAll("select, input, button[type=\"submit\"]");

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
     * @param formName 
     */
    public unlock = (formName:string|HTMLElement):void => {

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

        // Get all select and input on form el
        let items = formEl.querySelectorAll("select, input, button[type=\"submit\"]");

        // Check items
        if(items.length)

            // Iteration items
            for (let i = 0; i < items.length; i++){

                // Check tag name
                if(items[i].tagName == "INPUT" && items[i].hasAttribute("readonly")){

                    // Set Read only
                    items[i].removeAttribute("readonly");

                }else
                // Chech button
                if(items[i].tagName == "BUTTON" && items[i].hasAttribute("disabled")){

                    // Set Read only
                    items[i].removeAttribute("disabled");

                }


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
            itemEl["value"] = value;

            // Check values id is string
            if(typeof valuesID === "string"){

                // Set entity_id
                itemEl.setAttribute("value_id", valuesID);

            }else
            // Check value is object
            if(valuesID !== null && Object.keys(valuesID).includes(itemEl["name"])){

                // Set entity_id
                itemEl.setAttribute("value_id", valuesID[itemEl["name"]]);

            }

        }

    }

}