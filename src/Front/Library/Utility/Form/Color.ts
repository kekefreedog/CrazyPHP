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
 * Dependances
 */
import type { FormInputTypeHelpers } from './Type';
import type FormInputType from './Type';
import {default as Form} from '../Form';
import Pickr from '@simonwep/pickr';

/**
 * Color Type
 *
 * Handler for "color" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class ColorType implements FormInputType {

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

        // Ingest options
        this._options = {...this._options, ...options};

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Init
     *
     * @param inputEl
     * @returns {void}
     */
    public init = async (inputEl:HTMLSelectElement|HTMLInputElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check pickr
        if(inputEl instanceof HTMLInputElement && "colorPicker" in inputEl.dataset && inputEl.dataset.colorPicker == "pickr"){

            // Create divEl
            let divEl = document.createElement("div");

            // Append el after el
            inputEl.after(divEl);

            // Hide input
            inputEl.classList.add("hide");

            // Prepare options
            let options:Partial<Pickr.Options> = {
                el: divEl,
                theme: "colorTheme" in inputEl.dataset && ['classic', 'monolith', 'nano'].includes(inputEl.dataset.colorTheme as string)
                    ? inputEl.dataset.colorTheme as Pickr.Theme
                    : 'classic'
                ,
                lockOpacity: "colorOpacity" in inputEl.dataset && ['false', '0', '', 'null'].includes(inputEl.dataset.colorOpacity as string)
                    ? true
                    : false
                ,
                swatches: [
                    'rgba(244, 67, 54, 1)',
                    'rgba(233, 30, 99, 0.95)',
                    'rgba(156, 39, 176, 0.9)',
                    'rgba(103, 58, 183, 0.85)',
                    'rgba(63, 81, 181, 0.8)',
                    'rgba(33, 150, 243, 0.75)',
                    'rgba(3, 169, 244, 0.7)',
                    'rgba(0, 188, 212, 0.7)',
                    'rgba(0, 150, 136, 0.75)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(139, 195, 74, 0.85)',
                    'rgba(205, 220, 57, 0.9)',
                    'rgba(255, 235, 59, 0.95)',
                    'rgba(255, 193, 7, 1)'
                ],
                components: {
                    // Main components
                    preview: true,
                    opacity: true,
                    hue: true,
                    // Input / output Options
                    interaction: {
                        hex: true,
                        rgba: true,
                        hsla: true,
                        hsva: true,
                        cmyk: true,
                        input: true,
                        clear: true,
                        save: true
                    }
                }
            };

            // Check locale
            if(inputEl.dataset.colorLocale && ["fr-fr"].includes(inputEl.dataset.colorLocale)){

                // Set i18n
                options.i18n = {

                    // Strings visible in the UI
                   'ui:dialog': 'boîte de dialogue du sélecteur de couleur',
                   'btn:toggle': 'basculer la boîte de dialogue du sélecteur de couleur',
                   'btn:swatch': 'échantillon de couleur',
                   'btn:last-color': 'utiliser la couleur précédente',
                   'btn:save': 'Enregistrer',
                   'btn:cancel': 'Annuler',
                   'btn:clear': 'Effacer',

                   // Strings used for aria-labels
                   'aria:btn:save': 'enregistrer et fermer',
                   'aria:btn:cancel': 'annuler et fermer',
                   'aria:btn:clear': 'effacer et fermer',
                   'aria:input': 'champ de saisie de couleur',
                   'aria:palette': 'zone de sélection des couleurs',
                   'aria:hue': 'curseur de sélection de teinte',
                   'aria:opacity': 'curseur de sélection d\'opacité'

                }

            }

            // Check if input has default
            if(inputEl.hasAttribute("value")){

                // Get default
                var currentValue = inputEl.getAttribute("value");

                // Check current value
                if(currentValue && currentValue != "randomHex()"){

                    // Set default on option
                    options.default = currentValue;

                }else
                // Check if input has default
                if(inputEl.hasAttribute("default")){

                    // Get default
                    var currentDefault = inputEl.getAttribute("default");


                    // Check current value
                    if(currentDefault === "randomHex()"){

                        // Generate a random integer between 0 and 16777215 (0xFFFFFF)
                        const randomColor = Math.floor(Math.random() * 16777216);

                        // Convert to hexadecimal and pad with leading zeros if necessary
                        options.default = `#${randomColor.toString(16).padStart(6, '0')}`;

                    }else
                    // Check currentValue
                    if(currentDefault)

                        // Set default on option
                        options.default = currentDefault;


                }



            }

            // Simple example, see optional options for more configuration.
            const pickr = Pickr.create(options as Pickr.Options);

            // Add event on save
            pickr.on("save", (color:any, instance:any) => {

                // Get color
                let hexa:string = color.toHEXA();

                // Set value on inputEl
                inputEl.value = hexa

                // Close instance
                instance.hide();

            });

            // Add event on input
            inputEl.addEventListener(
                "change",
                (e:Event) => {

                    // Get targelt
                    let el = e.currentTarget;

                    // Check el
                    if(el instanceof HTMLInputElement){

                        // Get value
                        let value = el.value;

                        // Set value in pickr
                        pickr.setColor(value);

                    }

                }
            )

        }

    }

    /**
     * Get
     *
     * @param itemEl:HTMLElement
     * @return null|Array<any>
     */
    public get = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            let key:string = itemEl.name as string;

            // Set result
            let value:string = itemEl.value as string;

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
     * @return null|Array<any>[]
     */
    public getMultiple = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any>[] = null;

        // Check value
        if("value" in itemEl && "name" in itemEl){

            let key:string = itemEl.name as string;

            // Set result
            let value:string = itemEl.value as string;

            // Push in result
            result = [[key, value]];

        }

        // Return result
        return result;

    }

    /**
     * Set
     *
     * Set text in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @return void
     */
    public set = (itemEl:HTMLElement, value:string, valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(itemEl.tagName == "INPUT" && value !== null){

            // Set value
            itemEl.setAttribute("value", value);

            // Dispatch event change
            itemEl.dispatchEvent(new Event("change"));

            // Set id
            Form.setId(formEl, valuesID, itemEl);

        }

    }

}
