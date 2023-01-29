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

/**
 * Api V2 Filter
 *
 * Return all filtered items of entity
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ApiV2Filter extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get():void {

        # Check entity given by user
        $entityModel = Model::checkEntityInContext();

        # Set content
        $content = [get_class($entityModel)];

        # Set response
        (new ApiResponse())
            ->setStatusCode()
            ->pushContent("results", $content)
            ->pushContext()
            ->send();

    }

}