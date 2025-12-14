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
import Form, { formOnChangeOptions, formOnChangeResult } from "./Form";

/**
 * Filter
 *
 * Methods for retrieve value from filter
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Filter {

    /** Public Parameters
     ******************************************************
     */

    /** @param _containerEl */
    public _containerEl:HTMLElement;

    /** @param _formEl */
    public _formEl:HTMLFormElement;

    /** @param _formInstance */
    public _formInstance:Form;

    /** @param _collection */
    public _collections:Record<string,FilterItem[]> = {};

    /** Construct
     ******************************************************
     */

    /**
     * Constructor
     * 
     */
    constructor(containerEl:HTMLElement, name:string, collections:filterItem[]){

        // Set container el
        this._containerEl = containerEl;

        // Create form
        this._createForm(name);

        // Ingest Collection
        this._ingestCollection(collections);

        // Init Form
        this._initFrom();


    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get Items
     * 
     * @returns {FilterItem[]}
     */
    public getItems = ():Record<string, FilterItem[]> => {

        // Set result
        let result = this._collections;

        // Return result
        return result;

    }

    /**
     * Get Form El
     * 
     * @returns {HTMLFormElement}
     */
    public getFormEl = ():HTMLFormElement => {

        // Set result
        let result = this._formEl;

        // Return result
        return result;

    }

    /**
     * Get Form Instance
     * 
     * @returns {Form}
     */
    public getFormInstance = ():Form => {

        // Set result
        let result = this._formInstance;

        // Return result
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

        // Set on change on form instance
        this._formInstance.setOnChange(callable, options);

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Ingest Collection
     * 
     * @param collections:filterItem[]
     * @returns {void}
     */
    private _ingestCollection = (collections:filterItem[]):void => {

        // Iteration collections
        if(collections.length) for(let collection of collections) if(collection.name && collection.el){

            // Get el
            let el = collection.el instanceof HTMLElement 
                ? collection.el
                : this._containerEl.querySelector(collection.el)
            ;

            // Check el
            if(el instanceof HTMLElement){

                // Check if collection name
                if(!(collection.name in this._collections)) this._collections[collection.name] = [];

                // Create hidden input
                let inputEl = this._createHiddenInput(collection.name);

                // Set collection
                this._collections[collection.name].push(new FilterItem(el, collection, this, inputEl))

            }

        }

    }

    /**
     * Create Form
     * 
     * @param name
     * @returns {void}
     */
    private _createForm = (name:string):void => {

        // Create form
        let fromEl = document.createElement("form");

        // Set id
        fromEl.id = name;

        // Set form el
        fromEl.dataset.formFilter = "";

        // Append
        this._formEl = this._containerEl.appendChild(fromEl);

    }

    /**
     * Create Hidden Input
     * 
     * @param name
     * @returns {HTMLInputElement}
     */
    private _createHiddenInput = (name:string):HTMLInputElement => {

        // Set result
        let result:HTMLInputElement;

        // Search with name
        let inputEl = this._formEl.querySelector(`[name="${name}"]`);

        // Check result
        if(inputEl instanceof HTMLInputElement){

            // Set result
            result = inputEl;
        
        }else{

            // Set result
            result = document.createElement("input");

            // Set type
            result.type = "hidden";

            // Set name
            result.name = name;

            // Append
            result = this._formEl.appendChild(result);

        }

        // Return result
        return result;

    }

    /**
     * Init Form
     * 
     * @returns {void}
     */
    private _initFrom = ():void => {

        // New form
        this._formInstance = new Form(this._formEl, {
            filter: true
        });

    }

}

/**
 * Filter Item
 *
 * Methods for retrieve value from filter item
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export class FilterItem {

    /** Public Parameters
     ******************************************************
     */

    /** @param _itemEl */
    public _itemEl:HTMLElement;

    /** @param _inputEl */
    public _inputEl:HTMLInputElement;

    /** @param _collection */
    public _collection:filterItem;

    /** @param _container */
    public _container:Filter;

    /** @param _event */
    public _event;

    /** Construct
     ******************************************************
     */

    /**
     * Constructor
     * 
     * @param el
     * @param options
     * @param containerEl
     */
    constructor(el:HTMLElement, options:filterItem, container:Filter, inputEl:HTMLInputElement){

        // Set el
        this._itemEl = el;

        // Set collection
        this._collection = options;

        // Set container
        this._container = container;

        // Set input
        this._inputEl = inputEl;

        // Init item
        this._initItem();

        // Init event
        this._initEvent();

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * @returns {any}
     */
    public get = ():any => {

        // Get current value
        let currentValue = this._inputEl.value;

        // Check get
        if(this._collection.get)

            // Init
            currentValue = this._collection.get(this, currentValue);

        // Return value
        return currentValue;

    }

    /**
     * Set
     * 
     * @returns {any}
     */
    public set = (value?:any):void => {

        // Check set
        if(typeof this._collection.set === "boolean" && this._collection.set === false)

            // Return
            return;

        // Check set
        if(this._collection.set)

            // Init
            value = this._collection.set(this, this.get(), value);

        // Prepare object
        let response = {};

        // Push value
        response[this._collection.name] = value;

        // Set value
        this._container.getFormInstance().setValue(response);

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Init Items
     * 
     * @returns {void}
     */
    private _initItem = ():void => {

        // Init
        this._collection.init && this._collection.init(this);

    }

    /**
     * Init Events
     * 
     * @returns {void}
     */
    private _initEvent = ():void => {

        // Set event
        if(this._collection.event && this._collection.set){

            // Set event
            this._event = this._itemEl.addEventListener(this._collection.event, this._eventCall, true);

        }

    }

    /**
     * Init Call
     * 
     * @returns {void}
     */
    private _eventCall = (e:Event):void => {

        // PRevent default
        e.preventDefault;

        // Get current element
        if(this._collection.set){
            
            // Set
            this.set();

        }

    }

}
    
/** Interface
 ******************************************************
 */

/**
 * Filter Item
 */
export interface filterItem {
    name:string,
    el:HTMLElement|string,
    event?:keyof HTMLElementEventMap,
    init?:(filter:FilterItem)=>void,
    set?:false|((filter:FilterItem, currentValue:any, newValue?:any)=>any),
    get?:(filter:FilterItem, currentValue:any)=>any,

}