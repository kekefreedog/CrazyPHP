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
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\Config;
use CrazyPHP\Model\Env;

/**
 * Create new Application
 *
 * Classe for create step by step new application
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
            "default"       =>  "project",
            "select"        =>  [
                "project"       =>  "Project",
                "library"       =>  "Library",
                "other"         =>  "Undifined",
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
        # Database
        [
            "name"          =>  "database",
            "description"   =>  "Type of database used by your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "None",
            "select"        =>  [
                "mangodb"       =>  "MongoDB",
                "mariadb"       =>  "MariaDB",
                "none"          =>  "No Database",
                /* "mysql"         =>  "MySQL", */
                /* "postgresql"    =>  "PostgreSQL", */
            ],
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
         * Run Database
         * 0. Try to connect to database
         * 1. Create user
         * 2. Create database
         * 3. Create auth tables
         * 4. Create app tables
         */
        $this->runDatabase();    
        
        /**
        * Run Users
        * 0. Create permissions
        * 1. Create default users
        */
       $this->runUsers();

       /**
        * Run Webpack
        * 1. Prepare webpack config
        */
       $this->runWebpack();

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

        # Check input > application is set and not empty
        if(empty($this->inputs['application'] ?? []))
            
            # New error
            throw new CrazyException(
                "Input of application create is missing", 
                500,
                [
                    "custom_code"   =>  "create-002",
                ]
            );

        # Decalare input
        $inputs = $this->inputs["application"];

        # Wash input
        $inputs = Process::wash($inputs, Composer::DEFAULT_PROPERTIES);

        # Compilate inputs
        $inputs = Process::compilate($inputs);

        # Sort inputs
        $inputs = Process::sortByConditions($inputs, Composer::DEFAULT_PROPERTIES);

        # Stretch inputs
        $inputs = Arrays::stretch($inputs);

        # Get path of composer
        $composer = getcwd()."/composer.json";

        # Check json file exists
        if(!Json::check($composer))

            # Create composer file
            Composer::create($composer);

        # Set composer.json
        # - Reqire script to be executed from the project folder 
        Composer::set($inputs, $composer);

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
        if(empty($this->inputs['application'] ?? []))
            
            # New error
            throw new CrazyException(
                "Input of application create is missing", 
                500,
                [
                    "custom_code"   =>  "create-003",
                ]
            );

        # Decalare input
        $inputs = $this->inputs["application"];

        # Adapt inputs
        Package::adaptCreateInputs($inputs);

        # Wash input
        $inputs = Process::wash($inputs, Package::DEFAULT_PROPERTIES);

        # Compilate inputs
        $inputs = Process::compilate($inputs);

        # Sort inputs
        $inputs = Process::sortByConditions($inputs, Package::DEFAULT_PROPERTIES);

        # Stretch inputs
        $inputs = Arrays::stretch($inputs);

        # Get path of composer
        $composer = getcwd()."/package.json";

        # Check json file exists
        if(!Json::check($composer))

            # Create composer file
            Package::create($composer);

        # Set composer.json
        # - Reqire script to be executed from the project folder 
        Package::set($inputs, $composer);

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

        # Define Root
        $root = getcwd();

        # New structure instance
        $structure = new Structure($root);

        # Create new structure
        $structure->run();

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
     * 
     * @return Create
     */
    public function runConfig():Create {

        # Set config
        Config::set();

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
     * Run Users
     * 
     * Steps :
     * 0. Create permissions
     * 1. Create default users
     * @return Create
     */
    public function runUsers():Create {

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
     * Run First Compilation
     * 
     * @return Create
     */
    public function runFirstCompilation():Create {

        # Return instance
        return $this;

    }
}