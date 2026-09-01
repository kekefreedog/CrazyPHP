/**
 * Cookie Test
 *
 * Tests for Front TS Cookie utility methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { describe, it, before, after, beforeEach } from "node:test";
import assert from "node:assert/strict";
import { JSDOM } from "jsdom";
import Cookie from "../../../../src/Front/Library/Utility/Cookie";

/**
 * Cookie
 *
 * Methods for test cookie methods
 *
 * `Cookie` reads/writes the browser `document.cookie` global, which node:test
 * does not provide. We install a JSDOM window for the duration of this
 * describe block, and tear it down right after.
 */
describe("Front/Library/Utility/Cookie", () => {

    // Set original document (undefined outside a browser/jsdom)
    let originalDocument: typeof document | undefined;

    before(() => {

        // Set dom (a real url is required for document.cookie to work)
        const dom = new JSDOM("", { url: "http://localhost/" });

        // Save original
        originalDocument = (globalThis as any).document;

        // Install jsdom's document
        (globalThis as any).document = dom.window.document;

    });

    after(() => {

        // Restore original
        (globalThis as any).document = originalDocument;

    });

    beforeEach(() => {

        // Clear every cookie between tests so they don't interfere
        Cookie.clear();

    });

    /** get
     ******************************************************
     */
    describe("get", () => {

        it("returns the value of an existing cookie", () => {

            // Set cookie
            Cookie.set("foo", "bar");

            // Check result
            assert.equal(Cookie.get("foo"), "bar");

        });

        it("returns null for a cookie that does not exist", () => {

            // Check result
            assert.equal(Cookie.get("doesNotExist"), null);

        });

        it("decodes uri encoded values", () => {

            // Set cookie
            Cookie.set("greeting", "hello world & co");

            // Check result
            assert.equal(Cookie.get("greeting"), "hello world & co");

        });

    });

    /** getAll
     ******************************************************
     */
    describe("getAll", () => {

        it("returns an empty object when there is no cookie", () => {

            // Check result
            assert.deepEqual(Cookie.getAll(), {});

        });

        it("returns every cookie as an object", () => {

            // Set cookies
            Cookie.set("foo", "bar");
            Cookie.set("baz", "qux");

            // Check result
            assert.deepEqual(Cookie.getAll(), { foo: "bar", baz: "qux" });

        });

    });

    /** set
     ******************************************************
     */
    describe("set", () => {

        it("sets a session cookie readable through document.cookie", () => {

            // Set cookie
            Cookie.set("session", "abc");

            // Check result
            assert.equal(Cookie.get("session"), "abc");

        });

        it("accepts an expiration in days without throwing", () => {

            // Set cookie
            Cookie.set("withDays", "value", { days: 7 });

            // Check result
            assert.equal(Cookie.get("withDays"), "value");

        });

        it("accepts secure and sameSite options without throwing", () => {

            // Set cookie
            Cookie.set("secured", "value", { secure: true, sameSite: "Strict" });

            // Check result
            assert.equal(Cookie.get("secured"), "value");

        });

    });

    /** clear
     ******************************************************
     */
    describe("clear", () => {

        it("clears a specific cookie by name", () => {

            // Set cookies
            Cookie.set("toRemove", "value");
            Cookie.set("toKeep", "value");

            // Clear one cookie
            Cookie.clear("toRemove");

            // Check result
            assert.equal(Cookie.get("toRemove"), null);
            assert.equal(Cookie.get("toKeep"), "value");

        });

        it("clears every cookie when no name is given", () => {

            // Set cookies
            Cookie.set("first", "value");
            Cookie.set("second", "value");

            // Clear all cookies
            Cookie.clear();

            // Check result
            assert.deepEqual(Cookie.getAll(), {});

        });

    });

});
