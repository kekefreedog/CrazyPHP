/**
 * Loading Screen Btn (Index)
 *
 * Customisable Loadine Sreen Btn
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazyelement from "./../../../../vendor/kzarshenas/crazyphp/src/Front/Crazyelement";
const TemplateCompilated:CallableFunction = require("./template.hbs");
const StyleCompilated:CrazyelementStyle = require("!!css-loader!sass-loader!./style.scss");

/**
 * 
 */
export default class LoadingScreenBtn extends Crazyelement {

    /** Parameters
     ******************************************************
     */

    /** @var shadowEl:ShadowRoot */
    public shadow:ShadowRoot|null = null;

    /**
     * Constructor
     */
    constructor(){

        // Call parent constructor
        super();

        // Shadow Mode
        this.attachShadowMode();

        // Set content
        this.setHtmlContent(TemplateCompilated({}));

        // Set style
        this.setStyleContent(StyleCompilated);

    }

    /** Constants
     ******************************************************
     */
    
    /** @const shadowMode:ShadowRootMode|null Define Shadow mode "Open", "Closed" or null */
    public shadowMode:ShadowRootMode|null = null;

}