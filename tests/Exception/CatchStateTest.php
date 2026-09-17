<?php declare(strict_types=1);
/**
 * Test Catch State
 *
 * Test captured state and exception details
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Exception;

/**
 * Dependances
 */
use CrazyPHP\Exception\CatchState;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use RuntimeException;

/**
 * Catch State Test
 *
 * Test captured state and exception details
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class CatchStateTest extends TestCase{

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
     * Test Captured State And Exception Details
     *
     * Regression coverage for incompatible inherited property types.
     *
     * @return void
     */
    public function testPreservesStateAndExceptionDetails():void {

        # Set previous exception
        $previous = new RuntimeException("Previous failure");

        # Set state
        $state = [
            "results"   =>  [
                "id"    =>  42,
            ],
        ];

        # Create exception
        $exception = new CatchState("Captured state", 500, $state, $previous);

        # Assert get state
        $this->assertSame($state, $exception->getState());
        
        # Assert get get message
        $this->assertSame("Captured state", $exception->getMessage());
        
        $this->assertSame(500, $exception->getCode());
        
        # Assert get get previous
        $this->assertSame($previous, $exception->getPrevious());

    }

    /**
     * Test Default Captured State
     *
     * @return void
     */
    public function testDefaultState():void {

        # Create exception without optional arguments
        $exception = new CatchState();

        # Assert get state
        $this->assertSame([], $exception->getState());
        
        # Assert get message
        $this->assertSame("Catch State", $exception->getMessage());
        
        # Assert get code
        $this->assertSame(0, $exception->getCode());
        
        # Assert get previous
        $this->assertNull($exception->getPrevious());

    }

}
