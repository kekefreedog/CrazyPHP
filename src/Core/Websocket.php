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
use Workerman\Worker;

/**
 * Websocket
 *
 * Websocket methods of your crazy application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Websocket {

    /** Parameters | websocket
     ******************************************************
     */

    /** @var string $_protocol */
    private string $_protocol = "websocket";

    /** @var string $_adress */
    private string $_address = "0.0.0.0";

    /** @var string $_port */
    private int $_port = 2346;

    /** Parameters | Router
     ******************************************************
     */

    /** @var ?Router $_routerInstance */
    private ?Router $_routerInstance = null;

    /** Parameters | Websocket
     ******************************************************
     */

    /** @var ?Worker $_websocketServer */
    private ?Worker $_websocketServer = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Retrieve Parameters
        $this->_retrieveParameters();

        # Init Websocket server
        $this->_initWebsocketServer();

        # Init router instance
        $this->_initRouterInstance();

        # Load routers
        $this->_loadRouters();

        
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
     * Retrieve Parameters
     * 
     * @return void
     */
    private function _retrieveParameters():void {



    }

    /**
     * Init Websocket Server
     * 
     * @return void
     */
    private function _initWebsocketServer():void {

        # Set socket name
        $socketName = $this->_protocol."://".$this->_address.":".$this->_port;

        // Create a WebSocket server
        $this->_websocketServer = new Worker($socketName);

        // Set the number of processes to 1
        $this->_websocketServer->count = 1;
        
        // Define the behavior for when a client connects
        $this->_websocketServer->onConnect = function($connection) {
            echo "New Crazy connection\n";
        };
        
        // Define the behavior for when a client sends data
        $this->_websocketServer->onMessage = function($connection, $data) {
            echo "Received: $data\n";
            $connection->send("Crazy Echo: $data");
        };
        
        // Define the behavior for when a client disconnects
        $this->_websocketServer->onClose = function($connection) {
            echo "Crazy Connection closed\n";
        };

    }

    /** Private Methods | Router
     ******************************************************
     */

    /**
     * Init Router Instance
     * 
     * @return void
     */
    private function _initRouterInstance():void {

        # Set router instance
        $this->_routerInstance = new Router;

    }

    /**
     * Load Routers
     * 
     * @return void
     */
    private function _loadRouters():void {


    }

}