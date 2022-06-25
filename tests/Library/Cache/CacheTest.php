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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use LightnCandy\LightnCandy as Handlebars;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;

/**
 * Structure Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class CacheTest extends TestCase {
    
    /** Variables
     ******************************************************
     */

    /**
     * Instance of the cache
     */
    public $cache = null;

    /** Public method
     ******************************************************
     */

    /**
     * Test Tree Folder Generator
     * 
     * @return void
     */
    public function testNewCache():void {

        # Check folder exists
        if(is_dir(self::TEST_PATH))
            
            # Remove cache folder
            File::remove(self::TEST_PATH);

        # New cache
        $this->cache = new Cache("Files", null, self::TEST_PATH);

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

    /*     $template = ($this->cache->get("test-template"));
        $result = $template(["id" => 1, "name" => "Kevin"]);

        $this->assertSame($result, "1 is corresponding to Kevin"); */

    }
    
    /** Constants
     ******************************************************
     */

    /* Path */
    const TEST_PATH = __DIR__."../../../.cache/cache/";

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