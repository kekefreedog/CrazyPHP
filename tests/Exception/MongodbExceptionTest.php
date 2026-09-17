<?php declare(strict_types=1);
/**
 * Test Mongodb Exception
 *
 * Test the standard MongoDB exception constructor
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Exception;

/**
 * Dependances
 */
use CrazyPHP\Exception\MongodbException;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use RuntimeException;

/**
 * Mongodb Exception Test
 *
 * Test the standard MongoDB exception constructor
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MongodbExceptionTest extends TestCase{

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

    }


    /**
     * Tear Down After Class
     *
     * This method is called after the last test of this test class is run.
     *
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Standard Exception Constructor
     *
     * Regression coverage for inherited property types and constructor compatibility.
     *
     * @return void
     */
    public function testPreservesStandardExceptionConstructor():void {

        # Set previous exception
        $previous = new RuntimeException("Previous failure");

        # Create exception with the standard third argument
        $exception = new MongodbException("Database setup failed", 255, $previous);

        # Assert message
        $this->assertSame("Database setup failed", $exception->getMessage());
        
        # Assert get code
        $this->assertSame(255, $exception->getCode());
        
        # Assert get previous
        $this->assertSame($previous, $exception->getPrevious());

    }

    /**
     * Test Database Setup Exception
     *
     * Match the two-argument constructor used by Docker database setup.
     *
     * @return void
     */
    public function testDatabaseSetupException():void {

        # Create exception without a previous exception
        $exception = new MongodbException("Database setup failed", 255);

        # Assert get message
        $this->assertSame("Database setup failed", $exception->getMessage());
        
        # Assert get code
        $this->assertSame(255, $exception->getCode());
        
        # Assert get previous
        $this->assertNull($exception->getPrevious());

    }

}
