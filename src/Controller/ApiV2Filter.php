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
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Router\Middleware;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Query;
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
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ApiV2Filter extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get($request):void {

        # Check model middleware
        $request = Middleware::runModelMiddleware($request);

        # Check entity given by user
        self::Model()::checkEntityInContext();

        # Filters parameters
        $filtersParameters = Query::getForFilters();

        # Declare content
        $content = self::Model()->readWithFilters(...$filtersParameters);

        # Get last modified date of model config
        $lastModified = FileConfig::getLastModified("Model");

        # Set response
        static::ApiResponse()
            ->addLastModified($lastModified)
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}