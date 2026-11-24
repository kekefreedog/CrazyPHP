<?php declare(strict_types=1);
/**
 * Benchmark Fixtures
 *
 * Define the CLI route collection and the catalog shared by CLI and HTTP measurements.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */
use CrazyPHP\Interface\CrazyRouter;

# Resolve the framework used by this checkout
require_once dirname(__DIR__, 2)."/vendor/autoload.php";

/** Item partial used in both compilation and HTTP rendering. */
const PARTIALS = [
    "item"    =>  "<li>Item {{this}}</li>"
];

/** Build the fixed 100-item template context. */
function catalogData():array {

    # Supply numeric items to the template
    $result = [
        "items"    =>  range(1, 100)
    ];

    # Return the context
    return $result;

}

/** Register the same three GET routes on each router. */
function registerRoutes(CrazyRouter $router):void {

    # Return the fixed plaintext body
    $router->addRoute("/ping", static fn():string => "ok");

    # Serialize the matched numeric identifier
    $router->addRoute("/users/[i:id]", static fn(string $path, array $params):string => json_encode([
        "id"    =>  (int)$params["id"]
    ], JSON_THROW_ON_ERROR));

    # Include the catalog path in the registration workload
    $router->addRoute("/catalog", static fn():string => "catalog");

}
