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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Driver\Model;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Database\Driver\Mangodb;
use CrazyPHP\Interface\CrazyDriverModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Model\Schema;
use CrazyPHP\Library\Form\Query;
use CrazyPHP\Model\Context;
use MongoDB\Client;

/**
 * Config
 *
 * Class for drive a model of type config
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Mongo implements CrazyDriverModel {

    /** Private parameters
     ******************************************************
     */

    /** @var array $arguments */
    private array $arguments;

    /** @var Mongodb $mongodb */
    private Mangodb $mongodb;

    /** @var Schema $schema */
    private Schema|null $schema = null;

    /** @var string|null $id for select one item */
    private string|null $id = null;

    /** @var book $delete */
    private bool $delete = false;

    /** @var array find options */
    private $findOptions = [];

    /** Private parameters
     ******************************************************
     */

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

        # Mongo connection
        $this->newMongodb();

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

        # Set id
        $this->id = $id;

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

        # Check limit in options
        if($options["limit"] ?? false && is_numeric($options["limit"])){

            # Set in options
            $this->findOptions["limit"] = $options["limit"];

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

        # Check sort
        if($sort == "ASC"){

            # Set sort
            $this->findOptions["sort"]["_id"] = 1;

        }else
        # Check desc
        if($sort == "DESC"){

            # Set sort
            $this->findOptions["sort"]["_id"] = -1;

        }

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

        # New schema
        $schema = new Schema($this->arguments["schema"], [$data]);

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

        # Set remove
        $this->delete = true;

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

        # Check delete with id
        if($this->id && $this->delete && $this->schema === null){

            # Get result
            $result[] = $this->mongodb->deleteOneToCollection($this->arguments["collection"], $this->id, $this->arguments["database"]);

        }else
        # Update with id
        if($this->schema !== null && $this->id !== null){

            # Check collection
            $schemaResult = $this->schema->getResult();

            # Check result
            if(!empty($schemaResult)){

                # Iteration
                foreach($schemaResult as $v){

                    # Declare data
                    $data = [];

                    # Iteration v
                    foreach($v as $item)

                        # Push in data
                        $data[$item["name"]] = $item["value"];

                    # Get validator
                    $validator = Mangodb::convertToMongoSchema($this->arguments["schema"]);

                    # Get result
                    $result[] = $this->mongodb->updateOneToCollection($this->arguments["collection"], $data, $this->id, $this->arguments["database"], $validator);

                }

            }

        }else
        # Insert to mongo Check schema
        if($this->schema !== null){

            # Check collection
            $schemaResult = $this->schema->getResult();

            # Check result
            if(!empty($schemaResult)){

                # Iteration
                foreach($schemaResult as $v){

                    # Declare data
                    $data = [];

                    # Iteration v
                    foreach($v as $item)

                        # Push in data
                        $data[$item["name"]] = $item["value"];

                    # Get validator
                    $validator = Mangodb::convertToMongoSchema($this->arguments["schema"]);

                    # Get result
                    $result[] = $this->mongodb->insertToCollection($this->arguments["collection"], $data, $this->arguments["database"], true, $validator);

                }

            }
        
        }else{

            # Find
            $result = $this->mongodb->find($this->arguments["collection"], $this->arguments["database"], $this->findOptions);

        }

        # Return self
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

        # Return self
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

        # Check if collection
        if(isset($this->arguments["collection"]) && $this->arguments["collection"]){

            # Get Model Config
            $models = FileConfig::getValue("Model");

            # Search collection
            $search = Arrays::filterByKey($models, "name", $this->arguments["collection"]);

            # Check search
            if(!empty($search))

                # Get schema
                $this->arguments["schema"] = ($search[array_key_first($search)]["attributes"]) ?? [];

        }

        # Get database
        $databases = FileConfig::getValue("Database.collection.mongodb.database");

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
    private function newMongodb():void {

        # Get mongo db config
        $this->mongodb = new Mangodb();

        # New client
        $this->mongodb->newClient();

    }

    /** Public constants
     ******************************************************
     */

    /** @const array */
    public const ARGUMENTS = [
        "collection"    =>  "",
        "schema"        =>  [],
        "database"      =>  []
    ];
    
}
