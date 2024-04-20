<?php declare(strict_types=1);
/**
 * Interface
 *
 * Interface of CrazyPHP
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Interface;

/**
 * Dependances
 */

/**
 * Crazy Router Type Interface
 * 
 * Interface for define compatible your terminal
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyTerminal {

    /** Public static function
     ******************************************************
     */

    /**
     * Is
     * 
     * Check if is curent terminal
     * 
     * @return bool
     */
    public static function is():bool;

    /** Public static function | Get Command
     ******************************************************
     */

    /**
     * Command Set Env
     * 
     * Get command to set Env based on key value
     * 
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public static function commandSetEnv(string $key, mixed $value):string;

    /**
     * Command Chain
     * 
     * @param string[] $commands
     * @return string
     */
    public static function commandChain(...$commands):string;

    /** Public static function | Action
     ******************************************************
     */

    /**
     * Run
     * 
     * Run command given
     * 
     * @param string $command
     * @param bool $liveResult
     * @return mixed
     */
    public static function run(string $command, bool $liveResult = true):mixed;

}