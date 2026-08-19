/**
 * Form
 *
 * Front TS Scrips for form type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Form Input Type Helpers
 *
 * Small set of callbacks a type handler can need back from the owning Form
 * instance. Kept explicit (instead of passing the whole Form) so type
 * handlers stay easy to reason about on their own.
 */
export interface FormInputTypeHelpers {

    /** @var processQueryParams Resolve "{{fieldName}}" placeholders against the form's current values */
    processQueryParams:(query:Record<string,string>, formEl:HTMLFormElement|null) => Record<string,string>;

}

/**
 * Form Input Type
 *
 * Contract implemented by each per-input-type handler (Text, Select, Date, ...).
 * One instance is created per Form (see Form's constructor / this._typeRegistry)
 * so each can hold the owning form's options (e.g. filter mode).
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default interface FormInputType {

    /**
     * Init
     *
     * Wire any plugin/mask/picker needed on this input (was Form's
     * _init{Type}Input). Optional: types with no special wiring (text,
     * email, password, hidden) can omit it. Receives the owning Form's
     * current options so this handler resyncs its own this._options
     * instead of working off the snapshot cloned at construction time.
     *
     * @param inputEl
     * @param formEl
     * @param helpers
     * @param options
     * @return Promise<void>
     */
    init?(inputEl:HTMLInputElement|HTMLSelectElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions>):Promise<void>;

    /**
     * Get
     *
     * Read a single value out of the input (was Form's {type}Retrieve).
     * Receives the owning Form's current options so this handler resyncs
     * its own this._options instead of working off the snapshot cloned at
     * construction time.
     *
     * @param itemEl
     * @param options
     * @return null|Array<any>
     */
    get(itemEl:HTMLElement, options:Partial<FormOptions>):null|Array<any>;

    /**
     * Get Multiple
     *
     * Read a value (or several) out of the input for multi-value fields
     * (was Form's {type}RetrieveMultiple). Receives the owning Form's
     * current options so this handler resyncs its own this._options
     * instead of working off the snapshot cloned at construction time.
     *
     * @param itemEl
     * @param options
     * @return null|Array<any>[]
     */
    getMultiple(itemEl:HTMLElement, options:Partial<FormOptions>):null|Array<any>[];

    /**
     * Set
     *
     * Write a value into the input (was Form's {type}Set). Receives the
     * owning form element so it can reconcile the shared value_id via
     * Form.setId without holding a reference to the form itself, and the
     * owning Form's current options so this handler resyncs its own
     * this._options instead of working off the snapshot cloned at
     * construction time. Optional: types with no write-back support
     * (checkbox, radio) can omit it.
     *
     * @param itemEl
     * @param value
     * @param valuesID
     * @param formEl
     * @param options
     * @return void
     */
    set?(itemEl:HTMLElement, value:any, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions>):void;

}
