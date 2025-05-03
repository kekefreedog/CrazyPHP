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
use CrazyPHP\Library\File\Partial;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Partial Test
 *
 * Methods for test partial file methods
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class PartialTest extends TestCase {
    
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
     * Test Get All From Script
     * 
     * @return void
     */
    public function testGetAllFromScript():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate",
            "Navigation",
            "Hello",
            "Form"
        ];

        # Get all partial from ts
        $result = Partial::getAllFromScript(static::TEST_TS_PATH);

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get All From Script
     * 
     * @return void
     */
    public function testGetAllFromStyle():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate",
            "Form"
        ];

        # Get all partial from ts
        $result = Partial::getAllFromStyle(static::TEST_SCSS_PATH);

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get All From Script
     * 
     * @return void
     */
    public function testGetAllFromTemplate():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate",
            "Navigation",
            "Hello",
            "Form"
        ];

        # Get all partial from ts
        $result = Partial::getAllFromTemplate(static::TEST_HBS_PATH);

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get All From Script
     * 
     * @return void
     */
    public function testGetAllMinimize():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate",
            "Navigation",
            "Hello",
            "Form"
        ];

        # Get all partial from ts
        $result = Partial::getAll(
            true,
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH
        );

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get All From Script
     * 
     * @return void
     */
    public function testGetAll():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate"  =>  [
                "script"    => "@crazyphp_root/resources/Ts/Environment/Partials/PreloaderLinearIndeterminate.ts",
                "style"     => "@crazyphp_root/resources/Scss/style/partial/_preloader_linear_indeterminate.scss",
                "template"  => "@crazyphp_root/resources/Hbs/Partials/preloader_linear_indeterminate.hbs",
            ],
            "Navigation"                    =>  [
                "script"    => "@crazyphp_root/resources/Ts/Environment/Partials/Navigation.ts",
                "template"  => "@crazyphp_root/resources/Hbs/Partials/navigation.hbs",
            ],
            "Hello"                         =>  [
                "script"    => "@crazyphp_root/resources/Ts/Environment/Partials/Hello.ts",
                "template"  => "@crazyphp_root/resources/Hbs/Partials/hello.hbs",
            ],
            "Form"                          =>  [
                "script"    => "@crazyphp_root/resources/Ts/Environment/Partials/Form.ts",
                "style"     => "@crazyphp_root/resources/Scss/style/partial/_form.scss",
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form.hbs",
            ]
        ];

        # Get all partial from ts
        $result = Partial::getAll(
            false,
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH
        );

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get Form
     * 
     * @return void
     */
    public function testGetForm():void {

        # Set expect
        $expect = [
            "name"      =>  "Form",
            "file"      =>  "form",
            "script"    =>  "@crazyphp_root/resources/Ts/Environment/Partials/Form.ts",
            "style"     =>  "@crazyphp_root/resources/Scss/style/partial/_form.scss",
            "template"  =>  "@crazyphp_root/resources/Hbs/Partials/form.hbs",
        ];

        # Get all partial from ts
        $result = Partial::get(
            "Form",
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH
        );

        # Check result
        $this->assertEquals($expect, $result);

        # Check nul result
        $this->assertNull(Partial::get("NoExisting"));

        # Check not nul result
        $this->assertNotNull(Partial::get(
            "preloader_linear_indeterminate",
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH)
        );

        # Check not nul result
        $this->assertNotNull(Partial::get(
            "_form",
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH)
        );

    }

    /**
     * Test Get Form
     * 
     * @return void
     */
    public function testGetSummary():void {

        # Set expect
        $expect = [
            "PreloaderLinearIndeterminate"  =>  "PreloaderLinearIndeterminate",
            "Navigation"                    =>  "Navigation",
            "Hello"                         =>  "Hello",
            "Form"                          =>  "Form"
        ];

        # Get all partial from ts
        $result = Partial::getSummary(
            static::TEST_TS_PATH,
            static::TEST_SCSS_PATH,
            static::TEST_HBS_PATH
        );

        # Check result
        $this->assertEquals($expect, $result);

    }
    
    /** Public constants
     ******************************************************
     */

    /** @var string TEST_TS_PATH */
    public const TEST_TS_PATH = "@crazyphp_root/resources/Ts/Front/index.ts";

    /** @var string TEST_SCSS_PATH */
    public const TEST_SCSS_PATH = "@crazyphp_root/resources/Scss/style/index.scss";

    /** @var string TEST_HBS_PATH */
    public const TEST_HBS_PATH = "@crazyphp_root/resources/Hbs/Partials";

}