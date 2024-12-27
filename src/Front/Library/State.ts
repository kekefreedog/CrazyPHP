/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { createStore, Store } from 'killa';

/**
 * State
 *
 * Methods for store and manage your app state using new approach
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class State {

    /** Public parameters
     ******************************************************
     */

    /** @var defaultSchema */
    public static defaultSchema:Record<string,any>|any[] = {
        "_page": {},
        "_global": {}
    };

    /** Private parameters
     ******************************************************
     */

    /** @var _instance */
    private static _instance:State|null = null;

    /** @var _store:Store<StatePage> */
    private _store:Store<StatePage>;

    /** @var dynamicMethods:Set<string> dynamic methods */
    private _dynamicMethods:Set<string>;

    /** @var _trigger:Set<string> dynamic methods */
    private static _trigger:"set"|"get"|"delete" = "get";

    /**
     * Constructor
     */
    private constructor() {
      
        // Initialize the store with the default state
        this._store = createStore<StatePage>({});
        
    }

    /** Public methods | Get or Set
     ******************************************************
     */

    /**
     * Get
     * 
     * Get the singleton instance of the store with trigger switch to get
     * 
     * @returns {State}
     */
    public static get = ():State => {
      
        // Check instance
        if(!State._instance){

            // New _instance
            State._instance = new State();

            // Set default schema
            State._instance._store.setState((state:Record<string,any>|any[]) => State.defaultSchema)

        }

        // Set trigger
        State._trigger = "get";

        // Return instance 
        return State._instance;

    }

    /**
     * Set
     * 
     * Set the singleton instance of the store with trigger switch to set
     * 
     * @returns {State}
     */
    public static set = ():State => {
      
        // Check instance
        if(!State._instance){

            // New _instance
            State._instance = new State();

            // Set default schema
            State._instance._store.setState((state:Record<string,any>|any[]) => State.defaultSchema)

        }

        // Set trigger
        State._trigger = "set";

        // Return instance 
        return State._instance;

    }

    /**
     * Delete
     * 
     * Set the singleton instance of the store with trigger switch to set
     * 
     * @returns {State}
     */
    public static delete = ():State => {
      
        // Check instance
        if(!State._instance){

            // New _instance
            State._instance = new State();

            // Set default schema
            State._instance._store.setState((state:Record<string,any>|any[]) => State.defaultSchema)

        }

        // Set trigger
        State._trigger = "delete";

        // Return instance 
        return State._instance;

    }

    /** Public methods | Store
     ******************************************************
     */

    /**
     * store
     * 
     * Get the raw Killa store
     * 
     * @returns {Store<StatePage>}
     */
    public store = ():Store<StatePage> => this._store;

    /** Public methods | Data
     ******************************************************
     */

    /**
     * Data
     * 
     * Get or set data
     * 
     * @param key 
     * @param value 
     * @returns {any}
     */
    public data = (key:string, value?:any):any => {

        // Set result
        let result:any = null;

        // Check if get
        if(State._trigger === "set" && typeof value !== "undefined"){

            // Set state
            this._store.setState((state:Record<string,any>|any[]) => (this._setValueByKeyPath(state, key, value)));

        }else
        // If get
        if(State._trigger === "get"){
        
            // Return data
            result = this._getValueByKeyPath(this._store.getState().data, key);
  
        }else
        // If delete
        if(State._trigger === "delete"){

            this._store.setState((state:Record<string,any>|any[]) => (this._removeValueByKeyPath(state, key)))

        }

        // Return result
        return result;

    }

    /**
     * Page
     * 
     * Get or set data
     * 
     * @param key 
     * @param value 
     * @returns {any}
     */
    public page = (value?:any, page?:string):any => {

        // Declare result
        let result:any = null;

        // Check page
        if(!page)

            // Set page
            page = window.Crazyobject.currentPage.get()?.name as string;

        // Check page
        if(!page)

            // Stop
            return result;

        // Set key
        let key = `_page.${page}`;

        // Check if get
        if(State._trigger === "set" && typeof value !== "undefined"){

            // Set state
            this._store.setState((state:Record<string,any>|any[]) => (this._setValueByKeyPath(state, key, value)));

        }else
        // If get
        if(State._trigger === "get"){
        
            // Return data
            result = this._getValueByKeyPath(this._store.getState(), key);
  
        }else
        // If delete
        if(State._trigger === "delete"){

            // Delete all
            this._store.setState((state:Record<string,any>|any[]) => (this._removeValueByKeyPath(state, key)))

        }

        // Return result
        return result;

    }

    /**
     * Get All Data
     * 
     * Retrieve all data
     * 
     * @returns {Record<string,any>}
     */
    public all = ():Record<string,any> => {

        // Declare result
        let result:any = null;

        // Check if get
        if(State._trigger === "set"){

            // Set state
            this._store.setState((state:Record<string,any>|any[]) => {});

        }else
        // If get
        if(State._trigger === "get"){
        
            // Return data
            result = this._store.getState();
  
        }else
        // If delete
        if(State._trigger === "delete"){

            // Delete all
            this._store.setState((state:Record<string,any>|any[]) => {});

        }

        // Return result
        return result;

    }

    /**
     * Add Method
     * 
     * Add dynamic methods
     * 
     * @param name 
     * @param method 
     */
    public method = (name:string, method?:Function):any => {

        // Declare result
        let result:any = null;

        // Check if get
        if(State._trigger === "set" && method){

            // Add method
            (this as any)[name] = method.bind(this);

            // Push into _dynamicMethods
            this._dynamicMethods.add(name);

        }else
        // If get
        if(State._trigger === "get"){
        
            // Return data
            result = (this as any)[name] as Function;
  
        }else
        // If delete
        if(State._trigger === "delete"){

            // Delete all
            delete (this as any)[name];

        }

        // Return result
        return result;

    }
    
    /**
     * Reset
     * 
     * Reset all dynamic data
     * 
     * @param removeMethods:boolean Delete methods added
     * @returns {void}
     */
    public reset = (removeMethods:boolean = true):void => {

        // Delete all
        this._store.setState((state:Record<string,any>|any[]) => {});

        // Check removeMethods 
        if(removeMethods){

            // ITeration dynamicMethods
            this._dynamicMethods.forEach((methodName) => {

                // Remove each dynamic method
                delete (this as any)[methodName];
            
            });

            // Clear dynamicMethods
            this._dynamicMethods.clear();

        }

    }

    /** Private methods | Utilities
     ******************************************************
     */

    /**
     * Get Value By Key Path
     * 
     * @param data 
     * @param key 
     * @param separator 
     * @returns {any}
     */
    private _getValueByKeyPath = (data:any, key:string, separator:string = "."):any => {
        
        // Split the keyPath into an array of keys
        const keys = key.split(separator ? separator : ".");
        
        // Use a single loop to traverse the structure
        for(const k of keys){

            // Check data
            if(data == null || typeof data !== 'object')

                // If current data is not navigable, return null
                return null; 
    
            // Attempt to access the key
            data = data[k];
            
        }
    
        // Return value if found, else null
        return data !== undefined 
            ? data
            : null
        ;
    }

    /**
     * Set Value By Key Path
     * 
     * @param data 
     * @param keyPath 
     * @param value 
     * @param separator
     * @returns {any}
     */
    private _setValueByKeyPath = (data:Record<string,any>|any[], key:string, value:any, separator:string = "."):any => {

        // Get keys
        const keys = key.split(separator);

        // Extract the last key
        const lastKey = keys.pop(); 
    
        // If no keys are provided
        if(!lastKey) 
            
            // Return false
            return data; 
    
        // Set current
        let current:any = data;
    
        // Iteration of keys
        for(const k of keys){

            // Check current
            if(current[k] == null || typeof current[k] !== 'object')

                // If the key does not exist or is not an object, initialize it as an empty object
                current[k] = isNaN(Number(keys[0])) 
                    ? {} 
                    : []
                ;

            // Navigate deeper
            current = current[k];

        }
    
        // Set the value at the final key
        current[lastKey] = value;
    
        // Indicate successful set
        return data;

    }

    /**
     * Remove Value By Key Path
     * 
     * @param data 
     * @param key 
     * @param separator 
     * @returns {any}
     */
    private _removeValueByKeyPath = (data:Record<string,any>|any[], key:string, separator:string = "."):any => {

        // Set keys
        const keys = key.split(separator);

        // Extract the last key
        const lastKey = keys.pop();
    
        // If no keys are provided
        if(!lastKey) 
            
            // Return false
            return false;
    
        // Set current
        let current:any = data;
    
        // Iteration of keys
        for(const k of keys){

            // Check current
            if(current == null || typeof current !== 'object')

                // If the path is invalid, return false
                return false;
    
            // Navigate deeper
            current = current[k];

        }
    
        // Check if last key
        if(current && lastKey in current)

            // If the final key exists, delete it
            if(Array.isArray(current)){

                // For arrays, use splice to remove the element if the key is a valid index
                const index = parseInt(lastKey, 10);

                // Splice value
                if(!isNaN(index) && index >= 0 && index < current.length){

                    // Splice
                    current.splice(index, 1);

                    // Return trye
                    return data;

                }

            }else
            // For objects, use delete to remove the key
            {
            
                // Delete
                delete current[lastKey];

                // Return true
                return data;
            }
        
    
        // Key not found, return false
        return data; 

    }

}