/**
 * Page
 *
 * Pages of your app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
const css = require("!!css-loader!sass-loader!./style.scss");
import {Carousel, M} from "@materializecss/materialize";
import {Crazypage, LoaderPage} from "crazyphp";
const html = require("./template.hbs");
require("./style.scss");

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Home extends Crazypage {

    /** 
     * @param className:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly className:string = "Error";

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
     * Carousel Instance
     */
    public carouselInstance:Carousel|null = null; 

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
     * @returns {void}
     */
    public onReady = ():void => {

        console.log("hello error front");

        // Init carousel
        this.initCarousel();

        // Init Index Button
        this.initIndexBtn();

    }

    /**
     * Init Carousel
     * 
     * @returns {void}
     */
    private initCarousel = ():void => {
        
        // Get error-carousel
        let errorCarouselEl = document.getElementById("crazy-root");

        // Check el
        if(errorCarouselEl !== null)

            // New carousel instance
            this.carouselInstance = new Carousel(
                errorCarouselEl,
                {
                    fullWidth: true,
                    indicators: true,
                    noWrap: true
                }
            );

    }

    /**
     * Init Index Btn
     * 
     * @returns {void}
     */
    private initIndexBtn = ():void => {

        // Get btn el
        let btnEl = document.getElementById("root-btn");

        // Check el
        if(btnEl !== null)

            // Add event on click
            btnEl.addEventListener(
                "click",
                e => {

                    e.preventDefault();

                    // Set option
                    let options:LoaderPageOptions = {
                        name: "Index",
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

/**
 * Register current class
 */
window.Crazyobject.register(Home);