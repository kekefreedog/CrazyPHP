/**
 * Map
 *
 * Front TS Scrips for manage map
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Map from "./Module/Map";
import Module from "./Module";

/**
 * Api
 *
 * Loader of events coming from api response
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Api {

    /** Private Methods
     ******************************************************
     */

    /** @param _map::Record<string, Record<string,any>> */
    private _map:Record<string, Record<string, any>>;

    /** @param _aliasClass:any[]|null */
    private _aliasClass:Record<string, CallableFunction>|null = null;

    /**
     * Constructor
     * 
     * @param module:string
     * @param methods:Record<string,any>
     * @param aliasClass:any[]|null
     */
    public constructor(mapOrMapPath:Record<string, Record<string, any>>, aliasClass?:any[]|any) {

        // Check is object
        if(typeof mapOrMapPath === 'object')

            // Set map
            this._map = mapOrMapPath;

        // Send error
        else throw new Error("Invalid map array given");

        // Check alias class
        if(aliasClass)

            // Set alias class
            this._aliasClass = aliasClass;

        // Return proxy
        return new Proxy(this, {
            get:(target, prop: string, receiver) => {

                // Keep native methods/properties working
                if (prop in target) return Reflect.get(target, prop, receiver);

                // Delegate to map
                if (prop in target._map) return target.get(prop);

                // Return undifined
                return undefined;
                
            }
        });

    }

    /**
     * Get
     * 
     * @param name 
     * @returns {Module}
     */
    public get = (name: string):Module => {

        // Check name
        if(!(name in this._map)) 

            // Send error
            throw new Error(`Module '${name}' not found`);

        // Set result
        let result = new Module(name, this._map[name], this._aliasClass);

        // Return result
        return result;
        
    }

}