/**
 * Root Test
 *
 * Verify page root reconciliation through Root.setContent().
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Root from "../../../src/Front/Library/Dom/Root";
import { after, before, it } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";

/** Test Environment
 ******************************************************
 */

// Create an isolated browser environment
const dom = new JSDOM("<body></body>");

// Install the browser globals used by the renderer
before(() => {

    // Expose DOM constructors from the same document
    Object.assign(globalThis, {
        document: dom.window.document,
        HTMLElement: dom.window.HTMLElement,
        DOMParser: dom.window.DOMParser,
        Node: dom.window.Node,
    });

});

// Release the browser environment
after(() => dom.window.close());

/** Test Helpers
 ******************************************************
 */

/**
 * Render Page Contents
 *
 * @param html Inner markup of the test page
 * @return Connected page wrapper
 */
const render = (html:string):HTMLElement => {

    // Render the markup inside the page wrapper
    const result = Root.setContent(`<div id="crazy-root">${html}</div>`);

    // Return the connected wrapper
    return result;

};

/**
 * Build Form Markup
 *
 * @param value Default text for both text controls
 * @param checked Default state of the checkbox, radio and first option
 * @return Form control markup in fixture order
 */
const form = (value:string, checked:boolean):string => {

    // Build adjacent controls without introducing whitespace nodes
    const result = [
        `<input value="${value}">`,
        `<textarea>${value}</textarea>`,
        `<input type="checkbox" ${checked ? "checked" : ""}>`,
        `<input type="radio" ${checked ? "checked" : ""}>`,
        `<select multiple><option id="one" ${checked ? "selected" : ""}>One</option>`,
        "<option id=\"two\">Two</option></select>",
    ].join("");

    // Return the form markup
    return result;

};

/** Root Reconciliation Tests
 ******************************************************
 */

// Exercise keyed and positional reuse together
it("moves keyed controls with their listeners and edits", () => {

    // Mount the initial page
    document.body.innerHTML = "<div id=\"crazy-root\"></div>";

    // Create mixed siblings including duplicate keys
    const root = render("<input id=\"a\"><i data-key=\"x\"></i><input id=\"b\"><i data-key=\"x\"></i>");

    // Record initial identities
    const [a, first, b, second] = Array.from(root.children);

    // Edit the first control
    (a as HTMLInputElement).value = "draft";

    // Observe listener retention
    let clicks = 0;

    // Attach an application listener
    a.addEventListener("click", () => {

        // Count the click on the surviving input
        clicks++;

    });

    // Prepend and reorder controls
    render("<b id=\"new\"></b><input id=\"b\"><i data-key=\"x\"></i><input id=\"a\"><i data-key=\"x\"></i>");

    // Check every surviving identity
    assert.ok(
        root.children[1] === b &&
        root.children[2] === first &&
        root.children[3] === a &&
        root.children[4] === second
    );

    // Remove the other siblings
    render("<input id=\"a\">");

    // Activate the surviving input
    (root.firstChild as HTMLInputElement).click();

    // Check listener and state retention
    assert.deepEqual([clicks, (root.firstChild as HTMLInputElement).value], [1, "draft"]);

});

// Exercise ordinary node changes and wrapper replacement
it("returns the connected wrapper and preserves exact text and comments", () => {

    // Mount an empty element containing a break
    document.body.innerHTML = "<div id=\"crazy-root\"><br></div>";

    // Clear all children
    assert.equal(render("").childNodes.length, 0);

    // Replace the wrapper following leading content
    const root = Root.setContent("<style></style><main id=\"crazy-root\">  <!--one--></main>");

    // Check connected identity
    assert.ok(root.isConnected && root.tagName === "MAIN");

    // Update the same wrapper using the fallback
    assert.equal(Root.setContent("<main> <!--two--> </main>"), root);

    // Preserve exact whitespace and comment data
    assert.equal(root.innerHTML, " <!--two--> ");

    // Keep the root on empty input
    assert.equal(Root.setContent(""), root);

});

// Exercise live form state separately from markup defaults
it("preserves edited values until their template defaults change", () => {

    // Mount the initial page
    document.body.innerHTML = "<div id=\"crazy-root\"></div>";

    // Render controls
    const root = render(form("old", true));

    // Resolve the live controls
    const [input, area, checkbox, radio, select] = Array.from(root.children) as [
        HTMLInputElement,
        HTMLTextAreaElement,
        HTMLInputElement,
        HTMLInputElement,
        HTMLSelectElement,
    ];

    // Edit the input
    input.value = "draft";

    // Edit the textarea
    area.value = "draft";

    // Choose another option
    select.selectedIndex = 1;

    // Refresh with unchanged defaults
    render(form("old", true));

    // Check retained edits
    assert.deepEqual([input.value, area.value, select.selectedIndex], ["draft", "draft", 1]);

    // Apply changed defaults
    render(form("new", false));

    // Check all live controls
    assert.deepEqual(
        [input.value, area.value, checkbox.checked, radio.checked, select.selectedIndex],
        ["new", "new", false, false, -1]
    );

});

// Exercise component ownership through the page root
it("patches native slots and projected or parked children while respecting opaque hosts", async () => {

    // Load the component after installing HTMLElement
    const Base = (await import("../../../src/Front/Library/Crazycomponent2")).default;

    /** Light DOM fixture with default and named slots. */
    class lightFixture extends Base {

        /** Public Static Properties
         ******************************************************
         */

        /** Render both light DOM insertion points. */
        public static template = ():string => {

            // Prepare the slot markup
            const result = "<article><slot></slot><slot name=\"title\"></slot></article>";

            // Return the template
            return result;

        };

    }

    /** Shadow DOM fixture with a native default slot. */
    class shadowFixture extends Base {

        /** Public Static Properties
         ******************************************************
         */

        /** Enable the shadow root. */
        public static options = {shadow: true};

        /** Render the default slot. */
        public static template = ():string => {

            // Prepare the native slot markup
            const result = "<slot></slot>";

            // Return the template
            return result;

        };

    }

    // Register the light DOM fixture
    dom.window.customElements.define("light-fixture", lightFixture);

    // Register the native slot fixture
    dom.window.customElements.define("shadow-fixture", shadowFixture);

    // Register an opaque external component
    dom.window.customElements.define("opaque-fixture", class extends HTMLElement {});

    // Mount the initial page
    document.body.innerHTML = "<div id=\"crazy-root\"></div>";

    // Render supplied content
    const root = render(
        "<light-fixture><b id=\"a\">Old</b><i id=\"p\" slot=\"unused\">Parked</i></light-fixture>" +
        "<shadow-fixture>Old</shadow-fixture>" +
        "<opaque-fixture>Old</opaque-fixture>"
    );

    // Let components mount
    await new Promise(resolve => setTimeout(resolve, 0));

    // Remember the component-owned wrapper
    const wrapper = root.querySelector("article");

    // Remember the projected node
    const projected = root.querySelector("#a");

    // Change projected and parked content
    render(
        "<light-fixture><i id=\"p\" slot=\"title\">Visible</i><b id=\"a\">New</b></light-fixture>" +
        "<shadow-fixture>New</shadow-fixture>" +
        "<opaque-fixture>New</opaque-fixture>"
    );

    // Let projection observers run
    await new Promise(resolve => setTimeout(resolve, 0));

    // Check internal and projected identity
    assert.ok(wrapper && wrapper === root.querySelector("article") && projected === root.querySelector("#a"));

    // Check supplied content across ownership modes
    assert.deepEqual(
        [
            projected?.textContent,
            root.querySelector("#p")?.textContent,
            root.children[1].textContent,
            root.children[2].textContent,
        ],
        ["New", "Visible", "New", "Old"]
    );

});

// Exercise partial refresh semantics
it("rebuilds partials on markup changes or explicit refresh", () => {

    // Mount the initial page
    document.body.innerHTML = "<div id=\"crazy-root\"></div>";

    // Render a partial
    const root = render("<section partial=\"box\"><button>A</button></section>");

    // Keep its initial child identity
    const first = root.querySelector("button");

    // Render identical markup
    render("<section partial=\"box\"><button>A</button></section>");

    // Preserve the original button
    assert.equal(root.querySelector("button"), first);

    // Change the markup
    render("<section partial=\"box\"><button>B</button></section>");

    // Remember the rebuilt button
    const second = root.querySelector("button");

    // Verify replacement
    assert.notEqual(second, first);

    // Force another rebuild
    render("<section partial=\"box\" partial-refresh=\"true\"><button>B</button></section>");

    // Verify replacement and flag consumption
    assert.ok(root.querySelector("button") !== second && !root.querySelector("[partial-refresh]"));

});

// Exercise keyed option moves and implicit single-select defaults
it("retains a selected option across moves and applies a changed implicit default", () => {

    // Mount a select without explicit defaults
    document.body.innerHTML = "<div id=\"crazy-root\"><select><option id=\"a\">A</option><option id=\"b\">B</option><option id=\"c\">C</option></select></div>";

    // Resolve the select
    const select = document.querySelector("select")!;

    // Choose the third option
    select.value = "C";

    // Reorder without changing the first option
    render("<select><option id=\"a\">A</option><option id=\"c\">C</option><option id=\"b\">B</option></select>");

    // Preserve the user's selection
    assert.equal(select.value, "C");

    // Move a different option to the implicit default position
    render("<select><option id=\"b\">B</option><option id=\"c\">C</option><option id=\"a\">A</option></select>");

    // Apply the new template default
    assert.equal(select.value, "B");

});
