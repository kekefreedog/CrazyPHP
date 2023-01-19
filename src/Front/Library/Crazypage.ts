/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Crazy Page
 *
 * Methods for build your page script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default abstract class Crazypage {

    /**
     * Constructor
     */
    public constructor(){

        /**
         * Set Current Page
         */
        this.setCurrentPage();

        /**
         * Dispatch Events
         */
        this.dispatchEvents();

    }

    /** Abstract methods
     ******************************************************
     */

    /**
     * On Ready
     *
     * @return void
     */
    public onReady:CallableFunction;

    /** Private methods
     ******************************************************
     */

    /**
     * Set current page
     * 
     * @return void
     */
    private setCurrentPage = ():void => {

        

    }

    /**
     * Dispatch Events
     *  
     * Dispatch all new events
     *  
     * @return void
     */
    private dispatchEvents = ():void => {



    }

    /**
     * Dispatch Event
     *  
     * Dispatch one event
     *  
     * @param event:string
     * @param callable:CallableFunction
     * @return void
     */
    private dispatchEvent = (event:string, callable:CallableFunction):void => {



    }

    /** Public methods
     ******************************************************
     */

    /**
     * Redirect To
     * 
     * Redirect to another page
     * @param name:string Name of the page to redirect to
     * @param reloadPage:boolean Force a real reload of the page
     * @return void
     */
    public redirectTo = (name:string = "", reloadPage:boolean = false):void => {

        console.log(name);

    }

}