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
export default class RegularBtn extends Crazycomponent {

   /** Parameters
   ******************************************************
   */

   /** @var properties Propoerties of the current component */
   public properties:Object = {
      type: {
         value: "floating",
         type: "string",
         select: ["floating", "extended"],
      },
      depth: {
         value: "flat",
         type: "string",
         select: ["flat", "outlined", "1", "2", "3", "4", "5"],
      },
      shape: {
         value: "round",
         type: "string",
         select: ["round", "box"],
      },
      size: {
         value: "large",
         type: "string",
         select: ["small", "normal", "large"],
      },
      wave: {
         value: "light",
         type: "string",
         select: ["light", "dark", "false"],
      },
      label: {
         value: "",
         type: "string"
      },
      "icon-class": {
         value: "material-icons",
         type: "string",
      },
      "icon-text": {
         value: "add",
         type: "string",
      },
      "icon-position": {
         value: "right",
         type: "string",
         select: ["left", "right"],
      },
      "color-primary": {
         value: "",
         type: "string"
      },
      "color-secondary": {
         value: "blue lighten-5",
         type: "string",
      }
   };

   /**
    * Observable Attributes
    */
   static get observedAttributes() { 
      return Object.keys(["type", "depth", "shape", "size", "wave", "label", "icon-class", "icon-text", "icon-position", "color-primary", "color-secondary"]); 
   }

   /**
   * Constructor
   */
   constructor(){

      // Call parent constructor
      super();

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

      // Check attribute label
      if(this.getCurrentAttribute("type") == "floating" && this.hasCurrentAttribute("label")){

         // Get value
         let labelValue = this.getCurrentAttribute("label");

         // Check if string
         if(labelValue && typeof labelValue === "string"){
         
            // Tippy
            tippy(this.children[0], {
               content: labelValue,
               animateFill: true,
               arrow: false,
               plugins: [animateFill],
               placement: 'auto',
            });

         }

      }

   }

}