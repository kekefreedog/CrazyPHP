<?php declare(strict_types=1);
/**
 * CrazyPHP HTTP Benchmark Fixture
 *
 * Serve the shared endpoints through the framework router and template engine.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */
use CrazyPHP\Core\Router;
use CrazyPHP\Model\Env;

# Delegate transport scenarios to the same temporary benchmark server
if(str_starts_with(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/__bench/transport")){

    # Serve the browser page, client module or negotiated API fixture
    require __DIR__."/transport.php";

    # Finish the fixture without dispatching an application route
    return;

}

# Load the framework and shared catalog fixture
require __DIR__."/fixtures.php";

# Use the production file-cache driver under this fixture's own application root
Env::set([
    "app_root"         =>  __DIR__,
    "crazyphp_root"    =>  dirname(__DIR__, 2),
    "cache_driver"     =>  "Files"
]);

# Load the configured routing engine
$router = new Router();

# Register YAML routes through the application collection loader
$router->pushCollection();

# Include the framework middleware and PSR-7 request construction
$router->pushMiddlewares();

# Stop unmatched requests before resolving their route context
$router->setNoProcessorFoundErrorHandler(static function():void {

    # Send the missing-route status
    http_response_code(404);

    # End this request without serving files
    exit;

});

# Resolve the application context and invoke the controller
$router->callRouteExtended();
