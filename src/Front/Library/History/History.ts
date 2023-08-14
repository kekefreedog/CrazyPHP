/**
 * History
 *
 * Front TS Scrips for manage history
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';
import Arrays from '../Utility/Arrays';

/**
 * Crazy Page History
 *
 * Methods for manage simple history
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class History {

    /** Private parameters
     ******************************************************
     */

    /** @param _past Past collection */
    private _previous:Array<HistoryItem> = [];

    /** @var _limit Limit of previous values stored */
    private _limit:number = 50;

    /** Public methods
     ******************************************************
     */

    /**
     * Set
     * 
     * Set value in history
     * 
     * @param value 
     */
    public set = (value:any):any => {

        // Prepare nez history item
        let item:HistoryItem = {
            value: value,
            dateCreated: new Date(),
            type: typeof value
        };

        // Push into previous array
        this._previous.push(item);

        // Check Limit
        this._checkLimit();


    }

    public get = (key:number = 1):any => {

        // Let result
        let result:any = undefined;

        // Check key
        if(key <= 0)

            // Update key
            key = 1;

        // Deincrement key to match with array wich start at 0 <=> 1
        key = key--;

        // Check key exists in array
        if(key in this._previous.keys())

            // Set result
            result = this._previous[key];

        // Return result
        return key;

    }

    /**
     * Get All Previous
     * 
     * @param filterByType Filter value of history by type
     * @param order Newer or older
     * @returns Array<any>
     */
    public getAllPrevious = (filterByType:"string"|"number"|"boolean"|"bigint"|"symbol"|"null"|"undefined"|null = null, order:"newer"|"older" = 'newer'):Array<any> => {

        // Set result
        let result:Array<any> = [];

        // Check this._previous
        if(this._previous.length)

            // Iteration of previous
            for(let item of this._previous){

                // Check filterByType
                if(filterByType && item.type != filterByType)

                    // Continue iteration
                    continue;

                // Push value in result
                result.push(item.value);

            }

        // Check order
        if(order == "older")

            // Invert array
            result.reverse();

        // Return result
        return result;

    }

    /**
     * Set Limit
     * 
     * Set Limit of previous value stored 
     * 
     * @param max Max number of previous values stored
     */
    public setLimit = (max:number = 50):void => {

        // Check number
        if(max < 0)

            // Stop
            return;

        // Set limit
        this._limit = max;

        // Check limit
        this._checkLimit();

    }

    /**
     * Get Limit
     * 
     * Get limit number of previous value
     * 
     * @returns number
     */
    public getLimit = ():number => this._limit;

    /** Private methods
     ******************************************************
     */

    /**
     * Check Limit
     * 
     * Check limit of the previous array based on the limit number
     * 
     * @return void
     */
    private _checkLimit = ():void => {

        // Check if previous has more than limit
        if(this._previous.length > this._limit)

            // Slice previous array
            this._previous = this._previous.slice(0, this._limit);

    }

}