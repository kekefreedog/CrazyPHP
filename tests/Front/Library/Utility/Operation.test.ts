/**
 * Operation Test
 *
 * Tests for Front TS Operation utility methods
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
import Operation from "../../../../src/Front/Library/Utility/Operation";

/**
 * Operation
 *
 * Methods for test Operation methods
 */
describe("Front/Library/Utility/Operation", () => {

    /** LIST
     ******************************************************
     */
    describe("LIST", () => {

        it("exposes every operation with its own name, symbol and regex", () => {

            // Check result
            assert.equal(Operation.LIST["="].name, "equal");
            assert.equal(Operation.LIST["!="].name, "notEqual");
            assert.equal(Operation.LIST["<="].name, "lessThanOrEqual");
            assert.equal(Operation.LIST[">="].name, "greaterThanOrEqual");
            assert.equal(Operation.LIST["<"].name, "smaller");
            assert.equal(Operation.LIST[">"].name, "greater");
            assert.equal(Operation.LIST["![]"].name, "notBetween");
            assert.equal(Operation.LIST["[]"].name, "between");
            assert.equal(Operation.LIST["*"].name, "like");

        });

        it("keeps the operation symbol equal to the key used to store it", () => {

            // Set keys
            const keys = Object.keys(Operation.LIST);

            // Check every key
            for(const key of keys)

                // Check result
                assert.equal(Operation.LIST[key].operation, key);

        });

    });

    /** constructor
     ******************************************************
     */
    describe("constructor", () => {

        it("loads every operation by default", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(Object.keys(op.get()).sort(), Object.keys(Operation.LIST).sort());

        });

        it("loads every operation when '@all' is given explicitly", () => {

            // Set instance
            const op = new Operation("@all");

            // Check result
            assert.deepEqual(Object.keys(op.get()).sort(), Object.keys(Operation.LIST).sort());

        });

        it("loads a single operation from its symbol", () => {

            // Set instance
            const op = new Operation("=");

            // Check result
            assert.deepEqual(Object.keys(op.get()), ["="]);

        });

        it("loads a single operation from its name", () => {

            // Set instance
            const op = new Operation("between");

            // Check result
            assert.deepEqual(Object.keys(op.get()), ["[]"]);

        });

        it("loads several operations from a mix of symbols and names", () => {

            // Set instance
            const op = new Operation(["=", "between", "notEqual"]);

            // Check result
            assert.deepEqual(Object.keys(op.get()).sort(), ["!=", "=", "[]"].sort());

        });

        it("stays empty when an unknown operation string is given", () => {

            // Set instance
            const op = new Operation("nope");

            // Check result
            assert.deepEqual(op.get(), {});

        });

        it("stays empty when an empty array is given", () => {

            // Set instance
            const op = new Operation([]);

            // Check result
            assert.deepEqual(op.get(), {});

        });

        it("ingests options given at construction time", () => {

            // Set instance
            const op = new Operation("@all", { prefix: "P", suffix: "S", key: "K" });

            // Check result
            assert.equal(op.options.prefix, "P");
            assert.equal(op.options.suffix, "S");
            assert.equal(op.options.key, "K");

        });

    });

    /** set / get
     ******************************************************
     */
    describe("set", () => {

        it("replaces the current operations", () => {

            // Set instance
            const op = new Operation("=");

            // Set operations
            op.set(">");

            // Check result
            assert.deepEqual(Object.keys(op.get()), [">"]);

        });

        it("resets to '@all' by default", () => {

            // Set instance
            const op = new Operation("=");

            // Set operations
            op.set();

            // Check result
            assert.deepEqual(Object.keys(op.get()).sort(), Object.keys(Operation.LIST).sort());

        });

        it("empties the current operations for an unknown array entry", () => {

            // Set instance
            const op = new Operation("=");

            // Set operations
            op.set(["nope"]);

            // Check result
            assert.deepEqual(op.get(), {});

        });

    });

    describe("get", () => {

        it("returns the currently configured operations", () => {

            // Set instance
            const op = new Operation("=");

            // Check result
            assert.equal(op.get()["="].name, "equal");

        });

    });

    /** parseEqual / parseNotEqual / parseLessThanOrEqual / parseGreaterThanOrEqual / parseSmaller / parseGreater / parseBetween / parseNotBetween
     ******************************************************
     */
    describe("parseEqual", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseEqual("5", Operation.LIST["="]), { ...Operation.LIST["="], value: "5" });

        });

    });

    describe("parseNotEqual", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseNotEqual("5", Operation.LIST["!="]), { ...Operation.LIST["!="], value: "5" });

        });

    });

    describe("parseLessThanOrEqual", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseLessThanOrEqual("5", Operation.LIST["<="]), { ...Operation.LIST["<="], value: "5" });

        });

    });

    describe("parseGreaterThanOrEqual", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseGreaterThanOrEqual("5", Operation.LIST[">="]), { ...Operation.LIST[">="], value: "5" });

        });

    });

    describe("parseSmaller", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseSmaller("5", Operation.LIST["<"]), { ...Operation.LIST["<"], value: "5" });

        });

    });

    describe("parseGreater", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseGreater("5", Operation.LIST[">"]), { ...Operation.LIST[">"], value: "5" });

        });

    });

    describe("parseBetween", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseBetween(["[1:10]", "1", "10"], Operation.LIST["[]"]), { ...Operation.LIST["[]"], value: ["[1:10]", "1", "10"] });

        });

    });

    describe("parseNotBetween", () => {

        it("wraps the input value with the operation definition", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseNotBetween(["![1:10]", "1", "10"], Operation.LIST["![]"]), { ...Operation.LIST["![]"], value: ["![1:10]", "1", "10"] });

        });

    });

    /** parseDefault
     ******************************************************
     */
    describe("parseDefault", () => {

        it("wraps the value with a 'default' name and no operation/regex", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.parseDefault("abc"), { name: "default", value: "abc" });

        });

    });

    /** parseLike
     ******************************************************
     */
    describe("parseLike", () => {

        it("detects a leading wildcard as a 'start' position", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.parseLike("*abc", Operation.LIST["*"]);

            // Check result
            assert.equal(result.position, "start");
            assert.equal(result.case_sensitive, false);

        });

        it("detects a trailing wildcard as an 'end' position", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.parseLike("abc*", Operation.LIST["*"]);

            // Check result
            assert.equal(result.position, "end");

        });

        it("detects wildcards on both sides as 'start,end'", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.parseLike("*abc*", Operation.LIST["*"]);

            // Check result
            assert.equal(result.position, "start,end");

        });

        it("uses the first element when the value is an array", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.parseLike(["*abc", "abc"], Operation.LIST["*"]);

            // Check result
            assert.equal(result.position, "start");

        });

        it("sets position to null when no wildcard is found", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.parseLike("abc", Operation.LIST["*"]);

            // Check result
            assert.equal(result.position, null);

        });

    });

    /** run
     ******************************************************
     */
    describe("run", () => {

        it("parses a plain equal expression", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.run("=5");

            // Check result
            assert.equal(result.name, "equal");
            assert.deepEqual(result.value, ["=5", "5"]);

        });

        it("parses a not equal expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run("!=5").name, "notEqual");

        });

        it("parses a less than or equal expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run("<=5").name, "lessThanOrEqual");

        });

        it("parses a greater than or equal expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run(">=5").name, "greaterThanOrEqual");

        });

        it("parses a smaller than expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run("<5").name, "smaller");

        });

        it("parses a greater than expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run(">5").name, "greater");

        });

        it("parses a between expression", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.run("[1:10]");

            // Check result
            assert.equal(result.name, "between");
            assert.deepEqual(result.value, ["[1:10]", "1", "10"]);

        });

        it("parses a not between expression", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.run("![1:10]");

            // Check result
            assert.equal(result.name, "notBetween");
            assert.deepEqual(result.value, ["![1:10]", "1", "10"]);

        });

        it("parses a leading wildcard 'like' expression", () => {

            // Set instance
            const op = new Operation();

            // Set result
            const result = op.run("*abc");

            // Check result
            assert.equal(result.name, "like");
            assert.equal(result.position, "start");

        });

        it("parses a trailing wildcard 'like' expression", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.equal(op.run("abc*").position, "end");

        });

        it("falls back to 'default' when nothing matches", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.run("abc"), { name: "default", value: "abc" });

        });

        it("falls back to 'default' for an empty string", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.run(""), { name: "default", value: "" });

        });

        it("returns an empty array for an empty array input", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.run([]), []);

        });

        it("returns a 'default' result for a plain number (numbers can't carry an operator)", () => {

            // Set instance
            const op = new Operation();

            // Check result
            assert.deepEqual(op.run(5), { name: "default", value: 5 });

        });

        it("returns a 'default' result for each value of an array when none of them match an operator", () => {

            // Set instance
            const op = new Operation();

            // Check result: every element is parsed independently, none dropped
            assert.deepEqual(op.run(["5", "6"]), [
                { name: "default", value: "5" },
                { name: "default", value: "6" },
            ]);

        });

        it("parses every matching value of an array instead of stopping at the first", () => {

            // Set instance
            const op = new Operation();

            // Check result: both "=5" and "=6" match "equal" and are both parsed
            assert.deepEqual(op.run(["=5", "=6"]), [
                { ...Operation.LIST["="], value: ["=5", "5"] },
                { ...Operation.LIST["="], value: ["=6", "6"] },
            ]);

        });

        it("restricts matching to the currently configured operations", () => {

            // Set instance
            const op = new Operation("=");

            // Check result: "[1:10]" isn't recognized because "between" isn't loaded
            assert.deepEqual(op.run("[1:10]"), { name: "default", value: "[1:10]" });

        });

        it("merges per-call options on top of the instance options without mutating the instance", () => {

            // Set instance
            const op = new Operation("@all", { prefix: "P" });

            // Run with an override
            op.run("=5", { suffix: "S" });

            // Check instance options untouched
            assert.equal(op.options.prefix, "P");
            assert.equal(op.options.suffix, "");

        });

    });

});
