/**
 * Partials
 *
 * Front TS Scrips for partials components
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
const html = require("./../../../assets/Hbs/partials/navigation.hbs");
import { Crazypartial, Crazylanguage, LoaderPage } from "crazyphp";

/**
 * Navigation
 *
 * Script of the partial Navigation
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Navigation extends Crazypartial {
    
    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html = html;

    /**
     * Constructor
     */
    public constructor(input:RegisterPartialScanned){

        // Parent constructor
        super(input);

        // Init Redirection
        this._initRedirection();

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Init Redirections
     * 
     * @returns {void}
     */
    private _initRedirection = ():void => {

        // Set sleector query
        const querySelector = "[data-redirect-name]";

        // Get all elements
        let els = this.input.target.querySelectorAll(querySelector);

        // Check els
        if(els.length)

            // Iteration of els
            for(let el of els){

                // Check if html element
                if(el instanceof HTMLElement && "redirectName" in el.dataset){

                    // Get data-redirect-name value
                    let dataRedirectNameValue:string = el.dataset.redirectName as string;
                
                    // Detect language of navigator
                    let language = Crazylanguage.getNavigatorLanguage();

                    // Check value
                    if(dataRedirectNameValue){

                        // Add click event on the el
                        el.addEventListener(
                            "click",
                            e => {
                
                                // Prevent default
                                e.preventDefault();

                                // Prepare options
                                let options:LoaderPageOptions = {
                                    name: dataRedirectNameValue,
                                    arguments: {
                                        language: language
                                    }
                                };

                                // Check ctrl or cmd pressed
                                if(e.ctrlKey || e.metaKey)

                                    // Add openInNewTab in options
                                    options["openInNewTab"] = true;
                
                                // Load page (redirection)
                                new LoaderPage(options);
                
                            }
                        );

                    }
                
                }

            }

    }

}