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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Router;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\String\Strings;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\Env;

/**
 * Create new Router
 *
 * Classe for create step by step new router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Create extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Type
        [
            "name"          =>  "type",
            "description"   =>  "Type of your crazy router",
            "type"          =>  "VARCHAR",
            "default"       =>  "app",
            "select"        =>  [
                "app"           =>  "App",
                "api"           =>  "Api",
                "asset"         =>  "Asset",
            ]
        ],
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy router",
            "type"          =>  "VARCHAR",
            "default"       =>  "router",
            "required"      =>  true,
            "process"       =>  ['cleanPath', 'snakeToCamel', 'ucfirst', 'trim']
        ],
        # Methods
        [
            "name"          =>  "methods",
            "description"   =>  "Methods allowed by your crazy router",
            "type"          =>  "ARRAY",
            "default"       =>  "get",
            "required"      =>  true,
            "multiple"      =>  true,
            "select"        =>  [
                "get"           =>  "Get",
                "post"          =>  "Post",
                "put"           =>  "Put",
                "delete"        =>  "Delete",
                "option"        =>  "Option",
                "patch"         =>  "Patch",
            ],
        ],
        # Prefix
        [
            "name"          =>  "prefix",
            "description"   =>  "Prefix of your crazy router",
            "type"          =>  "STRING",
            "default"       =>  false,
        ],
    ];

    /** Private Parameters
     ******************************************************
     */

    /**
     * Inputs
     */
    private $inputs = [];

    /**
     * Logs
     */
    private $logs = true;

    /** @var array $router */
    private $router = [];

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
        $this->runPrepareRouter();


        /**
         * Run Create Index File
         * - Create the ts script file
         */
        $this->runCreateIndexFile();


        /**
         * Run Create Style File
         * - Create the scss style file
         */
        $this->runCreateStyleFile();


        /**
         * Run Create Template
         * - Create the hbs template
         */
        $this->runCreateTemplate();

        /**
         * Run Create Controler File
         * - Create the php controller file
         */
        $this->runCreateControllerFile();

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
     * Run Prepare Router
     * 
     * Process input to router object
     * 
     * @return void
     */
    public function runPrepareRouter():void {

        # Process inputs
        $this->router = Process::getResultSummary($this->inputs["router"]);
        
        # Get Router collection
        $routers = FileConfig::getValue("Router.".$this->router["Type"]);

        # Check if router name alreay exists
        if(!empty(Arrays::filterByKey($routers, "name", $this->router["Name"])))
            
            # New error
            throw new CrazyException(
                "Given name \"".$this->router["Name"]."\" already exists in \"".$this->router["Type"]."\" routers collection",
                500,
                [
                    "custom_code"   =>  "create-router-001",
                ]
            );

        # Check if prefix is empty
        if(!$this->router["Prefix"])

            # Unset false
            unset($this->router["Prefix"]);

        # Decode method
        $this->router["Methods"] = Json::check($this->router["Methods"]) ?
            json_decode($this->router["Methods"]) : 
                $this->router["Methods"]; 

        # Check methods
        if(is_string($this->router["Methods"]))

            # Convert to string
            $this->router["Methods"] = [$this->router["Methods"]];

        # Set patterns
        $this->router["patterns"] = ["/".Process::camelToSnake($this->router["Name"])];

        # Check if type is app
        if($this->router["Type"] == "app"){

            # Check is dir in environnement
            if(File::exists(Router::getAppPath().$this->router["Name"]))

                # Delete the folder
                File::removeAll(Router::getAppPath().$this->router["Name"]);

            # Create clean folder
            mkdir(File::path(Router::getAppPath().$this->router["Name"]));

        }

        # Set up env for cache driver
        Env::set([
            "cache_driver"  =>  "Files"
        ]);

    }

    /**
     * Run Create Index File
     * 
     * Create the ts script file
     * 
     * @return void
     */
    public function runCreateIndexFile():void {

        # Check if app
        if($this->router["Type"] == "app"){

            # Create template instance
            $template = new Handlebars([
                "template"  =>  Handlebars::PERFORMANCE_PRESET,
                "helpers"   =>  false
            ]);

            # Load template
            $template->load("@crazyphp_root/resources/Environment/Template/App/index.ts.hbs");

            # Render template with current router value
            $result = $template->render($this->router);

            # Write content into file
            file_put_contents(File::path(Router::getAppPath().$this->router["Name"]."/index.ts"), $result);

        }

    }

    /**
     * Run Create Style File
     * 
     * Create the scss style file
     * 
     * @return void
     */
    public function runCreateStyleFile():void {

        # Check if app
        if($this->router["Type"] == "app"){

            # Create template instance
            $template = new Handlebars([
                "template"  =>  Handlebars::PERFORMANCE_PRESET,
                "helpers"   =>  false
            ]);

            # Load template
            $template->load("@crazyphp_root/resources/Environment/Template/App/style.scss.hbs");

            # Render template with current router value
            $result = $template->render($this->router);

            # Write content into file
            file_put_contents(File::path(Router::getAppPath().$this->router["Name"]."/style.scss"), $result);

        }

    }

    /**
     * Run Create Template
     * 
     * Create the hbs template
     * 
     * @return void
     */
    public function runCreateTemplate():void {

        # Check if app
        if($this->router["Type"] == "app"){

            # Copy template
            File::copy("@crazyphp_root/resources/Environment/Template/App/template.hbs", Router::getAppPath().$this->router["Name"]."/template.hbs");

        }

    }

    /**
     * Run Create Controler File
     * 
     * Create the php controller file
     * 
     * @return void
     */
    public function runCreateControllerFile():void {

        # Check if app
        if($this->router["Type"] == "app"){

            # Set controller into router
            $this->router["Controller"] = "App\\Controller\\App\\".str_replace("/", "\\", $this->router["Name"]);
    
            # Set additionnal data
            $additionnal = [
                "Namespace"     =>  Strings::removeLastString($this->router["Controller"], "\\"),
                "Class"         =>  Strings::getLastString($this->router["Controller"], "\\"),
            ];

            # Create template instance
            $template = new Handlebars([
                "template"  =>  Handlebars::PERFORMANCE_PRESET,
                "helpers"   =>  false
            ]);

            # Load template
            $template->load("@crazyphp_root/resources/Hbs/App/Controller/App/Template.php.hbs");

            # Render template with current router value
            $result = $template->render($this->router + $additionnal);

            # Write content into file
            file_put_contents(File::path(Router::getControllerPath().ucfirst($this->router["Type"])."/".$this->router["Name"].".php"), $result);

        }else
        # Check if api
        if($this->router["Type"] == "api"){

            # Set controller into router
            $this->router["Controller"] = "App\\Controller\\Api\\V1\\".str_replace("/", "\\", $this->router["Name"]);
    
            # Set additionnal data
            $additionnal = [
                "Namespace"     =>  Strings::removeLastString($this->router["Controller"], "\\"),
                "Class"         =>  Strings::getLastString($this->router["Controller"], "\\"),
            ];

            # Create template instance
            $template = new Handlebars([
                "template"  =>  Handlebars::PERFORMANCE_PRESET,
                "helpers"   =>  false
            ]);

            # Load template
            $template->load("@crazyphp_root/resources/Hbs/App/Controller/Api/Template.php.hbs");

            # Render template with current router value
            $result = $template->render($this->router + $additionnal);

            # Write content into file
            file_put_contents(File::path(Router::getControllerPath().ucfirst($this->router["Type"])."\/v1/".$this->router["Name"].".php"), $result);

        }else
        # Check if asset
        if($this->router["Type"] == "asset"){

            # Set controller into router
            $this->router["Controller"] = "App\\Controller\\Assets\\".str_replace("/", "\\", $this->router["Name"]);
    
            # Set additionnal data
            $additionnal = [
                "Namespace"     =>  Strings::removeLastString($this->router["Controller"], "\\"),
                "Class"         =>  Strings::getLastString($this->router["Controller"], "\\"),
            ];

            # Create template instance
            $template = new Handlebars([
                "template"  =>  Handlebars::PERFORMANCE_PRESET,
                "helpers"   =>  false
            ]);

            # Load template
            $template->load("@crazyphp_root/resources/Hbs/App/Controller/Asset/Template.php.hbs");

            # Render template with current router value
            $result = $template->render($this->router + $additionnal);

            # Write content into file
            file_put_contents(File::path(Router::getControllerPath().ucfirst($this->router["Type"])."/".$this->router["Name"].".php"), $result);

        }

    }

    /**
     * Run Router In Config
     * 
     * Integrate Router into config
     * 
     * @return void
     */
    public function runRouterIntoConfig():void {

        # Change key case
        $router = Arrays::changeKeyCaseRecursively($this->router);

        # Set type
        $type = $router["type"];

        # Remove type from router
        unset($router["type"]);

        # Get router collection count
        $routers = FileConfig::getValue("Router.".$type);

        # Count routers
        $routersKey = count($routers ?: []);

        # Set value in config
        FileConfig::setValue("Router.$type.$routersKey", $router);

    }

}