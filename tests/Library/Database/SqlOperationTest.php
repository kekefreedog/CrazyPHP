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
use CrazyPHP\Library\Database\Operation\SqlOperation;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Sql Operation Test
 *
 * Methods for test Mongodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class SqlOperationTest extends TestCase {
    
    /** Public static parameters
     ******************************************************
     */

    /** @var @sqlOperation */
    public static SqlOperation $sqlOperation;
    
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
            "phpunit_test"      =>  true,
        ]);

        # Set sql operation instance
        static::$sqlOperation = new SqlOperation();

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
     * Test Parse Equal
     * 
     * Test parseEqual function
     * 
     * @return void
     */
    public function testParseEqual():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run("=toto");

        # Check result
        $this->assertEquals("= `toto`", $resultSimple);

        # Get result complex
        $resultComplex = static::$sqlOperation->run("=toto", [
            "prefix"    =>  "Table"
        ]);

        # Check complex result
        $this->assertEquals("= `Tabletoto`", $resultComplex);

        # Check suffix
        $resultComplex = static::$sqlOperation->run("=toto", [
            "suffix"    =>  "Suffix"
        ]);

        # Check complex result
        $this->assertEquals("= `totoSuffix`", $resultComplex);

    }

    /**
     * Test Parse Not Equal
     * 
     * Test parseNotEqual function
     * 
     * @return void
     */
    public function testParseNotEqual():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run("!=toto");

        # Check result
        $this->assertEquals("<> `toto`", $resultSimple);

    }

    /**
     * Test Less Than or Equal
     * 
     * Test parseLessThanOrEqual function
     * 
     * @return void
     */
    public function testParseLessThanOrEqual():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run("<=10");

        # Check result
        $this->assertEquals("<= 10", $resultSimple);

    }

    /**
     * Test Greater Than or Equal
     * 
     * Test parseGreaterThanOrEqual function
     * 
     * @return void
     */
    public function testParseGreaterThanOrEqual():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run(">=10");

        # Check result
        $this->assertEquals(">= 10", $resultSimple);

    }

    /**
     * Test Smaller
     * 
     * Test parseSmaller function
     * 
     * @return void
     */
    public function testParseSmaller():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run("<10");

        # Check result
        $this->assertEquals("< 10", $resultSimple);

    }

    /**
     * Test Greater
     * 
     * Test parseGreater function
     * 
     * @return void
     */
    public function testParseGreater():void {

        # Get result simple
        $resultSimple = static::$sqlOperation->run(">10");

        # Check result
        $this->assertEquals("> 10", $resultSimple);

    }

    /**
     * Test parse between
     * 
     * Test parseBetween function
     * 
     * @return void
     */
    public function testParseBetween():void {

        # Get result A
        $resultA = static::$sqlOperation->run("[1:10]");

        # Check result A
        $this->assertEquals("BETWEEN 1 AND 10", $resultA);

        # Get result B
        $resultB = static::$sqlOperation->run("[A:Z]");

        # Check result B
        $this->assertEquals("BETWEEN 'A' AND 'Z'", $resultB);

    }

    /**
     * Test parse new between
     * 
     * Test parseNotBetween function
     * 
     * @return void
     */
    public function testParseNotBetween():void {

        # Get result A
        $resultA = static::$sqlOperation->run("![1:10]");

        # Check result A
        $this->assertEquals("NOT BETWEEN 1 AND 10", $resultA);

        # Get result B
        $resultB = static::$sqlOperation->run("![A:Z]");

        # Check result B
        $this->assertEquals("NOT BETWEEN 'A' AND 'Z'", $resultB);

    }

}