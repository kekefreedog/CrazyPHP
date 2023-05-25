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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Header Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class HeaderTest extends TestCase{
    
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

        # Clean cache
        (new Cache("Files"))->clear();

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Prepare Cache
     */
    public function prepareCache():void {

        # New cache
        $this->cache = new Cache("Files");

    }

    /**
     * Test Header PHP
     * 
     * Test PHP Header
     */
    public function testHeaderPhp():void {

        # Check cache
        if(!$this->cache)

            # PRepare cache
            $this->prepareCache();

        # Get Php Header
        $headerPhp = Header::get("php");

        # Check result is equal to header generated
        $this->assertEquals(html_entity_decode($headerPhp), self::RESULT);
            
        # Remove cache folder
        $this->cache->deleteMultiple(["test-string", "test-array", "test-template"]);
        File::remove(self::TEST_PATH);
        
    }
    
    /** Constants
     ******************************************************
     */

    /* Path */
    const TEST_PATH = __DIR__."/../../../.cache/cache/";

    /* Result */
    const RESULT =
"/**
 * kzarshenas/crazyphp
 *
 * My crazy framework for creating ultra-fast webapps.
 *
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright   2023 kzarshenas/crazyphp
 */
";

}