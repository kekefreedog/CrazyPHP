/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';

/**
 * Events
 *
 * Methods for store custom events
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Events {

    /** Private parameters
     ******************************************************
     */

    /** @param _collection Collection of the event */
    private _collection:Array<Event|CustomEvent> = [];

    /**
     * Constructor
     */
    public constructor(){



    }

    /**
     * Add
     * 
     * Add Event
     * 
     * @param name Name of the event
     * @param detail Data you want pass into the event
     */
    public add = (name:string, detail:object|null = null):void => {

        // Check name
        if(!name)

            // Error
            throw new PageError("Event must have a valid name");

        // Check if event already exists
        if(this.has(name))

            // Error
            throw new PageError("The Event already exists");

        // Declare var
        let event:Event|CustomEvent;

        // Check detail
        if(detail === null)

            // Set event
            event = new Event(name);

        else

            // Set event
            event = new CustomEvent(name, {
                detail: detail
            });

        // Push event in the collection
        this._collection.push(event);

    }

    /**
     * Get
     * 
     * Get a event
     * 
     * @param name Name of the event
     * @returns Event|CustomEvent|null Null if not event with this name
     */
    public get = (name:string):Event|CustomEvent|null => {

        // Prepare result
        let result:Event|CustomEvent|null = null;

        // Check name
        if(name && this._collection.length)

            // Iteration of the events
            for(let event of this._collection)

                // Check if type is matching with name
                if(event.type == name){

                    // Set result
                    result = event;

                    // Break event
                    break;

                }

        // Return result
        return result;

    }

    /**
     * Get All
     * 
     * Get All Events
     * 
     * @returns Array<Event|CustomEvent>
     */
    public getAll = ():Array<Event|CustomEvent> => this._collection;

    /**
     * Has
     * 
     * Has event
     * 
     * @param name 
     */
    public has = (name:string):Boolean => {

        // Prepare result
        let result:Boolean = false;

        // Check name
        if(name && this._collection.length)

            // Iteration of the events
            for(let event of this._collection)

                // Check if type is matching with name
                if(event.type == name){

                    // Set result
                    result = true;

                    // Break event
                    break;

                }

        // Return result
        return result;

    }

    /**
     * Remove
     * 
     * Remove Event
     * 
     * @param name 
     * @returns boolean
     */
    public remove = (name:string):Boolean => {

        // Prepare result
        let result:Boolean = false;

        // Check if event is set
        if((name && !this.has(name)) || this._collection.length === 0)

            // Set result
            result = true;

        else
        // Check name
        if(name && this._collection.length)

            // Iteration of the events
            for(let eventKey in this._collection)

                // Check if type is matching with name
                if(this._collection[eventKey].type == name){

                    // Delete item
                    delete this._collection[eventKey];

                    // Set result
                    result = true;

                    // Break event
                    break;

                }

        // Return result
        return result;

    }

    /**
     * Dispatch
     * 
     * dispatch event
     * 
     * @param name 
     */
    public dispatch = (name:string|Array<string>, target:HTMLElement|Document = document):void => {

        // Check name
        if(!name)

            // Return
            return;

        // Check if name is string
        if(typeof name === "string")

            // Convert it to array
            name = [name];

        // Declare event
        let currentEvent:Event|CustomEvent|null;

        // Check name
        if(name.length)

            // Iteration of the name
            for(let current of name){

                // Get event
                currentEvent = this.get(current);

                // check current event
                if(currentEvent !== null)

                    // Dispatch event
                    target.dispatchEvent(currentEvent);

            }

    }

}