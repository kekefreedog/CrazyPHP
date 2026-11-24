<?php declare(strict_types=1);
/**
 * PHP Benchmarks
 *
 * Measure router setup, dispatch, template compilation and compiled rendering.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Adapter\LeagueRoute;
use CrazyPHP\Library\Router\Adapter\FastRoute;
use CrazyPHP\Library\Router\Adapter\Symfony;
use CrazyPHP\Library\Router\Adapter\Mezon;
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Interface\CrazyRouter;
use RuntimeException;
use Closure;

# Load the framework and fixtures
require __DIR__."/fixtures.php";

/**
 * Measure one discarded warm-up batch and eleven recorded batches.
 *
 * The factory runs before the timer so first-dispatch samples can use fresh routes.
 */
function measure(string $name, int $iterations, Closure $prepare, Closure $verify):array {

    # Collect microseconds per operation for each recorded batch
    $samples = [];

    # Repeat each workload with an independently prepared batch
    for($batch = 0; $batch <= 11; $batch++){

        # Prepare fixtures and obtain the timed operation
        $operation = $prepare();

        # Start the monotonic clock
        $start = hrtime(true);

        # Time only the repeated operation
        for($i = 0; $i < $iterations; $i++)

            # Keep the last observation for verification outside the timed section
            $value = $operation();

        # Compute the mean time per operation in this batch
        $elapsed = (hrtime(true) - $start) / (1000 * $iterations);

        # Reject incorrect or incomplete work even during warm-up
        if(!$verify($value))

            # Report the failing scenario
            throw new RuntimeException("Unexpected result for ".$name);

        # Discard the first batch
        if($batch > 0)

            # Record the verified sample
            $samples[] = $elapsed;

    }

    # Order samples for the median and range
    sort($samples, SORT_NUMERIC);

    # Preserve measurement units and sample counts for comparisons
    $result = [
        "scenario"       =>  $name,
        "medianUs"       =>  $samples[intdiv(count($samples), 2)],
        "minUs"          =>  min($samples),
        "maxUs"          =>  max($samples),
        "batches"        =>  count($samples),
        "opsPerBatch"    =>  $iterations
    ];

    # Return the measured row
    return $result;

}

/** Construct one adapter with the complete route collection. */
function registeredRouter(string $class):CrazyRouter {

    # Instantiate this engine without loading application configuration
    $result = new $class();

    # Add the shared routes
    registerRoutes($result);

    # Return the registered adapter
    return $result;

}

# Reject unexpected CLI options
if(array_diff(array_slice($argv, 1), [
    "--json"
]))

    # Explain the supported invocation
    throw new RuntimeException("Usage: composer bench [-- --json]");

# Set the request method used by each adapter
$_SERVER["REQUEST_METHOD"] = "GET";

# Collect results in execution order
$rows = [];

# Select the two route shapes and their expected bodies
$paths = [
    "/ping"        =>  "ok",
    "/users/42"    =>  '{"id":42}'
];

# Exercise all supported adapters on the same collection
foreach([
    "Mezon"         =>  Mezon::class,
    "FastRoute"     =>  FastRoute::class,
    "LeagueRoute"   =>  LeagueRoute::class,
    "Symfony"       =>  Symfony::class
] as $name => $class){

    # Include construction and registration in this baseline
    $rows[] = measure(
        "$name setup (3 routes)",
        1,
        static fn() => static fn() => registeredRouter($class),
        static fn(CrazyRouter $router):bool => $router->callRoute("/users/42") === $paths["/users/42"]
    );

    # Exercise static and dynamic dispatch separately
    foreach($paths as $path => $expected){

        # Verify the controller result after each batch
        $verify = static fn(mixed $value):bool => $value === $expected;

        # Build a new router outside the timer for every first-dispatch sample
        $rows[] = measure("$name first $path", 1, static function() use ($class, $path):Closure {

            # Set up routes without exercising the matcher
            $router = registeredRouter($class);

            # Capture the first request as the timed operation
            $result = static fn() => $router->callRoute($path);

            # Return the operation
            return $result;

        }, $verify);

        # Prime the actual dispatch path before measuring repeated requests
        $router = registeredRouter($class);

        # Populate lazy matching tables
        $router->callRoute($path);

        # Measure steady-state routing
        $rows[] = measure("$name warm $path", 500, static fn() => static fn() => $router->callRoute($path), $verify);

    }

}

# Read template input once outside the timer
$source = file_get_contents(__DIR__."/catalog.hbs");

# Compile with the framework preset and the fixture partial
$compile = static fn():string => Handlebars::compile($source, Handlebars::CRAZY_PRESET + [
    "partials"    =>  PARTIALS
]);

# Prepare the render context
$data = catalogData();

# Build the expected HTML independently of the template
$expectedHtml = "<ul>".implode("", array_map(static fn(int $i):string => "<li>Item $i</li>", $data["items"]))."</ul>\n";

# Measure compilation and validate the resulting renderer outside the timer
$rows[] = measure("Handlebars compile", 10, static fn() => $compile, static fn(string $php):bool => (eval($php))($data) === $expectedHtml);

# Prepare the callable from trusted, locally compiled fixture source
$render = eval($compile());

# Measure rendering with the compiled template and its partial
$rows[] = measure("Handlebars render (100 items)", 100, static fn() => static fn() => $render($data), static fn($html):bool => $html === $expectedHtml);

# Select the requested output format
if(in_array("--json", $argv, true)){

    # Print a machine-readable report
    echo json_encode([
        "php"            =>  PHP_VERSION,
        "routeCount"     =>  3,
        "catalogItems"   =>  count($data["items"]),
        "results"        =>  $rows
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\n";

}else{

    # Identify the runtime and table units
    printf("PHP %s\n%-32s %11s %11s %11s %8s %10s\n", PHP_VERSION, "Scenario", "Median µs", "Min µs", "Max µs", "Batches", "Ops/batch");

    # Present each operation on its own line
    foreach($rows as $row)

        # Format batch averages without rounding the JSON data
        printf("%-32s %10.3f %10.3f %10.3f %8d %10d\n", ...array_values($row));

}
