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
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Model\Schema;

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

        # Return self
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run
     * 
     * Return data with given information
     * 
     * @param bool $clearOptionsAfter
     * @return array
     */
    public function run(bool $clearOptionsAfter = true):array {

        # Set result
        $result = [];

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
        "name"      =>  "",
        "attributes"=>  []
    ];

}
