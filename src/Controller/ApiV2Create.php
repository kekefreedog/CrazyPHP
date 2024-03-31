<?php declare(strict_types=1);
/**
 * Controller
 *
 * Collection of controllers
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Controller;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Model;

/**
 * Api V2 Create
 *
 * Create new item of entity
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ApiV2Create extends Controller {
    
    /**
     * post
     * 
     * @return void
     */
    public static function post():void {

        # Declare content
        $content = self::Model()
            ->create(Controller::getHttpRequestData())
        ;

        # Set response
        self::ApiResponse()
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}