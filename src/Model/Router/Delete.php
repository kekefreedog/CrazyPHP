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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\File;

/**
 * Delete Router
 *
 * Classe for deletion of router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Delete implements CrazyCommand {

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

    /**
     * Inputs
     */
    private $inputs = [];

    /**
     * Constructor
     * 
     * Construct current class
     * 
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Get routers to delete
        $routers = $inputs["routers"][0]["value"];

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
            $this->inputs[] = [
                "name"  =>  $exploded[1],
                "type"  =>  $exploded[0]
            ];

        }

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
         * Run Remove From Config
         * - Remove Router from config
         */
        $this->runRemoveFromConfig();

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
     * Run Retrieve Router
     * 
     * Process input to retrieve object
     * 
     * @return void
     */
    public function runRetrieveRouter():void {



    }

    /**
     * Run Delete Index File
     * 
     * Delete the ts script file
     * 
     * @return void
     */
    public function runDeleteIndexFile():void {



    }

    /**
     * Run Delete Style File
     * 
     * Delete the scss style file
     * 
     * @return void
     */
    public function runDeleteStyleFile():void {

    }

    /**
     * Run Delete Template
     * 
     * Create the hbs template
     * 
     * @return void
     */
    public function runDeleteTemplate():void {

    }

    /**
     * Run Delete Controler File
     * 
     * Delete the php controller file
     * 
     * @return void
     */
    public function runDeleteControllerFile():void {

    }

    /**
     * Run Remove From Config
     * 
     * Remove Router from config
     * 
     * @return void
     */
    public function runRemoveFromConfig():void {

    }

    /** Public constants
     ******************************************************
     */

    /** @const public ROUTER_APP_PATH */
    public const ROUTER_APP_PATH = Create::ROUTER_APP_PATH;

    /** @const public ROUTER_CONTROLLER_PATH */
    public const ROUTER_CONTROLLER_PATH = Create::ROUTER_CONTROLLER_PATH;

}