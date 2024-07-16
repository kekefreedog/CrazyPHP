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
use CrazyPHP\Library\Database\Driver\Mariadb as MariadbModel;
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Model\Schema;
use CrazyPHP\Library\Array\Arrays;

/* use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Parser; */

/**
 * Crazy Driver Model Interface
 * 
 * Interface for define compatible class with Driver Model (based on mongo or other model driver...)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Mariadb implements CrazyDriverModel {

    /** Private parameters
     ******************************************************
     */

    /** @var array $arguments */
    private array $arguments;

    /** @var Mysql Instance */
    public MariadbModel $mariadb;

    /** @var Schema $schema */
    private Schema|null $schema = null;

    /** @var string|null $id for select one item */
    private string|null $id = null;

    /** @var string|null $rawQuery for select one item */
    private string|null $rawQuery = null;

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
        $this->newMariadb();

        # Create table
        $this->createTable();

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

        # Create table
        $this->mariadb->createTable($this->arguments["table"], $this->arguments["schema"]);

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

        # Raw query
        $this->rawQuery = $sql;

        /* # New parser
        $parser = new Parser($sql);

        # Iteration 
        foreach ($parser->statements as $statement) {
            if ($statement instanceof SelectStatement) {
                // The $statement variable now holds an object representation of the SQL query
                var_dump($statement);
            }
        } */

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

        # New schema
        $schema = new Schema($this->arguments["schema"], [$data], [
            "flatten"           =>  true,
            "skipEmptyValue"    =>  $this->isUpdate()
        ]);

        # Push schema in classe schema
        $this->schema = $schema;

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

        # Check raw query
        if($this->rawQuery !== null){

            # Set result
            $result = $this->mariadb->find($this->arguments["table"], "", [
                "query" =>  $this->rawQuery
            ]);

        }else
        # Insert to mongo Check schema
        if($this->schema !== null){

            # Check collection
            $schemaResult = $this->schema->getResult([
                "skipAttributes"    =>  ["id"]
            ]);

            # Iteration
            foreach($schemaResult as $v){

                # Declare data
                $data = [];

                # Iteration v
                foreach($v as $item)

                    # Push in data
                    $data[$item["name"]] = $item["value"];

                # Unflatten result
                $data = Arrays::unflatten($data);

                # Insert
                $result[] = $this->mariadb->insertToTable($this->arguments["table"], $data);

            }

        }

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



        # Check if collection
        if(isset($this->arguments["table"]) && $this->arguments["table"]){

            # Get Model Config
            $models = FileConfig::getValue("Model");

            # Search table
            $search = Arrays::filterByKey($models, "name", $this->arguments["table"]);

            # Check search
            if(!empty($search))

                # Get schema
                $this->arguments["schema"] = ($search[array_key_first($search)]["attributes"]) ?? [];

        }

        # Get database
        $databases = FileConfig::getValue("Database.collection.mariadb.database");

        # If empty database
        if(empty($databases))
            
            # New error
            throw new CrazyException(
                "No mongodb database defined in config.", 
                500,
                [
                    "custom_code"   =>  "model-mongodb-001",
                ]
            );

        # Set database
        $this->arguments["database"] = $databases[array_key_first($databases)];

    }

    /**
     * New Client
     * 
     * New client connection to mongo db
     * 
     * @return void
     */
    private function newMariadb():void {

        # Get mongo db config
        $this->mariadb = new MariadbModel();

        # New client
        $this->mariadb->newClient();

    }

    /** Private methods | Options
     ******************************************************
     */

    /**
     * Is Update
     * 
     * @return bool
     */
    private function isUpdate():bool {

        return $this->id !== null;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array */
    public const ARGUMENTS = [
        "table"        =>  "",
        "schema"            =>  [],
        "database"          =>  [],
        "pageStateProcess" =>  false,
    ];

}