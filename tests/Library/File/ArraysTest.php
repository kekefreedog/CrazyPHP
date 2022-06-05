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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Array\Arrays;
use Tests\TestCase;

/**
 * Arrays test
 *
 * Methods for test interactions with array
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ArraysTest extends TestCase{

    /**
     * Loop update
     * 
     * @private
     * 
     * @return void
     */
    public function testMergeMultidimensionalArrays():void {

        # Declare stack
        $stack1 = [
            "name"          =>  "Crazy Project",
            "description"   =>  "My Crazy Web Application",
            "type"          =>  "library",
            "homepage"      =>  "https://github.com/kekefreedog/CrazyPHP/",
        ];
        $stack2 = [
            "repositories" [
                [
                    "type"      =>  "path",
                    "url"       =>  "../CrazyPHP",
                    "options"   => [
                        "symlink"   =>  1
                    ]
                ]
            ],
            "require" => [
                "kzarshenas/crazyphp" => "@dev"
            ]
        
        ];
        $stack3 = [
            "name"          =>  "Crazy Project",
            "description"   =>  "My Crazy Web Application",
            "type"          =>  "library",
            "homepage"      =>  "https://github.com/kekefreedog/CrazyPHP/",
            "repositories" [
                [
                    "type"      =>  "path",
                    "url"       =>  "../CrazyPHP",
                    "options"   => [
                        "symlink"   =>  1
                    ]
                ]
            ],
            "require" => [
                "kzarshenas/crazyphp" => "@dev"
            ]
        
        ];

        # Check
        $this->assertSame(
            $stack3, 
            Arrays::mergeMultidimensionalArrays(false, $stack2, $stack1);
        );

    }

}