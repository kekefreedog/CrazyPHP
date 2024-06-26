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
use CrazyPHP\Library\File\Edl;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Edl Test
 *
 * Methods for test EDL file methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class EdlTest extends TestCase {
    
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

        # Create file
        File::create(
            self::TEST_PATH."testA.edl",
"TITLE: 240531_Edit_DirCut
FCM: NON-DROP FRAME

001  2405RCUT V     C        01:00:04:21 01:00:06:21 00:00:00:00 00:00:02:00 
DLEDL: SEGMENTID:    H_1047827978_S_1717183439_U_435369
FROM CLIP NAME: ith_010
DLEDL: EDIT:0 FILENAME: 240529 EDIT V1.mp4
DLEDL: START TC: 01:00:04:16
DLEDL: PATH: /rdo/shows/sdi0524/_offline/from_client/240531
DLEDL: EDIT:0 ORIGIN: /rdo/shows/sdi0524/_offline/from_client/240531/240529 EDIT V1 DIRCUT.mp4
* UNSUPPORTED EFFECT:0 RESIZE

  "
        );

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
    public function testOpenEdl():void {

        # Get edl content
        $edlContent = Edl::open(self::TEST_PATH."testA.edl");

        # Check if array
        $this->assertIsArray($edlContent);

        # Check title
        $this->assertEquals("240531_Edit_DirCut", $edlContent["title"]);

    }
    
    /** Public constants
     ******************************************************
     */

    /* Path */
    public const TEST_PATH = "@crazyphp_root/tests/.cache/cache/";

}