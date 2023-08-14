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
 * Component Register
 *
 * Methods for manage components loaded and to load
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Crazyevents {

    /**
     * Constructor
     */
    public constructor(){

        // Register Events Listeners
        this.registerEventListeners();

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Register Event Listeners
     * 
     * Register Click / Double Click... Events on front interface
     * 
     * @return void
     */
    public registerEventListeners = ():void => {

        // Check type
        if(this.TYPE.length)

            // Iteration of type
            for(let type of this.TYPE)

                // Register event listener
                document.addEventListener(
                    type, 
                    this._eventDispatcher,
                    { capture: true }
                );

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Event Dispatcher
     * 
     * Dispatch current event
     * 
     * @param e:Event Event catch vu event listener
     * @return void
     */
    private _eventDispatcher = (e:Event):void => {

        console.log(e);

    }

    /** Constants
     ******************************************************
     */

    /** @const TYPE:Array<string>  */
    public readonly TYPE:Array<string> = ["click"];
 

}