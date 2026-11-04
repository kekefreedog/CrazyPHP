/**
 * Form Persistence Tests
 *
 * Verify real localStorage caching, scoped restoration and dependency changes.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/** Dependencies */
import {after, before, beforeEach, describe, it} from "node:test";
import assert from "node:assert/strict";
import {JSDOM} from "jsdom";

/** Isolated browser environment. */
let dom:JSDOM;

/** Production persistence helper. */
let Persistence:typeof import("../../../../src/Front/Library/Utility/Form/Persistance").default;

/** Production cache wrapper. */
let Cache:typeof import("../../../../src/Front/Library/Crazycache").default;

/** Original globals restored after testing. */
const descriptors = new Map<string, PropertyDescriptor|undefined>();

// Prepare browser storage before loading localForage
before(async() => {

    // Use a stable origin for real jsdom localStorage
    dom = new JSDOM("<html><body></body></html>", {url: "http://form.test"});

    // Expose only the browser APIs needed by the persistence helper
    for(const key of ["window", "document", "localStorage", "navigator"]){

        // Preserve the host descriptor
        descriptors.set(key, Object.getOwnPropertyDescriptor(globalThis, key));

        // Install the isolated browser property
        Object.defineProperty(globalThis, key, {value: key === "window" ? dom.window : (dom.window as any)[key], configurable: true});

    }

    // Load the helper after browser storage is available
    Persistence = (await import("../../../../src/Front/Library/Utility/Form/Persistance")).default;

    // Use the actual CrazyPHP cache for inspecting stored values
    Cache = (await import("../../../../src/Front/Library/Crazycache")).default;

});

// Isolate each form's DOM and stored values
beforeEach(() => {

    // Remove previous preferences
    dom.window.localStorage.clear();

    // Remove previous controls
    dom.window.document.body.replaceChildren();

});

// Restore the original host environment
after(() => {

    // Release jsdom resources
    dom.window.close();

    // Restore replaced properties
    for(const [key, descriptor] of descriptors){

        // Reinstate previously defined globals
        if(descriptor){

            // Restore the original descriptor
            Object.defineProperty(globalThis, key, descriptor);

        }else{

            // Remove globals introduced by this fixture
            delete (globalThis as any)[key];

        }

    }

});

/** Create a native form containing the supported value shapes and a dependent remote selector. */
function form():HTMLFormElement {

    // Create the isolated form
    const result = dom.window.document.createElement("form");

    // Identify this reusable form
    result.id = "download_reference";

    // Supply representative native inputs without widget mocks
    result.innerHTML = '<input name="filepath"><input name="name"><select name="type"><option>Shot</option><option>Asset</option></select><select name="entity" data-depends="type" data-select-remote="{}"><option value="">Choose</option><option value="10">Shot ten</option></select><input type="checkbox" name="enabled"><textarea name="notes"></textarea><select name="tags[]" multiple><option value="a">A</option><option value="b">B</option></select><input type="password" name="password">';

    // Opt in only the fields whose values should survive reopening
    for(const name of ["name", "type", "entity", "enabled", "notes", "tags[]", "password"]){

        // Mark the native control using the shared form contract
        (result.elements.namedItem(name) as HTMLElement).dataset.persist = "true";

    }

    // Mount the form before dispatching native events
    dom.window.document.body.append(result);

    // Return the fixture form
    return result;

}

/** Resolve one named native control. */
function field(form:HTMLFormElement, name:string):HTMLInputElement {

    // Resolve the control through the native form collection
    const result = form.elements.namedItem(name) as HTMLInputElement;

    // Return the native control
    return result;

}

// Verify actual localStorage persistence without touching an application session
describe("Form persistence", () => {

    // Remember supported controls while excluding paths and credentials
    it("stores values in localStorage and restores parent fields before remote defaults", async() => {

        // Create the source form
        const first = form();

        // Populate ordinary text
        field(first, "name").value = "fromDirector";

        // Populate an excluded source path
        field(first, "filepath").value = "/private/source.mov";

        // Populate a selected entity
        field(first, "entity").value = "10";

        // Populate a checkbox
        field(first, "enabled").checked = true;

        // Populate multiline text
        field(first, "notes").value = "line one\nline two";

        // Select both values in the native multi-select
        Array.from((first.elements.namedItem("tags[]") as HTMLSelectElement).options).forEach(option => option.selected = true);

        // Populate a credential that must not be persisted
        field(first, "password").value = "never-store";

        // Create the opted-in policy
        const options = {enabled: true, scope: "user7:project42", exclude: []};

        // Persist through Crazycache
        await new Persistence(first, options).save();

        // Inspect the actual stored record
        const stored = await new Cache("form-values", {driver: "localStorage"}).get(JSON.stringify([first.id, options.scope]));

        // Require path exclusion
        assert.equal(stored.filepath, undefined);

        // Require credential exclusion
        assert.equal(stored.password, undefined);

        // Require native multiple values
        assert.deepEqual(stored["tags[]"].value, ["a", "b"]);

        // Confirm the localStorage driver was used
        assert.ok(Object.keys(dom.window.localStorage).some(key => key.includes("form-values")));

        // Reopen a fresh form
        const reopened = form();

        // Restore its preferences before widget initialization
        await new Persistence(reopened, options).restore(new dom.window.AbortController().signal);

        // Preserve the remembered reference name
        assert.equal(field(reopened, "name").value, "fromDirector");

        // Keep the source path blank on reopen
        assert.equal(field(reopened, "filepath").value, "");

        // Defer remote entity selection to validated loaded choices
        assert.equal(field(reopened, "entity").getAttribute("default"), "10");

        // Preserve native checkbox values
        assert.equal(field(reopened, "enabled").checked, true);

        // Preserve multiline text
        assert.equal(field(reopened, "notes").value, "line one\nline two");

        // Prevent another project from inheriting the values
        const other = form();

        // Restore a distinct project scope
        await new Persistence(other, {...options, scope: "user7:project43"}).restore(new dom.window.AbortController().signal);

        // Require isolated preferences
        assert.equal(field(other, "name").value, "");

    });

    // Prevent old entity IDs from leaking between selected models
    it("invalidates saved children when their parent changes", async() => {

        // Mount a populated form
        const current = form();

        // Select an entity from the original model
        field(current, "entity").value = "10";

        // Record the scoped persistence policy
        const persistence = new Persistence(current, {enabled: true, scope: "one", exclude: []});

        // Save the original values
        await persistence.save();

        // Watch the real native change events
        const controller = new dom.window.AbortController();

        // Attach lifecycle-owned listeners
        persistence.watch(controller.signal);

        // Change the source model without selecting a new entity
        field(current, "type").value = "Asset";

        // Trigger dependency invalidation
        field(current, "type").dispatchEvent(new dom.window.Event("change", {bubbles: true}));

        // Wait for the latest state to be stored
        await persistence.save();

        // Inspect the saved record
        const stored = await new Cache("form-values", {driver: "localStorage"}).get(JSON.stringify([current.id, "one"]));

        // Keep the new parent value
        assert.equal(stored.type.value, "Asset");

        // Remove the entity from the old model
        assert.equal(stored.entity, undefined);

        // Release form event listeners
        controller.abort();

    });

});
