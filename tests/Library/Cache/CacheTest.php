<?php declare(strict_types=1);
/**
 * Cache File
 *
 * Test Cache Classes
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
use LightnCandy\LightnCandy as Handlebars;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Structure Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class CacheTest extends TestCase {
    
    /** Variables
     ******************************************************
     */

    /**
     * Instance of the cache
     */
    public Cache|null $cache = null;    
    
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
            "phpunit_test"  =>  true,
            "crazyphp_root" =>  getcwd(),
            "app_root"      =>  getcwd(),
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
        (new Cache("Files", null))->clear();

        # Reset env
        Env::reset();

   }

   /** Public method | Tests
    ******************************************************
    */

    /**
     * Prepare cache
     * 
     * @return void
     */
    public function prepareCache(){

        # New cache
        $this->cache = new Cache("Files", null);

    }

    /**
     * Test Tree Folder Generator
     * 
     * @return void
     */
    public function testNewCache():void {

        # Check cache
        if(!$this->cache)

            # PRepare cache
            $this->prepareCache();

        # Store a string
        $this->cache->set('test-string', self::TEST_STRING, 300);

        # Store an array
        $this->cache->set('test-array', self::TEST_ARRAY, 300);

        # Store a compilate template
        $this->cache->set('test-template', Handlebars::compile(self::TEST_TEMPLATE), 300);

        # Check template has all items
        $this->assertTrue($this->cache->has("test-string"));
        $this->assertTrue($this->cache->has("test-array"));
        $this->assertTrue($this->cache->has("test-template"));

        # Chack same value
        $this->assertSame(self::TEST_STRING, $this->cache->get("test-string"));
        $this->assertSame(self::TEST_ARRAY, $this->cache->get("test-array"));
        $this->assertSame(Handlebars::compile(self::TEST_TEMPLATE), $this->cache->get("test-template"));

        # Get cache value and eval it
        $template = eval($this->cache->get("test-template"));

        # Execute template
        $result = $template(["id" => 1, "name" => "Kevin"]);

        # Check
        $this->assertSame($result, "1 is corresponding to Kevin");
            
        # Remove cache folder
        $this->cache->deleteMultiple(["test-string", "test-array", "test-template"]);
        File::remove(self::TEST_PATH);

    }
    
    /** Constants
     ******************************************************
     */

    /* Path */
    const TEST_PATH = __DIR__."/../../../.cache/cache/";

    /* String */
    const TEST_STRING = 'lorem ipsum';

    /* Array */
    const TEST_ARRAY = [
        [
            "id"    =>  1,
            "name"  =>  "Kevin"
        ],
        [
            "id"    =>  2,
            "name"  =>  "Justine"
        ],
    ];

    /* Template handlebars */
    const TEST_TEMPLATE = "{{id}} is corresponding to {{name}}";

}