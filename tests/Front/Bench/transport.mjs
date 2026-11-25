/**
 * Measure Transport
 *
 * Compare four representations in rotating order and validate every response.
 */
export async function measureTransport(origin, unpack){

    // Prepare the output rows
    const result = [];

    // Record the PHP runtime used by this run
    let runtime;

    // Define the four format and content coding combinations
    const cases = ["json", "msgpack"].flatMap(format =>
        ["identity", "gzip"].map(coding => ({format, coding, samples: []}))
    );

    // Prepare an independent expected catalog for full validation
    const expected = JSON.stringify({page: "catalog", items: Array.from({length: 1000}, (_, id) => ({id, name: `Item ${id}`, active: true}))});

    // Reuse the UTF-8 decoder for the isolated JSON parsing timer
    const decoder = new TextDecoder();

    // Discard one warm-up and collect eleven observations per case
    for(let pass = 0; pass < 12; pass++){

        // Rotate which case runs first
        for(let offset = 0; offset < cases.length; offset++){

            // Resolve the current case
            const entry = cases[(offset + pass) % cases.length];

            // Start the end-to-end client timer
            const start = performance.now();

            // Request the exact MIME type through normal Fetch negotiation
            const response = await fetch(origin + "/__bench/transport/state?coding=" + entry.coding, {headers: {Accept: "application/" + entry.format}, cache: "no-store"});

            // Read bytes after Fetch-managed HTTP decompression
            const bytes = new Uint8Array(await response.arrayBuffer());

            // Parse with the same underlying decoders as CrazyPHP's frontend
            const parse = () => entry.format === "json" ? JSON.parse(decoder.decode(bytes)) : unpack(bytes);

            // Decode the complete state
            const state = parse();

            // Stop the client timer before validation
            const totalMs = performance.now() - start;

            // Capture server details alongside the timings
            runtime = response.headers.get("X-Bench-Runtime");

            // Read the actual representation size on the wire
            const wireBytes = Number(response.headers.get("Content-Length"));

            // Read PHP negotiation, encoding and compression time
            const timing = /^prepare;dur=([\d.e+-]+)$/i.exec(response.headers.get("Server-Timing") || "");

            // Verify headers, bytes and full decoded data for this case
            if(response.status !== 200 || response.headers.get("Content-Type")?.split(";")[0] !== "application/" + entry.format ||
                (response.headers.get("Content-Encoding") || "identity") !== entry.coding || !wireBytes || !timing ||
                JSON.stringify(state) !== expected)

                // Fail visibly on a representation or transport mismatch
                throw new Error("Invalid response for " + entry.format + "/" + entry.coding);

            // Start a batch to reduce timer quantization
            const decodeStart = performance.now();

            // Retain the last batch output for validation
            let last;

            // Time decoding from already decompressed bytes
            for(let operation = 0; operation < 30; operation++)

                // Decode the same independent state
                last = parse();

            // Convert the batch mean to microseconds
            const decodeUs = (performance.now() - decodeStart) * 1000 / 30;

            // Verify the final timed result
            if(JSON.stringify(last) !== expected)

                // Reject inconsistent decodes
                throw new Error("Decode mismatch");

            // Keep observations after warm-up
            if(pass > 0)

                // Record each measured quantity separately
                entry.samples.push({wireBytes, phpUs: Number(timing[1]) * 1000, decodeUs, totalMs});

        }

    }

    // Summarize the median of each metric independently
    for(const entry of cases){

        // Select the middle of eleven sorted observations
        const median = key => entry.samples.map(sample => sample[key]).sort((a, b) => a - b)[5];

        // Report time units alongside encoded body bytes
        result.push({runtime, format: entry.format, coding: entry.coding, bytes: median("wireBytes"), phpUs: median("phpUs"), decodeUs: median("decodeUs"), totalMs: median("totalMs")});

    }

    // Return validated median measurements
    return result;

}
