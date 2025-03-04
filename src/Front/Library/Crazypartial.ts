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
 * Dependances
 */
import Pageregister from "./Pageregister";
import Crazyrequest from "./Crazyrequest";
import { Crazyobject } from "../Types";
import State from "./State";

/**
 * Crazy Partial
 *
 * Methods for build your partial script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default abstract class Crazypartial {

    /** Parameters
     ******************************************************
     */

    /**
     * Input
     */
    public isReloaded:boolean = false;

    /**
     * Input
     */
    public input:RegisterPartialScanned;

    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public html:string|null|CallableFunction = null;

    /**
     * Constructor
     * 
     * @param input
     */
    public constructor(input:RegisterPartialScanned){

        // Set input
        this.input = input;

        // Check id and target
        if(typeof this.input.id === "number" && this.input.target instanceof HTMLElement)

            // Set id on target
            this.input.target.dataset.partialId = this.input.id.toString();

        // Check if html
        if(this.input.callable.html)

            // Set html
            this.html = this.input.callable.html;

    }

    /** Protected methods
     ******************************************************
     */

    /**
     * Reload
     * 
     * @param state:Object|null
     */
    public reload = (state:Object|null = null) => {
        
        // Check target
        if(this.input.target instanceof HTMLElement && this.html !== null){

            // Set data
            var htmlString = "";

            // Check html
            if(typeof state === "object" && typeof this.html === "function")

                // Get string
                htmlString = this.html(typeof state === "object" ? state : {});

            // Get content dom
            var contentDom = document.createRange().createContextualFragment(htmlString);

            // Search partial
            let partialEl = contentDom.querySelector("[partial]");

            // Check partialEl and set id
            partialEl && partialEl.setAttribute("data-partial-id", this.input.id.toString());

            // Get parent
            let parentEl = this.input.target.parentElement;

            // Destroy previous instance
            this.onDestroy();

            // Reload partial
            this.input.target.replaceWith(contentDom);

            // Check parent el
            if(parentEl){

                // Get new element
                let newTargetEl = parentEl.querySelector(`[data-partial-id="${this.input.id.toString()}"]`);

                // Check new target el
                if(newTargetEl){

                    // Set input target
                    this.input.target = newTargetEl;

                }

            }

        }

        // Set is reloaded
        this.isReloaded = true;

        // Execute on ready
        this.onReady();

    }

    /** Public methods
     ******************************************************
     */

    /**
     * On Ready
     */
    public onReady = () => {

    }

    /**
     * On Destroy
     */
    public onDestroy = () => {

    }

    /**
     * Enable
     */
    public enable = () => {

    }

    /**
     * Enable
     */
    public disable = () => {

    }

    /**
     * On Change
     * 
     * @param callable
     * @param options
     * @return any
     */
    public onChange = (callable:(result:any)=>void, options:Record<any, any>):any => {

    }

    /** Public methods | Utilities
     ******************************************************
     */

    /**
     * Get Current Page Name
     */
    public getCurrentPageName = ():string => {

        // Set result
        let result:string = "";

        // Check page
        if(this.input.page){

            // Set result
            result = this.input.page["className"] as string;

        }else{

            // Search for current page name
            if(window.Crazyobject && window.Crazyobject.currentPage && window.Crazyobject.currentPage.get()){

                // Get current page
                let currentPage = window.Crazyobject.currentPage.get();

                // Get page name
                let currentPageName = currentPage?.name;

                // Check current page name
                if(currentPageName)

                    // Set result
                    result = currentPageName;

            }

        }

        // Return result
        return result;

    }

}