<?php declare(strict_types=1);
/**
 * Test Url
 *
 * Test invalid inputs in HTTP response helpers
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\String;

/**
 * Dependances
 */
use CrazyPHP\Library\String\Url;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Url Test
 *
 * Test invalid inputs in HTTP response helpers
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class UrlTest extends TestCase{

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
     * Test Curl Invalid Inputs
     *
     * Invalid inputs must return false before attempting a network request.
     *
     * @return void
     */
    public function testCurlInvalidInputs():void {

        # Set inputs
        $inputs = [
            null,
            false,
            true,
            0,
            123,
            [],
            ["url"],
            new \stdClass(),
            "",
        ];

        # Iteration of inputs
        foreach($inputs as $input){

            # Assert
            $this->assertFalse(Url::getHttpResponseCode_using_curl($input));

        }

    }

    /**
     * Test Get Headers Invalid Inputs
     *
     * Invalid inputs must return false before attempting a network request.
     *
     * @return void
     */
    public function testGetHeadersInvalidInputs():void {

        # Set inputs
        $inputs = [
            null,
            false,
            true,
            0,
            123,
            [],
            ["url"],
            new \stdClass(),
            "",
        ];

        # Iteration of inputs
        foreach($inputs as $input){

            # Assert
            $this->assertFalse(Url::getHttpResponseCode_using_getheaders($input));

        }

    }

}
