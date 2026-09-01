/**
 * Objects Test
 *
 * Tests for Front TS Objects utility methods
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
import Objects from "../../../../src/Front/Library/Utility/Objects";

/**
 * Objects
 *
 * Methods for test objects methods
 */
describe("Front/Library/Utility/Objects", () => {

    /** convertToNestedObject
     ******************************************************
     */
    describe("convertToNestedObject", () => {

        it("converts dotted keys into a nested structure", () => {

            // Check result
            assert.deepEqual(
                Objects.convertToNestedObject({ "a.b": 1, "a.c": 2, d: 3 }),
                { a: { b: 1, c: 2 }, d: 3 }
            );

        });

        it("recurses into existing nested objects", () => {

            // Check result
            assert.deepEqual(
                Objects.convertToNestedObject({ a: { b: 1 }, "c.d": 2 }),
                { a: { b: 1 }, c: { d: 2 } }
            );

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Objects.convertToNestedObject({}), {});

        });

        // NOTE: convertToNestedObject() checks `typeof obj[key] === 'object'`
        // to decide whether to recurse, but that check is also true for
        // arrays, so an array value gets walked with a `for...in` loop and
        // rebuilt as a plain object keyed by its numeric indexes instead of
        // staying an array. This assertion intentionally pins the current
        // (buggy) behavior - see conversation with the maintainer. Update it
        // to expect an actual array once convertToNestedObject is fixed.
        it("currently turns array values into index-keyed objects, see note above", () => {

            // Set result
            const result = Objects.convertToNestedObject({ a: [1, 2, 3] });

            // Check array-ness is lost
            assert.equal(Array.isArray((result as any).a), false);

            // Check result
            assert.deepEqual(result, { a: { "0": 1, "1": 2, "2": 3 } });

        });

    });

    /** flatten
     ******************************************************
     */
    describe("flatten", () => {

        it("flattens a nested object using the default separator", () => {

            // Check result
            assert.deepEqual(
                Objects.flatten({ a: { b: 1, c: { d: 2 } }, e: 3 }),
                { "a.b": 1, "a.c.d": 2, e: 3 }
            );

        });

        it("keeps arrays as leaf values instead of flattening them", () => {

            // Check result
            assert.deepEqual(
                Objects.flatten({ a: [1, 2], b: 1 }),
                { a: [1, 2], b: 1 }
            );

        });

        it("uses a custom separator when given", () => {

            // Check result
            assert.deepEqual(
                Objects.flatten({ a: { b: 1 } }, "", "_"),
                { "a_b": 1 }
            );

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Objects.flatten({}), {});

        });

    });

    /** unflatten
     ******************************************************
     */
    describe("unflatten", () => {

        it("expands dotted keys back into a nested object", () => {

            // Check result
            assert.deepEqual(
                Objects.unflatten({ "a.b": 1, "a.c.d": 2, e: 3 }),
                { a: { b: 1, c: { d: 2 } }, e: 3 }
            );

        });

        it("supports a custom separator", () => {

            // Check result
            assert.deepEqual(
                Objects.unflatten({ "a_b": 1 }, "_"),
                { a: { b: 1 } }
            );

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Objects.unflatten({}), {});

        });

    });

    /** deepMergeOld
     ******************************************************
     */
    describe("deepMergeOld", () => {

        it("deeply merges plain objects, the later value winning", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMergeOld(false, { a: 1, b: { c: 1 } }, { b: { d: 2 }, a: 3 }),
                { a: 3, b: { c: 1, d: 2 } }
            );

        });

        it("accumulates values into an array when createIfNotExists is true", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMergeOld(true, { a: 1 }, { a: 2 }),
                { a: [1, 2] }
            );

        });

    });

    /** deepMerge
     ******************************************************
     */
    describe("deepMerge", () => {

        it("deeply merges plain objects, the later value winning", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMerge(false, { a: 1, b: { c: 1 } }, { b: { d: 2 }, a: 3 }),
                { a: 3, b: { c: 1, d: 2 } }
            );

        });

        it("overwrites an existing key even when createIfNotExists is false", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMerge(false, { a: 1 }, { a: 2 }),
                { a: 2 }
            );

        });

        it("merges arrays index by index", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMerge(false, { a: [1, 2] }, { a: [9] }),
                { a: [9, 2] }
            );

        });

        it("ignores null/undefined sources and keeps the current target", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMerge(false, { a: 1 }, null),
                { a: 1 }
            );

        });

        it("returns an empty object when no input is given", () => {

            // Check result
            assert.deepEqual(Objects.deepMerge(false), {});

        });

    });

    /** deepMergeWithSeparator
     ******************************************************
     */
    describe("deepMergeWithSeparator", () => {

        it("concatenates string leaves with the given separator", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMergeWithSeparator(true, ",", { a: "x" }, { a: "y" }),
                { a: "x,y" }
            );

        });

        it("deeply merges nested string leaves", () => {

            // Check result
            assert.deepEqual(
                Objects.deepMergeWithSeparator(true, "-", { a: { b: "x" } }, { a: { b: "y" } }),
                { a: { b: "x-y" } }
            );

        });

    });

    /** sortByKey
     ******************************************************
     */
    describe("sortByKey", () => {

        it("sorts entries by their numeric orderKey property", () => {

            // Check result
            assert.deepEqual(
                Objects.sortByKey({
                    x: { order: 2 },
                    y: { order: 1 },
                }),
                {
                    y: { order: 1 },
                    x: { order: 2 },
                }
            );

        });

        it("excludes entries whose orderKey is missing or not a positive number", () => {

            // Set result
            const result = Objects.sortByKey({
                x: { order: 2 },
                z: { order: 0 },
                w: {},
            });

            // Check result
            assert.deepEqual(result, { x: { order: 2 } });

        });

        it("supports a custom orderKey name", () => {

            // Check result
            assert.deepEqual(
                Objects.sortByKey({ x: { rank: 2 }, y: { rank: 1 } }, "rank"),
                { y: { rank: 1 }, x: { rank: 2 } }
            );

        });

    });

    /** setValue
     ******************************************************
     */
    describe("setValue", () => {

        it("sets a nested value using a dotted string path", () => {

            // Set input
            const obj: any = { a: { b: 1 } };

            // Set value
            Objects.setValue(obj, "a.c", 5);

            // Check result
            assert.deepEqual(obj, { a: { b: 1, c: 5 } });

        });

        it("creates the intermediate objects when they don't exist", () => {

            // Set input
            const obj: any = {};

            // Set value
            Objects.setValue(obj, "x.y", 10);

            // Check result
            assert.deepEqual(obj, { x: { y: 10 } });

        });

        it("accepts an already-split array path and ignores the separator", () => {

            // Set input
            const obj: any = {};

            // Set value
            Objects.setValue(obj, ["x", "y"], 10, null);

            // Check result
            assert.deepEqual(obj, { x: { y: 10 } });

        });

        it("sets a top-level key when the path has no separator", () => {

            // Set input
            const obj: any = {};

            // Set value
            Objects.setValue(obj, "single", 1);

            // Check result
            assert.deepEqual(obj, { single: 1 });

        });

    });

    /** equal
     ******************************************************
     */
    describe("equal", () => {

        it("returns true for equal primitives", () => {

            // Check result
            assert.equal(Objects.equal(1, 1), true);

        });

        it("returns true for deeply equal objects", () => {

            // Check result
            assert.equal(Objects.equal({ a: 1, b: { c: 2 } }, { a: 1, b: { c: 2 } }), true);

        });

        it("returns false for objects that differ", () => {

            // Check result
            assert.equal(Objects.equal({ a: 1 }, { a: 2 }), false);

        });

        it("returns true for deeply equal arrays", () => {

            // Check result
            assert.equal(Objects.equal([1, 2, 3], [1, 2, 3]), true);

        });

        it("returns false when comparing an object to null", () => {

            // Check result
            assert.equal(Objects.equal({ a: 1 }, null), false);

        });

    });

    /** difference
     ******************************************************
     */
    describe("difference", () => {

        it("returns only the changed properties between two objects", () => {

            // Check result
            assert.deepEqual(
                Objects.difference(false, { a: 1, b: 2 }, { a: 1, b: 3 }),
                { b: 3 }
            );

        });

        it("returns an empty object when the objects are identical", () => {

            // Check result
            assert.deepEqual(Objects.difference(false, { a: 1 }, { a: 1 }), {});

        });

        it("returns an empty object when fewer than two objects are given", () => {

            // Check result
            assert.deepEqual(Objects.difference(false, { a: 1 }), {});

        });

        it("excludes newly-added properties when onlyUpdated is true", () => {

            // Check result
            assert.deepEqual(Objects.difference(true, { a: 1 }, { a: 1, b: 2 }), {});

        });

        it("includes newly-added properties when onlyUpdated is false", () => {

            // Check result
            assert.deepEqual(Objects.difference(false, { a: 1 }, { a: 1, b: 2 }), { b: 2 });

        });

        it("keeps a deleted property in the result set to undefined", () => {

            // Set result
            const result = Objects.difference(false, { a: 1, b: 2 }, { a: 1 });

            // Check the key is present (JSON.stringify would hide an undefined value)
            assert.equal("b" in result, true);

            // Check its value
            assert.equal(result.b, undefined);

        });

    });

    /** mapHeaders
     ******************************************************
     */
    describe("mapHeaders", () => {

        it("maps numeric-index rows to named columns", () => {

            // Check result
            assert.deepEqual(
                Objects.mapHeaders(["name", "age"] as const, { 0: "John", 1: 30 }),
                { name: "John", age: 30 }
            );

        });

        it("returns an empty object for an empty row", () => {

            // Check result
            assert.deepEqual(Objects.mapHeaders(["name", "age"] as const, {}), {});

        });

    });

    /** sortKey
     ******************************************************
     */
    describe("sortKey", () => {

        it("sorts keys ascending by default", () => {

            // Check result
            assert.deepEqual(
                Objects.sortKey({ b: 1, a: 2, c: 3 }),
                { a: 2, b: 1, c: 3 }
            );

        });

        it("sorts keys descending when asc is false", () => {

            // Check result
            assert.deepEqual(
                Objects.sortKey({ b: 1, a: 2, c: 3 }, false),
                { c: 3, b: 1, a: 2 }
            );

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Objects.sortKey({}), {});

        });

    });

    /** keysPrefix
     ******************************************************
     */
    describe("keysPrefix", () => {

        it("prefixes the top level keys by default (level 1)", () => {

            // Check result
            assert.deepEqual(
                Objects.keysPrefix({ a: 1, b: { c: 2 } }, "pre_"),
                { pre_a: 1, pre_b: { c: 2 } }
            );

        });

        it("prefixes keys at the requested nesting level", () => {

            // Check result
            assert.deepEqual(
                Objects.keysPrefix({ a: 1, b: { c: 2, d: 3 } }, "pre_", 2),
                { a: 1, b: { pre_c: 2, pre_d: 3 } }
            );

        });

        it("returns an empty object for an empty input", () => {

            // Check result
            assert.deepEqual(Objects.keysPrefix({}, "pre_"), {});

        });

    });

});
