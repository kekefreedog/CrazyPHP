/**
 * Index
 *
 * Index of the front script for declare types
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazyobject from "./../Crazyobject";

// Export
export {};

// Declare GLobal type
declare global {

    /**
     * Interface of Window
     */
    interface Window {

        // Add Crazyobject
        Crazyobject: Crazyobject;

    }

    /**
     * Interface key value
     */
    interface ObjectAttribute {
        value:string,
        type:string
    }

    /**
     * Interface Crazyelement
     */
    interface Crazyelement {
        innerHtmlContent: string|null;
    }

    /**
     * Interface Crazycomponent
     */
    interface Crazycomponent {

        /** @var properties Propoerties of the current component */
        abstract properties:Object;

        /** @var crazy Crazy methods for Web Component */
        private crazy:CrazycomponentAction;

        /** Methods
         ******************************************************
         */

        /**
         * Render
         * 
         * Render current template
         * 
         * @return string
         */
        public render():string;

        /**
         * Set Default Properties
         * 
         * @param properties:Object
         * @return
         */
        public setDefaultProperties(properties:Object):void;

        /**
         * Set Html And Css
         * 
         * @param html:Function|string Html or callable html generator
         * @param css:Function|string Css or callable css generator
         * @return void
         */
        public setHtmlAndCss(html:Function|string, css:Function|string):void;

        /**
         * Prepare Collection
         * 
         * Prepare collection for render
         * 
         * @return Object
         */
        private prepareCollectionForRender:Object;

        /** Methods | Callbacks
         ******************************************************
         */

        /**
         * Connected Callback
         */
        public connectedCallback();

        /**
         * Disconnected Callback
         */
        public disconnectedCallback();

        /**
         * Attribute Changed Callback
         */
        public attributeChangedCallback();

        /**
         * Adopted Callback
         */
        public adoptedCallback();

    }

    /**
     * Interface CrazycomponentAction
     */
    interface CrazycomponentAction {

        /** Attributes
         ******************************************************
         */

        /** @var Attributes Collection Default */
        private attributesByDefault:Object = {};

        /** @var attributesCurrent Current attribute */
        private attributesCurrent:Object = {};

        /** @var htmlTemplate Callable function to generate content */
        private htmlTemplate:Function|null;
    
        /** @var htmlContent Content html */
        private htmlContent:string|null;

        /** @var cssTemplate Callable function to generate content */
        private cssTemplate:Function|null;
    
        /** @var cssContent Content html */
        private cssContent:string|null;

        // Style Content
        styleContent: string|null;

        /** Methods | Attributes
         ******************************************************
         */

        /**
         * Set Attributes
         * 
         * Set multiple attributes
         * 
         * @param attributes Attributes object
         * @param setAsDefault Set given attributes as default attributes
         * @return void
         */
        public setAttributes(attributes:Object, setAsDefault:boolean = false):void;

        /**
         * Set Attribute
         * 
         * Set Attribute on current element
         * 
         * @param name 
         * @param value 
         * @return
         */
        public setAttribute(name:string = "", value:string|boolean|Object = ""):void;

        /**
         * Update Attribute
         * 
         * Update attribute on current element
         * 
         * @param name
         * @param value
         * @return
         */
        public updateAttribute(name:string = "", value:string|boolean|Object = ""):void;

        /**
         * Get Attributes Name
         * 
         * @return string[]
         */
        public getAttributesName():string[];

        /**
         * Get Attribute
         * 
         * @param name Name of the attribute to return
         */
        public getAttribute(name:string):string|boolean|null;

        /** Methods | Content
         ******************************************************
         */

        /**
         * Set Content
         * 
         * Set Content string or content callable
         * 
         * @param content Content to put inside webcomponenet
         */
        public setContent(content:string|Function):void;

        /**
         * Get Content
         * 
         * Get Content string of content callable
         * @return string|Function
         */
        public getContent():string|Function;

        /**
         * Set Style
         * 
         * Set Style string or content callable
         * 
         * @param content Content to put inside webcomponenet
         */
        public setStyle(content:string|Function):void;

        /**
         * Get Style
         * 
         * Get Style string of content callable
         * @return string|Function
         */
        public getStyle():string|Function;

    };

    /**
     * Crazy Page
     */
    interface Crazypage {

        /** Name */
        get name():string;

    }

}