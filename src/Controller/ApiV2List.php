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
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Core\ApiResponse;
use CrazyPHP\Core\Controller;

/**
 * Api V2 List
 *
 * List all entities available
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ApiV2List extends Controller {
    
    /**
     * get
     * 
     * @return void
     */
    public static function get():void {

        # Get last modified date of model config
        $lastModified = FileConfig::getLastModified("Model");

        # Get all model available
        $modelConfig = FileConfig::getValue("Model");

        # Check model config
        if($modelConfig === null || empty($modelConfig))

            # Set content
            $content = [];

        # If model config model valid
        else{

            # Clean columns of content
            Arrays::removeColumn($modelConfig, []);

            # Clean attributes
            array_walk(
                $modelConfig,
                function(&$v){
                    if(isset($v['attributes']) && !empty($v["attributes"]))
                        $v["attributes"] = array_column($v["attributes"], "name");
                }
            );

            # Set content
            $content = $modelConfig;

        }

        # Set response
        (new ApiResponse())
            ->addLastModified($lastModified)
            ->setStatusCode()
            ->pushContent("results", $content)
            ->send();

    }

}