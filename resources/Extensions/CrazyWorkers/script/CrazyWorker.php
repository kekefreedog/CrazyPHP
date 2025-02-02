<?php declare(strict_types=1);
/**
 * Interface
 *
 * Script of Crazy Worker Extension
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  App\Library;

use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\System\Os;
use Workerman\Worker;
use Workerman\Timer;
use ReflectionClass;

/**
 * Dependances
 */

/**
 * Crazy Worker
 * 
 * Methods for manipulate workers
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class CrazyWorker {

    /** Parameters
     ******************************************************
     */

    /** @var $_timerCollection */
    private array $_timerCollection = [
        "onStart"   =>  [],
        "timer"     =>  [],
        "onStop"    =>  [],
    ];

    /** @var $_workerInstance */
    private ?Worker $_workerInstance = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Retrieve workers
        $this->_retrieveWorkers();

        # Setup workers
        $this->_setupWorkers();

        # Set processes
        $this->_setProcesses();

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

    /** Private Methods | Parameters
     ******************************************************
     */

    /**
     * Retrieve Workers
     * 
     * @return void
     */
    private function _retrieveWorkers():void {

        # Declare workerAlreadyRetrieved
        $workerAlreadyRetrieved = [];

        # Get worker config
        $workerConfig = Config::get("Workers");

        # Get list of workers
        $workersList = $workerConfig["Workers"]["list"] ?? [];

        # Iteration list
        if(!empty($workersList)) foreach($workersList as $worker) if($worker['name'] ?? false && !in_array($worker['name'], $workerAlreadyRetrieved)){

            # Get type
            $type = $worker['type'] ?? "";

            # Prepare method name
            $methodName = "_retrieve".ucfirst($type);

            # Check method exits
            (new ReflectionClass($this))->hasMethod($methodName) && $this->{$methodName}($worker);


            # Push name in worker already retireved
            $workerAlreadyRetrieved[] = $worker['name'];

        }

    }

    /**
     * Setup Workers
     * 
     * @return void
     */
    private function _setupWorkers():void {

        # Setup worker instance
        $this->_workerInstance === null && ($this->_workerInstance = new Worker());

        # Set onWorkerStart
        $this->_workerInstance->onWorkerStart = function(){

            # Get on start
            $onStartTimerCollection = $this->_timerCollection["onStart"];

            # Iteration foreach
            if(!empty($onStartTimerCollection)) foreach($onStartTimerCollection as $item) {

                # Check function
                if(is_callable($item["function"] ?? false))

                    # Call function
                    $item["function"]($this->_workerInstance);


            }

            # Get on start
            $timerCollection = $this->_timerCollection["timer"];

            # Iteration foreach
            if(!empty($timerCollection)) foreach($timerCollection as $item) {

                # Check function
                if(is_callable($item["function"] ?? false)){

                    # Add timezone_transitions_get
                    Timer::add($item["arguments"]["interval"] ?? 10, $item["function"], $item["arguments"] ?? []);

                }


            }

        };

        # Set onWorkerStop
        $this->_workerInstance->onWorkerStop = function(){

            # Get on start
            $onStopTimerCollection = $this->_timerCollection["onStop"];

            # Iteration foreach
            if(!empty($onStopTimerCollection)) foreach($onStopTimerCollection as $item) {

                # Check function
                if(is_callable($item["function"] ?? false))

                    # Call function
                    $item["function"]($this->_workerInstance);


            }

        };

    }

    /**
     * Set Processes
     * 
     * Set proccesses number based on config
     * 
     * @return void
     */
    private function _setProcesses():void {

        # Setup worker instance
        $this->_workerInstance === null && ($this->_workerInstance = new Worker());

        # Set result
        $result = 1;

        # Get worker config
        $workerConfig = Config::get("Workers");

        # Get processes workers parameter
        $processes = $workerConfig["Workers"]["processes"] ?? "auto";

        # Check processes
        if($processes == "auto"){

            # Set result
            $result = Os::getCpuNumber();

            # Check result
            if($result < 1) $result = 1;

        }elseif(intval($processes) > 1){

            # Set result
            $result = $processes;

        }
        
        # Set processes
        $this->_workerInstance->count = $result;

    }

    /** Private Methods | Parameters
     ******************************************************
     */

    /**
     * Retrieve Timer
     * 
     * @return void
     */
    private function _retrieveTimer(array $worker):void {

        # Get class
        $class = $worker["class"] ?? null;

        # Check class
        if($class && class_exists($class)){

            # Register methods into _timerCollection
            foreach(["onStart", "timer", "onStop"] as $method)
                
                # Append method 
                $this->_timerCollection[$method][] = [
                    "arguments" =>  $worker["arguments"] ?? [],
                    "function"  =>  "$class::$method"
                ];
            
        }

    }

}