/**
 * Process Test
 *
 * Tests for Front TS Process utility methods
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
import Process from "../../../../src/Front/Library/Utility/Process";

/**
 * Process
 *
 * Methods for test process methods
 */
describe("Front/Library/Utility/Process", () => {

    /** spaceBeforeCapital
     ******************************************************
     */
    describe("spaceBeforeCapital", () => {

        it("inserts a space before each capital letter, except the first character", () => {

            // Set input
            const input = "HelloWorld";

            // Check result
            assert.equal(Process.spaceBeforeCapital(input), "Hello World");

        });

        it("inserts a space before every subsequent capital in a longer word", () => {

            // Set input
            const input = "ThisIsATest";

            // Check result
            assert.equal(Process.spaceBeforeCapital(input), "This Is A Test");

        });

        it("leaves a lowercase-only string unchanged", () => {

            // Check result
            assert.equal(Process.spaceBeforeCapital("hello"), "hello");

        });

        it("returns falsy input untouched", () => {

            // Check result
            assert.equal(Process.spaceBeforeCapital(""), "");

        });

    });

    /** capitalize
     ******************************************************
     */
    describe("capitalize", () => {

        it("capitalizes the first character", () => {

            // Check result
            assert.equal(Process.capitalize("hello world"), "Hello world");

        });

        it("leaves an already capitalized string unchanged", () => {

            // Check result
            assert.equal(Process.capitalize("Hello"), "Hello");

        });

        it("returns falsy input untouched", () => {

            // Check result
            assert.equal(Process.capitalize(""), "");

        });

    });

});
