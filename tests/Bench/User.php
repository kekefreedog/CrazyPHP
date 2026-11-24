<?php declare(strict_types=1);
/**
 * User Benchmark Controller
 *
 * Serialize the identifier extracted by the router.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */
use CrazyPHP\Model\Context;

/**
 * User
 *
 * Handle the corresponding CrazyPHP HTTP benchmark route.
 */
final class User {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     *
     * Serialize the identifier extracted by the router.
     *
     * @return void
     */
    public static function get():void {

        # Select the JSON media type
        header("Content-Type: application/json");

        # Encode the numeric route parameter
        echo json_encode([
            "id"    =>  (int)Context::getParameters("id")
        ], JSON_THROW_ON_ERROR);

    }

}
