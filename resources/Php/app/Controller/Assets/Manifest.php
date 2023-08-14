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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace App\Controller\Assets;

/**
 * Dependances
 */
use CrazyPHP\Core\Media\Favicon as MediaFavion;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;
use CrazyPHP\Model\Asset;
use CrazyPHP\Core\File;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class manifest extends Controller {

    public static function get($request){

        # Get config
        $config = self::getConfig("App");

        # Get appName
        $appNameTemp = explode("/", $config["App"]["name"], 2);
        $appName = array_pop($appNameTemp);

        # Set data
        $data = [
            "name"              =>  $appName,
            "short_name"        =>  $appName,
            "icons"             =>  [
                [
                    "src"   =>  "asset/favicon/android-chrome-192x192.png",
                    "sizes" =>  "192x192",
                    "type"  =>  "image/png"
                ],
                [
                    "src"   =>  "asset/favicon/android-chrome-512x512.png",
                    "sizes" =>  "512x512",
                    "type"  =>  "image/png"
                ]
            ],
            "theme_color"       =>  "#ffffff",
            "background_color"  =>  "#ffffff",
            "display"           =>  "standalone",
        ];

        # Set response
        (new Response())
            ->setContentType("json")
            ->setContent($data)
            ->allowCache("public", 604800, true)
            ->send();

    }

}