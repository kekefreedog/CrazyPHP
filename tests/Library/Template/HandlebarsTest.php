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
 * @copyright  2022-2024 Kévin Zarshenas
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
 * @copyright  2022-2024 Kévin Zarshenas
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

        # Recursively scan directory and retrieve filenames (resources/Hbs/Partials is no
        # longer flat: form.hbs's per-item.type sub-partials live under nested form/ and
        # filter/ folders, so this has to walk subfolders too, not just the top level)
        $basePath = rtrim(File::path(self::PARTIAL_DIR), "/");

        # Prepare result
        $result = [];

        # Iteration of every file found recursively under the partials dir
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)) as $file){

            # Get file extension
            $fileExtension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

            # Check file has extension
            if(in_array($fileExtension, Handlebars::EXTENSIONS)){

                # Get path relative to the partials dir, without its extension (e.g. "form/form_color")
                $relativePath = ltrim(substr($file->getPathname(), strlen($basePath)), "/");
                $name = rtrim(substr($relativePath, 0, -strlen($fileExtension)), ".");

                # Push in result
                $result[$name] = file_get_contents($file->getPathname());

            }

        }

        # Assertion
        $this->assertEquals($partials, $result);

    }

    /**
     * Test Load App Partials Nested Path
     *
     * Regression test for the fix that keys partials by their path relative to the
     * partials directory (instead of by basename only). Uses the real form/filter
     * sub-partials shipped under resources/Hbs/Partials/{form,filter}/ -- one partial
     * per form item.type, included by form.hbs's dispatch chain -- rather than a
     * synthetic fixture, now that real nested partials exist in this repo.
     *
     * @return void
     */
    public function testLoadAppPartialsNestedPath():void {

        # Load partials from the real partials dir
        $partials = Handlebars::loadAppPartials(self::PARTIAL_DIR);

        # A nested partial must be keyed by its relative path, not its basename
        $this->assertArrayHasKey("form/form_color", $partials);
        $this->assertArrayNotHasKey("form_color", $partials);

        $this->assertArrayHasKey("filter/filter_color", $partials);
        $this->assertArrayNotHasKey("filter_color", $partials);

        # A flat partial must still be keyed by its plain basename
        $this->assertArrayHasKey("form", $partials);

        # Content should match the files on disk
        $this->assertEquals(
            File::open(self::PARTIAL_DIR."/form/form_color.hbs"),
            $partials["form/form_color"]
        );
        $this->assertEquals(
            File::open(self::PARTIAL_DIR."/filter/filter_color.hbs"),
            $partials["filter/filter_color"]
        );

    }

    /** Public method | Tests Helpers
     ******************************************************
     */

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
     * Test Expand Color Fill
     * 
     * @return void
     */
    public function testExpandColorFill():void {

        # Set input 1
        $input1 = [
            "fill"  =>  "blue",
            "text"  =>  "red",
        ];

        # Set output1
        $output1 = "light-mode-blue dark-mode-red ";

        # Set input 2
        $input2 = [
            "fill"  =>  "blue darken-1",
            "text"  =>  "red lighten-6",
        ];

        # Set output2
        $output2 = "light-mode-blue darken-1-light-mode dark-mode-red lighten-6-dark-mode ";

        # Set input 3
        $input3 = [
            "fill"  =>  "blue",
            "text"  =>  "red",
        ];

        # Set output 3
        $output3 = "light-mode-blue-text dark-mode-red-text ";

        # Set input 4
        $input4 = [
            "fill"  =>  "blue darken-1",
            "text"  =>  "red lighten-6",
        ];

        # Set output 4
        $output4 = "light-mode-blue-border border-darken-1-light-mode dark-mode-red-border border-lighten-6-dark-mode ";

        # Assert
        $this->assertEquals(Helpers::expandColorFill($input1), $output1);
        $this->assertEquals(Helpers::expandColorFill($input2), $output2);
        $this->assertEquals(Helpers::expandColorFill($input3, "text"), $output3);
        $this->assertEquals(Helpers::expandColorFill($input4, "border"), $output4);

    }

    /**
     * Test Expand Color Text
     * 
     * @return void
     */
    public function testExpandColorText():void {

        # Set input 1
        $input1 = [
            "text"  =>  "blue",
            "fill"  =>  "red",
        ];

        # Set output1
        $output1 = "light-mode-blue dark-mode-red ";

        # Set input 2
        $input2 = [
            "text"  =>  "blue darken-1",
            "fill"  =>  "red lighten-6",
        ];

        # Set output2
        $output2 = "light-mode-blue darken-1-light-mode dark-mode-red lighten-6-dark-mode ";

        # Set input 3
        $input3 = [
            "text"  =>  "blue",
            "fill"  =>  "red",
        ];

        # Set output 3
        $output3 = "light-mode-blue-text dark-mode-red-text ";

        # Set input 4
        $input4 = [
            "text"  =>  "blue darken-1",
            "fill"  =>  "red lighten-6",
        ];

        # Set output 4
        $output4 = "light-mode-blue-border border-darken-1-light-mode dark-mode-red-border border-lighten-6-dark-mode ";

        # Assert
        $this->assertEquals(Helpers::expandColorText($input1), $output1);
        $this->assertEquals(Helpers::expandColorText($input2), $output2);
        $this->assertEquals(Helpers::expandColorText($input3, "text"), $output3);
        $this->assertEquals(Helpers::expandColorText($input4, "border"), $output4);

    }

    /**
     * Test Color To Css Class
     * 
     * @return void
     */
    public function testExpandColor():void {

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
        $this->assertEquals($output1a, Helpers::expandColor($input1, true));
        $this->assertEquals($output1b, Helpers::expandColor($input1, 0));

        # Asset 2
        $this->assertEquals($output2a, Helpers::expandColor($input2, "true"));
        $this->assertEquals($output2b, Helpers::expandColor($input2, "false"));

        # Asset 3
        $this->assertEquals($output3a, Helpers::expandColor($input3, "true"));
        $this->assertEquals($output3b, Helpers::expandColor($input3, ""));

        # Asset 4
        $this->assertEquals("", Helpers::expandColor($input4, true));

    }

}