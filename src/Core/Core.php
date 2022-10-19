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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\Middleware;
use CrazyPHP\Core\Instance;
use CrazyPHP\Model\Env;
use ReflectionMethod;
use ReflectionClass;
use App\Core\Kernel;


/**
 * Core
 *
 * Interface between application and crazy php framework
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Core extends Kernel {

    /** Parameter
     ******************************************************
     */

    /**
     * @var Instance $instance Instance of your app
     * ->router()
     */
    public $instance = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructor
        parent::__construct();

        # Load instances
        $this->instance = new Instance();

    }

    /** Public methods | Env
     ******************************************************
     */

    /**
     * Set Env
     */
    public function setEnv(array $customEnv = []):void {

        # Env to push
        $envToPush = [
            "app_root"      =>  $_SERVER["DOCUMENT_ROOT"]."/..",
            "crazyphp_root" =>  $_SERVER["DOCUMENT_ROOT"]."/../vendor/kzarshenas/crazyphp",
        ];

        # Merge custom env
        if(!empty($customEnv))

            # Iteration of custom env
            foreach($customEnv as $k => $v)

                # Check k is string
                if(!is_string($k) || !$k)
        
                    # New Exception
                    throw new CrazyException(
                        "Please check custom env \"$k\" => \"$v\". Actually env name looks not valid, you have to cheoose a char string name.",
                        500,
                        [
                            "custom_code"   =>  "core-002",
                        ]
                    );

                else

                    # Push env in env to push
                    $envToPush[$k]  =   $v;

        # Set envs
        Env::set($envToPush);

    }

    /** Public methods | Router
     ******************************************************
     */

    /**
     * Run Routers Preparation
     * 
     * Prepare router instance
     * 
     * @return void
     */
    public function runRoutersPreparation():void {

        # Check instance router
        if(!isset($this->instance->router))
        
            # New Exception
            throw new CrazyException(
                "Please check if router instance is correctly launch in your app.",
                500,
                [
                    "custom_code"   =>  "core-001",
                ]
            );

        # Push collection in router instance
        $this->instance->router->pushCollection();

    }

    /**
     * Run Router Redirection
     * 
     * @return void
     */
    public function runRouterRedirection():void {

        # Check instance router
        if(!isset($this->instance->router))
        
            # New Exception
            throw new CrazyException(
                "Please check if router instance is correctly launch in your app.",
                500,
                [
                    "custom_code"   =>  "core-002",
                ]
            );

        # Get request uri
        $request_uri = $_SERVER["REQUEST_URI"];
        
        # Call route controller
        $this->instance->router->callRoute($request_uri);

    }

    /** Public methods | Router
     ******************************************************
     */
    
    
    /**
     * Run Middlewares Preparation
     * 
     * @return void
     */
    public function runMiddlewaresPreparation():void {

        # New reflection
        $reflection = new ReflectionClass("CrazyPHP\Core\Middleware");

        # Get static methods
        $staticMethods = $reflection->getMethods(ReflectionMethod::IS_STATIC|ReflectionMethod::IS_PUBLIC);

        # Check static methods
        if(empty($staticMethods))

            # Stop function
            return;

        /** @var ReflectionMethod $staticMethod */
        foreach($staticMethods as $staticMethod)

            # Register middleware
            $this->instance->router->registerMiddleware(
                "*", 
                function(string $route, ...$parameters) use ($staticMethod){
                    return $staticMethod->class::{$staticMethod->name}($route, ...$parameters);
                }
            );

    }

}