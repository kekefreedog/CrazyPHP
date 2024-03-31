/**
 * Navigator
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Client
 *
 * Methods for manage client navigator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Client {

    /** Private parameters
     ******************************************************
     */

    /** @var message:string */
    private _closeMessage:string = 'Are you sure you want to leave ?';

    /** @var _customCloseMessage:string */
    private _customCloseMessage:string;

    /** Public parameters
     ******************************************************
     */

    /** @var isActive Check if event close is active */
    public isPreventCloseActive = false;


    /** Public methods
     ******************************************************
     */

    /**
     * Prevent Close
     * 
     * @param message
     * @returns {void}
     */
    public preventClose = (message:string = "") => {

        // Set message
        this._customCloseMessage = message ? message : this._closeMessage;

        // Add event listener
        window.addEventListener("beforeunload", this._beforeUnloadEvent);

        // Switch status
        this.isPreventCloseActive = true;

    }

    /**
     * Desable Close
     * 
     * @param message
     * @returns {void}
     */
    public disablePreventClose = () => {

        // Add event listener
        window.removeEventListener("beforeunload", this._beforeUnloadEvent);

        // Switch status
        this.isPreventCloseActive = false;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Before Unload Event
     * 
     * @param event
     * @returns {void}
     */
    private _beforeUnloadEvent = (event:Event):void => {

        // Prevent default
        event.preventDefault();

        // 
        // @ts-ignore
        event.returnValue = `Are you sure you want to leave?`;

        /* // check if event dialog
        if(
            "dialog" in event && 
            typeof event.dialog === "object" && 
            event.dialog &&
            "setMessage" in event.dialog && 
            typeof event.dialog.setMessage === "function" && 
            event.dialog.setMessage &&
            "setButtonLabel" in event.dialog && 
            typeof event.dialog.setButtonLabel === "function" && 
            event.dialog.setButtonLabel
        ){

            // Set message
            event.dialog.setMessage(this._customCloseMessage);

            // Set button
            event.dialog.setButtonLabel("Save");

            // Check show
            if("show" in event.dialog && typeof event.dialog.show === "function")

                // Show dialog
                event.dialog.show().then(async (result) => {
                    if (result == "Save"){
                        // save the document.
                    }
                });
        } */
    }

}