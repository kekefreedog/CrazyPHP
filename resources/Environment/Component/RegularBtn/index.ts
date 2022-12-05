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
import Crazycomponent from "./../../../../vendor/kzarshenas/crazyphp/src/Front/Crazycomponent";
const TemplateCompilated:CallableFunction = require("./template.hbs");
const StyleCompilated:CrazyelementStyle = require("!!css-loader!sass-loader!./style.scss");

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
         type: "string"
      },
      depth: {
         value: "flat",
         type: "string"
      }
   };

   /**
   * Constructor
   */
   constructor(){

      // Call parent constructor
      super();

      // Set attributes by default
      this.setDefaultProperties(this.properties);

      // Set Content
      this.setHtmlAndCss(
         TemplateCompilated,
         StyleCompilated
      );

   }

}