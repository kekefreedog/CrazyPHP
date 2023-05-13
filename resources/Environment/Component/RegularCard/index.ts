/**
 * Full Screen
 *
 * Full Screen with background
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
const TemplateCompilated:CallableFunction = require("./template.hbs");
const StyleCompilated:CrazyelementStyle = require("!!css-loader!sass-loader!./style.scss");
import tippy, {animateFill} from 'tippy.js';
import { Crazycomponent } from "crazyphp";

/**
 * Regular Button
 *
 * Webcomponent for Regular Button
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class RegularCard extends Crazycomponent {

    /** Parameters
    ******************************************************
    */

    /** @var properties Propoerties of the current component */
    public properties:Object = {
        depth: {
            value: "flat",
            type: "string",
            select: ["flat", "1", "2", "3", "4", "5"],
        },
        shape: {
            value: "round",
            type: "string",
            select: ["round", "box"],
        },
        type: {
            value: "fill",
            type: "string",
            select: ["fill", "outline"]
        },
        wave: {
            value: "false",
            type: "string",
            select: ["light", "dark", "false"],
        },
        "color-primary": {
           value: "blue lighten-5",
           type: "string"
        },
        "color-secondary": {
           value: "red",
           type: "string",
        },
        preloader: {
            value: "false",
            type: "string",
        }
    };

    /**
     * Observable Attributes
     */
    static get observedAttributes() { 
       return ["depth", "shape", "wave", "color-primary",  "color-secondary", "preloader"]; 
    }

    /**
    * Constructor
    */
    constructor(){

        // Call parent constructor
        super({
            allowChildNodes: true,
        });

        // Set Content
        this.setHtmlAndCss(
            TemplateCompilated,
            StyleCompilated
        );

    }

    /**
     * Post Render
     * 
     * Event Post Render
     * 
     @return void
     */
    public postRender():void {

            

    }

}