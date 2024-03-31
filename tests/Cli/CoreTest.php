<?php declare(strict_types=1);
/**
 * Test Cli
 *
 * Test State Classes
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
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use CrazyPHP\Cli\Core;

/**
 * Core Cli
 *
 * Methods for test core of cli
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class CoreTest extends TestCase{
    
    /** Public constants
     ******************************************************
     */

    /** @const array INPUT */
    public const INPUT = [];

    /** @const array RESULT */
    public const RESULT = [];

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
     * Test Page State
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testGetRouters():void {

        # Set page state instance
        $routersFirst = Core::getRouters();

        # Get config from file
        $routersFromConfig = File::open("@crazyphp_root/resources/Yml/CliRouter.yml");

        # Check routers match
        $this->assertEquals($routersFirst, $routersFromConfig);

    }

}