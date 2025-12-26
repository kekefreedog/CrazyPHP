<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Core;

/**
 * Dependances
 */
use Monolog\Handler\StreamHandler;
use Workerman\Worker;
use Monolog\Logger;

/**
 * Timer
 *
 * Timer methods of your crazy application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Timer {

    /** Parameters | Router
     ******************************************************
     */

    /** @var string $_pollInterval Check every pollInterval time */
    private int $_pollInterval = 5;

    /** Parameters | Websocket
     ******************************************************
     */

    /** @var ?Worker $_worker */
    private ?Worker $_worker = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Retrieve Parameters
        $this->_retrieveParameters();

        # Init worker
        $this->_initWorkerInstance();

        # Init logger
        $this->_initLoggers();
        
    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Run
     * 
     * Run Websocket server
     * 
     * @return void
     */
    public function run():void {

        # Set command
        Worker::$command = 'start';

        # Run worker
        Worker::runAll();

    }

    /**
     * Stop
     * 
     * Stop Websocket server
     * 
     * @return void
     */
    public function stop():void {

        # Run worker
        Worker::stopAll();

    }

    /**
     * Restart
     * 
     * Restart Websocket server
     * 
     * @return void
     */
    public function restart():void {

        # Set command
        Worker::$command = 'restart';

        # Run worker
        Worker::runAll();

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Retrieve Parameters
     * 
     * @return void
     */
    private function _retrieveParameters():void {

        # Retrieve 

    }

    /**
     * Init Router Instance
     * 
     * @return void
     */
    private function _initWorkerInstance():void {

        # Set router instance
        $this->_worker = new Worker();

    }

    /**
     * Init Loggers
     * 
     * @return void
     */
    private function _initLoggers():void {



    }

}