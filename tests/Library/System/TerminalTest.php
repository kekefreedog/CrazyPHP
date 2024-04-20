<?php declare(strict_types=1);
/**
 * Test State
 *
 * Test State Classes
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
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use PHPUnit\TextUI\Configuration\Constant;

/**
 * Page Test
 *
 * Methods for test terminal classes
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class TerminalTest extends TestCase {

    /** Public constant
     ******************************************************
     */

    /* Value and result */
    public const SET_ENV = [
        [
            "_command"      =>  "commandSetEnv",
            "commandPrompt" =>  [
                "input"         =>  ["key", "value"],
                "result"        =>  "set KEY=value"
            ],
            "powershell"    =>  [
                "input"         =>  ["key", "value"],
                "result"        =>  "\$env:KEY = 'value'"
            ],
        ],
        [
            "_command"      =>  "commandSetEnv",
            "commandPrompt" =>  [
                "input"         =>  ["key", true],
                "result"        =>  "set KEY=True"
            ],
            "powershell"    =>  [
                "input"         =>  ["key", true],
                "result"        =>  "\$env:KEY = \$true"
            ],
        ],
        [
            "_command"      =>  "commandChain",
            "commandPrompt" =>  [
                "input"         =>  [
                    "echo Starting process...",
                    "set DATE_VAR=%date%",
                    "echo Today's date is: %DATE_VAR%"
                ],
                "result"        =>  "echo Starting process... && set DATE_VAR=%date% && echo Today's date is: %DATE_VAR%"
            ],
            "powershell"    =>  [
                "input"         =>  [
                    "Write-Output 'Starting process...'",
                    "\$date = Get-Date",
                    "Write-Output \"The current date and time is: \$date\""
                ],
                "result"        =>  "Write-Output 'Starting process...'; \$date = Get-Date; Write-Output \"The current date and time is: \$date\";"
            ],
        ],
    ];

    /** Private method | Preparation
     ******************************************************
     */

    /**
     * Get Terminals
     * 
     * @return array
     */
    private function _getTerminals():array {

        # Set result
        $result = [
            "commandPrompt" =>  "\CrazyPHP\Library\System\Terminal\CommandPrompt",
            "powershell"    =>  "\CrazyPHP\Library\System\Terminal\Powershell"
        ];

        # Retun result
        return $result;

    }

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
            # App root for composer class
            "crazyphp_root"     =>  getcwd(),
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
     * Test Command Set Env
     * 
     * @return void
     */
    public function testCommandSetEnv():void {

        # Get terminals
        $terminals = $this->_getTerminals();

        # Iteration terminals
        foreach($terminals as $terminalName => $terminal){

            # Iteration key value
            foreach(self::SET_ENV as $data){

                # Get result
                $result = call_user_func_array([$terminal, $data["_command"]], $data[$terminalName]["input"]);

                # Asset
                $this->assertEquals($data[$terminalName]["result"], $result);

            }

        }

    }

}