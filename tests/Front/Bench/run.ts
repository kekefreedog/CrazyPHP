/**
 * Frontend and HTTP Benchmarks
 *
 * Measure decoding and serve the local HTTP fixture for the duration of the run.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import MessagePack from "../../../src/Front/Library/Utility/MessagePack";
import {measureTransport} from "./transport.mjs";
import {once} from "node:events";
import {createInterface} from "node:readline";
import {spawn} from "node:child_process";
import {measureDecode} from "./decode";
import {measureHttp} from "./http";
import {parseArgs} from "node:util";

/** Parse a bounded positive integer from the CLI. */
function count(input:string, maximum:number, option:string):number {

    // Convert the supplied text once.
    const result = Number(input);

    // Require a whole number inside the supported range.
    if(!Number.isSafeInteger(result) || result < 1 || result > maximum)

        // Identify the invalid argument.
        throw new Error(`--${option} must be an integer from 1 to ${maximum}`);

    // Return the validated setting.
    return result;

}

/** Run the local measurements and print one report. */
async function main():Promise<void> {

    // Parse shared output and HTTP workload options once.
    const {values} = parseArgs({options: {
        target: {type: "string", multiple: true}, requests: {type: "string", default: "200"},
        concurrency: {type: "string", default: "8"}, json: {type: "boolean"}, browser: {type: "boolean"}
    }});

    // Check the requested sample count.
    const requests = count(values.requests!, 100000, "requests");

    // Bound concurrency by the sample count.
    const concurrency = count(values.concurrency!, Math.min(requests, 128), "concurrency");

    // Measure decoding before starting the HTTP fixture.
    const results = measureDecode();

    // Run a single PHP worker so the child owns the complete server lifecycle.
    const env = {...process.env};

    // Use PHP's single-worker default even when a development shell enables workers.
    delete env.PHP_CLI_SERVER_WORKERS;

    // Let the OS allocate a free loopback port for this run.
    const server = spawn("php", ["-d", "zlib.output_compression=0", "-S", "127.0.0.1:0", "-t", "tests/Bench", "tests/Bench/http.php"],
        {env, stdio: ["ignore", "ignore", "pipe"]});

    // Terminate only the server started by this invocation.
    const stop = () => server.kill();

    // Translate terminal interrupts into normal process cleanup.
    const interrupt = () => process.exit(130);

    // Handle termination by a parent process.
    const terminate = () => process.exit(143);

    // Release the child when Node exits during startup or measurement.
    process.once("exit", stop);

    // Preserve the usual Ctrl-C exit status.
    process.once("SIGINT", interrupt);

    // Preserve the usual termination exit status.
    process.once("SIGTERM", terminate);

    // Parse PHP's startup line and drain subsequent request logs.
    const logs = createInterface({input: server.stderr});

    // Bound startup even when PHP fails to announce an address.
    const startup = AbortSignal.timeout(10000);

    // Always release the fixture after reporting or a failed preflight.
    try{

        // Wait until this child reports the address it successfully bound.
        const origin = await new Promise<string>((resolve, reject) => {

            // Retain startup diagnostics for a failing child.
            let diagnostic = "";

            // Report missing PHP executables and other launch errors.
            server.once("error", reject);

            // Explain early exits such as an unavailable socket.
            server.once("close", code => reject(new Error(`PHP fixture exited (${code}): ${diagnostic}`)));

            // Fail startup if no address arrives before the deadline.
            startup.addEventListener("abort", () => reject(new Error("PHP fixture startup timed out")), {once: true});

            // Inspect complete lines even when stderr arrives in partial chunks.
            logs.on("line", line => {

                // Retain the most recent PHP message.
                diagnostic = line;

                // Extract the port reported by the PHP development server.
                const match = /Development Server \((http:\/\/127\.0\.0\.1:\d+)\) started/.exec(line);

                // Start benchmarking once the listener is ready.
                if(match)

                    // Pass the verified origin to the HTTP runner.
                    resolve(match[1]);

            });

        });

        // Continue draining PHP stderr without parsing individual request logs.
        logs.removeAllListeners("line");

        // Measure the local fixture and any reference applications sequentially.
        const http = await measureHttp([`CrazyPHP=${origin}`, ...(values.target ?? [])], requests, concurrency);

        // Measure the four real PHP responses through Node Fetch using the framework decoder.
        const transport = await measureTransport(origin, MessagePack.parse);

        // Include transport measurements alongside the established report fields.
        if(values.json){

            // Emit one parseable document for the entire command.
            console.log(JSON.stringify({node: process.version, results, http, transport}, null, 2));

        }else{

            // Identify the runtime and decode latency units.
            console.log(`Node ${process.version} · MessagePack decode · microseconds per operation`);

            // Print rounded decode measurements.
            console.table(results.map(row => ({...row, medianUs: +row.medianUs.toFixed(3),
                minUs: +row.minUs.toFixed(3), maxUs: +row.maxUs.toFixed(3)})));

            // Identify the transport workload and the client used for decoding.
            console.log("PHP → Node Fetch · 1,000 items · median preparation and decode in µs, total in ms");

            // Print the verified medians for each format and content coding.
            console.table(transport);

            // Label the HTTP latency table.
            console.log("HTTP latency in milliseconds");

            // Print rounded latency and throughput for each endpoint.
            console.table(http.map(row => ({...row, p50Ms: row.p50Ms?.toFixed(2), p95Ms: row.p95Ms?.toFixed(2),
                successPerSecond: row.successPerSecond.toFixed(1)})));

        }

        // Keep the same fixture available for an optional real-browser measurement.
        if(values.browser){

            // Send instructions to stderr to keep the JSON report machine-readable.
            console.error(`Browser measurement: ${origin}/__bench/transport (Ctrl-C to stop)`);

            // Leave the server running only when the interactive option is selected.
            await once(server, "close");

        }

    }finally{

        // Stop the PHP server on successful and failed runs.
        stop();

        // Remove exit handlers once cleanup has been requested.
        process.removeListener("exit", stop);

        // Restore the default interrupt behavior.
        process.removeListener("SIGINT", interrupt);

        // Restore the default termination behavior.
        process.removeListener("SIGTERM", terminate);

    }

}

// Report startup and validation errors through the normal command status.
main().catch(error => {

    // Explain the failed workload or fixture.
    console.error(error.message);

    // Fail the combined command.
    process.exitCode = 1;

});
