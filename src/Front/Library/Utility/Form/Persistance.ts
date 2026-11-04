/**
 * Form Persistence
 *
 * Remember opted-in form values through Crazycache without persisting excluded inputs.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/** Dependencies */
import Crazycache from "../../Crazycache";

/**
 * Form Persistence
 *
 * Restore native values before widgets initialize and remember subsequent user input.
 */
export default class formPersistence {

    /** Cache shared by remembered forms, explicitly backed by localStorage. */
    private _cache:Crazycache;

    /** Last remembered values, including temporarily disabled scenario fields. */
    private _fields:Record<string, StoredField> = {};

    /** Child values invalidated until a choice is selected for the new parent. */
    private _invalidated = new Set<string>();

    /** Ordered writes prevent older saves from overwriting newer selections. */
    private _pending:Promise<unknown> = Promise.resolve();

    /** Stable form, user and project identity supplied by the application. */
    private _key:string;

    /** Mounted native form. */
    private _form:HTMLFormElement;

    /** Explicitly excluded input names. */
    private _exclude:Set<string>;

    /** Constructor
     ******************************************************
     */

    /**
     * Constructor
     *
     * @param form Mounted form
     * @param options Opt-in storage configuration
     */
    public constructor(form:HTMLFormElement, options:FormPersistenceOptions){

        // Retain the native form
        this._form = form;

        // Exclude declared path and other nonpersistent fields
        this._exclude = new Set(options.exclude ?? []);

        // Keep independent users, projects and forms in separate cache entries
        this._key = JSON.stringify([options.key || form.id, options.scope || ""]);

        // Use the CrazyPHP cache library with an explicit browser localStorage driver
        this._cache = new Crazycache("form-values", {driver: "localStorage"});

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Restore
     *
     * Restore native defaults before initializing dependent widgets.
     * @param signal Form lifecycle cancellation
     * @return Promise resolving after storage has been read
     */
    public async restore(signal:AbortSignal):Promise<void> {

        // Treat unavailable or malformed storage as an empty preference set
        try{

            // Read the last remembered values
            const stored = await this._cache.get(this._key);

            // Ignore obsolete forms and invalid payloads
            if(!signal.aborted && stored && typeof stored === "object" && !Array.isArray(stored)){

                // Visit parents before their dependent selectors
                const remaining = this._inputs();

                // Track names whose native values are ready
                const restored = new Set<string>();

                // Resolve dependencies without allowing cycles to block initialization
                for(let pass = 0; remaining.length && pass <= this._inputs().length; pass++){

                    // Process every field whose parent inputs are ready
                    for(const input of [...remaining]){

                        // Read parent field names
                        const parents = (input.dataset.depends || "").split(",").filter(Boolean);

                        // Defer dependent controls until their parents have been considered
                        if(parents.every(name => restored.has(name) || !remaining.some(field => field.name === name))){

                            // Read the remembered record for this field only
                            const record = stored[input.name] as StoredField|undefined;

                            // Require a matching parent context and a supported stored value
                            if(record && this._valid(record) && parents.every(name => record.dependencies?.[name] === this._dependencyValue(name))){

                                // Restore only choices that are available or will be loaded remotely
                                this._restoreInput(input, record.value);

                                // Retain validated values for inactive scenario fields
                                this._fields[input.name] = record;

                            }

                            // Mark this parent selection as resolved
                            restored.add(input.name);

                            // Remove the completed control from the dependency queue
                            remaining.splice(remaining.indexOf(input), 1);

                        }

                    }

                }

            }

        }catch{

            // Keep the form usable if browser storage is disabled or full
            this._fields = {};

        }

    }

    /**
     * Watch
     *
     * Persist edits after widget initialization without saving transient restore events.
     * @param signal Form lifecycle cancellation
     * @return void
     */
    public watch(signal:AbortSignal):void {

        // Capture dependency changes before widgets reload their child choices
        this._form.addEventListener("change", event => {

            // Read the changed native control
            const target = event.target as PersistentInput;

            // Accept an explicit selection in a previously invalidated child
            this._invalidated.delete(target.name);

            // Remove obsolete child defaults when their parent selection changes
            for(const input of this._inputs()){

                // Match only children of this control
                if((input.dataset.depends || "").split(",").includes(target.name)){

                    // Prevent a previous model's identifier being restored after a reload
                    input.removeAttribute("default");

                    // Forget deferred values belonging to the previous parent
                    delete input.dataset.selectValueToSet;

                    // Keep stale child values out of snapshots until a new selection arrives
                    this._invalidated.add(input.name);

                    // Forget the stale saved child selection
                    delete this._fields[input.name];

                }

            }

        }, {capture: true, signal});

        // Save completed native changes, including select and checkbox updates
        this._form.addEventListener("change", () => this.save(), {signal});

        // Remember typed text even when the popup is closed without submission
        this._form.addEventListener("input", () => this.save(), {signal});

    }

    /**
     * Save
     *
     * Queue a snapshot without delaying the user's form interaction.
     * @return Promise resolving after the current snapshot is stored
     */
    public async save():Promise<void> {

        // Capture only supported, enabled controls
        for(const input of this._inputs()){

            // Keep inactive scenario values instead of replacing them with omitted data
            if(!input.disabled && !this._invalidated.has(input.name)){

                // Read parent selections alongside the dependent value
                const dependencies = Object.fromEntries((input.dataset.depends || "").split(",").filter(Boolean).map(name => [name, this._dependencyValue(name)]));

                // Skip unchecked radio siblings so they cannot overwrite the selected value
                if(input.type !== "radio" || (input as HTMLInputElement).checked){

                    // Preserve boolean and multiple-selection values
                    const value = input.type === "checkbox"
                        ? (input as HTMLInputElement).checked
                        : input.tagName === "SELECT" && (input as HTMLSelectElement).multiple
                            ? Array.from((input as HTMLSelectElement).selectedOptions).map(option => option.value)
                            : input.value;

                    // Store the current value with its dependency context
                    this._fields[input.name] = {value, dependencies};

                }

            }

        }

        // Copy the current snapshot before another input event mutates it
        const snapshot = JSON.parse(JSON.stringify(this._fields));

        // Serialize writes while allowing the form to continue when storage fails
        this._pending = this._pending.then(() => this._cache.set(this._key, snapshot)).catch(() => {});

        // Make completion observable to form owners and tests
        await this._pending;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Inputs
     *
     * @return Controls that may be persisted
     */
    private _inputs():PersistentInput[] {

        // Exclude paths by configuration and never store submission credentials or uploaded files
        const result = Array.from(this._form.querySelectorAll<PersistentInput>("input[name], select[name], textarea[name]")).filter(input =>
            !this._exclude.has(input.name) &&
            !this._exclude.has(input.name.replace(/\[\]$/, "")) &&
            input.dataset.persist === "true" &&
            !["password", "file", "hidden", "submit", "button", "reset"].includes(input.type)
        );

        // Return the supported controls
        return result;

    }

    /**
     * Dependency Value
     *
     * @param name Parent field name
     * @return Current serialized parent selection
     */
    private _dependencyValue(name:string):string {

        // Resolve parent names without interpolating selectors
        const input = Array.from(this._form.elements).find(element => (element as PersistentInput).name === name) as PersistentInput|undefined;

        // Keep dependencies comparable across browser sessions
        const result = input?.value ?? "";

        // Return the parent selection
        return result;

    }

    /**
     * Valid
     *
     * @param record Untrusted browser preference
     * @return Whether the record contains a supported native value
     */
    private _valid(record:StoredField):boolean {

        // Accept only native value shapes
        const result = typeof record.value === "string" || typeof record.value === "boolean" || (Array.isArray(record.value) && record.value.every(value => typeof value === "string"));

        // Return the validation result
        return result;

    }

    /**
     * Restore Input
     *
     * @param input Native control before widget initialization
     * @param value Remembered field value
     * @return void
     */
    private _restoreInput(input:PersistentInput, value:StoredField["value"]):void {

        // Restore checkable controls without rewriting their submitted values
        if(input.type === "checkbox" || input.type === "radio"){

            // Restore the checked state of this checkbox or radio option
            (input as HTMLInputElement).checked = input.type === "checkbox" ? value === true : input.value === value;

        }else if(input.tagName === "SELECT"){

            // Resolve scalar and multiple selections uniformly
            const values = Array.isArray(value) ? value : [String(value)];

            // Retain remote identifiers only as defaults; loaded choices validate their availability
            if(input.dataset.selectRemote){

                // Let the remote select apply the saved identifier after loading its current choices
                input.setAttribute("default", Array.isArray(value) ? JSON.stringify(value) : String(value));

            }else if(values.every(value => Array.from((input as HTMLSelectElement).options).some(option => option.value === value))){

                // Restore only available choices in static or already loaded model selects
                for(const option of (input as HTMLSelectElement).options){

                    // Apply the native selection before the widget mounts
                    option.selected = values.includes(option.value);

                }

            }

        }else{

            // Restore native text and textarea values
            input.value = String(value);

        }

        // Notify application dependency configuration before shared widgets initialize
        input.dispatchEvent(new this._form.ownerDocument.defaultView!.Event("change", {bubbles: true}));

    }

}

/** Native controls supported by form persistence. */
type PersistentInput = HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement;

/** Value and parent selections captured together to prevent cross-model restoration. */
interface StoredField {
    value:string|string[]|boolean;
    dependencies:Record<string, string>;
}
