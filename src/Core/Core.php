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
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\File;
use CrazyPHP\Core\Instance;
use CrazyPHP\Core\Response;
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
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Core extends Kernel {

    /** Parameter
     ******************************************************
     */

    /**
     * @var Instance $instance Instance of your app
     * ->router()
     */
    public ?Instance $instance = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructor
        parent::__construct();

        # Check if dist file
        $this->runCheckIfDist();

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
            "app_assets"    =>  $_SERVER["DOCUMENT_ROOT"]."/../assets"
        ];

        # Merge custom env
        if(!empty($customEnv))

            # Iteration of custom env
            foreach($customEnv as $k => $v)

                # Check k is string
                if(!is_string($k) || !$k)
        
                    # New Exception
                    throw new CrazyException(
                        "Please check custom env \"$k\" => \"$v\". Actually env name looks not valid, you have to choose a char string name.",
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
     * Run Check If Dist File
     * 
     * Check if request call a resource in dist folder
     * 
     * @return self
     */
    public function runCheckIfDist(): void{
        
        $requestUri = $_SERVER["REQUEST_URI"] ?? '';
    
        // Check if "dist" is in the request URI and the file is a ".js" file.
        if (strpos($requestUri, "/dist/") !== false && substr($requestUri, -2) == "js") {
            
            // Extract page name using regex.
            $pattern = '/\/dist\/page\/app\/(.*?)\./';
            if (preg_match($pattern, $requestUri, $matches)) {
                $pageName = $matches[1];
    
                $finder = new Finder();
                // Locate the appropriate JS file.
                $finder
                    ->files()
                    ->name("$pageName.*.js")
                    ->depth('== 0')
                    ->in(realpath("./dist/page/app"));
    
                if ($finder->hasResults()) {
                    foreach ($finder as $file) {
                        header('Content-Type: application/javascript');
                        $fileContent = file_get_contents($file->getRealPath());
    
                        if ($fileContent !== false) {
                            echo $fileContent;
                        } else {
                            echo "Error reading the file.";
                        }
                        exit;
                    }
                }
            }
        }
    }
    

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
        
        # Call route controller
        $this->instance->router->callRouteExtended();

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
        
        # Call route controller
        $this->instance->router->pushMiddlewares();

    }

}