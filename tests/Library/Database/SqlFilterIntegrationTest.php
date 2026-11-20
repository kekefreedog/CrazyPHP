<?php declare(strict_types=1);
/**
 * SQL Filter Integration Tests
 *
 * Exercise real PDO queries in a disposable database explicitly supplied by the test runner.
 *
 * PHP version 8.5
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace Tests\Library\Database;

/**
 * Dependances
 */
use CrazyPHP\Driver\Model\Mariadb as MariadbModel;
use CrazyPHP\Library\Database\Driver\Mariadb;
use CrazyPHP\Driver\Model\Sql as SqlModel;
use CrazyPHP\Library\Database\Driver\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Query;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use ReflectionClass;
use PDO;

/**
 * SQL Filter Integration Test
 *
 * Set CRAZY_SQL_TEST_DSN and optionally CRAZY_SQL_TEST_USER/PASSWORD to an isolated server.
 * Each test creates and removes its own randomly named database; no app configuration is loaded.
 */
class SqlFilterIntegrationTest extends TestCase {

    /** @var PDO $_client Isolated database connection */
    private PDO $_client;

    /** @var string $_database Unique fixture database */
    private string $_database = "";

    /** Public Methods | Preparation
     ******************************************************
     */

    /**
     * Prepare Disposable Records
     *
     * @return void
     */
    protected function setUp():void {

        # Require an explicit isolated test server
        if(!getenv("CRAZY_SQL_TEST_DSN")){

            # Avoid falling back to application credentials
            $this->markTestSkipped("Set CRAZY_SQL_TEST_DSN to a disposable MariaDB server.");

        }

        # Connect only to the explicitly supplied fixture server
        $this->_client = new PDO(
            getenv("CRAZY_SQL_TEST_DSN"),
            getenv("CRAZY_SQL_TEST_USER") ?: "root",
            getenv("CRAZY_SQL_TEST_PASSWORD") ?: "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        # Generate a database name unrelated to application databases
        $this->_database = "crazy_sql_".bin2hex(random_bytes(8));

        # Create the isolated fixture database
        $this->_client->exec("CREATE DATABASE `".$this->_database."` CHARACTER SET utf8mb4");

        # Select the fixture database
        $this->_client->exec("USE `".$this->_database."`");

        # Create scalar and relation fields
        $this->_client->exec("CREATE TABLE Fixture (id INT PRIMARY KEY, name TEXT, other TEXT, score INT, nullable TEXT, router_id INT)");

        # Create a related table matching the framework's built-in test model
        $this->_client->exec("CREATE TABLE Router (id INT PRIMARY KEY, fixture_id INT, name TEXT)");

        # Seed deterministic records
        $this->_client->exec("INSERT INTO Fixture VALUES (1, 'Alpha', 'Alpha', 10, NULL, 1), (2, 'Beta', 'Other', 20, 'x', 2), (3, 'Gamma', 'Gamma', 30, 'x', 2)");

        # Seed relation records
        $this->_client->exec("INSERT INTO Router VALUES (1, 1, 'linked'), (2, 2, 'other')");

        # Restrict model lookups to framework fixture declarations
        Env::set([
            "phpunit_test" => true,
            "crazyphp_root" => dirname(__DIR__, 3),
            "config_location" => "@crazyphp_root/resources/Yml",
        ]);

    }

    /**
     * Remove Disposable Records
     *
     * @return void
     */
    protected function tearDown():void {

        # Remove only the database created by this test
        if($this->_database !== ""){

            # Drop the isolated fixture database
            $this->_client->exec("DROP DATABASE `".$this->_database."`");

            # Reset fixture environment overrides
            Env::reset();

        }

    }

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Real Queries And Injection Payloads
     *
     * @param string $driverClass SQL database driver
     * @param bool $emulated PDO prepare mode
     * @param string $mode SQL quoting mode
     * @return void
     */
    #[DataProvider("drivers")]
    public function testRealQueries(string $driverClass, bool $emulated, string $mode):void {

        # Exercise native and emulated prepared statements
        $this->_client->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulated);

        # Exercise alternate SQL quoting rules
        $this->_client->exec("SET SESSION sql_mode = '".$mode."'");

        # Instantiate the real driver without loading application connections
        $driver = (new ReflectionClass($driverClass))->newInstanceWithoutConstructor();

        # Supply only the disposable database connection
        $driver->client = $this->_client;

        # Supply only the disposable database name
        $driver->config = ["database" => [$this->_database]];

        # Define supported query behavior
        $cases = [
            [["name" => "Alpha"], [1]],
            [["score" => ">=20"], [2, 3]],
            [["score" => "<=20"], [1, 2]],
            [["score" => "<20"], [1]],
            [["score" => ">20"], [3]],
            [["score" => "[10:20]"], [1, 2]],
            [["score" => "![10:20]"], [3]],
            [["name" => "[A:Bz]"], [1, 2]],
            [["name" => "*Al"], [1]],
            [["name" => "ma*"], [3]],
            [["name" => "*et*"], [2]],
            [["name" => "=other"], [1, 3]],
            [["name" => "!=other"], [2]],
            [["id" => [1, 3]], [1, 3]],
            [["id" => [2]], [2]],
            [["id" => []], []],
            [["score" => 0], []],
            [["score" => "0"], []],
            [["score" => false], []],
            [["name" => ""], []],
            [["nullable" => null], [1]],
            [["id" => 1, "||name" => "Beta"], [1, 2]],
            [["id" => 1, "!!name" => "Beta"], [1, 2]],
            [["<>id" => "Router@name:linked"], [1]],
            [["@@id" => "Router@name:linked"], [1]],
            [["><id" => "Router@name:linked"], [2, 3]],
            [["<>router_id" => "Router@name:linked"], [1]],
        ];

        # Check the supported filter contract on real rows
        foreach($cases as [$filters, $expected]){

            # Execute the production database driver
            $rows = $driver->find("Fixture", "", ["filters" => $filters, "sort" => "asc"]);

            # Verify exact matching records
            $this->assertSame($expected, array_map("intval", array_column($rows, "id")), json_encode($filters));

        }

        # Verify filtered pagination and descending sorting
        $this->assertSame([2], array_map("intval", array_column($driver->find("Fixture", "", [
            "filters" => ["score" => ">0"], "sort" => ["-id"], "limit" => 1, "page" => 2,
        ]), "id")));

        # Verify the formerly separate unfiltered sorting path
        $this->assertSame([3, 2, 1], array_map("intval", array_column($driver->find("Fixture", "", ["sort" => "desc"]), "id")));

        # Prepare payloads containing quotes, comments, multiple statements and binary characters
        $payloads = ["x\" OR 1=1 -- ", "x' OR 1=1 -- ", "x\\\" OR 1=1 #", "x'; DROP TABLE Fixture; -- ", "été 💡 ' \\\" ?", "x\0'\""];

        # Treat every payload as a real stored value
        foreach($payloads as $index => $payload){

            # Prepare a fixture insert
            $insert = $this->_client->prepare("INSERT INTO Fixture VALUES (?, ?, 'fixture', 42, 'x', 1)");

            # Store the exact payload as data
            $insert->execute([100 + $index, $payload]);

            # Check scalar equality, IN lists and textual ranges
            foreach([$payload, [$payload], "[".$payload.":".$payload."]"] as $value){

                # Execute the actual prepared filter query
                $rows = $driver->find("Fixture", "", ["filters" => ["name" => $value]]);

                # Match only the row containing the payload
                $this->assertSame([100 + $index], array_map("intval", array_column($rows, "id")));

            }

            # Verify LIKE payloads also remain bound data
            $this->assertSame([100 + $index], array_map("intval", array_column($driver->find("Fixture", "", [
                "filters" => ["name" => "*".str_replace("\\", "\\\\", $payload)."*"],
            ]), "id")));

        }

        # Verify relation values cannot bypass the linked-field comparison
        $this->assertSame([], $driver->find("Fixture", "", ["filters" => ["<>id" => "Router@name:x' OR 1=1 -- "]]));

        # Ensure all seeded rows survived the multiple-statement payload
        $this->assertCount(9, $driver->find("Fixture"));

        # Reject relation constraint field injection
        $this->expectException(CrazyException::class);

        # Require an input error rather than a database execution failure
        $this->expectExceptionCode(400);

        # Exercise the additional relation field validation
        $driver->find("Fixture", "", ["filters" => ["<>id" => "Router@name) OR 1=1 -- :x"]]);

    }

    /**
     * Test Model Query Parsing And Metadata
     *
     * @return void
     */
    public function testModelQueryParsing():void {

        # Create the real database driver without application configuration
        $database = (new ReflectionClass(Mariadb::class))->newInstanceWithoutConstructor();

        # Inject the isolated PDO connection
        $database->client = $this->_client;

        # Inject the isolated database name
        $database->config = ["database" => [$this->_database]];

        # Exercise both existing SQL model entrypoints
        foreach([MariadbModel::class, SqlModel::class] as $class){

            # Prepare a driver with its real defaults and an isolated database
            $model = $this->_model($class, $database);

            # Parse URL query-string filters using the production parser
            parse_str(http_build_query(["filter" => ["score" => ">=20"], "sorting" => "-id", "options" => ["limit" => 1, "page" => 2]]), $_GET);

            # Read the actual API query aliases
            [$filters, $sort, $group, $options] = Query::getForFilters();

            # Execute the production model parsing chain
            $rows = $model->parseFilter($filters, $options)->parseSort($sort, $options)->parseGroup($group, $options)->run();

            # Verify sorting and pagination survived model parsing
            $this->assertSame([2], array_map("intval", array_column($rows, "id")));

            # Exercise batch-style query argument parsing
            [$filters, $sort] = Query::getForFiltersFromArray(["filters" => ["name" => "x\" OR 1=1 -- "], "sort" => "asc"]);

            # Create an unpaginated model for the injection probe
            $model = $this->_model($class, $database);

            # Verify the batch parsing path cannot broaden a filter
            $this->assertSame([], $model->parseFilter($filters)->parseSort($sort)->run());

            # Create a model for an attempted raw-query option override
            $model = $this->_model($class, $database);

            # Keep HTTP query options separate from the trusted parseSql API
            $this->assertSame([1], array_map("intval", array_column($model->parseFilter(
                ["id" => 1],
                ["query" => "SELECT * FROM Fixture", "rawQuery" => "SELECT * FROM Fixture"]
            )->run(), "id")));

            # Create an ID-read model
            $model = $this->_model($class, $database);

            # Verify internal read-by-ID no longer relies on a raw SQL fragment
            $this->assertSame([2], array_map("intval", array_column($model->parseId(2)->run(), "id")));

        }

        # Create a paginated MariaDB model with metadata enabled
        $model = $this->_model(MariadbModel::class, $database);

        # Execute the metadata total query through the same bound filters
        $rows = $model->parseFilter(["score" => ">=20"], ["pageStateProcess" => true, "limit" => 1])->run();

        # Verify the total count respects the active filter
        $this->assertSame(2, $rows["_metadata"]["records_total"]);

        # Clear test request input
        $_GET = [];

    }

    /**
     * Test Model Rejection Of Malformed Input
     *
     * @return void
     */
    public function testModelRejectsMalformedInput():void {

        # Create the production database driver without application configuration
        $database = (new ReflectionClass(Mariadb::class))->newInstanceWithoutConstructor();

        # Inject the fixture connection
        $database->client = $this->_client;

        # Inject the fixture database
        $database->config = ["database" => [$this->_database]];

        # Prepare fields, sort expressions and malformed relations
        $inputs = [
            ["filters" => ["id OR 1=1 -- " => 1]],
            ["filters" => ["name" => "=other` OR 1=1 -- "]],
            ["filters" => ["name" => ["sql" => "OR 1=1", "parameters" => []]]],
            ["filters" => ["<>id" => "UnknownFixtureModel"]],
            ["filters" => ["<>id" => "Router@name"]],
            ["filters" => ["<>id" => "Router@name) OR 1=1 -- :x"]],
            ["sort" => ["id, (SELECT SLEEP(1))"]],
            ["sort" => [["id"]]],
            ["sort" => [1]],
            ["sort" => [""]],
        ];

        # Check both model driver entrypoints
        foreach([MariadbModel::class, SqlModel::class] as $class){

            # Run each malformed request independently
            foreach($inputs as $input){

                # Parse batch-style API arguments
                [$filters, $sort] = Query::getForFiltersFromArray($input);

                # Create a fresh model parser
                $model = $this->_model($class, $database);

                # Verify unsupported input is never silently dropped
                try {

                    # Exercise the production model-to-database path
                    $model->parseFilter($filters)->parseSort($sort)->run();

                    # Fail if an invalid constraint or sort was accepted
                    $this->fail("Malformed SQL filter or sort was accepted.");

                } catch (CrazyException $exception) {

                    # Require a client-input error
                    $this->assertSame(400, $exception->getCode());

                }

            }

        }

        # Verify the rejected requests did not modify fixture data
        $this->assertCount(3, $database->find("Fixture"));

    }

    /**
     * Provide Driver And SQL Modes
     *
     * @return array Driver matrix
     */
    public static function drivers():array {

        # Prepare result
        $result = [];

        # Exercise both database implementations
        foreach([Mariadb::class, Mysql::class] as $driver){

            # Exercise PDO native and emulated prepares
            foreach([false, true] as $emulated){

                # Exercise default and strict quoting modes
                foreach(["", "ANSI_QUOTES,NO_BACKSLASH_ESCAPES"] as $mode){

                    # Add an independent matrix case
                    $result[] = [$driver, $emulated, $mode];

                }

            }

        }

        # Return result
        return $result;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Prepare Isolated Model Driver
     *
     * @param string $class Production model driver class
     * @param Mariadb $database Isolated database driver
     * @return MariadbModel|SqlModel Model with real parsing and execution methods
     */
    private function _model(string $class, Mariadb $database):MariadbModel|SqlModel {

        # Prepare result without invoking application schema setup
        $result = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        # Inject the isolated database driver
        $result->mariadb = $database;

        # Supply the normal model arguments with a fixture table
        (new ReflectionClass($class))->getProperty("arguments")->setValue($result, array_replace($class::ARGUMENTS, ["table" => "Fixture"]));

        # Return result
        return $result;

    }

}
