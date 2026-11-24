/**
 * Page State Decode Benchmarks
 *
 * Measure CrazyPHP MessagePack decoding with fixed small and large payloads.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import MessagePack from "../../../src/Front/Library/Utility/MessagePack";
import assert from "node:assert/strict";

/** Measure both encodings and payload sizes. */
export function measureDecode():DecodeMeasurement[] {

    // Collect one row per payload and encoding.
    const result:DecodeMeasurement[] = [];

    // Exercise a small state update and a larger catalog.
    for(const items of [1, 1000]){

        // Create stable, nested records outside the timed section.
        const state = {page: "catalog", items: Array.from({length: items}, (_, id) => ({id, name: `Item ${id}`, active: true}))};

        // Give slower operations smaller batches.
        const opsPerBatch = items === 1 ? 500 : 30;

        // Measure identical content with both supported encodings.
        for(const gzip of [false, true]){

            // Serialize once so only decoding is timed.
            const payload = MessagePack.stringify(state, gzip);

            // Verify every field before measuring.
            assert.deepStrictEqual(MessagePack.parse(payload, gzip), state);

            // Collect batch means in microseconds.
            const samples:number[] = [];

            // Discard the first batch as a warm-up.
            for(let batch = 0; batch <= 11; batch++){

                // Retain the final value for validation.
                let decoded:unknown;

                // Start the monotonic clock.
                const start = performance.now();

                // Measure repeated decodes.
                for(let i = 0; i < opsPerBatch; i++)

                    // Decode the complete state.
                    decoded = MessagePack.parse(payload, gzip);

                // Stop the timer before checking the output.
                const elapsed = (performance.now() - start) * 1000 / opsPerBatch;

                // Verify the timed code still produces the expected state.
                assert.deepStrictEqual(decoded, state);

                // Save measured batches.
                if(batch > 0)

                    // Record the mean per decode.
                    samples.push(elapsed);

            }

            // Order batch averages for the reported range.
            samples.sort((left, right) => left - right);

            // Include payload size to expose the compression trade-off.
            result.push({
                items, encoding: gzip ? "gzip" : "plain", bytes: payload.byteLength,
                medianUs: samples[Math.floor(samples.length / 2)], minUs: samples[0], maxUs: samples[samples.length - 1],
                batches: samples.length, opsPerBatch
            });

        }

    }

    // Return the verified decode measurements.
    return result;

}

/** Encoded size and distribution of batch means for a single page state. */
interface DecodeMeasurement {
    items:number;
    encoding:"plain"|"gzip";
    bytes:number;
    medianUs:number;
    minUs:number;
    maxUs:number;
    batches:number;
    opsPerBatch:number;
}
