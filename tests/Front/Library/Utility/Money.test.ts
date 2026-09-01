/**
 * Money Test
 *
 * Tests for Front TS Money utility methods
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
import Money from "../../../../src/Front/Library/Utility/Money";

/**
 * Money
 *
 * Methods for test money methods
 */
describe("Front/Library/Utility/Money", () => {

    /** dollar
     ******************************************************
     */
    describe("dollar", () => {

        it("formats a positive number to US dollar", () => {

            // Check result
            assert.equal(Money.dollar(1234.5), "$1,234.50");

        });

        it("formats a negative number to US dollar", () => {

            // Check result
            assert.equal(Money.dollar(-5), "-$5.00");

        });

        it("formats zero to US dollar", () => {

            // Check result
            assert.equal(Money.dollar(0), "$0.00");

        });

    });

    /** pounds
     ******************************************************
     */
    describe("pounds", () => {

        it("formats a positive number to British pounds", () => {

            // Check result
            assert.equal(Money.pounds(1234.5), "£1,234.50");

        });

        it("formats a negative number to British pounds", () => {

            // Check result
            assert.equal(Money.pounds(-5), "-£5.00");

        });

        it("formats zero to British pounds", () => {

            // Check result
            assert.equal(Money.pounds(0), "£0.00");

        });

    });

    /** rupee
     ******************************************************
     */
    describe("rupee", () => {

        it("formats a positive number to Indian rupee", () => {

            // Check result
            assert.equal(Money.rupee(1234.5), "₹1,234.50");

        });

        it("formats a negative number to Indian rupee", () => {

            // Check result
            assert.equal(Money.rupee(-5), "-₹5.00");

        });

        it("formats zero to Indian rupee", () => {

            // Check result
            assert.equal(Money.rupee(0), "₹0.00");

        });

    });

    /** euro
     ******************************************************
     */
    describe("euro", () => {

        it("formats a positive number to Euro", () => {

            // Check result
            assert.equal(Money.euro(1234.5), "€1,234.50");

        });

        it("formats a negative number to Euro", () => {

            // Check result
            assert.equal(Money.euro(-5), "-€5.00");

        });

        it("formats zero to Euro", () => {

            // Check result
            assert.equal(Money.euro(0), "€0.00");

        });

    });

});
