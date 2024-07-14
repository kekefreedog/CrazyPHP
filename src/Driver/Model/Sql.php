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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Driver\Model;

/**
 * Dependances
 */
use CrazyPHP\Library\Database\Driver\Mysql;
use CrazyPHP\Interface\CrazyDriverModel;

/**
 * Crazy Driver Model Interface
 * 
 * Interface for define compatible class with Driver Model (based on mongo or other model driver...)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Sql extends CrazyDriverModel {

    /** Private parameters
     ******************************************************
     */

    /** @var Mysql Instance */
    public Mysql $mysql;

    /** @var bool $attributesAsValues Indicate if attributes is set as values in current schema */
    # private bool $attributesAsValues = false;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(...$inputs) {

        # Set name
        $this->ingestParameters($inputs);

        # Sql connection
        $this->newSql();

    }

    /** Public mathods | Attributes
     ******************************************************
     */

    /**
     * Set Attributes As Values
     * 
     * Switch attributes to values
     * 
     * @return self
     */
    public function setAttributesAsValues():self {

        # Return self
        return $this;

    }

    /** Public methods | Collection / Table
     ******************************************************
     */

    /**
     * Create table
     * 
     * @return self
     */
    public function createTable():self {

        # Return self
        return $this;

    }

    /**
     * Create collection
     * 
     * @return self
     */
    public function createCollection():self {

        # Return self
        return $this;

    }

    /** Public methods | Parser
     ******************************************************
     */

    /**
     * Parse Id
     * 
     * @param string|int $id Id to parse
     * @param ?array $options Optionnal options
     * @return self
     */
    public function parseId(string|int $id, ?array $options = null):self {

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

        # Set result
        $result = [];

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
        $result = 0;

        # Return result
        return $result;

    }

    /** Public methods | tests
     ******************************************************
     */

    /**
     * Force Summary
     * 
     * Use for test for force summary argument value
     * 
     * @param null|bool|array $input Summary state
     * @return self
     */
    public function forceSummary(null|bool|array $input = true):self {

        # Return self
        return $this;

    }

    /** Private methods | Process
     ******************************************************
     */

    /**
     * Page State Process
     * 
     * Process result (input) for Page State by adding _metadata info...
     * 
     * @param array $input
     * @return array
     */
    public function _pageStateProcess(array $input):array {

        # Set result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Ingest Parameters
     * 
     * @param array $inputs Inputs of the constructor
     * @return void
     */
    private function ingestParameters(array $inputs):void {

    }

    /**
     * New Client
     * 
     * New client connection to mongo db
     * 
     * @return void
     */
    private function newSql():void {

        # Get mongo db config
        $this->mysql = new Mysql();

        # New client
        $this->mysql->newClient();

    }

}