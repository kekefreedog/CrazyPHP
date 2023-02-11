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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Controller;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Model;
use CrazyPHP\Model\Context;

/**
 * Api V2 By Id
 *
 * Return item of entity by id
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ApiV2Id extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get():void {

        # Check entity given by user
        Model::checkEntityInContext();

        # New api model
        //$model = new Model();

        # Set content
        $content = [Context::get()];

        # Set response
        (new ApiResponse())
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}