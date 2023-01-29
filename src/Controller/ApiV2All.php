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
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;
use CrazyPHP\Model\Context;
use CrazyPHP\Core\Model;

/**
 * Api V2 List
 *
 * Return all items of entity
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ApiV2All extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get():void {

        # Check entity given by user
        Model::checkEntityInContext();

        # Declare content
        $content = [];

        # Get last modified date of model config
        $lastModified = FileConfig::getLastModified("Model");

        # Get all model available
        $modelConfig = FileConfig::get("Model");
        
        # Iteration of model
        foreach($modelConfig["Model"] as $model){

            # Check if current model is good
            if(strtolower(Context::getParameters("entity")) == strtolower($model["name"]))

                # Set content
                $content = $model;


        }

        # Set response
        (new ApiResponse())
            ->addLastModified($lastModified)
            ->setStatusCode()
            ->pushContent("results", $content)
            ->pushContext()
            ->send();

    }

}