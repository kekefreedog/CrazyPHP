/**
 * Crazycomponent2 Test
 *
 * Behavioral tests for light DOM and shadow DOM component lifecycles.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { after, before, describe, it } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";
import Handlebars from "handlebars";
import type Component from "../../../src/Front/Library/Crazycomponent2";
import type { Crazycomponent2Properties } from "../../../src/Front/Library/Crazycomponent2";

/** Test Environment
 ******************************************************
 */

let dom: JSDOM;
let Base: typeof Component;
let originalHTMLElement: typeof HTMLElement;
let sequence = 0;
const settle = async () => { await new Promise(resolve => setTimeout(resolve, 0)); };

before(async () => {
    originalHTMLElement = globalThis.HTMLElement;
    dom = new JSDOM("<!doctype html><body></body>", { url: "http://localhost" });
    globalThis.HTMLElement = dom.window.HTMLElement;
    Base = (await import("../../../src/Front/Library/Crazycomponent2")).default;
});

after(() => {
    dom.window.close();
    if (originalHTMLElement === undefined) Reflect.deleteProperty(globalThis, "HTMLElement");
    else globalThis.HTMLElement = originalHTMLElement;
});

/**
 * Create Fixture
 *
 * Register a unique component with typed properties and tracked widget cleanup.
 *
 * @param shadow Whether the fixture uses an open shadow root
 * @return Component constructor, registered name, and unconnected instance
 */
function fixture(shadow = false) {
    class Example extends Base {
        static options = { shadow };
        static properties = {
            label: { type: "string", default: "Initial" },
            count: { type: "number", default: 5, reflect: true },
            enabled: { type: "boolean", default: false, reflect: true },
            size: { type: "string", default: "small", select: ["small", "large"] },
            items: { type: "array", default: [{ id: 1 }] },
            config: { type: "object", default: { nested: { value: 1 } }, reflect: true },
            privateValue: { type: "string", default: "private", attribute: false },
            imageUrl: { type: "string", default: "", attribute: "image-url" },
        } satisfies Crazycomponent2Properties;
        static template = Handlebars.compile('<section><h2>{{attributes.label}}</h2><output>{{attributes.count}}</output><slot name="heading"><b>Fallback title</b></slot><slot>Fallback body</slot><button>Click</button></section>');
        static styles = { default: { toString: () => "section { color: red; }" } };
        renders = 0;
        cleanups = 0;
        clicks = 0;
        postRender() {
            this.renders++;
            const button = this.renderRoot.querySelector("button")!;
            const handler = () => this.clicks++;
            button.addEventListener("click", handler);
            this.onCleanup(() => {
                this.cleanups++;
                button.removeEventListener("click", handler);
            });
        }
    }
    const name = `test-component-${sequence++}`;
    dom.window.customElements.define(name, Example);
    const element = dom.window.document.createElement(name) as Example;
    return { Example, name, element };
}

/** Shared Rendering Modes
 ******************************************************
 */

for (const shadow of [false, true]) {
    describe(`Crazycomponent2 ${shadow ? "shadow" : "light"} DOM`, () => {
        it("constructs without rendering and mounts compiled HBS/CSS after subclass initialization", async () => {
            const { element } = fixture(shadow);
            assert.equal(element.childNodes.length, 0);
            assert.equal(element.shadowRoot !== null, shadow);
            element.setAttribute("label", "<Unsafe>  two spaces");
            dom.window.document.body.append(element);
            assert.equal(await element.updateComplete, true);
            assert.equal(element.renders, 1);
            assert.equal(element.renderRoot.querySelector("h2")!.textContent, "<Unsafe>  two spaces");
            assert.equal(element.renderRoot.querySelector("h2")!.children.length, 0);
            assert.equal(element.renderRoot.querySelector("style")!.textContent, "section { color: red; }");
            assert.equal(element.renderRoot, shadow ? element.shadowRoot : element);
            element.remove();
        });

        it("observes schema attributes, aliases, and defaults while excluding private values", async () => {
            const { Example, element } = fixture(shadow);
            assert.ok(Example.observedAttributes.includes("image-url"));
            assert.ok(!Example.observedAttributes.includes("privatevalue"));
            element.setAttribute("count", "0");
            element.setAttribute("enabled", "");
            element.setAttribute("label", "");
            element.setAttribute("image-url", "image.png");
            element.setAttribute("privatevalue", "external");
            assert.equal(element.getProperty("count"), 0);
            assert.equal(element.getProperty("enabled"), true);
            assert.equal(element.getProperty("label"), "");
            assert.equal(element.getProperty("imageUrl"), "image.png");
            assert.equal(element.getProperty("privateValue"), "private");
            for (const value of ["false", "0", "FALSE"]) {
                element.setAttribute("enabled", value);
                assert.equal(element.getProperty("enabled"), false);
            }
            element.removeAttribute("count");
            assert.equal(element.getProperty("count"), 5);
            await element.updateComplete;
        });

        it("falls back for invalid attributes and validates programmatic values", async () => {
            const { element } = fixture(shadow);
            for (const value of ["NaN", "Infinity", "", "abc"]) {
                element.setAttribute("count", value);
                assert.equal(element.getProperty("count"), 5);
            }
            element.setAttribute("size", "huge");
            assert.equal(element.getProperty("size"), "small");
            element.setAttribute("size", "large");
            assert.equal(element.getProperty("size"), "large");
            assert.throws(() => element.setProperty("count", "5"), /Invalid component property/);
            assert.throws(() => element.setProperty("count", NaN), /Invalid component property/);
            assert.throws(() => element.setProperty("size", "huge"), /Invalid component property/);
            assert.throws(() => element.getProperty("missing"), /Unknown component property/);
            await element.updateComplete;
        });

        it("parses JSON, rejects wrong shapes, and isolates nested defaults", async () => {
            const { Example, element } = fixture(shadow);
            const other = new Example();
            (element.getProperty("config") as { nested: { value: number } }).nested.value = 20;
            assert.deepEqual(other.getProperty("config"), { nested: { value: 1 } });
            (element.getProperty("items") as unknown[]).push(2);
            assert.deepEqual(other.getProperty("items"), [{ id: 1 }]);
            element.setAttribute("items", '[1,2]');
            assert.deepEqual(element.getProperty("items"), [1, 2]);
            element.setAttribute("config", '{"a":1}');
            assert.deepEqual(element.getProperty("config"), { a: 1 });
            for (const invalid of ["null", "[]", "bad JSON"]) {
                element.setAttribute("config", invalid);
                assert.deepEqual(element.getProperty("config"), { nested: { value: 1 } });
            }
            element.setAttribute("items", '{}');
            assert.deepEqual(element.getProperty("items"), [{ id: 1 }]);
            await element.updateComplete;
        });

        it("reflects values without loops and batches synchronous changes", async () => {
            const { element } = fixture(shadow);
            dom.window.document.body.append(element);
            await element.updateComplete;
            element.setProperty("count", 0);
            element.setProperty("enabled", false);
            element.setProperty("config", { a: 2 });
            element.setProperty("label", "Updated");
            assert.equal(element.getAttribute("count"), "0");
            assert.equal(element.getAttribute("enabled"), "false");
            assert.equal(element.getAttribute("config"), '{"a":2}');
            assert.equal(element.getAttribute("label"), null);
            await element.updateComplete;
            assert.equal(element.renders, 2);
            assert.equal(element.renderRoot.querySelectorAll("style").length, 1);
            element.setProperty("count", 0);
            await settle();
            assert.equal(element.renders, 2);
            const circular: Record<string, unknown> = {};
            circular.self = circular;
            assert.throws(() => element.setProperty("config", circular), /circular/i);
            assert.deepEqual(element.getProperty("config"), { a: 2 });
            element.remove();
        });

        it("cleans widgets/listeners before rerender and disconnect, then reconnects", async () => {
            const { element } = fixture(shadow);
            dom.window.document.body.append(element);
            await element.updateComplete;
            const oldButton = element.renderRoot.querySelector("button")!;
            oldButton.click();
            assert.equal(element.clicks, 1);
            element.setProperty("label", "Updated");
            await element.updateComplete;
            oldButton.click();
            assert.equal(element.clicks, 1);
            assert.equal(element.cleanups, 1);
            element.remove();
            assert.equal(element.cleanups, 2);
            element.setAttribute("count", "17");
            assert.equal(await element.updateComplete, false);
            dom.window.document.body.append(element);
            await element.updateComplete;
            assert.equal(element.renderRoot.querySelector("output")!.textContent, "17");
            element.renderRoot.querySelector("button")!.click();
            assert.equal(element.clicks, 2);
            element.remove();
            assert.equal(element.cleanups, 3);
        });

        it("upgrades markup that existed before registration", async () => {
            const name = `test-upgrade-${sequence++}`;
            const wrapper = dom.window.document.createElement("div");
            wrapper.innerHTML = `<${name} count="12"><i>Child</i></${name}>`;
            dom.window.document.body.append(wrapper);
            const { Example } = fixture(shadow);
            class Upgraded extends Example {}
            dom.window.customElements.define(name, Upgraded);
            const element = wrapper.firstElementChild as Upgraded;
            const child = element.querySelector("i");
            await element.updateComplete;
            assert.equal(element.getProperty("count"), 12);
            assert.equal(element.querySelector("i"), child);
            wrapper.remove();
        });

        it("preserves supplied nodes, text, listeners, and input values across updates", async () => {
            const { element } = fixture(shadow);
            const input = dom.window.document.createElement("input");
            input.value = "User text";
            let clicks = 0;
            input.addEventListener("click", () => clicks++);
            const text = dom.window.document.createTextNode("  significant whitespace  ");
            element.append(text, input);
            dom.window.document.body.append(element);
            await element.updateComplete;
            for (const label of ["First", "Second"]) {
                element.setProperty("label", label);
                await element.updateComplete;
                assert.equal(element.querySelector("input"), input);
                assert.equal(input.value, "User text");
                assert.equal(text.textContent, "  significant whitespace  ");
                assert.ok(element.contains(text));
                input.click();
            }
            assert.equal(clicks, 2);
            if (shadow) {
                assert.equal(input.parentNode, element);
                const slot = element.shadowRoot!.querySelector('slot:not([name])') as HTMLSlotElement;
                assert.deepEqual(slot.assignedNodes(), [text, input]);
            } else {
                assert.equal(input.parentNode!.nodeName, "SLOT");
            }
            element.remove();
        });

        it("supports named slots and children appended after rendering", async () => {
            const { element } = fixture(shadow);
            dom.window.document.body.append(element);
            await element.updateComplete;
            const heading = dom.window.document.createElement("h3");
            heading.slot = "heading";
            heading.textContent = "Supplied title";
            element.append(heading, "Late body");
            await settle();
            const slot = element.renderRoot.querySelector('slot[name="heading"]') as HTMLSlotElement;
            if (shadow) assert.deepEqual(slot.assignedNodes(), [heading]);
            else assert.equal(slot.firstChild, heading);
            heading.remove();
            await settle();
            if (shadow) assert.deepEqual(slot.assignedNodes(), []);
            else assert.equal(slot.textContent, "Fallback title");
            element.setProperty("label", "Updated");
            await element.updateComplete;
            assert.equal(element.querySelector("h3"), null);
            element.remove();
        });
    });
}

/** Configuration And Light Projection
 ******************************************************
 */

describe("Crazycomponent2 configuration and light projection", () => {
    it("allows constructor options to override the class shadow default", () => {
        const { Example } = fixture(true);
        const light = new Example({ shadow: false });
        assert.equal(light.shadowRoot, null);
        assert.equal(light.renderRoot, light);
    });

    it("supports asset setters, strings, default exports, and CSS functions", async () => {
        const { element } = fixture();
        element.postRender = () => {};
        element.setHtmlAndCss({ default: "<pre>one  two\nthree</pre>" }, context => `pre { --label: '${context.attributes.label}'; }`);
        dom.window.document.body.append(element);
        await element.updateComplete;
        assert.equal(element.querySelector("pre")!.textContent, "one  two\nthree");
        assert.ok(element.querySelector("style")!.textContent!.includes("Initial"));
        element.setHtmlAndCss("", "");
        await element.updateComplete;
        assert.equal(element.innerHTML, "");
        element.remove();
    });

    it("retains unmatched children until a matching slot becomes available", async () => {
        const { element } = fixture();
        const child = dom.window.document.createElement("span");
        child.slot = "missing";
        element.append(child);
        dom.window.document.body.append(element);
        await element.updateComplete;
        assert.equal(element.contains(child), false);
        child.slot = "heading";
        await settle();
        assert.equal(element.querySelector('slot[name="heading"]')!.firstChild, child);
        child.slot = "missing";
        await settle();
        element.setHtmlAndCss('<slot name="missing"></slot><button></button>', "");
        await element.updateComplete;
        assert.equal(element.querySelector("slot")!.firstChild, child);
        element.remove();
    });

    it("preserves appending order and does not consume a nested component's slots", async () => {
        const { element } = fixture();
        element.setHtmlAndCss('<nested-example><slot name="heading">Nested fallback</slot></nested-example><slot></slot><button></button>', "");
        element.append("First", "Second");
        dom.window.document.body.append(element);
        await element.updateComplete;
        element.append("Third");
        await settle();
        assert.equal(element.querySelector(':scope > slot')!.textContent, "FirstSecondThird");
        assert.equal(element.querySelector("nested-example slot")!.textContent, "Nested fallback");
        element.remove();
    });

    it("rejects duplicate attribute mappings", () => {
        class Invalid extends Base {
            static properties = {
                first: { type: "string", attribute: "same" },
                second: { type: "string", attribute: "same" },
            } satisfies Crazycomponent2Properties;
        }
        assert.throws(() => dom.window.customElements.define(`invalid-${sequence++}`, Invalid), /distinct/);
    });
});
