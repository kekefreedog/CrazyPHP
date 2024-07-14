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
use CrazyPHP\Library\Array\Arrays;
use PHPUnit\Framework\TestCase;

/**
 * Arrays test
 *
 * Methods for test interactions with array
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ArraysTest extends TestCase{

    /**
     * Merge multidimensional array test
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

    /**
     * Stretch test
     * 
     * @return void
     */
    public function testStretch():void {

        # Declare input
        $input = [
            'name' => 'Crazy Project',
            'description' => 'My Crazy Web Application',
            'authors__name' => 'CrazyPerson',
            'authors__email' => 'crazy@person.com',
            'type' => 'library',
            'homepage' => 'https://github.com/kekefreedog/CrazyPHP/',
        ];

        # Declare await result
        $awaitResult = array (
            'name' => 'Crazy Project',
            'description' => 'My Crazy Web Application',
            'authors' => 
            array (
              0 => 
              array (
                'name' => 'CrazyPerson',
                'email' => 'crazy@person.com',
              ),
            ),
            'type' => 'library',
            'homepage' => 'https://github.com/kekefreedog/CrazyPHP/',
        );

        # Result
        $result = Arrays::stretch($input);

        # Send results to php unit
        $this->assertEquals($result, $awaitResult);

    }

    /**
     * Test Parse Key
     * 
     * @return void
     */
    public function testParseKey():void {

        /* Prepare io */

        # Array
        $array = [
            "first" =>	[
                "second"    =>	[
                    "third"     =>  "Youpi"
                ]
            ]
        ];

        # Key 1
        $key1 = "first.second.third";
        
        # Key 2
        $key2 = "first/second/third";
        
        # Key 3
        $key3 = "first/second.third";

        /* Process */

        # Test key 1
        $valueKey1 = Arrays::parseKey($key1, $array);
        
        # Test key 2
        $valueKey2 = Arrays::parseKey($key2, $array);
        
        # Test key 3
        $valueKey3 = Arrays::parseKey($key3, $array);
        
        /* Assert */
        
        $this->assertEquals($valueKey1, "Youpi");
        $this->assertEquals($valueKey2, "Youpi");
        $this->assertEquals($valueKey3, "Youpi");

    }

    /**
     * Test Fill
     * 
     * @return void
     */
    public function testFill():void {

        /* Prepare io */

        # Array
        $array = [
            "first" =>	[
                "second"    =>	[
                    "third"     =>  "Youpi",
                    "fourth"    =>  "Woow"
                ]
            ],
            "other" =>  [
                "lola"  =>  [ 1, 2 ]
            ]
        ];

        # Result waited
        $resultWaited = [
            "first" =>	[
                "second"    =>	[
                    "third"     =>  "Youpi",
                ]
            ],
            "other" =>  [
                "lola"  =>  [ 1, 2 ]
            ]
        ];

        # Key 1
        $key1 = "first.second.third";
        $key2 = "other.lola";

        # Result
        $result = [];

        /* Process */

        # Fill result
        Arrays::fill($result, $key1, Arrays::parseKey($key1, $array));
        Arrays::fill($result, $key2, Arrays::parseKey($key2, $array));

        /* Assert */
        $this->assertEquals($result, $resultWaited);


    }

    /**
     * Test Has
     * 
     * @return void
     */
    public function testHas():void {

        # Set array A
        $arrayA = [
            "toto"  => [
                "titi"  => "value"
            ]
        ];

        # Set array B
        $arrayB = [
            "toto"  => [
                "tutu"  => "value"
            ]
        ];

        # Value to search
        $searchKey = "toto.titi";

        # Assert 1
        $this->assertTrue(Arrays::has($arrayA, $searchKey));

        # Assert 2
        $this->assertFalse(Arrays::has($arrayB, $searchKey));

    }

    /**
     * Test Has Key
     * 
     * @return void
     */
    public function testHasKey():void {

        # Set array A
        $arrayA = [
            "toto"  => [
                "titi"  => "value"
            ]
        ];

        # Set array B
        $arrayB = [
            "toto"  => [
                "tutu"  => "value"
            ]
        ];

        # Value to search
        $searchKey = "toto.titi";

        # Assert 1
        $this->assertTrue(Arrays::hasKey($arrayA, $searchKey));

        # Assert 2
        $this->assertFalse(Arrays::hasKey($arrayB, $searchKey));

    }

    /**
     * Test Get Key
     * 
     * @return void
     */
    public function testGetKey():void {

        # Set array A
        $arrayA = [
            "toto"  => [
                "titi"  => "value"
            ]
        ];

        # Value to search
        $searchKeyA = "toto.titi";
        $searchKeyB = "toto";

        # Assert 1
        $this->assertEquals(Arrays::getKey($arrayA, $searchKeyA), $arrayA["toto"]["titi"]);

        # Assert 2
        $this->assertEquals(Arrays::getKey($arrayA, $searchKeyB), $arrayA["toto"]);

    }

    /**
     * Test Get Key
     * 
     * @return void
     */
    public function testSetKey():void {

        # Set array A
        $result = [
            "toto"  => [
                "titi"  => "value"
            ]
        ];

        # Set array
        $array = [];

        # Value to search
        $keyA = "toto.titi";
        $keyB = "toto.tata";

        # Value
        $value = "value";

        # Assert 1
        $this->assertTrue(Arrays::setKey($array, $keyA, $value));

        # Assert 2
        $this->assertEquals($array, $result);

        # Assert 3
        $this->assertFalse(Arrays::setKey($array, $keyB, $value, false));

    }

    /**
     * Test Flatten
     * 
     * @return void
     */
    public function testFlatten():void {

        # Input
        $input = [
            'type'          =>  'Project',
            'attributes'    =>  [
                'name'              =>  'test',
                'image'             =>  "",
                'sg_release_title'  =>  'test2',
            ],
            'relationships' =>  [],
            'id'            =>  1111,
            'links'         =>  [
                'self'              =>  '/api/v1/entity/projects/1111',
            ],
        ];

        # Output
        $output = [
            "type"                          =>  "Project",
            "attributes.name"               =>  "test",
            "attributes.image"              =>  "",
            "attributes.sg_release_title"   =>  "test2",
            "id"                            =>  "1111",
            "links.self"                    =>  "/api/v1/entity/projects/1111",
        ];

        # Flatten
        $result = Arrays::flatten($input);

        # Assert 
        $this->assertEquals($output, $result);

    }

    /**
     * Test Unflatten
     * 
     * @return void
     */
    public function testUnflatten():void {

        # Input
        $input = [
            'type'                          =>  'Project',
            'attributes.name'               =>  'test',
            'attributes.image'              =>  '',
            'attributes.sg_release_title'   =>  'test2',
            'id'                            =>  1111,
            'links.self'                    =>  '/api/v1/entity/projects/1111',
        ];

        # Output
        $output = [
            'type'          =>  'Project',
            'attributes'    =>  [
                'name'              =>  'test',
                'image'             =>  "",
                'sg_release_title'  =>  'test2',
            ],
            'id'            =>  1111,
            'links'         =>  [
                'self'              =>  '/api/v1/entity/projects/1111',
            ],
        ];

        # Flatten
        $result = Arrays::unflatten($input);

        # Assert 
        $this->assertEquals($output, $result);

    }

    /**
     * Test Remove By Key
     * 
     * @return void
     */
    public function testRemoveByKey():void {

        # Set input
        $input = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        # Set output
        $output = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 3, 'name' => 'Charlie'],
        ];

        # Assert one
        $this->assertEquals($output, Arrays::removeByKey($input, "id", 2));

    }

}