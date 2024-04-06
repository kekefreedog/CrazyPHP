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
use PHPUnit\Framework\Attributes\Depends;
use CrazyPHP\Library\File\Composer;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Composer Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ComposerTest extends TestCase{
    
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

        # Copy composer
        File::copy("@crazyphp_root/composer.json", self::COMPOSER_TEST_PATH);

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
     * test Require Package
     * 
     * @return void
     */
    public function testRequirePackage():void {

        # Require package
        Composer::requirePackage(self::PACKAGE_TEST, false, false, self::COMPOSER_TEST_PATH);

        # Get require
        $require = Composer::get("require", self::COMPOSER_TEST_PATH);

        # Check 
        $this->assertArrayHasKey(self::PACKAGE_TEST, $require);

    }

    /**
     * Test Remove Package
     * 
     * @return void
     */
    #[Depends("testRequirePackage")]
    public function testRemovePackage():void {

        # Require package
        Composer::removePackage(self::PACKAGE_TEST, false, self::COMPOSER_TEST_PATH);

        # Get require
        $require = Composer::get("require", self::COMPOSER_TEST_PATH);

        # Check 
        $this->assertArrayNotHasKey(self::PACKAGE_TEST, $require);

    }
    
    /** Public constants
     ******************************************************
     */

    /* Package to test */
    public const PACKAGE_TEST = "guzzlehttp/guzzle";

    /* Path */
    public const TEST_PATH = "@crazyphp_root/tests/.cache/cache/";

    /* Composer Test Path */
    public const COMPOSER_TEST_PATH = "@crazyphp_root/tests/.cache/cache/composer.json";

}