<?php declare(strict_types=1);
/**
 * Driver
 *
 * Drivers of your CrazyPHP App
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Driver\Model;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Model\Schema;
use CrazyPHP\Model\Context;

/**
 * Config
 *
 * Class for drive a model of type config
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Config implements CrazyDriverModel {

    /** Private parameters
     ******************************************************
     */

    /** @var array $arguments */
    private array $arguments;

    /** @var Schema $schema */
    private Schema $schema;

    /** @var array $arrayData */
    private array $arrayData = [];

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(...$inputs) {

        # Set name
        $this->ingestParameters($inputs);

        # Check config name
        $this->checkNameGiven();

        # Prepare Schema
        $this->prepareSchema();

    }

    /** Public mathods | Attributes
     ******************************************************
     */

    /**
     * Set Attributes As Values
     * 
     * Switch attributes to values
     * 
     * @param bool $summary Return summary of the attributes
     * @return self
     */
    public function setAttributesAsValues(bool $summary = false):self {

        # Spread action to schema
        $this->schema->setAttributesAsValues();

        # Return self
        return $this;


    }

    /** Public methods
     ******************************************************
     */

    /**
     * Parse Id
     * 
     * @param string|int $id ID to parse
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseId(string|int $id, ?array $options = null):self {
                
        # New error
        throw new CrazyException(
            "Get Id isn't supported for your request", 
            500,
            [
                "custom_code"   =>  "model-003",
            ]
        );

        # Return self
        return $this;

    }

    /**
     * Parse Filters
     * 
     * @param array $filters Filter to process
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseFilter(?array $filters, ?array $options = null):self {

        # Return self
        return $this;

    } 

    /**
     * Parse Sort
     * 
     * @param null|array|string $sort Sort to process
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseSort(null|array|string $sort, ?array $options = null):self {

        # Return self
        return $this;

    }

    /**
     * Parse Group
     * 
     * @param array $group Group to process
     * @param ?array $options Optionnal options
     */
    public function parseGroup(?array $group, ?array $options = null):self {

        # Return self
        return $this;

    }

    /**
     * Parse Sql
     * 
     * @param string $sql Sql query
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseSql(string $sql, ?array $options = null):self {

        # Return self
        return $this;
    
    }

    /** Public methods | Ingester
     ******************************************************
     */

    /**
     * Ingest Data
     * 
     * Import data in current driver
     * 
     * @param array $data
     * @param ?array $options Optionnal options
     * @return self
     */
    public function ingestData(array $data, ?array $options = null):self {

        # Return self
        return $this;

    }

    /** Public methods | Pusher
     ******************************************************
     */

    /**
     * Push to trash
     * 
     * Put to trash current value
     * 
     * @param ?array $options Optionnal options
     * @param 
     */
    public function pushToTrash(?array $options = null):self {

        # Return self
        return $this;
    }

    /** Public methods | Execute
     ******************************************************
     */

    /**
     * Run
     * 
     * Return data with given information
     * 
     * @return array
     */
    public function run():array {

        # Check summary
        if($this->arguments["summary"])

            # Set result
            $result = $this->schema->getResultSummary();

        else

            # Set result
            $result = $this->schema->getResult();

        # Return result
        return $result;

    }

    /**
     * Count
     * 
     * Return counted data with given information
     * 
     * @return int
     */
    public function count():int {

        # Set result
        $result = $this->schema->getCount();

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest Parameters
     * 
     * @param array $inputs Inputs of the constructor
     * @return void
     */
    private function ingestParameters(array $inputs):void {

        # Set arguments
        $this->arguments = self::ARGUMENTS;

        # Check inputs
        if(!empty($inputs))

            # Iteration inputs
            foreach($inputs as $name => $value)

                # Check name in arguments
                if(array_key_exists($name, $this->arguments))

                        # Set value
                        $this->arguments[$name] = $value;

        # Check summarize
        if(in_array($_REQUEST["summary"] ?? [], [null, false, "false", 0]))

            # Set options summary
            $this->arguments["summary"] = false;

    }

    /**
     * Check Name Given
     * 
     * @return void
     */
    private function checkNameGiven():void {

        # Check name in arguments is valid
        if(!in_array($this->arguments["name"], self::SUPPORTED))
            
            # New error
            throw new CrazyException(
                "Given config name \"".$this->arguments["name"]."\“ isn't supported by the Config Model Driver...", 
                500,
                [
                    "custom_code"   =>  "driver-model-config-001",
                ]
            );

    }

    /**
     * Prepare Schema
     * 
     * @return void
     */
    private function prepareSchema():void {
        
        # Check attributes
        if(
            !isset($this->arguments["attributes"]) || 
            empty($this->arguments["attributes"])
        )
                
            # New error
            throw new CrazyException(
                "No attributes found in current \"".$this->arguments["name"]."\" modal schema", 
                404,
                [
                    "custom_code"   =>  "driver-model-config-001",
                ]
            );

        # New schema
        $this->schema = new Schema($this->arguments["attributes"]);

        # Get values
        $values = $this->getValues();

        # Set value
        $this->schema->setValues($values);

    }

    /**
     * Get Values
     * 
     * Get values from root given in options (arguments)
     * @return array
     */
    private function getValues():array {

        # Set result
        $result = [];

        # Check arguments root
        if(!$this->arguments["root"] ?? true)
                
            # New error
            throw new CrazyException(
                "Root parameter is missing in current model \"".$this->arguments["name"]."\"", 
                500,
                [
                    "custom_code"   =>  "driver-model-config-002",
                ]
            );

        # Get data
        $data = FileConfig::getValue($this->arguments["root"]);

        # Check data
        if(!$data || empty($data) || $data === null)
                
            # New error
            throw new CrazyException(
                "Data obtain from root \"".$this->arguments["root"]."\" isn't valid for current model \"".$this->arguments["name"]."\"", 
                500,
                [
                    "custom_code"   =>  "driver-model-config-002",
                ]
            );

        # Set result
        $result = $data;

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array Supported Config */
    public const SUPPORTED = [
        "Router"
    ];

    /** @const array */
    public const ARGUMENTS = [
        "name"          =>  "",
        "root"          =>  "",
        "attributes"    =>  [],
        "summary"       =>  true
    ];

}
