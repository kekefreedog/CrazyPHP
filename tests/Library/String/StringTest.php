<?php declare(strict_types=1);
/**
 * Test Json
 *
 * Test Json
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
 */;
use CrazyPHP\Library\String\Strings;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * String test
 *
 * Methods for test interactions with string
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class StringTest extends TestCase{

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
     * Test Is Valid Method
     * 
     * @return void
     */
    public static function testIsValidMethod():void {

        # Check hex
        static::assertTrue(Strings::isValidMethod("CrazyPHP\Library\String\Strings::isValidMethod"));

        # Check rgb
        static::assertFalse(Strings::isValidMethod("toto"));

    }
    
    /** Public constants
     ******************************************************
     */

}