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
use PHPUnit\Framework\TestCase;

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
     * Merge multidimensional array test
     * 
     * @private
     * 
     * @return void
     */
    public function testMergeMultidimensionalArrays():void {

        # Test set
        $array11 = [
            'alpha' =>  'alpha',
            'beta'  =>  'beta',
            'hello'	=>	[
            ]
        ];
        $array12 = [
            'beta'  =>  'tera',
            'tera'  =>  'quad',
            'hello'	=>	[
            	"woow"	=>	true	
            ]
        ];
        $array13 = [
        	'one'	=>	[
        		'two'	=>	[
        			'three'
    			]	
        	]
        ];
        $array14 = [

        ];
        $result1 = [
            'alpha' =>  'alpha',
            'beta'  =>  'tera',
            'hello'	=>	[
            	"woow"	=>	true	
            ],
            'tera'  =>  'quad',
        	'one'	=>	[
        		'two'	=>	[
        			'three'
    			]	
        	]
        ];
        $mergedArrays = Arrays::mergeMultidimensionalArrays(true, $array11, $array12, $array13, $array14);
        $this->assertEquals($mergedArrays, $result1);

        # Test update
        $array21 = [
            'alpha' =>  'alpha',
            'beta'  =>  'beta',
            'hello'	=>	[
            ]
        ];
        $array22 = [
            'beta'  =>  'tera',
            'tera'  =>  'quad',
            'hello'	=>	[
            	"woow"	=>	true	
            ]
        ];
        $array23 = [
        	'one'	=>	[
        		'two'	=>	[
        			'three'
    			]	
        	]
        ];
        $result2 = [
            'alpha' =>  'alpha',
            'beta'  =>  'tera',
            'hello'	=>	[
            ]
        ];
        $mergedArrays = Arrays::mergeMultidimensionalArrays(false, $array21, $array22, $array23);
        $this->assertEquals($mergedArrays, $result2);

        # Test empty
        $array31 = [

        ];
        $array32 = [

        ];
        $array33 = [

        ];
        $result3 = [

        ];
        $mergedArrays = Arrays::mergeMultidimensionalArrays(true, $array31, $array32, $array33);
        $this->assertEquals($mergedArrays, $result3);

    }

}