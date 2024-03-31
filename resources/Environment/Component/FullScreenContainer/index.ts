/**
 * Full Screen
 *
 * Full Screen with background
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { Crazyelement } from "crazyphp";
 
 /**
  * 
  */
 export default class FullScreenContainer extends Crazyelement {

   /** Parameters
    ******************************************************
   */

   /** @var shadowEl:ShadowRoot */
   public shadow:ShadowRoot|null = null;

   /** @var innerHtmlContent:string|null */
   public innerHtmlContent: string|null;

   /** @var attributesCollection:Object|null */
   public attributesCollection: Object|null = null;
 
    /**
    * Constructor
    */
    constructor(){

      // Call parent constructor
      super();

      // Shadow Mode
      this.attachShadowMode();

      // New element
      let el = document.createElement("div");

      // Append content
      el.innerHTML = "<slot></slot>";

    }

    /** Constants
    ******************************************************
    */
     
     /** @const shadowMode:ShadowRootMode|null Define Shadow mode "Open", "Closed" or null */
     public shadowMode:ShadowRootMode|null = "open";
 
 }