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
import {default as PageError} from './../Error/Page';

/**
 * Root
 *
 * Methods for manipulate dom root
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Root {

    /** Public Constants
     ******************************************************
     */

    /** @var id:string Id of the root
     * 
     */
    public static readonly id:string = "crazy-root";

    /** Public static methods
     ******************************************************
     */

    /**
     * Get ID
     * 
     * Get Id of the root element
     * 
     * @return string
     */
    public static getId = ():string => {

        // Check id
        if(!Root.id)

            // New error
            throw new PageError("ID of the root is not set.");

        // Return id
        return Root.id;
    }

    /**
     * Get El
     * 
     * Get Root Element
     * 
     * @return HTMLElement
     */
    public static getEl = ():HTMLElement => {

        // Get id
        const id:string = Root.id;

        // Get el
        let result:HTMLElement|null = document.getElementById(id);

        // Check result is null
        if(result === null)

            // New error
            throw new PageError(`Element "#${id}" does not exist in the dom.`);

        return result;

    }

    /**
     * Set Content
     * 
     * Set Content of the Root 
     * 
     * @return void
     */
    public static setContent = (content:string = ""):HTMLElement => {

        // Get crazy root
        let rootEl:HTMLElement = Root.getEl();

        // Set content
        rootEl.innerHTML = content;

        // Return content
        return rootEl;

    }


}