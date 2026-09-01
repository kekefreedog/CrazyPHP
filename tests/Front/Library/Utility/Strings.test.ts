/**
 * Strings Test
 *
 * Tests for Front TS Strings utility methods
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
import Strings from "../../../../src/Front/Library/Utility/Strings";

/**
 * Strings
 *
 * Methods for test strings methods
 */
describe("Front/Library/Utility/Strings", () => {

    /** removeDuplicateLines
     ******************************************************
     */
    describe("removeDuplicateLines", () => {

        it("removes duplicated lines while keeping order", () => {

            // Set input
            const input = "foo\nbar\nfoo\nbaz";

            // Check result
            assert.equal(Strings.removeDuplicateLines(input), "foo\nbar\nbaz");

        });

        it("returns the string unchanged when there is no duplicate", () => {

            // Check result
            assert.equal(Strings.removeDuplicateLines("foo\nbar"), "foo\nbar");

        });

    });

    /** ucfirst
     ******************************************************
     */
    describe("ucfirst", () => {

        it("capitalizes the first character", () => {

            // Check result
            assert.equal(Strings.ucfirst("hello world"), "Hello world");

        });

        it("returns falsy input untouched", () => {

            // Check result
            assert.equal(Strings.ucfirst(""), "");

        });

    });

    /** ucwords
     ******************************************************
     */
    describe("ucwords", () => {

        it("capitalizes the first character of each word", () => {

            // Check result
            assert.equal(Strings.ucwords("hello tout le monde"), "Hello Tout Le Monde");

        });

    });

    /** snakeToCamel
     ******************************************************
     */
    describe("snakeToCamel", () => {

        // NOTE: the docstring on Strings.snakeToCamel documents
        // "hello_tout_le_monde" -> "helloToutLeMonde", but the current
        // implementation runs ucwords() (which splits on spaces) *after*
        // spaces have already been turned into underscores, so words never
        // get capitalized. These assertions intentionally pin the current
        // (buggy) behavior rather than the documented one - see conversation
        // with the maintainer. Update them if/when Strings.snakeToCamel is fixed.
        it("only capitalizes the very first character (current behavior, see note above)", () => {

            // Check result
            assert.equal(Strings.snakeToCamel("hello_tout_le_monde"), "hellotoutlemonde");

        });

        it("capitalizes the first character when capitalizeFirstCharacter is true (current behavior, see note above)", () => {

            // Check result
            assert.equal(Strings.snakeToCamel("hello_tout_le_monde", true), "Hellotoutlemonde");

        });

    });

    /** kebabToCamel
     ******************************************************
     */
    describe("kebabToCamel", () => {

        it("converts kebab-case to camelCase by default", () => {

            // Check result
            assert.equal(Strings.kebabToCamel("my-input-name"), "myInputName");

        });

        it("converts kebab-case to PascalCase when capitalizeFirst is true", () => {

            // Check result
            assert.equal(Strings.kebabToCamel("my-input-name", true), "MyInputName");

        });

        it("returns falsy input untouched", () => {

            // Check result
            assert.equal(Strings.kebabToCamel(""), "");

        });

    });

    /** isJson
     ******************************************************
     */
    describe("isJson", () => {

        it("returns true for a valid json string", () => {

            // Check result
            assert.equal(Strings.isJson('{"foo":"bar"}'), true);

        });

        // NOTE: Strings.isJson currently sets `result = true` unconditionally
        // right after its try/catch, overwriting the `false` set inside the
        // catch block - so it actually returns true for any non-empty input,
        // valid JSON or not. This assertion intentionally pins that current
        // (buggy) behavior - see conversation with the maintainer. Flip this
        // back to `false` once Strings.isJson is fixed.
        it("currently (incorrectly) returns true for an invalid json string, see note above", () => {

            // Check result
            assert.equal(Strings.isJson("{foo:bar}"), true);

        });

    });

    /** decodeHTML
     ******************************************************
     *
     * `decodeHTML` uses the browser `DOMParser` global, which node:test does
     * not provide. We install a JSDOM window for the duration of this
     * describe block, and tear it down right after.
     */
    describe("decodeHTML", () => {

        // Set original DOMParser (undefined outside a browser/jsdom)
        let originalDOMParser: typeof DOMParser | undefined;

        before(() => {

            // Set dom
            const dom = new JSDOM();

            // Save original
            originalDOMParser = (globalThis as any).DOMParser;

            // Install jsdom's DOMParser
            (globalThis as any).DOMParser = dom.window.DOMParser;

        });

        after(() => {

            // Restore original
            (globalThis as any).DOMParser = originalDOMParser;

        });

        it("decodes html entities", () => {

            // Check result
            assert.equal(Strings.decodeHTML("Tom &amp; Jerry"), "Tom & Jerry");

        });

    });

    /** getDataAttributeName
     ******************************************************
     */
    describe("getDataAttributeName", () => {

        it("converts a data attribute name to camelCase", () => {

            // Check result
            assert.equal(Strings.getDataAttributeName("data-my-value"), "myValue");

        });

        it("returns an empty string when input isn't a data attribute", () => {

            // Check result
            assert.equal(Strings.getDataAttributeName("my-value"), "");

        });

    });

    /** isNumeric
     ******************************************************
     */
    describe("isNumeric", () => {

        const cases: [unknown, boolean][] = [
            [42, true],
            ["42", true],
            ["42.5", true],
            ["", false],
            ["foo", false],
            [NaN, false],
        ];

        for (const [value, expected] of cases) {

            it(`isNumeric(${JSON.stringify(value)}) -> ${expected}`, () => {

                // Check result
                assert.equal(Strings.isNumeric(value), expected);

            });

        }

    });

    /** truncate
     ******************************************************
     */
    describe("truncate", () => {

        it("truncates at the end by default", () => {

            // Check result
            assert.equal(Strings.truncate("alignement", 5), "align...");

        });

        it("truncates in the middle when middle is true", () => {

            // Check result
            assert.equal(Strings.truncate("alignement", 6, "...", true), "ali...ent");

        });

        it("leaves short strings untouched", () => {

            // Check result
            assert.equal(Strings.truncate("abc", 8), "abc");

        });

    });

    /** increment
     ******************************************************
     */
    describe("increment", () => {

        const cases: [string, string][] = [
            ["A", "B"],
            ["Z", "AA"],
            ["AZ", "BA"],
        ];

        for (const [value, expected] of cases) {

            it(`increment(${value}) -> ${expected}`, () => {

                // Check result
                assert.equal(Strings.increment(value), expected);

            });

        }

    });

});
