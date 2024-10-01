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
const html = require("./../../../assets/Hbs/partials/form.hbs");
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
export default class Form extends Crazypartial {
    
    /** Parameters
     ******************************************************
     */

    /** @var _form Form Instance */
    private _form:UtilityForm;
    
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

        console.log("hello form");

        // Prepare form
        this._form = new UtilityForm(UtilityForm.isHTMLFormElement(this.input.target) ? this.input.target : "");

    }
    
    /** Private methods
     ******************************************************
     */

    /**
     * Get Form Data
     */
    public getFormData = () => {

        console.log("dev");
        console.log(this._form);

    }

}