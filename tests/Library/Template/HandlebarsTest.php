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
use CrazyPHP\Library\Template\Handlebars\Helpers;
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

    /** Public method | Tests Helpers
     ******************************************************
     */

    /**
     * Test Color Suffix
     * 
     * @return void
     */
    /*  public function testColorSuffix():void {

        # Set input 1
        $input1 = "red";
        $output1 = "red-text";

        # Set input 2
        $input2 = "red";
        $output2 = "light-mode-red";

        # Set input 3
        $input3 = "red darken-2";
        $output3 = "red-text text-darken-2";

        # Set input 4
        $input4 = "red darken-2";
        $output4 = "light-mode-red-text text-darken-2-light-mode";

        # Assert 1
        $this->assertEquals(Helpers::colorSuffix($input1, "text"), $output1);

        # Assert 2
        $this->assertEquals(Helpers::colorSuffix($input2, "", "light-mode"), $output2);

        # Assert 3
        $this->assertEquals(Helpers::colorSuffix($input3, "text"), $output3);

        # Assert 3
        $this->assertEquals(Helpers::colorSuffix($input4, "text", "light-mode"), $output4);

    } */

    /**
     * Test Json Stringofy Suffix
     * 
     * @return void
     */
    public function testJsonStringify():void {

        # Set input
        $input = ["toto"=>"titi"];

        # Assert
        $this->assertEquals(Helpers::JSONstringify($input), json_encode($input));

    }

    /**
     * Text Color To Css Class
     * 
     * @return void
     */
    /* public function testColorToCssClass():void {

        # Set input 1
        $input1 = [
            "fill"  =>  "blue",
            "text"  =>  "red",
        ];

        # Set output 1 a & b
        $output1a = "light-mode-blue light-mode-red-text dark-mode-blue-text dark-mode-red ";
        $output1b = "light-mode-blue-text light-mode-red dark-mode-blue dark-mode-red-text ";
        
        # Set input 2
        $input2 = [
            "fill"  =>  "blue darken-1",
            "text"  =>  "red lighten-6",
        ];

        # Set output 1 a & b
        $output2a = "light-mode-blue darken-1-light-mode light-mode-red-text text-lighten-6-light-mode dark-mode-blue-text text-darken-1-dark-mode dark-mode-red lighten-6-dark-mode ";
        $output2b = "light-mode-blue-text text-darken-1-light-mode light-mode-red lighten-6-light-mode dark-mode-blue darken-1-dark-mode dark-mode-red-text text-lighten-6-dark-mode ";
        
        # Set input 2
        $input3 = [
            "fill"  =>  "",
            "text"  =>  "",
        ];

        # Set output 1 a & b
        $output3a = "light-mode-grey darken-1-light-mode light-mode-white-text dark-mode-grey-text text-darken-1-dark-mode dark-mode-white ";
        $output3b = "light-mode-grey-text text-darken-1-light-mode light-mode-white dark-mode-grey darken-1-dark-mode dark-mode-white-text ";

        # Set input 2
        $input4 = "error";

        # Asset 1
        $this->assertEquals($output1a, Helpers::colorToCssClass($input1, false));
        $this->assertEquals($output1b, Helpers::colorToCssClass($input1, 1));

        # Asset 2
        $this->assertEquals($output2a, Helpers::colorToCssClass($input2, "false"));
        $this->assertEquals($output2b, Helpers::colorToCssClass($input2, "true"));

        # Asset 3
        $this->assertEquals($output3a, Helpers::colorToCssClass($input3, ""));
        $this->assertEquals($output3b, Helpers::colorToCssClass($input3, "true"));

        # Asset 4
        $this->assertEquals("error", Helpers::colorToCssClass($input4, false));

    } */

}