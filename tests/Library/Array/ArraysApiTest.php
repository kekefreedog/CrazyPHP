<?php declare(strict_types=1);
/**
 * Test Json
 *
 * Test Json
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Array;

/**
 * Dependances
 */
use CrazyPHP\Library\Array\Api;
use CrazyPHP\Library\Array\Module\Map;
use PHPUnit\Framework\TestCase;

/**
 * Arrays Api Test
 *
 * Methods for test interactions with api array
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ArraysApiTest extends TestCase{

    /**
     * Test Array Api
     * 
     * @return void
     */
    public function testArrayApi():void {

        # Set map
        $map = [
            'version'   =>  [
                'getType'           =>  "version type",
                'getPackageName'    =>  "package name",
            ],
            'delivery'  =>  [
                'getType'           =>  "delivery type",
                'getPackageName'    =>  "delivery name",
            ],
        ];

        # New api
        $api = new Api($map);

        # Check result 1
        $this->assertEquals($api->version->getType(), $map["version"]["getType"]);

        # Check result 2
        $this->assertIsObject($api->delivery);

    }

    /**
     * Merge multidimensional array test
     * 
     * @return void
     */
    public function testArrayApiWithPath():void {

        # Set map
        $map = [
            'version'   =>  [
                'getType'           => "version type",
                'getPackageName'    =>  "package name",
            ],
            'delivery'  =>  [
                'getType' => "delivery type",
                'getPackageName'    =>  "delivery name",
            ],
            'method'    =>  [
                'getTest'           =>  "test"
            ]
        ];

        # Create temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');

        // Encode JSON
        $json = json_encode($map, JSON_PRETTY_PRINT);

        // Write file
        file_put_contents($tmpFile, $json);

        # New api
        $api = new Api($tmpFile, "Tests\Library\Array\Test");
        
        # Check result 1
        $this->assertEquals($api->method->getTest(), Test::test());

        # Remove tmp file
        unlink($tmpFile);

    }

    /**
     * Merge Array Api With Path Alias
     * 
     * @return void
     */
    public function testArrayApiWithPathWithAlias():void {

        # Set map
        $map = [
            'version'   =>  [
                'getType'           => "version type",
                'getPackageName'    =>  "package name",
            ],
            'delivery'  =>  [
                'getType' => "delivery type",
                'getPackageName'    =>  "delivery name",
            ],
            'method'    =>  [
                'getTest'           =>  "test"
            ]
        ];

        # Create temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');

        // Encode JSON
        $json = json_encode($map, JSON_PRETTY_PRINT);

        // Write file
        file_put_contents($tmpFile, $json);

        # New api
        $api = new Api($tmpFile, ["Tests\\Library\\Array\\Test"]);
        
        # Check result 1
        $this->assertEquals($api->method->getTest(), Test::test());

        # Remove tmp file
        unlink($tmpFile);

    }

}

/** Public Class
 ******************************************************
 */

/** Test */
class Test extends Map { public static function test(string $input = "") { return $input ? $input : "test complete"; } }