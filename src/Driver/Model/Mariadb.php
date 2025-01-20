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
use CrazyPHP\Library\Database\Operation\SqlOperation;
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Model\Schema;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Core\Model;

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

    /** @var string|int|null $id for select one item */
    private string|int|null $id = null;

    /** @var string|null $rawQuery for select one item */
    private string|null $rawQuery = null;

    /** @var bool $attributesAsValues Indicate if attributes is set as values in current schema */
    # private bool $attributesAsValues = false;

    /** @var array|null $field to retrieve */
    private $_fields = null;

    /** @var bool $_delete */
    private $_delete = false;

    /** @var null|array conditions */
    private array|null $conditions = null;

    /** @var bool conditions */
    private bool $optionAlreadyLoaded = false;

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

        # Ingest options
        $this->_ingestOptions($options);

        # Store id
        $this->id = is_int($id) 
            ? $id 
            :intval($id)
        ;

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

        # Ingest options
        $this->_ingestOptions($options);

        # Check filters
        if(isset($filters) && is_array($filters)){

            # Process Operations In Filters
            $filters = $this->_processOperationsInFilters($filters);

            # Push filters in filters
            $this->conditions = $filters;

        }

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

        # Ingest options
        $this->_ingestOptions($options);

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

        # Ingest options
        $this->_ingestOptions($options);

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

        # Ingest options
        $this->_ingestOptions($options);

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

    /** Public methods | Ingest options
     ******************************************************
     */

    /**
     * Ingest Options
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestOptions(?array $options = null):void {

        # Check optionAlreadyLoaded
        if(!$this->optionAlreadyLoaded){

            # Ingest extended
            $this->_ingestExtended($options);

            # Ingest fields
            $this->_ingestFields($options);

            # Ingest page state process
            $this->_ingestPageStateProcess($options);

            # Ingest sql prefix
            $this->_ingestSqlPrefix($options);

            # Ingest unflatten
            $this->_ingestUnflatten($options);

            # Ingest load reference
            $this->_ingestLoadReference($options);

            # Set option already loaded
            $this->optionAlreadyLoaded = true;

        }

    }

    /**
     * Ingest pageStateProcess
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestPageStateProcess(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["pageStateProcess"]) && Process::bool($options["pageStateProcess"]) == true)

            # Switch value in arguments
            $this->arguments["pageStateProcess"] = true;

    }

    /**
     * Ingest sql prefix
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestExtended(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["extended"]) && Process::bool($options["extended"]) == true){

            # Switch value in arguments
            $this->arguments["extended"] = true;

            # Switch value in arguments
            $this->arguments["unflatten"] = true;

            # Switch value in arguments
            $this->arguments["loadReference"] = true;

            # Switch value in arguments
            $this->arguments["sqlPrefix"] = true;

        }

    }

    /**
     * Ingest sql prefix
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestSqlPrefix(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["sqlPrefix"]) && Process::bool($options["sqlPrefix"]) == true)

            # Switch value in arguments
            $this->arguments["sqlPrefix"] = true;

    }

    /**
     * Ingest sql prefix
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestUnflatten(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["unflatten"]) && Process::bool($options["unflatten"]) == true)

            # Switch value in arguments
            $this->arguments["unflatten"] = true;

    }

    /**
     * Ingest load reference
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestLoadReference(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["loadReference"]) && Process::bool($options["loadReference"]) == true)

            # Switch value in arguments
            $this->arguments["loadReference"] = true;

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

        # Swith delete
        $this->_delete = true;

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
        # Delete item by id
        if($this->_delete && $this->id !== null){
            
            # Insert
            $result[] = $this->mariadb->deleteOneToCollection($this->arguments["table"], $this->id);

        }else
        # Insert to maria db Check schema
        if($this->schema !== null && $this->id !== null){

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
                $result[] = $this->mariadb->updateOneToTable($this->arguments["table"], $data, $this->id);

            }

        }else
        # Insert to mongo Check schema
        if($this->id !== null){

            # Set result
            $result = $this->mariadb->find($this->arguments["table"], "", [
                "filters" =>  [
                    "id"    =>  " = ".$this->id
                ]
            ]);
            
        }else
        # Insert to mariadb Check schema
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
                foreach($v as $item){

                    # Check if type if date of dateime and value is today()
                    if(
                        (
                            $item["type"] === "DATE" || 
                            $item["type"] === "DATE"
                        )
                    ){

                        # Check if value is today
                        if($item["value"] == "today()"){

                            # Set current timestamp
                            $data[$item["name"]] = date("Y-m-d H:i:s");

                        }else
                        # Check if value is yesterday
                        if($item["value"] == "yesterday()"){

                            # Set current timestamp
                            $data[$item["name"]] =  (new DateTime("yesterday"))->format("Y-m-d H:i:s");

                        }else
                        # Check if value is tomorrow
                        if($item["value"] == "tomorrow()"){

                            # Set current timestamp
                            $data[$item["name"]] = (new DateTime("tomorrow"))->format("Y-m-d H:i:s");

                        }else{

                            # Push in data
                            $data[$item["name"]] = $item["value"];

                        }

                    }else

                        # Push in data
                        $data[$item["name"]] = $item["value"];

                }

                # Unflatten result
                $data = Arrays::unflatten($data);

                # Insert
                $result[] = $this->mariadb->insertToTable($this->arguments["table"], $data);

            }

        }else{

            # Check conditions
            if($this->conditions !== null){

                # Set result
                $result = $this->mariadb->find(
                    $this->arguments["table"], 
                    "",
                    [
                        "filters"   =>  $this->conditions
                    ]
                );


            }else{

                # Set result
                $result = $this->mariadb->find($this->arguments["table"]);

            }

            ## Fields filter | end
    
            ## Process For Page State | Start

            # Load reference
            if($this->arguments["loadReference"])

                # Process
                $result = $this->_loadReferenceProcess($result);
    
            # check arguments
            if($this->arguments["sqlPrefix"])
    
                # Check result
                if(!empty($result))
    
                    # Iteration result
                    foreach($result as &$row){

                        # Get id
                        $id = $row["id"] ?? null;
    
                        # Process value
                        $row = $this->_sqlPrefixProcess($row);

                        # Check id
                        if($id)

                            # Push id in row
                            $row["id"] = $id;

                        # Push table
                        $row["entity"] = $this->arguments["table"];

                    }
    
            # check arguments
            if($this->arguments["unflatten"])
    
                # Check result
                if(!empty($result))
    
                    # Iteration result
                    foreach($result as &$row)
    
                    # Set result
                    $row = Arrays::unflatten($row, "_");
    
            # check arguments
            if($this->arguments["pageStateProcess"])
    
                # Process value
                $result = $this->_pageStateProcess($result);
    
            ## Process For Page State | End

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
        $result = count($this->mariadb->find($this->arguments["table"]));

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
     * Load Preference
     * 
     * @param array $input
     * @return array
     */
    public function _loadReferenceProcess(array $input):array {

        # Set result
        $result = $input;

        # Temp cache
        $tempCache = [];

        # Check input
        if(!empty($result)){

            # Get models
            $models = Model::getListAllModelNames();

            # Iteration input
            foreach($result as &$row)

                # Iteration row
                foreach($row as $key => $col){

                    # Check if column as "_id" at the end
                    if($col && strlen($key) > 3 && substr($key, -3) == '_id' && ($entity = ucfirst(str_replace("_id", "", $key))) && in_array($entity, $models)){

                        # Get model
                        $entity = $entity;
                        
                        # Get id
                        $id = $col;

                        # Check if already in cache
                        if(isset($tempCache[$entity][$id])){

                            # Get result temp
                            $resultTemp = $tempCache[$entity][$id];

                        # Get value
                        }else{

                            # Read item
                            $resultTemp = (new Model($entity))->readById($id);

                            # Check if result
                            if(!empty($resultTemp) && is_array($resultTemp[0])){

                                # Set result temp
                                $resultTemp = $resultTemp[0];

                                # Push result in temps 
                                $tempCache[$entity][$id] = $resultTemp;

                            }else

                                # Continue iteration
                                continue;

                        }

                        # Add prefix
                        $resultTemp = Arrays::addPrefixToKeys($resultTemp, $entity);

                        # Remove key
                        unset($row[$key]);

                        # Merge result to row
                        $row += $resultTemp;

                    }

                }

        }

        # Return result
        return $result;

    }

    /**
     * Sql Prefix Process
     * 
     * Process result
     * 
     * @param array $input
     * @return array
     */
    public function _sqlPrefixProcess(array $input):array {

        # Get models
        $models = Model::getListAllModelNames();

        # Set result
        $result = Arrays::addPrefixToKeysWithException($input, $this->arguments["table"], $models, true);

        # Return result
        return $result;

    }

    /**
     * Unflatten Process
     * 
     * Process result
     * 
     * @param array $input
     * @return array
     */
    public function unflattenProcess(array $input):array {

        # Set result
        $result = Arrays::addPrefixToKeys($input, $this->arguments["table"]);

        # Return result
        return $result;

    }

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
        $result = [
            "records"   =>  $input
        ];

        # Prepare metadata
        $result["_metadata"]["records_total"] = count($input);

        # Return result
        return $result;

    }

    /**
     * Process Operations In Filters
     * 
     * @param array $input
     * @return array
     */
    private function _processOperationsInFilters(array $filters = []):array {

        # Set result
        $result = $filters;

        # Check filters
        if(!empty($result)){

            # New operations
            $operation = new SqlOperation();

            # Iteration filters
            foreach($result as &$value){

                # Run operation
                $value = $operation->run($value);

            }

        }

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

    /**
     * Ingest Fields
     * 
     * @param ?array $options
     * @return void
     */
    private function _ingestFields(?array $options = null):void {

        # Check options
        if($options !== null && isset($options["fields"]) && !empty($options["fields"]))

            # check if string
            if(is_string($options["fields"]) && ($this->_fields === null || !in_array($options["fields"], $this->_fields)))

                # Push in fileds
                $this->_fields[] = $options["fields"];

            else
            # Check if array
            if(is_array($options["fields"]))

                # Iteration values
                foreach($options["fields"] as $field)

                    # If not already on fields
                    if(!in_array($field, $this->_fields))

                        # Push in fields
                        $this->_fields[] = $field;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array */
    public const ARGUMENTS = [
        "table"        =>  "",
        "schema"            =>  [],
        "database"          =>  [],
        "pageStateProcess"  =>  false,
        "sqlPrefix"         =>  false,
        "unflatten"         =>  false,
        "loadReference"     =>  false,
        "extended"          =>  false, # Apply sqlPrefix , loadReference , unflatten
    ];

}