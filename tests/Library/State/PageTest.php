<?php declare(strict_types=1);
/**
 * Test State
 *
 * Test State Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */

use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\State\Page;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Page Test
 *
 * Methods for test page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class PageTest extends TestCase{
    
    /** Public constants
     ******************************************************
     */

    /** @const array INPUT */
    public const INPUT = [
        "context"   =>  true,
        "cookie"    =>  true,
        "config"    =>  ["Middleware"]
    ];

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
    public function testPageState():void {

        # Set page state instance
        $pageState = new Page(static::INPUT);

        # Get result
        $result = $pageState->render();

        # Prepare result await
        $resultAwait = [
            "_context"  =>  [],
            "_cookies"  =>  [],
            "_config"   =>  Config::get(static::INPUT["config"])
        ];

        # Assert
        $this->assertEquals($result, $resultAwait);

    }

}