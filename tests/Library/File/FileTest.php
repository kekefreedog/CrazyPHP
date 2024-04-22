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

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * File Test
 *
 * Methods for test file methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class FileTest extends TestCase {
    
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
     * test Symlink
     * 
     * @return void
     */
    public function testSymlink():void {

        # Collections
        $collections = [
            # File
            [
                "source"    =>  "@crazyphp_root/composer.json",
                "target"    =>  self::TEST_PATH."/file/composer.json"
            ],
            # Folder
            [
                "source"    =>  "@crazyphp_root/tests",
                "target"    =>  self::TEST_PATH."/directory"
            ],
        ];

        # Iteration collections
        foreach($collections as $value){

            # Create file symlink
            $this->assertTrue(File::symlink($value["source"], $value["target"]));

            # Check is symlink
            $this->assertTrue(File::isSymlink($value["target"]));

        }

        # Catch error
        try {

            # Impossible actio 
            File::symlink("@crazyphp_root/composer.json", self::TEST_PATH);

        }catch(CrazyException $e){

            # Get message
            $message = $e->getMessage();

            # Assert
            $this->assertEquals($message, "Can't link file to directory.");

        }

    }
    
    /** Public constants
     ******************************************************
     */

    /* Path */
    public const TEST_PATH = "@crazyphp_root/tests/.cache/cache/";

}