<?php declare(strict_types=1);
/**
 * Laravel Benchmark Routes
 *
 * Install in the isolated Laravel fixture's routes/web.php.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
use Illuminate\Support\Facades\Route;

# Send the plaintext workload
Route::get("/ping", static fn() => response("ok")->header("Content-Type", "text/plain"));

# Serialize the identifier captured by the router
Route::get("/users/{id}", static fn(string $id) => response()->json([
    "id"    =>  (int)$id
]))->whereNumber("id");

# Render a fresh list through Blade with a partial per item
Route::get("/catalog", static fn() => view("catalog", [
    "items"    =>  range(1, 100)
]));
