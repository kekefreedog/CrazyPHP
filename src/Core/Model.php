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
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Interface\CrazyModel;
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
class Model implements CrazyModel {

    /** Private parameters
     ******************************************************
     */

    /** @var string $name Name of the current entity called */
    private string $name = ""; 

    /** @var array|null $current Current model */
    private array|null $current = null;

    /** @var CrazyDriverModel $driver */
    private CrazyDriverModel $driver;

    /**
     * Constructor
     */
    public function __construct(string $entity = "") {

        # Set name
        $this->name = $entity ? $entity : get_class();

        # Prepare config of current model
        $this->_prepareModelConfig();

        # Set arguments
        $this->_prepareArguments();

        # Load driver
        $this->_loadDriver();

    }

    /** Public methods | Create
     ******************************************************
     */
    
    /**
     * Create
     * 
     * @param array $data Data with attributes values to use for create item
     * @param ?array $options Optionnal options
     * @return array
     */
    public function create(array $data, ?array $options = null):array {

        # Declare result
        $result = [];

        # Process data
        $process = new Process($data);



        # Return result
        return $result;

    }

    /** Public methods | Id 
     ******************************************************
     */

    /**
     * Read By Id
     * 
     * @param string|int $id Id of the item you want read
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readById(string|int $id, ?array $options = null):array {

        # Declare result
        $result = [];

        # Set schema
        $result = $this->driver
            ->parseId($id)
            ->run()
        ;

        # Return result
        return $result;

    }

    /**
     * Update By Id
     * 
     * @param string|int $id Id of the item you want update
     * @param array $data Data with attributes values to use for update
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateById(string|int $id, array $data, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Delete By Id
     * 
     * @param string|int $id Id of the item you want delete
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteById(string|int $id, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }


    /** Public methods | Filters 
     ******************************************************
     */

    /**
     * Read With Filters
     * 
     * @param array $filters Filters to use for read items
     * @param array $sort Options to use for sort items read
     * @param array $sort Options to use for group items read
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readWithFilters(array $filters, ?array $sort = null, ?array $group = null, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Update With Filters
     * 
     * @param array $data Data with attributes values to use for update
     * @param array $filters Filters to use for read items
     * @param array $sort Options to use for sort items read
     * @param array $sort Options to use for group items read
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateWithFilters(array $data, array $filters, ?array $sort = null, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Delete With Filters
     * 
     * @param array $filters Filters to use for read items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteWithFilters(array $filters, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }


    /** Public methods | Sql 
     ******************************************************
     */

    /**
     * Create With Sql
     * 
     * @param string $sql Sql query to use for create item
     * @param ?array $options Optionnal options
     * @return array
     */
    public function createWithSql(string $sql, array $data, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Read With Sql
     * 
     * @param string $sql Sql query to use for read items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function readWithSql(string $sql, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Update With Sql
     * 
     * @param string $sql Sql query to use for update items
     * @param array $data Data with attributes values to use for update
     * @param ?array $options Optionnal options
     * @return array
     */
    public function updateWithSql(string $sql, array $data, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Delete With Sql
     * 
     * @param string $sql Sql query to use for delete items
     * @param ?array $options Optionnal options
     * @return array
     */
    public function deleteWithSql(string $sql, ?array $options = null):array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /** Private Utilities
     ******************************************************
     */

    /**
     * Get Current
     * 
     * Get Current Model
     * 
     * @return array|null
     */
    public function getCurrent():array|null {

        # Set result
        $result = $this->current;

        # Return result
        return $result;

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
        $modelMatching = Arrays::filterByKey($modelConfig["Model"], "name", $this->name);

        # Check model matching
        if(empty($modelMatching)){
            
            # New error
            throw new CrazyException(
                "No modal schema found for current ", 
                404,
                [
                    "custom_code"   =>  "model-001",
                ]
            );

        }

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
        if(!isset($this->current["attributes"]) && empty($this->current["attributes"]))
                
            # New error
            throw new CrazyException(
                "No attributes found in current \"".$this->current["name"]."\" modal schema", 
                404,
                [
                    "custom_code"   =>  "model-002",
                ]
            );

    }

    /**
     * Load driver
     * 
     * @return void
     */
    private function _loadDriver():void {

        # Check driver name is set
        if(
            isset($this->current["driver"]["name"]) && 
            $this->current["driver"]["name"]
        ){

            # Set className
            $className = "CrazyPHP\\Driver\\Model\\".$this->current["driver"]["name"];

            # Set appClassName
            $appClassName = "App\\Driver\\Model\\".$this->current["driver"]["name"];

            # Set arguments
            $arguments = $this->current["driver"]["arguments"] ?? [];

            # Push schema schema in arguments
            $arguments["attributes"] = $this->current["attributes"];

            # Check driver class exists in Driver Model
            if(class_exists($className))

                # Set driver
                $this->driver = new $className(...$arguments);

            else
            # Check app driver class exists
            if(class_exists($appClassName))

                # Set driver
                $this->driver = new $appClassName(...$arguments);

        }

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Check Entity in Entity
     * 
     * @return Model return controller of the current model found
     */
    public static function checkEntityInContext():Model {

        # Declare result with model namespace

        $result = get_called_class();

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

        # Get key of current model
        $key = array_search(strtolower($parameterEntity), $models);

        # Check parameterEntity is in the models collection
        if($key === false)
                
            # New error
            throw new CrazyException(
                "\"$parameterEntity\" isn't supported by the current api", 
                500,
                [
                    "custom_code"   =>  "model-004",
                ]
            );

        # Check if script associate to the model
        if(isset($modelConfig["Model"][$key]["script"]) && $modelConfig["Model"][$key]["script"])

            # Check class
            if(!class_exists($modelConfig["Model"][$key]["script"]))
                
                # New error
                throw new CrazyException(
                    "Script associated to \"$parameterEntity\" doesn't exists...", 
                    500,
                    [
                        "custom_code"   =>  "model-004",
                    ]
                );

            else

                # Set result
                $result = $modelConfig["Model"][$key]["script"];

        # Return called result
        return new $result($modelConfig["Model"][$key]["name"]);

    }

}