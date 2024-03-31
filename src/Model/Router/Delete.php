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
namespace CrazyPHP\Model\Router;

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
            "select"        =>  "CrazyPHP\Library\Router\Router::getSummary"
        ],
    ];

    /** Parameters
     ******************************************************
     */

    /** @var array $inputs */
    private array $inputs = [];

    /** @var array $routers */
    private array $routers = [];

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
         * Run Delete Index File
         * - Delete the ts script file
         */
        $this->runDeleteIndexFile();
    
        /**
         * Run Delete Style File
         * - Delete the scss style file
         */
        $this->runDeleteStyleFile();
    
        /**
         * Run Delete Template
         * - Create the hbs template
         */
        $this->runDeleteTemplate();
    
        /**
         * Run Delete Controler File
         * - Delete the php controller file
         */
        $this->runDeleteControllerFile();

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
        $routers = $this->inputs["routers"][0]["value"];

        # Check routers
        if(empty($routers))

            # New error
            throw new CrazyException(
                "None router selected.",
                500,
                [
                    "custom_code"   =>  "create-router-001",
                ]
            );

        # Iteration of routers
        foreach($routers as $router){

            # Exploder router
            $exploded = explode(".", $router);

            # Push in inputs
            $this->routers[] = [
                "name"  =>  $exploded[1],
                "type"  =>  $exploded[0]
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
        foreach($this->routers as $router){

            # Open config of the current type
            $routers = Config::getValue("Router.".$router["type"]);

            # Check routers
            if(is_array($routers)){

                # Search router in collection
                $search = Arrays::filterByKey($routers, "name", $router["name"]);

                # Check search
                if(!empty($search)){

                    # Set Router key
                    $setRouterKey = array_key_first($search);

                    # Send to trash
                    Trash::sendAnObject(
                        [
                            "Router"    =>  [
                                $router["type"] =>  $search
                            ]
                        ],
                        "config",
                        "router/".$router["type"]."/".$router["name"]
                    );

                    # Remove this config from router config
                    Config::removeValue("Router.app.$setRouterKey");

                }

            }

        }

    }

    /**
     * Run Delete Index File
     * 
     * Delete the ts script file
     * 
     * @return void
     */
    public function runDeleteIndexFile():void {

        # Iteration routers
        foreach($this->routers as $router){

            # Check type is app
            if($router["type"] != "app")

                # Continue iteration
                continue;

            # Set index path
            $indexPath = Router::getAppPath()."/".$router["name"]."/index.ts";

            # Check index exits
            if(!File::exists($indexPath))

                # Continue iteration
                continue;

            # Send file to trash
            Trash::send(
                $indexPath, 
                "router/".$router["type"]."/".$router["name"]
            );

        }

    }

    /**
     * Run Delete Style File
     * 
     * Delete the scss style file
     * 
     * @return void
     */
    public function runDeleteStyleFile():void {

        # Iteration routers
        foreach($this->routers as $router){

            # Check type is app
            if($router["type"] != "app")

                # Continue iteration
                continue;

            # Set index path
            $stylePath = Router::getAppPath()."/".$router["name"]."/style.scss";

            # Check index exits
            if(!File::exists($stylePath))

                # Continue iteration
                continue;

            # Send file to trash
            Trash::send(
                $stylePath, 
                "router/".$router["type"]."/".$router["name"]
            );

        }

    }

    /**
     * Run Delete Template
     * 
     * Create the hbs template
     * 
     * @return void
     */
    public function runDeleteTemplate():void {

        # Iteration routers
        foreach($this->routers as $router){

            # Check type is app
            if($router["type"] != "app")

                # Continue iteration
                continue;

            # Set index path
            $templatePath = Router::getAppPath()."/".$router["name"]."/template.hbs";

            # Check index exits
            if(!File::exists($templatePath))

                # Continue iteration
                continue;

            # Send file to trash
            Trash::send(
                $templatePath, 
                "router/".$router["type"]."/".$router["name"]
            );

        }

    }

    /**
     * Run Delete Controler File
     * 
     * Delete the php controller file
     * 
     * @return void
     */
    public function runDeleteControllerFile():void {

        # Iteration routers
        foreach($this->routers as $router){

            # Set index path
            $controllerPath = 
                Router::getControllerPath()."/".
                ucfirst($router["type"]).
                (
                    $router["type"] == "api" ? 
                        "/v1" :
                            "/"
                )."/".
                $router["name"].".php";

            # Check index exits
            if(!File::exists($controllerPath))

                # Continue iteration
                continue;

            # Send file to trash
            Trash::send(
                $controllerPath, 
                "controller/".$router["type"]
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

        # Iteration routers
        foreach($this->routers as $router){

            # Check type is app
            if($router["type"] != "app")

                # Continue iteration
                continue;

            # Folder to delete
            $folderToDelete = [
                Router::getAppPath()."/".$router["name"]
            ];

            # Iteration folders to delete
            foreach($folderToDelete as $folder)

                # Check if folder is dir and empty
                if(File::isEmpty($folder))

                    # Remove folder
                    File::remove($folder);

        }

    }

}