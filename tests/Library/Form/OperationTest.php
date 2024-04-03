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
use CrazyPHP\Library\Form\Operation;
use CrazyPHP\Model\Docker\Install;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Header Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class OperationTest extends TestCase{
    
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
     * Test Operation Construct
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testOperationConstruct():void {

        # New operation instance
        $instance = new Operation("");

        # Check operations
        $this->assertEmpty($instance->get());

        # Update instance
        $instance->set();

        # Check operations
        $this->assertEquals(Operation::LIST, $instance->get());

        # Value A
        $valueA = "@>";

        # Update instance
        $instance->set($valueA);

        # Check operations
        $this->assertEquals([$valueA => Operation::LIST[$valueA]], $instance->get());

        # Set value b
        $valueB = ["~*", "*", $valueA];

        # Update instance
        $instance->set($valueB);

        # Check operations
        $this->assertEquals([
            $valueA => Operation::LIST[$valueA],
            "~*" => Operation::LIST["~*"],
            "*" => Operation::LIST["*"],
        ], $instance->get());

    }

    /**
     * Test Operation Run
     * 
     * @return void
     */
    public function testOperationRun():void {

        # New instance
        $instance = new Operation();

        # Run empty
        $result = $instance->run("");

        # Check result
        $this->assertEquals([
            "name"  =>  "default",
            "value" =>  ""
        ] , $result);

        # Run empty
        $result = $instance->run([]);

        # Check result
        $this->assertNull($result);

    }

}