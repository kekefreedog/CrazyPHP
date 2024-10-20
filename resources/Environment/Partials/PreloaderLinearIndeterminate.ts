/**
 * Partials
 *
 * Front TS Scrips for partials components
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
const html = require("./../../../assets/Hbs/partials/preloader_linear_indeterminate.hbs");
import { Crazypartial, Form as UtilityForm} from "crazyphp";

/**
 * Form
 *
 * Script of the partial form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class PreloaderLinearIndeterminate extends Crazypartial {
    
    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html = html;

    /**
     * Constructor
     */
    public constructor(input:RegisterPartialScanned){

        // Parent constructor
        super(input);

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Enable
     * 
     * Enable progress anim
     * 
     * @return {void}
     */
    public enable = ():void => {

        // Check target
        if(this.input.target instanceof HTMLDivElement && this.input.target.hasAttribute("disabled"))

            // Remove disabled attribute
            this.input.target.removeAttribute("disabled");

    }

    /**
     * Disable
     * 
     * Disable progress anim
     * 
     * @return {void}
     */
    public disable = ():void => {

        // Check target
        if(this.input.target instanceof HTMLDivElement && !this.input.target.hasAttribute("disabled"))

            // Remove disabled attribute
            this.input.target.setAttribute("disabled", "");

    }

}