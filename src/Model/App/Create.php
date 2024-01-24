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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Model\App;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Database\Database;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\String\Strings;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Model\Webpack\Run;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\Config;

/**
 * Create new Application
 *
 * Classe for create step by step new application
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
            "description"   =>  "Name of your crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPHP\Model\App\Create::defaultAppName",
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
                "application"   =>  "Application",
                "website"       =>  "Website",
                "api"           =>  "Only Api",
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
            "type"          =>  "ARRAY",
            "default"       =>  "None",
            "multiple"      =>  true,
            "select"        =>  [
                "mongodb"       =>  "MongoDB",
                "mariadb"       =>  "MariaDB",
                "mysql"         =>  "MySQL",
                "postgresql"    =>  "PostgreSQL",
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

    /** @var bool $npm_local */
    private $npm_local = false;

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
         * Backup Composer
         * 1. Copy initial composer config in backup folder
         */
        $this->runBackupComposer();

        /**
         * Run Composer
         * 1. Fill composer.json
         * 2. Run composer install
         */
        $this->runComposer();

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
         * Run NPM
         * 0. Check package.json exists
         * 1. Fill package.json
         * 2. Run npm install
         */
        $this->runNpm();

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
        * Composer Update
        * 1. Update Composer
        */
       $this->runComposerUpdate();

        /**
         * Run Front Script
         */
        $this->runFrontScript();

       /**
        * Run Webpack
        * 1. Prepare webpack config
        */
       $this->runWebpack();


        /**
         * Run Check Permission Node Modules
         * 1. Check permission of some directories
         */
        $this->runCheckPermissionNodeModules();

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
     * Backup Composer
     * 
     * Steps :
     * 1. Copy initial composer config in backup folder
     * 
     * @return Create
     */
    public function runBackupComposer():Create {
        
        # Composer path
        $composerPath = "@app_root/composer.json";

        # Check file exists
        if(!File::exists($composerPath))
            
            # New error
            throw new CrazyException(
                "How did you execute this code if your composer.json doesn't exist in your app folder ??", 
                500,
                [
                    "custom_code"   =>  "create-001",
                ]
            );

        # Get timestamp of now
        $currentTimestamp = time();

        # Copy the file in backup folder
        File::copy($composerPath, "@app_root/assets/Json/backup/composer/$currentTimestamp-new-composer.json");

        # Return instance
        return $this;

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

        # Push autoload_psr-4_App\\_0 in inputs
        $inputs[] = Composer::DEFAULT_PROPERTIES["autoload_psr-4_App\\_0"];

        # Wash input
        $inputs = Process::wash($inputs, Composer::DEFAULT_PROPERTIES);

        # Compilate inputs
        $inputs = Process::compilate($inputs);

        # Sort inputs
        $inputs = Process::sortByConditions($inputs, Composer::DEFAULT_PROPERTIES);

        # Stretch inputs
        $inputs = Arrays::stretch($inputs);

        # Get path of composer
        $composer = File::path("@app_root/composer.json");

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
     * Run Structure Folder
     * 
     * Steps : 
     * 1. Create structure folder
     * 2. Check permissions
     * 
     * @return Create
     */
    public function runStructureFolder():Create {

        # Get path of structure
        $structurePath = File::path(Structure::DEFAULT_TEMPLATE);

        # Get data for render
        $data = self::_getData();

        # Run creation of docker structure
        Structure::create($structurePath, $data);

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
        Config::setup();

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
        $inputs = array_merge(
            $this->inputs["application"],
            [   
                Package::DEFAULT_PROPERTIES["devDependencies"]
            ]
        );
        $inputs[array_key_last($inputs)]["name"]="devDependencies";

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

        # Set package.json
        # - Reqire script to be executed from the project folder 
        Package::set($inputs, $composer);

        # Set crazyphp package
        Package::setCrazyphpPackage();

        # Set default scripts
        Package::setDefaultScripts();

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

        # Get database from inputs
        $database = Arrays::filterByKey($this->inputs['application'], "name", "database");

        # Get database values
        $databaseValues = $database[array_key_first($database)]['value'] ?? [];

        # Check database values
        if(!empty($databaseValues))

            # Iteration of values
            foreach($databaseValues as $value)

                # Push setup in config of database
                Database::setupConfig($value);

        /* Vendor for Mongo DB */

        # Check if mongo set in current app
        if(in_array('mongodb', $databaseValues)){

            # Get package
            $packages = FileConfig::getValue("App.dependencies.php.packages");

            # check
            $key = (is_array($packages)) ? count($packages) : 0;

            # Push app dependances
            FileConfig::setValue("App.dependencies.php.packages.$key", "php8.2-mongodb");

            # Compose requiere
            Composer::requirePackage("mongodb/mongodb", true, false);

            # Php fast cache extension for mongo
            Composer::requirePackage("phpfastcache/mongodb-extension", true, false);

        }
                
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
     * Run Public
     * 
     * Steps : 
     * 1. Fill .htaccess
     * 2. Fill index.php
     * 
     * @return self
     */
    public function runComposerUpdate():self {

        # Composer Update
        $log = Composer::exec("update", "", false);

        # Return instance
        return $this;

    }

    /**
     * Run Front Script
     * 
     * Steps :
     * 
     * @return self
     */
    public function runFrontScript():self {

        # 

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

        # Check NPM local installation
        if(Command::exists("npm")){

            # Set config App.local.npm = true
            FileConfig::setValue("App.local.npm", true, true);

            # Set NPM local
            $this->npm_local = true;

        }

        # Check npm in local
        if(!$this->npm_local){

            # Echo message
            echo "Webpack will be run later, in docker instance.".PHP_EOL;

            # Stop current run
            return $this;

        }

        # Install npm dependences
        Package::exec("install", "", false);

        # Return instance
        return $this;

    }

    /**
     * Run Check Permission Node Modules
     * 
     * Steps :
     * 1. Check permission of some directories
     * 
     * @return Create
     */
    public function runCheckPermissionNodeModules():Create {

        # Folder to check permission
        $folders = ["@app_root/node_modules/.bin/webpack"];

        # Iteration des dossiers
        foreach($folders as $folder){

            # Get reel path
            $path = File::path($folder);

            # Check path
            if(file_exists($path) || is_dir($path)){

                # Check permission
                $result = chmod($path, 0755);

            # Create folder
            }else{

                # Create folder
                $result = mkdir($path, 0755, true);

            }

            # Check result
            if($result){

                # Echo message
                echo "Folder \"$path\" successfully checked ✅".PHP_EOL;
                
            }else{

                # Check if is dir
                if(is_dir($path))

                    # Echo message
                    echo "After installation, please execute \"mkdir 755 $path\" in your terminal with root permission ⚠️".PHP_EOL;

                else

                    # Echo message
                    echo "After installation, please execute \"chmod 755 $path\" in your terminal with root permission ⚠️".PHP_EOL;

            }

        }

        

        # Return instance
        return $this;

    }

    /** 
     * Run First Compilation
     * 
     * @return Create
     */
    public function runFirstCompilation():Create {

        # Check if npm installed in local
        if(!$this->npm_local)

            # Stop method
            return $this;

        # Get first script in package
        $scriptName = FileConfig::getValue("Front.scripts")[0];

        # Run Compilation
        $webpack = new Run();

        # Run webapck
        $webpack
            ->setScript($scriptName)
            ->run()
        ;

        # Return instance
        return $this;

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

    /** Public static methods | Default
     ******************************************************
     */

    /**
     * Default App Name
     * 
     * Try to return the best default app name
     * 
     * @return string
     */
    public static function defaultAppName():string {

        # Set result
        $result = "Crazy Project";

        # Get current app path
        $appPath = File::path("@app_root");

        # Check app path
        if($appPath){

            # Set composer path
            $conposerAppPath = "$appPath/composer.json";

            # Check composer name
            if(File::exists($conposerAppPath) && ($composerName = Composer::get("name", $conposerAppPath)) !== null)

                # Set result
                $result = $composerName;

            else

                # Extract name from app path
                $result = Strings::getLastString(trim($appPath, "/"), "/");

        }

        # Then add space before capital letters
        $result = Process::spaceBeforeCapital($result);

        # Return result
        return $result;

    }

}