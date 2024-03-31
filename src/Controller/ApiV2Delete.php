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
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;

/**
 * Api V2 Delete
 *
 * Delete item of entity
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ApiV2Delete extends Controller {
    
    /**
     * delete
     * 
     * @return void
     */
    public static function delete():void {

        # Get id
        $id = (string) self::getParametersUrl("id");

        # Declare content
        $content = self::Model()
            ->deleteById($id)
        ;

        # Set response
        self::ApiResponse()
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}