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
use CrazyPHP\Core\Media\Favicon as MediaFavion;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;
use CrazyPHP\Core\File;

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

    public static function get($request){

        # Get parameter
        $faviconName = self::getParametersUrl(MediaFavion::PARAMETER_NAME);

        # Prepare favion media
        $favicon = new MediaFavion($faviconName ?: "favicon");

        # Set response
        (new Response())
            ->setContent($favicon)
            ->allowCache("public", 604800, true)
            ->send();

    }

    /** Public constants
     ******************************************************
     */

    public const PATH = "@app_root/assets/Favicon";

}