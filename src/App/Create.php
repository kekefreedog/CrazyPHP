<?php declare(strict_types=1);
/**
 * New application
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\App;

/**
 * Create new Application
 *
 * Classe for create step by step new application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Create{

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "Crazy Project",
            "required"      =>  true,
            "process"       =>  ['trim']
        ],
        # Description
        [
            "name"          =>  "description",
            "description"   =>  "Description of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "My Crazy Web Application",
            "process"       =>  ['trim']
        ],
        # Author Name
        [
            "name"          =>  "authors__name",
            "description"   =>  "Author of this crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Author Email
        [
            "name"          =>  "authors__email",
            "description"   =>  "Email of the crazy author",
            "type"          =>  "VARCHAR",
            "default"       =>  "crazy@person.com",
            "required"      =>  true,
            "process"       =>  ['trim'],
            "validate"      =>  ['email'],
        ],
        # Type
        [
            "name"          =>  "type",
            "description"   =>  "Type of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "library",
            "select"        =>  [
                ""              =>  "Undifined",
                "library"       =>  "Library",
                "project"       =>  "Project"
            ]
        ],
        # Homepage
        [
            "name"          =>  "homepage",
            "description"   =>  "Home page of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "https://github.com/kekefreedog/CrazyPHP/",
            "validate"      =>  ['http'],
        ],
    ];

    /** Variables
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

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run creation of project
     *
     * @return Create
     */
    public function run(){

        /**
         * Run Composer
         * 1. Fill composer.json
         * 2. Run composer install
         */
        $this->runComposer();

        /**
         * Run NPM
         * 0. Check package.json exists
         * 1. Fill package.json
         * 2. Run npm install
         */
        $this->runNpm();

        /**
         * Run Structure Folder
         * 1. Create structure folder
         * 2. Check permissions
         */
        $this->runStructureFolder();

        /**
         * Run Config
         * 0. Check configs files exists
         * 1. Fill config files
         * 2. Create app routes
         * 3. Create basic users
         */
        $this->runConfig();

        /**
         * Run Public
         * 1. Fill .htaccess
         * 2. Fill index.php
         */
        $this->runPublic();

        /**
         * Run User Interface
         * 1. Create basic components
         * 2. Prepare basic assets
         */
        $this->runUserInterface();

        /**
         * Run Webpack
         * 1. Prepare webpack config
         */
        $this->runWebpack();

        /**
         * Run Database
         * 0. Try to connect to database
         * 1. Create user
         * 2. Create database
         * 3. Create auth tables
         * 4. Create app tables
         */
        $this->runDatabase();

        /**
         * Run First Compilation
         * 
         * First compilation of thr app
         */
        $this->runFirstCompilation();

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

    /**
     * Run Composer
     * 
     * Steps : 
     * 0. Check composer.json exists
     * 1. Fill composer.json
     * 2. Run composer install
     * 
     * @return Create
     */
    public function runComposer():Create {

        # Return instance
        return $this;

    }

    /**
     * Run NPM
     * 
     * Steps : 
     * 0. Check package.json exists
     * 1. Fill package.json
     * 2. Run npm install
     * 
     * @return Create
     */
    public function runNpm():Create {

        # Check input > application is set and not empty
        if(empty($this->input['application'] ?? []))



        # Return instance
        return $this;

    }

    /**
     * Run Structure Folder
     * 
     * Steps : 
     * 1. Create structure folder
     * 2. Check permissions
     * 
     * @return Create
     */
    public function runStructureFolder():Create {

        # Return instance
        return $this;

    }

    /**
     * Run Config
     * 
     * Steps : 
     * 0. Check configs files exists
     * 1. Fill config files
     * 2. Create app routes
     * 3. Create basic users
     * 
     * @return Create
     */
    public function runConfig():Create {

        # Return instance
        return $this;

    }

    /**
     * Run Public
     * 
     * Steps : 
     * 1. Fill .htaccess
     * 2. Fill index.php
     * 
     * @return Create
     */
    public function runPublic():Create {

        # Return instance
        return $this;

    }

    /**
     * Run User Interface
     * 
     * Steps : 
     * 1. Create basic components
     * 2. Prepare basic assets
     * 
     * @return Create
     */
    public function runUserInterface():Create {

        # Return instance
        return $this;

    }

    /**
     * Run Webpack
     * 
     * 1. Prepare webpack config
     * 
     * @return Create
     */
    public function runWebpack():Create {

        # Return instance
        return $this;

    }

    /**
     * Run Database
     * 
     * Steps : 
     * 0. Try to connect to database
     * 1. Create user
     * 2. Create database
     * 3. Create auth tables
     * 4. Create app tables
     * 
     * @return Create
     */
    public function runDatabase():Create {

        # Return instance
        return $this;

    }

    /** 
     * Run First Compilation
     * 
     * @return Create
     */
    public function runFirstCompilation():Create {

        # Return instance
        return $this;

    }
}