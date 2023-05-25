<?php declare(strict_types=1);
/**
 * Test Php Unit
 *
 * Test Php Unit
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependances
 */

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Model\Trash\Delete;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Model test
 *
 * Methods for test interactions with model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class TrashTest extends TestCase {

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
            "trash_path"        =>  self::TRASH_PATH,
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
     * Test Trash Delete
     * 
     * Test model trash delete
     * 
     * @return void
     */
    public function testTrashDelete():void {

        # Set content test
        $contentText = "Hello World";

        # Create file in trash
        if(!File::create(self::TRASH_PATH."/folder/item.txt", $contentText))

            # New error
            throw new CrazyException(
                "Test can't create folder \"/tests/.trash\"... Please check permission of the parent folder.",
                500,
                [
                    "custom_code"   =>  "trash-test-001",
                ]
            );

        # Check file content
        $content = File::read(self::TRASH_PATH."/folder/item.txt") ? true : false;

        # Check content
        $this->assertEquals($content, $contentText);

        # New delete instance
        $delete = new Delete();

        # Execute delete scripts
        $delete->run();

        # Check folder is empty
        $trashEmpty = File::isEmpty(self::TRASH_PATH);

        # Check last operation is true
        $this->assertEquals($trashEmpty, true);

        # Remove trash folder
        File::remove(self::TRASH_PATH);

    }

    /** Public constant
     ******************************************************
     */

    /** @const public TRASH_PATH */
    public const TRASH_PATH = "@crazyphp_root/tests/.trash/";

}