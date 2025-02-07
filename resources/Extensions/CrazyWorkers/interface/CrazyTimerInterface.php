<?php declare(strict_types=1);
/**
 * Interface
 *
 * Interface of Crazy Worker Extension
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Interface;

/**
 * Dependances
 */
use CrazyPHP\Library\System\Logger;
use Workerman\Worker;

/**
 * Crazy Timer Interface
 * 
 * Interface for timer class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyTimerInterface {

    /** Public static methods
     ******************************************************
     */

    /**
     * On Start
     * 
     * Merhod call on worker start
     * 
     * @param Worker $worker
     * @param ?Logger $logger
     * @return void
     */
    public static function onStart(Worker $worker, ?Logger $logger = null):void;

    /**
     * Timer
     * 
     * Merhod call on each timer repeat
     * 
     * @param float $duration Monitor duration
     * @param float $interval Repeat delay duration
     * @param ?Logger $logger
     * @return void
     */
    public static function timer(float $duration, float $interval, ?Logger $logger = null):void;

    /**
     * On Stop
     * 
     * Merhod call on worker stop
     * 
     * @param Worker $worker
     * @param ?Logger $logger
     * @return void
     */
    public static function onStop(Worker $worker, ?Logger $logger = null):void;

}