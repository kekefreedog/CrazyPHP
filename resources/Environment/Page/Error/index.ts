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
import {Carousel} from "@materializecss/materialize";
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
     * @return void
     */
    public onReady = ():void => {

        console.log("hello error front");

        // Init carousel
        this.initCarousel();

    }

    /**
     * Init Carousel
     * 
     * @return void
     */
    private initCarousel = () => {
        
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

}

/**
 * Register current class
 */
window.Crazyobject.register(Home);