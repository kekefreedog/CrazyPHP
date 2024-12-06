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
use CrazyPHP\Library\Database\Driver\Mangodb;
use CrazyPHP\Library\Database\Driver\Mariadb;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Mariadb Test
 *
 * Methods for test Mongodb
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MariadbTest extends TestCase {
    
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
     * Test Set Entity As Prefix
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testSetEntityAsPrefix():void {

        # Set entity
        $entity = "Router";

        # Set alias
        $alias = "a";

        # Get result
        $result = Mariadb::setEntityAsPrefix($entity, $alias);

        # Check result
        $this->assertEquals("a.name AS Router_name, a.path AS Router_path", $result);

    }

}