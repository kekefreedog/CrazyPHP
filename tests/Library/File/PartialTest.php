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
use CrazyPHP\Library\File\Partial;
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
        ];

        # Get all partial from ts
        $result = Partial::getAllFromStyle(static::TEST_SCSS_PATH);

        # Check result
        $this->assertEquals($expect, $result);

    }

    /**
     * Test Get All From Mixed Style Imports
     *
     * @return void
     */
    public function testGetAllFromMixedStyleImports():void {

        # Prepare an isolated stylesheet with both supported loading styles
        $path = tempnam(sys_get_temp_dir(), "crazyphp-partial-");
        $this->assertNotFalse($path);

        try{

            file_put_contents($path, <<<'SCSS'
@import './partial/legacy_widget';
@include meta.load-css("./partial/modern_widget");
@include meta.load-css( './partial/form' );
@import './global';
@include meta.load-css('./global');
SCSS
            );

            # Check discovery and descending name-length order
            $this->assertSame(
                ["LegacyWidget", "ModernWidget", "Form"],
                Partial::getAllFromStyle($path)
            );

        }finally{

            # Remove the fixture even if an assertion fails
            unlink($path);

        }

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
            "Filter/FilterCheckbox",
            "Filter/FilterPassword",
            "Filter/NumberEndpoint",
            "Filter/FilterDefault",
            "Filter/NumberControl",
            "Filter/RangeInterval",
            "Filter/FilterHidden",
            "Filter/FilterNumber",
            "Filter/FilterSelect",
            "Filter/FilterSwitch",
            "Filter/FilterColor",
            "Filter/FilterRadio",
            "Filter/FilterRange",
            "Form/FormSeparator",
            "Filter/FilterDate",
            "Filter/FilterFile",
            "Filter/FilterText",
            "Form/FormCheckbox",
            "Form/FormPassword",
            "Form/FormTextarea",
            "Form/FormDefault",
            "Filter/Operator",
            "Form/FormHidden",
            "Form/FormNumber",
            "Form/FormSelect",
            "Form/FormSwitch",
            "Form/FormChips",
            "Form/FormColor",
            "Form/FormRadio",
            "Form/FormRange",
            "Form/FormDate",
            "Form/FormFile",
            "Form/FormOtp",
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
            0   =>  "PreloaderLinearIndeterminate",
            1   =>  "Navigation",
            2   =>  "Hello",
            3   =>  "Form",
            6   =>  "Filter/FilterCheckbox",
            7   =>  "Filter/FilterPassword",
            8   =>  "Filter/NumberEndpoint",
            9   =>  "Filter/FilterDefault",
            10  =>  "Filter/NumberControl",
            11  =>  "Filter/RangeInterval",
            12  =>  "Filter/FilterHidden",
            13  =>  "Filter/FilterNumber",
            14  =>  "Filter/FilterSelect",
            15  =>  "Filter/FilterSwitch",
            16  =>  "Filter/FilterColor",
            17  =>  "Filter/FilterRadio",
            18  =>  "Filter/FilterRange",
            19  =>  "Form/FormSeparator",
            20  =>  "Filter/FilterDate",
            21  =>  "Filter/FilterFile",
            22  =>  "Filter/FilterText",
            23  =>  "Form/FormCheckbox",
            24  =>  "Form/FormPassword",
            25  =>  "Form/FormTextarea",
            26  =>  "Form/FormDefault",
            27  =>  "Filter/Operator",
            28  =>  "Form/FormHidden",
            29  =>  "Form/FormNumber",
            30  =>  "Form/FormSelect",
            31  =>  "Form/FormSwitch",
            32  =>  "Form/FormChips",
            33  =>  "Form/FormColor",
            34  =>  "Form/FormRadio",
            35  =>  "Form/FormRange",
            36  =>  "Form/FormDate",
            37  =>  "Form/FormFile",
            38  =>  "Form/FormOtp",
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
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form.hbs",
            ],
            # form.hbs's per-item.type sub-partials: template-only entries (no .ts/.scss of
            # their own, they're plain includes rather than fully-registered DOM partials)
            "Filter/FilterPassword"        =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_password.hbs",
            ],
            "Filter/FilterCheckbox"        =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_checkbox.hbs",
            ],
            "Filter/FilterDefault"         =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_default.hbs",
            ],
            "Filter/FilterSelect"          =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_select.hbs",
            ],
            "Filter/FilterSwitch"          =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_switch.hbs",
            ],
            "Filter/FilterHidden"          =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_hidden.hbs",
            ],
            "Filter/FilterNumber"          =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_number.hbs",
            ],
            "Filter/FilterRadio"           =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_radio.hbs",
            ],
            "Filter/FilterRange"           =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_range.hbs",
            ],
            "Filter/FilterColor"           =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_color.hbs",
            ],
            "Form/FormPassword"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_password.hbs",
            ],
            "Form/FormCheckbox"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_checkbox.hbs",
            ],
            "Filter/FilterFile"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_file.hbs",
            ],
            "Filter/FilterDate"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_date.hbs",
            ],
            "Form/FormDefault"             =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_default.hbs",
            ],
            "Form/FormNumber"              =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_number.hbs",
            ],
            "Form/FormHidden"              =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_hidden.hbs",
            ],
            "Form/FormSwitch"              =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_switch.hbs",
            ],
            "Form/FormSelect"              =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_select.hbs",
            ],
            "Form/FormRange"               =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_range.hbs",
            ],
            "Form/FormColor"               =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_color.hbs",
            ],
            "Form/FormRadio"               =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_radio.hbs",
            ],
            "Form/FormDate"                =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_date.hbs",
            ],
            "Form/FormFile"                =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_file.hbs",
            ],
            "Filter/FilterText"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/filter_text.hbs",
            ],
            "Filter/RangeInterval"            =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/_range_interval.hbs",
            ],
            "Filter/Operator"                 =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/_operator.hbs",
            ],
            "Form/FormTextarea"               =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_textarea.hbs",
            ],
            "Form/FormChips"                  =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_chips.hbs",
            ],
            "Filter/NumberEndpoint"           => [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/_number_endpoint.hbs",
            ],
            "Filter/NumberControl"            => [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/filter/_number_control.hbs",
            ],
            "Form/FormSeparator"              => [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_separator.hbs",
            ],
            "Form/FormOtp"                    =>  [
                "template"  => "@crazyphp_root/resources/Hbs/Partials/form/form_otp.hbs",
            ],
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

        # Set expect (resources/Hbs/Partials is no longer flat: form.hbs's per-item.type
        # sub-partials live under nested form/ and filter/ folders)
        $expect = [
            "PreloaderLinearIndeterminate"  =>  "PreloaderLinearIndeterminate",
            "Navigation"                    =>  "Navigation",
            "Hello"                         =>  "Hello",
            "Form"                          =>  "Form",
            "Filter/FilterPassword"         =>  "Filter/FilterPassword",
            "Filter/FilterCheckbox"         =>  "Filter/FilterCheckbox",
            "Filter/FilterDefault"          =>  "Filter/FilterDefault",
            "Filter/FilterSelect"           =>  "Filter/FilterSelect",
            "Filter/FilterSwitch"           =>  "Filter/FilterSwitch",
            "Filter/FilterHidden"           =>  "Filter/FilterHidden",
            "Filter/FilterNumber"           =>  "Filter/FilterNumber",
            "Filter/FilterRadio"            =>  "Filter/FilterRadio",
            "Filter/FilterRange"            =>  "Filter/FilterRange",
            "Filter/FilterColor"            =>  "Filter/FilterColor",
            "Form/FormPassword"             =>  "Form/FormPassword",
            "Form/FormCheckbox"             =>  "Form/FormCheckbox",
            "Filter/FilterFile"             =>  "Filter/FilterFile",
            "Filter/FilterDate"             =>  "Filter/FilterDate",
            "Form/FormDefault"              =>  "Form/FormDefault",
            "Form/FormNumber"               =>  "Form/FormNumber",
            "Form/FormHidden"               =>  "Form/FormHidden",
            "Form/FormSwitch"               =>  "Form/FormSwitch",
            "Form/FormSelect"               =>  "Form/FormSelect",
            "Form/FormRange"                =>  "Form/FormRange",
            "Form/FormColor"                =>  "Form/FormColor",
            "Form/FormRadio"                =>  "Form/FormRadio",
            "Form/FormDate"                 =>  "Form/FormDate",
            "Form/FormFile"                 =>  "Form/FormFile",
            "Filter/FilterText"             =>  "Filter/FilterText",
            "Filter/RangeInterval"          =>  "Filter/RangeInterval",
            "Filter/Operator"               =>  "Filter/Operator",
            "Form/FormTextarea"             =>  "Form/FormTextarea",
            "Form/FormChips"                =>  "Form/FormChips",
            "Form/FormOtp"                  =>  "Form/FormOtp",
            "Filter/NumberEndpoint"           => "Filter/NumberEndpoint",
            "Filter/NumberControl"            => "Filter/NumberControl",
            "Form/FormSeparator"              => "Form/FormSeparator",
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
