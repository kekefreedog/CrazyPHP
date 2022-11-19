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
namespace App\Controller\App;

/**
 * Dependances
 */
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;

use CrazyPHP\Library\Html\Structure;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Home extends Controller {

    /**
     * Get
     */
    public static function get($request){

        # Set structure
        $structure = (new Structure())
            ->setDoctype()
            ->setLanguage()
            ->setHead()
            ->setBodyContent("<pre>".var_export($GLOBALS, true)."</pre>")
            ->setJsScripts()
            ->prepare()
            ->render()
        ;


        # Set response
        (new Response())
            ->setContent($structure)
            ->send();

    }

}