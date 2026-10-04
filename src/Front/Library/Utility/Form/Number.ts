/**
 * Number Form Type
 *
 * Initialize number inputs and serialize numeric filters
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 *
 * @migrated-to /Users/kzarshenas/Sites/CrazyProject/kmaterialize/components/number-input/number-input.ts
 */

/**
 * Dependances
 */
import type { FormInputTypeHelpers, FormInputType } from "./FormType";
import IMask, { MaskedNumberOptions } from "imask";
import {default as Form} from "../Form";
import FormType from "./FormType";

/**
 * Number Type
 *
 * Handler for "number" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class NumberType extends FormType implements FormInputType {

    /** Private Parameters
     ******************************************************
     */

    /** @var _options */
    private _options:Partial<FormOptions> = {
        filter: false
    };

    /**
     * Constructor
     *
     * @param options
     */
    constructor(options:Partial<FormOptions> = {}){

        // Call parent constructor
        super();

        // Ingest options
        this._options = {...this._options, ...options};

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Initialize Bounds
     *
     * Initialize native number fields and return their lifecycle cleanup.
     *
     * @param input
     * @param form
     * @returns {() => void} Remove listeners and disconnect the observer
     */
    public static initializeBounds(input:HTMLInputElement, form:HTMLFormElement):() => void {

        // Set host
        const host = input.closest<HTMLElement>("[data-number-bounds]")!;

        // Set min
        const min = host.querySelector<HTMLInputElement>("[data-number-bound=min]")!;

        // Set max
        const max = host.querySelector<HTMLInputElement>("[data-number-bound=max]")!;

        // Set error
        const error = host.querySelector<HTMLElement>("[data-number-bounds-error]")!;

        // Set destroyed
        let destroyed = false;

        /**
         * Validate Bounds
         *
         * Validate endpoints before exposing a condition to query serialization.
         *
         * @returns {boolean}
         */
        const validate = ():boolean => {

            // Clear minimum validity
            min.setCustomValidity("");

            // Clear maximum validity
            max.setCustomValidity("");

            // Set reversed
            const reversed = min.value !== "" && max.value !== "" && min.valueAsNumber > max.valueAsNumber;

            // Set message
            const message = reversed ? "Minimum must be less than or equal to maximum." : "";

            // Set min custom validity
            min.setCustomValidity(message);

            // Set max custom validity
            max.setCustomValidity(message);

            // Set invalid
            const invalid = !min.validity.valid || !max.validity.valid;

            // Set error message
            error.textContent = message || (invalid ? "Enter valid numbers within the allowed limits." : "");

            // Set hidden
            error.hidden = !invalid;

            // Set aria invalid for min
            min.setAttribute("aria-invalid", String(!min.validity.valid));

            // Set aria invalid for max
            max.setAttribute("aria-invalid", String(!max.validity.valid));

            // Return validity
            return !invalid;

        };

        /**
         * Sync
         *
         * @param event
         * @returns {void}
         */
        const sync = (event?:Event):void => {

            // Ignore edits on disabled or readonly fields
            if(event && (input.disabled || input.readOnly))
                return;

            // Encode the valid endpoints
            input.value = validate()
                ? min.value !== "" && max.value !== "" ? `=[${min.value}:${max.value}]`
                    : min.value !== "" ? `>=${min.value}` : max.value !== "" ? `<=${max.value}` : ""
                : "";

            // Forward edits through the encoded input
            if(event){

                event.stopPropagation();
                input.dispatchEvent(new Event(event.type, {bubbles: true}));

            }

        };

        /**
         * Restore Bounds
         *
         * @returns {void}
         */
        const restore = ():void => {

            // Read the encoded condition
            const value = input.value;
            const interval = value.match(/^=?\[([^:]+):([^\]]+)\]$/);

            // Restore each endpoint
            min.value = interval ? interval[1] : value.startsWith(">=") ? value.slice(2) : "";
            max.value = interval ? interval[2] : value.startsWith("<=") ? value.slice(2) : "";

            // Validate the restored condition
            sync();

        };

        /**
         * Handle Value Change
         *
         * Forward only external changes; an edit must retain its invalid endpoints.
         *
         * @returns {void}
         */
        const onValue = ():void => {

            restore();

        };

        /**
         * Handle Endpoint Edit
         *
         * @param event
         * @returns {void}
         */
        const onEdit = (event:Event):void => {

            // Temporarily suspend restoration while forwarding the edit
            input.removeEventListener(event.type, onValue);
            sync(event);
            input.addEventListener(event.type, onValue);

        };

        /**
         * Synchronize Field State
         *
         * @returns {void}
         */
        const state = ():void => {

            // Mirror the encoded input state on both endpoints
            for(const field of [min, max]){

                field.disabled = input.disabled;
                field.readOnly = input.readOnly;

            }

        };

        // Observe disabled and readonly changes
        const observer = new MutationObserver(state);
        observer.observe(input, {attributes: true, attributeFilter: ["disabled", "readonly"]});

        /**
         * Reset Bounds
         *
         * @returns {void}
         */
        const reset = ():void => {

            // Native reset finishes after the reset event has propagated.
            queueMicrotask(() => {

                if(!destroyed)
                    restore();

            });

        };

        // Listen to endpoint edits and external value changes
        for(const type of ["input", "change"]){

            // Set on edit event on min
            min.addEventListener(type, onEdit);
            
            // Set on edit event on max
            max.addEventListener(type, onEdit);
            
            // Set on on value intm
            input.addEventListener(type, onValue);

        }

        // Initialize reset handling, field state and endpoint values
        form.addEventListener("reset", reset);

        // State
        state();
        
        // Restore
        restore();

        // Return lifecycle cleanup
        return () => {

            // Prevent queued resets and stop observing state
            destroyed = true;
            observer.disconnect();
            form.removeEventListener("reset", reset);

            // Remove endpoint and value listeners
            for(const type of ["input", "change"]){

                min.removeEventListener(type, onEdit);
                max.removeEventListener(type, onEdit);
                input.removeEventListener(type, onValue);

            }

        };

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Init
     *
     * @param inputEl
     * @param formEl
     * @param helpers
     * @param options
     * @returns {Promise<void>}
     */
    public init = async (inputEl:HTMLSelectElement|HTMLInputElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Declare mask options
        let maskOptions:MaskedNumberOptions = {
            mask: Number,
            skipInvalid: true,
            thousandsSeparator: " ",
            radix: ".",
            mapToRadix: [","],
            autofix: true,
        };

        // Check if max
        inputEl.hasAttribute("max") && inputEl.getAttribute("max") && (maskOptions.max = Number(inputEl.getAttribute("max")));

        // Check if min
        inputEl.hasAttribute("min") && inputEl.getAttribute("min") && (maskOptions.min = Number(inputEl.getAttribute("min")));

        // Check if decimal
        inputEl.hasAttribute("step") && inputEl.getAttribute("step")?.includes(".") && (maskOptions.scale = inputEl.getAttribute("step")?.split(".").at(-1)?.length);

        // Set instance
        IMask(inputEl, maskOptions);

    }

    /**
     * Get
     *
     * @param itemEl:HTMLElement
     * @param options
     * @returns {null|Array<any>}
     */
    public get = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            // Set key
            let key:string = itemEl.name as string;

            // Set result
            let value:number = itemEl.value as number;

            // Push in result
            result = [key, value];

        }

        // Return result
        return result;

    }

    /**
     * Get Multiple
     *
     * @param itemEl:HTMLElement
     * @param options
     * @returns {null|Array<any>[]}
     */
    public getMultiple = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any>[] = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            // Set key
            let key:string = itemEl.name as string;

            // Set result
            let value:number = itemEl.value as number;

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /**
     * Filter Get
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @param options
     * @returns {null|Array<any>}
     */
    public filterGet = (itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Bounds already carry their inclusive comparison or interval operator.
        if(itemEl instanceof HTMLInputElement && itemEl.hasAttribute("data-filter-number-bounds"))
            return itemEl.value ? [itemEl.name, itemEl.value] : null;

        // Get plain key/value
        let result = this.get(itemEl, options);

        // Combine with operator
        if(result){

            // Set operator
            const operator = FormType.getFilterOperatorValue(formEl, result[0]);

            // Set value
            const value = String(result[1]);

            // Only inequality contributes a condition when the number is empty.
            if(value === "" && operator !== "!="){

                // Set result
                result = null;

            }else{

                // Set result
                result = [result[0], value === "" && operator === "!="
                    ? "!="
                    : FormType.combineFilterOperatorValue(operator, value, result[0], options)
                ];

            }

        }

        // Return result
        return result;

    }

    /**
     * Filter Get Multiple
     *
     * @param itemEl:HTMLElement
     * @param formEl:HTMLFormElement
     * @param options
     * @returns {null|Array<any>[]}
     */
    public filterGetMultiple = (itemEl:HTMLElement, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Number inputs contribute a single value, even when configured as multiple.
        const result = this.filterGet(itemEl, formEl, options);

        // Return the condition as a collection
        return result ? [result] : null;

    }

    /**
     * Set
     *
     * Set number in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    public set = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            Form.setId(formEl, valuesID, itemEl);

        }

    }

    /**
     * Set Filter
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @param options
     * @returns {void}
     */
    public filterSet = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Keep restored bounds intact; the paired controls listen to this value.
        if(itemEl instanceof HTMLInputElement && itemEl.hasAttribute("data-filter-number-bounds")){

            // Set value
            itemEl.value = String(value ?? "");

            // Dispatch event
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            Form.setId(formEl, valuesID, itemEl);

        }else

            // Delegate to the regular setter
            this.set(itemEl, value, valuesID, formEl, options);

    }

}
