/**
 * HTTP Framework Benchmarks
 *
 * Compare verified local endpoints under identical closed-loop concurrency.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

// Build the expected list independently of each framework's renderer.
const catalog = "<ul>" + Array.from({length: 100}, (_, i) => `<li>Item ${i + 1}</li>`).join("") + "</ul>";

// Define status, media type and body contracts shared by all targets.
const scenarios:Scenario[] = [
    {path: "/ping", type: "text/plain", valid: body => body === "ok"},
    {path: "/users/42", type: "application/json", valid: body => {
        // Parse the response to permit equivalent JSON formatting.
        const value = JSON.parse(body);

        // Require the single numeric field.
        const result = value?.id === 42 && Object.keys(value).length === 1;

        // Return the contract check.
        return result;
    }},
    {path: "/catalog", type: "text/html", valid: body => body.trim().replace(/>\s+</g, "><") === catalog}
];

/** Read one full response, measure client latency and verify the workload. */
async function request(url:URL, scenario:Scenario):Promise<number> {

    // Start the client-side latency timer.
    const start = performance.now();

    // Send directly to the configured endpoint with a bounded timeout.
    const response = await fetch(url, {redirect: "manual", signal: AbortSignal.timeout(5000)});

    // Consume the response before recording completion.
    const body = await response.text();

    // Capture latency before content validation.
    const result = performance.now() - start;

    // Reject redirects, error pages and mismatched fixture output.
    if(response.status !== 200 || response.headers.get("content-type")?.split(";")[0].trim() !== scenario.type || !scenario.valid(body))

        // Surface an actionable failure without counting it as throughput.
        throw new Error(`Unexpected status, content type or body for ${url} (HTTP ${response.status})`);

    // Return elapsed milliseconds.
    return result;

}

/** Compare each target in sequence so they do not compete for the same server resources. */
export async function measureHttp(targets:string[], requests:number, concurrency:number):Promise<HttpMeasurement[]> {

    // Accumulate one row per target and scenario.
    const result:HttpMeasurement[] = [];

    // Measure the bundled fixture and any additional targets.
    for(const target of targets){

        // Split the label from its local origin.
        const match = /^([^=]+)=(https?:\/\/.+)$/i.exec(target);

        // Require an explicit label and URL.
        if(!match)

            // Show the expected syntax.
            throw new Error("Use --target Name=http://127.0.0.1:PORT");

        // Resolve the origin before constructing workload URLs.
        const origin = new URL(match[2]);

        // Limit the load generator to local test applications.
        if(!["127.0.0.1", "[::1]", "localhost"].includes(origin.hostname) || origin.pathname !== "/" || origin.username || origin.password || origin.search || origin.hash)

            // Reject ambiguous or non-local targets.
            throw new Error("Each target must be a loopback origin without credentials, path, query or fragment");

        // Run the same endpoints in the same order.
        for(const scenario of scenarios){

            // Construct the endpoint URL once.
            const url = new URL(scenario.path, origin);

            // Warm connections and framework caches at the configured concurrency.
            const warmup = await Promise.allSettled(Array.from({length: concurrency}, () => request(url, scenario)));

            // Detect unavailable or incompatible fixtures before recording timings.
            const failure = warmup.find(entry => entry.status === "rejected");

            // Explain the endpoint that failed preflight.
            if(failure?.status === "rejected")

                // Include the underlying validation or connection error.
                throw new Error(`Warm-up failed at ${url}: ${failure.reason}`);

            // Store latencies only for successful responses.
            const times:number[] = [];

            // Retain the first failure for diagnosis.
            let firstError = "";

            // Track allocated requests across the workers.
            let issued = 0;

            // Start the shared wall clock.
            const start = performance.now();

            // Keep at most one request in flight per worker.
            await Promise.all(Array.from({length: concurrency}, async () => {

                // Claim work synchronously before awaiting the network.
                while(issued < requests){

                    // Reserve one unique request.
                    issued++;

                    // Measure and validate each response independently.
                    try{

                        // Record successful requests.
                        times.push(await request(url, scenario));

                    }catch(error){

                        // Keep the first error while other workers finish.
                        firstError ||= String(error);

                    }

                }

            }));

            // Capture wall time before sorting or formatting.
            const seconds = (performance.now() - start) / 1000;

            // Sort successful latency samples for nearest-rank percentiles.
            times.sort((left, right) => left - right);

            // Read the nearest-rank percentile or indicate an empty sample.
            const percentile = (fraction:number) => times[Math.ceil(times.length * fraction) - 1] ?? null;

            // Count every failed validation, timeout or connection error.
            const errors = requests - times.length;

            // Record enough context to reproduce the comparison.
            result.push({target: match[1], origin: origin.origin, path: scenario.path, requests, concurrency,
                p50Ms: percentile(0.5), p95Ms: percentile(0.95), successPerSecond: times.length / seconds,
                errors, errorPercent: 100 * errors / requests});

            // Signal failures to automation while retaining the measured row.
            if(errors){

                // Identify the first measured failure on stderr.
                console.error(`${url}: ${firstError}`);

                // Preserve a failing process status after printing all results.
                process.exitCode = 1;

            }

        }

    }

    // Return the measurements for the combined report.
    return result;

}

/** Endpoint output contract verified before and throughout the measured run. */
interface Scenario {
    path:string;
    type:string;
    valid:(body:string) => boolean;
}

/** Latency and throughput of verified responses for one target and endpoint. */
interface HttpMeasurement {
    target:string;
    origin:string;
    path:string;
    requests:number;
    concurrency:number;
    p50Ms:number|null;
    p95Ms:number|null;
    successPerSecond:number;
    errors:number;
    errorPercent:number;
}
