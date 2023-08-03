<?php declare(strict_types=1);
/**
 * Test Migration
 *
 * Test Migration Classes
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
use CrazyPHP\Library\Migration\Migration;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Migration Test
 *
 * Methods for test migration class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class MigrationTest extends TestCase {

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
     * Test Env And Config Values
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testMigration():void {

        # New migration instance
        $migration = new Migration();

    }

}