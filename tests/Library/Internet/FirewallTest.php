<?php declare(strict_types=1);
/**
 * Test Internet
 *
 * Test Internet Classes
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
use CrazyPHP\Library\Internet\Firewall;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Firewall Test
 *
 * Methods for test firewall class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class FirewallTest extends TestCase {

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
     * Test White List
     * 
     * @return void
     */
    public function testWhitelist():void {

        # Get white list
        $result = Firewall::whitelist();

        # Asset
        $this->assertEquals(["*"], $result);

    }

    /**
     * Test Black List
     * 
     * @return void
     */
    public function testBlacklist():void {

        # Get white list
        $result = Firewall::blacklist();

        # Asset
        $this->assertEquals([], $result);

    }

}