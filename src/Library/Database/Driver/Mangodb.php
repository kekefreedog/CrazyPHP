<?php declare(strict_types=1);
/**
 * Databse Driver
 *
 * Drivers for manage differents database system
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database\Driver;

/**
 * Dependances
 */
use MongoDB\Driver\Exception\CommandException as MongoDbCommandException;
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Cli\Command as CliCommand;
use CrazyPHP\Interface\CrazyDatabaseDriver;
use CrazyPHP\Exception\CrazyException;
use MongoDB\Driver\Manager;
use MongoDB\BSON\ObjectId;
use MongoDB\Database;
use MongoDB\Client;

/**
 * MangoDB
 *
 * Driver for manipulate mangodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Mangodb implements CrazyDatabaseDriver {

    /** Parameters
     ******************************************************
     */

    /**
     * @var $config Config of current database
     */
    public $config = null;

    /**
     * @var $client Client of current database
     */
    public $client = null;

    /**
     * @var $manager Manager of current database
     */
    public $manager = null;

    /**
     * Constructor
     * 
     * 1. Get current database config
     * @return self
     */
    public function __construct(){

        # Get current conffig
        $this->config = FileConfig::getValue(self::CONFIG_KEY);
        
    }

    /** Public Methods | Clients
     ******************************************************
     */

    /**
     * New Client
     * 
     * Define new databse client
     * 
     * @param string|int $user from option in Config > Database
     * @return self
     */
    public function newClient(string|int $user = 0):self{

        # Connection
        $connection = [];

        # Check if root
        if($user === "root"){

            # Set login
            $connection["login"] = $this->config["root"]["login"];

            # Set passord
            $connection["password"] = $this->config["root"]["password"];

        }else
        # Check user
        if($user === "" || !array_key_exists($user, $this->config["users"]))

            # New Exception
            throw new CrazyException(
                "User \"$user\" can't be found...",
                500,
                [
                    "custom_code"   =>  "mongodb-001",
                ]
            );

        else
        # Check if key
        if(isset($this->config["users"][$user]) && !empty($this->config["users"][$user])){

            # Set login
            $connection["login"] = $this->config["users"][$user]["login"];

            # Set passord
            $connection["password"] = $this->config["users"][$user]["password"];

        }else

            # New Exception
            throw new CrazyException(
                "User \"$user\" don't exists...",
                500,
                [
                    "custom_code"   =>  "mongodb-002",
                ]
            );

        # Get host
        $connection["host"] = $this->config["host"];

        # Datanbase name
        $connection["database"] = $user === "root" ?
            "admin" :
                $this->config["database"][0];
        

            # Set database

        # Get port
        $connection["port"] = $this->config["port"];

        # Get connection string
        $connectionString = self::getConnectionString($connection);

        # Set client
        $this->client = new Client($connectionString);

        # Set manager
        $this->manager = new Manager($connectionString);

        # Return self
        return $this;

    }

    /** Public Methods | User
     ******************************************************
     */

    /**
     * Create User
     * 
     * Create new user
     * 
     * @param string $user User name
     * @param string $password Password
     * @param string|array databases Name of database
     * @param string|array $options Options for create user
     * @return self
     */
    public function createUser(string $user = "", string $password = "", string|array $databases = [], string|array $options = []):self{

        # Check client
        if(!$this->manager)

            # New Exception
            throw new CrazyException(
                "Please execute \"newClient\" method before \"".__METHOD__."\" method...",
                500,
                [
                    "custom_code"   =>  "mongodb-003",
                ]
            );

        # Check user
        if(!$user)

            # New Exception
            throw new CrazyException(
                "User parameter is empty...",
                500,
                [
                    "custom_code"   =>  "mongodb-004",
                ]
            );

        # Check databases
        if(empty($databases))

            # Set databases
            $databases = $this->config["database"] ?? ["admin"];

        # Check is not array
        elseif(!is_array($databases))

            # Convert to array
            $databases = [$databases];

        # Prepare command
        $command = [
            "createUser"    =>  $user,
            "pwd"           =>  $password,
            "roles"         =>  $options["roles"] ?? ["readWrite"],
        ];

        # Iteration of databases
        foreach($databases as $database){

            # Select database
            $databaseInstance = $this->client->selectDatabase($database);

            # Try
            try{

                # Create user
                $result = $databaseInstance->command($command);

            }catch(MongoDbCommandException $e){

                # Get message
                $result = $e->getMessage();

            }

            # Check message
            if(is_string($result) && strpos($result, "already exists") === false)

                # New Exception
                throw new CrazyException(
                    "Error when creating user in MongoDB \"$result\"",
                    500,
                    [
                        "custom_code"   =>  "mongodb-005",
                    ]
                );

        }

        # Return self
        return $this;

    }

    /**
     * Create Users From Config
     * 
     * Create users from config
     * 
     * @return self
     */
    public function createUserFromConfig():self {

        # Check client
        if(!$this->client)

            # New Exception
            throw new CrazyException(
                "Please execute \"newClient\" method before \"".__METHOD__."\" method...",
                500,
                [
                    "custom_code"   =>  "mongodb-006",
                ]
            );

        # Set users
        $users = $this->config["users"] ?? null;

        # Check users
        if(!empty($users))

            # Iteration of users
            foreach($users as $user)

                # Create user
                $this->createUser($user["login"], $user["password"]);

        # Return self
        return $this;

    }

    /** Public Methods | Database
     ******************************************************
     */

    /**
     * Create Database
     * 
     * @return void
     */
    public function createDatabase():void {

        # Switch to database
        $database = $this->client->{$this->config["database"][0]};

        # Check if current database has collection
        if(!$this->hasCollection("cache", $database))

            # Create collection
            $database->createCollection("cache");

    }

    /**
     * Get All Databases
     * 
     * Get All Databases in Mongo DB
     * 
     * @return array
     */
    public function getAllDatabases():array {

        # Declare result
        $result = [];

        # Get list of databases
        $result = $this->client->listDatabases();

        # Return result
        return $result;

    }

    /** Public Methods | Collection
     ******************************************************
     */

    /**
     * Get All Collections
     * 
     * Get Collection from database
     * 
     * @param string $database Name of the database (by default the first one in config file)
     * @return array|null
     */
    public function getAllCollections(string|Database $database = ""):array|null {

        # Set result
        $result = null;

        # Check is string
        if(is_string($database)){

            # Check database
            if(!$database)

                # Get main database
                $database = $this->config["database"][0];
                
            # Get database object
            $databaseObject = $this->client->$database;

        }else

            # Set object directly
            $databaseObject = $database;

        # Get database object
        $databaseObject = $this->client->$database;

        # Get collections of current database
        $result = $databaseObject->listCollectionNames();

        # Return result
        return $result;

    }

    /**
     * Has Collection
     * 
     * Check database has collection
     * 
     * @param string $name Name of the collection
     * @param string $database Name of the database (by default the first one in config file)
     * @return array|null
     */
    public function hasCollection(string $collection = "", string|Database $database = ""):bool {

        # Set result
        $result = false;

        # Check collection
        if(!$collection)

            # Return result
            return $result;

        # Check is string
        if(is_string($database)){

            # Check database
            if(!$database)

                # Get main database
                $database = $this->config["database"][0];
                
            # Get database object
            $databaseObject = $this->client->$database;

        }else

            # Set object directly
            $databaseObject = $database;

        # Get collections of current database
        $collections = $databaseObject->listCollectionNames();

        # Check collection
        if(!empty($collections))

            # Iteration of collections
            foreach($collections as $collectionName)

                # Check is equal to current collection
                if($collectionName == $collection){

                    # Update result
                    $result = true;

                    # break loop
                    break;

                }

        # Return result
        return $result;

    }

    /**
     * Insert To Collection
     * 
     * Insert value to collection
     * 
     * @param string $collectionName
     * @param array $value
     * @param string $database
     * @param bool $createIfNotExists
     * @param array $validator
     */
    public function insertToCollection(string $collectionName, array $value, string $database = "", bool $createIfNotExists = false, array $validator = []) {

        # Set result
        $result = null;

        # Check input
        if(!$collectionName || empty($value) || !$database)

            # Return result
            return $result;

        # Set database
        $database = $this->client->$database;

        # Check has
        if(!$this->hasCollection($collectionName, $database)){

            # Check if create
            if(!$createIfNotExists)

                # Stop
                return $result;

            # Set collection
            $database->createCollection($collectionName, $validator);

        }

        # Go in collection
        $collection = $database->$collectionName;

        # Result
        $result = $collection->insertOne($value);

        # Return result
        return $result;

    }

    /**
     * Update One To Collection
     * 
     * Update one value to collection
     * 
     * @param string $collectionName
     * @param array $value
     * @param string $id
     * @param string $database
     * @param array $validator
     */
    public function updateOneToCollection(string $collectionName, array $value, string $id, string $database = "", array $validator = []) {

        # Set result
        $result = null;

        # Check input
        if(!$collectionName || empty($id) || empty($value) || !$database)

            # Return result
            return $result;

        # Set database
        $database = $this->client->$database;

        # Go in collection
        $collection = $database->$collectionName;

        # Prepare id
        $criteria = ['_id' => new ObjectId($id)];

        # Prepare value to set
        $valueToSet = [
            '$set'  =>  $value
        ];

        # Result
        $result = $collection->updateOne($criteria, $valueToSet);

        if($result->isAcknowledged())

            # Set result
            $result = [
                "code"      =>  200,
                "message"   =>  "Item updated"
            ];

        else

            # Set result
            $result = [
                "code"      =>  500,
                "message"   =>  "Item not updated"
            ];

        # Return result
        return $result;

    }

    /**
     * Delete One To Collection
     * 
     * Delete one value to collection
     * 
     * @param string $collectionName
     * @param string $id
     * @param string $database
     */
    public function deleteOneToCollection(string $collectionName, string $id, string $database = "") {

        # Set result
        $result = null;

        # Check input
        if(!$collectionName || !$database)

            # Return result
            return $result;

        # Set database
        $database = $this->client->$database;

        # Go in collection
        $collection = $database->$collectionName;

        # Prepare id
        $criteria = ['_id' => new ObjectId($id)];

        # Result
        $result = $collection->deleteOne($criteria);

        if($result->getDeletedCount() === 1)

            # Set result
            $result = [
                "code"      =>  200,
                "message"   =>  "Item deleted"
            ];

        else

            # Set result
            $result = [
                "code"      =>  500,
                "message"   =>  "Item not deleted"
            ];

        # Return result
        return $result;

    }

    /**
     * Find
     * 
     * Find value
     * 
     * @param string $collectionName
     * @param string $database
     * @param array $options = [
     *      "filters":array
     * ]
     */
    public function find(string $collectionName, string $database, array $options = []):array|null {

        # Set result
        $result = null;

        # Check input
        if(!$collectionName || !$database)

            # Return result
            return $result;

        # Connect to database
        $database = $this->client->$database;

        # Connect to the collection
        $collection = $database->$collectionName;

        # Set filters
        $filters = isset($options["filters"]) && is_array($options["filters"])
            ? $options["filters"]
            : []
        ;

        # Last result
        $documents = $collection->find($filters, $options);

        # Iteration documents found
        foreach($documents as $document)

            # Push in result
            $result[] = $document;

        # Return result
        return $result === null ? [] : $result;

    }

    /**
     * Find Last One
     * 
     * Find last value of collection
     * 
     * @param string $collectionName
     * @param string $database
     */
    public function findLastOne(string $collectionName, string $database):array|null {

        # Set result
        $result = null;

        # Check input
        if(!$collectionName || empty($value) || !$database)

            # Return result
            return $result;

        # Connect to database
        $database = $this->client->$database;

        # Connect to the collection
        $collection = $database->$collectionName;

        # Last result
        $result = $collection->findOne([], [
            'sort' => ['_id' => -1],
        ]);

        # Return result
        return $result === null ? [] : $result;

    }

    /** Public Static Methods | Utilities
     ******************************************************
     */

    /**
     * Test
     * 
     * Test Database connection
     * 
     * @param array $options Option from Config > Database
     * @return bool
     */
    public static function test():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Get Connection String
     * 
     * Useful for connect to mongo db
     * 
     * @param array $options Option
     * @return string
     */
    public static function getConnectionString(array $options = []):string {

        # Set result
        $result = "";

        # Set result
        $result =                
            ($options["protocol"] ?? "mongodb")."://".
            ( $options["login"] ?: '' ).
            ( 
                $options["password"] ? 
                    ":".$options["password"] : 
                        '' 
            ).
            ( 
                $options["login"] || $options["password"] ? 
                    '@' : 
                        ''
            ).
            $options["host"].
            ( 
                $options["port"] !== false ?
                    ":".$options["port"] :
                         ''
            ).
            (
                $options["database"] ? 
                    "/".$options["database"] :
                        ''
            ).
            (
                isset($options["options"]) && count($options["options"]) > 0 ? 
                    '?' . http_build_query($options["options"]) : 
                        ''
            )
        ;

        # Return result
        return $result;

    }

    /** 
     * Convert Crazy Schema to Mongodb Schema
     * 
     * @param schema
     * @return array
     */
    public static function convertToMongoSchema(array $schema = []):array {

        # Declare result
        $result = [
            "validator" =>  [
                '$jsonSchema'   =>  static::_recursiveMongoSchemaConvertor($schema)
            ]
        ];

        # Return result
        return $result;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Setup
     * 
     * Setup current database
     * 
     * @return void
     */
    public static function setup():void {

        # Test mongodb
        $driver = new self();

        # New root client
        $driver->newClient("root");

        # Create database
        $driver->createDatabase();

        # Create config user
        $driver->createUserFromConfig();

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Recursive Mongo Schema convertor
     * 
     * @param array $schema
     * @param string $prefix = ""
     * @return array ["bsonType", "properties", "required"]
     */
    private static function _recursiveMongoSchemaConvertor(array $schema = [], string $prefix = ""):array {

        # Set result
        $result = [
            "bsonType"      =>  "object",
        ];

        # Declare required
        $required = [];

        # Declare properties
        $properties = [];

        # Check schema
        if(!empty($schema))

            # Iteration schema
            foreach($schema as $item){

                # Check prefix
                if($prefix)

                    # Check current item in child
                    if(strpos($item["name"], "$prefix.") !== false)

                        # Remove prefix from name
                        $item["name"] = str_replace("$prefix.", "", $item["name"]);

                    # Else continue loop
                    else

                        # Continue iteration
                        continue;

                # Check dot on name
                if(strpos($item["name"], ".") !== false){

                    # Set name
                    $name = explode(".", $item["name"])[0];

                    # Set description
                    $description = null;

                    # Recursive
                    $recursiveResult = static::_recursiveMongoSchemaConvertor(
                        $schema, 
                        $prefix 
                            ? "$prefix.$name"
                            : $name
                    );

                    # Set bson type
                    $bsonType = $recursiveResult["bsonType"] ?? "object";

                    # Set properties
                    $childProperties = $recursiveResult["properties"] ?? null;

                    # Set required
                    $childRequired = $recursiveResult["required"] ?? null;

                    # check if requierd
                    if($item["required"] ?? false)

                        # Push name in required
                        $required[] = $name;

                }else
                # Check if type VARCHAR
                if($item["type"] == "VARCHAR"){

                    # Set bson type
                    $bsonType = "string";

                    # Set Name
                    $name = $item["name"];

                    # Set description
                    $description = ($item['label'] ?? $name)." must be a \"$bsonType\"";

                    # check if requierd
                    if($item["required"] ?? false)

                        # Push name in required
                        $required[] = $name;

                    # Set properties
                    $childProperties = null;

                    # Set required
                    $childRequired = null;

                }else
                # Check if type VARCHAR
                if($item["type"] == "INT"){

                    # Set bson type
                    $bsonType = "int";

                    # Set Name
                    $name = $item["name"];

                    # Set description
                    $description = ($item['label'] ?? $name)." must be a \"$bsonType\"";

                    # check if requierd
                    if($item["required"] ?? false)

                        # Push name in required
                        $required[] = $name;

                    # Set properties
                    $childProperties = null;

                    # Set required
                    $childRequired = null;

                }else{

                    # Set bson type
                    $bsonType = "string";

                    # Set Name
                    $name = $item["name"];

                    # Set description
                    $description = ($item['label'] ?? $name)." should be a \"$bsonType\"";

                    # check if requierd
                    if($item["required"] ?? false)

                        # Push name in required
                        $required[] = $name;

                    # Set properties
                    $childProperties = null;

                    # Set required
                    $childRequired = null;

                }

                # Push bson type in properties
                $properties[$name]["bsonType"] = $bsonType;

                # Check description
                if($description !== null)

                    # Push description in properties
                    $properties[$name]["description"] = $description;

                # Check properties
                if($childProperties !== null)

                    # Push description in properties
                    $properties[$name]["properties"] = $childProperties;

                # Check properties
                if($childRequired !== null)

                    # Push description in properties
                    $properties[$name]["required"] = $childRequired;



            }

        # Fill result properties
        $result["properties"] = $properties;

        # Check required
        if(!empty($required))

            # Fill result properties
            $result["required"] = $required;

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "Database.collection.mongodb";

}