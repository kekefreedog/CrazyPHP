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
use CrazyPHP\Model\Trash\Delete as TrashDelete;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;

/**
 * Delete Router
 *
 * Classe for deletion of router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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

                    # Get current time
                    $now = (new DateTime())->format('Y-m-d_H-i-s_v');

                    # Copy the file in trash
                    File::create(
                        self::TRASH_PATH."router/".$router["type"]."/".$router["name"]."/config.json_".$now, 
                        Json::encode(
                            [
                                "Router"    =>  [
                                    $router["type"] =>  $search
                                ]
                            ], 
                            true
                        )
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
            $indexPath = self::ROUTER_APP_PATH."/".$router["name"]."/index.ts";

            # Check index exits
            if(!File::exists($indexPath))

                # Continue iteration
                continue;

            # Get current time
            $now = (new DateTime())->format('Y-m-d_H-i-s_v');

            # Copy the file in trash
            File::copy($indexPath, self::TRASH_PATH."router/".$router["type"]."/".$router["name"]."/index.ts_".$now);

            # Delete index
            File::remove($indexPath);

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
            $stylePath = self::ROUTER_APP_PATH."/".$router["name"]."/style.scss";

            # Check index exits
            if(!File::exists($stylePath))

                # Continue iteration
                continue;

            # Get current time
            $now = (new DateTime())->format('Y-m-d_H-i-s_v');

            # Copy the file in trash
            File::copy($stylePath, self::TRASH_PATH."router/".$router["type"]."/".$router["name"]."/style.scss_".$now);

            # Delete index
            File::remove($stylePath);

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
            $templatePath = self::ROUTER_APP_PATH."/".$router["name"]."/template.hbs";

            # Check index exits
            if(!File::exists($templatePath))

                # Continue iteration
                continue;

            # Get current time
            $now = (new DateTime())->format('Y-m-d_H-i-s_v');

            # Copy the file in trash
            File::copy($templatePath, self::TRASH_PATH."router/".$router["type"]."/".$router["name"]."/template.hbs_".$now);

            # Delete index
            File::remove($templatePath);

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
                self::ROUTER_CONTROLLER_PATH."/".
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

            # Get current time
            $now = (new DateTime())->format('Y-m-d_H-i-s_v');

            # Copy the file in trash
            File::copy($controllerPath, self::TRASH_PATH."controller/".$router["type"]."/".$router["name"].".php_".$now);

            # Delete index
            File::remove($controllerPath);

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
                self::ROUTER_APP_PATH."/".$router["name"]
            ];

            # Iteration folders to delete
            foreach($folderToDelete as $folder)

                # Check if folder is dir and empty
                if(File::isEmpty($folder))

                    # Remove folder
                    File::remove($folder);

        }

    }

    /** Public constants
     ******************************************************
     */

    /** @const public ROUTER_APP_PATH */
    public const ROUTER_APP_PATH = Create::ROUTER_APP_PATH;

    /** @const public ROUTER_CONTROLLER_PATH */
    public const ROUTER_CONTROLLER_PATH = Create::ROUTER_CONTROLLER_PATH;

    /** @const public TRASH_PATH */
    public const TRASH_PATH = TrashDelete::TRASH_PATH;

}