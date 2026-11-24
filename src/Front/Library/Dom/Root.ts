/**
 * DOM Root
 *
 * Update the live page root from rendered HTML.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import PageError from "../Error/Page";

/**
 * Root
 *
 * Locate the page wrapper and reconcile its DOM with incoming markup.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Root {

    /** Public Constants
     ******************************************************
     */

    /** @var id:string ID of the page wrapper */
    public static readonly id:string = "crazy-root";

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get ID
     *
     * Read the configured ID of the page wrapper.
     *
     * @return string
     */
    public static getId = ():string => {

        // Prepare the root ID
        const result = Root.id;

        // Check for a missing ID
        if(!result)

            // Report the missing root ID
            throw new PageError("ID of the root is not set.");

        // Return id
        return result;

    }

    /**
     * Get El
     *
     * Find the current page wrapper.
     *
     * @return HTMLElement
     * @throws PageError When the wrapper is missing
     */
    public static getEl = ():HTMLElement => {

        // Prepare the root lookup result
        const result = document.getElementById(Root.id);

        // Check for a missing wrapper
        if(result === null)

            // Report the missing wrapper
            throw new PageError(`Element "#${Root.id}" does not exist in the dom.`);

        // Return result
        return result;

    }

    /**
     * Check El
     *
     * Check whether the page wrapper exists.
     *
     * @return boolean
     */
    public static checkEl = ():boolean => {

        // Check for an HTML page wrapper
        const result = document.getElementById(Root.id) instanceof HTMLElement;

        // Return result
        return result;

    }

    /**
     * Set Content
     *
     * Apply rendered page markup to the live wrapper.
     *
     * @param content Rendered page markup
     * @return HTMLElement
     */
    public static setContent = (content:string = ""):HTMLElement => {

        // Resolve the live page root
        let result = Root.getEl();

        // Parse the incoming page
        const page = new DOMParser().parseFromString(content, "text/html");

        // Prefer the designated wrapper when a page contains leading elements
        const incoming = page.getElementById(Root.id) ?? page.body.firstElementChild;

        // Keep the public root an HTML element
        if(incoming && incoming.namespaceURI === result.namespaceURI){

            // Preserve the lookup contract for older templates
            incoming.id = Root.id;

            // Patch a compatible wrapper
            if(Root._compatible(result, incoming)){

                // Update the existing root
                Root._patchElement(result, incoming);

            }else{

                // Import the new wrapper into the live document
                const replacement = result.ownerDocument.importNode(incoming, true) as HTMLElement;

                // Install the replacement
                result.replaceWith(replacement);

                // Store the connected wrapper
                result = replacement;

            }

        }

        // Return the current page root
        return result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Check Node Compatibility
     *
     * Check whether a node can represent the incoming markup in place.
     *
     * @param current Live node
     * @param incoming Template node
     * @return boolean
     */
    private static _compatible = (current:Node, incoming:Node):boolean => {

        // Compare node kind, element name and namespace
        const result =
            current.nodeType === incoming.nodeType &&
            current.nodeName === incoming.nodeName &&
            (current as Element).namespaceURI === (incoming as Element).namespaceURI &&
            (current as Element).getAttribute?.("is") === (incoming as Element).getAttribute?.("is")
        ;

        // Return compatibility
        return result;

    }

    /**
     * Get Sibling Key
     *
     * Read a sibling identity, preferring an explicit key.
     *
     * @param node Sibling to identify
     * @return string
     */
    private static _key = (node:Node):string => {

        // Read the element key when available
        const result = (node as Element).getAttribute?.("data-key") || (node as Element).id || "";

        // Return the sibling key
        return result;

    }

    /**
     * Reconcile Siblings
     *
     * Match siblings before moving them so edits and listeners follow their items.
     *
     * @param previous Existing sibling nodes
     * @param incoming Template sibling nodes
     * @param owner Document for new nodes
     * @return Node[]
     */
    private static _reconcile = (previous:Node[], incoming:Node[], owner:Document):Node[] => {

        // Prepare the requested sibling sequence
        const result:Node[] = [];

        // Index old siblings by key
        const keyed = new Map<string, Node>();

        // Track ambiguous keys on either side
        const duplicates = new Set<string>();

        // Inspect each sibling list independently
        for(const siblings of [previous, incoming]){

            // Track occurrences on this side
            const seen = new Set<string>();

            // Inspect each sibling identity
            for(const node of siblings){

                // Resolve the sibling key
                const key = Root._key(node);

                // Mark repeated keys for positional matching
                if(seen.has(key))

                    // Record the ambiguous key
                    duplicates.add(key);

                // Record this occurrence
                seen.add(key);

                // Index the existing node
                if(siblings === previous)

                    // Index the live sibling
                    keyed.set(key, node);

            }

        }

        // Preserve relative order among positional candidates
        const unkeyed = previous.filter(node => !Root._key(node) || duplicates.has(Root._key(node)));

        // Track the next positional match
        let position = 0;

        // Match each incoming node
        for(const next of incoming){

            // Resolve its sibling key
            const key = Root._key(next);

            // Consume the appropriate candidate
            const current = key && !duplicates.has(key) ? keyed.get(key) : unkeyed[position++];

            // Reuse a compatible node
            if(current && Root._compatible(current, next)){

                // Reconcile element state or character data
                if(current.nodeType === Node.ELEMENT_NODE){

                    // Patch the matched element
                    Root._patchElement(current as Element, next as Element);

                }else if(current.nodeValue !== next.nodeValue){

                    // Preserve exact text or comment content
                    current.nodeValue = next.nodeValue;

                }

                // Retain the matched node
                result.push(current);

            }else{

                // Create a fresh node for a new identity
                result.push(owner.importNode(next, true));

            }

        }

        // Index the nodes retained by the template
        const retained = new Set(result);

        // Visit existing nodes
        for(const node of previous){

            // Remove an unmatched node
            if(!retained.has(node))

                // Detach the obsolete node
                node.parentNode?.removeChild(node);

        }

        // Return the desired sequence
        return result;

    }

    /**
     * Patch Children
     *
     * Place ordinary children in template order.
     *
     * @param current Live parent
     * @param incoming Template parent
     * @return void
     */
    private static _patchChildren = (current:Element|DocumentFragment, incoming:Element|DocumentFragment):void => {

        // Skip leaf elements and update a lone text node without allocating sibling indexes
        if(
            current.firstChild?.nodeType === Node.TEXT_NODE &&
            current.firstChild === current.lastChild &&
            incoming.firstChild?.nodeType === Node.TEXT_NODE &&
            incoming.firstChild === incoming.lastChild
        ){

            // Preserve text identity when the contents change
            if(current.firstChild.nodeValue !== incoming.firstChild.nodeValue)

                // Update the existing text node
                current.firstChild.nodeValue = incoming.firstChild.nodeValue;

        }else if(current.firstChild || incoming.firstChild){

            // Resolve the final child sequence
            const children = Root._reconcile(Array.from(current.childNodes), Array.from(incoming.childNodes), current.ownerDocument);

            // Track the next sibling already in place
            let cursor = current.firstChild;

            // Install each child at its requested position
            for(const child of children){

                // Move only nodes that are out of place
                if(child !== cursor)

                    // Move the child into position
                    current.insertBefore(child, cursor);

                // Advance past the installed child
                cursor = child.nextSibling;

            }

        }

    }

    /**
     * Patch Element
     *
     * Synchronize markup while respecting component and form ownership.
     *
     * @param current Live element
     * @param incoming Template element
     * @return void
     */
    private static _patchElement = (current:Element, incoming:Element):void => {

        // Capture form defaults before attribute or child updates
        const restore = Root._formUpdate(current, incoming);

        // Synchronize host attributes
        Root._syncAttributes(current, incoming);

        // Resolve the live custom element definition
        const custom = current.ownerDocument.defaultView?.customElements.get(current.getAttribute("is") || current.localName);

        // Read the component's optional light DOM reconciliation hook
        const host = current as PatchableHost;

        // Delegate custom element content according to its ownership
        if(custom){

            // Patch supplied children of an open shadow host
            if(current.shadowRoot){

                // Leave native projection to the browser
                Root._patchChildren(current, incoming);

            }else if(typeof host.patchSuppliedChildren === "function"){

                // Reconcile the component's logical sequence of supplied nodes
                host.patchSuppliedChildren(nodes => Root._reconcile(nodes, Array.from(incoming.childNodes), current.ownerDocument));

            }

        }else if(incoming.hasAttribute("partial")){

            // Read the requested refresh mode
            const refresh = incoming.getAttribute("partial-refresh") === "true";

            // Rebuild changed or explicitly refreshed partials
            if(refresh || current.innerHTML !== incoming.innerHTML)

                // Replace the partial contents
                current.innerHTML = incoming.innerHTML;

            // Consume the one-shot refresh flag
            if(refresh)

                // Clear the refresh flag
                current.removeAttribute("partial-refresh");

        }else if(current.localName === "template" && "content" in current){

            // Patch the inert template fragment
            Root._patchChildren((current as HTMLTemplateElement).content, (incoming as HTMLTemplateElement).content);

        }else{

            // Patch ordinary children
            Root._patchChildren(current, incoming);

        }

        // Apply form state after option and textarea children settle
        restore?.();

    }

    /**
     * Prepare Form Update
     *
     * Prepare live form updates only where template defaults change.
     *
     * @param current Live control
     * @param incoming Template control
     * @return Deferred update callback or undefined
     */
    private static _formUpdate = (current:Element, incoming:Element):(() => void)|undefined => {

        // Prepare the deferred form update
        let result:(() => void)|undefined = undefined;

        // Inspect native HTML controls
        if(current.namespaceURI === "http://www.w3.org/1999/xhtml"){

            // Compare input defaults before updating attributes
            if(current.localName === "input"){

                // Resolve the live control
                const input = current as HTMLInputElement;

                // Resolve the template control
                const next = incoming as HTMLInputElement;

                // Detect a changed text default
                const valueChanged = input.defaultValue !== next.defaultValue;

                // Detect a changed checked default
                const checkedChanged = input.defaultChecked !== next.defaultChecked;

                // Apply changed defaults after the input type has been updated
                result = () => {

                    // Update editable values without assigning file paths
                    if(valueChanged && input.type !== "file" && input.value !== next.value)

                        // Apply the template value
                        input.value = next.value;

                    // Apply a changed checked default
                    if(checkedChanged)

                        // Apply the template checked state
                        input.checked = next.defaultChecked;

                };

            }else if(current.localName === "textarea"){

                // Resolve the incoming default text
                const value = (incoming as HTMLTextAreaElement).defaultValue;

                // Replace a live edit only when the template changes
                if((current as HTMLTextAreaElement).defaultValue !== value){

                    // Defer the live textarea update
                    result = () => {

                        // Apply the new textarea value
                        (current as HTMLTextAreaElement).value = value;

                    };

                }

            }else 
            // Check if select    
            if(current.localName === "select"){

                // Resolve the live control
                const select = current as HTMLSelectElement;

                // Record whether the control had any choices
                const hadOptions = select.options.length > 0;

                // Record explicit defaults, including their submitted values
                const previousOptions = Array.from(select.options);

                // Resolve the selected options a reset would produce
                const explicit = previousOptions.filter(option => option.defaultSelected);

                // Resolve an implicit single-select default when no option declares one
                const implicit = !select.multiple && select.size <= 1 && !explicit.length
                    ? previousOptions.find(option => !option.matches(":disabled") && !option.closest("optgroup[disabled]"))
                    : undefined
                ;

                // Keep the identities and values of effective defaults
                const defaults = new Map((implicit ? [implicit] : select.multiple ? explicit : explicit.slice(-1))
                    .map(option => [option, option.value] as const));

                // Snapshot live option identities
                const selected = new Set(Array.from(select.selectedOptions));

                // Reconcile selection after options move
                result = () => {

                    // Inspect the updated option sequence
                    const options = Array.from(select.options);

                    // Read the browser's resolved incoming selection
                    const template = (incoming as HTMLSelectElement).options;

                    // Compare effective defaults after matching option identities
                    const incomingDefaults = options.filter((_, index) => template[index]?.selected);

                    // Detect changed default options or submitted values
                    const changed = incomingDefaults.length !== defaults.size || incomingDefaults.some(option => defaults.get(option) !== option.value);

                    // Restore surviving choices or accept the template when all choices disappeared
                    const useTemplate = !hadOptions || changed || (selected.size > 0 && !options.some(option => selected.has(option)));

                    // Resolve desired selection before changing the live control
                    const desired = useTemplate ? new Set(incomingDefaults) : selected;

                    // Update the live selection only when necessary
                    if(options.some(option => option.selected !== desired.has(option))){

                        // Clear automatic or stale selections
                        select.selectedIndex = -1;

                        // Select the desired options
                        for(const option of options){

                            // Restore this selected option
                            if(desired.has(option))

                                // Select the retained option
                                option.selected = true;

                        }

                    }

                };

            }

        }

        // Return the deferred update
        return result;

    }

    /**
     * Sync Attributes
     *
     * Synchronize attributes with their original namespaces.
     *
     * @param current Live element
     * @param incoming Template element
     * @return void
     */
    private static _syncAttributes = (current:Element, incoming:Element):void => {

        // Visit current attributes
        for(const attr of Array.from(current.attributes)){

            // Remove absent attributes
            if(!incoming.hasAttributeNS(attr.namespaceURI, attr.localName))

                // Remove the obsolete attribute
                current.removeAttributeNode(attr);

        }

        // Visit incoming attributes
        for(const attr of Array.from(incoming.attributes)){

            // Update changed values
            if(current.getAttributeNS(attr.namespaceURI, attr.localName) !== attr.value){

                // Preserve attributes with a namespace
                if(attr.namespaceURI){

                    // Apply the qualified attribute
                    current.setAttributeNS(attr.namespaceURI, attr.name, attr.value);

                }else{

                    // Accept ordinary HTML names including colons
                    current.setAttribute(attr.name, attr.value);

                }

            }

        }

    }

}

/** Interface
 ******************************************************
 */

/** Optional component contract for reconciling caller-supplied light DOM. */
interface PatchableHost extends Element {

    /** Reconcile children supplied by the page template. */
    patchSuppliedChildren?:(reconcile:(children:Node[]) => Node[]) => void;

}
