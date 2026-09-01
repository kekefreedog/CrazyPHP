/**
 * cssInlineStyle Test
 *
 * Tests for Front TS cssInlineStyle utility methods
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
import cssInlineStyle from "../../../../src/Front/Library/Utility/cssInlineStyle";

/**
 * cssInlineStyle
 *
 * Methods for test css inline style methods
 *
 * `cssInlineStyle` reads `window.getComputedStyle`/`document` and writes to
 * `navigator.clipboard`, none of which node:test provides. We install a
 * JSDOM window (plus a stubbed clipboard) for the duration of the suite, and
 * tear it down right after.
 */
describe("Front/Library/Utility/cssInlineStyle", () => {

    // Set originals (undefined outside a browser/jsdom)
    let originalWindow: any;
    let originalDocument: any;
    let originalNavigator: any;

    // Set dom instance (reused, body rebuilt per test)
    let dom: JSDOM;

    before(() => {

        // Set dom
        dom = new JSDOM("<!doctype html><html><body></body></html>", { url: "http://localhost/" });

        // Save originals
        originalWindow = (globalThis as any).window;
        originalDocument = (globalThis as any).document;
        originalNavigator = (globalThis as any).navigator;

        // Install jsdom's globals
        (globalThis as any).window = dom.window;
        (globalThis as any).document = dom.window.document;
        (globalThis as any).navigator = dom.window.navigator;

    });

    after(() => {

        // Restore originals
        (globalThis as any).window = originalWindow;
        (globalThis as any).document = originalDocument;
        (globalThis as any).navigator = originalNavigator;

    });

    /**
     * Build a parent/child DOM pair with inline styles inside the shared
     * jsdom document, and return both elements.
     */
    const buildElements = (): { parent: Element; child: Element } => {

        // Set parent
        const parent = dom.window.document.createElement("div");
        parent.setAttribute("style", "color: red;");

        // Set child
        const child = dom.window.document.createElement("span");
        child.setAttribute("style", "color: green;");
        child.textContent = "hello";

        // Attach child to parent
        parent.appendChild(child);

        // Attach parent to body so getComputedStyle can resolve it
        dom.window.document.body.appendChild(parent);

        // Return elements
        return { parent, child };

    };

    /** constructor / _applyInlineStyles
     ******************************************************
     */
    describe("constructor", () => {

        it("normalizes the element's own declared inline style", () => {

            // Set elements
            const { parent } = buildElements();

            // Instantiate
            new cssInlineStyle(parent);

            // Check result
            assert.equal((parent as HTMLElement).style.color, "rgb(255, 0, 0)");

        });

        it("recursively applies computed styles to descendant elements", () => {

            // Set elements
            const { parent, child } = buildElements();

            // Instantiate
            new cssInlineStyle(parent);

            // Check result
            assert.equal((child as HTMLElement).style.color, "rgb(0, 128, 0)");

        });

    });

    /** getHtml
     ******************************************************
     */
    describe("getHtml", () => {

        it("returns the outerHTML of the processed element", () => {

            // Set elements
            const { parent } = buildElements();

            // Instantiate
            const instance = new cssInlineStyle(parent);

            // Check result
            assert.equal(instance.getHtml(), parent.outerHTML);

        });

        it("includes the normalized styles of nested children", () => {

            // Set elements
            const { parent } = buildElements();

            // Instantiate
            const instance = new cssInlineStyle(parent);

            // Check result
            assert.match(instance.getHtml(), /color:\s*rgb\(0, 128, 0\)/);

        });

    });

    /** copyHtmlToClipboard
     ******************************************************
     */
    describe("copyHtmlToClipboard", () => {

        // Set original clipboard
        let originalClipboard: any;

        before(() => {

            // Save original clipboard
            originalClipboard = (dom.window.navigator as any).clipboard;

        });

        after(() => {

            // Restore original clipboard
            (dom.window.navigator as any).clipboard = originalClipboard;

        });

        it("writes the element's outerHTML to the clipboard", async () => {

            // Set written value holder
            let written: string | null = null;

            // Stub clipboard
            (dom.window.navigator as any).clipboard = {
                writeText: (text: string) => {
                    written = text;
                    return Promise.resolve();
                },
            };

            // Set elements
            const { parent } = buildElements();

            // Instantiate
            const instance = new cssInlineStyle(parent);

            // Copy to clipboard
            instance.copyHtmlToClipboard();

            // Wait for the clipboard promise to resolve
            await new Promise(resolve => setTimeout(resolve, 0));

            // Check result
            assert.equal(written, parent.outerHTML);

        });

        it("does not throw synchronously when the clipboard write is rejected", async () => {

            // Stub clipboard that rejects
            (dom.window.navigator as any).clipboard = {
                writeText: () => Promise.reject(new Error("denied")),
            };

            // Set elements
            const { parent } = buildElements();

            // Instantiate
            const instance = new cssInlineStyle(parent);

            // Check result (must not throw)
            assert.doesNotThrow(() => instance.copyHtmlToClipboard());

            // Wait a tick so the rejection is handled by the internal .catch()
            await new Promise(resolve => setTimeout(resolve, 0));

        });

    });

});
