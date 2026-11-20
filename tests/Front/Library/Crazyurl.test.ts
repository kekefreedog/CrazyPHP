/**
 * Crazy URL Query Tests
 *
 * Preserve browser filter contracts while keeping URL-controlled keys out of shared prototypes.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
import {afterEach, beforeEach, describe, it} from "node:test";
import Crazyurl from "../../../src/Front/Library/Crazyurl";
import assert from "node:assert/strict";

// Remember the existing browser global without invoking an accessor.
const originalWindow = Object.getOwnPropertyDescriptor(globalThis, "window");

// Give each case a native URL without loading an application or browser service.
beforeEach(() => {

    // Install only the location contract used by query parsing.
    Object.defineProperty(globalThis, "window", {
        value: {location: new URL("https://toolkit.test/en/")},
        configurable: true,
        writable: true,
    });

});

// Restore the exact previous environment after each case.
afterEach(() => {

    // Restore an existing descriptor without changing its flags.
    if(originalWindow){

        // Reinstate the caller's original browser global.
        Object.defineProperty(globalThis, "window", originalWindow);

    }else{

        // Remove only the test-created browser global.
        Reflect.deleteProperty(globalThis, "window");

    }

});

/**
 * Parse Fixture Query
 *
 * @param query URL-encoded fixture parameters
 * @param names Optional requested paths
 * @returns Actual production parser output
 */
function parse(query:string, names?:string|string[]):Record<string, any> {

    // Set only this isolated browser's query string.
    window.location.search = query;

    // Execute the real query parser.
    const result = Crazyurl.getQueryParameters(names);

    // Return result.
    return result;

}

// Verify the same parser used by page loading, Home searches and filter restoration.
describe("Crazyurl query parsing", () => {

    // Preserve ordinary text and typed JSON values exactly.
    it("preserves decoded text, JSON values, empty flags and duplicate-key precedence", () => {

        // Supply values as a real browser would encode them.
        const query = new URLSearchParams({search: "Été & A+B", zero: "0", enabled: "false", empty: "", nothing: "null", quoted: '"123"', literal: "__proto__"});

        // Keep the current last-value-wins behavior.
        query.append("search", "Final search");

        // Check all scalar types without coercion.
        assert.deepEqual(parse(query.toString()), {
            search: "Final search", zero: 0, enabled: false, empty: "", nothing: null, quoted: "123", literal: "__proto__",
        });

        // Preserve actual percent-decoding and plus-sign semantics.
        assert.deepEqual(parse("search=%C3%89t%C3%A9+%26+A%2BB"), {search: "Été & A+B"});

    });

    // Preserve both supported path syntaxes for current filter controls.
    it("preserves bracket and dot paths with dense numeric filters", () => {

        // Supply the same URL shape used by report filter forms.
        const query = new URLSearchParams({
            "filters[planning_preset][studio][1]": "=paris",
            "filters[planning_preset][studio][0]": "=",
            "filters.planning_preset.mikros": "true",
            "filters[planning_preset][count]": "0",
        });

        // Keep numeric key ordering and boolean/zero values.
        assert.deepEqual(parse(query.toString()), {
            filters: {planning_preset: {studio: ["=", "=paris"], mikros: true, count: 0}},
        });

    });

    // Preserve sparse indexing and the existing empty-bracket interpretation.
    it("keeps sparse numeric objects and repeated empty brackets unchanged", () => {

        // Exercise object keys that do not form a dense zero-based sequence.
        assert.deepEqual(parse("items[2]=third&items[01]=leading&tag[]=first&tag[]=last"), {
            items: {2: "third", "01": "leading"}, tag: "last",
        });

        // Keep the existing dense-root conversion as well.
        assert.deepEqual(parse("0=first&1=second"), ["first", "second"]);

    });

    // Keep JSON arrays explicit while retaining the established numeric-object conversion.
    it("preserves JSON arrays and numeric objects nested inside them", () => {

        // Prepare explicit JSON structures separately from bracket indexing.
        const query = new URLSearchParams({
            array: '[{"0":"keep object"},null,false,0,[]]',
            object: '{"0":"convert","1":"object"}',
            emptyArray: '[]',
            emptyObject: '{}',
        });

        // Preserve every existing structure decision.
        assert.deepEqual(parse(query.toString()), {
            array: [{0: "keep object"}, null, false, 0, []],
            object: ["convert", "object"], emptyArray: [], emptyObject: {},
        });

    });

    // Retain the selected-name result shape used by Home and Form.
    it("selects only requested own values without losing false, zero or null", () => {

        // Request both bracket and dot names without rebuilding their nesting in the response.
        assert.deepEqual(parse("search=show&filters[group][enabled]=false&zero=0&nothing=null", [
            "search", "filters.group.enabled", "filters[group][enabled]", "zero", "nothing", "missing", "toString", "__proto__",
        ]), {
            search: "show", "filters.group.enabled": false, "filters[group][enabled]": false, zero: 0, nothing: null,
        });

        // Keep an empty requested-name list equivalent to an unrestricted read.
        assert.deepEqual(parse("search=show", []), {search: "show"});

        // Keep a single selected root in its named envelope.
        assert.deepEqual(parse("filters[group][enabled]=false", "filters"), {filters: {group: {enabled: false}}});

    });

    // Verify every attacker path against fresh shared prototype descriptors.
    it("ignores prototype-control paths before parsing selected parameters", () => {

        // Record the shared object model independently of the returned result.
        const before = [Object, Array, Function].map(type => Object.getOwnPropertyDescriptors(type.prototype));

        // Cover both decoded path syntaxes and prototype-looking roots.
        for(const key of [
            "__proto__[queryPollutionMarker]", "__proto__.queryPollutionMarker", "[__proto__][queryPollutionMarker]",
            "constructor[prototype][queryPollutionMarker]", "filters[__proto__][queryPollutionMarker]",
            "filters.constructor.prototype.queryPollutionMarker", "prototype[queryPollutionMarker]", "__proto__",
        ]){

            // Place the hostile parameter alongside legitimate page state.
            const query = new URLSearchParams({search: "demo", [key]: '{"queryPollutionMarker":1}'});

            // Preserve legitimate selected fields without returning unsafe branches.
            assert.deepEqual(parse(query.toString(), "search"), {search: "demo"});

            // Keep unrestricted parser callers safe as well.
            assert.deepEqual(parse(query.toString()), {search: "demo"});

            // Detect prototype changes even when filtered output looks correct.
            assert.deepEqual([Object, Array, Function].map(type => Object.getOwnPropertyDescriptors(type.prototype)), before);

        }

        // Decode encoded key components exactly once through URLSearchParams.
        assert.deepEqual(parse("%5f%5fproto%5f%5f%5bqueryPollutionMarker%5d=1&search=demo"), {search: "demo"});

    });

    // Protect later state/form merges from unsafe keys carried inside JSON values.
    it("removes unsafe JSON keys recursively while preserving safe siblings", () => {

        // Include objects inside both arrays and nested JSON objects.
        const payload = '{"__proto__":{"queryPollutionMarker":1},"constructor":{"prototype":{"queryPollutionMarker":2}},"safe":{"prototype":3,"value":0},"rows":[{"__proto__":4,"enabled":false},{"constructor":5,"0":"literal"}]}';

        // Parse the actual JSON-valued parameter.
        assert.deepEqual(parse(new URLSearchParams({filters: payload}).toString()), {
            filters: {safe: {value: 0}, rows: [{enabled: false}, {0: "literal"}]},
        });

    });

    // Keep prefix collisions deterministic rather than throwing from null or array setters.
    it("handles scalar, null and JSON-array prefixes without touching shared objects", () => {

        // Expand a null prefix into an ordinary filter object.
        assert.deepEqual(parse("filters=null&filters[studio]=paris"), {filters: {studio: "paris"}});

        // Retain a JSON array extended by a bracket-indexed parameter.
        assert.deepEqual(parse('items=%5B%22first%22%5D&items[1]=second'), {items: ["first", "second"]});

        // Treat length as a data key instead of invoking an array's special setter.
        assert.deepEqual(parse('items=%5B%22first%22%5D&items[length]=-1'), {items: {0: "first", length: -1}});

        // Prevent inherited-array prototype traversal after a JSON-valued prefix.
        assert.deepEqual(parse('items=%5B%22first%22%5D&items[__proto__][queryPollutionMarker]=1'), {items: ["first"]});

    });

    // Reject inherited data even if another component previously polluted a shared prototype.
    it("never imports inherited entries while normalizing or selecting query data", () => {

        // Add a uniquely named temporary enumerable property for this test only.
        Object.defineProperty(Object.prototype, "crazyQueryInheritedFixture", {value: "inherited", configurable: true, enumerable: true});

        // Remove the fixture even if parsing fails.
        try{

            // Keep the entire result restricted to own query data.
            assert.deepEqual(parse("search=demo"), {search: "demo"});

            // Do not expose inherited properties through selected names.
            assert.deepEqual(parse("search=demo", "crazyQueryInheritedFixture"), {});

        }finally{

            // Restore the shared prototype exactly for subsequent tests.
            Reflect.deleteProperty(Object.prototype, "crazyQueryInheritedFixture");

        }

    });

    // Preserve harmless own names when page state is serialized for a full-page refresh.
    it("serializes a literal hasOwnProperty field without invoking it", () => {

        // Keep harmless field names available as ordinary own data.
        const parsed = parse("hasOwnProperty=literal&filters[hasOwnProperty]=nested&filters[studio]=paris");

        // Execute the serializer used by Crazypage.getPageState.
        const serialized = Crazyurl.toQueryString(parsed);

        // Inspect the actual encoded request parameters independently of that serializer.
        assert.deepEqual(Object.fromEntries(new URLSearchParams(serialized)), {
            hasOwnProperty: "literal", "filters[hasOwnProperty]": "nested", "filters[studio]": "paris",
        });

    });

});
