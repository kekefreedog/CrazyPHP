<?php declare(strict_types=1);
/**
 * Browser Transport Benchmark
 *
 * Serve the benchmark page and a fixed page state through the production API response.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
use Nyholm\Psr7\ServerRequest;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Model\Context;
use CrazyPHP\Model\Env;

# Restrict this development fixture to the PHP development server
if(PHP_SAPI !== "cli-server")

    # End the fixture outside its benchmark environment
    exit;

# Resolve the requested benchmark path
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

# Exclude browser and proxy caches from the measurements
header("Cache-Control: no-store");

# Serve the installed browser build used for MessagePack decoding
if($path === "/__bench/transport/decoder.js"){

    # Identify the browser script
    header("Content-Type: application/javascript");

    # Serve the decoder from the installed npm dependency
    readfile(dirname(__DIR__, 2)."/node_modules/msgpackr/dist/index.min.js");

}elseif($path === "/__bench/transport/measure.mjs"){

    # Identify the shared client measurement module
    header("Content-Type: application/javascript");

    # Use the same workload in Node and the browser
    readfile(dirname(__DIR__)."/Front/Bench/transport.mjs");

}elseif($path === "/__bench/transport"){

    # Identify the benchmark page
    header("Content-Type: text/html; charset=UTF-8");

    # Serve the self-running measurement page
    readfile(__DIR__."/transport.html");

}elseif($path === "/__bench/transport/state"){

    # Load the framework
    require dirname(__DIR__, 2)."/vendor/autoload.php";

    # Initialize an isolated application environment
    Env::set([
        "app_root"         =>  __DIR__,
        "crazyphp_root"    =>  dirname(__DIR__, 2)
    ]);

    # Set the same default representation as a production application
    $GLOBALS[Config::PREFIX]["Router"] = [
        "Router"    =>  [
            "parameters"    =>  [
                "api"    =>  [
                    "format"    =>  "msgpack"
                ]
            ]
        ]
    ];

    # Supply the route context normally installed by the router
    $GLOBALS[Context::PREFIX] = [
        "ROUTES"    =>  [
            "CURRENT"    =>  []
        ]
    ];

    # Prepare the fixed catalog outside the timer
    $items = [];

    # Generate the same thousand records for every representation
    for($id = 0; $id < 1000; $id++)

        # Append the next catalog record
        $items[] = [
            "id"        =>  $id,
            "name"      =>  "Item $id",
            "active"    =>  true
        ];

    # Restrict the identity control case while retaining the real client format and gzip preferences
    $request = new ServerRequest("GET", "/state", [
        "Accept"             =>  $_SERVER["HTTP_ACCEPT"] ?? "",
        "Accept-Encoding"    =>  ($_GET["coding"] ?? "") === "identity" ? "identity" : ($_SERVER["HTTP_ACCEPT_ENCODING"] ?? "")
    ]);

    # Create the real API response with the same state in each case
    $response = (new ApiResponse($request))->setContent([
        "page"     =>  "catalog",
        "items"    =>  $items
    ]);

    # Identify the runtime and the encoder selected by the framework
    $response->addHeader("X-Bench-Runtime", "PHP ".PHP_VERSION." / ".(function_exists("msgpack_pack") ? "ext-msgpack" : "rybakit/msgpack"));

    # Start the server-side preparation timer
    $start = hrtime(true);

    # Negotiate, encode and optionally compress the response
    $response->prepare();

    # Report preparation time before the emitter runs
    $response->response = $response->response->withHeader("Server-Timing", "prepare;dur=".((hrtime(true) - $start) / 1e6));

    # Send the prepared PSR-7 response
    $response->send();

}else{

    # Reject unknown benchmark paths
    http_response_code(404);

}
