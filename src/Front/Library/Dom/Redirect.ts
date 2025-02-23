/**
 * Dom
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Strings from '../Utility/Strings';
import LoaderPage from '../Loader/Page';

/**
 * Root
 *
 * Methods for manipulate redirect item based on attribute [data-redirect-name]
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Redirect {

    /** Public Methods
     ******************************************************
     */

    /**
     * Scan
     * 
     * Scan all redirect
     * @param e element
     * @param onlyNew only new item (without id of disable)
     * @returns {void}
     */
    public static scan = (e:HTMLElement = document.body, onlyNew:boolean = true):void => {

        // Prepare selector
        let selector:string = 
            `[${Redirect.ATTRIBUTE}]` +
            `:NOT([${Redirect.OMIT}])` +
            (
                onlyNew 
                    ? `:NOT([${Redirect.ID}])`
                    : ``
            )
        ;

        // Search all item
        let itemEls = e.querySelectorAll(selector);

        // Check items
        for(let itemEl of Array.from(itemEls)) if(itemEl instanceof HTMLElement){

            // Check id only new and already have id attribute
            if(!onlyNew && itemEl.dataset[Strings.getDataAttributeName(Redirect.ID)]){

                // Clear item
                Redirect.clear(itemEl);

            }

            // Init Event
            Redirect._initEvent(itemEl);

        }

    }

    /**
     * Clear All
     * 
     * Clear all redirect
     * 
     * @param 
     * @returns {void}
     */
    public static clearAll = ():void => {

        // Iteration collection
        if(Redirect._collection) for(let id in Redirect._collection){

            // Set item
            let item = Redirect._collection[id];

            // Remove event of el
            item.e.removeEventListener("click", item.event);

            // Clear id attribute
            delete item.e.dataset[Strings.getDataAttributeName(Redirect.ID)];

            // Clear id of collection
            delete Redirect._collection[id];

        }

        // Check Redirect._collection
        if(Redirect._collection && Object.keys(Redirect._collection).length == 0) Redirect._collection = null;

    }

    /**
     * Clear By Id
     * 
     * @param id string
     * @returns {void}
     */
    public static clearById = (id:string):void => {

        // Check id
        if(id && typeof Redirect._collection === "object" && Redirect._collection && id in Redirect._collection){

            // Set item
            let item = Redirect._collection[id];

            // Remove event of el
            item.e.removeEventListener("click", item.event);

            // Clear id attribute
            delete item.e.dataset[Strings.getDataAttributeName(Redirect.ID)];

            // Clear id of collection
            delete Redirect._collection[id];

        }

        // Check Redirect._collection
        if(Redirect._collection && Object.keys(Redirect._collection).length == 0) Redirect._collection = null;

    }

    /**
     * Clear
     * 
     * @param e htmlElement
     * @returns {void}
     */
    public static clear = (e:HTMLElement):void => {

        // Check id
        if(e.dataset[Strings.getDataAttributeName(Redirect.ID)]){

            // Get id
            let id = e.dataset[Strings.getDataAttributeName(Redirect.ID)];

            // Check id
            if(id)

                // Clear el
                Redirect.clearById(id);

            // Else
            else

                // Clear redirect
                delete e.dataset[Strings.getDataAttributeName(Redirect.ID)];

        }

        // Check Redirect._collection
        if(Redirect._collection && Object.keys(Redirect._collection).length == 0) Redirect._collection = null;

    }

    /**
     * Get All
     * 
     * @param 
     * @returns {void}
     */
    public static getAll = ():null|HTMLElement[] => {

        // Declare result
        let result:null|HTMLElement[] = Redirect._collection
            ? []
            : null
        ;

        // Iteration collection
        if(Redirect._collection) for(let key in Redirect._collection) {

            // Set item
            let item = Redirect._collection[key];

            // Check item el
            if(item.e){

                // Check result
                if(!result) result = [];

                // Push to result
                result?.push(item.e);

            }

        }

        // Return result
        return result;

    }

    /**
     * Scan
     * 
     * Scan all redirect
     * 
     * @param id string
     * @returns {void}
     */
    public static getByID = (id:string):HTMLElement|null => {

        // Set result
        let result:HTMLElement|null = null;

        // Check id
        if(id && typeof Redirect._collection === "object" && Redirect._collection && id in Redirect._collection){

            // Set result
            result = Redirect._collection[id].e;

        }

        // Return result
        return result;

    }

    /** Public ReadOnly
     ******************************************************
     */

    /** @constant readonly ATTRIBUTE Name */
    public static readonly ATTRIBUTE:string = "data-redirect-name";

    /** @constant readonly OMIT Name */
    public static readonly OMIT:string = "data-redirect-omit";

    /** @constant readonly ID Name */
    public static readonly ID:string = "data-redirect-id";

    /** @constant readonly Prefix */
    public static readonly PREFIX:string = "redirect";
    
    /** Private Static Methods
     ******************************************************
     */

    /**
     * Init Event
     * 
     * Init event on
     * 
     * @param el
     */
    private static _initEvent = (el:HTMLElement):void => {

        // Check if html element
        if(Strings.getDataAttributeName(Redirect.ATTRIBUTE) in el.dataset && el.dataset[Strings.getDataAttributeName(Redirect.ATTRIBUTE)]){

            // Generate id
            let id = Date.now() + Math.floor(Math.random() * 1000);

            // Push id into el
            el.setAttribute(Redirect.ID, id.toString());

            // Set new event
            let currentEvent = (event:MouseEvent) => {

                // Prevent default
                event.preventDefault();

                // Prepare options
                let options:LoaderPageOptions = {};

                // Get current target
                let currentTarget = event.currentTarget;

                // Check current target
                if(currentTarget instanceof HTMLElement && Strings.getDataAttributeName(Redirect.ATTRIBUTE) in el.dataset && el.dataset[Strings.getDataAttributeName(Redirect.ATTRIBUTE)]){

                    // Get data-redirect-name value
                    let dataRedirectNameValue:string = el.dataset[Strings.getDataAttributeName(Redirect.ATTRIBUTE)] as string;

                    // Set name
                    options.name = dataRedirectNameValue;

                    // Check target
                    if(currentTarget instanceof HTMLAnchorElement && currentTarget.target == "_blank")
            
                        // Add openInNewTab in options
                        options["openInNewTab"] = true;

                    else
                    // Check ctrl or cmd pressed
                    if(event.ctrlKey || event.metaKey)

                        // Add openInNewTab in options
                        options["openInNewTab"] = true;


                    // Get global attributes
                    window.Crazyobject.state.globalStore?.iterate((value, key)=>{

                        // Check arguments
                        if(typeof options.arguments !== "object" || !options.arguments)

                            // Create object
                            options.arguments = {};

                        // Set as object
                        options.arguments[key] = value;

                        // Return options
                        return options;

                    }).then((options) => {
                            
                        // Load page (redirection)
                        new LoaderPage(options);

                    })

                }
                
            };

            // Check Redirect._collection
            if(!Redirect._collection) Redirect._collection = {};

            // Store event and el in collection
            Redirect._collection[id.toString()] = {
                e: el,
                event: currentEvent
            }

            // Add event on item
            el.addEventListener("click", currentEvent, true);
        
        }

    }
    
    /** Private Static Parameters
     ******************************************************
     */

    /** @var collection */
    private static _collection:Record<string, {e:HTMLElement, event:(event: MouseEvent)=>void}>|null = null;

}