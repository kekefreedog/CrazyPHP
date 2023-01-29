<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Context;

/**
 * Model
 *
 * Class for manage model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Model {

    /** Private parameters
     ******************************************************
     */

    /** @var array|null $current Current model */
    private array|null $current = null;

    /**
     * Constructor
     */
    public function __construct() {

        # Prepare config of current model
        $this->_prepareModelConfig();

        # Set arguments
        $this->_prepareArguments();

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Prepare Model Config
     * 
     * @return void
     */
    private function _prepareModelConfig():void {

        # Open Model Config
        $modelConfig = Config::get("Model");

        # Check current class model config exists
        $modelMatching = Arrays::filterByKey($modelConfig["Model"], "name", get_class());

        # Check model matching
        if(empty($modelMatching))
            
            # New error
            throw new CrazyException(
                "No modal schema found for current ", 
                404,
                [
                    "custom_code"   =>  "model-001",
                ]
            );

        # Set current model
        $this->current = array_pop($modelMatching);

    }

    /**
     * Prepare Arguments of model
     * 
     * @return void
     */
    private function _prepareArguments():void {

        # Check current model.attributes
        if(isset($this->current["attributes"]) && isset($this->current["attributes"]))
                
            # New error
            throw new CrazyException(
                "No attributes found in current \"".$this->current["name"]."\" modal schema", 
                404,
                [
                    "custom_code"   =>  "model-002",
                ]
            );

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Check Entity in Entity
     * 
     * @return bool
     */
    public static function checkEntityInContext():void {

        # Get parameters in context
        $parameterEntity = Context::getParameters("entity");

        # Check if result null
        if($parameterEntity === null)
                
            # New error
            throw new CrazyException(
                "Entity is missing in attribues...", 
                500,
                [
                    "custom_code"   =>  "model-003",
                ]
            );

        # Open Model Config
        $modelConfig = Config::get("Model");

        # Filter models
        $models = array_column($modelConfig["Model"], "name");

        # Change case of models
        $models = array_map('strtolower', $models);

        # Check parameterEntity is in the models collection
        if(!in_array(strtolower($parameterEntity), $models))
                
            # New error
            throw new CrazyException(
                "\"$parameterEntity\" isn't supported by the current api", 
                500,
                [
                    "custom_code"   =>  "model-004",
                ]
            );

    }

}