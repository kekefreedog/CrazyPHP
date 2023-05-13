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
use CrazyPHP\Library\Database\Database;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Model\Webpack\Run;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\Config;
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
class Create implements CrazyCommand {

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
            "process"       =>  ['trim']
        ],
        # Methods
        [
            "name"          =>  "methods",
            "description"   =>  "Methods allowed by your crazy router",
            "type"          =>  "ARRAY",
            "default"       =>  "get",
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

    /** Parameters
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
         * Run Router In Config
         * - Integrate Router into config
         */
        $this->runRouterIntoConfig();

        # Return this
        return $this;

    }

    /**
     * Get story line
     * 
     * Used for execute each method one after another
     * 
     * @return array
     */
    public function getStoryline():array {

        # Declare result
        $result = [];

        # New reflection
        $reflection = new \ReflectionClass($this);

        # Get methods
        $methods = $reflection->getMethods();

        # Check methods
        if($methods)

            # Iteration of methods
            foreach($methods as $method)

                # Check run children methods
                if(
                    substr($method->name, 0, 3) == "run" && 
                    strlen($method->name) > 3
                )

                    # Push result in result
                    $result[] = $method->name;

        # Return result
        return $result;

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

        # Set router
        $this->router = Process::getResultSummary($this->inputs);

        print_r($this->router);

    }


    /**
     * Run Create Index File
     * 
     * Create the ts script file
     * 
     * @return void
     */
    public function runCreateIndexFile():void {


    }


    /**
     * Run Create Style File
     * 
     * Create the scss style file
     * 
     * @return void
     */
    public function runCreateStyleFile():void {



    }


    /**
     * Run Create Template
     * 
     * Create the hbs template
     * 
     * @return void
     */
    public function runCreateTemplate():void {

        

    }


    /**
     * Run Router In Config
     * 
     * Integrate Router into config
     * 
     * @return void
     */
    public function runRouterIntoConfig():void {



    }

    /** Private methods
     ******************************************************
     */

    /**
     * Get data
     * 
     * Get all data needed for template engine
     * 
     * @return array
     */
    private function _getData():array {

        # Set result
        $result = [];

        # Return result
        return $result;

    }

}