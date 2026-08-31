/**
 * Page
 *
 * Pages of your app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {Crazylanguage, Crazypage, DomRoot, State} from "crazyphp";
const css = require("!!css-loader!sass-loader!./style.scss");
const html = require("./template.hbs");
require("./style.scss");

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Home extends Crazypage {

    /** 
     * @param className:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly className:string = "Home";

    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html = html;

    /** 
     * @param css:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly css = css;

    /**
     * Constructor
     */
    public constructor(){

        /**
         * Parent constructor
         */
        super();

        /**
         * On Ready
         */
        this.onReady();

    }

    /**
     * On Ready
     *
     * @return void
     */
    public onReady = ():void => {

        console.log("hello home");

        // Check page loaded well
        this._pageLoadedWell();

    }

    /** Private methods | Init
     ******************************************************
     */

    /**
     * Page Loaded Well
     * 
     * @returns {void}
     */
    private _pageLoadedWell = ():void => {

        // Get root
        let rootEl = DomRoot.getEl();

        // Get state
        let state = State.get().page();

        // Check root el
        if(rootEl && rootEl.childElementCount == 0 && Array.isArray(state) && state.length === 0){
        
            // Detect language of navigator
            let language = Crazylanguage.getNavigatorLanguage();

            // Reload page
            this.redirectByName(Home.className, { 
                arguments: {
                    language: language
                }
            });

        }

    }

}

/**
 * Register current class
 */
window.Crazyobject.register(Home);