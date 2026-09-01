/**
 * Arrays Test
 *
 * Tests for Front TS Arrays utility methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { describe, it } from "node:test";
import assert from "node:assert/strict";
import Arrays from "../../../../src/Front/Library/Utility/Arrays";

/**
 * Arrays
 *
 * Methods for test arrays methods
 */
describe("Front/Library/Utility/Arrays", () => {

    /** filterByKey
     ******************************************************
     */
    describe("filterByKey", () => {

        it("filters objects by a key/value pair", () => {

            // Set input
            const input = [{ id: 1, name: "a" }, { id: 2, name: "b" }];

            // Check result
            assert.deepEqual(Arrays.filterByKey(input, "id", 2), [{ id: 2, name: "b" }]);

        });

        it("uses loose equality, so a numeric key value also matches a string value", () => {

            // Set input
            const input = [{ id: 1 }, { id: "1" }, { id: 2 }];

            // Check result
            assert.deepEqual(Arrays.filterByKey(input, "id", "1"), [{ id: 1 }, { id: "1" }]);

        });

        it("returns an empty array when no entry matches", () => {

            // Check result
            assert.deepEqual(Arrays.filterByKey([{ id: 1 }], "id", 99), []);

        });

        it("defaults to an empty array when no array is given", () => {

            // Check result
            assert.deepEqual(Arrays.filterByKey(undefined, "id", 1), []);

        });

    });

    /** filterByKeyMD
     ******************************************************
     */
    describe("filterByKeyMD", () => {

        it("filters objects by a nested dot-notation key", () => {

            // Set input
            const input = [{ a: { b: 1 } }, { a: { b: 2 } }];

            // Check result
            assert.deepEqual(Arrays.filterByKeyMD(input, "a.b", 1), [{ a: { b: 1 } }]);

        });

        it("returns an empty array when the nested key doesn't exist", () => {

            // Check result
            assert.deepEqual(Arrays.filterByKeyMD([{ a: {} }], "a.b", 1), []);

        });

    });

    /** removeObjectsByKeyValue
     ******************************************************
     */
    describe("removeObjectsByKeyValue", () => {

        it("removes objects matching a simple key value", () => {

            // Set input
            const input = [{ id: 1 }, { id: 2 }, { id: 1 }];

            // Check result
            assert.deepEqual(Arrays.removeObjectsByKeyValue(input, "id", 1), [{ id: 2 }]);

        });

        it("removes objects matching a nested dot-notation key value", () => {

            // Set input
            const input = [{ a: { b: 1 } }, { a: { b: 2 } }];

            // Check result
            assert.deepEqual(Arrays.removeObjectsByKeyValue(input, "a.b", 1), [{ a: { b: 2 } }]);

        });

        it("keeps every entry when nothing matches", () => {

            // Set input
            const input = [{ id: 1 }, { id: 2 }];

            // Check result
            assert.deepEqual(Arrays.removeObjectsByKeyValue(input, "id", 99), input);

        });

    });

    /** removeByKey
     ******************************************************
     */
    describe("removeByKey", () => {

        it("removes objects matching the given key/value", () => {

            // Set input
            const input = [{ a: 1 }, { a: 2 }, { a: 1 }];

            // Check result
            assert.deepEqual(Arrays.removeByKey(input, "a", 1), [{ a: 2 }]);

        });

        it("returns the array untouched when nothing matches", () => {

            // Set input
            const input = [{ a: 1 }];

            // Check result
            assert.deepEqual(Arrays.removeByKey(input, "a", 99), input);

        });

    });

    /** flatten
     ******************************************************
     */
    describe("flatten", () => {

        it("flattens a nested object using the default separator", () => {

            // Set input
            const input = { a: { b: 1, c: 2 }, d: 3 };

            // Check result
            assert.deepEqual(Arrays.flatten(input), { "a.b": 1, "a.c": 2, "d": 3 });

        });

        it("flattens using a custom separator", () => {

            // Set input
            const input = { a: { b: 1 } };

            // Check result
            assert.deepEqual(Arrays.flatten(input, "/"), { "a/b": 1 });

        });

        it("keeps arrays as values instead of recursing into them", () => {

            // Set input
            const input = { a: [1, 2], b: { c: [3, 4] } };

            // Check result
            assert.deepEqual(Arrays.flatten(input), { "a": [1, 2], "b.c": [3, 4] });

        });

        it("keeps null values as-is instead of recursing", () => {

            // Check result
            assert.deepEqual(Arrays.flatten({ a: null }), { a: null });

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Arrays.flatten({}), {});

        });

    });

    /** unflatten
     ******************************************************
     */
    describe("unflatten", () => {

        it("unflattens a dot-notation object using the default separator", () => {

            // Check result
            assert.deepEqual(Arrays.unflatten({ "a.b": 1, "a.c": 2, "d": 3 }), { a: { b: 1, c: 2 }, d: 3 });

        });

        it("unflattens using a custom separator", () => {

            // Check result
            assert.deepEqual(Arrays.unflatten({ "a/b": 1 }, "/"), { a: { b: 1 } });

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Arrays.unflatten({}), {});

        });

        it("round-trips with flatten", () => {

            // Set input
            const input = { a: { b: 1, c: { d: 2 } }, e: 3 };

            // Set flattened
            const flattened = Arrays.flatten(input);

            // Check result
            assert.deepEqual(Arrays.unflatten(flattened), input);

        });

    });

    /** equal
     ******************************************************
     */
    describe("equal", () => {

        it("returns true for identical primitives", () => {

            // Check result
            assert.equal(Arrays.equal(1, 1), true);

        });

        it("returns true for two equal arrays", () => {

            // Check result
            assert.equal(Arrays.equal([1, 2, 3], [1, 2, 3]), true);

        });

        it("returns false for arrays with a different order", () => {

            // Check result
            assert.equal(Arrays.equal([1, 2, 3], [3, 2, 1]), false);

        });

        it("returns false for arrays of different length", () => {

            // Check result
            assert.equal(Arrays.equal([1, 2], [1, 2, 3]), false);

        });

        it("returns true for two deeply equal objects", () => {

            // Check result
            assert.equal(Arrays.equal({ a: 1, b: { c: 2 } }, { a: 1, b: { c: 2 } }), true);

        });

        it("returns false for objects with a different number of keys", () => {

            // Check result
            assert.equal(Arrays.equal({ a: 1 }, { a: 1, b: 2 }), false);

        });

        it("returns false when comparing an array to a plain object", () => {

            // Check result
            assert.equal(Arrays.equal([1, 2], { 0: 1, 1: 2 }), false);

        });

        it("returns true for two null values", () => {

            // Check result
            assert.equal(Arrays.equal(null, null), true);

        });

        it("returns false when one side is null and the other an object", () => {

            // Check result
            assert.equal(Arrays.equal(null, { a: 1 }), false);

        });

        // NOTE: Arrays.equal(NaN, NaN) falls through every branch (NaN === NaN
        // is false, and typeof NaN is "number" not "object") down to the final
        // `return false`, even though NaN is mathematically "equal to itself"
        // for most equality-check helpers (e.g. Object.is). This assertion
        // intentionally pins the current behavior - see conversation with the
        // maintainer. Update it if/when Arrays.equal is special-cased for NaN.
        it("currently (incorrectly, arguably) returns false for two NaN values, see note above", () => {

            // Check result
            assert.equal(Arrays.equal(NaN, NaN), false);

        });

        it("returns true for two empty arrays", () => {

            // Check result
            assert.equal(Arrays.equal([], []), true);

        });

    });

    /** chunk
     ******************************************************
     */
    describe("chunk", () => {

        it("splits an array into chunks of the given size", () => {

            // Check result
            assert.deepEqual(Arrays.chunk([1, 2, 3, 4, 5], 2), [[1, 2], [3, 4], [5]]);

        });

        it("defaults to a chunk size of 50", () => {

            // Set input
            const input = Array.from({ length: 120 }, (_, i) => i);

            // Set result
            const result = Arrays.chunk(input);

            // Check result
            assert.deepEqual(result.map(chunk => chunk.length), [50, 50, 20]);

        });

        it("returns an empty array for an empty input", () => {

            // Check result
            assert.deepEqual(Arrays.chunk([]), []);

        });

    });

});
