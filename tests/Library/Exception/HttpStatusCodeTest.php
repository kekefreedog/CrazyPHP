<?php declare(strict_types=1);
/**
 * Test Exception
 *
 * Test Exception Classes
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
use CrazyPHP\Library\Exception\HttpStatusCode;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Http Status Code Test
 *
 * Methods for test Http Status Code Collection
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class HttpStatusCodeTest extends TestCase{

    /** Public constants method
     ******************************************************
     */

    /**
     * Root path of the test
     */
    public const DEFAULT = [
        "code"      =>  null,
        'type'      =>  null,
        "detail"    =>  null,
        "_status_code"  =>  [
            "title"         =>  "Error",
            "style"         =>  [
                "color"         =>  [
                    "text"          =>  "white",
                    "fill"          =>  "grey darken-1"
                ],
                "icon"          =>  [
                    "class"         =>  "material-icons",
                    "text"          =>  "error"
                ]
            ]
        ]
    ];

    /**
     * Toot path of the test 001
     */
    public const OUTPUT_1 = [
        "code"          =>  1,
        "type"          =>  "Warning",
        "detail"        =>  "Detail",
        "_status_code"  =>  [
            "title"         =>  "Test",
            "style"         =>  [
                "color"         =>  [
                    "text"          =>  "red",
                    "fill"          =>  "red darken-1"
                ],
                "icon"          =>  [
                    "class"         =>  "material-icons",
                    "text"          =>  "add"
                ],
            ]
        ]
    ];

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
            "crazyphp_root" =>  getcwd(),
            "phpunit_test"  =>  true,
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
     * Test Get All
     * 
     * @return void
     */
    public function testGetAll():void {

        # Get 404
        $input = HttpStatusCode::getAll();

        # File content
        $fileContent = File::open("@crazyphp_root/resources/Yml/HttpStatusCode.yml");

        # Get output
        $output = $fileContent["HttpStatusCode"] ?? [];

        # Assert
        $this->assertEquals($output, $input);

    }

    /**
     * Test Get Default
     * 
     * @return void
     */
    public function testGetDefault():void {

        # Get default
        $input = HttpStatusCode::getDefault();

        # Assert
        $this->assertEquals(self::DEFAULT, $input);

    }

    /**
     * Test Get 404
     * 
     * @return void
     */
    public function testGet404():void {

        # Get 404
        $input = HttpStatusCode::get(404);

        # File content
        $fileContent = File::open("@crazyphp_root/resources/Yml/HttpStatusCode.yml");

        # Get output
        $output = $fileContent["HttpStatusCode"][404] ?? [];

        # Assert
        $this->assertEquals($output, $input);

    }

    /**
     * Test Get Unknow number
     * 
     * @return void
     */
    public function testGet001():void {

        # Get 404
        $input = HttpStatusCode::get(001, static::OUTPUT_1);

        # Assert
        $this->assertEquals(static::OUTPUT_1, $input);

    }

}