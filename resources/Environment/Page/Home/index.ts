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
const css = require("!!css-loader!sass-loader!./style.scss");
import { M } from "@materializecss/materialize";
const html = require("./template.hbs");
import {Crazypage} from "crazyphp";
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

        // Init scroll spy
        this.initScrollSpy();

    }

    /**
     * Init Scroll Spy
     * 
     * @return void
     */
    private initScrollSpy = () => {

        // Get el
        let els = document.querySelectorAll('.scrollspy');

        // Check els
        if(els.length)

            M.ScrollSpy.init(els, {
                "activeClass": "active",
                "getActiveElement": (id) => {
                    return 'a[href="#' + id + '"]';
                }
            });

    }

}

/**
 * Register current class
 */
window.Crazyobject.register(Home);