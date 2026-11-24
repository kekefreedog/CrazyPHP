<?php declare(strict_types=1);
/**
 * Ping Benchmark Controller
 *
 * Send the fixed plaintext response.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */

/**
 * Ping
 *
 * Handle the corresponding CrazyPHP HTTP benchmark route.
 */
final class Ping {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     *
     * Send the fixed plaintext response.
     *
     * @return void
     */
    public static function get():void {

        # Select the plaintext media type
        header("Content-Type: text/plain");

        # Emit the fixture body
        echo "ok";

    }

}
