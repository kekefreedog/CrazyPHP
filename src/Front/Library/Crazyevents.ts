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
 * Component Register
 *
 * Methods for manage components loaded and to load
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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

        // Get target el
        let targetEl = e.target as HTMLElement;

        // Check tagname
        let tagName:string = targetEl.tagName;

        console.log(tagName);

        // Check function exits
        if(typeof this[`${tagName.toLowerCase()}Event`] === "function")

            // Execute
            this[`${tagName.toLowerCase()}Event`](targetEl);

    }

    /** Events By Tag
     ******************************************************
     */

    /**
     * A Event
     * 
     * Event on element "a"
     * 
     * @param el 
     */
    private aEvent = (el:HTMLElement):void => {

        console.log("is aaa");

    }

    /** Constants
     ******************************************************
     */

    /** @const TYPE:Array<string>  */
    public readonly TYPE:Array<string> = ["click"];
 

}