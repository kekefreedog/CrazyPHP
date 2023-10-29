<?php declare(strict_types=1);
/**
 * New router
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Model\RouterType;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Create new Router
 *
 * Classe for create step by step new router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Create extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy router type",
            "default"       =>  "test",
            "type"          =>  "VARCHAR",
            "required"      =>  true,
            "process"       =>  ['cleanPath', 'snakeToCamel', 'strtolower', 'trim']
        ],
        # Methods
        [
            "name"          =>  "regex",
            "description"   =>  "Regex to use for catch token",
            "default"       =>  "(.*)",
            "type"          =>  "VARCHAR",
        ]
    ];

    /** Private Parameters
     ******************************************************
     */

    /**
     * Inputs
     */
    private $inputs = [];

    /** @var array $router */
    private $routerType = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Ingest data
        $this->inputs = $inputs;

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Get Required Values
     * 
     * Return required values
     * 
     * @return array
     */
    public static function getRequiredValues():array {

        # Set result
        $result = self::REQUIRED_VALUES;

        # Return result
        return $result;

    }

    /** Public methods
     ******************************************************
     */
    
    /**
     * Run creation of project
     *
     * @return Create
     */
    public function run():self {

        /**
         * Run Prepare Router
         * - Process input to router object
         */
        $this->runPrepareRouterType();

        /**
         * Run Create Script File
         * - Create script file
         */
        $this->runCreateScriptFile();

        /**
         * Run Router In Config
         * - Integrate Router into config
         */
        $this->runRouterIntoConfig();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Prepare Router Type
     * 
     * Process input to router type object
     * 
     * @return void
     */
    public function runPrepareRouterType():void {

        # Process inputs
        $this->routerType = Process::getResultSummary($this->inputs["router"]);
        
        # Get Router collection
        $routerTypeCollection = FileConfig::getValue("Router.type");

        # Clean router name
        $routerTypeName = Process::snakeToCamel(str_replace(["/", "."], "_", $this->routerType["Name"]), true);

        # Check if router name alreay exists
        if(!empty(@Arrays::filterByKey($routerTypeCollection, "name", $this->routerType["name"])))
            
            # New error
            throw new CrazyException(
                "Given name \"$routerTypeName\" already exists in router type collection",
                500,
                [
                    "custom_code"   =>  "create-router-type-001",
                ]
            );

        # Set up env for cache driver
        Env::set([
            "cache_driver"  =>  "Files"
        ]);

    }

    /**
     * Run Create Controler File
     * 
     * Create the php controller file
     * 
     * @return void
     */
    public function runCreateScriptFile():void {

        # Set additionnal data
        $this->routerType["Namespace"] = "App\\Library\\Router\\Type";
        $this->routerType["Class"] = ucfirst($this->routerType["Name"]);
        $this->routerType["Controller"] = $this->routerType["Namespace"]."\\".$this->routerType["Class"];

        # Create template instance
        $template = new Handlebars();

        # Load template
        $template->load("@crazyphp_root/resources/Hbs/App/routerType.hbs");

        # Render template with current router value
        $result = $template->render($this->routerType);

        # File path
        $filePath = Router::getRouterTypePath()."/".$this->routerType["Class"].".php";

        # Create file
        File::create($filePath, $result);

    }

    /**
     * Run Router In Config
     * 
     * Integrate Router into config
     * 
     * @return void
     */
    public function runRouterIntoConfig():void {

        # Get router collection count
        $routerTypeCollection = FileConfig::getValue("Router.type");

        # Count routers
        $routerTypeKey = count($routerTypeCollection ?: []);

        # Set value in config
        FileConfig::setValue("Router.type.".$routerTypeKey, [
            "name"  =>  $this->routerType["Name"],
            "class" =>  $this->routerType["Controller"]
        ]);

    }

}