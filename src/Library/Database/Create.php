<?php declare(strict_types=1);
/**
 * Database
 *
 * Useful class for manipulate database
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Database;

/** Dependances
 * 
 */
use \PDO;

/**
 * Create database
 *
 * Process form values return error / log message for client
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Create {

    /** Variables
     ******************************************************
     */

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Driver
        [
            "name"          =>  "driver",
            "description"   =>  "Type of your database",
            "type"          =>  "VARCHAR",
            "default"       =>  "mysql",
            "select"        =>  [
                "MySQL"         =>  "mysql",
                "MariaDB"       =>  "mysql",
                "PostgreSQL"    =>  "pgsql"
            ]
        ],
        # Host
        [
            "name"          =>  "host",
            "description"   =>  "Hostname of your crazy database service",
            "type"          =>  "VARCHAR",
            "default"       =>  "localhost",
            "required"      =>  true,
        ],
        # Root
        [
            "name"          =>  "root_login",
            "description"   =>  "Root of your crazy database service",
            "type"          =>  "VARCHAR",
            "default"       =>  "root",
            "required"      =>  true,
        ],
        # Root Password
        [
            "name"          =>  "root_password",
            "description"   =>  "Password of the root",
            "type"          =>  "PASSWORD",
            "default"       =>  "root"
        ],
        # Database
        [
            "name"          =>  "name",
            "description"   =>  "Name and prefix of your crazy database",
            "type"          =>  "VARCHAR",
            "default"       =>  "crazy",
            "required"      =>  true,
            "process"       =>  ['trim', 'clean']
        ],
        # User
        [
            "name"          =>  "user_login",
            "description"   =>  "New crazy user of your database",
            "type"          =>  "VARCHAR",
            "required"      =>  true,
            "default"       =>  "root"
        ],
        # User Password
        [
            "name"          =>  "user_password",
            "description"   =>  "Password of your crazy user",
            "type"          =>  "PASSWORD",
            "default"       =>  "root"
        ],
    ];

    /**
     * Input
     */
    private $input = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $inputs Collection of value to process
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Ingest inputs
        $this->_ingestInputs($inputs);

        # Return instance
        return $this;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Ingest Inputs
     * 
     * Ingest Inputs
     * 
     * @param array $inputs Collection of value to process
     * @return void
     */
    private function _ingestInputs(array $inputs = []):void {

        /* 
        # Check input
        if(!empty($inputs))

            # Iteration des inputs
            foreach($inputs as $input => $value)

                # Check parameter is in current input
                if(isset($this->input[$input]))

                    # Push value in input
                    $this->input[$input] = $value;
        */

        # Set input
        $this->input = $inputs;

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Run creation of database
     * 
     * Create database based on input values
     * @return Create
     */
    public function run():Create {

        # Try
        try {

            # Conenction to mysql
            $database = new PDO(
                "mysql:host=".$this->input['host'], 
                $this->input['root'],
                $this->input['host_password']
            );
        
            # New database
            $database->exec(
                # Create database
                "CREATE DATABASE `$db`;".
                # Create user
                "CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';".
                # Set permission of new user
                "GRANT ALL ON `$db`.* TO '$user'@'localhost';".
                "FLUSH PRIVILEGES;"
            ) or die(
                print_r($database->errorInfo(), true)
            );
        

        } catch (\PDOException $e) {

            die("DB ERROR: " . $e->getMessage());

        }

    }

}