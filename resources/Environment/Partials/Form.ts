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
import { formOnChangeOptions, formOnChangeResult, formOnResetResult, formOnSubmitResult } from "crazyphp/src/Front/Library/Utility/Form";
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
    
    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html = html;

    /** @var _form Form Instance */
    private _form:UtilityForm;

    /** @param partialEl */
    private _partialEl:HTMLDivElement;

    /**
     * Constructor
     */
    public constructor(input:RegisterPartialScanned, options:Partial<FormOptions> = {}){

        // Parent constructor
        super(input);

        console.log("hello form");

        // Get form
        let form = this.input.target && this.input.target instanceof HTMLFormElement 
            ? this.input.target 
            : ""
        ;

        // Prepare form
        this._form = new UtilityForm(form , options);

    }
    
    /** Public methods
     ******************************************************
     */

    /**
     * Get Form Data
     */
    public getFormData = ():null|FormData => {

        // Return get form data
        let result = this.input.target instanceof HTMLFormElement
            ? this._form.getFormData(this.input.target)
            : null;
        ;

        // return result
        return result;

    }

    /**
     * On Submit
     */
    public onSubmit = (callable:(result:formOnSubmitResult)=>void) => {

        // Set on submit
        this._form.setOnSubmit(callable);

    }

    /**
     * On Reset
     */
    public onReset = (callable:(result:formOnResetResult)=>void) => {

        // Set on submit
        this._form.setOnReset(callable);

    }

    /**
     * On Change
     * 
     * @param callable
     * @param options
     * @return any
     */
    public onChange = (callable:(result:formOnChangeResult)=>void, options:Partial<formOnChangeOptions>):any => {

        // Set on change on form instance
        this._form.setOnChange(callable, options);

    }

}