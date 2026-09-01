/**
 * MessagePack Test
 *
 * Tests for Front TS MessagePack utility methods
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
import MessagePack from "../../../../src/Front/Library/Utility/MessagePack";

/**
 * MessagePack
 *
 * Methods for test message pack methods
 */
describe("Front/Library/Utility/MessagePack", () => {

    /** stringify
     ******************************************************
     */
    describe("stringify", () => {

        it("packs a string into a Buffer/Uint8Array", () => {

            // Set result
            const result = MessagePack.stringify("hello world");

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("packs a number", () => {

            // Set result
            const result = MessagePack.stringify(42);

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("packs an array", () => {

            // Set result
            const result = MessagePack.stringify([1, 2, 3]);

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("packs an object", () => {

            // Set result
            const result = MessagePack.stringify({ foo: "bar" });

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("packs a boolean", () => {

            // Set result
            const result = MessagePack.stringify(true);

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("packs null", () => {

            // Set result
            const result = MessagePack.stringify(null);

            // Check result
            assert.ok(result instanceof Uint8Array);

        });

        it("gzips the packed result when gzipBrotli is true", () => {

            // Set plain result
            const plain = MessagePack.stringify("hello world");

            // Set gzipped result
            const gzipped = MessagePack.stringify("hello world", true);

            // Check result is different from plain packed buffer
            assert.ok(gzipped instanceof Uint8Array);

            // Check gzip magic bytes (0x1f, 0x8b)
            assert.equal(gzipped[0], 0x1f);
            assert.equal(gzipped[1], 0x8b);

            // Check gzipped output differs from ungzipped output
            assert.notEqual(Buffer.from(gzipped).toString('hex'), Buffer.from(plain).toString('hex'));

        });

    });

    /** parse
     ******************************************************
     */
    describe("parse", () => {

        it("returns null when input is falsy", () => {

            // Check result
            assert.equal(MessagePack.parse(null as any), null);

        });

        it("round-trips a string", () => {

            // Set packed
            const packed = MessagePack.stringify("hello world");

            // Check result
            assert.equal(MessagePack.parse(packed), "hello world");

        });

        it("round-trips a number", () => {

            // Set packed
            const packed = MessagePack.stringify(42);

            // Check result
            assert.equal(MessagePack.parse(packed), 42);

        });

        it("round-trips a negative float", () => {

            // Set packed
            const packed = MessagePack.stringify(-3.14);

            // Check result
            assert.equal(MessagePack.parse(packed), -3.14);

        });

        it("round-trips zero", () => {

            // Set packed
            const packed = MessagePack.stringify(0);

            // Check result
            assert.equal(MessagePack.parse(packed), 0);

        });

        it("round-trips an array", () => {

            // Set packed
            const packed = MessagePack.stringify([1, "two", 3.0, null, true]);

            // Check result
            assert.deepEqual(MessagePack.parse(packed), [1, "two", 3.0, null, true]);

        });

        it("round-trips an empty array", () => {

            // Set packed
            const packed = MessagePack.stringify([]);

            // Check result
            assert.deepEqual(MessagePack.parse(packed), []);

        });

        it("round-trips an object", () => {

            // Set input
            const input = { foo: "bar", nested: { a: 1, b: [1, 2, 3] } };

            // Set packed
            const packed = MessagePack.stringify(input);

            // Check result
            assert.deepEqual(MessagePack.parse(packed), input);

        });

        it("round-trips an empty string", () => {

            // Set packed
            const packed = MessagePack.stringify("");

            // Check result
            assert.equal(MessagePack.parse(packed), "");

        });

        it("round-trips a boolean", () => {

            // Set packed
            const packed = MessagePack.stringify(false);

            // Check result
            assert.equal(MessagePack.parse(packed), false);

        });

        it("round-trips null", () => {

            // Set packed
            const packed = MessagePack.stringify(null);

            // Check result
            assert.equal(MessagePack.parse(packed), null);

        });

        it("round-trips a gzipped payload when gzipBrotli is true", () => {

            // Set input
            const input = { foo: "bar", list: [1, 2, 3] };

            // Set packed and gzipped
            const packed = MessagePack.stringify(input, true);

            // Check result
            assert.deepEqual(MessagePack.parse(packed, true), input);

        });

        it("round-trips a large array through gzip", () => {

            // Set input
            const input = Array.from({ length: 200 }, (_, i) => i);

            // Set packed and gzipped
            const packed = MessagePack.stringify(input, true);

            // Check result
            assert.deepEqual(MessagePack.parse(packed, true), input);

        });

    });

});
