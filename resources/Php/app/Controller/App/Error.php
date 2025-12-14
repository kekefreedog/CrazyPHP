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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace App\Controller\App;

/**
 * Dependances
 */
use CrazyPHP\Core\Controller;


 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Error extends Controller {

    /** @const string TEMPLATE */
    public const TEMPLATE = "@app_root/app/Environment/Page/Error/template.hbs";

    /**
     * Get
     * 
     * @param $request
     * @return void
     */
    public static function get($request){

        # Status code
        $statusCode = 404;

        # Set state
        $state = static::State()
            ->setStatusCode($statusCode)
            ->render()
        ;

        # Set structure
        $structure = static::Structure()
            ->setDoctype()
            ->setLanguage()
            ->setHead()
            ->setBodyTemplate(self::TEMPLATE, null, (array) $state)
            ->setJsScripts()
            ->prepare()
            ->render()
        ;

        # Set response
        static::Response()
            ->setStatusCode($statusCode)
            ->setContent($structure)
            ->send();

    }

}