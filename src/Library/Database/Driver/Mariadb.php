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
use CrazyPHP\Core\Model;
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

        # Set simple uniques
        $simpleUniques = [];

        # Set multiple uniques
        $multipleUniques = [];

        # Set simple keys
        $simpleKeys = [];

        # Set multiple keys
        $multipleKeys = [];

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
                    ? (
                        $attribute['default'] === null 
                            ? "DEFAULT NULL"
                            : "DEFAULT '{".$attribute['default']."}'"
                    ) 
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
                    ? (
                        $attribute['default'] === null 
                            ? "DEFAULT NULL"
                            : "DEFAULT '".$attribute['default']."'"
                    ) 
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

            }else
            # If file
            if($columnTypeTemp == "FILE"){

                # Set column type
                $columnType = "VARCHAR(255)";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? 'NOT NULL' 
                    : 'NULL'
                ;

                # Set default
                $default = isset($attribute['default']) 
                    ? (
                        $attribute['default'] === null 
                            ? "DEFAULT NULL"
                            : "DEFAULT '{".$attribute['default']."}'"
                    ) 
                    : ''
                ;

            }else
            # If file
            if($columnTypeTemp == "JSON"){

                # Set column type
                $columnType = "JSON";

                # Set required
                $required = isset($attribute['required']) && $attribute['required'] 
                    ? "NOT NULL, CHECK (JSON_VALID($columnName))"
                    : 'NULL'
                ;

                # Set default
                $default = "";

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

            # Check if unique
            if(isset($attribute["unique"])){

                # Check if bool
                if(is_bool($attribute["unique"]) && $attribute["unique"] === true){

                    # Set unique
                    $simpleUniques[] = $columnName;

                }else
                # If is string
                if(is_string($attribute["unique"]) && $attribute["unique"]){

                    # Set unique
                    $multipleUniques[$attribute["unique"]][] = $columnName;

                }

            }

            # Check if key
            if(isset($attribute["key"])){

                # Check if bool
                if(is_bool($attribute["key"]) && $attribute["key"] === true){

                    # Set key
                    $simpleKeys[] = $columnName;

                }else
                # If is string
                if(is_string($attribute["key"]) && $attribute["key"]){

                    # Set unique
                    $multipleKeys[$attribute["unique"]][] = $columnName;

                }

            }
            
        }

        # Check columns
        if(empty($columns))

            # Return result
            return $result;

        # Fill query
        $query .= 
            # Start
            "(".
                # Columns
                implode(', ', $columns).
                # Primary key
                ", $primary". 
                # Key
                (!empty($simpleKeys) ? ", ".self::setSimpleExtra("key", $simpleKeys) : "").
                (!empty($multipleKeys) ? ", ".self::setMultipleExtra("key", $multipleKeys) : "").
                # Unique
                (!empty($simpleUniques) ? ", ".self::setSimpleExtra("unique", $simpleUniques) : "").
                (!empty($multipleUniques) ? ", ".self::setMultipleExtra("unique", $multipleUniques) : "").
                # Reference
                (!empty($references) ? ", ".implode(', ', $references) : "" ).
            # End
            ')'
        ;

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

            # Prepare query
            $query = $this->client->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :databaseName");

            # Execute
            $query->execute(['databaseName' => $database]);

            # Fetch results
            $result = $query->fetchAll(PDO::FETCH_COLUMN);

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

                # Set alias
                $alias = "a";

                # Set extra alias
                $extraAlias = "a";

                # Prepare query
                $query = "SELECT * FROM $table AS $alias";

                # Set filterStacked
                $filterStacked = [];

                # Iteration filters
                foreach($filters as $key => $value){

                    /**
                     * AND Operation
                     */

                    # Set operation
                    $operation = "AND";

                    /**
                     * OR Operation
                     * 
                     * Catcj || or !!
                     */

                    # check if key started with ||
                    if(substr($key, 0, 2) == "||" || substr($key, 0, 2) == "!!"){

                        # Update $key
                        $key = substr($key, 2);

                        # Set operation
                        $operation = "OR";

                    }

                    /**
                     * Include
                     * 
                     * Catch <> 
                     */

                    # Check if contains []
                    if(substr($key, 0, 2) == "<>" || substr($key, 0, 2) == "@@"){

                        # Update $key
                        $key = substr($key, 2);

                        # Clean "
                        $value = trim($value, '"');

                        # Clean '= '
                        $value = ltrim($value, '= "');

                        # Check if ? in value
                        if(strpos($value, "@") !== false){

                            # Explode current value
                            $currentValue = explode("@", $value, 2);

                            # Set table name
                            $includeTableName = $currentValue[0];
                            
                            # Set extra arguments
                            $includeExtraArguments = $currentValue[1] ?? null;

                        }else{

                            # Set table name
                            $includeTableName = $value;
                            
                            # Set extra arguments
                            $includeExtraArguments = null;


                        }

                        # Check if table exists
                        if(!Model::exists($includeTableName))

                            # Continue
                            continue;
                    
                        # Increment extra alias
                        $extraAlias++;

                        # Prepare includeQuery
                        $includeQuery = "EXISTS (";

                        # Check if key is id
                        if(strpos($key, "_") !== false){

                            # Set includeKey
                            $includeKey = $key;

                            # Check includeTableName in $key
                            if(substr($includeKey, 0, strlen($includeTableName)+1) == strtolower($includeTableName)."_")

                                $includeKey = str_replace(strtolower($includeTableName)."_", "", $includeKey);

                            else

                                # Set key target
                                $includeKey = $key;

                            # Set key target
                            $includeKeyTarget = $key;

                        }else{

                            # Set include key
                            $includeKey = strtolower($table)."_$key";

                            # Set key target
                            $includeKeyTarget = $key;

                        }

                        # Push sub query
                        $includeQuery .= "SELECT 1 FROM $includeTableName $extraAlias WHERE $extraAlias.$includeKey = $alias.$includeKeyTarget";

                        # Check include extra arguments
                        if($includeExtraArguments){

                            # Explode extra aruments
                            $includeExtraArguments = array_merge(...array_map(fn($pair) => [$k = explode(':', $pair, 2)[0] => is_numeric($v = explode(':', $pair, 2)[1]) ? (int)$v : $v], explode('@', $includeExtraArguments)));

                            # Iteration of extra argument
                            if(is_array($includeExtraArguments) && !empty($includeExtraArguments)) foreach($includeExtraArguments as $includeExtraArgumentKey => $includeExtraArgumentValue){

                                # Push it into includeQuery
                                $includeQuery .=  " AND $extraAlias.$includeExtraArgumentKey = ".(is_string($includeExtraArgumentValue) ? "'$includeExtraArgumentValue'" : $includeExtraArgumentValue);

                            }

                        }

                        # Close includeQuery
                        $includeQuery .= ")";

                        # Fill filter stacked
                        $filterStacked[] = [
                            "operation" =>  $includeQuery,
                            "condition" =>  $operation
                        ];


                    }else

                        # Fill filter stacked
                        $filterStacked[] = [
                            "operation" =>  "$alias.$key $value",
                            "condition" =>  $operation
                        ];

                }

                # Display filter stacked
                /* print_r($filterStacked);
                exit; */

                # Check filterStacked
                if(!empty($filterStacked)){

                    # Push where
                    $query .= " WHERE";

                    # Iteration of filterStacked
                    for($i = 1; $i <= count($filterStacked); $i++){

                        # Check if first
                        if($i === 1)

                            # Push in query
                            $query .= " ".$filterStacked[$i-1]["operation"];

                        else

                            # Push in query
                            $query .= " ".$filterStacked[$i-1]["condition"]." ".$filterStacked[$i-1]["operation"];

                    }

                }

                # Append sort
                $query .= static::appendSort($options["sort"] ?? null, $alias);

                # Update table
                $statment = $this->client->query($query);

                # Set result
                $result = $statment->fetchAll(PDO::FETCH_ASSOC);

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

    /** Public Static Methods | Audit (catch event on CREATE | UPDATE | DELETE)
     ******************************************************
     */
     
    /**
     * Create Audit Table
     * 
     * Create audit table and create trigger based on operation and table to fill audit table automatically
     * 
     * @param string|array $tables to audit
     * @param string|array $column to retrieve on audit
     * @param string|array $operations to audit ['INSERT'|'UPDATE'|'DELETE']
     * @param string $auditLogTableName audit table name (:-Audit_log)
     * @return void
     */
    public static function createAuditTable(
        string|array $tables,
        string|array $columns,
        string|array $operations = ['INSERT', 'UPDATE', 'DELETE'],
        string $auditLogTableName = 'Audit_log'
    ):void {

        # Check operation
        if(!is_array($operations))
        
            # Set operation
            $operations = [$operations];

        # Check columns
        if(!is_array($columns))
        
            # Set operation
            $columns = [$columns];

        # Check tables
        if(!is_array($tables))
        
            # Set tables
            $tables = [$tables];

        # Check audit log table name
        if(!$auditLogTableName)

            # Set default name
            $auditLogTableName = 'Audit_log';
     
        # Create the audit_log table if it does not exist
        $createAuditTableSQL = "
            CREATE TABLE IF NOT EXISTS `$auditLogTableName` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(255) NOT NULL,
                operation_type ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
                old_data JSON NULL,
                new_data JSON NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";

        # Get pdo
        $pdo = MariadbConnection::getInstance();

        # Create audiot table is not exists
        $pdo->exec($createAuditTableSQL);

        # Check tables
        if(!empty($tables) && !empty($operations))
        
            # Generate triggers for each table and operation
            foreach($tables as $table)

                # Check table
                if($table)

                    # Iteration operations
                    foreach($operations as $operation){

                        # Set Trigger name
                        $triggerName = "{$table}_after_".strtolower($operation);
            
                        # Check if the trigger already exists
                        $checkTriggerSQL = "
                            SELECT COUNT(*) AS trigger_exists
                            FROM information_schema.TRIGGERS
                            WHERE TRIGGER_NAME = :trigger_name
                            AND EVENT_OBJECT_TABLE = :table_name
                            AND TRIGGER_SCHEMA = DATABASE();
                        ";
            
                        # Prepare request
                        $stmt = $pdo->prepare($checkTriggerSQL);

                        # Execute request
                        $stmt->execute([
                            'trigger_name' => $triggerName,
                            'table_name' => $table,
                        ]);
            
                        # Get result
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
                        # Check if exists
                        if($result['trigger_exists'] > 0)

                            # Trigger already exists, skip creation
                            continue;
            
                        # Generate JSON_OBJECT for old_data
                        $oldDataParts = [];

                        # Generate JSON_OBJECT for new_data
                        $newDataParts = [];

                        # Iteration column needed
                        foreach($columns as $column)

                            # Check column
                            if($column){

                                # Push into old column
                                $oldDataParts[] = "'$column', OLD.$column";

                                # Push into new column
                                $newDataParts[] = "'$column', NEW.$column";

                            }

                        # Set old data json schema
                        $oldDataJson = count($oldDataParts) > 0 
                            ? "JSON_OBJECT(" . implode(', ', $oldDataParts) . ")" 
                            : "NULL"
                        ;

                        # Set new data json schema
                        $newDataJson = count($newDataParts) > 0 
                            ? "JSON_OBJECT(" . implode(', ', $newDataParts) . ")" 
                            : "NULL"
                        ;
            
                        # Build SQL for the trigger
                        $triggerSQL = "
                            CREATE TRIGGER `$triggerName`
                            AFTER $operation ON `$table`
                            FOR EACH ROW
                            BEGIN
                                INSERT INTO `$auditLogTableName` (table_name, operation_type, old_data, new_data)
                                VALUES ('$table', '$operation', 
                                    " . ($operation !== 'INSERT' ? $oldDataJson : 'NULL') . ", 
                                    " . ($operation !== 'DELETE' ? $newDataJson : 'NULL') . "
                                );
                            END;
                        ";
            
                        # Execute the trigger creation SQL
                        $pdo->exec($triggerSQL);

                    }

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
     * Append Sort
     * 
     * @param array|null $sort
     * @param string $alias
     * @return string
     */
    public static function appendSort(array|string|null $sort, string $alias = ""):string {

        # Set result
        $result = "";

        # Check sort
        if($sort !== null){

            # Check string
            if(is_string($sort)) $sort = [$sort];

            # Iteration sort
            if(!empty($sort)) foreach($sort as $field) if($field){

                # Check if first character is "-"
                if($field[0] == "-"){

                    # Push field
                    $result .= ", ".($alias ? "$alias." : "").ltrim($field, "-")." DESC";

                }else{

                    # Push field
                    $result .= ", ".($alias ? "$alias." : "")."$field ASC";

                }

            }

        }

        # Append ORDER BY
        if($result) $result = " ORDER BY".ltrim($result, ",");

        # Return result
        return $result;

    }

    /**
     * Set Simple Extra
     * 
     * @param string $type (unique or key)
     * @param array $content
     * @return string
     */
    public static function setSimpleExtra(string $type, array $content):string {

        # Set result
        $result = '';

        # Check type
        if(in_array($type, ["key", "unique"]))

            # Check content
            if(!empty($content))

                # Iteration content
                foreach($content as $column)

                    # Check column
                    if(is_string($column) && $column)

                        # Push value in string
                        $result .= 
                            ($type == "key" ? "KEY" : "UNIQUE KEY").
                            " `$column` (`$column`), "
                        ;

        # Return result
        return rtrim($result, ", ");

    }

    /**
     * Set Multiple Extra
     * 
     * @param string $type (unique or key)
     * @param array $content
     * @return string
     */
    public static function setMultipleExtra(string $type, array $content):string {

        # Set result
        $result = '';

        # Check type
        if(in_array($type, ["key", "unique"]))

            # Check content
            if(!empty($content))

                # Iteration content
                foreach($content as $key => $columns)

                    # Check column
                    if(is_string($key) && $key && is_array($columns) && !empty($columns)){

                        # Push value in string
                        $result .= 
                            ($type == "key" ? "KEY" : "UNIQUE KEY").
                            " `$key` ("
                        ;

                        # Iteration columns
                        foreach($columns as $column)

                            # Push into result
                            $result .= "`$column`, ";

                        # Push end
                        $result .= "), ";

                    }

        # Return result
        return rtrim(str_replace(", )", ")", $result), ", ");

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