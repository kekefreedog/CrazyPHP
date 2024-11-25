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
 */
use PHPUnit\Framework\Attributes\Depends;
use CrazyPHP\Library\String\Color;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Color test
 *
 * Methods for test interactions with color
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ColorTest extends TestCase{

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
     * test Color Package
     * 
     * @return void
     */
    public static function testColorInstance():void {

        # New instance
        $color = new Color('rgba(93,111,222,0.33)');

        # Not numfmt_get_locale
        static::assertNotNull($color);

    }

    /**
     * Test Remove Package
     * 
     * @return void
     */
    public static function testToHex():void {

        # New instance
        $color = new Color('rgb(76,175,80)');

        # Convert
        $result = $color->toHex();

        # Check
        static::assertEquals("#4caf50", $result);

    }

    /**
     * Test Remove Package
     * 
     * @return void
     */
    public static function testToHexa():void {

        # New instance
        $color = new Color('hsla(300,100,50,1.0)');

        # Convert
        $result = (string) $color->toHexa();

        # Check
        static::assertEquals("#ff00ffff", $result);

    }
    
    /** Public constants
     ******************************************************
     */

}