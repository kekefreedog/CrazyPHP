/**
 * Crazy Form Adapter
 *
 * Widget initialization and lifecycle contract for CrazyPHP forms.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Form Adapter Options
 *
 * Application settings passed to the selected widget adapter.
 */
export interface CrazyFormAdapterOptions {
    
    /** rich textarea */
    richTextarea?:{
        fontFamily?:string,
        stylesheet?:string,
    };

}

/**
 * Crazy Form Adapter
 */
export default interface CrazyFormAdapter {

    /** Return true when the adapter owns initialization, false to use the existing handler. */
    initialize(input:HTMLInputElement|HTMLSelectElement, form:HTMLFormElement):Promise<boolean>;

    /** Initialize or refresh a filter comparison dropdown. */
    initializeOperator?(input:HTMLSelectElement):void;

    /** Release widgets, observers and listeners, including pending initialization. */
    destroy():void;

}
