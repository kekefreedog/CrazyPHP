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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Model\RouterType;

/**
 * Dependances
 */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\Trash;
use CrazyPHP\Library\File\File;

/**
 * Delete Router
 *
 * Classe for deletion of router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Type
        [
            "name"          =>  "routers",
            "description"   =>  "Routers to delete",
            "type"          =>  "ARRAY",
            "required"      =>  true,
            "multiple"      =>  true,
            "select"        =>  "CrazyPHP\Library\Router\Router::getRouterTypeSummary"
        ],
    ];

    /** Parameters
     ******************************************************
     */

    /** @var array $inputs */
    private array $inputs = [];

    /** @var array $routerType */
    private array $routerType = [];

    /**
     * Constructor
     * 
     * Construct current class
     * 
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Set inputs
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

        # Declare result
        $result = self::REQUIRED_VALUES;

        # Return result
        return $result;

    }

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run delete of project
     *
     * @return Delete
     */
    public function run():self {

        /**
         * Run Retrieve Router
         * - Process input to retrieve object
         */
        $this->runRetrieveRouter();
    
        /**
         * Run Remove From Config
         * - Remove Router from config
         */
        $this->runRemoveFromConfig();
    
        /**
         * Run Delete Controler File
         * - Delete the php controller file
         */
        $this->runDeleteScriptFile();

        /**
         * Run Remove Folder
         * - Remove folder in environnement
         */
        $this->runRemoveFolder();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Retrieve Router
     * 
     * Process input to retrieve object
     * 
     * @return void
     */
    public function runRetrieveRouter():void {

        # Get routers to delete
        $routerTypeCollection = $this->inputs["router"][0]["value"];

        # Check routers
        if(empty($routerTypeCollection))

            # New error
            throw new CrazyException(
                "None router selected.",
                500,
                [
                    "custom_code"   =>  "create-router-001",
                ]
            );

        # Iteration of routers
        foreach($routerTypeCollection as $router){

            # Push in inputs
            $this->routerType[] = [
                "name"  =>  $router,
            ];

        }

    }

    /**
     * Run Remove From Config
     * 
     * Remove Router from config
     * 
     * @return void
     */
    public function runRemoveFromConfig():void {

        # Iteration routers
        foreach($this->routerType as $routerType){

            # Open config of the current type
            $routerTypeCollection = Config::getValue("Router.type");

            # Check routers
            if(is_array($routerTypeCollection)){

                # Search router in collection
                $search = Arrays::filterByKey($routerTypeCollection, "name", $routerType["name"]);

                # Check search
                if(!empty($search)){

                    # Set Router key
                    $setRouterKey = array_key_first($search);

                    # Send to trash
                    Trash::sendAnObject(
                        [
                            "Router"    =>  [
                                "type" =>  $search
                            ]
                        ],
                        "config",
                        "router/type/".$routerType["name"]
                    );

                    # Remove this config from router config
                    Config::removeValue("Router.type.$setRouterKey");

                }

            }

        }

    }

    /**
     * Run Delete Script File
     * 
     * Delete the php script file
     * 
     * @return void
     */
    public function runDeleteScriptFile():void {

        # Iteration routers
        foreach($this->routerType as $routerType){

            # Set index path
            $controllerPath = Router::getRouterTypePath()."/".ucfirst($routerType["name"]).".php";

            # Check index exits
            if(!File::exists($controllerPath))

                # Continue iteration
                continue;

            # Send file to trash
            Trash::send(
                $controllerPath, 
                "routerType/".$routerType["name"]
            );

        }

    }

    /**
     * Run Remove Folder
     * 
     * Remove folder in environnement
     * 
     * @return void
     */
    public function runRemoveFolder():void {

        # Get router type path
        $folder = Router::getRouterTypePath();

        # Check if empty
        if(File::isEmpty($folder))

            # Remove folder
            File::remove($folder);

    }

}