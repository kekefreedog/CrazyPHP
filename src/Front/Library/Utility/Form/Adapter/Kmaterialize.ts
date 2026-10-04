/**
 * Kmaterialize Form Adapter
 *
 * Adapt kmaterialize widgets to the existing CrazyPHP form contract.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
import {
    AirDatepickerField,
    PasswordInput,
    TomSelectField,
    MaskitoInput,
    RichTextarea,
    RangeInterval,
    Autocomplete,
    NumberInput,
    ColorInput,
    FileInput,
    OtpInput,
    Forms,
    FormSelect,
} from "kmaterialize";
import type { CrazyFormAdapterOptions } from "../../../../../Interface/CrazyFormAdapter";
import type CrazyFormAdapter from "../../../../../Interface/CrazyFormAdapter";
import airDatepickerEnglish from "air-datepicker/locale/en";
import airDatepickerFrench from "air-datepicker/locale/fr";
import filePondFrench from "filepond/locale/fr-fr.js";

/**
 * Kmaterialize
 *
 * Initialize form widgets and synchronize their values, state and lifecycle.
 */
export default class Kmaterialize implements CrazyFormAdapter {

    /** Private Parameters
     ******************************************************
     */

    /** @var _cleanup Widget and event cleanup */
    private _cleanup:Array<() => void> = [];

    /** @var _destroyed Ignore pending initialization after navigation */
    private _destroyed:boolean = false;

    /** @var _options Application widget settings */
    private _options:CrazyFormAdapterOptions;

    /** Constructor
     ******************************************************
     */

    /**
     * Constructor
     *
     * @param options Application widget settings
     */
    public constructor(options:CrazyFormAdapterOptions = {}){

        // Store application widget settings
        this._options = options;

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Initialize Input
     *
     * Return false to keep CrazyPHP's existing initializer.
     *
     * @param input Named input or select to enhance
     * @param form Form owning the input
     * @returns Whether the adapter handled initialization
     */
    public initialize = async(input:HTMLInputElement|HTMLSelectElement, form:HTMLFormElement):Promise<boolean> => {

        // Prepare the result
        let result:boolean = false;

        // Check whether the adapter was destroyed
        if(this._destroyed){

            // Set the result
            result = true;

        }else if(input.classList.contains("filter-operator")){

            // Set the result
            result = true;

        }else{

            // Synchronize filter operator availability with the value control
            if(form.hasAttribute("data-form-filter")){

                // Find the filter field
                const field = input.closest<HTMLElement>(".filter-field");

                // Find the comparison control
                const operator = field?.querySelector<HTMLSelectElement>(".filter-operator");

                // Check that the comparison control exists
                if(operator)

                    // Synchronize and observe the input state
                    this._observeState(input, () => {

                        // Resolve the disabled state
                        const disabled = input.matches(":disabled") || input.hasAttribute("readonly") || !!field?.hasAttribute("data-filter-readonly");

                        // Refresh the comparison control only when its disabled state changes
                        if(operator.disabled !== disabled){

                            // Update operator.disabled
                            operator.disabled = disabled;

                            // Check that the comparison widget is already initialized
                            if(FormSelect.getInstance(operator))

                                // Initialize the comparison dropdown
                                FormSelect.init(operator, {});

                        }

                    });

                // Prevent edits to read-only filter checkboxes
                if(field?.hasAttribute("data-filter-readonly") && input instanceof HTMLInputElement && input.type === "checkbox"){

                    // Prepare editing prevention for locked controls
                    const preventEdit = (event:Event):void => {

                        // Prevent the default action
                        event.preventDefault();

                        // Stop duplicate event propagation
                        event.stopImmediatePropagation();

                    };

                    // Register the event listener
                    input.addEventListener("click", preventEdit, true);

                    // Register resource cleanup
                    this._cleanup.push(() => input.removeEventListener("click", preventEdit, true));

                }

            }

            // Check for a serialized interval input
            if(input instanceof HTMLInputElement && input.hasAttribute("data-filter-range-interval")){

                // Find the interval widget host
                const host = input.parentElement?.querySelector<HTMLElement>(".range-interval");

                // Check whether the interval host is missing
                if(!host){

                    // Set the result
                    result = false;

                }else{

                    // Initialize the widget
                    const widget = RangeInterval.init(host, {
                        showInputs: true,
                        startLabel: input.dataset.rangeStartLabel || "From",
                        endLabel: input.dataset.rangeEndLabel || "To",
                    });

                    // Wait for initialization and register widget cleanup
                    await this._track(widget);

                    // Check whether the adapter was destroyed
                    if(this._destroyed){

                        // Set the result
                        result = true;

                    }else{

                        // Restore the interval from its serialized value
                        const restore = ():void => {

                            // Parse the serialized interval
                            const match = input.value.match(/^\[([^:]+):([^\]]+)\]$/);

                            // Accept only intervals with two finite bounds
                            if(match && Number.isFinite(Number(match[1])) && Number.isFinite(Number(match[2]))){

                                // Restore the widget value
                                widget.setValues(Number(match[1]), Number(match[2]));

                                // Read the values needed for synchronization
                                const [start, end] = widget.getValues();

                                // Read the current value
                                const value = `[${start}:${end}]`;

                                // Write the normalized interval only when its value changes
                                if(input.value !== value)

                                    // Update input.value
                                    input.value = value;

                            }

                        };

                        // Forward handle changes through the named input
                        const sync = (event:Event):void => {

                            // Synchronize changes originating from either interval handle
                            if(event.target === widget.start || event.target === widget.end){

                                // Read the values needed for synchronization
                                const [start, end] = widget.getValues();

                                // Update input.value
                                input.value = `[${start}:${end}]`;

                                // Forward the change to the form
                                input.dispatchEvent(new Event(event.type, { bubbles: true }));

                            }

                        };

                        // Register the event listener
                        host.addEventListener("input", sync);

                        // Register the event listener
                        host.addEventListener("change", sync);

                        // Register the event listener
                        input.addEventListener("input", restore);

                        // Register the event listener
                        input.addEventListener("change", restore);

                        // Register resource cleanup
                        this._cleanup.push(() => {

                            // Remove the event listener
                            host.removeEventListener("input", sync);

                            // Remove the event listener
                            host.removeEventListener("change", sync);

                            // Remove the event listener
                            input.removeEventListener("input", restore);

                            // Remove the event listener
                            input.removeEventListener("change", restore);

                        });

                        // Apply the input state to the visible widget
                        this._observeState(input, () => {

                            // Resolve the disabled state
                            const disabled = input.disabled || input.readOnly;

                            // Update widget.start.disabled
                            widget.start.disabled = disabled;

                            // Update widget.end.disabled
                            widget.end.disabled = disabled;

                            // Refresh the widget
                            widget.update();

                        });

                        // Restore interval values after attribute updates and form resets
                        this._observeState(input, restore, ["value"]);

                        // Synchronize the widget after form reset
                        this._afterReset(form, () => {

                            // Apply the prepared synchronization
                            restore();

                            // Refresh the widget
                            widget.update();

                        });

                        // Set the result
                        result = true;

                    }

                }

            }else 
            // If input is html select element    
            if(input instanceof HTMLSelectElement){

                // Keep remote and dependent selects with the existing handler
                if(input.dataset.selectRemote || input.dataset.depends){

                    // Set the result
                    result = false;

                }else{

                    // Render color choices with a swatch and an escaped text label
                    const renderColor = (data:{
                        value?:string;
                        text?:string;
                    }):HTMLElement => {

                        // Prepare the result
                        let result:HTMLElement = document.createElement("div");

                        // Update result.className
                        result.className = "filter-color-choice";

                        // Read the selected color
                        const color = Array.from(input.options).find(option => option.value === String(data.value))?.dataset.color || "";

                        // Create the color swatch
                        const swatch = document.createElement("span");

                        // Update swatch.className
                        swatch.className = "filter-color-swatch";

                        // Set the element attribute
                        swatch.setAttribute("aria-hidden", "true");

                        // Apply only supported CSS colors
                        if(CSS.supports("color", color))

                            // Update swatch.style.backgroundColor
                            swatch.style.backgroundColor = color;

                        // Create the option label
                        const label = document.createElement("span");

                        // Update label.textContent
                        label.textContent = data.text || "";

                        // Append the prepared elements
                        result.append(swatch, label);

                        // Return the result
                        return result;

                    };

                    // Initialize the widget
                    const widget = TomSelectField.init(input, {
                        settings: {
                            createOnBlur: true,
                            plugins: input.multiple ? { remove_button: { title: "Remove selection" } } : {},
                            ...(input.hasAttribute("data-filter-color") ? { render: { option: renderColor, item: renderColor } } : {}),
                        },
                    });

                    // Wait for initialization and register widget cleanup
                    await this._track(widget);

                    // Check whether the adapter was destroyed
                    if(this._destroyed){

                        // Set the result
                        result = true;

                    }else{

                        // Synchronize and observe the input state
                        this._observeState(input, () => {

                            // Get the enhanced select instance
                            const select = widget.tomSelect;

                            // Check that the enhanced select is ready
                            if(select){

                                // Synchronize the disabled state when it changes
                                if(input.disabled !== select.isDisabled){

                                    // Check whether the input is disabled
                                    if(input.disabled)

                                        // Disable the select
                                        select.disable();
                                    else

                                        // Enable the select
                                        select.enable();

                                }

                                // Lock a read-only select that is still editable
                                if(input.hasAttribute("readonly") && !select.isLocked)

                                    // Lock editing of the selection
                                    select.lock();
                                else if(!input.disabled && !input.hasAttribute("readonly") && select.isLocked)

                                    // Unlock editing of the selection
                                    select.unlock();

                            }

                        });

                        // Set the result
                        result = true;

                    }

                }

            }else if(input.dataset.textareaTarget){

                // Initialize and synchronize the specialized input
                await this._initializeTextarea(input, form);

                // Set the result
                result = true;

            }else if(input.dataset.chipsTarget){

                // Initialize and synchronize the specialized input
                await this._initializeChips(input, form);

                // Set the result
                result = true;

            }else if(input.hasAttribute("data-otp")){

                // Initialize the widget
                const widget = OtpInput.init(input);

                // Initialize and synchronize the specialized input
                await this._initializeMask(input, form, widget);

                // Set the result
                result = true;

            }else if(input.hasAttribute("data-maskito")){

                // Initialize the widget
                const widget = MaskitoInput.init(input);

                // Initialize and synchronize the specialized input
                await this._initializeMask(input, form, widget);

                // Set the result
                result = true;

            }else if(input.dataset.autocompleteOptions){

                // Initialize the widget
                const widget = Autocomplete.init(input, JSON.parse(input.dataset.autocompleteOptions));

                // Wait for initialization and register widget cleanup
                await this._track(widget);

                // Synchronize and observe the input state
                this._observeState(input, () => {

                    // Check whether interaction is blocked
                    if(input.disabled || input.readOnly)

                        // Close the autocomplete suggestions
                        widget.close();

                });

                // Set the result
                result = true;

            }else if(input.matches("[data-password-toggle]")){

                // Initialize the widget
                const widget = PasswordInput.init(input);

                // Wait for initialization and register widget cleanup
                await this._track(widget);

                // Set the result
                result = true;

            }else if(input.dataset.type === "number"){

                // Read the numeric precision
                const scale = Number(input.dataset.numberScale || 0);

                // Initialize the widget
                const widget = NumberInput.init(input, { scale, step: 10 ** -scale });

                // Wait for initialization and register widget cleanup
                await this._track(widget);

                // Set the result
                result = true;

            }else if(input.dataset.colorPicker === "pickr"){

                // Initialize the widget
                const widget = ColorInput.init(input);

                // Wait for initialization and register widget cleanup
                await this._track(widget);

                // Check whether the adapter was destroyed
                if(this._destroyed){

                    // Set the result
                    result = true;

                }else{

                    // Synchronize and observe the input state
                    this._observeState(input, () => {

                        // Check whether interaction is blocked
                        if(input.disabled || input.readOnly)

                            // Disable the color picker
                            widget.pickr?.disable();
                        else

                            // Enable the color picker
                            widget.pickr?.enable();

                    });

                    // Synchronize the widget after form reset
                    this._afterReset(form, () => widget.pickr?.setColor(input.value, true));

                    // Set the result
                    result = true;

                }

            }else if(["airdatepicker", "air-datepicker"].includes(input.dataset.datePicker || "")){

                // Pass the locale object directly so CommonJS imports cannot fall back to Russian.
                const language = input.dataset.dateLang?.toLowerCase().split("-")[0];

                // Choose the date picker locale
                const locale = language === "fr" ? airDatepickerFrench : airDatepickerEnglish;

                // Initialize the widget
                const widget = AirDatepickerField.init(input, { locale });

                // Wait for initialization and register widget cleanup
                await this._track(widget);

                // Check whether the adapter was destroyed
                if(this._destroyed){

                    // Set the result
                    result = true;

                }else{

                    // Prepare editing prevention for locked controls
                    const preventEdit = (event:Event):void => {

                        // Check whether interaction is blocked
                        if(input.disabled || input.readOnly){

                            // Prevent the default action
                            event.preventDefault();

                            // Stop duplicate event propagation
                            event.stopImmediatePropagation();

                        }

                    };

                    // Prevent labels and adornments from opening a locked date picker
                    const targets = [input, ...Array.from(input.parentElement?.querySelectorAll<HTMLElement>("label, .prefix, .suffix") || [])];

                    // Process each target
                    for(const target of targets){

                        // Register the event listener
                        target.addEventListener("click", preventEdit, true);

                        // Register the event listener
                        target.addEventListener("focus", preventEdit, true);

                        // Register resource cleanup
                        this._cleanup.push(() => {

                            // Remove the event listener
                            target.removeEventListener("click", preventEdit, true);

                            // Remove the event listener
                            target.removeEventListener("focus", preventEdit, true);

                        });

                    }

                    // Synchronize and observe the input state
                    this._observeState(input, () => {

                        // Check whether interaction is blocked
                        if(input.disabled || input.readOnly)

                            // Hide the date picker
                            widget.picker?.hide();

                    });

                    // Restore single, multiple or interval date selections
                    const syncDate = ():void => {

                        // Read the current value
                        const value = input.value;

                        // Clear the date selection without emitting a change
                        widget.picker?.clear({ silent: true });

                        // Restore a date selection when a value is present
                        if(value)

                            // Restore the configured date selection without emitting a change
                            widget.picker?.selectDate(input.multiple || input.dataset.dateRange === "true" ? value.split(" - ") : value, { silent: true });

                    };

                    // Synchronize the widget after form reset
                    this._afterReset(form, syncDate);

                    // Synchronize and observe the input state
                    this._observeState(input, syncDate, ["value"]);

                    // Set the result
                    result = true;

                }

            }else if(input.type === "file" && input.classList.contains("filepond")){

                // Find the file widget wrapper
                const wrapper = input.closest<HTMLElement>(".file-field");

                // Check whether the upload wrapper is missing
                if(!wrapper){

                    // Set the result
                    result = false;

                }else{

                    // Initialize the widget
                    const widget = FileInput.init(wrapper);

                    // Wait for initialization and register widget cleanup
                    await this._track(widget);

                    // Check whether the adapter was destroyed
                    if(this._destroyed){

                        // Set the result
                        result = true;

                    }else{

                        // Apply the French upload messages when requested
                        if(["fr-fr", "fr_FR"].includes(input.dataset.fileLocale || ""))

                            // Configure the upload widget locale
                            widget.pond?.setOptions(filePondFrench);

                        // Keep the marker consumed by CrazyPHP's file value extractor.
                        const sync = ():void => {

                            // Find the filter field
                            const field = widget.pond?.element?.querySelector<HTMLInputElement>("input[name]");

                            // Check that the generated file input and upload widget exist
                            if(field && widget.pond){

                                // Copy the upload instance identifier when available
                                if(widget.pond.id)

                                    // Update field.dataset.pondId
                                    field.dataset.pondId = widget.pond.id;

                                // Update field.dataset.type
                                field.dataset.type = "file";

                            }

                        };

                        // Observe upload changes
                        widget.pond?.on("updatefiles", sync);

                        // Register resource cleanup
                        this._cleanup.push(() => widget.pond?.off("updatefiles", sync));

                        // Apply the prepared synchronization
                        sync();

                        // Set the result
                        result = true;

                    }

                }

            }else{

                // Keep native file labels synchronized when no enhanced widget is used
                if(input.type === "file"){

                    // Find the native file label
                    const path = input.closest(".file-field")?.querySelector<HTMLInputElement>("input.file-path");

                    // Check that the native file label exists
                    if(path){

                        // Forms can arrive after Materialize's document-ready initialization.
                        const sync = ():void => {

                            // Collect the selected filenames
                            const names = Array.from(input.files || []).map(file => file.name).join(", ");

                            // Update path.value
                            path.value = names;

                            // Update path.title
                            path.title = names;

                        };

                        // Register the event listener
                        input.addEventListener("change", sync);

                        // Register resource cleanup
                        this._cleanup.push(() => input.removeEventListener("change", sync));

                        // Synchronize the widget after form reset
                        this._afterReset(form, sync);

                        // Apply the prepared synchronization
                        sync();

                    }

                }

                // Set the result
                result = false;

            }

        }

        // Return the result
        return result;

    }

    /**
     * Initialize Operator
     *
     * Use kmaterialize for comparison controls as well as value widgets.
     *
     * @param input Filter comparison select
     * @returns {void}
     */
    public initializeOperator = (input:HTMLSelectElement):void => {

        // Enhance non-native comparison controls while the adapter is active
        if(!this._destroyed && !input.classList.contains("browser-default")){

            // Initialize the comparison dropdown and register its cleanup
            FormSelect.init(input, {});

            // Register resource cleanup
            this._cleanup.push(() => FormSelect.getInstance(input)?.destroy());

        }

    }

    /**
     * Destroy
     *
     * Stop pending initialization and release widgets, listeners and observers.
     *
     * @returns {void}
     */
    public destroy = ():void => {

        // Block pending initialization before releasing resources
        this._destroyed = true;

        // Release resources in reverse initialization order
        this._cleanup.reverse().forEach(cleanup => cleanup());

        // Update this._cleanup
        this._cleanup = [];

    }

    /** Private Methods | Initialization
     ******************************************************
     */

    /**
     * Initialize Mask
     *
     * Synchronize masks after CrazyPHP sets or resets input values.
     *
     * @param input Named input holding the mask value
     * @param form Form owning the input
     * @param widget OTP or mask widget to initialize
     * @returns {Promise<void>}
     */
    private _initializeMask = async(input:HTMLInputElement, form:HTMLFormElement, widget:OtpInput|MaskitoInput):Promise<void> => {

        // Wait for initialization and register widget cleanup
        await this._track(widget);

        // Continue only while the adapter is active
        if(!this._destroyed){

            // Synchronize values assigned through CrazyPHP input attributes
            const observer = new MutationObserver(() => {

                // Restore the widget value
                void widget.setValue(input.getAttribute("value") || "", false);

            });

            // Start observing the configured changes
            observer.observe(input, { attributes: true, attributeFilter: ["value"] });

            // Register resource cleanup
            this._cleanup.push(() => observer.disconnect());

            // Synchronize the widget after form reset
            this._afterReset(form, () => {

                // Restore the widget value
                void widget.setValue(input.getAttribute("default") || "", false);

            });

        }

    }

    /**
     * Initialize Textarea
     *
     * Synchronize textarea values through CrazyPHP's named-input contract.
     *
     * @param input Named input holding the submitted value
     * @param form Form owning the textarea
     * @returns {Promise<void>}
     */
    private _initializeTextarea = async(input:HTMLInputElement, form:HTMLFormElement):Promise<void> => {

        // Resolve the textarea belonging to this form
        const textarea = document.getElementById(input.dataset.textareaTarget || "");

        // Initialize only a textarea owned by this form
        if((textarea instanceof HTMLTextAreaElement) && textarea.form === form){

            // Load the configured rich-text stylesheet once
            const richTextarea = this._options.richTextarea;

            // Load the configured rich-text stylesheet only once
            if(textarea.dataset.editor === "quill" && richTextarea?.stylesheet && !document.querySelector("link[data-rich-textarea-font]")){

                // Create the stylesheet link
                const stylesheet = document.createElement("link");

                // Update stylesheet.rel
                stylesheet.rel = "stylesheet";

                // Update stylesheet.href
                stylesheet.href = richTextarea.stylesheet;

                // Update stylesheet.dataset.richTextareaFont
                stylesheet.dataset.richTextareaFont = "";

                // Append the prepared elements
                document.head.append(stylesheet);

            }

            // Create a rich editor only when requested by the field
            const widget = textarea.dataset.editor === "quill"
                ? RichTextarea.init(textarea, {
                    fontFamily: richTextarea?.fontFamily,
                })
                : null;

            // Check whether a rich editor was created
            if(widget)

                // Wait for initialization and register widget cleanup
                await this._track(widget);

            // Continue only while the adapter is active
            if(!this._destroyed){

                // Prepare value and state synchronization
                const sync = ():void => {

                    // Apply the submitted input state to the visible editor
                    textarea.disabled = input.disabled;

                    // Update textarea.readOnly
                    textarea.readOnly = input.readOnly;

                    // Synchronize the editor only when its value differs
                    if(textarea.value !== input.value){

                        // Check whether a rich editor was created
                        if(widget)

                            // Restore the widget value
                            widget.setValue(input.value, false);
                        else

                            // Update textarea.value
                            textarea.value = input.value;

                    }

                    // Resize the native textarea when no rich editor is present
                    if(!widget)

                        // Resize the textarea to its content
                        Forms.textareaAutoResize(textarea);

                };

                // Prepare change forwarding
                const update = (event:Event):void => {

                    // Resize the native textarea when no rich editor is present
                    if(!widget)

                        // Resize the textarea to its content
                        Forms.textareaAutoResize(textarea);

                    // Update the submitted value when the editor changes
                    if(input.value !== textarea.value)

                        // Update input.value
                        input.value = textarea.value;

                    // Only the named input emits the form change notification.
                    event.stopPropagation();

                    // Forward the change to the form
                    input.dispatchEvent(new Event(event.type, { bubbles: true }));

                };

                // Forward editor changes and unregister listeners on destruction
                textarea.addEventListener("input", update);

                // Register the event listener
                textarea.addEventListener("change", update);

                // Register resource cleanup
                this._cleanup.push(() => {

                    // Remove the event listener
                    textarea.removeEventListener("input", update);

                    // Remove the event listener
                    textarea.removeEventListener("change", update);

                });

                // Resize the native textarea when no rich editor is present
                if(!widget){

                    // Focus can change the outline thickness without changing the field width.
                    const resize = ():void => Forms.textareaAutoResize(textarea);

                    // Register the event listener
                    textarea.addEventListener("focus", resize);

                    // Register the event listener
                    textarea.addEventListener("blur", resize);

                    // Register resource cleanup
                    this._cleanup.push(() => {

                        // Remove the event listener
                        textarea.removeEventListener("focus", resize);

                        // Remove the event listener
                        textarea.removeEventListener("blur", resize);

                    });

                    // Reflow wrapped lines after layout changes, including hidden fields becoming visible.
                    let width = 0;

                    // Create the state observer
                    const observer = new ResizeObserver(() => {

                        // Read the current textarea width
                        const nextWidth = textarea.getBoundingClientRect().width;

                        // Reflow the textarea only when its width changes
                        if(nextWidth !== width){

                            // Update width
                            width = nextWidth;

                            // Resize the textarea to its content
                            Forms.textareaAutoResize(textarea);

                        }

                    });

                    // Start observing the configured changes
                    observer.observe(textarea);

                    // Register resource cleanup
                    this._cleanup.push(() => observer.disconnect());

                    // Wait for font loading before the final textarea resize
                    void document.fonts.ready.then(() => {

                        // Resize after font loading only while the textarea remains active
                        if(!this._destroyed && textarea.isConnected)

                            // Resize the textarea to its content
                            Forms.textareaAutoResize(textarea);

                    });

                }

                // Synchronize and observe the input state
                this._observeState(input, sync, ["disabled", "readonly", "value"]);

                // Restore the serialized default after form reset
                this._afterReset(form, () => {

                    // Update input.value
                    input.value = input.getAttribute("default") || "";

                    // Apply the prepared synchronization
                    sync();

                });

            }

        }

    }

    /**
     * Initialize Chips
     *
     * Store Tom Select tags as a JSON array in one named input.
     *
     * @param input Named input holding the serialized tags
     * @param form Form owning the editor
     * @returns {Promise<void>}
     */
    private _initializeChips = async(input:HTMLInputElement, form:HTMLFormElement):Promise<void> => {

        // Resolve the tag editor belonging to this form
        const editor = document.getElementById(input.dataset.chipsTarget || "");

        // Initialize only a tag editor owned by this form
        if((editor instanceof HTMLSelectElement) && editor.form === form){

            // Read unique tags from the serialized input value
            const read = ():Array<string> => {

                // Prepare the result
                let result:Array<string> = [];

                // Read the current values
                const values = JSON.parse(input.value || "[]");

                // Set the result
                result = Array.isArray(values) ? [...new Set(values.map(String))] : [];

                // Return the result
                return result;

            };

            // Initialize the widget
            const widget = TomSelectField.init(editor, {
                settings: {
                    create: true,
                    createOnBlur: true,
                    plugins: { remove_button: { title: "Remove tag" } },
                },
            });

            // Wait for initialization and register widget cleanup
            await this._track(widget);

            // Continue when the adapter is active and the select is ready
            if(!this._destroyed && widget.tomSelect){

                // Get the enhanced select instance
                const select = widget.tomSelect;

                // Apply required validation to the visible tag input
                const validate = ():void => {

                    // Validate required tags through the visible input
                    select.control_input.setCustomValidity(input.hasAttribute("data-chips-required") && !select.items.length ? "Add at least one tag." : "");

                };

                // Prepare change forwarding
                const update = (event:Event):void => {

                    // Stop duplicate event propagation
                    event.stopPropagation();

                    // Update input.value
                    input.value = JSON.stringify(select.items);

                    // Apply the prepared synchronization
                    validate();

                    // Forward the change to the form
                    input.dispatchEvent(new Event("change", { bubbles: true }));

                };

                // Prepare value and state synchronization
                const sync = ():void => {

                    // Restore tag choices before synchronizing the selection
                    const values = read();

                    // Process each value
                    for(const value of values)

                        // Add a stored tag that is missing from the available options
                        if(!select.options[value])

                            // Add the missing tag option
                            select.addOption({ value, text: value });

                    // Restore the selection only when the stored tags differ
                    if(JSON.stringify(select.items) !== JSON.stringify(values))

                        // Restore the widget value
                        select.setValue(values, true);

                    // Synchronize the disabled state when it changes
                    if(input.disabled !== select.isDisabled){

                        // Check whether the input is disabled
                        if(input.disabled)

                            // Disable the select
                            select.disable();

                        else

                            // Enable the select
                            select.enable();

                    }

                    // Lock read-only tags that are still editable
                    if(input.readOnly && !select.isLocked)

                        // Lock editing of the selection
                        select.lock();
                    else if(!input.disabled && !input.readOnly && select.isLocked)

                        // Unlock editing of the selection
                        select.unlock();

                    // Apply the prepared synchronization
                    validate();

                };

                // Forward tag changes and observe submitted input updates
                editor.addEventListener("change", update);

                // Register resource cleanup
                this._cleanup.push(() => editor.removeEventListener("change", update));

                // Synchronize and observe the input state
                this._observeState(input, sync, ["disabled", "readonly", "value"]);

                // Restore the serialized default after form reset
                this._afterReset(form, () => {

                    // Update input.value
                    input.value = input.getAttribute("default") || "[]";

                    // Apply the prepared synchronization
                    sync();

                });

            }

        }

    }

    /** Private Methods | Lifecycle
     ******************************************************
     */

    /**
     * Track Widget
     *
     * Wait for initialization and register cleanup, or destroy a late widget.
     *
     * @param widget Widget with optional asynchronous initialization
     * @returns {Promise<void>}
     */
    private _track = async(widget:{
        destroy:() => void;
        ready?:Promise<void>;
    }):Promise<void> => {

        // Wait for initialization and handle widget failures
        try{

            // Wait for the widget to become ready
            await widget.ready;

        }
        catch(error){

            // Destroy the widget
            widget.destroy();

            // Propagate the initialization error
            throw error;

        }

        // Check whether the adapter was destroyed
        if(this._destroyed)

            // Destroy the widget
            widget.destroy();
        else

            // Register resource cleanup
            this._cleanup.push(() => widget.destroy());

    }

    /**
     * Observe State
     *
     * Synchronize immediately and observe subsequent input attribute changes.
     *
     * @param input Element whose attributes control the widget
     * @param sync Callback applying the current input state
     * @param attributeFilter Attributes that trigger synchronization
     * @returns {void}
     */
    private _observeState = (input:HTMLElement, sync:() => void, attributeFilter:Array<string> = ["disabled", "readonly"]):void => {

        // Continue only while the adapter is active
        if(!this._destroyed){

            // Apply the prepared synchronization
            sync();

            // Observe only the attributes used by the widget
            const observer = new MutationObserver(sync);

            // Start observing the configured changes
            observer.observe(input, { attributes: true, attributeFilter });

            // Register resource cleanup
            this._cleanup.push(() => observer.disconnect());

        }

    }

    /**
     * After Reset
     *
     * Synchronize after CrazyPHP and the browser finish resetting the form.
     *
     * @param form Form whose reset events should be observed
     * @param sync Callback restoring the widget state
     * @returns {void}
     */
    private _afterReset = (form:HTMLFormElement, sync:() => void):void => {

        // Continue only while the adapter is active
        if(!this._destroyed){

            // Defer synchronization until the reset event has completed
            const reset = ():void => {

                // Defer synchronization until the reset handlers finish
                queueMicrotask(() => {

                    // Synchronize only while the adapter remains active
                    if(!this._destroyed)

                        // Apply the prepared synchronization
                        sync();

                });

            };

            // Register reset synchronization and its cleanup
            form.addEventListener("reset", reset);

            // Register resource cleanup
            this._cleanup.push(() => form.removeEventListener("reset", reset));

        }

    }

}
