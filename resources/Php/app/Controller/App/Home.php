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
namespace App\Controller\App;

/**
 * Dependances
 */
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;


 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Home extends Controller {

    /** @const string TEMPLATE */
    public const TEMPLATE = "@app_root/app/Environment/Page/Home/template.hbs";

    /**
     * Get
     */
    public static function get($request){

        # Set structure
        $structure = (new Structure())
            ->setDoctype()
            ->setLanguage()
            ->setHead()
            ->setJsScripts()
            ->setBodyTemplate(self::TEMPLATE)
            ->prepare()
            ->render()
        ;

        # Set response
        (new Response())
            ->setContent($structure)
            ->send();

    }

}