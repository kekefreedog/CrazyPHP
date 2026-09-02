/**
 * Filter Test
 *
 * Tests for Front TS Filter utility methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { describe, it, before, after } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";
import type FilterType from "../../../../src/Front/Library/Utility/Filter";
import type { FilterItem as FilterItemType } from "../../../../src/Front/Library/Utility/Filter";

// `Filter`'s module graph (through `./Form`) contains browser-only UMD
// bundles that read globals such as `window`/`self` at import time, so the
// real module can only be imported *after* the jsdom environment below has
// been installed - hence the dynamic import in `before()` instead of a
// static top-level import.
let Filter: typeof FilterType;
let FilterItem: typeof FilterItemType;

// Node's own timer implementations must be left in place - jsdom's
// setTimeout/setInterval are bound to its own window and recurse infinitely
// if cross-wired with Node's.
const TIMER_KEYS = new Set(["setTimeout", "clearTimeout", "setInterval", "clearInterval", "setImmediate", "clearImmediate", "queueMicrotask"]);

/**
 * Shared setup for both describe blocks below: install a full jsdom window
 * as the global environment (needed for `Filter`'s constructor to build a
 * real `Form` instance, which in turn pulls in the full app runtime -
 * MaterializeCSS, filepond, `window.Crazyobject`, ...), stub the single
 * piece of global app state (`window.Crazyobject.currentPage`) that `Form`'s
 * constructor reads synchronously, then import the module under test.
 * `Form`'s own asynchronous initialization chain is not awaited anywhere -
 * so tests here only exercise the parts of `Filter`/`FilterItem` that don't
 * depend on that chain having settled.
 */
before(async () => {

    // Set dom
    const dom = new JSDOM("<!doctype html><html><body></body></html>", { url: "http://localhost/" });

    // Set window
    const window = dom.window as any;

    // Copy every jsdom global onto the node global, timers excluded
    for(const key of Object.getOwnPropertyNames(window))
        if(!TIMER_KEYS.has(key))
            try { (globalThis as any)[key] = window[key]; } catch { /* read-only global, ignore */ }

    // Install missing window/document/navigator references
    (globalThis as any).window = window;
    (globalThis as any).document = window.document;
    (globalThis as any).navigator = window.navigator;

    // Stub the app-wide state Form's constructor reads synchronously
    window.Crazyobject = { currentPage: { get: () => undefined, set: () => {} } };

    // Import the module under test now that the environment is ready
    const module = await import("../../../../src/Front/Library/Utility/Filter");
    Filter = module.default;
    FilterItem = module.FilterItem;

});

/**
 * Filter
 *
 * Methods for test Filter methods
 */
describe("Front/Library/Utility/Filter", () => {

    /** Filter
     ******************************************************
     */
    describe("constructor", () => {

        it("creates a hidden form and appends it to the container", () => {

            // Set container
            const container = document.createElement("div");

            // Set instance
            const f = new Filter(container, "my-filter", []);

            // Check result
            assert.equal(f.getFormEl().tagName, "FORM");
            assert.equal(f.getFormEl().id, "my-filter");
            assert.equal(f.getFormEl().classList.contains("hide"), true);
            assert.equal(container.contains(f.getFormEl()), true);

        });

        it("ingests a collection item given as a real HTMLElement", () => {

            // Set container
            const container = document.createElement("div");

            // Set select
            const select = document.createElement("select");
            container.appendChild(select);

            // Set instance
            const f = new Filter(container, "f2", [{ name: "status", el: select }]);

            // Check result
            assert.deepEqual(Object.keys(f.getItems()), ["status"]);
            assert.equal(f.getItems()["status"].length, 1);
            assert.equal(f.getItems()["status"][0]._itemEl, select);

        });

        it("ingests a collection item given as a css selector resolved against the container", () => {

            // Set container
            const container = document.createElement("div");

            // Set select
            const select = document.createElement("select");
            select.className = "my-select";
            container.appendChild(select);

            // Set instance
            const f = new Filter(container, "f3", [{ name: "status", el: ".my-select" }]);

            // Check result
            assert.equal(f.getItems()["status"][0]._itemEl, select);

        });

        it("groups several collection entries sharing the same name", () => {

            // Set container
            const container = document.createElement("div");

            // Set elements
            const selectA = document.createElement("select");
            const selectB = document.createElement("select");
            container.appendChild(selectA);
            container.appendChild(selectB);

            // Set instance
            const f = new Filter(container, "f4", [
                { name: "status", el: selectA },
                { name: "status", el: selectB },
            ]);

            // Check result
            assert.equal(f.getItems()["status"].length, 2);

        });

        it("skips a collection entry missing a name", () => {

            // Set container
            const container = document.createElement("div");

            // Set select
            const select = document.createElement("select");
            container.appendChild(select);

            // Set instance
            const f = new Filter(container, "f5", [{ name: "", el: select } as any]);

            // Check result
            assert.deepEqual(f.getItems(), {});

        });

        it("skips a collection entry missing an el", () => {

            // Set instance
            const container = document.createElement("div");
            const f = new Filter(container, "f6", [{ name: "status" } as any]);

            // Check result
            assert.deepEqual(f.getItems(), {});

        });

        it("skips a collection entry whose selector doesn't resolve to an element", () => {

            // Set instance
            const container = document.createElement("div");
            const f = new Filter(container, "f7", [{ name: "status", el: ".does-not-exist" }]);

            // Check result
            assert.deepEqual(f.getItems(), {});

        });

        it("creates one hidden input per distinct collection name inside the form", () => {

            // Set container
            const container = document.createElement("div");
            const selectA = document.createElement("select");
            const selectB = document.createElement("select");
            container.appendChild(selectA);
            container.appendChild(selectB);

            // Set instance
            const f = new Filter(container, "f8", [
                { name: "status", el: selectA },
                { name: "author", el: selectB },
            ]);

            // Check result: 2 distinct names => 2 hidden inputs on the form
            const inputs = f.getFormEl().querySelectorAll("input[type=hidden]");
            assert.equal(inputs.length, 2);

        });

    });

    describe("getItems", () => {

        it("returns the ingested collections keyed by name", () => {

            // Set instance
            const container = document.createElement("div");
            const select = document.createElement("select");
            container.appendChild(select);
            const f = new Filter(container, "f9", [{ name: "status", el: select }]);

            // Check result
            assert.ok(f.getItems()["status"][0] instanceof FilterItem);

        });

    });

    describe("getFormEl", () => {

        it("returns the internal form element", () => {

            // Set instance
            const container = document.createElement("div");
            const f = new Filter(container, "f10", []);

            // Check result
            assert.equal(f.getFormEl(), f._formEl);

        });

    });

    describe("getFormInstance", () => {

        it("returns the internal Form instance", () => {

            // Set instance
            const container = document.createElement("div");
            const f = new Filter(container, "f11", []);

            // Check result
            assert.equal(f.getFormInstance(), f._formInstance);
            assert.equal(typeof f.getFormInstance().setValue, "function");

        });

    });

    describe("setOnChange", () => {

        it("delegates to the internal Form instance's setOnChange", () => {

            // Set instance
            const container = document.createElement("div");
            const f = new Filter(container, "f12", []);

            // Set spy in place of the real Form.setOnChange
            let received:any = null;
            f.getFormInstance().setOnChange = (callable:any, options:any) => { received = { callable, options }; };

            // Set callback
            const callback = () => {};

            // Call method
            f.setOnChange(callback, { eventType: "input" } as any);

            // Check result
            assert.equal(received.callable, callback);
            assert.deepEqual(received.options, { eventType: "input" });

        });

    });

});

/**
 * FilterItem
 *
 * Methods for test FilterItem methods
 *
 * `FilterItem` doesn't depend on `Form`/`Filter` being fully constructed: its
 * constructor only stores the values it receives. So it is tested here
 * against a lightweight fake "container" object that only implements the
 * handful of members `FilterItem` actually reads
 * (`getFormInstance().setValue`, `_formInstance.getFormData`, `_formEl`).
 */
describe("Front/Library/Utility/FilterItem", () => {

    /**
     * Build a fake Filter-like container recording every `setValue` call.
     */
    const makeContainer = (formData:FormData = new FormData()) => {

        // Set calls
        const setValueCalls:Record<string, any>[] = [];

        // Set fake form el
        const formEl = {} as HTMLFormElement;

        // Set fake container
        const container:any = {
            _formEl: formEl,
            _formInstance: {
                getFormData: () => formData,
            },
            getFormInstance: () => ({
                setValue: (value:Record<string, any>) => { setValueCalls.push(value); },
            }),
        };

        // Return result
        return { container, setValueCalls };

    };

    describe("init", () => {

        it("calls the collection's init callback with the value found in the form data", () => {

            // Set form data
            const formData = new FormData();
            formData.set("status", "open");

            // Set container
            const { container } = makeContainer(formData);

            // Set input el
            const inputEl = { name: "status" } as HTMLInputElement;

            // Set init spy
            let initCalledWith:any = "not-called";

            // Set collection
            const collection = {
                name: "status",
                el: {} as HTMLElement,
                init: (item:FilterItemType, value:any) => { initCalledWith = value; },
            };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, inputEl);

            // Call method
            item.init();

            // Check result
            assert.equal(initCalledWith, "open");

        });

        it("calls init with null when the form data has no value for the input name", () => {

            // Set container
            const { container } = makeContainer(new FormData());

            // Set input el
            const inputEl = { name: "missing" } as HTMLInputElement;

            // Set init spy
            let initCalledWith:any = "not-called";

            // Set collection
            const collection = {
                name: "missing",
                el: {} as HTMLElement,
                init: (item:FilterItemType, value:any) => { initCalledWith = value; },
            };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, inputEl);

            // Call method
            item.init();

            // Check result
            assert.equal(initCalledWith, null);

        });

        it("doesn't call init when the collection has no init callback", () => {

            // Set container
            const { container } = makeContainer(new FormData());

            // Set collection
            const collection = { name: "status", el: {} as HTMLElement };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status" } as HTMLInputElement);

            // Check result (no throw is enough, there is nothing observable)
            assert.doesNotThrow(() => item.init());

        });

        it("registers the collection's event listener on the item element when both event and set are provided", () => {

            // Set container
            const { container, setValueCalls } = makeContainer(new FormData());

            // Set fake item element able to record/replay a listener
            // (kept as an object property rather than a bare `let`: TS's control flow
            // analysis narrows a bare `let` to its initializer and never revisits that
            // narrowing for a reassignment that only happens inside a nested closure,
            // which would otherwise make `registered` look like it's always `null`)
            const state:{ registered:{ type:string, listener:EventListener } | null } = { registered: null };
            const itemEl:any = {
                addEventListener: (type:string, listener:EventListener) => { state.registered = { type, listener }; },
            };

            // Set collection
            const collection = {
                name: "status",
                el: itemEl,
                event: "change" as keyof HTMLElementEventMap,
                set: (item:FilterItemType, current:any, next:any) => next,
            };

            // Set instance
            const item = new FilterItem(itemEl, collection, container, { name: "status", value: "" } as HTMLInputElement);

            // Call method
            item.init();

            // Check result: listener registered
            assert.equal(state.registered?.type, "change");

            // Simulate the event firing
            state.registered?.listener({ preventDefault: () => {} } as any);

            // Check result: firing the event called set() with no argument, so the
            // collection's set callback receives `next === undefined`
            assert.equal(setValueCalls.length, 1);
            assert.deepEqual(setValueCalls[0], { status: undefined });

        });

        it("doesn't register a listener when the collection has an event but no set callback", () => {

            // Set container
            const { container } = makeContainer(new FormData());

            // Set fake item element
            let addEventListenerCalled = false;
            const itemEl:any = { addEventListener: () => { addEventListenerCalled = true; } };

            // Set collection (event without set)
            const collection = { name: "status", el: itemEl, event: "change" as keyof HTMLElementEventMap };

            // Set instance
            const item = new FilterItem(itemEl, collection, container, { name: "status" } as HTMLInputElement);

            // Call method
            item.init();

            // Check result
            assert.equal(addEventListenerCalled, false);

        });

    });

    describe("get", () => {

        it("returns the raw input value when the collection has no get callback", () => {

            // Set container
            const { container } = makeContainer();

            // Set collection
            const collection = { name: "status", el: {} as HTMLElement };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status", value: "open" } as HTMLInputElement);

            // Check result
            assert.equal(item.get(), "open");

        });

        it("passes the raw value through the collection's get callback", () => {

            // Set container
            const { container } = makeContainer();

            // Set collection
            const collection = {
                name: "status",
                el: {} as HTMLElement,
                get: (item:FilterItemType, current:any) => `${current}-processed`,
            };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status", value: "open" } as HTMLInputElement);

            // Check result
            assert.equal(item.get(), "open-processed");

        });

    });

    describe("set", () => {

        it("forwards the value straight to the container's form instance when the collection has no set callback", () => {

            // Set container
            const { container, setValueCalls } = makeContainer();

            // Set collection
            const collection = { name: "status", el: {} as HTMLElement };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status", value: "" } as HTMLInputElement);

            // Call method
            item.set("closed");

            // Check result
            assert.deepEqual(setValueCalls, [{ status: "closed" }]);

        });

        it("transforms the value through the collection's set callback before forwarding it", () => {

            // Set container
            const { container, setValueCalls } = makeContainer();

            // Set collection
            const collection = {
                name: "status",
                el: {} as HTMLElement,
                set: (item:FilterItemType, current:any, next:any) => `${next}!`,
            };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status", value: "" } as HTMLInputElement);

            // Call method
            item.set("closed");

            // Check result
            assert.deepEqual(setValueCalls, [{ status: "closed!" }]);

        });

        it("does nothing when the collection explicitly disables set", () => {

            // Set container
            const { container, setValueCalls } = makeContainer();

            // Set collection
            const collection = { name: "status", el: {} as HTMLElement, set: false as const };

            // Set instance
            const item = new FilterItem({} as HTMLElement, collection, container, { name: "status", value: "" } as HTMLInputElement);

            // Call method
            item.set("closed");

            // Check result
            assert.deepEqual(setValueCalls, []);

        });

    });

});
