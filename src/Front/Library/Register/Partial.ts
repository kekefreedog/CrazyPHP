/**
 * Register
 *
 * Front TS Scrips for register elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Crazypartial from '../Crazypartial';

/**
 * Crazy Page Loader
 *
 * Methods for register a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Register {

    /** Private parameters
     ******************************************************
     */

    /** @var _collection Collection of partials */
    private _collection:Object = {};

    /**
     * Constructor
     */
    public constructor(){
    
    }

    /** Public methods
     ******************************************************
     */

    /**
     * Register
     * 
     * Register on or multiple partials
     * 
     * @param inputs Inputs to register
     * @returns void
     */
    public register = (inputs:Object|null = null):void  => {

        // Check object length
        if(inputs && Object.keys(inputs).length)

            // Iteration of input
            for(let input in inputs)

                // Check if value callable
                if(inputs[input].prototype instanceof Crazypartial)

                    // Push in collection
                    this._collection[input] = inputs[input];

    }

    /**
     * Get
     * 
     * Get partial class by name
     * 
     * @param name Name of the partial
     * @returns null|Crazypartial
     */
    public get = (name:string):null|Crazypartial => {

        // Set result
        let result:null|Crazypartial = null;

        // Check name
        if(name && Object.keys(this._collection).length && name in this._collection)

            // Set result
            result = this._collection[name];

        // Return result
        return result;

    }

    /**
     * Get All
     * 
     * Get All Partial
     * 
     * @returns null|Object
     */
    public getAll = ():Object => {

        // Set result
        let result = this._collection;

        // Return result
        return result;

    }

    /**
     * Scan
     * 
     * Scan all partial on dom
     * 
     * @param parent Parent where search
     * @returns Arrays
     */
    /** */
    public scan = (parent:string|Element[]|Element):Array<RegisterPartialScanned> => {

        // Set result
        let result:Array<RegisterPartialScanned> = [];
        let partialValue:string|null;
        let tempValue:null|Crazypartial;
        let tempResult:RegisterPartialScanned;

        // Declare parent
        let parentEls:Element[] = [];

        // Check parent is list of element
        if(parent instanceof Element){

            // Set parent els
            parentEls.push(parent);

        }else
        // Check parent element[]
        if(Array.isArray(parent)){

            // Check parent empty
            if(parent.length)

                // Iteration of element
                for(let item of parent)

                    // Check instance of parent
                    if(item instanceof Element)

                        // Push into parentEls
                        parentEls.push(item);

        }else 
        // Check is string
        if(typeof parent === "string")
        {

            // Get els
            let items = document.querySelectorAll(parent)

            // Get parent
            parentEls = Array.from(items);

        }

        // Check result
        if(parentEls.length)

            // Iterations parents
            for(let parentEl of parentEls){

                // Search elements with partial
                let resultEls = parentEl.querySelectorAll("[partial]");
                
                // Check result
                if(resultEls.length)

                    // Iteration results
                    resultEls.forEach(resultEl => {

                        // Get attributes
                        partialValue = resultEl.getAttribute("partial");

                        // Check partial value
                        if(partialValue){

                            // Get temp result object previously ingested in this instance of in the global instance
                            tempValue = this.get(partialValue) || window.Crazyobject.partials.get(partialValue);

                            // Check temp value
                            if(tempValue){

                                // Result temp result
                                tempResult = {
                                    name: partialValue,
                                    target: resultEl,
                                    callable: tempValue,
                                    id: Date.now() + Math.floor(Math.random() * 1000)
                                };

                                // Push temp result in result
                                result.push(tempResult);

                            }

                        }

                    });

            };

        // Return result
        return result;

    }

}