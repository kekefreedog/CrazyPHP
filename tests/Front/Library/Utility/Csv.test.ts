/**
 * Csv Test
 *
 * Tests for Front TS Csv utility methods
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
import { JSDOM, VirtualConsole } from "jsdom";
import Csv from "../../../../src/Front/Library/Utility/Csv";

/**
 * Csv
 *
 * Methods for test csv methods
 */
describe("Front/Library/Utility/Csv", () => {

    /** render
     ******************************************************
     */
    describe("render", () => {

        it("renders a simple array of objects as a csv string", () => {

            // Set input
            const input = [
                { name: "Foo", age: 20 },
                { name: "Bar", age: 30 },
            ];

            // Check result
            assert.equal(Csv.render(input), "name,age\nFoo,20\nBar,30");

        });

        it("encloses fields containing a comma in quotes", () => {

            // Set input
            const input = [{ name: "Doe, John", age: 40 }];

            // Check result
            assert.equal(Csv.render(input), 'name,age\n"Doe, John",40');

        });

        it("encloses fields containing a line break in quotes", () => {

            // Set input
            const input = [{ note: "line1\nline2" }];

            // Check result
            assert.equal(Csv.render(input), 'note\n"line1\nline2"');

        });

        it("encloses fields containing quotes and doubles the existing quotes", () => {

            // Set input
            const input = [{ note: 'he said "hi"' }];

            // Check result
            assert.equal(Csv.render(input), 'note\n"he said ""hi"""');

        });

        it("leaves numeric fields unescaped", () => {

            // Set input
            const input = [{ count: 12 }];

            // Check result
            assert.equal(Csv.render(input), "count\n12");

        });

        it("returns an empty string for an empty array", () => {

            // Check result
            assert.equal(Csv.render([]), "");

        });

    });

    /** download
     ******************************************************
     *
     * `download` relies on browser globals (`Blob`, `document`, `URL`) that
     * node:test does not provide. We install a JSDOM window for the duration
     * of this describe block, stub `URL.createObjectURL`/`revokeObjectURL`
     * (jsdom does not implement them), and tear everything down right after.
     */
    describe("download", () => {

        // Set originals (undefined outside a browser/jsdom)
        let originalWindow: any;
        let originalDocument: any;
        let originalBlob: any;
        let originalURL: any;

        before(() => {

            // Set a silent virtual console (avoids noisy "Not implemented:
            // navigation" jsdom warnings when the link is clicked)
            const virtualConsole = new VirtualConsole();

            // Set dom
            const dom = new JSDOM("", { url: "http://localhost", virtualConsole });

            // Save originals
            originalWindow = (globalThis as any).window;
            originalDocument = (globalThis as any).document;
            originalBlob = (globalThis as any).Blob;
            originalURL = (globalThis as any).URL;

            // Install jsdom's globals
            (globalThis as any).window = dom.window;
            (globalThis as any).document = dom.window.document;
            (globalThis as any).Blob = dom.window.Blob;
            (globalThis as any).URL = dom.window.URL;

            // Stub methods jsdom does not implement
            (globalThis as any).URL.createObjectURL = () => "blob:mock-url";
            (globalThis as any).URL.revokeObjectURL = () => {};

        });

        after(() => {

            // Restore originals
            (globalThis as any).window = originalWindow;
            (globalThis as any).document = originalDocument;
            (globalThis as any).Blob = originalBlob;
            (globalThis as any).URL = originalURL;

        });

        it("creates, clicks and removes a download link with the rendered csv content", () => {

            // Set input
            const input = [{ name: "Foo", age: 20 }];

            // Set captured attributes
            let captured: { href: string | null; download: string | null } | undefined;

            // Set original appendChild
            const originalAppendChild = document.body.appendChild.bind(document.body);

            // Spy on appendChild to capture the link before it gets removed
            (document.body as any).appendChild = (node: any) => {

                // Capture attributes
                captured = {
                    href: node.getAttribute("href"),
                    download: node.getAttribute("download"),
                };

                // Call original
                return originalAppendChild(node);

            };

            // Call method
            Csv.download(input, "export.csv");

            // Restore appendChild
            document.body.appendChild = originalAppendChild;

            // Check captured attributes
            assert.equal(captured?.href, "blob:mock-url");
            assert.equal(captured?.download, "export.csv");

            // Check link was removed from the body afterwards
            assert.equal(document.body.childElementCount, 0);

        });

    });

});
