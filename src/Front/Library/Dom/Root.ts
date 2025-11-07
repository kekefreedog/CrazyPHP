/**
 * Dom
 *
 * Front TS Scrips for load elements
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
 * Root
 *
 * Methods for manipulate dom root
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Root {

    /** Public Constants
     ******************************************************
     */

    /** @var id:string Id of the root
     * 
     */
    public static readonly id:string = "crazy-root";

    /** Public static methods
     ******************************************************
     */

    /**
     * Get ID
     * 
     * Get Id of the root element
     * 
     * @return string
     */
    public static getId = ():string => {

        // Check id
        if(!Root.id)

            // New error
            throw new PageError("ID of the root is not set.");

        // Return id
        return Root.id;
    }

    /**
     * Get El
     * 
     * Get Root Element
     * 
     * @return HTMLElement
     */
    public static getEl = ():HTMLElement => {

        // Get id
        const id:string = Root.id;

        // Get el
        let result:HTMLElement|null = document.getElementById(id);

        // Check result is null
        if(result === null)

            // New error
            throw new PageError(`Element "#${id}" does not exist in the dom.`);

        // Return result
        return result;

    }

    /**
     * Check El
     * 
     * Check Root Element
     * 
     * @return HTMLElement
     */
    public static checkEl = ():boolean => {

        // Get id
        const id:string = Root.id;

        // Get el
        let result:boolean = false;

        // Check result is null
        if(document.getElementById(id) instanceof HTMLElement) result = true;

        // Return result
        return result;

    }

    /**
     * Set Content
     * 
     * Set Content of the Root 
     * 
     * @returns {HTMLElement}
     */
    public static setContent = (content:string = ""):HTMLElement => {

        // Get crazy root
        let rootEl:HTMLElement = Root.getEl();

        // Parse new HTML into DOM
        let parser = new DOMParser();

        // Create new DOM tree from content
        let newDoc = parser.parseFromString(content, "text/html");

        // Extract the first element inside <body>
        let newRoot = newDoc.body.firstElementChild;

        // If no content or invalid HTML → exit
        if(!newRoot) return rootEl;

        // Patch the existing root content instead of replacing it
        this._patchElement(rootEl, newRoot);

        // Return the updated root
        return rootEl;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Patch Element
     * 
     * Reuse DOM elements instead of replacing them completely
     * Handle custom elements, partial HTML, and refresh flags correctly
     * 
     * @param oldEl Current DOM element in the page
     * @param newEl New element from parsed HTML
     * @returns {void}
     */
    private static _patchElement = (oldEl: Element, newEl: Element): void => {

        // If different tag → replace the node entirely
        if (oldEl.tagName !== newEl.tagName) {
            oldEl.replaceWith(newEl);
            return;
        }

        // Check if element is a registered custom element (e.g. <regular-btn>)
        const tag = oldEl.tagName.toLowerCase();
        const isCustom = !!customElements.get(tag);

        // If custom element → only sync attributes, never patch inside shadow DOM
        if (isCustom) {
            this._syncAttributes(oldEl, newEl);
            return;
        }

        // Sync attributes between old and new
        this._syncAttributes(oldEl, newEl);

        // Handle elements with "partial" attribute (used for Handlebars partials)
        if (oldEl.hasAttribute("partial")) {

            // Check if a forced refresh is required
            const forceRefresh = oldEl.getAttribute("partial-refresh") === "true";

            // Get new HTML content
            const newHtml = newEl.innerHTML.trim();
            const oldHtml = oldEl.innerHTML.trim();

            // If changed or forced → replace innerHTML (render real HTML, not text)
            if (forceRefresh || oldHtml !== newHtml) {

                // Replace content safely
                oldEl.innerHTML = newHtml;

                // Reset refresh flag if needed
                if (forceRefresh) oldEl.removeAttribute("partial-refresh");
            }

            // Stop recursion inside this partial
            return;
        }

        // If both are text-only nodes → update text or real HTML
        if (!oldEl.hasChildNodes() || !newEl.hasChildNodes()) {
            const newHtml = newEl.innerHTML.trim();

            // If new HTML contains tags → render it as DOM
            if (newHtml.includes("<")) {
                oldEl.innerHTML = newHtml;
            } else if (oldEl.textContent !== newEl.textContent) {
                oldEl.textContent = newEl.textContent;
            }

            return;
        }

        // Convert children to arrays
        const oldChildren = Array.from(oldEl.childNodes);
        const newChildren = Array.from(newEl.childNodes);

        // Loop through maximum child count
        const max = Math.max(oldChildren.length, newChildren.length);

        for (let i = 0; i < max; i++) {

            const oldChild = oldChildren[i];
            const newChild = newChildren[i];

            // If new child doesn't exist → remove old
            if (oldChild && !newChild) {
                oldChild.remove();
                continue;
            }

            // If old child doesn't exist → append new
            if (!oldChild && newChild) {
                oldEl.appendChild(newChild.cloneNode(true));
                continue;
            }

            // If both text nodes → update content if changed
            if (oldChild?.nodeType === Node.TEXT_NODE && newChild?.nodeType === Node.TEXT_NODE) {
                if (oldChild.textContent !== newChild.textContent)
                    oldChild.textContent = newChild.textContent;
                continue;
            }

            // If both element nodes → recurse patch
            if (oldChild?.nodeType === Node.ELEMENT_NODE && newChild?.nodeType === Node.ELEMENT_NODE) {
                this._patchElement(oldChild as Element, newChild as Element);
                continue;
            }

            // Otherwise replace node entirely
            if (oldChild && newChild) {
                oldChild.replaceWith(newChild.cloneNode(true));
            }

        }

    }

    /**
     * Sync Attributes
     * 
     * Update or remove attributes as needed
     * 
     * @param oldEl 
     * @param newEl 
     * @returns {void}
     */
    private static _syncAttributes = (oldEl: Element, newEl: Element): void => {

        // Remove old attributes not in new element
        for (const attr of Array.from(oldEl.attributes)) {
            if (!newEl.hasAttribute(attr.name))
                oldEl.removeAttribute(attr.name);
        }

        // Add / update attributes from new element
        for (const attr of Array.from(newEl.attributes)) {
            if (oldEl.getAttribute(attr.name) !== attr.value)
                oldEl.setAttribute(attr.name, attr.value);
        }

    }


}