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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Form\Process;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Header Test
 *
 * Methods for test structure folder generator
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ProcessTest extends TestCase{
    
    /** Public constants
     ******************************************************
     */

    /** @const array INPUT */
    public const INPUT = [
        "toto"  =>  [
            "toto"  =>  [
                "value" =>  "@env_value"
            ],
            "toto"  =>  "{{Test.process}}"
        ]
    ];

    /** @const array RESULT */
    public const RESULT = [
        "toto"  =>  [
            "toto"  =>  [
                "value" =>  "env_result"
            ],
            "toto"  =>  "process_result"
        ]
    ];
    
    /** Public methods
     ******************************************************
     */

    /**
     * Test Env And Config Values
     * 
     * Test envAndConfigValues function
     * 
     * @return void
     */
    public function testEnvAndConfigValues():void {

        # Setup env
        Env::set([
            # App root for composer class
            "crazyphp_root"          =>  getcwd(),
            "phpunit_test"      =>  true,
            "env_value"         =>  "env_result",
            "config_location"   =>  "@crazyphp_root/resources/Yml"
        ]);

        # Set input
        $result = Process::envAndConfigValues(self::INPUT);

        # Check result is equal to header generated
        $this->assertEquals($result, self::RESULT);

    }

}