<?php declare(strict_types=1);
/**
 * Test Handlebars
 *
 * Verify template helpers and partial loading
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
use LightnCandy\LightnCandy;
use CrazyPHP\Model\Env;

/**
 * Handlebars Test
 *
 * Verify server-rendered templates and helper output
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class HandlebarsTest extends TestCase {

    /** Public Constants
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
            "crazyphp_root" => getcwd(),
            "phpunit_test" => true,
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

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Page State
     *
     * Verify recursive partial loading and legacy aliases.
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

                # Include legacy aliases for underscore-prefixed partials
                $alias = preg_replace("~(^|/)_+([^/]+)$~", "$1$2", $name);

                # Set result
                $result[$alias] = $result[$name];

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

    /**
     * Test Load Underscore Partials
     *
     * Test underscore partial names and their legacy aliases during compilation.
     *
     * @return void
     */
    public function testLoadUnderscorePartials():void {

        # Set directory
        $directory = "@crazyphp_root/tests/.cache/crazyphp-partials-".uniqid();

        # Create directory
        File::createDirectory($directory."/filter");

        # Try
        try{

            # Put content
            File::create($directory."/filter/_operator.hbs", "Operator {{value}}");

            # Get partials
            $partials = Handlebars::loadAppPartials($directory);

            # Check operator
            $this->assertSame("Operator {{value}}", $partials["filter/_operator"]);

            # Check operator
            $this->assertSame($partials["filter/_operator"], $partials["filter/operator"]);

            # Compile template
            $compiled = LightnCandy::compile(
                "{{> filter/_operator}}|{{> filter/operator}}",
                [
                    "flags"    => Handlebars::CRAZY_PRESET["flags"] | LightnCandy::FLAG_ERROR_EXCEPTION,
                    "partials" => $partials,
                ]
            );

            # Check is string
            $this->assertIsString($compiled);

            # Render
            $render = LightnCandy::prepare($compiled);

            # Assert same
            $this->assertSame("Operator equal|Operator equal", $render(["value" => "equal"]));

        }
        # Finally
        finally{

            # Remove test directory and its contents
            File::remove($directory);

        }

    }

    /** Public method | Tests Helpers
     ******************************************************
     */

    /**
     * Test JSON Stringify
     *
     * @return void
     */
    public function testJsonStringify():void {

        # Set input
        $input = ["toto" => "titi"];

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
            "fill" => "blue",
            "text" => "red",
        ];

        # Set output1
        $output1 = "light-mode-blue dark-mode-red ";

        # Set input 2
        $input2 = [
            "fill" => "blue darken-1",
            "text" => "red lighten-6",
        ];

        # Set output2
        $output2 = "light-mode-blue darken-1-light-mode dark-mode-red lighten-6-dark-mode ";

        # Set input 3
        $input3 = [
            "fill" => "blue",
            "text" => "red",
        ];

        # Set output 3
        $output3 = "light-mode-blue-text dark-mode-red-text ";

        # Set input 4
        $input4 = [
            "fill" => "blue darken-1",
            "text" => "red lighten-6",
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
            "text" => "blue",
            "fill" => "red",
        ];

        # Set output1
        $output1 = "light-mode-blue dark-mode-red ";

        # Set input 2
        $input2 = [
            "text" => "blue darken-1",
            "fill" => "red lighten-6",
        ];

        # Set output2
        $output2 = "light-mode-blue darken-1-light-mode dark-mode-red lighten-6-dark-mode ";

        # Set input 3
        $input3 = [
            "text" => "blue",
            "fill" => "red",
        ];

        # Set output 3
        $output3 = "light-mode-blue-text dark-mode-red-text ";

        # Set input 4
        $input4 = [
            "text" => "blue darken-1",
            "fill" => "red lighten-6",
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
            "fill" => "blue",
            "text" => "red",
        ];

        # Set output 1 a & b
        $output1a = "light-mode-blue light-mode-red-text dark-mode-blue-text dark-mode-red ";
        $output1b = "light-mode-blue-text light-mode-red dark-mode-blue dark-mode-red-text ";

        # Set input 2
        $input2 = [
            "fill" => "blue darken-1",
            "text" => "red lighten-6",
        ];

        # Set output 1 a & b
        $output2a = "light-mode-blue darken-1-light-mode light-mode-red-text text-lighten-6-light-mode dark-mode-blue-text text-darken-1-dark-mode dark-mode-red lighten-6-dark-mode ";
        $output2b = "light-mode-blue-text text-darken-1-light-mode light-mode-red lighten-6-light-mode dark-mode-blue darken-1-dark-mode dark-mode-red-text text-lighten-6-dark-mode ";

        # Set input 2
        $input3 = [
            "fill" => "",
            "text" => "",
        ];

        # Set output 1 a & b
        $output3a = "light-mode-grey darken-1-light-mode light-mode-white-text dark-mode-grey-text text-darken-1-dark-mode dark-mode-white ";
        $output3b = "light-mode-grey-text text-darken-1-light-mode light-mode-white dark-mode-grey darken-1-dark-mode dark-mode-white-text ";

        # Set input 2
        $input4 = "error";

        # Assert 1
        $this->assertEquals($output1a, Helpers::expandColor($input1, true));
        $this->assertEquals($output1b, Helpers::expandColor($input1, 0));

        # Assert 2
        $this->assertEquals($output2a, Helpers::expandColor($input2, "true"));
        $this->assertEquals($output2b, Helpers::expandColor($input2, "false"));

        # Assert 3
        $this->assertEquals($output3a, Helpers::expandColor($input3, "true"));
        $this->assertEquals($output3b, Helpers::expandColor($input3, ""));

        # Assert 4
        $this->assertEquals("", Helpers::expandColor($input4, true));

    }

    /**
     * Test Number Filter Rendering
     *
     * Render numeric bounds and the single-value control on the server.
     *
     * @return void
     */
    public function testNumberFilterRendering():void {

        # Load the number filter partials
        $partials = Handlebars::loadAppPartials(self::PARTIAL_DIR);

        # Compile the server template with its helpers
        $compiled = LightnCandy::compile("{{> filter/filter_number}}", [
            "flags"    => Handlebars::CRAZY_PRESET["flags"] | LightnCandy::FLAG_ERROR_EXCEPTION,
            "partials" => $partials,
            "helpers" => Helpers::listArray(),
        ]);

        # Prepare the renderer and field context
        $render = LightnCandy::prepare($compiled);
        $base = [
            "name"  => "amount",
            "label" => "Amount",
            "form" => ["id" => "numbers"],
        ];

        # Render labeled min and max bounds
        $output = $render($base + [
            "_style" => [
                "number" => [
                    "min" => 0,
                    "max" => ["label" => "At most", "value" => 50],
                ],
            ],
        ]);

        # Assert the paired controls share one encoded value
        $this->assertStringContainsString('value="&#x3D;[0:50]"', $output);
        $this->assertStringContainsString('data-number-bound="min"', $output);
        $this->assertStringContainsString('data-number-bound="max"', $output);
        $this->assertStringContainsString(">At most</label>", $output);
        $this->assertStringNotContainsString('class="filter-operator ', $output);
        $this->assertSame(1, substr_count($output, 'name="amount"'));

        # Render the ordinary single-value filter
        $output = $render($base + ["default" => 5]);

        # Assert the ordinary control retains its operator
        $this->assertStringNotContainsString("data-filter-number-bounds", $output);
        $this->assertStringContainsString('value="5"', $output);
        $this->assertStringContainsString('data-operator-name="amount"', $output);

    }

}
