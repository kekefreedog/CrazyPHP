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
use CrazyPHP\Library\File\PythonCollection;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Python Collection Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class PythonCollectionTest extends TestCase {

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

    }

    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Python Collection
     */
    public function testEncode():void {

        # Check assert
        $this->assertEquals(static::INPUT, PythonCollection::encode(self::OUTPUT));
        
    }

    /**
     * Test Python Collection
     */
    public function testCheck():void {

        # Check assert
        $this->assertTrue(PythonCollection::check(static::INPUT));
        
    }

    /**
     * Test Python Collection
     */
    public function testDecode():void {

        # Check assert
        $this->assertEquals(static::OUTPUT, PythonCollection::decode(static::INPUT));
        
    }

    /** Public method | Constants
     ******************************************************
     */

    /** @var string INPUT_A */
    public const INPUT = "{'key1': 'O\'Reilly', 'key2': 'He said, \"Hello!\"', 'key3': 'Backslash: \\\ test', 'key4': True, 'key5': None, 'key6': [1, 'Two', 'Three\'s'], 'key7': {'nestedKey1': 'Line with \'quotes\'', 'nestedKey2': 'Backslash \\\ and quotes \'\"'}}";


    /** @var string OUTPUT */
    public const OUTPUT = [
        "key1" => "O'Reilly",
        "key2" => 'He said, "Hello!"',
        "key3" => "Backslash: \\ test",
        "key4" => true,
        "key5" => null,
        "key6" => [1, "Two", "Three's"],
        "key7" => [
            "nestedKey1" => "Line with 'quotes'",
            "nestedKey2" => "Backslash \\ and quotes '\""
        ]
    ];
    

}