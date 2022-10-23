<?php declare(strict_types=1);
/**
 * App
 *
 * Workflow of your app
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace App\Controller\Assets;

/**
 * Dependances
 */
use Symfony\Component\Finder\Finder;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;
use CrazyPHP\Core\Media;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Favicon extends Controller {

    public static function get(){

        # New media instance
        $media = new Media();

        # Set response
        (new Response())
            ->setContentType("json")
            ->setContent($content)
            ->send();

    }

}