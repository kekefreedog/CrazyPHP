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

use Workerman\Worker;
use Workerman\Timer;

/**
 * Dependances
 */

/**
 * Crazy Timer Interface
 * 
 * Interface for timer class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyTimer {

    /**
     * Constructor
     * 
     * Get current database config
     * 
     * @param Timer $timer
     * @param Worker $worker
     * @return self
     */
    public function __construct(Timer $timer, Worker $worker, ?string $logPath = null);

    /**
     * On Start
     * 
     * @return void
     */
    public function onStart():void;

    /**
     * On Stop
     * 
     * @return void
     */
    public function onStop():void;

}