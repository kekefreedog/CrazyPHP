/**
 * Hash Test
 *
 * Tests for Front TS Hash utility methods
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
import Hash from "../../../../src/Front/Library/Utility/Hash";

/**
 * Hash
 *
 * Methods for test hash methods
 *
 * `Hash` reads `document.querySelector()`/`HTMLMetaElement` (for the meta
 * tag lookups) which node:test does not provide. We install a JSDOM window
 * for the duration of the whole suite, and tear it down right after.
 */
describe("Front/Library/Utility/Hash", () => {

    // Set originals (undefined outside a browser/jsdom)
    let originalWindow: any;
    let originalDocument: any;
    let originalHTMLMetaElement: any;

    before(() => {

        // Set dom
        const dom = new JSDOM(`<!doctype html><html><head></head><body></body></html>`);

        // Save originals
        originalWindow = (globalThis as any).window;
        originalDocument = (globalThis as any).document;
        originalHTMLMetaElement = (globalThis as any).HTMLMetaElement;

        // Install jsdom's window/document/HTMLMetaElement
        (globalThis as any).window = dom.window;
        (globalThis as any).document = dom.window.document;
        (globalThis as any).HTMLMetaElement = dom.window.HTMLMetaElement;

    });

    after(() => {

        // Restore originals
        (globalThis as any).window = originalWindow;
        (globalThis as any).document = originalDocument;
        (globalThis as any).HTMLMetaElement = originalHTMLMetaElement;

    });

    /** constructor
     ******************************************************
     */
    describe("constructor", () => {

        it("leaves the hash unset when no value is given", () => {

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(hash.get(), null);

        });

        it("sets the hash when a value is given", () => {

            // Set instance
            const hash = new Hash("initial-hash");

            // Check result
            assert.equal(hash.get(), "initial-hash");

        });

    });

    /** set / get
     ******************************************************
     */
    describe("set", () => {

        it("stores the given hash and returns true", () => {

            // Set instance
            const hash = new Hash();

            // Set hash
            const result = hash.set("abc");

            // Check result
            assert.equal(result, true);

            // Check stored value
            assert.equal(hash.get(), "abc");

        });

        it("returns false and keeps the previous value for a falsy hash", () => {

            // Set instance
            const hash = new Hash();

            // Set hash
            hash.set("abc");

            // Set falsy hash
            const result = hash.set("");

            // Check result
            assert.equal(result, false);

            // Check the previous value is unchanged
            assert.equal(hash.get(), "abc");

        });

    });

    describe("get", () => {

        it("returns null before any hash has been set", () => {

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(hash.get(), null);

        });

    });

    /** getAllFromHistory
     ******************************************************
     */
    describe("getAllFromHistory", () => {

        it("returns an empty array when only one hash was ever set", () => {

            // Set instance
            const hash = new Hash();

            // Set hash
            hash.set("abc");

            // Check result (only the current value has been set, nothing pushed to history yet)
            assert.deepEqual(hash.getAllFromHistory(), []);

        });

        it("returns previous values once the hash has been replaced", () => {

            // Set instance
            const hash = new Hash();

            // Set first hash
            hash.set("abc");

            // Replace with a new hash (pushes "abc" into history)
            hash.set("def");

            // Check result
            assert.deepEqual(hash.getAllFromHistory(), ["abc"]);

        });

    });

    /** isWatch
     ******************************************************
     */
    describe("isWatch", () => {

        it("returns false when no application-watch meta tag is present", () => {

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(hash.isWatch(), false);

        });

        it("returns true when the application-watch meta tag content is 'true'", () => {

            // Set meta tag
            const meta = document.createElement("meta");
            meta.setAttribute("name", "application-watch");
            meta.setAttribute("content", "true");
            document.head.appendChild(meta);

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(hash.isWatch(), true);

            // Clean meta tag
            document.head.removeChild(meta);

        });

        it("returns false when the application-watch meta tag content isn't 'true'", () => {

            // Set meta tag
            const meta = document.createElement("meta");
            meta.setAttribute("name", "application-watch");
            meta.setAttribute("content", "false");
            document.head.appendChild(meta);

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(hash.isWatch(), false);

            // Clean meta tag
            document.head.removeChild(meta);

        });

    });

    /** setFromMetaTag
     ******************************************************
     */
    describe("setFromMetaTag", () => {

        it("sets the hash from the default 'application-hash' meta tag", () => {

            // Set meta tag
            const meta = document.createElement("meta");
            meta.setAttribute("name", "application-hash");
            meta.setAttribute("content", "4fe1efd8");
            document.head.appendChild(meta);

            // Set instance
            const hash = new Hash();

            // Set from meta tag
            const result = hash.setFromMetaTag();

            // Check result
            assert.equal(result, true);

            // Check stored value
            assert.equal(hash.get(), "4fe1efd8");

            // Clean meta tag
            document.head.removeChild(meta);

        });

        it("supports a custom tag name", () => {

            // Set meta tag
            const meta = document.createElement("meta");
            meta.setAttribute("name", "custom-hash");
            meta.setAttribute("content", "custom123");
            document.head.appendChild(meta);

            // Set instance
            const hash = new Hash();

            // Set from meta tag
            const result = hash.setFromMetaTag("custom-hash");

            // Check result
            assert.equal(result, true);

            // Check stored value
            assert.equal(hash.get(), "custom123");

            // Clean meta tag
            document.head.removeChild(meta);

        });

        it("returns false when the meta tag doesn't exist", () => {

            // Set instance
            const hash = new Hash();

            // Set from meta tag
            const result = hash.setFromMetaTag("does-not-exist");

            // Check result
            assert.equal(result, false);

            // Check hash stays unset
            assert.equal(hash.get(), null);

        });

    });

    /** setFromRequest
     ******************************************************
     *
     * `setFromRequest` fetches a real crazy app endpoint through
     * `Crazyrequest`, which needs a live server/network to resolve. We only
     * exercise the synchronous "falsy url" guard clause here, which is
     * safe to run without a network.
     */
    describe("setFromRequest", () => {

        it("returns null without performing any request when the url is falsy", async () => {

            // Set instance
            const hash = new Hash();

            // Check result
            assert.equal(await hash.setFromRequest(""), null);

        });

    });

});
