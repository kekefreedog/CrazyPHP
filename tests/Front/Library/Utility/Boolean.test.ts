/**
 * Boolean Test
 *
 * Tests for Front TS Boolean utility methods
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
import Boolean from "../../../../src/Front/Library/Utility/Boolean";

/**
 * Boolean
 *
 * Methods for test boolean methods
 */
describe("Front/Library/Utility/Boolean", () => {

    /** check
     ******************************************************
     */
    describe("check", () => {

        const truthyCases: unknown[] = [
            true,
            1,
            -1,
            "true",
            "1",
            "on",
            "hello",
            {},
            [],
            " ",
        ];

        for (const value of truthyCases) {

            it(`check(${JSON.stringify(value)}) -> true`, () => {

                // Check result
                assert.equal(Boolean.check(value), true);

            });

        }

        const falsyCases: unknown[] = [
            false,
            null,
            undefined,
            0,
            "",
            "false",
            "0",
            "off",
        ];

        for (const value of falsyCases) {

            it(`check(${JSON.stringify(value)}) -> false`, () => {

                // Check result
                assert.equal(Boolean.check(value), false);

            });

        }

        it("returns true for NaN (not part of the explicit falsy list)", () => {

            // Set input
            const input = NaN;

            // Check result
            assert.equal(Boolean.check(input), true);

        });

    });

});
