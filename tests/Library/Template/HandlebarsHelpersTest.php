<?php declare(strict_types=1);
/**
 * Test Handlebars Helpers
 *
 * Test inline and block helpers used by LightnCandy
 *
 * PHP version 8.3
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
use CrazyPHP\Exception\CrazyException;
use PHPUnit\Framework\TestCase;
use LightnCandy\LightnCandy;
use CrazyPHP\Model\Env;
use ReflectionMethod;
use ReflectionClass;

/**
 * Handlebars Helpers Test
 *
 * Methods for test Handlebars helpers
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class HandlebarsHelpersTest extends TestCase{

    /** Private parameters
     ******************************************************
     */

    /** @var string $timezone Original timezone */
    private static string $timezone;

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
            # Set the framework root for test resources
            "crazyphp_root"     =>  dirname(__DIR__, 3),
            # Enable test mode
            "phpunit_test"      =>  true,
        ]);

        # Save the original timezone
        self::$timezone = date_default_timezone_get();

        # Set UTC for deterministic date formatting
        date_default_timezone_set("UTC");

    }


    /**
     * Tear Down After Class
     *
     * This method is called after the last test of this test class is run.
     *
     * @return void
     */
    public static function tearDownAfterClass():void {

        # Restore the original timezone
        date_default_timezone_set(self::$timezone);

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test List Array
     *
     * @return void
     */
    public function testListArray():void {

        # Get registered helpers
        $helpers = Helpers::listArray();

        # Assert discovery is not registered as a template helper
        $this->assertArrayNotHasKey("listArray", $helpers);

        # Assert helpers are available
        $this->assertNotEmpty($helpers);

        # Inspect each registered helper
        foreach($helpers as $name => $callable){

            # Assert the registered callback identifies the helper method
            $this->assertSame(Helpers::class."::".$name, $callable);

            # Assert the registered callback is callable
            $this->assertTrue(is_callable($callable));

            # Calendar-dependent helper coverage is intentionally excluded
            if($name !== "daysOfMonth")

                # Assert each included helper has a dedicated test
                $this->assertTrue(method_exists($this, "test".ucfirst($name)), "Missing helper test: ".$name);
        }

        # Get all public helper methods
        $methods = (new ReflectionClass(Helpers::class))->getMethods(ReflectionMethod::IS_PUBLIC);

        # Assert all public helper methods are registered
        $this->assertCount(count($methods) - 1, $helpers);

    }

    /**
     * Test Http Status Code
     *
     * @return void
     */
    public function testHttpStatusCode():void {

        # Assert the title for a known status
        $this->assertSame("Not Found", $this->httpStatusCode(["code" => 404], "title"));

        # Render the complete status collection with custom detail
        $result = $this->httpStatusCode(["code" => 404, "detail" => "Missing shot"], "*");

        # Assert the requested status code is preserved
        $this->assertSame(404, $result["code"]);

        # Assert custom detail replaces the default description
        $this->assertSame("Missing shot", $result["description"]);

        # Assert the icon class
        $this->assertSame("material-icons", $result["icon-class"]);

        # Assert the icon name
        $this->assertSame("power", $result["icon-text"]);

        # Assert the primary color
        $this->assertSame("black", $result["primary-color"]);

        # Assert the secondary color
        $this->assertSame("orange", $result["secondary-color"]);

        # Assert the server error title
        $this->assertSame("Internal Server Error", $this->httpStatusCode(["code" => 500], "title"));

    }

    /**
     * Test Http Status Code Invalid Code
     *
     * @return void
     */
    public function testHttpStatusCodeInvalidCode():void {

        # Expect an invalid status request to be rejected
        $this->expectException(CrazyException::class);

        # Expect the helper error status code
        $this->expectExceptionCode(500);

        # Invoke the helper with the invalid request
        $this->httpStatusCode(["code" => 999], "title");

    }

    /**
     * Test Http Status Code Invalid Field
     *
     * @return void
     */
    public function testHttpStatusCodeInvalidField():void {

        # Expect an invalid status request to be rejected
        $this->expectException(CrazyException::class);

        # Expect the helper error status code
        $this->expectExceptionCode(500);

        # Invoke the helper with the invalid request
        $this->httpStatusCode(["code" => 404], "missing");

    }

    /**
     * Test Color Suffix
     *
     * @return void
     */
    public function testColorSuffix():void {

        # Assert a suffix is added to a simple color
        $this->assertSame("blue-text", Helpers::colorSuffix("blue", "text"));

        # Assert a suffix is applied to a color with a shade
        $this->assertSame("blue-text text-darken-1", Helpers::colorSuffix("blue darken-1", "text"));

        # Assert null input is preserved
        $this->assertNull(Helpers::colorSuffix(null, "text"));

    }

    /**
     * Test Color Prefix
     *
     * @return void
     */
    public function testColorPrefix():void {

        # Assert a prefix is added to a simple color
        $this->assertSame("light-mode-blue", Helpers::colorPrefix("blue", "light-mode"));

        # Assert a prefix is applied to a color with a shade
        $this->assertSame("light-mode-blue darken-1-light-mode", Helpers::colorPrefix("blue darken-1", "light-mode"));

        # Assert non-string input is preserved
        $this->assertSame(42, Helpers::colorPrefix(42, "light-mode"));

    }

    /**
     * Test Expand Color Fill
     *
     * @return void
     */
    public function testExpandColorFill():void {

        # Assert fill colors for light and dark modes
        $this->assertSame("light-mode-blue dark-mode-red ", Helpers::expandColorFill(["fill" => "blue", "text" => "red"]));

        # Assert null input produces no classes
        $this->assertSame("", Helpers::expandColorFill(null));

    }

    /**
     * Test Expand Color Text
     *
     * @return void
     */
    public function testExpandColorText():void {

        # Assert text colors for light and dark modes
        $this->assertSame("light-mode-red dark-mode-blue ", Helpers::expandColorText(["fill" => "blue", "text" => "red"]));

        # Assert null input produces no classes
        $this->assertSame("", Helpers::expandColorText(null));

    }

    /**
     * Test Expand Color
     *
     * @return void
     */
    public function testExpandColor():void {

        # Assert the default fill and text classes
        $this->assertSame("light-mode-blue-text light-mode-red dark-mode-blue dark-mode-red-text ", Helpers::expandColor(["fill" => "blue", "text" => "red"]));

        # Assert inverse mode swaps fill and text classes
        $this->assertSame("light-mode-blue light-mode-red-text dark-mode-blue-text dark-mode-red ", Helpers::expandColor(["fill" => "blue", "text" => "red"], true));

        # Assert null input produces no classes
        $this->assertSame("", Helpers::expandColor(null));

    }

    /**
     * Test Color Theme Suffix
     *
     * @return void
     */
    public function testColorThemeSuffix():void {

        # Assert theme and suffix are combined
        $this->assertSame("light-mode-blue-text", Helpers::colorThemeSuffix("blue", "text", "light"));

        # Assert the theme works without a suffix
        $this->assertSame("dark-mode-red", Helpers::colorThemeSuffix("red", "", "dark"));

        # Assert null input is preserved
        $this->assertNull(Helpers::colorThemeSuffix(null, "text", "light"));

    }

    /**
     * Test Is
     *
     * @return void
     */
    public function testIs():void {

        # Assert equivalent values render the block
        $this->assertSame("yes", Helpers::is(2, "2", $this->blockOptions()));

        # Assert different values render the inverse block
        $this->assertSame("no", Helpers::is(1, 2, $this->blockOptions()));

        # Assert exact comparison distinguishes integers and strings
        $this->assertSame("no", Helpers::is(2, "2", $this->blockOptions(["exact" => true])));

    }

    /**
     * Test Isnt
     *
     * @return void
     */
    public function testIsnt():void {

        # Assert different values render the block
        $this->assertSame("yes", Helpers::isnt(1, 2, $this->blockOptions()));

        # Assert equivalent values render the inverse block
        $this->assertSame("no", Helpers::isnt(2, "2", $this->blockOptions()));

        # Assert exact comparison distinguishes integers and strings
        $this->assertSame("yes", Helpers::isnt(2, "2", $this->blockOptions(["exact" => true])));

    }

    /**
     * Test And
     *
     * @return void
     */
    public function testAnd():void {

        # Assert two true values render the block
        $this->assertSame("yes", Helpers::and(true, true, $this->blockOptions()));

        # Assert a false operand renders the inverse block
        $this->assertSame("no", Helpers::and(true, false, $this->blockOptions()));

    }

    /**
     * Test Or
     *
     * @return void
     */
    public function testOr():void {

        # Assert one true operand renders the block
        $this->assertSame("yes", Helpers::or(false, true, $this->blockOptions()));

        # Assert two false operands render the inverse block
        $this->assertSame("no", Helpers::or(false, false, $this->blockOptions()));

    }

    /**
     * Test Gt
     *
     * @return void
     */
    public function testGt():void {

        # Assert a greater value renders the block
        $this->assertSame("yes", Helpers::gt(3, 2, $this->blockOptions()));

        # Assert equality renders the inverse block
        $this->assertSame("no", Helpers::gt(2, 2, $this->blockOptions()));

    }

    /**
     * Test Gte
     *
     * @return void
     */
    public function testGte():void {

        # Assert equality renders the block
        $this->assertSame("yes", Helpers::gte(2, 2, $this->blockOptions()));

        # Assert a smaller value renders the inverse block
        $this->assertSame("no", Helpers::gte(1, 2, $this->blockOptions()));

    }

    /**
     * Test Lt
     *
     * @return void
     */
    public function testLt():void {

        # Assert a smaller value renders the block
        $this->assertSame("yes", Helpers::lt(1, 2, $this->blockOptions()));

        # Assert equality renders the inverse block
        $this->assertSame("no", Helpers::lt(2, 2, $this->blockOptions()));

    }

    /**
     * Test Lte
     *
     * @return void
     */
    public function testLte():void {

        # Assert equality renders the block
        $this->assertSame("yes", Helpers::lte(2, 2, $this->blockOptions()));

        # Assert a greater value renders the inverse block
        $this->assertSame("no", Helpers::lte(3, 2, $this->blockOptions()));

    }

    /**
     * Test JSONstringify
     *
     * @return void
     */
    public function testJSONstringify():void {

        # Assert array values are encoded as JSON
        $this->assertSame('{"name":"Ada","active":true}', Helpers::JSONstringify(["name" => "Ada", "active" => true]));

        # Assert null is encoded as JSON null
        $this->assertSame("null", Helpers::JSONstringify(null));

    }

    /**
     * Test Resolve
     *
     * @return void
     */
    public function testResolve():void {

        # Assert an existing file resolves to its real path
        $this->assertSame(realpath(__FILE__), Helpers::resolve(__FILE__));

        # Assert a missing path is preserved
        $this->assertSame("missing-template.hbs", Helpers::resolve("missing-template.hbs"));

        # Assert null input is preserved
        $this->assertNull(Helpers::resolve(null));

    }

    /**
     * Test In Array
     *
     * @return void
     */
    public function testInArray():void {

        # Assert a matching value renders the block
        $this->assertSame("yes", Helpers::inArray(["a", "b"], "b", $this->blockOptions()));

        # Assert a missing value renders the inverse block
        $this->assertSame("no", Helpers::inArray(["a"], "b", $this->blockOptions()));

        # Assert invalid collection input renders the inverse block
        $this->assertSame("no", Helpers::inArray(null, "b", $this->blockOptions()));

    }

    /**
     * Test Length
     *
     * @return void
     */
    public function testLength():void {

        # Assert string length
        $this->assertSame(3, Helpers::length("abc"));

        # Assert array length
        $this->assertSame(2, Helpers::length([1, 2]));

        # Assert null input has zero length
        $this->assertSame(0, Helpers::length(null));

    }

    /**
     * Test First
     *
     * @return void
     */
    public function testFirst():void {

        # Assert the first requested items are returned
        $this->assertSame([1, 2], Helpers::first([1, 2, 3], 2));

        # Assert an empty list stays empty
        $this->assertSame([], Helpers::first([], 2));

        # Assert invalid input returns an empty string
        $this->assertSame("", Helpers::first(null, 2));

    }

    /**
     * Test Last
     *
     * @return void
     */
    public function testLast():void {

        # Assert the last requested items are returned
        $this->assertSame([2, 3], Helpers::last([1, 2, 3], 2));

        # Assert an empty list stays empty
        $this->assertSame([], Helpers::last([], 2));

        # Assert invalid input returns an empty string
        $this->assertSame("", Helpers::last(null, 2));

    }

    /**
     * Test Timecode To Frame
     *
     * @return void
     */
    public function testTimecodeToFrame():void {

        # Assert valid timecode converts to frames
        $this->assertSame(90012, Helpers::timecodeToFrame("01:00:00:12", 25));

        # Assert invalid minutes preserve the timecode
        $this->assertSame("00:60:00:00", Helpers::timecodeToFrame("00:60:00:00", 25));

        # Assert frames outside the frame rate preserve the timecode
        $this->assertSame("00:00:00:25", Helpers::timecodeToFrame("00:00:00:25", 25));

        # Assert malformed timecode is preserved
        $this->assertSame("bad", Helpers::timecodeToFrame("bad", 25));

        # Assert a zero frame rate preserves the timecode
        $this->assertSame("00:00:01:00", Helpers::timecodeToFrame("00:00:01:00", 0));

        # Assert null timecode is preserved
        $this->assertNull(Helpers::timecodeToFrame(null, 25));

    }

    /**
     * Test Round
     *
     * @return void
     */
    public function testRound():void {

        # Assert a decimal rounds to the nearest integer
        $this->assertSame(3.0, Helpers::round(2.6));

        # Assert a numeric string is rounded
        $this->assertSame(12.0, Helpers::round("12"));

        # Assert nonnumeric text is preserved
        $this->assertSame("bad", Helpers::round("bad"));

    }

    /**
     * Test Round Decimal
     *
     * @return void
     */
    public function testRoundDecimal():void {

        # Assert a decimal rounds to one decimal place
        $this->assertSame(2.7, Helpers::roundDecimal(2.66));

        # Assert nonnumeric text is preserved
        $this->assertSame("bad", Helpers::roundDecimal("bad"));

    }

    /**
     * Test Uppercase
     *
     * @return void
     */
    public function testUppercase():void {

        # Assert accented text converts to uppercase
        $this->assertSame("ÉCOLE", Helpers::uppercase("école"));

        # Assert only string items in an array are converted
        $this->assertSame(["ADA", 7], Helpers::uppercase(["Ada", 7]));

        # Assert null input is preserved
        $this->assertNull(Helpers::uppercase(null));

    }

    /**
     * Test Lowercase
     *
     * @return void
     */
    public function testLowercase():void {

        # Assert accented text converts to lowercase
        $this->assertSame("école", Helpers::lowercase("ÉCOLE"));

        # Assert only string items in an array are converted
        $this->assertSame(["ada", 7], Helpers::lowercase(["ADA", 7]));

        # Assert null input is preserved
        $this->assertNull(Helpers::lowercase(null));

    }

    /**
     * Test Capitalize
     *
     * @return void
     */
    public function testCapitalize():void {

        # Assert the first character is capitalized
        $this->assertSame("Hello world", Helpers::capitalize("hello world"));

        # Assert non-string input is preserved
        $this->assertSame(7, Helpers::capitalize(7));

    }

    /**
     * Test Join
     *
     * @return void
     */
    public function testJoin():void {

        # Assert the default separator
        $this->assertSame("a, b", Helpers::join(["a", "b"]));

        # Assert a custom separator
        $this->assertSame("a-b", Helpers::join(["a", "b"], "-"));

        # Assert an invalid separator uses the default
        $this->assertSame("a, b", Helpers::join(["a", "b"], null));

        # Assert string input is preserved
        $this->assertSame("a", Helpers::join("a"));

        # Assert null input returns an empty string
        $this->assertSame("", Helpers::join(null));

    }

    /**
     * Test Replace
     *
     * @return void
     */
    public function testReplace():void {

        # Assert all matching substrings are replaced
        $this->assertSame("b b", Helpers::replace("a a", "a", "b"));

        # Assert an invalid search value preserves the input
        $this->assertSame("unchanged", Helpers::replace("unchanged", null, "b"));

    }

    /**
     * Test Split
     *
     * @return void
     */
    public function testSplit():void {

        # Assert a string splits into items
        $this->assertSame(["a", "b"], Helpers::split("a,b", ",", []));

        # Assert an empty string is preserved
        $this->assertSame("", Helpers::split("", ",", []));

        # Assert null input returns an empty string
        $this->assertSame("", Helpers::split(null, ",", []));

    }

    /**
     * Test Add
     *
     * @return void
     */
    public function testAdd():void {

        # Assert numeric strings and numbers are added
        $this->assertSame(5.0, Helpers::add("2", 3, []));

        # Assert text operands produce a readable expression
        $this->assertSame("a + b", Helpers::add("a", "b", []));

        # Assert unsupported operands return an empty string
        $this->assertSame("", Helpers::add(null, 3, []));

    }

    /**
     * Test Subtract
     *
     * @return void
     */
    public function testSubtract():void {

        # Assert numeric strings and numbers are subtracted
        $this->assertSame(-1.0, Helpers::subtract("2", 3, []));

        # Assert text operands produce a readable expression
        $this->assertSame("a - b", Helpers::subtract("a", "b", []));

        # Assert unsupported operands return an empty string
        $this->assertSame("", Helpers::subtract(null, 3, []));

    }

    /**
     * Test Modulo
     *
     * @return void
     */
    public function testModulo():void {

        # Assert the remainder for uneven division
        $this->assertSame(1, Helpers::modulo(7, 3, []));

        # Assert the remainder for exact division
        $this->assertSame(0, Helpers::modulo(6, 3, []));

        # Assert nonnumeric input uses the fallback
        $this->assertSame(1, Helpers::modulo("bad", 3, []));

    }

    /**
     * Test Is Last
     *
     * @return void
     */
    public function testIsLast():void {

        # Assert the final index renders the block
        $this->assertSame("yes", Helpers::isLast(1, ["a", "b"], $this->blockOptions()));

        # Assert an earlier index renders the inverse block
        $this->assertSame("no", Helpers::isLast(0, ["a", "b"], $this->blockOptions()));

        # Assert invalid list input renders the inverse block
        $this->assertSame("no", Helpers::isLast(0, null, $this->blockOptions()));

    }

    /**
     * Test Divide
     *
     * @return void
     */
    public function testDivide():void {

        # Assert numeric division
        $this->assertSame(2.5, Helpers::divide(5, 2, []));

        # Assert a zero divisor produces a readable expression
        $this->assertSame("5/0", Helpers::divide(5, 0, []));

        # Assert text operands produce a readable expression
        $this->assertSame("a/b", Helpers::divide("a", "b", []));

    }

    /**
     * Test Multiply
     *
     * @return void
     */
    public function testMultiply():void {

        # Assert numeric strings and numbers are multiplied
        $this->assertSame(6, Helpers::multiply("2", 3, []));

        # Assert text operands produce a readable expression
        $this->assertSame("a*b", Helpers::multiply("a", "b", []));

    }

    /**
     * Test Date_status_day
     *
     * @return void
     */
    public function testDate_status_day():void {

        # Assert a past date is classified as past
        $this->assertSame(-1, Helpers::date_status_day(date("Y-m-d", strtotime("-2 days")), []));

        # Assert today is classified as present
        $this->assertSame(0, Helpers::date_status_day(date("Y-m-d"), []));

        # Assert a future date is classified as future
        $this->assertSame(1, Helpers::date_status_day(date("Y-m-d", strtotime("+2 days")), []));

        # Assert invalid date text is preserved
        $this->assertSame("bad", Helpers::date_status_day("bad", []));

        # Assert null input is preserved
        $this->assertNull(Helpers::date_status_day(null, []));

    }

    /**
     * Test Date_status_week
     *
     * @return void
     */
    public function testDate_status_week():void {

        # Assert a previous week is classified as past
        $this->assertSame(-1, Helpers::date_status_week(date("Y-m-d", strtotime("-2 weeks")), []));

        # Assert this week is classified as present
        $this->assertSame(0, Helpers::date_status_week(date("Y-m-d"), []));

        # Assert a later week is classified as future
        $this->assertSame(1, Helpers::date_status_week(date("Y-m-d", strtotime("+2 weeks")), []));

        # Assert invalid date text is preserved
        $this->assertSame("bad", Helpers::date_status_week("bad", []));

        # Assert null input is preserved
        $this->assertNull(Helpers::date_status_week(null, []));

    }

    /**
     * Test Is Array
     *
     * @return void
     */
    public function testIsArray():void {

        # Assert a sequential array renders the block
        $this->assertSame("yes", Helpers::isArray([1, 2], $this->blockOptions()));

        # Assert an empty array renders the block
        $this->assertSame("yes", Helpers::isArray([], $this->blockOptions()));

        # Assert an associative array renders the inverse block
        $this->assertSame("no", Helpers::isArray(["name" => "Ada"], $this->blockOptions()));

        # Assert null input renders the inverse block
        $this->assertSame("no", Helpers::isArray(null, $this->blockOptions()));

    }

    /**
     * Test Is Object
     *
     * @return void
     */
    public function testIsObject():void {

        # Assert an object renders the block
        $this->assertSame("yes", Helpers::isObject((object)["name" => "Ada"], $this->blockOptions()));

        # Assert an associative array renders the block
        $this->assertSame("yes", Helpers::isObject(["name" => "Ada"], $this->blockOptions()));

        # Assert a sequential array renders the inverse block
        $this->assertSame("no", Helpers::isObject([1, 2], $this->blockOptions()));

        # Assert null input renders the inverse block
        $this->assertSame("no", Helpers::isObject(null, $this->blockOptions()));

    }

    /**
     * Test Is String
     *
     * @return void
     */
    public function testIsString():void {

        # Assert an empty string renders the block
        $this->assertSame("yes", Helpers::isString("", $this->blockOptions()));

        # Assert text renders the block
        $this->assertSame("yes", Helpers::isString("Ada", $this->blockOptions()));

        # Assert a number renders the inverse block
        $this->assertSame("no", Helpers::isString(7, $this->blockOptions()));

    }

    /**
     * Test Color Hex Random
     *
     * @return void
     */
    public function testColorHexRandom():void {

        # Assert the generated color uses six uppercase hexadecimal digits
        $this->assertMatchesRegularExpression("/^#[0-9A-F]{6}$/", Helpers::colorHexRandom([]));

    }

    /**
     * Test Format Currency
     *
     * @return void
     */
    public function testFormatCurrency():void {

        # Assert dollar formatting and separators
        $this->assertSame("$ 1.234,50", Helpers::formatCurrency(1234.5, "dollar", []));

        # Assert euro formatting and separators
        $this->assertSame("1 234,50 €", Helpers::formatCurrency("1234.5", "euro", []));

        # Assert missing currency uses the dollar default
        $this->assertSame("$ 0,00", Helpers::formatCurrency(0, null, []));

    }

    /**
     * Test Is Current Day
     *
     * @return void
     */
    public function testIsCurrentDay():void {

        # Assert today renders the block
        $this->assertSame("yes", Helpers::isCurrentDay(date("Y-m-d"), $this->blockOptions()));

        # Assert yesterday renders the inverse block
        $this->assertSame("no", Helpers::isCurrentDay(date("Y-m-d", strtotime("yesterday")), $this->blockOptions()));

        # Assert null input renders the inverse block
        $this->assertSame("no", Helpers::isCurrentDay(null, $this->blockOptions()));

    }

    /**
     * Test Is Weekend
     *
     * @return void
     */
    public function testIsWeekend():void {

        # Assert Saturday renders the block
        $this->assertSame("yes", Helpers::isWeekend("2025-01-11", $this->blockOptions()));

        # Assert Sunday renders the block
        $this->assertSame("yes", Helpers::isWeekend("2025-01-12", $this->blockOptions()));

        # Assert Monday renders the inverse block
        $this->assertSame("no", Helpers::isWeekend("2025-01-13", $this->blockOptions()));

        # Assert null input renders the inverse block
        $this->assertSame("no", Helpers::isWeekend(null, $this->blockOptions()));

    }

    /**
     * Test Date To Local Format
     *
     * @return void
     */
    public function testDateToLocalFormat():void {

        # Assert French weekday and month names
        $this->assertSame("Lundi 13 Janvier", Helpers::dateToLocalFormat("2025-01-13", "fr_FR", []));

        # Assert English weekday and month names
        $this->assertSame("Monday 13 January", Helpers::dateToLocalFormat("2025-01-13", "en_US", []));

        # Assert an unknown locale falls back to English
        $this->assertSame("Monday 13 January", Helpers::dateToLocalFormat("2025-01-13", "unknown", []));

        # Assert null input is preserved
        $this->assertNull(Helpers::dateToLocalFormat(null, "fr_FR", []));

    }

    /**
     * Test Date To Local Format Month Year
     *
     * @return void
     */
    public function testDateToLocalFormatMonthYear():void {

        # Assert French month and year formatting
        $this->assertSame("Janvier 2025", Helpers::dateToLocalFormatMonthYear(1, 2025, "fr_FR", []));

        # Assert an unknown locale falls back to English
        $this->assertSame("January 2025", Helpers::dateToLocalFormatMonthYear(1, 2025, "unknown", []));

        # Assert missing locale returns an empty string
        $this->assertSame("", Helpers::dateToLocalFormatMonthYear(1, 2025, null, []));

    }

    /**
     * Test Date To YYYYMMDD
     *
     * @return void
     */
    public function testDateToYYYYMMDD():void {

        # Assert month and day are padded with zeros
        $this->assertSame("2025-01-09", Helpers::dateToYYYYMMDD(2025, 1, 9));

        # Assert leap day is preserved
        $this->assertSame("2024-02-29", Helpers::dateToYYYYMMDD(2024, 2, 29));

    }

    /**
     * Test Background Gradient
     *
     * @return void
     */
    public function testBackgroundGradient():void {

        # Assert an empty list produces no style
        $this->assertSame("", Helpers::backgroundGradient([], []));

        # Assert null input produces no style
        $this->assertSame("", Helpers::backgroundGradient(null, []));

        # Assert one color produces a solid background
        $this->assertSame("background: rgb(255,0,0);", Helpers::backgroundGradient(["255,0,0"], []));

        # Assert multiple colors produce distinct bands
        $this->assertSame("background: linear-gradient(to bottom, rgba(255,0,0,1) 0%, rgba(255,0,0,1) 50%, rgba(0,0,255,1) 50%, rgba(0,0,255,1) 100%);", Helpers::backgroundGradient(["255,0,0,1", "0,0,255,1"], []));

        # Assert smooth mode distributes color stops
        $this->assertSame("background: linear-gradient(to bottom, rgba(255,0,0,1) 0%, rgba(0,0,255,1) 100%);", Helpers::backgroundGradient(["255,0,0,1", "0,0,255,1"], ["hash" => ["smooth" => true]]));

    }

    /**
     * Test Normalize
     *
     * @return void
     */
    public function testNormalize():void {

        # Assert whitespace and punctuation are normalized
        $this->assertSame("hello_world", Helpers::normalize("  Hello   World! "));

        # Assert only string items in an array are normalized
        $this->assertSame(["hello_world", 7], Helpers::normalize(["Hello World!", 7]));

        # Assert null input is preserved
        $this->assertNull(Helpers::normalize(null));

    }

    /**
     * Test Iterate Until
     *
     * @return void
     */
    public function testIterateUntil():void {

        # Prepare storage for captured iteration metadata
        $rows = [];

        # Prepare the callback used by each iteration
        $options = ["fn" => function(array $row) use (&$rows):string {

            # Capture the current iteration context
            $rows[] = $row;

            # Return the current iteration value
            return (string)$row["i"];
        }];

        # Assert default iteration starts at one
        $this->assertSame("123", Helpers::iterateUntil(3, $options));

        # Assert each iteration receives its index and boundary flags
        $this->assertSame([
            # Expect the first iteration to set the first flag
            ["i" => 1, "@index" => 0, "@first" => true, "@last" => false],
            # Expect the middle iteration to clear both boundary flags
            ["i" => 2, "@index" => 1, "@first" => false, "@last" => false],
            # Expect the final iteration to set the last flag
            ["i" => 3, "@index" => 2, "@first" => false, "@last" => true],
        ], $rows);

        # Enable zero-based iteration
        $options["hash"]["startAtOne"] = false;

        # Assert zero-based iteration includes zero
        $this->assertSame("012", Helpers::iterateUntil(2, $options));

        # Restore default one-based iteration
        unset($options["hash"]);

        # Assert an array determines the iteration count
        $this->assertSame("12", Helpers::iterateUntil(["a", "b"], $options));

        # Assert a string length determines the iteration count
        $this->assertSame("12", Helpers::iterateUntil("ab", $options));

        # Assert null input produces no iterations
        $this->assertSame("", Helpers::iterateUntil(null, $options));

    }

    /**
     * Test To Hex Color
     *
     * @return void
     */
    public function testToHexColor():void {

        # Assert RGB notation converts to hexadecimal
        $this->assertSame("#FF0080", Helpers::toHexColor("rgb(255, 0, 128)", []));

        # Assert raw RGB components convert to hexadecimal
        $this->assertSame("#FF0080", Helpers::toHexColor("255,0,128", []));

        # Assert shorthand hex expands to six digits
        $this->assertSame("#AABBCC", Helpers::toHexColor("#abc", []));

        # Assert lowercase hex converts to uppercase
        $this->assertSame("#AABBCC", Helpers::toHexColor("#aabbcc", []));

        # Assert out-of-range components are rejected
        $this->assertSame("", Helpers::toHexColor("rgb(256, 0, 0)", []));

        # Assert invalid hex digits are rejected
        $this->assertSame("", Helpers::toHexColor("#xyz", []));

        # Assert unsupported color names are rejected
        $this->assertSame("", Helpers::toHexColor("unknown", []));

    }

    /**
     * Test Markdown
     *
     * @return void
     */
    public function testMarkdown():void {

        # Assert a heading renders as HTML
        $this->assertSame('<h1 style="margin: 0 0 0 0;">Title</h1>', Helpers::markdown("# Title", []));

        # Assert bold, italic and inline code formatting
        $this->assertSame('<p style="margin: 0 0 0 0;"><strong>Bold</strong> and <em>italic</em> and <code>code</code></p>', Helpers::markdown("**Bold** and *italic* and `code`", []));

        # Assert raw HTML is escaped
        $this->assertSame('<p style="margin: 0 0 0 0;">&lt;script&gt;bad&lt;/script&gt;</p>', Helpers::markdown("<script>bad</script>", []));

        # Assert null input is preserved
        $this->assertNull(Helpers::markdown(null, []));

    }

    /**
     * Test Lightn Candy Rendering
     *
     * @return void
     */
    public function testLightnCandyRendering():void {

        # Compile inline helpers and a conditional block through LightnCandy
        $compiled = LightnCandy::compile(
            # Combine inline helpers with a conditional block
            '{{uppercase name}}|{{join values "-"}}|{{#is value expected}}yes{{else}}no{{/is}}',
            # Register the helpers and enable Handlebars-compatible compilation
            ["helpers" => Helpers::listArray(), "flags" => LightnCandy::FLAG_HANDLEBARSJS]
        );

        # Assert compilation produces PHP source
        $this->assertIsString($compiled);

        # Prepare the compiled template renderer
        $render = LightnCandy::prepare($compiled);

        # Assert the compiled renderer is callable
        $this->assertIsCallable($render);

        # Assert inline helpers and the matching block render together
        $this->assertSame("ADA|a-b|yes", $render(["name" => "Ada", "values" => ["a", "b"], "value" => 2, "expected" => 2]));

        # Assert the inverse block renders when values differ
        $this->assertSame("ADA|a-b|no", $render(["name" => "Ada", "values" => ["a", "b"], "value" => 1, "expected" => 2]));

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Block Options
     *
     * Provide distinguishable outputs for the block and inverse branches.
     *
     * @param array $hash Helper hash options
     * @return array
     */
    private function blockOptions(array $hash = []):array {

        # Return block callbacks
        return [
            # Pass named helper options to the callback
            "hash"      =>  $hash,
            # Return the marker for the matching block
            "fn"        =>  fn(mixed ...$inputs):string => "yes",
            # Return the marker for the inverse block
            "inverse"   =>  fn(mixed ...$inputs):string => "no",
        ];

    }

    /**
     * Http Status Code
     *
     * Reproduce the installed-app layout expected by the legacy helper.
     *
     * @param array $error Status details
     * @param string $what Status field or all fields
     * @return mixed
     */
    private function httpStatusCode(array $error, string $what):mixed {

        # Locate the temporary fixture cache directory
        $cacheRoot = dirname(__DIR__, 2)."/.cache";

        # Create the cache directory if needed
        if(!is_dir($cacheRoot)) mkdir($cacheRoot, 0777, true);

        # Choose a unique directory for this fixture
        $root = $cacheRoot."/handlebars-helpers-".bin2hex(random_bytes(8));

        # List the directories required by the installed-app layout
        $directories = [
            # Create the unique fixture root
            $root,
            # Create the simulated web root
            $root."/public",
            # Create the dependency directory
            $root."/vendor",
            # Create the package vendor directory
            $root."/vendor/kzarshenas",
            # Create the installed framework directory
            $root."/vendor/kzarshenas/crazyphp",
            # Create the framework resource directory
            $root."/vendor/kzarshenas/crazyphp/resources",
            # Create the directory for HTTP status definitions
            $root."/vendor/kzarshenas/crazyphp/resources/Json",
        ];

        # Set the path of the copied status definitions
        $fixture = end($directories)."/http_status_code.json";

        # Save the current working directory
        $cwd = getcwd();

        try {

            # Create each fixture directory
            foreach($directories as $directory) mkdir($directory);

            # Copy the real HTTP status definitions into the fixture
            copy(dirname(__DIR__, 3)."/resources/Json/http_status_code.json", $fixture);

            # Run the helper from the simulated public directory
            chdir($root."/public");

            # Return the selected field or complete status context
            return Helpers::httpStatusCode($error, $what, ["fn" => fn(mixed $value):mixed => $value]);

        } finally {

            # Restore the original working directory
            chdir($cwd);

            # Remove the copied status definitions
            if(is_file($fixture)) unlink($fixture);

            # Remove fixture directories from deepest to shallowest
            foreach(array_reverse($directories) as $directory){

                # Remove the empty fixture directory
                if(is_dir($directory)) rmdir($directory);
            }

        }

    }

}
