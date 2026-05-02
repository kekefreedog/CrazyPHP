/**
 * Map
 *
 * Front TS Scrips for manage map
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

import { existsSync } from "fs";
import Map from "./Module/Map";

/**
 * Dependances
 */

/**
 * Module
 *
 * Loader of events coming from api response
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Module {

    /**
     * Constructor
     * 
     * @param module 
     * @param methods 
     * @param aliasClass 
     */
    constructor(private module:string, private methods:Record<string, any>, private aliasClass: Record<string, CallableFunction>|null = null) {

        // Return proxy to allow dynamic calls like: user.greet()
        return new Proxy(this, {

            /**
             * Get
             * 
             * @param target 
             * @param prop 
             * @param receiver 
             * @returns {any}
             */
            get:(target, prop:string|symbol, receiver):any => {

                // Keep native behavior for symbols
                if (typeof prop === 'symbol')
                    return Reflect.get(target, prop, receiver);

                // If property exists in class → return it
                if (prop in target)

                    // Return reflect
                    return Reflect.get(target, prop, receiver);

                // Otherwise → dynamic call
                return (...args: any[]) => target.call(prop, ...args);

            }
        });

    }

    /**
     * Call
     * 
     * @param method 
     * @param args 
     * @returns 
     */
    public call = (method: string, ...args: any[]): any => {

        // Check if method exist
        if (!(method in this.methods))
            throw new Error(`Method "${method}" does not exist in module "${this.module}"`);

        // Set value
        const value = this.methods[method];

        // Set result
        let result:any = null;

        // If null → default
        if (value === null)

            // Set default
            result = this._default(method, args);

        else
        // If function → execute
        if (typeof value === 'function')

            // Call value
            result = value(...args);

        else
        // If string, try static resolution
        if (typeof value === 'string' && this.aliasClass && value in this.aliasClass) {

            // Set result
            result = this.aliasClass[value](...args);

        } else
        // Otherwise → raw value

            result = value;

        // Return result
        return result;
    }

    /**
     * Default
     * 
     * @param method 
     * @param args 
     * @returns 
     */
    private _default = (method: string, args: any[]): string => {

        return `${this.module}.${method} called with ${JSON.stringify(args)}`;
    }

    /**
     * Is Static Method
     * 
     * @param cls 
     * @param method 
     * @returns 
     */
    private _isStaticMethod = (cls: any, method: string): boolean => {

        return cls && typeof cls === 'function' && typeof cls[method] === 'function';
    }

}