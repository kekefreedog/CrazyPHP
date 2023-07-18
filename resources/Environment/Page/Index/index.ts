/**
 * Page
 *
 * Pages of your app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {Crazypage, Crazylanguage} from "crazyphp";
const html = require("./template.hbs");
// const css = require("./style.scss");

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Index extends Crazypage {

    /** 
     * @param className:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly className:string = "Index";

    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html = html;

    /** 
     * @param css:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly css = null;

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

        // Detect language of navigator
        let language = Crazylanguage.getNavigatorLanguage();

        // Redirect to home page
        this.redirectByName("Home", {
            arguments: {
                language: language
            },
            mimetype: "json"
        });

    }

}

/**
 * Register current class
 */
window.Crazyobject.pages.register(Index);
window.Crazyobject.registerPage(Index);