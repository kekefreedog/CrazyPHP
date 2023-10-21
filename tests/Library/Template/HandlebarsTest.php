<?php declare(strict_types=1);
/**
 * Test State
 *
 * Test State Classes
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace Tests\Library\Template;

/**
 * Dependances
 */
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Page Test
 *
 * Methods for test page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class HandlebarsTest extends TestCase{
    
    /** Public constants
     ******************************************************
     */

    /** @var string partialDir */
    public const PARTIAL_DIR = "@crazyphp_root/resources/Hbs/Partials";

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
     * Test Page State
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testPageState():void {

        # Load partials
        $partials = Handlebars::loadAppPartials(self::PARTIAL_DIR);

        # Scan directory and retrieve filenames
        $filenames = scandir(File::path(self::PARTIAL_DIR));

        # Prepare result
        $result = [];

        # Check filenames
        if(!empty($filenames))

            # Iteration foreach
            foreach ($filenames as $filename){

                # Get file path
                $filePath = File::path(self::PARTIAL_DIR)."/$filename";
            
                # Get file extension
                $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            
                # Check file has extension
                if(in_array($fileExtension, Handlebars::EXTENSIONS))
                
                    $result[pathinfo($filename, PATHINFO_FILENAME)] = file_get_contents($filePath);

            }

        # Assertion
        $this->assertEquals($partials, $result);

    }

}