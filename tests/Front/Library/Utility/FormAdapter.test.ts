/**
 * Form Adapter Test
 *
 * Verify opt-in widget initialization, legacy fallback and lifecycle cleanup.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
import type KmaterializeType from "../../../../src/Front/Library/Utility/Form/Adapter/Kmaterialize";
import type FormType from "../../../../src/Front/Library/Utility/Form";
import {after, before, describe, it} from "node:test";
import assert from "node:assert/strict";
import {JSDOM} from "jsdom";

/** Test Fixtures
 ******************************************************
 */

let Kmaterialize:typeof KmaterializeType;
let Form:typeof FormType;
let dom:JSDOM;
const forms:FormType[] = [];
const descriptors = new Map<string, PropertyDescriptor|undefined>();
const TIMER_KEYS = new Set(["setTimeout", "clearTimeout", "setInterval", "clearInterval", "setImmediate", "clearImmediate", "queueMicrotask"]);

/** Lifecycle
 ******************************************************
 */

// Install the DOM environment before loading browser-dependent modules
before(async() => {

    dom = new JSDOM("<!doctype html><html><body></body></html>", {url: "http://localhost/", pretendToBeVisual: true});

    // Install DOM globals while preserving Node timers
    for(const key of Object.getOwnPropertyNames(dom.window)){

        if(TIMER_KEYS.has(key))
            continue;

        // Save the original global descriptor
        const previous = Object.getOwnPropertyDescriptor(globalThis, key);
        if(previous && !previous.configurable)
            continue;

        // Expose the DOM property for widget initialization
        descriptors.set(key, previous);
        Object.defineProperty(globalThis, key, {value: (dom.window as any)[key], configurable: true, writable: true});

    }

    // jsdom has no layout engine; these tests exercise values and widget lifecycle.
    descriptors.set("ResizeObserver", Object.getOwnPropertyDescriptor(globalThis, "ResizeObserver"));
    Object.defineProperty(globalThis, "ResizeObserver", {
        configurable: true,
        value: class {
            observe():void {}
            unobserve():void {}
            disconnect():void {}
        },
    });

    // Provide the text and layout APIs used by widgets
    Object.defineProperty(HTMLElement.prototype, "innerText", {
        configurable: true,
        get(){ return this.textContent; },
        set(value:string){ this.textContent = value; },
    });
    Element.prototype.getAnimations = () => [];
    window.matchMedia = (media:string):MediaQueryList => ({
        matches: true,
        media,
        onchange: null,
        addListener() {},
        removeListener() {},
        addEventListener() {},
        removeEventListener() {},
        dispatchEvent: () => true,
    });
    Object.defineProperty(document, "fonts", {value: {ready: Promise.resolve()}});
    (window as any).Crazyobject = {currentPage: {get: () => undefined, set: () => {}}};

    // Load utilities after installing their browser dependencies
    Form = (await import("../../../../src/Front/Library/Utility/Form")).default;
    Kmaterialize = (await import("../../../../src/Front/Library/Utility/Form/Adapter/Kmaterialize")).default;

});

// Destroy forms and restore the original global environment
after(async() => {

    forms.forEach(form => form.destroy());
    dom.window.close();
    await new Promise(resolve => setTimeout(resolve, 0));

    // Restore global descriptors after widget cleanup
    for(const [key, descriptor] of descriptors){

        if(descriptor)
            Object.defineProperty(globalThis, key, descriptor);
        else
            Reflect.deleteProperty(globalThis, key);

    }

});

/**
 * Create Form
 *
 * Keep each form attached while its widgets are initialized.
 *
 * @param markup
 * @param options
 * @returns Form element and initialized utility
 */
const create = (markup:string, options:Partial<FormOptions> = {}) => {

    // Attach the form before initializing widgets
    const element = document.createElement("form");
    element.innerHTML = markup;
    document.body.append(element);

    // Track the utility for lifecycle cleanup
    const instance = new Form(element, options);
    forms.push(instance);

    // Return the attached fixture
    return {element, instance};

};

const PASSWORD = '<div><input name="password" type="password" data-password-toggle><button data-password-toggle-icon>Toggle</button></div>';

/** Tests | Form adapter
 ******************************************************
 */

describe("Form adapter", () => {

    it("keeps the existing handler when no adapter is selected", async() => {

        // Prepare the test fixture
        const {element, instance} = create(PASSWORD);
        await instance.ready;
        element.querySelector("button")!.click();

        // Assert the legacy password handler remains selected
        assert.equal(element.querySelector("input")!.type, "password");
        assert.equal((instance as any)._adapter, null);

    });

    it("selects kmaterialize and destroys its widgets and form listeners", async() => {

        // Prepare the test fixture
        let submitted = 0;
        const {element, instance} = create(PASSWORD, {adapter: "kmaterialize", onSubmitDone: () => submitted++});
        await instance.ready;
        const input = element.querySelector("input")!;
        const button = element.querySelector("button")!;
        button.click();

        // Assert the password widget state and lifecycle
        assert.equal(input.type, "text");
        assert.ok((instance as any)._adapter instanceof Kmaterialize);
        assert.equal(instance.getFormData(element).get("password"), "");

        instance.destroy();
        instance.destroy();
        button.type = "button";
        const previous = input.type;
        button.click();
        element.dispatchEvent(new Event("submit", {cancelable: true}));

        // Assert the password widget state and lifecycle
        assert.equal(input.type, previous);
        assert.equal(submitted, 0);

    });

    it("runs application overrides before the adapter and falls back for unhandled inputs", async() => {

        // Prepare the test fixture
        const visited:string[] = [];
        const {element, instance} = create(PASSWORD + '<input name="plain" value="hello">', {
            adapter: "kmaterialize",
            initializeInput: async(input) => {

                visited.push(input.name);
                return input.name === "password";

            },
        });
        await instance.ready;

        // Assert application overrides run before adapter fallback
        assert.deepEqual(visited, ["password", "plain"]);
        assert.equal((element.querySelector("input") as any).M_PasswordInput, undefined);
        assert.equal(instance.getFormData(element).get("plain"), "hello");

    });

    it("delegates remote and dependent selects to existing handlers", async() => {

        // Prepare the test fixture
        const adapter = new Kmaterialize();
        const form = document.createElement("form");
        for(const attribute of ["data-select-remote", "data-depends"]){

            const select = document.createElement("select");
            select.setAttribute(attribute, "source");
            form.append(select);

            // Assert remote and dependent selects use the existing handler
            assert.equal(await adapter.initialize(select, form), false);
            assert.equal((select as any).tomselect, undefined);

        }

        adapter.destroy();

    });

    it("stops initialization when destroyed while an application override is pending", async() => {

        // Prepare the test fixture
        let release!:() => void;
        let started!:() => void;
        const pending = new Promise<void>(resolve => {

            release = resolve;

        });
        const entered = new Promise<void>(resolve => {

            started = resolve;

        });
        let calls = 0;
        const {element, instance} = create(PASSWORD+PASSWORD, {
            adapter: "kmaterialize",
            initializeInput: async() => {

                calls++;
                started();
                await pending;
                return false;

            },
        });
        await entered;
        instance.destroy();
        release();
        await instance.ready;

        // Assert destruction stops further initialization
        assert.equal(calls, 1);

        for(const input of element.querySelectorAll("input"))

            // Assert the destroyed form retains no password widget
            assert.equal((input as any).M_PasswordInput, undefined);

    });

    it("destroys widgets that finish loading after adapter destruction", async() => {

        // Prepare the test fixture
        const adapter = new Kmaterialize();
        let release!:() => void;
        let destroyed = 0;
        const ready = new Promise<void>(resolve => {

            release = resolve;

        });
        const pending = (adapter as any)._track({ready, destroy: () => destroyed++});
        adapter.destroy();
        release();
        await pending;

        // Assert a late widget is destroyed once
        assert.equal(destroyed, 1);

    });

    it("synchronizes interval values, readonly state and reset defaults", async() => {

        // Prepare the test fixture
        const {element, instance} = create('<div><input type="hidden" name="interval" data-type="text" data-filter-range-interval value="[20:80]" default="[20:80]"><div class="range-interval"><input type="range" min="0" max="100" value="20"><input type="range" min="0" max="100" value="80"></div></div>', {adapter: "kmaterialize"});
        await instance.ready;
        const value = element.querySelector<HTMLInputElement>("[name=interval]")!;
        const handles = element.querySelectorAll<HTMLInputElement>("input[type=range]");
        handles[0].value = "30";
        handles[0].dispatchEvent(new Event("input", {bubbles: true}));

        // Assert the serialized form value
        assert.equal(instance.getFormData(element).get("interval"), "[30:80]");

        value.readOnly = true;
        await Promise.resolve();

        // Assert state and defaults reach the range handles
        assert.equal(handles[0].disabled, true);
        assert.equal(handles[1].disabled, true);

        value.readOnly = false;
        element.reset();
        await Promise.resolve();

        // Assert the stored value survives reset or listener cleanup
        assert.equal(value.value, "[20:80]");
        assert.equal(handles[0].value, "20");
        assert.equal(handles[1].value, "80");
        assert.equal(handles[0].disabled, false);

    });

    it("uses kmaterialize for filter operators without a Materialize webpack alias", async() => {

        // Prepare the test fixture
        const {element, instance} = create('<div class="filter-field"><select class="filter-operator" data-operator-name="value"><option value="=">=</option><option value="!=">!=</option></select><input name="value" value="hello"></div>', {adapter: "kmaterialize", filter: true});
        await instance.ready;
        const operator = element.querySelector("select")!;

        // Assert the operator widget lifecycle
        assert.ok((operator as any).M_FormSelect);

        operator.value = "!=";
        operator.dispatchEvent(new Event("change", {bubbles: true}));

        // Assert the serialized form value
        assert.equal(instance.getFormData(element).get("value"), "!=hello");

        instance.destroy();

        // Assert the operator widget lifecycle
        assert.equal((operator as any).M_FormSelect, undefined);

    });

    it("rejects unknown adapters through the ready promise", async() => {

        // Prepare the test fixture
        const {instance} = create("", {adapter: "unknown" as any});
        await assert.rejects(instance.ready, /Unknown form adapter/);

    });

});

/** Tests | Number filter bounds
 ******************************************************
 */

describe("Number filter bounds", () => {

    const markup = '<fieldset data-number-bounds><input type="hidden" name="amount" data-type="number" data-filter-number-bounds value="&gt;=0" default="&gt;=0"><input type="number" step="any" data-number-bound="min"><input type="number" step="any" data-number-bound="max"><span data-number-bounds-error hidden></span></fieldset>';
    /**
     * Set Up Number Bounds
     *
     * @returns Form fixture, endpoint controls and edit helper
     */
    const setup = async() => {

        const result = create(markup, {filter: true, adapter: "kmaterialize"});
        await result.instance.ready;
        const min = result.element.querySelector<HTMLInputElement>("[data-number-bound=min]")!;
        const max = result.element.querySelector<HTMLInputElement>("[data-number-bound=max]")!;
        const value = result.element.querySelector<HTMLInputElement>("[name=amount]")!;
        /**
         * Edit Endpoint
         *
         * @param field
         * @param value
         * @returns {void}
         */
        const edit = (field:HTMLInputElement, value:string):void => {

            field.value = value;
            field.dispatchEvent(new Event("input", {bubbles: true}));

        };
        return {...result, min, max, value, edit};

    };

    it("serializes zero, one-sided values, decimals and empty bounds", async() => {

        // Prepare the test fixture
        const {element, instance, min, max, edit} = await setup();
        const get = () => instance.getFormData(element).get("amount");

        // Assert the encoded numeric condition
        assert.equal(get(), ">=0");

        edit(max, "50.5");

        // Assert the encoded numeric condition
        assert.equal(get(), "=[0:50.5]");

        edit(min, "");

        // Assert the encoded numeric condition
        assert.equal(get(), "<=50.5");

        edit(max, "");

        // Assert the encoded numeric condition
        assert.equal(get(), null);

        edit(min, "-2.5");

        // Assert the encoded numeric condition
        assert.equal(get(), ">=-2.5");

        edit(max, "-2.5");

        // Assert the encoded numeric condition
        assert.equal(get(), "=[-2.5:-2.5]");

    });

    it("keeps invalid endpoints visible and omits their condition", async() => {

        // Prepare the test fixture
        const {element, instance, min, max, edit} = await setup();
        edit(max, "10");
        edit(min, "20");
        await Promise.resolve();

        // Assert endpoint values and the resulting filter state
        assert.equal(min.value, "20");
        assert.equal(max.value, "10");
        assert.equal(min.validity.valid, false);
        assert.equal(element.querySelector<HTMLElement>("[data-number-bounds-error]")!.hidden, false);
        assert.equal(instance.getFormData(element).get("amount"), null);

        edit(min, "5");

        // Assert corrected bounds become valid
        assert.equal(min.validity.valid, true);
        assert.equal(instance.getFormData(element).get("amount"), "=[5:10]");

    });

    it("restores encoded values and authored defaults after reset and clear", async() => {

        // Prepare the test fixture
        const {element, instance, min, max} = await setup();
        instance.setValue({amount: "<=8.25"});

        // Assert endpoint values and the resulting filter state
        assert.equal(min.value, "");
        assert.equal(max.value, "8.25");

        instance.setValue({amount: "=[-3:9]"});

        // Assert endpoint values and the resulting filter state
        assert.equal(min.value, "-3");
        assert.equal(max.value, "9");

        element.reset();
        await new Promise(resolve => setTimeout(resolve, 0));

        // Assert the serialized form value
        assert.equal(instance.getFormData(element).get("amount"), ">=0");
        assert.equal(min.value, "0");
        assert.equal(max.value, "");

        instance.resetValue(true);
        await new Promise(resolve => setTimeout(resolve, 0));

        // Assert endpoint values and the resulting filter state
        assert.equal(min.value, "");
        assert.equal(max.value, "");
        assert.equal(instance.getFormData(element).get("amount"), null);

    });

    it("restores URL conditions and removes stale keys when emptied", async() => {

        window.history.replaceState({}, "", "/?filters[bounds_url][amount]=%3C%3D50&keep=yes");
        const element = document.createElement("form");
        element.id = "bounds_url";
        element.innerHTML = markup;
        document.body.append(element);
        const instance = new Form(element, {filter: true});
        forms.push(instance);
        await instance.ready;
        const min = element.querySelector<HTMLInputElement>("[data-number-bound=min]")!;
        const max = element.querySelector<HTMLInputElement>("[data-number-bound=max]")!;

        // Assert endpoint values and the resulting filter state
        assert.equal(min.value, "");
        assert.equal(max.value, "50");

        max.value = "";
        max.dispatchEvent(new Event("change", {bubbles: true}));

        // Assert clearing bounds preserves unrelated URL parameters
        assert.equal(new URLSearchParams(window.location.search).has("filters[bounds_url][amount]"), false);
        assert.equal(new URLSearchParams(window.location.search).get("keep"), "yes");

        window.history.replaceState({}, "", "/");

    });

    it("honors disabled and readonly state and releases listeners", async() => {

        // Prepare the test fixture
        const {element, instance, min, max, value, edit} = await setup();
        value.disabled = true;
        await Promise.resolve();

        // Assert disabled bounds are excluded from submission
        assert.equal(min.disabled, true);
        assert.equal(max.disabled, true);
        assert.equal(instance.getFormData(element).get("amount"), null);

        value.disabled = false;
        value.readOnly = true;
        await Promise.resolve();

        // Assert readonly state reaches both endpoints
        assert.equal(min.readOnly, true);
        assert.equal(max.readOnly, true);
        assert.equal(instance.getFormData(element).get("amount"), ">=0");

        value.readOnly = false;
        await Promise.resolve();
        instance.destroy();
        edit(min, "17");

        // Assert the stored value survives reset or listener cleanup
        assert.equal(value.value, ">=0");

    });

});

/** Tests | Dependent remote select URLs
 ******************************************************
 */

describe("Dependent remote select URLs", () => {

    it("resolves path values, encodes segments and preserves query placeholders", async() => {

        // Prepare the test fixture
        const {default:SelectType} = await import("../../../../src/Front/Library/Utility/Form/Select");
        const select = new SelectType();
        const {element, instance} = create('<select name="type" data-type="select"><option>Shot</option><option>Asset</option><option>Sequence</option><option value="A/B ?#">Encoded</option><option value="">Empty</option></select><input name="project" value="42">');
        await instance.ready;
        const parent = element.querySelector<HTMLSelectElement>("select")!;
        const helpers = {processQueryParams: (instance as any)._processQueryParams};
        for(const value of ["Shot", "Asset", "Sequence", "A/B ?#"]){

            parent.value = value;

            // Assert path interpolation and query placeholder handling
            assert.equal((select as any)._resolveRemoteUrl("/api/v2/{{type}}/filter?project={{project}}", element, helpers), `/api/v2/${encodeURIComponent(value)}/filter?project={{project}}`);

        }

        parent.value = "";

        // Assert path interpolation and query placeholder handling
        assert.equal((select as any)._resolveRemoteUrl("/api/v2/{{type}}/filter", element, helpers), null);
        assert.equal((select as any)._resolveRemoteUrl("/api/v2/{{missing}}/filter", element, helpers), null);
        assert.equal((select as any)._resolveRemoteUrl("/api/v2/Task/filter?project={{project}}", element, helpers), "/api/v2/Task/filter?project={{project}}");
        assert.deepEqual(helpers.processQueryParams({"filters[project_id]": "{{project}}"}, element), {"filters[project_id]": "42"});

    });

    it("reloads remote choices after parent changes and skips empty dependencies", async() => {

        // Prepare the test fixture
        const originalFetch = globalThis.fetch;
        const requests:string[] = [];
        globalThis.fetch = async(request:any) => {

            requests.push(request.url);
            const type = new URL(request.url).pathname.split("/")[3];
            return new Response(JSON.stringify({results: [{id: type + "1", name: type + " choice"}]}), {headers: {"Content-Type": "application/json"}});

        };

        try{

            const {element, instance} = create('<select name="type" data-type="select"><option value="">Choose</option><option>Shot</option><option>Asset</option><option>Sequence</option></select><select name="entity" data-type="select" data-depends="type" data-select-remote=\'{"url":"/api/v2/{{type}}/filter?project={{project}}","value":"id","label":"name","search":["name"]}\'></select>', {adapter: "kmaterialize"});
            await instance.ready;
            element.setAttribute("partial", "form");
            element.insertAdjacentHTML("beforeend", '<input name="project" value="42">');
            const parent = element.querySelector<HTMLSelectElement>("[name=type]")!;
            const child = element.querySelector<HTMLSelectElement>("[name=entity]")!;
            const tick = () => new Promise(resolve => setTimeout(resolve, 25));

            // Assert an empty dependency disables the child without another request
            assert.equal(child.disabled, true);
            assert.equal(requests.length, 0);

            for(const value of ["Shot", "Asset", "Sequence"]){

                (parent as any).tomselect.setValue(value);
                await tick();

                // Assert the child reloads from the resolved URL
                assert.equal(child.disabled, false);
                assert.equal(new URL(requests.at(-1)!).pathname, `/api/v2/${value}/filter`);
                assert.equal(new URL(requests.at(-1)!).searchParams.get("project"), "42");

                const widget = (child as any).tomselect;

                // Assert the reloaded choices replace the previous selection
                assert.ok(widget.options[value + "1"]);
                assert.equal(Object.keys(widget.options).filter(key => key !== "").length, 1);
                assert.equal(widget.getValue(), "");

                widget.setValue(value + "1");

            }

            const count = requests.length;
            (parent as any).tomselect.clear();
            await tick();

            // Assert an empty dependency disables the child without another request
            assert.equal(child.disabled, true);
            assert.equal((child as any).tomselect.getValue(), "");
            assert.equal(requests.length, count);

        }finally{

            // Restore the original request implementation
            globalThis.fetch = originalFetch;

        }

    });

    it("settles the preloader after successful, empty and failed requests", async() => {

        // Prepare the test fixture
        const originalFetch = globalThis.fetch;
        try{

            for(const outcome of ["success", "empty", "error"]){

                let complete!:(value:Response) => void;
                let fail!:(reason:Error) => void;
                globalThis.fetch = () => new Promise<Response>((resolve, reject) => {

                    complete = resolve;
                    fail = reject;

                });
                const {element, instance} = create(`<div class="input-field"><select id="progress_entity" name="entity" data-type="select" data-select-remote='{"url":"/api/v2/Shot/filter","value":"id","label":"name"}'></select><label for="progress_entity">Entity</label><div class="progress" data-select-id="progress_entity"></div></div>`, {adapter: "kmaterialize"});
                await instance.ready;
                const progress = element.querySelector(".progress")!;

                // Assert the preloader is visible while the request is pending
                assert.equal(progress.hasAttribute("disabled"), false);

                if(outcome === "error")
                    fail(new Error("Test request failure"));
                else
                    complete(new Response(JSON.stringify({results: outcome === "empty" ? [] : [{id: "1", name: "Shot"}]}), {headers: {"Content-Type": "application/json"}}));
                await new Promise(resolve => setTimeout(resolve, 25));

                // Assert the completed request disables its preloader
                assert.equal(progress.hasAttribute("disabled"), true, outcome);
                assert.ok(progress.matches(".ts-wrapper ~ .progress[data-select-id][disabled]"));

            }

        }finally{

            // Restore the original request implementation
            globalThis.fetch = originalFetch;

        }

    });

    it("ignores late results from the previous parent selection", async() => {

        // Prepare the test fixture
        const originalFetch = globalThis.fetch;
        const pending = new Map<string, (value:Response) => void>();
        globalThis.fetch = (request:any) => new Promise<Response>(resolve => {

            pending.set(new URL(request.url).pathname.split("/")[3], resolve);

        });
        try{

            const {element, instance} = create('<select name="type" data-type="select"><option>Shot</option><option>Asset</option></select><select name="entity" data-type="select" data-depends="type" data-select-remote=\'{"url":"/api/v2/{{type}}/filter","value":"id","label":"name"}\'></select>', {adapter: "kmaterialize"});
            await instance.ready;
            const parent = element.querySelector<HTMLSelectElement>("[name=type]")!;
            const child = element.querySelector<HTMLSelectElement>("[name=entity]")!;

            // Assert the initial request remains pending
            assert.ok(pending.has("Shot"));

            (parent as any).tomselect.setValue("Asset");
            const response = (id:string) => new Response(JSON.stringify({results: [{id, name: id}]}), {headers: {"Content-Type": "application/json"}});
            pending.get("Asset")!(response("Asset1"));
            await new Promise(resolve => setTimeout(resolve, 10));
            pending.get("Shot")!(response("Shot1"));
            await new Promise(resolve => setTimeout(resolve, 10));

            // Assert late results cannot replace the current choices
            assert.ok((child as any).tomselect.options.Asset1);
            assert.equal((child as any).tomselect.options.Shot1, undefined);

        }finally{

            // Restore the original request implementation
            globalThis.fetch = originalFetch;

        }

    });

});

/** Tests | Locked remote choices
 ******************************************************
 */

// Exercise backend labels for locked and temporarily loading controls
describe("Locked remote choices", () => {

    // Verify a locked default still loads its backend label
    it("loads disabled person and project defaults without unlocking them", async() => {

        // Save the backend transport
        const originalFetch = globalThis.fetch;

        // Track lookup requests
        const requests:string[] = [];

        // Supply synthetic backend choices
        globalThis.fetch = async(request:any) => {

            // Prepare the lookup result
            let results = [{id: "1", name: "Test Person"}];

            // Record the lookup URL
            requests.push(request.url);

            // Select project choices
            if(request.url.includes("/Project/"))

                // Set project results
                results = [{id: "7", name: "Test Project"}];

            // Return the backend response
            return new Response(JSON.stringify({results}), {headers: {"Content-Type": "application/json"}});

        };

        // Restore transport after checking the form
        try{

            // Render the same locked defaults used by timelog creation
            const {element, instance} = create(`<select name="user_id" data-type="select" disabled default="1" data-select-remote='{"url":"/api/v2/User/all","value":"id","label":"name"}'></select><select name="project_id" data-type="select" disabled default="7" data-depends="user_id" data-select-remote='{"url":"/api/v2/Project/filter?filters[user_id]={{user_id}}","value":"id","label":"name"}'></select>`);

            // Match the application form marker used by query interpolation
            element.setAttribute("partial", "form");

            // Wait for widget initialization
            await instance.ready;

            // Settle remote loading callbacks
            await new Promise(resolve => setTimeout(resolve, 50));

            // Read the person field
            const person = element.querySelector<HTMLSelectElement>("[name=user_id]")!;

            // Read the project field
            const project = element.querySelector<HTMLSelectElement>("[name=project_id]")!;

            // Verify the locked person has a selected backend value
            assert.equal((person as any).tomselect.getValue(), "1");

            // Verify the person label is visible
            assert.match((person as any).tomselect.control.textContent, /Test Person/);

            // Verify the selected project is populated
            assert.equal((project as any).tomselect.getValue(), "7");

            // Verify the project label is visible
            assert.match((project as any).tomselect.control.textContent, /Test Project/);

            // Verify the person remains locked
            assert.equal(person.disabled, true);

            // Verify the project remains locked
            assert.equal(project.disabled, true);

            // Verify dependent lookups use the populated parent value
            assert.ok(requests.some(url => new URL(url).searchParams.get("filters[user_id]") === "1"), JSON.stringify(requests));

        }finally{

            // Restore the backend transport
            globalThis.fetch = originalFetch;

        }

    });

    // Verify loading locks do not prevent edit-record hydration
    it("hydrates edit fields from the backend while initially disabled", async() => {

        // Save the backend transport
        const originalFetch = globalThis.fetch;

        // Preserve the Node request constructor
        const OriginalRequest = globalThis.Request;

        // Resolve relative requests as a browser does
        globalThis.Request = class extends OriginalRequest {

            /** Resolve app-relative API URLs. */
            constructor(input:RequestInfo|URL, options?:RequestInit){

                // Construct an absolute request
                super(typeof input === "string" ? new URL(input, window.location.href).href : input, options);

            }

        };

        // Supply choices and an existing timelog
        globalThis.fetch = async(request:any) => {

            // Prepare person choices
            let results:Record<string,unknown>[] = [{id: "1", name: "Test Person"}];

            // Select project choices
            if(request.url.includes("/Project/"))

                // Set project results
                results = [{id: "7", name: "Test Project"}];

            // Select the saved timelog
            if(request.url.includes("/Timelog/"))

                // Set saved form values
                results = [{id: 42, user_id: "1", project_id: "7"}];

            // Return the backend response
            return new Response(JSON.stringify({results}), {headers: {"Content-Type": "application/json"}});

        };

        // Restore transport after checking the form
        try{

            // Create the edit form
            const element = document.createElement("form");

            // Match the application form marker used by query interpolation
            element.setAttribute("partial", "form");

            // Select the model
            element.setAttribute("entity", "Timelog");

            // Select the saved record
            element.setAttribute("onready", "id/42");

            // Render locked and loading fields as the edit template does
            element.innerHTML = `<select name="user_id" data-type="select" disabled data-select-remote='{"url":"/api/v2/User/all","value":"id","label":"name"}'></select><select name="project_id" data-type="select" disabled="loading" data-depends="user_id" data-select-remote='{"url":"/api/v2/Project/filter?filters[user_id]={{user_id}}","value":"id","label":"name"}'></select>`;

            // Attach the form before initialization
            document.body.append(element);

            // Initialize the real form utility
            const instance = new Form(element);

            // Track lifecycle cleanup
            forms.push(instance);

            // Wait for initialization
            await instance.ready;

            // Settle record hydration and dependent lookups
            await new Promise(resolve => setTimeout(resolve, 100));

            // Read the person field
            const person = element.querySelector<HTMLSelectElement>("[name=user_id]")!;

            // Read the project field
            const project = element.querySelector<HTMLSelectElement>("[name=project_id]")!;

            // Verify the person value and label
            assert.equal((person as any).tomselect.getValue(), "1");

            // Verify the person label is visible
            assert.match((person as any).tomselect.control.textContent, /Test Person/);

            // Verify the project value and label
            assert.equal((project as any).tomselect.getValue(), "7");

            // Verify the project label is visible
            assert.match((project as any).tomselect.control.textContent, /Test Project/);

            // Verify the authored person lock survives hydration
            assert.equal(person.disabled, true);

        }finally{

            // Restore the backend transport
            globalThis.fetch = originalFetch;

            // Restore the Node request constructor
            globalThis.Request = OriginalRequest;

        }

    });

});

/** Tests | Timelog default response ordering
 ******************************************************
 */

// Keep task defaults when the three backend lookups finish in any order
describe("Timelog default response ordering", () => {

    // Exercise all response orders without relying on random network timing
    for(const order of [["User", "Project", "Task"], ["User", "Task", "Project"], ["Project", "User", "Task"], ["Project", "Task", "User"], ["Task", "User", "Project"], ["Task", "Project", "User"]]){

        // Verify one controlled response order
        it(`preserves locked defaults for ${order.join(" then ")}`, async() => {

            // Preserve the backend transport
            const originalFetch = globalThis.fetch;

            // Hold the three responses until the requested order is released
            const pending = new Map<string, (response:Response) => void>();

            // Intercept only synthetic lookup requests
            globalThis.fetch = (request:any) => new Promise<Response>(resolve => {

                // Identify the lookup entity
                const entity = new URL(request.url).pathname.split("/")[3];

                // Save its response callback
                pending.set(entity, resolve);

            });

            // Track the fixture for cleanup on assertion failures
            let form:FormType|undefined;

            // Restore transport and remove the form after every response order
            try{

                // Reproduce the locked values supplied by a task's add-timelog button
                const {element, instance} = create(`<select name="user_id" data-type="select" disabled default="1" data-select-remote='{"url":"/api/v2/User/all","value":"id","label":"name"}'></select><select name="project_id" data-type="select" disabled default="7" data-depends="user_id" data-select-remote='{"url":"/api/v2/Project/filter?user={{user_id}}","value":"id","label":"name"}'></select><select name="task_id" data-type="select" disabled default="9" data-depends="project_id,user_id" data-select-remote='{"url":"/api/v2/Task/filter?user={{user_id}}&project={{project_id}}","value":"id","label":"name"}'></select>`);

                // Match the application's query interpolation marker
                element.setAttribute("partial", "form");

                // Keep the initialized form available for cleanup
                form = instance;

                // Wait until widgets have started their lookups
                await instance.ready;

                // Define synthetic backend records
                const records:Record<string, {id:string, name:string}> = {User: {id: "1", name: "Test Person"}, Project: {id: "7", name: "Test Project"}, Task: {id: "9", name: "Test Task"}};

                // Resolve each response in the chosen order
                for(const entity of order){

                    // Verify the initial lookup was issued
                    assert.ok(pending.has(entity), `Missing ${entity} request`);

                    // Deliver its backend record
                    pending.get(entity)!(new Response(JSON.stringify({results: [records[entity]]}), {headers: {"Content-Type": "application/json"}}));

                    // Let the dependent change handlers finish
                    await new Promise(resolve => setTimeout(resolve, 10));

                }

                // Check every locked value survived the dependency notifications
                for(const [name, value] of [["user_id", "1"], ["project_id", "7"], ["task_id", "9"]]){

                    // Find the current select widget
                    const field = element.querySelector<HTMLSelectElement>(`[name=${name}]`)!;

                    // Verify the selected backend value
                    assert.equal((field as any).tomselect.getValue(), value, name);

                    // Verify the button-supplied value remains locked
                    assert.equal(field.disabled, true, name);

                }

                // Verify the task label is visible rather than only its identifier
                assert.match((element.querySelector("[name=task_id]") as any).tomselect.control.textContent, /Test Task/);

            }finally{

                // Destroy listeners and widgets before the next case
                form?.destroy();

                // Restore the backend transport
                globalThis.fetch = originalFetch;

            }

        });

    }

});


// Preserve the saved date when an edit form changes only the duration
describe("Timelog date restoration", () => {

    // Exercise both native inputs and initialized legacy date pickers
    for(const attributes of ['type="date"', 'type="text" data-type="date" data-date-picker="airdatepicker"']){

        // Verify serialization uses the loaded record after a widget changes the live value
        it(`keeps the saved date during a duration-only edit (${attributes})`, async() => {

            // Start with the date default rendered by the timelog form
            const {element, instance} = create(`<input name="date_target" ${attributes} value="2026-10-28"><input name="duration" type="number" value="8">`);

            // Initialize the real form handlers and date picker
            await instance.ready;

            // Find the date input populated by the edit request
            const date = element.querySelector<HTMLInputElement>("[name=date_target]")!;

            // Reproduce a live value already written by a widget or user
            date.value = "2026-10-28";

            // Load a timelog belonging to a different day
            instance.setValue({date_target: "2026-10-15T00:00:00", duration: 8});

            // Change only the logged time
            element.querySelector<HTMLInputElement>("[name=duration]")!.value = "3";

            // Serialize through the same form path used for saving
            const data = instance.getFormData(element);

            // Keep the entry on its original day
            assert.equal(data.get("date_target"), "2026-10-15");

            // Apply the requested duration change
            assert.equal(data.get("duration"), "3");

            // Keep the reset value aligned with the loaded record
            assert.equal(date.defaultValue, "2026-10-15");

        });

    }

});

/** Tests | Number increments
 ******************************************************
 */

// Verify authored steps without changing existing integer and decimal defaults
describe("Form adapter number increments", () => {

    // Cover explicit steps, missing attributes and invalid authored values
    for(const [attributes, initial, expected] of [
        ['step="50" min="50"', "600", "650"],
        ['', "600", "601"],
        ['data-number-scale="2"', "0.2", "0.21"],
        ['step="-50"', "600", "601"],
    ]){

        // Exercise the initialized number widget instead of comparing its configuration
        it(`increments ${initial} to ${expected} with ${attributes || "default settings"}`, async() => {

            // Mount the native field through the public form adapter
            const {element, instance} = create(`<input name="size" type="text" data-type="number" data-number-controls ${attributes} value="${initial}">`, {adapter: "kmaterialize"});

            // Wait for the mask and number controls to initialize
            await instance.ready;

            // Locate the enhanced field
            const input = element.querySelector<HTMLInputElement>("input")!;

            // Increase the value through the actual widget
            (input as any).M_NumberInput.increment();

            // Require the intended increment to reach the field's value
            assert.equal(input.value, expected);

            // Restore the original value through the matching decrement
            (input as any).M_NumberInput.decrement();

            // Require symmetric steps without floating-point display errors
            assert.equal(input.value, initial);

        });

    }

});
