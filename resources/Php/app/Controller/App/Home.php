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
class Home extends Controller {

    /** @const string TEMPLATE */
    public const TEMPLATE = "@app_root/app/Environment/Page/Home/template.hbs";

    /**
     * Get
     * 
     * @param $request
     * @return void
     */
    public static function get($request){
    
        # Render state
        $state = static::State()
            ->pushColorSchema()
            ->render()
        ;

        # Set structure
        $structure = static::Structure()
            ->setDoctype()
            ->setLanguage()
            ->setHead()
            ->setJsScripts()
            ->setBodyTemplate(self::TEMPLATE, null, (array) $state)
            ->prepare()
            ->render()
        ;

        # Set response
        static::Response()
            ->setContent($structure)
            ->send();

    }
    
    /** Public constant
     ******************************************************
     */

}