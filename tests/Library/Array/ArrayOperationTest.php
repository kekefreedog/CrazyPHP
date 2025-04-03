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
use CrazyPHP\Library\Array\ArrayOperation;
use CrazyPHP\Model\Env;
use PHPUnit\Framework\TestCase;

/**
 * Array Operation Test
 *
 * Methods for test interactions with array operation
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ArrayOperationTest extends TestCase {
    
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

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Parse Equal
     * 
     * Test parseEqual function
     * 
     * @return void
     */
    public function testParseEqual():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "first" =>  "=toto"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([0 => $input[0]], $filtered);

    }

    /**
     * Test Parse Equal
     * 
     * Test parseEqual function
     * 
     * @return void
     */
    public function testParseEqualDeep():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "second.third" =>  "=tutu"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1 => $input[1]], $filtered);

    }

    /**
     * Test Parse Equal
     * 
     * Test parseEqual function
     * 
     * @return void
     */
    public function testParseNotEqual():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "first" =>  "!=toto"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1 => $input[1]], $filtered);

    }

    /**
     * Test Parse Less Than Or Equal
     * 
     * Test parseLessThanOrEqual function
     * 
     * @return void
     */
    public function testParseLessThanOrEqual():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "six" =>  "<=6"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1 => $input[1]], $filtered);

    }

    /**
     * Test Parse Greater Than Or Equal
     * 
     * Test parseGreaterThanOrEqual function
     * 
     * @return void
     */
    public function testParseGreaterThanOrEqual():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "six" =>  ">=6"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([0 => $input[0]], $filtered);

    }

    /**
     * Test Parse Smaller
     * 
     * Test parseSmaller function
     * 
     * @return void
     */
    public function testParseSmaller():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "six" =>  "<12"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1 => $input[1]], $filtered);

    }

    /**
     * Test Parse Greater
     * 
     * Test parseGreater function
     * 
     * @return void
     */
    public function testParseGreater():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "six" =>  ">0"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([0 => $input[0]], $filtered);

    }

    /**
     * Test Between
     * 
     * Test parseBetween function
     * 
     * @return void
     */
    public function testParseBetween():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "six" =>  "[5:20]"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([0=>$input[0]], $filtered);

    }

    /**
     * Test Like
     * 
     * Test parseLike function
     * 
     * @return void
     */
    public function testParseLike():void {

        # Set input
        $input = [
            # Item 1
            [
                "first"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "first"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "first" =>  "*at*"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1=>$input[1]], $filtered);

    }

    /**
     * Test Like Start
     * 
     * Test parseLike function
     * 
     * @return void
     */
    public function testParseLikeStart():void {

        # Set input
        $input = [
            # Item 1
            [
                "debug"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "debug"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "debug" =>  "tot*"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([0=>$input[0]], $filtered);

    }

    /**
     * Test Like End
     * 
     * Test parseLike function
     * 
     * @return void
     */
    public function testParseLikeEnd():void {

        # Set input
        $input = [
            # Item 1
            [
                "debug"     =>  "toto",
                "second"    =>  [
                    "third"     =>  "titi",
                    "four"      =>  [
                        "five"      =>  "tonton"
                    ]  
                ],
                "six"   =>  12,
                "seven" =>  null,
                "height"=>  true
            ],
            # Item 2
            [
                "debug"     =>  "tata",
                "second"    =>  [
                    "third"     =>  "tutu",
                    "four"      =>  [
                        "five"      =>  "tictac"
                    ]  
                ],
                "six"   =>  0,
                "seven" =>  null,
                "height"=>  false
            ],
        ];

        # Set filter
        $filter = [
            "debug" =>  "*ta"
        ];

        # New ope
        $filtered = ArrayOperation::filter($input, $filter);

        # Check
        $this->assertEquals([1=>$input[1]], $filtered);

    }

}