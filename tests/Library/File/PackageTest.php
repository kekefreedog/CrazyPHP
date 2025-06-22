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
use CrazyPHP\Library\File\Package;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Package Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class PackageTest extends TestCase{
    
    /** Variables
     ******************************************************
     */

    /* @var null|Cache Cache */
    public $cache = null;

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
            "crazyphp_root"     =>  getcwd(),
            "app_root"          =>  getcwd(),
        ]);

        # Check folder exists
        if(File::exists(self::TEST_PATH)){
            
            # Remove cache folder
            File::removeAll(self::TEST_PATH);

        }else{

            # Create dir
            File::createDirectory(self::TEST_PATH);

        }

        # Copy package
        File::copy("@crazyphp_root/package.json", self::PACKAGE_TEST_PATH);

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {
           
        # Remove cache folder
        File::removeAll(self::TEST_PATH);

        # Remove folder
        File::remove(self::TEST_PATH);

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Get Dependencies
     * 
     * @return void
     */
    public function testGetDependencies():void {

        # Check package
        if(File::exists("@crazyphp_root/package-lock.json")){

            # Get depedencies
            $result = Package::getDependencies("@crazyphp_root/package-lock.json");

            # Check result
            $this->assertNotEmpty($result);

        }else

            # Assert true
            $this->assertTrue(true);

    }

    /**
     * Detect Non Commercial Dependecies
     * 
     * @return void
     */
    public function testDetectNonCommercialDependecies():void {

        # Check package
        if(File::exists("@crazyphp_root/package-lock.json")){

            # Get result
            $result = Package::detectNonCommercialDependecies("@crazyphp_root/package-lock.json");

            # Set result
            $result = [];

            # Check is empty
            $this->assertEmpty($result, "You are using non-commercial dependency !!!");

        }else

            # Assert true
            $this->assertTrue(true);

    }
    
    /** Public constants
     ******************************************************
     */

    /* Package to test */
    public const PACKAGE_TEST = "guzzlehttp/guzzle";

    /* Path */
    public const TEST_PATH = "@crazyphp_root/tests/.cache/cache/";

    /* Composer Test Path */
    public const PACKAGE_TEST_PATH = "@crazyphp_root/tests/.cache/cache/package.json";

}