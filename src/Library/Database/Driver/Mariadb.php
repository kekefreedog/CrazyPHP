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
use CrazyPHP\Library\Database\Singleton\MariadbConnection;
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Interface\CrazyDatabaseDriver;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use Envms\FluentPDO\Query;
use PDOException;
use PDO;

/**
 * Mariadb
 *
 * Driver for manipulate MariaDB
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Mariadb implements CrazyDatabaseDriver {

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
     * Get current database config
     * 
     * @return self
     */
    public function __construct() {

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
    public function newClient(string|int $user = 0):self {

        # New connection
        $this->client = MariadbConnection::getInstance([
            "user"  =>  $user
        ]);

        # Set manager
        $this->manager = new Query($this->client);

        # Return self
        return $this;

    }

    /** Public Methods | User
     ******************************************************
     */

    /**
     * Create Users
     * 
     * Create new user
     * 
     * @param string $user User name
     * @param string $password Password
     * @param string|array databases Name of database
     * @param string|array $options Options for create user
     * @return self
     */
    public function createUser(string $user = "", string $password = "", string|array $databases = [], string|array $options = []):self {

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

        # Prepare query
        $query = "CREATE USER :user IDENTIFIED BY :password";

        # Prepate statment
        $statment = $this->client->prepare($query);

        # Set user
        $statment->bindParam(':user', $user);

        # Set password
        $statment->bindParam(':password', $password);

        try {

            # Execute statment
            $statment->execute();

        } catch (PDOException $e) {

            throw new CrazyException(
                "Error creating user: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-001",
                ]
            );

        }

        # Check database
        if(is_string($databases))

            # Convert to array
            $databases = [$databases];

        # Iteration databases
        foreach ($databases as $database) {

            # Prepare query
            $query = "GRANT ALL PRIVILEGES ON $database.* TO :user";

            # Prepare statment
            $statment = $this->client->prepare($query);

            # Set user
            $statment->bindParam(':user', $user);

            try {

                # Execute the grant statement
                $statment->execute();
                
            } catch (PDOException $e) {

                # New exception
                throw new CrazyException(
                    "Error granting privileges: " . $e->getMessage(),
                    500,
                    [
                        "custom_code"   =>  "mariadb-002",
                    ]
                );

            }
        }

        // Flush privileges
        $this->client->exec("FLUSH PRIVILEGES");

        # Return result
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
                    "custom_code"   =>  "mariadb-003",
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

        # Return result
        return $this;

    }

    /** Public Methods | Database
     ******************************************************
     */

    /**
     * Create Database
     * 
     * @param string $options
     * @return void
     */
    public function createDatabase(string $option = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"):void {

        # Check client
        if(!$this->client)

            # New Exception
            throw new CrazyException(
                "Please execute \"newClient\" method before \"".__METHOD__."\" method...",
                500,
                [
                    "custom_code"   =>  "mariadb-004",
                ]
            );

        # Switch to database
        $database = $this->client->{$this->config["database"][0]};

        # Prepare query
        $query = "CREATE DATABASE IF NOT EXISTS $database";

        # Check option
        if (!empty($options))

            # Append option to query
            $query .= " $options";

        try {

            $this->client->exec($query);

        } catch (PDOException $e) {

            throw new CrazyException(
                "Error creating database: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-005",
                ]
            );

        }

    }

    /**
     * Create Table
     * 
     * @param string $table
     * @param array $attributes
     * @param bool $replaceTable
     * @param array $option
     * @param string $database
     * @return mixed
     */
    public function createTable($tableName = "", $attributes = [], $replaceTable = false, $option = [], string $database = ""):mixed {

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Set result
        $result = null;

        # Check attributes
        if(empty($attributes) || !$tableName)

            # Return result
            return $result;

        # Switch to the specified database
        $this->client->exec("USE " . $database);

        # Prepare query
        $query = "CREATE TABLE ".($replaceTable ? "" : "IF NOT EXISTS ")."`$tableName` ";

        # Set columns
        $columns = [];

        # Set primary
        $primary = "";

        # Set reference
        $references = [];

        # Iteration attributes
        foreach($attributes as $attribute){

            # Set id id
            $isId = false;

            # Get column name
            $columnName = $attribute['name'];

            # Set type
            $columnTypeTemp = strtoupper($attribute['type']);

            # Set column type
            $columnType = $columnTypeTemp;

            # If varchar
            if($columnTypeTemp == "INT" && $columnName == "id" && !$isId){

                # Set column type
                $columnType = "int(6) UNSIGNED";

                # Set is id
                $isId = true;

                # Set primary
                $primary = "PRIMARY KEY (`$columnName`)";

                # Set required
                $required = 'NOT NULL';

                # Set default
                $default = isset($attribute['default']) 
                    ? "DEFAULT '{".$attribute['default']."}'" 
                    : ''
                ;

            }else
            # If varchar
            if($columnTypeTemp == "VARCHAR"){

                # Set column type
                $columnType = "VARCHAR(255)";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? "DEFAULT '{".$attribute['default']."}'" 
                    : ''
                ;

            }else
            # If int
            if($columnTypeTemp == "INT"){

                # Set column type
                $columnType = "INT(11)";

                # Check if reference
                if(isset($attribute['reference']) && $attribute['reference'])

                    # Update column type
                    $columnType = "int(6) UNSIGNED";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? "DEFAULT '{".$attribute['default']."}'" 
                    : ''
                ;

            }else
            # If decimal
            if($columnTypeTemp == "DECIMAL"){

                # Set decimal
                $decimal = 2;

                # Set column type
                $columnType = "DECIMAL(11,$decimal)";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? "DEFAULT '".number_format($attribute['default'], $decimal, '.', '')."'" 
                    : ''
                ;

            }else
            # If int
            if($columnTypeTemp == "BOOL" || $columnTypeTemp == "BOOLEAN"){

                # Set column type
                $columnType = "BOOLEAN";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? "DEFAULT '{".$attribute['default']."}'" 
                    : ''
                ;

            }else
            # If date
            if($columnTypeTemp == "DATE" || $columnTypeTemp == "DATETIME"){

                # Set column type (DATE)
                $columnType = "TIMESTAMP";

                # Set required

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? (
                        $attribute['default'] == "today()"
                            ? "DEFAULT current_timestamp"
                            : "DEFAULT '{".$attribute['default']."}'" 
                    )
                    : ''
                ;

            }

            # Clean column type
            $columnType = strtolower($columnType);

            # Push result into columns
            $columns[] = trim("`$columnName` $columnType $required $default").($isId ? " AUTO_INCREMENT" : "");

            # Check if reference
            if(isset($attribute['reference']) && $attribute['reference']){

                # Set column type
                $references[] = trim("FOREIGN KEY (`$columnName`) REFERENCES `".$attribute['reference']."` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");

            }
            
        }

        # Check columns
        if(empty($columns))

            # Return result
            return $result;

        # Fill query
        $query .= "(". implode(', ', $columns) . ", $primary" . (!empty($references) ? ", ".implode(', ', $references) : "" ) . ')';

        # Execute the SQL statement to create the table
        try {

            # Exec
            $result = $this->client->exec($query);

        } catch (PDOException $e) {

            # Error
            throw new CrazyException(
                "Error creating table: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-006",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Get All Tables
     * 
     * Retrieve a list of all tables in a given database
     * 
     * @param string $database Name of the database (by default take the first one in config file)
     * @return array
     */
    public function getAllTables(string $database = ""):array {

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Set result
        $result = [];

        try {

            # Switch to the specified database
            $this->client->exec("USE " . $database);

            # Execute the SHOW TABLES command
            $statment = $this->client->query("SHOW TABLES");

            # Fetch all table
            $result = $statment->fetchAll(PDO::FETCH_COLUMN);

        } catch (PDOException $e) {

            throw new CrazyException(
                "Error retrieving tables: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-006",
                ]
            );

        }

        # Return result
        return $result;

    }


    /**
     * Has Table
     * 
     * Check if a specific table exists in a given database
     * 
     * @param string $database Name of the database
     * @param string $table Name of the table
     * @return bool
     */
    public function hasTable(string $table = "", string $database = ""): bool {

        # Set result
        $result = false;

        # Check collection
        if(!$table)

            # Return result
            return $result;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        try {

            # Preapre statment
            $stmt = $this->client->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table
            ");

            # Set database
            $stmt->bindParam(':database', $database);

            # Set table
            $stmt->bindParam(':table', $table);

            # Execute
            $stmt->execute();

            # Get count
            $count = $stmt->fetchColumn();

            # Set result
            $result = $count > 0;

        } catch (PDOException $e) {

            throw new CrazyException(
                "Error checking table existence: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-007",
                ]
            );

        }

        # Return result
        return $result;
    }

    /**
     * Insert To Table
     * 
     * Insert values to table
     * 
     * @param string $table
     * @param array $value
     * @param string $database
     * @param bool $createIfNotExists
     * @param array $validator
     */
    public function insertToTable(string $table, array $value, string $database = "", bool $createIfNotExists = false, array $validator = []) {

        # Set result
        $result = null;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Check input
        if(!$table || empty($value) || !$database)

            # Return result
            return $result;

        # Use database
        $this->client->exec("USE " . $database);

        # Check has
        if(!$this->hasTable($table, $database)){

            # Check if create
            if(!$createIfNotExists)

                # Stop
                return $result;

            # Create table
            $this->createTable($table, $value);

        }

        try {
        
            // Insert chain
            $result = $this->manager
                ->insertInto($table)
                ->values($value)
                ->execute()
            ;
        
        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "An error occurred: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-008",
                ]
            );
        }

        # Return result
        return $result;

    }    
    
    /**
     * Update One To Table
     * 
     * Update one value into table
     * 
     * @param string $table
     * @param array $value
     * @param int $id
     * @param string $database
     */
    public function updateOneToTable(string $table, array $value, int $id, string $database = "") {

        # Set result
        $result = null;

        # Check input
        if(!$table || empty($value))

            # Return result
            return $result;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Use database
        $this->client->exec("USE " . $database);

        try {

            # Update table
            $result = $this->manager
                ->update($table)
                ->set($value)
                ->where('id', $id)
                ->execute()
            ;

        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "Error updating item: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-008",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Delete One To Table
     * 
     * Delete one value to table
     * 
     * @param string $table
     * @param int $id
     * @param string $database
     */
    public function deleteOneToCollection(string $table, int $id, string $database = "") {

        # Set result
        $result = null;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Check input
        if(!$table || !$database)

            # Return result
            return $result;

        # Use database
        $this->client->exec("USE " . $database);
    
        try {

            # Update table
            $result = $this->manager
                ->deleteFrom($table)
                ->where('id', $id)
                ->execute()
            ;

        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "Error deleting item: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-009",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Find
     * 
     * Find values
     * 
     * @param string $table
     * @param string $database
     * @param array $options = [
     *      "query":string
     *      "filters":array,
     *      "sort":array
     *      "limit":array
     * ]
     */
    public function find(string $table, string $database = "", array $options = []):array|null {

        # Set result
        $result = null;

        # Check input
        if(!$table)

            # Return result
            return $result;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Use database
        $this->client->exec("USE " . $database);

        # Check query
        $isQuery = isset($options["query"]) && !empty($options["query"]);

        # Check query
        if(!$isQuery)

            # Set filters
            $filters = isset($options["filters"]) && is_array($options["filters"])
                ? $options["filters"]
                : []
            ;
    
        try {

            # Check query
            if($isQuery){

                # Update table
                $statment = $this->client->query($options["query"]);

                # Set result
                $result = $statment->fetchAll(PDO::FETCH_ASSOC);

            }else
            # Check filters
            if(!empty($filters)){

                # Set instance
                $instance = $this->manager->from($table);

                # Iteration filters
                foreach($filters as $key => $value){

                    $instance->where($key, $value);

                }

                # Update table
                $result = $instance->fetchAll();

            }else{

                # Update table
                $result = $this->manager
                    ->from($table)
                    ->fetchAll()
                ;

            }

        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "Error finding items: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-009",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Find One
     * 
     * Find value
     * 
     * @param string $table
     * @param string $database
     * @param array $options = [
     *      "filters":array,
     *      "sort":array
     *      "limit":array
     * ]
     */
    public function findOne(string $table, string $database, array $options = []):array|null {

        # Set result
        $result = null;

        # Check input
        if(!$table || empty($value) || !$database)

            # Return result
            return $result;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Use database
        $this->client->exec("USE " . $database);

        # Set filters
        $filters = isset($options["filters"]) && is_array($options["filters"])
            ? $options["filters"]
            : []
        ;
    
        try {

            # Update table
            $result = $this->manager
                ->from($table)
                ->where($filters)
                ->limit(1)
                ->fetch()
            ;

        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "Error finding item: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-009",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Find Last One
     * 
     * Find last value
     * 
     * @param string $table
     * @param string $database
     * @param array $options = [
     *      "filters":array,
     *      "sort":array
     *      "limit":array
     * ]
     */
    public function findLastOne(string $table, string $database, array $options = []):array|null {

        # Set result
        $result = null;

        # Check input
        if(!$table || empty($value) || !$database)

            # Return result
            return $result;

        # Check database
        if(!$database)

            # Get main database
            $database = $this->_getDefaultDatabase();

        # Use database
        $this->client->exec("USE " . $database);

        # Set filters
        $filters = isset($options["filters"]) && is_array($options["filters"])
            ? $options["filters"]
            : []
        ;
    
        try {

            # Update table
            $result = $this->manager
                ->from($table)
                ->where($filters)
                ->orderBy('id DESC')
                ->limit(1)
                ->fetch()
            ;

        } catch (PDOException $e) {

            # New exception
            throw new CrazyException(
                "Error finding last item: " . $e->getMessage(),
                500,
                [
                    "custom_code"   =>  "mariadb-009",
                ]
            );

        }

        # Return result
        return $result;

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
     * Get Connection Array
     * 
     * Useful for connect to pdo
     * 
     * @param array $options Option
     * @return array
     */
    public static function getConnectionArray(array $options = []):array {

        # Set result
        $result = [];

        # Set dsn
        $result[0] = 
            "mysql:host=".$options["host"].";".
            (
                $options["port"] !== false 
                    ? "port=".$options["port"].";"
                    : ""
            ).
            "dbname=".$options["database"]
        ;

        # Check login
        if($options["login"]){

            # Set user
            $result[1] = $options["login"];

            # Check password
            if($options["password"])

                # Set password
                $result[2] = $options["password"];

        }

        # Return result
        return $result;

    }

    /**
     * Set Entity As Prefix
     * 
     * @param string $entity
     * @param string $alias
     * @return string
     */
    public static function setEntityAsPrefix(string $entity, string $alias = ""):string {

        # Set result
        $result = "";

        # Set result exploded
        $resultExploded = [];

        # Open Model Config
        $modelConfig = FileConfig::get("Model");

        # Check current class model config exists
        $modelMatching = Arrays::filterByKey($modelConfig["Model"], "name", $entity);

        # Check model matching
        if(!empty($modelMatching)){

            # Get modelObject 
            $modelObject = array_pop($modelMatching);

            # Get attributes
            $attributes = $modelObject["attributes"] ?? [];

            # Check attributes
            if(!empty($attributes))

                # Iteration of attributes
                foreach($attributes as $attribute){

                    # Get currentName
                    $currentName = $attribute["name"] ?? "";

                    # Check current name
                    if($currentName){

                        # Push current name into result
                        $resultExploded[] = 
                            (
                                $alias 
                                    ? "$alias."
                                    : ""
                            ).$currentName.
                            " AS ".
                            $entity.
                            "_".
                            $currentName
                        ;
                    }

                }

        }

        # Check resultExploded
        if(!empty($resultExploded))

            # Set result
            $result = implode(", ", $resultExploded);

        # Return result
        return $result;

    }

    /** Private Methods
     ******************************************************
     */

    private function _getDefaultDatabase():string {

        # Get result
        $result = $this->config["database"][0];

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



    }

    /** Public constants
     ******************************************************
     */

    /**
     * @const string CONFIG_KEY Config key for current database
     */
    public const CONFIG_KEY = "Database.collection.mariadb";

}