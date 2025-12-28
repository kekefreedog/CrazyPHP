<?php declare(strict_types=1);
/**
 * Test System
 *
 * Test System
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Array;

/**
 * Dependances
 */
use CrazyPHP\Library\System\Uuid;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Uuid test
 *
 * Methods for test uuid methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class UuidTest extends TestCase{

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
     * Test Color Valid
     * 
     * @return void
     */
    public static function testIsValid():void {

        # Assert
        static::assertTrue(Uuid::isValid(Uuid::create()));

    }

}