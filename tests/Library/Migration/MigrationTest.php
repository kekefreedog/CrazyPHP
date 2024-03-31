<?php declare(strict_types=1);
/**
 * Test Migration
 *
 * Test Migration Classes
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

use CrazyPHP\Library\Migration\Migration;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Migration Test
 *
 * Methods for test migration class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class MigrationTest extends TestCase {

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
            "crazyphp_root"     =>  getcwd(),
            "phpunit_test"      =>  true,
        ]);

        # Setup env
        Env::set([
            "app_root"          =>  File::path(self::TEMP_FOLDER)
        ]);

        # Copy resources
        File::copy("@crazyphp_root/resources/Test/MigrationTest", self::TEMP_FOLDER);

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Remove temp folder
        File::removeAll(self::TEMP_FOLDER);

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Env And Config Values
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testMigration():void {

        # New migration instance
        $migration = new Migration();

        # Run preview
        $migration->runPreviews();

        # Get action upgradePageRegisterCommand
        $getActionByNameResult = $migration->getActionByName("upgradePageRegisterCommand");

        # Set preview count 
        $previewResult = $getActionByNameResult["_fromPreview"] ?? [];

        # Assert | Check if equal to two value
        $this->assertCount(2, $previewResult);

        # Get action upgradePageRegisterCommand
        $getActionByNameResultSecond = $migration->getActionByName("upgradeGitignore");

        # Set preview count 
        $previewResultSeconnd = $getActionByNameResultSecond["_fromPreview"] ?? [];

        # Assert | Check if equal to two value
        $this->assertCount(1, $previewResultSeconnd);

        # Check $previewResult
        if(!empty($previewResult) && !empty($previewResultSeconnd)){

            # Run migration
            $migration->run();

            # Iteration of preview result
            foreach($previewResult as $preview){

                # Open file
                $fileContent = file_get_contents($preview["parameters"]["file"]);

                # Check if contains result
                $this->assertStringContainsString($preview["parameters"]["replace"], $fileContent);

                # Check if contains search value
                $searchIncluded = strpos($fileContent, $preview["parameters"]["search"]) !== false ? true : false;

                # Check if false
                $this->assertFalse($searchIncluded, "Value \"".$preview["parameters"]["search"]."\" is always included in the file \"".basename($preview["parameters"]["file"]."\""));

            }

            # Iteration of preview result
            foreach($previewResultSeconnd as $preview){

                # Open file
                $fileContent = file_get_contents($preview["parameters"]["file"]);

                # Check if contains result
                $this->assertStringContainsString($preview["parameters"]["add"], $fileContent);

            }

        }

    }

    /** Public method | Tests
     ******************************************************
     */

    /** @const TEMP_FOLDER for test */
    const TEMP_FOLDER = "@crazyphp_root/tests/.temp";

}