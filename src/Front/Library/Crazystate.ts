/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazylanguage from "./Crazylanguage";
import * as localforage from "localforage";

/**
 * Crazy State
 *
 * Methods for store and manage your glable, system, local and component states
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Crazystate {

    /** Private parameters | Store
     ******************************************************
     */

    /** @var globalStore:LocalForage|null */
    public globalStore:LocalForage|null = null;

    /** @var systemStore:LocalForage|null */
    public systemStore:LocalForage|null = null;

    /** @var localStore:LocalForage|null */
    public localStore:LocalForage|null = null;

    /** @var componentStore:LocalForage|null */
    public componentStore:LocalForage|null = null;

    /** Private parameters | Database
     ******************************************************
     */

    /** Database Name */
    databaseName:string = "crazyFrontDatabase"; 

    /**
     * Constructor
     * 
     * @param options:LoaderPageOptions Options with all page details
     */
    public constructor(input:CrazyObjectInput){

        // Create global store
        this.globalStore = localforage.createInstance({
            name        : this.databaseName,
            storeName   : 'globalStore',
            description : 'Global state store'
        });

        // Create system store
        this.systemStore = localforage.createInstance({
            name        : this.databaseName,
            storeName   : 'systemStore',
            description : 'System state store'
        });

        // Create local store
        this.localStore = localforage.createInstance({
            name        : this.databaseName,
            storeName   : 'localStore',
            description : 'Local state store'
        });

        // Create local store
        this.componentStore = localforage.createInstance({
            name        : this.databaseName,
            storeName   : 'componentStore',
            description : 'Component state store'
        });

        // Set global state
        this.globalStore
            .ready()
            .then(
                ()  =>  (
                    this._setGlobalState(input)
                )
            );

        // Set system
        this.systemStore
            .ready()
            .then(
                ()  =>  this._setSystemState()
            );

    }

    /** Public methods | Global
     ******************************************************
     */

    /**
     * Set Global State
     * 
     * @param input:CrazyObjectInput
     * @return Promise<void>
     */
    private async _setGlobalState(input:CrazyObjectInput):Promise<void> {

        // Check input
        if(input.globalStateCollection && Object.keys(input.globalStateCollection).length)

            // Iteration of values
            for(let key in input.globalStateCollection)

                // Set value
                await this.setItemIfNot(key, input.globalStateCollection[key], this.globalStore);

    }

    /** Public methods | System
     ******************************************************
     */

    /**
     * Set System State
     * 
     * @return void
     */
    private async _setSystemState():Promise<void> {

    }

    /** Public methods | Local
     ******************************************************
     */

    /** Public methods | Dynamic
     ******************************************************
     */

    /** Public methods
     ******************************************************
     */

    /**
     * Set Item If Not
     */
    public async setItemIfNot(key: string, value: string, store:LocalForage|null = this.globalStore): Promise<any> {

        // Try
        try {

            // Ensure store is ready
            await store?.ready();
    
            // Get current value
            const currentValue = await store?.getItem(key);
    
            // If currentValue is not present, set the new value
            if (!currentValue) 

                // Return set value
                return await store?.setItem(key, value);
    
            // If currentValue exists, return it
            return currentValue;
            
        } catch (error) {

            console.error("Error in setItemIfNot:", error);
            throw error;

        }
    }
        
}