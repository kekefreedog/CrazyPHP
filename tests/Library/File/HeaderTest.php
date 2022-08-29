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

    /**
     * Prepare Cache
     */
    public function prepareCache():void {

        # Setup env
        Env::set([
            # App root for composer class
            "app_root"      =>  getcwd(),
            "phpunit_test"  =>  true,
        ]);

        # Check folder exists
        if(is_dir(self::TEST_PATH))
            
            # Remove cache folder
            File::remove(self::TEST_PATH);

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
    const TEST_PATH = __DIR__."../../../.cache/cache/";

    /* Result */
    const RESULT =
"/**
 * kzarshenas/crazyphp
 *
 * My crazy framework for creating ultra-fast webapps.
 *
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright   2022 kzarshenas/crazyphp
 */
";

}