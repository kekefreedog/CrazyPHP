/**
 * Front
 *
 * Front TS scripts for your Crazy App.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Crazycomponent2
 *
 * Reactive web components using compiled Handlebars templates and SCSS styles.
 * Supports light DOM projection or native slots inside an open shadow root.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
export default abstract class Crazycomponent2<T extends object = Record<string, unknown>> extends HTMLElement {

    /** Static Parameters
     ******************************************************
     */

    /** @var properties Property schema, available before custom element registration */
    public static properties: Crazycomponent2Properties = {};

    /** @var template Compiled HBS function, HTML string, or module export */
    public static template: Crazycomponent2Template = "";

    /** @var styles Compiled CSS, context function, or css-loader export */
    public static styles: Crazycomponent2Styles = "";

    /** @var options Default rendering mode for instances of the component */
    public static options: Crazycomponent2Options = {
        shadow: false
    };

    /** Public Parameters
     ******************************************************
     */

    /** @var renderRoot Query this element or shadow root in component hooks */
    public readonly renderRoot: HTMLElement | ShadowRoot;

    /** @var updateComplete Resolves true after rendering, false if disconnected before the update */
    public updateComplete: Promise<boolean> = Promise.resolve(false);

    /** Private Parameters
     ******************************************************
     */

    /** @var _values Current typed values, independent from the static schema */
    private _values: Record<string, unknown> = Object.create(null);

    /** @var _initialized Whether defaults have been validated and copied */
    private _initialized = false;

    /** @var _pending Whether a render microtask is already queued */
    private _pending = false;

    /** @var _reflecting Prevent attribute reflection from feeding back into property updates */
    private _reflecting = false;

    /** @var _template Optional template override for this instance */
    private _template?: Crazycomponent2Template;

    /** @var _styles Optional stylesheet override for this instance */
    private _styles?: Crazycomponent2Styles;

    /** @var _cleanups Resources to release before rerendering or disconnecting */
    private _cleanups: Array<() => void> = [];

    /** @var _observer Observer for light DOM child changes */
    private _observer: MutationObserver | null = null;

    /** @var _children Supplied child nodes in their projection order */
    private _children: Node[] = [];

    /** @var _ownedRoots Template roots, excluded when collecting supplied children */
    private _ownedRoots = new Set<Node>();

    /** @var _slots Light DOM insertion points and their original fallback content */
    private _slots: LightSlot[] = [];

    /** @var _parking Retained children without a matching light DOM slot */
    private _parking: DocumentFragment;

    /**
     * Constructor
     *
     * Choose the rendering root without reading attributes or supplied children.
     * Subclass fields are initialized after this constructor returns.
     *
     * @param options Rendering options overriding the static defaults
     */
    public constructor(options: Crazycomponent2Options = {}) {

        // Construct the native element before accessing the subclass configuration.
        super();

        // Constructor options take precedence over the component's static defaults.
        const configuration = { 
            ...this.component.options, 
            ...options 
        };

        // Set render root
        this.renderRoot = configuration.shadow 
            ? this.attachShadow({ mode: "open" }) 
            : this
        ;

        // Keep unmatched light DOM children alive without displaying them.
        this._parking = this.ownerDocument.createDocumentFragment();

    }

    /** Methods | Events
     ******************************************************
     */

    /**
     * Post Render
     *
     * Called after the generated markup and projected children have been mounted.
     * Override to install event handlers or widgets, paired with onCleanup().
     *
     * @return void
     */
    public postRender(): void {

        // Component subclasses can attach their behavior after rendering.
    }

    /**
     * On Cleanup
     *
     * Register a resource disposer for the current rendered content.
     *
     * @param cleanup Callback executed before rerendering or disconnecting
     * @return void
     */
    public onCleanup(cleanup: () => void): void {

        // On clean up of component
        this._cleanups.push(cleanup);

    }

    /** Public Methods | Properties
     ******************************************************
     */

    /**
     * Get Property
     *
     * Read a typed value, initializing per-instance defaults when necessary.
     *
     * @param name Declared property name
     * @return Current property value
     * @throws TypeError When the property is not declared
     */
    public getProperty<K extends keyof T & string>(name: K): T[K] {

        // Set definition
        this.definition(name);

        // Initalize
        this.initialize();

        // Return values
        return this._values[name] as T[K];

    }

    /**
     * Set Property
     *
     * Update a typed value and optionally reflect it to the mapped HTML attribute.
     * Programmatic values must already match the declared type.
     *
     * @param name Declared property name
     * @param value New typed value
     * @return void
     * @throws TypeError When the value is invalid or cannot be serialized
     */
    public setProperty<K extends keyof T & string>(name: K, value: T[K]): void {

        // Set property
        const property = this.definition(name);

        // Initalize
        this.initialize();

        // Checi if not valid
        if(!this.valid(value, property)) 
            
            // Invalid compoenent
            throw new TypeError(`Invalid component property: ${name}`);

        // Set attribute
        const attribute = this.component.attributeName(name, property);

        // Serialize before changing state: circular JSON must not partially update it.
        const serialized = property.reflect && attribute !== null
            ? (
                property.type === "array" || property.type === "object" 
                    ? JSON.stringify(value)
                    : String(value)
            )
            : null;

        // Set changed
        const changed = !Object.is(this._values[name], value);

        // Set property
        this._values[name] = value;

        // Reflected writes must not trigger a second conversion or render request.
        if (serialized !== null && attribute !== null) {

            // Set reflecting
            this._reflecting = true;

            // Try
            try {

                // Chec attribute not serialize
                if(this.getAttribute(attribute) !== serialized) 
                    
                    // Set attribute
                    this.setAttribute(attribute, serialized);

            }finally{

                // Set reflecting
                this._reflecting = false;

            }

        }

        // Checj change
        if(changed) 
            
            // Request update
            this.requestUpdate();

    }

    /** Public Methods | Rendering
     ******************************************************
     */

    /**
     * Set Html And Css
     *
     * Override the static assets for one instance, including constructor-based setup.
     *
     * @param html HTML string, compiled template, or module export
     * @param css CSS string, context function, or css-loader export
     * @return void
     */
    public setHtmlAndCss(html:Crazycomponent2Template, css:Crazycomponent2Styles):void {


        // Set template
        this._template = html;
        
        // Set style
        this._styles = css;
        
        // Request update
        this.requestUpdate();

    }

    /**
     * Render
     *
     * Evaluate the template without mounting it or changing supplied children.
     * Styles are mounted separately during the scheduled update.
     *
     * @return Rendered HTML
     */
    public render(): string {

        // Get template
        let template = this._template ?? this.component.template;
        
        // Iteration template
        while (typeof template === "object") 
            
            // Update template
            template = template.default;

        // Return template
        return typeof template === "function" 
            ? template(this.prepareContext()) 
            : template
        ;

    }

    /**
     * Request Update
     *
     * Batch synchronous changes into one render microtask.
     * Changes made while disconnected are rendered on the next connection.
     *
     * @return Promise resolving whether the queued update rendered
     */
    public requestUpdate():Promise<boolean> {

        // Reuse the pending update so synchronous property changes render together.
        if (this._pending) 
            
            // Reuse
            return this.updateComplete;

        // Set pending
        this._pending = true;

        // Update complete
        this.updateComplete = Promise.resolve().then(() => {

            // Release the queue before rendering so hooks can request a later update.
            this._pending = false;

            // Check is connected
            if (!this.isConnected) 
                
                // Return false
                return false;

            // Update
            this.update();

            // Return true
            return true;

        });

        // Return update complete
        return this.updateComplete;

    }

    /** Protected Methods
     ******************************************************
     */

    /**
     * Prepare Context
     *
     * Build the template data using the existing attributes/name convention.
     * Override to add component-specific context.
     *
     * @return Template context
     */
    protected prepareContext(): Crazycomponent2Context {

        // Initerliaze
        this.initialize();

        // Return attributes
        return { 
            attributes: { 
                ...this._values 
            }, 
            name: this.localName 
        };

    }

    /** Private Methods | Properties
     ******************************************************
     */

    /**
     * Component
     *
     * Access declarations on the concrete subclass rather than instance fields.
     *
     * @return Component constructor
     */
    private get component():typeof Crazycomponent2 {

        // Return constructor
        return this.constructor as typeof Crazycomponent2;

    }

    /**
     * Definition
     *
     * Resolve an own schema entry, rejecting undeclared property names.
     *
     * @param name Property name
     * @return Property definition
     */
    private definition(name: string): Crazycomponent2Property {

        // Chheck property
        if(!Object.prototype.hasOwnProperty.call(this.component.properties, name)) 

            // Throw new error
            throw new TypeError(`Unknown component property: ${name}`);
        
        // Return propertie
        return this.component.properties[name];

    }

    /**
     * Clone
     *
     * Copy JSON-compatible defaults recursively so instances do not share objects.
     *
     * @param value Default value to copy
     * @return Independent copy of the value
     */
    private clone(value:unknown):unknown {

        // Check value is array
        if(Array.isArray(value)) 
            
            // Return close
            return value.map(item => this.clone(item));

        // Check value
        if(value !== null && typeof value === "object"){

            // Set result
            const result:Record<string, unknown> = {};

            // Iteration value
            for(const key of Object.keys(value)){

                // Define preperty
                Object.defineProperty(
                    result, 
                    key, {
                        value: this.clone((value as Record<string, unknown>)[key]),
                        enumerable: true, configurable: true, writable: true,
                    }
                );

            }

            // Return result
            return result;

        }

        // Return value
        return value;

    }

    /**
     * Default Value
     *
     * Use the declared default or the empty value for the declared type.
     *
     * @param property Property definition
     * @return Fresh default value
     */
    private defaultValue(property:Crazycomponent2Property):unknown {

        // Check default propoerty
        if (property.default !== undefined) 
            
            // Return clone
            return this.clone(property.default);

        // Switch
        switch (property.type) {
            case "string": return "";
            case "number": return 0;
            case "boolean": return false;
            case "array": return [];
            case "object": return {};
        }

    }

    /**
     * Valud
     *
     * Check the runtime type and any allowed scalar values.
     *
     * @param value Value to validate
     * @param property Property definition
     * @return Whether the value matches the schema
     */
    private valid(value:unknown, property:Crazycomponent2Property):boolean {

        // Set matches
        let matches:boolean;

        // Switch
        switch (property.type){

            // Case number
            case "number": matches = typeof value === "number" && Number.isFinite(value); break;
            
            // Case array
            case "array": matches = Array.isArray(value); break;
            
            // Case object
            case "object": matches = Object.prototype.toString.call(value) === "[object Object]"; break;
            
            // Case default
            default: matches = typeof value === property.type;
        }

        // Return matches
        return matches && (
            !property.select || (
                property.select as readonly unknown[]
            ).includes(value)
        );

    }

    /**
     * Initialize
     *
     * Validate the schema and create each instance's initial values once.
     *
     * @return void
     * @throws TypeError When defaults or reflection options are inconsistent
     */
    private initialize():void {

        // Set initialized
        if (this._initialized) 
            
            // Return
            return;

        // Iteration properties
        for (const name of Object.keys(this.component.properties)) {

            // Get property
            const property = this.definition(name);
            
            // Get value
            const value = this.defaultValue(property);

            // Check not valud
            if (!this.valid(value, property))

                // New error
                throw new TypeError(`Invalid default for component property: ${name}`);
            
            // Check reflect and no attribute
            if (property.reflect && property.attribute === false)

                // New error
                throw new TypeError(`Reflected property must have an attribute: ${name}`);
            
            // Set value
            this._values[name] = value;

        }

        // Set initialized
        this._initialized = true;

    }

    /**
     * From Attribute
     *
     * Convert HTML strings to typed values. Invalid or removed attributes restore
     * the declared default, including explicit false and zero values.
     *
     * @param value HTML attribute value, or null when removed
     * @param property Property definition
     * @return Converted value or default
     */
    private fromAttribute(value:string|null, property:Crazycomponent2Property):unknown {

        // Set value
        if (value === null) 
            
            // Return default value
            return this.defaultValue(property);

        // Set parsed
        let parsed: unknown = value;

        // Switch
        switch (property.type) {

            // Case number
            case "number": parsed = value.trim() === "" 
                ? NaN 
                : Number(value); 
            break;

            // Case booloean
            case "boolean": {

                // Set normaized
                const normalized = value.trim().toLowerCase();

                // set parsed
                parsed = ["", "true", "1"].includes(normalized) 
                    ? true
                    : (
                        ["false", "0"].includes(normalized) 
                            ? false
                            : undefined
                        
                    )
                ;

                // Break
                break;
            }

            // Case array
            case "array":
            // Case Object
            case "object":

                // Try
                try { 
                    
                    // Get parsed
                    parsed = JSON.parse(value); 
                
                }catch{ 
                    
                    // Set parsed
                    parsed = undefined;
                
                }

                // Break
                break;

        }

        // Return valid
        return this.valid(parsed, property) 
            ? parsed 
            : this.defaultValue(property)
        ;

    }

    /** Private Methods | Rendering
     ******************************************************
     */

    /**
     * Style Text
     *
     * Normalize styles while retaining css-loader's CSS-aware serialization.
     *
     * @return CSS text
     */
    private styleText(): string {

        // Get styles
        let styles = this._styles ?? this.component.styles;

        // Iteration styles
        while(typeof styles === "object" && "default" in styles) 
            
            // Set default
            styles = styles.default;

        
        // Check if function
        if(typeof styles === "function") 
            
            // Return style
            return styles(this.prepareContext());

        // Check if string
        if(typeof styles === "string") 
            
            // Return styles
            return styles;

        // css-loader exports a list with its own CSS-aware toString().
        if(styles.toString !== Object.prototype.toString && styles.toString !== Array.prototype.toString)

            // Return to string
            return styles.toString();
        
        // Error
        throw new TypeError("Component styles must be CSS text or a css-loader export.");

    }

    /**
     * Update
     *
     * Prepare the new markup before replacing the current render.
     * Retain supplied light DOM nodes and mount them into the new insertion points.
     *
     * @return void
     */
    private update(): void {

        // Initialize
        this.initialize();

        // Evaluate both assets before disturbing the currently mounted content.
        const template = this.ownerDocument.createElement("template");

        // Set template
        template.innerHTML = this.render();

        // Set css
        const css = this.styleText();

        // Check css 
        if(css){

            // Check style
            const style = this.ownerDocument.createElement("style");
            
            // Set text content
            style.textContent = css;

            // Append
            template.content.prepend(style);
        }

        // Internal node moves must not be interpreted as new supplied children.
        this._observer?.disconnect();

        // Try
        try{

            // Clean up
            this.cleanup();

            // Set render root
            if(this.renderRoot === this){

                // Save original nodes rather than cloning their markup and losing state.
                this.collectChildren();

                // Iteration children
                for (const node of this._children) 
                    
                    // Apoend node
                    this._parking.appendChild(node);

                // A nested custom element owns its own slots.
                this._slots = Array.from(template.content.querySelectorAll("slot"))
                    .filter(slot => {
                        for (let parent = slot.parentElement; parent; parent = parent.parentElement) {
                            if (parent.localName.includes("-")) return false;
                        }
                        return true;
                    })
                    .map(element => ({ element, fallback: Array.from(element.childNodes) }))
                ;

                // Set onwned roots
                this._ownedRoots = new Set(template.content.childNodes);

            }

            // Native shadow slots project automatically; light DOM requires explicit moves.
            this.renderRoot.replaceChildren(template.content);

            // Check render root
            if(this.renderRoot === this)
                
                // Project children
                this.projectChildren();

        }finally{

            // Observe children
            this.observeChildren();

        }

        // Hooks see the complete render, including any supplied content.
        this.postRender();

    }

    /**
     * Cleanup
     *
     * Release resources in reverse registration order.
     * Run every disposer even if one fails, then propagate the last error.
     *
     * @return void
     */
    private cleanup(): void {

        // GEt callbacks
        const callbacks = this._cleanups.splice(0).reverse();

        // Set failure
        let failure:unknown;

        // Set failed
        let failed = false;

        // Iteration callbacks
        for(const callback of callbacks)

            // Try
            try{ 
                
                // Callback
                callback(); 
            
            }catch(error){ 
                
                // Set failed
                failed = true; 
                
                // Set failure
                failure = error; 
            
            }
        
        // Check fail
        if (failed) 
            
            // Throw failure
            throw failure;

    }

    /** Private Methods | Children
     ******************************************************
     */

    /**
     * Patch Supplied Children
     * 
     * Reconcile supplied light DOM nodes, including currently parked children.
     *
     * @param reconcile Patch the logical children and return their new order
     */
    public patchSuppliedChildren(reconcile:(children:Node[]) => Node[]):void {

        // Suspend observation during projection
        this._observer?.disconnect();

        // Restore observation even if a child throws during reconciliation
        try{

            // Include recently added children
            this.collectChildren();

            // Update the logical child sequence
            this._children = reconcile(this._children);

            // Project the updated sequence
            this.projectChildren();

        }finally{

            // Resume observing application changes
            this.observeChildren();

        }

    }

    /**
     * Collect Children
     *
     * Retain supplied nodes still owned by this component and discover newly
     * appended host children. Removed nodes must not return on the next render.
     *
     * @return void
     */
    private collectChildren(): void {

        // Drop nodes removed or transferred out of this component by application code.
        this._children = this._children.filter(node => this.contains(node) || node.parentNode === this._parking);

        // Set added
        const added = Array.from(this.childNodes).filter(node => !this._ownedRoots.has(node));
        
        // Re-appending a supplied node moves it to the end, as appendChild does.
        this._children = this._children.filter(node => !added.includes(node as ChildNode));

        // Push in children
        this._children.push(...added);

    }

    /**
     * Project Children
     *
     * Assign supplied nodes to the first matching light DOM slot.
     * Restore fallback content for empty slots and retain unmatched nodes.
     *
     * @return void
     */
    private projectChildren(): void {

        // Group nodes by the first matching named or default slot.
        const groups = new Map<HTMLSlotElement, Node[]>();

        // Iteration chuildren
        for (const node of this._children) {

            // Set name
            const name = node.nodeType === 1 
                ? (node as Element).getAttribute("slot") ?? "" 
                : ""
            ;

            // Set slot
            const slot = this._slots.find(slot => slot.element.name === name);

            // Check slot
            if(slot){

                // Set group
                const group = groups.get(slot.element) ?? [];

                // Push node
                group.push(node);

                // Set slot
                groups.set(slot.element, group);

            }else
            // Check paking not is parent    
            if(node.parentNode !== this._parking)

                // Append node in parking
                this._parking.appendChild(node);

        }

        // Avoid unnecessary moves, which would reconnect nested custom elements.
        for(const slot of this._slots){

            // Get children
            const children = groups.get(slot.element) ?? slot.fallback;

            // Checj children
            if(
                children.length !== slot.element.childNodes.length ||
                children.some((node, index) => node !== slot.element.childNodes[index])
            )

                // Replace children
                slot.element.replaceChildren(...children);

        }

    }

    /**
     * Observe Children
     *
     * Watch supplied child changes only in light DOM. Native shadow slots are
     * managed by the browser. Pause observation while performing internal moves.
     *
     * @return void
     */
    private observeChildren(): void {

        // Check render root not this and not connected
        if(this.renderRoot !== this || !this.isConnected) 
            
            // Return
            return;

        // If not oberver
        if(!this._observer){

            // Get observer
            const Observer = this.ownerDocument.defaultView!.MutationObserver;

            // Observer
            this._observer = new Observer(() => {

                // Disconnect
                this._observer!.disconnect();

                try {

                    // Collect children
                    this.collectChildren();

                    // Project chuldren
                    this.projectChildren();

                }finally{

                    // Observe children
                    this.observeChildren();

                }
                
            });

        }

        // Also watch retained nodes: a changed slot name can make them visible again.
        this._observer.observe(
            this, 
            { 
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ["slot"] 
            }
        );

        // Observe parking
        this._observer.observe(
            this._parking, 
            {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ["slot"] 
            }
        );

    }

    /** Methods | Callbacks
     ******************************************************
     */

    /**
     * Connected Callback
     *
     * Initialize values, resume child observation, and schedule rendering.
     *
     * @return void
     */
    public connectedCallback(): void {

        // Initialize
        this.initialize();
        
        // Observe children
        this.observeChildren();
        
        // Request update
        this.requestUpdate();

    }

    /**
     * Disconnected Callback
     *
     * Stop child observation and release resources for the current render.
     *
     * @return void
     */
    public disconnectedCallback(): void {

        // Disconnect
        this._observer?.disconnect();

        // Cleanup
        this.cleanup();

    }

    /**
     * Attribute Changed Callback
     *
     * Keep typed values current even while detached, without reflection loops.
     *
     * @param name Changed HTML attribute name
     * @param oldValue Previous attribute value
     * @param newValue New attribute value, or null when removed
     * @return void
     */
    public attributeChangedCallback(name: string, oldValue: string | null, newValue: string | null): void {

        // Checkreflecting or not change
        if(this._reflecting || oldValue === newValue) 
            
            // Return
            return;

        // Initialize
        this.initialize();

        // Get key
        const key = Object.keys(this.component.properties).find(key => this.component.attributeName(key, this.definition(key)) === name);

        // Check key not defined
        if (key === undefined) 
            
            // Return
            return;

        // Get values
        const value = this.fromAttribute(newValue, this.definition(key));

        // Check is
        if(!Object.is(this._values[key], value)){

            // Set value
            this._values[key] = value;

            // Request update
            this.requestUpdate();

        }

    }

    /** Static Methods
     ******************************************************
     */

    /**
     * Observed Attributes
     *
     * Derive observed attributes from the static schema at registration time.
     * Duplicate mappings would make property updates ambiguous.
     *
     * @return Mapped HTML attribute names
     */
    public static get observedAttributes(): string[] {

        // GEt names
        const names = Object.keys(this.properties).map(name => this.attributeName(name, this.properties[name])).filter((name): name is string => name !== null);

        // Checj names size not valid
        if(new Set(names).size !== names.length)

            // New error
            throw new TypeError("Component properties must use distinct attribute names.");

        // Return
        return names;

    }

    /**
     * Attribute Name
     *
     * Resolve and validate an optional lowercase HTML attribute mapping.
     *
     * @param name Property name
     * @param property Property definition
     * @return Mapped name, or null for a property without an attribute
     */
    private static attributeName(name:string, property:Crazycomponent2Property):string|null {

        // Check property attribute
        if(property.attribute === false) 
            
            // Return null
            return null;

        // Get attribute
        const attribute = typeof property.attribute === "string" 
            ? property.attribute 
            : name.toLowerCase()
        ;

        //Check not attribute
        if(!attribute || /[\s\u0000"'>/=]/.test(attribute) || attribute !== attribute.toLowerCase())

            // New error
            throw new TypeError(`Invalid component attribute name: ${attribute}`);

        // Return attribute
        return attribute;

    }

}

/** Interface
 ******************************************************
 */

/** Shared property metadata, with allowed values restricted to scalar types. */
type PropertyOptions<T, Name extends string> = {
    /** Declared runtime value type. */
    type: Name;
    /** Initial value and fallback for removed or invalid attributes. */
    default?: T;
    /** Observed HTML attribute; defaults to the lowercase property name. */
    attribute?: boolean | string;
    /** Also write programmatic changes to the HTML attribute. Default: false. */
    reflect?: boolean;
    /** Allowed scalar values; the default must belong to this collection. */
    select?: [T] extends [string | number | boolean] ? readonly T[] : never;
};

/** Supported property definitions and their corresponding default types. */
export type Crazycomponent2Property =
    | PropertyOptions<string, "string">
    | PropertyOptions<number, "number">
    | PropertyOptions<boolean, "boolean">
    | PropertyOptions<unknown[], "array">
    | PropertyOptions<Record<string, unknown>, "object">;

/** Static property schema for one component class. */
export type Crazycomponent2Properties = Record<string, Crazycomponent2Property>;

/** Rendering configuration, fixed when an instance is constructed. */
export interface Crazycomponent2Options {
    /** false: light DOM (default); true: an open shadow root. Fixed at construction. */
    shadow?: boolean;
}

/** Template data compatible with existing CrazyPHP Handlebars expressions. */
export interface Crazycomponent2Context<T extends object = Record<string, unknown>> {
    /** Current typed property values. */
    attributes: T;
    /** Registered custom element tag name. */
    name: string;
}

/** Compiled Handlebars function, literal HTML, or a default module wrapper. */
export type Crazycomponent2Template = string
    | ((context: Crazycomponent2Context) => string)
    | { default: Crazycomponent2Template };

/** Compiled CSS or loader output; style functions receive the template context. */
export type Crazycomponent2Styles = string
    | ((context: Crazycomponent2Context) => string)
    | { default: Crazycomponent2Styles }
    | { toString(): string };

/** Internal light DOM projection point and its reusable fallback nodes. */
interface LightSlot {
    /** Slot belonging to this component's generated template. */
    element: HTMLSlotElement;
    /** Original slot children used when no supplied nodes match. */
    fallback: Node[];
}
