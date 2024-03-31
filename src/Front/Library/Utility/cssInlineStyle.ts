/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {default as PageError} from './../Error/Page';

/**
 * Css Inline Style
 *
 * Transfer css style into html
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class cssInlineStyle {

    /**
     * @var element:HTMLElement Element processed
     */
    private element:Element;
    
    /**
     * Constructor
     * 
     * @param element 
     */
    constructor(element:Element){

        // Fill element
        this.element = element;

        // Apply
        this._applyInlineStyles();

    }

    /**
     * Apply Inlines Style
     */
    private _applyInlineStyles = ():void => {

        // Apply
        this._recursiveApplyStyles(this.element);

    }

    /**
     * Recursive Apply Styles
     * 
     * @param element 
     */
    private _recursiveApplyStyles = (element:Element) => {

        // Get computedStyle
        var computedStyle = window.getComputedStyle(element);

        // Convert to array
        var styleArray = Array.from(computedStyle);

        // Iteration of style
        for(let property of styleArray)

            // Check style content
            if("style" in element && typeof element.style === "object" && element.style)

                // Fill style
                element.style[property] = computedStyle.getPropertyValue(property);

        // Iteration of children
        Array.from(element.children).forEach(child => {

            // Do the same thing to child node
            this._recursiveApplyStyles(child);
            
        });

    }

    /**
     * Get Html
     * 
     * @returns 
     */
    public getHtml() {

        // Set result
        let result = "";

        // Check element
        if(this.element)

            // Fill result
            result = this.element.outerHTML;

        // return result
        return result;

    }

    /**
     * Copy Html to Clipboard
     * 
     * @returns void
     */
    public copyHtmlToClipboard = ():void => {

        const htmlContent = this.getHtml();
        
        navigator.clipboard.writeText(htmlContent).then(() => {
            console.log('HTML content copied to clipboard');
        }).catch(err => {
            console.error('Failed to copy HTML content to clipboard: ', err);
        });
        
    }

}