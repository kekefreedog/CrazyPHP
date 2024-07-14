<?php declare(strict_types=1);
/**
 * Test File
 *
 * Test File Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Form\Validate;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Validate Test
 *
 * Methods for test validate methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ValidateTest extends TestCase{
    
    /** Public constants
     ******************************************************
     */

    /** @const array INPUT */
    public const INPUT = [
        "phoneNumber"  =>  [
            "0694450403",
            "+33694454345",
            "toto",
            "0123456789012"
        ]
    ];

    /** @const array RESULT */
    public const OUTPUT = [
        "phoneNumber"  =>  [
            true,
            true,
            false,
            false
        ]
    ];

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
            "env_value"         =>  "env_result",
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
     * Test Is Mobile Phone
     * 
     * @return void
     */
    public function testIsMobilePhone():void {

        # Iteration input
        foreach(self::INPUT["phoneNumber"] as $i => $input)

            # Assert
            $this->assertEquals(self::OUTPUT["phoneNumber"][$i], Validate::isMobilePhone($input), "\"$input\" is not valid");

    }

}