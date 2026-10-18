<?php declare(strict_types=1);
/**
 * Docker Bootstrap
 *
 * Prepare configured databases inside Compose without depending on container IDs.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File\Docker;

/**
 * Dependencies
 */
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use RuntimeException;
use PDO;

/**
 * Bootstrap
 *
 * Create missing databases and accounts while preserving existing credentials and data.
 */
class Bootstrap {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Container Configuration
     *
     * @param array $configuration Original database document
     * @return array
     */
    public static function containerConfiguration(array $configuration):array {

        # Replace only endpoints explicitly associated with a generated Docker service
        foreach($configuration["Database"]["collection"] ?? [] as $engine => $database){

            # Keep disabled engines and externally managed databases unchanged
            if(!is_array($database) || !isset($database["docker"]["service"]["name"]))

                # Preserve the source settings for this entry
                continue;

            # Require a supported engine before deriving its container port
            if(!isset(Docker::DATABASE_TO_SERVICE[$engine]))

                # Reject unsupported runtime mappings without guessing a port
                throw new RuntimeException("Unsupported Docker database: $engine");

            # Use Compose's stable service DNS name
            $configuration["Database"]["collection"][$engine]["host"] = $database["docker"]["service"]["name"];

            # Use internal ports independently of the host's local configuration
            $configuration["Database"]["collection"][$engine]["port"] = match($engine){
                "mongodb" => 27017,
                "postgresql" => 5432,
                default => 3306,
            };

        }

        # Return an in-memory overlay without modifying the source file
        return $configuration;

    }

    /**
     * Run
     *
     * @return void
     */
    public static function run():void {

        # Read the local source through the preparation service's unmodified project mount
        $source = File::path("@app_root/config/Database.yml");

        # Support projects that intentionally have no database
        $configuration = is_file($source) ? Yaml::open($source) : ["Database" => ["collection" => []]];

        # Resolve all container endpoints before contacting databases
        $configuration = self::containerConfiguration($configuration);

        # Provision every configured service rather than only the first engine
        foreach($configuration["Database"]["collection"] ?? [] as $engine => $database){

            # Leave disabled and external databases alone
            if(!is_array($database) || empty($database["docker"]["service"]["name"]))

                # Continue without contacting an unmanaged server
                continue;

            # Require explicit database and account definitions
            self::_validate($database);

            try{

                # Select the supported provisioning protocol
                if($engine === "mongodb")

                    # Provision MongoDB using its extension rather than shell interpolation
                    self::_mongo($database);

                else

                    # Provision SQL engines with quoted identifiers and values
                    self::_sql($engine, $database);

            }catch(\Throwable $exception){

                # Keep connection strings and credentials out of command output
                throw new RuntimeException("Database initialization failed for $engine; check readiness and administrator credentials (code ".$exception->getCode().").");

            }

            # Report completion without printing user passwords
            echo "Database prepared: $engine".PHP_EOL;

        }

        # Update the existing bind source in place so runtime containers see the new settings
        Yaml::create(File::path("@app_root/docker/config/Database.yml"), $configuration);

        # Permit the HTTP worker to read its bind mount; the host directory is private
        chmod(File::path("@app_root/docker/config/Database.yml"), 0644);

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Validate
     *
     * @param array $database Database configuration
     * @return void
     */
    private static function _validate(array $database):void {

        # Require at least one database before creating accounts
        if(empty($database["database"]) || !is_array($database["database"]))

            # Explain invalid configuration without echoing its contents
            throw new RuntimeException("Database.yml must contain database names.");

        # Validate identifiers before using them in database commands
        foreach($database["database"] as $name)

            # Forbid empty names and protocol delimiters without rejecting normal punctuation
            if(!is_string($name) || $name === "" || preg_match('/[\x00-\x1f;]/', $name))

                # Reject names that cannot be safely used across the supported connection formats
                throw new RuntimeException("Invalid database name in Database.yml.");

        # Require accounts with nonempty passwords
        foreach($database["users"] ?? [] as $user)

            # Reject invalid configuration before partial account creation
            if(!is_array($user) || !is_string($user["login"] ?? null) || $user["login"] === "" || !is_string($user["password"] ?? null) || $user["password"] === "")

                # Keep secret values out of validation errors
                throw new RuntimeException("Each database user needs a login and password.");

    }

    /**
     * SQL
     *
     * @param string $engine Database engine
     * @param array $database Resolved configuration
     * @return void
     */
    private static function _sql(string $engine, array $database):void {

        # Match the administrator initialized by the official image
        $admin = $engine === "postgresql" ? $database["users"][0] : ["login" => "root", "password" => $database["root"]["password"]];

        # Connect to the existing administrative database rather than one we may need to create
        $dsn = ($engine === "postgresql" ? "pgsql" : "mysql").":host=".$database["host"].";port=".$database["port"].($engine === "postgresql" ? ";dbname=postgres" : ";charset=utf8mb4");

        # Make all SQL errors fail the preparation service
        $pdo = new PDO($dsn, $admin["login"], $admin["password"], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        # Quote identifiers using the selected server's syntax
        $quote = static fn(string $name):string => $engine === "postgresql" ? '"'.str_replace('"', '""', $name).'"' : '`'.str_replace('`', '``', $name).'`';

        # Create missing accounts without altering existing passwords
        foreach($database["users"] ?? [] as $user){

            # PostgreSQL requires a catalog check because CREATE ROLE has no IF NOT EXISTS
            if($engine === "postgresql"){

                # Look up the account using a bound parameter
                $statement = $pdo->prepare("SELECT 1 FROM pg_roles WHERE rolname = ?");

                # Keep the login separate from SQL code
                $statement->execute([$user["login"]]);

                # Create the role only when it does not already exist
                if(!$statement->fetchColumn())

                    # Quote both the role identifier and its password
                    $pdo->exec("CREATE ROLE ".$quote($user["login"])." LOGIN PASSWORD ".$pdo->quote($user["password"]));

            }else{

                # Treat MySQL account names as string values and scope connections to the container network
                $pdo->exec("CREATE USER IF NOT EXISTS ".$pdo->quote($user["login"])."@'%' IDENTIFIED BY ".$pdo->quote($user["password"]));

            }

        }

        # Create every configured database, preserving existing tables and records
        foreach($database["database"] as $name){

            # PostgreSQL needs a catalog check outside a transaction
            if($engine === "postgresql"){

                # Look up the database without injecting its name into SQL
                $statement = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");

                # Bind the requested database name
                $statement->execute([$name]);

                # Create only missing databases
                if(!$statement->fetchColumn())

                    # Preserve the default user's ownership of newly created databases
                    $pdo->exec("CREATE DATABASE ".$quote($name)." OWNER ".$quote($admin["login"]));

            }else{

                # MySQL and MariaDB support idempotent creation directly
                $pdo->exec("CREATE DATABASE IF NOT EXISTS ".$quote($name));

            }

            # Grant configured users access to the application's databases
            foreach($database["users"] ?? [] as $user){

                # Use database-specific privileges rather than global administrative grants
                if($engine === "postgresql"){

                    # Allow the configured user to connect to this database
                    $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE ".$quote($name)." TO ".$quote($user["login"]));

                    # Connect to the target database to grant schema-level creation rights
                    $target = new PDO("pgsql:host=".$database["host"].";port=".$database["port"].";dbname=$name", $admin["login"], $admin["password"], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                    # PostgreSQL 15 and newer restrict creation in the public schema by default
                    $target->exec("GRANT USAGE, CREATE ON SCHEMA public TO ".$quote($user["login"]));

                }else{

                    # Restrict application users to this database's objects
                    $pdo->exec("GRANT ALL PRIVILEGES ON ".$quote($name).".* TO ".$pdo->quote($user["login"])."@'%'");

                }

            }

        }

    }

    /**
     * Mongo
     *
     * @param array $database Resolved configuration
     * @return void
     */
    private static function _mongo(array $database):void {

        # Encode administrator credentials before constructing the authenticated URI
        $uri = "mongodb://".rawurlencode($database["root"]["login"] ?? "root").":".rawurlencode($database["root"]["password"])."@".$database["host"].":".$database["port"]."/?authSource=admin";

        # Use the extension already installed in the selected PHP image
        $manager = new Manager($uri);

        # Create each configured database and its application accounts
        foreach($database["database"] as $name){

            # Query the marker collection to avoid duplicate-collection errors on restarts
            $collections = $manager->executeCommand($name, new Command(["listCollections" => 1, "filter" => ["name" => "_crazyphp"]]))->toArray();

            # MongoDB materializes a database when its first collection is created
            if($collections === [])

                # Create a harmless marker collection without deleting application data
                $manager->executeCommand($name, new Command(["create" => "_crazyphp"]));

            # Authenticate application users against their configured database
            foreach($database["users"] ?? [] as $user){

                # Check whether this account already exists in this database
                $information = $manager->executeCommand($name, new Command(["usersInfo" => $user["login"]]))->toArray();

                # Preserve existing passwords across repeated startup
                if(empty($information[0]->users))

                    # Grant application read/write access to every configured database
                    $manager->executeCommand($name, new Command(["createUser" => $user["login"], "pwd" => $user["password"], "roles" => array_map(static fn(string $target):array => ["role" => "readWrite", "db" => $target], $database["database"])]));

            }

        }

    }

}
