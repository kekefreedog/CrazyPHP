/**
 * ColorSchema Test
 *
 * Tests for Front TS ColorSchema utility methods
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
import ColorSchema from "../../../../src/Front/Library/Utility/ColorSchema";

/**
 * ColorSchema
 *
 * Methods for test color schema methods
 *
 * `ColorSchema` reads/writes `window`, `document` and `localStorage`, which
 * node:test does not provide. We install a JSDOM window for the duration of
 * the whole suite, and tear it down right after. `window.matchMedia` isn't
 * implemented by jsdom, so it is replaced by a controllable stub.
 */
describe("Front/Library/Utility/ColorSchema", () => {

    // Set originals (undefined outside a browser/jsdom)
    let originalWindow: any;
    let originalDocument: any;
    let originalLocalStorage: any;

    // Set controllable "prefers-color-scheme: dark" state used by the matchMedia stub
    let matchMediaMatches = false;

    before(() => {

        // Set dom (a real url is required for localStorage to be available)
        const dom = new JSDOM("", { url: "http://localhost/" });

        // Save originals
        originalWindow = (globalThis as any).window;
        originalDocument = (globalThis as any).document;
        originalLocalStorage = (globalThis as any).localStorage;

        // Install a controllable matchMedia stub (jsdom doesn't implement it)
        (dom.window as any).matchMedia = (query: string) => ({
            matches: matchMediaMatches,
            media: query,
            addEventListener: () => {},
            removeEventListener: () => {},
        });

        // Install jsdom's globals
        (globalThis as any).window = dom.window;
        (globalThis as any).document = dom.window.document;
        (globalThis as any).localStorage = dom.window.localStorage;

    });

    after(() => {

        // Restore originals
        (globalThis as any).window = originalWindow;
        (globalThis as any).document = originalDocument;
        (globalThis as any).localStorage = originalLocalStorage;

    });

    beforeEach(() => {

        // Reset matchMedia state
        matchMediaMatches = false;

        // Reset forced theme
        delete (window as any).Crazyobject;

        // Reset local storage
        localStorage.clear();

        // Reset theme attribute
        document.documentElement.removeAttribute("theme");

    });

    /** get
     ******************************************************
     */
    describe("get", () => {

        it("returns 'light' when the browser does not prefer dark", () => {

            // Set preference
            matchMediaMatches = false;

            // Check result
            assert.equal(ColorSchema.get(), "light");

        });

        it("returns 'dark' when the browser prefers dark", () => {

            // Set preference
            matchMediaMatches = true;

            // Check result
            assert.equal(ColorSchema.get(), "dark");

        });

        it("returns the forced theme when window.Crazyobject.forcedTheme is set", () => {

            // Force a theme that disagrees with the matchMedia preference
            (window as any).Crazyobject = { forcedTheme: "light" };
            matchMediaMatches = true;

            // Check result
            assert.equal(ColorSchema.get(), "light");

        });

    });

    /** setTheme
     ******************************************************
     */
    describe("setTheme", () => {

        it("sets the theme attribute on the document element", () => {

            // Set theme
            ColorSchema.setTheme("dark");

            // Check result
            assert.equal(document.documentElement.getAttribute("theme"), "dark");

        });

        it("does not touch local storage by default", () => {

            // Set theme
            ColorSchema.setTheme("light");

            // Check result
            assert.equal(localStorage.getItem("crazy-theme"), null);

        });

        it("stores the theme in local storage when useLocalStorage is true", () => {

            // Set theme
            ColorSchema.setTheme("dark", true);

            // Check result
            assert.equal(localStorage.getItem("crazy-theme"), "dark");

        });

    });

    /** getTheme
     ******************************************************
     */
    describe("getTheme", () => {

        it("returns ColorSchema.get() when useLocalStorage is false", () => {

            // Set preference
            matchMediaMatches = true;

            // Check result
            assert.equal(ColorSchema.getTheme(), "dark");

        });

        it("uses the stored local storage value when useLocalStorage is true", () => {

            // Store a value that disagrees with the current matchMedia preference
            localStorage.setItem("crazy-theme", "dark");
            matchMediaMatches = false;

            // Check result: honors the stored "dark", not ColorSchema.get() ("light")
            assert.equal(ColorSchema.getTheme(true), "dark");

        });

    });

    /** rgbToHex
     ******************************************************
     */
    describe("rgbToHex", () => {

        it("converts numeric rgb components to a hex color", () => {

            // Check result
            assert.equal(ColorSchema.rgbToHex(255, 0, 128), "#ff0080");

        });

        it("pads single-digit hex components with a leading zero", () => {

            // Check result
            assert.equal(ColorSchema.rgbToHex(0, 0, 0), "#000000");

        });

        it("converts string rgb components to a hex color", () => {

            // Check result
            assert.equal(ColorSchema.rgbToHex("255", "0", "128"), "#ff0080");

        });

    });

    /** createVerticalGradient
     ******************************************************
     */
    describe("createVerticalGradient", () => {

        it("builds a smooth gradient by default", () => {

            // Set colors
            const colors = ["255,0,0", "0,255,0", "0,0,255"];

            // Check result
            assert.equal(
                ColorSchema.createVerticalGradient(colors),
                "background: linear-gradient(to bottom, rgba(255,0,0) 0%, rgba(0,255,0) 50%, rgba(0,0,255) 100%);"
            );

        });

        it("builds a hard stop gradient when smooth is false", () => {

            // Set colors
            const colors = ["255,0,0", "0,0,255"];

            // Check result
            assert.equal(
                ColorSchema.createVerticalGradient(colors, false),
                "background: linear-gradient(to bottom, rgba(255,0,0) 0%, rgba(255,0,0) 50%, rgba(0,0,255) 50%, rgba(0,0,255) 100%);"
            );

        });

        it("builds a plain color background for a single color list", () => {

            // Check result
            assert.equal(
                ColorSchema.createVerticalGradient(["1,2,3"]),
                "background: rgba(1,2,3);"
            );

        });

    });

    /** rgbSoften
     ******************************************************
     */
    describe("rgbSoften", () => {

        it("softens a gray color toward white using the default amount", () => {

            // Check result
            assert.equal(ColorSchema.rgbSoften(100, 100, 100), "178, 178, 178");

        });

        it("softens a saturated color toward white using the default amount", () => {

            // Check result
            assert.equal(ColorSchema.rgbSoften(255, 0, 0), "255, 128, 128");

        });

        it("accepts string components and a custom amount/saturation", () => {

            // Check result
            assert.equal(ColorSchema.rgbSoften("10", "20", "30", 0.2, 0.5), "63, 67, 71");

        });

    });

    /** constructor
     ******************************************************
     */
    describe("constructor", () => {

        it("applies the stored/current theme to the document element without throwing", () => {

            // Set preference
            matchMediaMatches = true;

            // Instantiate
            new ColorSchema();

            // Check result
            assert.equal(document.documentElement.getAttribute("theme"), "dark");

        });

    });

});
