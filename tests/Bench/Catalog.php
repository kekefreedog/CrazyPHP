<?php declare(strict_types=1);
/**
 * Catalog Benchmark Controller
 *
 * Render the shared list through Handlebars.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Bench;

/**
 * Dependances
 */
use CrazyPHP\Library\Template\Handlebars;

/**
 * Catalog
 *
 * Handle the corresponding CrazyPHP HTTP benchmark route.
 */
final class Catalog {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     *
     * Render the shared list through Handlebars.
     *
     * @return void
     */
    public static function get():void {

        # Select the HTML media type
        header("Content-Type: text/html; charset=utf-8");

        # Configure the item partial for the cached template
        $template = new Handlebars([
            "partials"    =>  PARTIALS,
            "helpers"     =>  false
        ]);

        # Load the catalog template
        $template->load(__DIR__."/catalog.hbs", "benchmark");

        # Render a fresh list
        echo $template->render(catalogData());

    }

}
