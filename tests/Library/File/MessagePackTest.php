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
use CrazyPHP\Library\File\MessagePack;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Message Pack Test
 *
 * Methods for test file methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MessagePackTest extends TestCase {
    
    /** Variables
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
            "phpunit_test"      =>  true,
            "crazyphp_root"     =>  getcwd(),
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

        # Reset env
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Encode
     * 
     * @return void 
     */
    public function testEncodeDecode():void {

        # Set data
        $data = [
            "first"     =>  null,
            "second"    =>  "test",
            "third"     =>  3,
            "fourth"    =>  true
        ];

        # Encode
        $encoded = MessagePack::encode($data);

        # Decode
        $result = MessagePack::decode($encoded);

        # Compare
        $this->assertEquals($data, $result);

    }

    /**
     * Test Create
     * 
     * @return void 
     */
    public function testCreateOpen():void {

        # Set data
        $data = [
            "first"     =>  null,
            "second"    =>  "test",
            "third"     =>  3,
            "fourth"    =>  true,
            "param"     =>  [
                "value"     =>  "deep"
            ]
        ];

        # Set path
        $path = static::TEST_PATH."MessagePack.".MessagePack::FILE_EXT;

        # Create file
        MessagePack::create($path, $data);

        # Check file exists
        $this->assertFileExists(File::path($path));

        # Open file
        $result = MessagePack::open($path);

        # Check result
        $this->assertEquals($result, $data);

    }

    /**
     * Test Has
     * 
     * @return void 
     */
    #[Depends('testCreateOpen')]
    public function testHas():void {

        # Set path
        $path = static::TEST_PATH."MessagePack.".MessagePack::FILE_EXT;

        # Create file
        $result = MessagePack::has($path, "param.value");

        # Check result
        $this->assertTrue($result);

    }

    /**
     * Test Set
     * 
     * @return void 
     */
    #[Depends('testHas')]
    public function testSet():void {

        # Set path
        $path = static::TEST_PATH."MessagePack.".MessagePack::FILE_EXT;

        # Data 2
        $data2 = [
            "param2"     =>  [
                "value"     =>  "deep"
            ]
        ];

        # Create file
        MessagePack::set($path, $data2);

        # Create file
        $result = MessagePack::has($path, "param2.value");

        # Check result
        $this->assertTrue($result);

    }

    /**
     * Test Update
     * 
     * @return void 
     */
    #[Depends('testSet')]
    public function testUpdate():void {

        # Set path
        $path = static::TEST_PATH."MessagePack.".MessagePack::FILE_EXT;

        # Data 2
        $data2 = [
            "param2"     =>  [
                "value"     =>  "newDeep"
            ]
        ];

        # Create file
        MessagePack::set($path, $data2);

        # Create file
        $result = MessagePack::has($path, "param2.value");

        # Check result
        $this->assertTrue($result);

    }

    /**
     * Test Delete
     * 
     * @return void 
     */
    #[Depends('testUpdate')]
    public function testDelete():void {

        # Set data
        $data = [
            "first"     =>  null,
            "second"    =>  "test",
            "third"     =>  3,
            "fourth"    =>  true,
            "param"     =>  [
                "value"     =>  "deep"
            ]
        ];

        # Set path
        $path = static::TEST_PATH."MessagePack.".MessagePack::FILE_EXT;

        # Data 2
        $data2 = ["param2"];

        # Create file
        MessagePack::delete($path, $data2);

        # Create file
        $result = MessagePack::open($path);

        # Check result
        $this->assertEquals($result, $data);

    }
    
    /** Public constants
     ******************************************************
     */

    /* Path */
    public const TEST_PATH = "@crazyphp_root/tests/.cache/cache/";

}