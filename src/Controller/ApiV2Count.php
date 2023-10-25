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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Controller;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Model;

/**
 * Api V2 Count
 *
 * Return number of items of entity
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class ApiV2Count extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get():void {

        # Check entity given by user
        Model::checkEntityInContext();

        # New model
        $model = new Model();

        # Get data
        $data = self::getHttpRequestData();

        # Set filters
        $filters = $data ?? null;

        # Set options
        $options = [];
        if(isset($data["summary_fields"])) $options["summary_fields"] = $data["summary_fields"];
        if(isset($data["grouping"])) $options["grouping"] = $data["grouping"];

        # Declare content
        $content = $model->countWithFilters(
            $data["filters"] ?? null,
            !empty($options) ? $options : null
        );

        # Get last modified date of model config
        $lastModified = FileConfig::getLastModified("Model");

        # Set response
        (new ApiResponse())
            ->addLastModified($lastModified)
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}