<?php declare(strict_types=1);
/**
 * Test Database
 *
 * Test Database Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Database;

/**
 * Dependances
 */
use CrazyPHP\Library\Database\Driver\Mangodb;
use CrazyPHP\Library\Database\Driver\Mariadb;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Mariadb Test
 *
 * Methods for test Mongodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MariadbTest extends TestCase {
    
    /** Public constants
     ******************************************************
     */
    

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up Before Class
     * 
     * This method is called before the first test of this test class is run.
     * 
     * @return void
     */
    public static function setUpBeforeClass():void {

        # Setup env
        Env::set([
            # App root for composer class
            "crazyphp_root"     =>  getcwd(),
            "phpunit_test"      =>  true,
            "config_location"   =>  "@crazyphp_root/resources/Yml"
        ]);

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Set Entity As Prefix
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testSetEntityAsPrefix():void {

        # Set entity
        $entity = "Router";

        # Set alias
        $alias = "a";

        # Get result
        $result = Mariadb::setEntityAsPrefix($entity, $alias);

        # Check result
        $this->assertEquals("a.name AS Router_name, a.path AS Router_path", $result);

    }

    /**
     * Test Set Simple extra
     * 
     * @return void
     */
    public function testSetSimpleExtra():void {

        # Input
        $input = [
            "key",
            [
                "name",
                "id",
                "toto"
            ]
        ];

        # Result
        $result = Mariadb::setSimpleExtra(...$input);

        # Expected
        $expected = "KEY `name` (`name`), KEY `id` (`id`), KEY `toto` (`toto`)";

        # Assert
        $this->assertEquals($expected, $result);

    }

    /**
     * Test Set Multiple extra
     * 
     * @return void
     */
    public function testSetMultipleExtra():void {

        # Input
        $input = [
            "unique",
            [
                "name_id_toto"  =>  [ 
                    "name",
                    "id",
                    "toto"
                ],
                "id_royal_po"  =>   [
                    "id",
                    "royal_po"
                ],
            ]
        ];

        # Result
        $result = Mariadb::setMultipleExtra(...$input);

        # Expected
        $expected = "UNIQUE KEY `name_id_toto` (`name`, `id`, `toto`), UNIQUE KEY `id_royal_po` (`id`, `royal_po`)";

        # Assert
        $this->assertEquals($expected, $result);

    }

    /**
     * Test Append Sort
     * 
     * @return void
     */
    public function testAppendSort():void {

        # Set input
        $input = [
            "field1",
            "-field2",
            "toto"
        ];

        # Get result A
        $resultA = Mariadb::appendSort($input);

        # Expected A
        $expectedA = " ORDER BY field1 ASC, field2 DESC, toto ASC";

        # Get result B
        $resultB = Mariadb::appendSort($input, "a");

        # Expected B
        $expectedB = " ORDER BY a.field1 ASC, a.field2 DESC, a.toto ASC";

        # Assert
        $this->assertEquals($expectedB, $resultB);

    }

    /**
     * Test Prepare Order By
     * 
     * @return void
     */
    public function testPrepareOrderBy():void {

        # Set input 1
        $input1 = ["test1"];

        # Set expected
        $expected1 = "test1";

        # Set input 1
        $input2 = ["-test2"];

        # Set expected
        $expected2 = "test2 DESC";

        # Set input 1
        $input3 = ["test3", "test4", "-test5"];

        # Set expected
        $expected3 = "test3, test4, test5 DESC";

        # Set input 4
        $input4 = [];

        # Set expected
        $expected4 = "";

        # Test 1
        $this->assertEquals($expected1, Mariadb::prepareOrderBy($input1));

        # Test 2
        $this->assertEquals($expected2, Mariadb::prepareOrderBy($input2));

        # Test 3
        $this->assertEquals($expected3, Mariadb::prepareOrderBy($input3));

        # Test 4
        $this->assertEquals($expected4, Mariadb::prepareOrderBy($input4));

    }

}