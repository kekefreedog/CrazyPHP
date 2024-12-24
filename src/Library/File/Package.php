<?php declare(strict_types=1);
/**
 * File
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\App\Create;

/**
 * Package
 *
 * Methods for interacting with Npm files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Package{

    /** Constants
     ******************************************************
     */

    # Path of composer
    public const PATH = [
        # "package.json" =>  __DIR__."/../../../package.json",
        # "package-lock.json" =>  __DIR__."/../../../package-lock.json",
        "package.json" =>  "@app_root/package.json",
        "package-lock.json" =>  "@app_root/package-lock.json",
    ];

    # Crazy PHP package value default
    public const CRAZYPHP_PACKAGE_VALUE_DEFAULT = "file:./vendor/kzarshenas/crazyphp";

    # Default properties of composer
    public const DEFAULT_PROPERTIES = [
        # Name
        "name"          =>  Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  Create::REQUIRED_VALUES[1],
        # Version
        "version"       =>  [
            "name"          =>  "version",
            "description"   =>  "Version of your crazy project",
            "type"          =>  "VARCHAR",
        ],
        # Keywords
        "keywords"      =>  [
            "name"          =>  "keywords",
            "description"   =>  "Keywords about your app",
            "type"          =>  "ARRAY",
        ],
        # Homepage
        "homepage"      =>  Create::REQUIRED_VALUES[5],
        # Licence
        "license"       =>  [
            "name"          =>  "licence",
            "description"   =>  "Licence of your app",
            "type"          =>  "VARCHAR",
        ],
        # Author name
        "authors_name" =>  [
            "name"          =>  "authors_name",
            "description"   =>  "Author of this crazy project",
            "type"          =>  "VARCHAR",
            "default"       =>  "CrazyPerson",
            "required"      =>  true,
            "process"       =>  ['trim'],
        ],
        # Author name
        "authors_email"=>  [
            "name"          =>  "authors_email",
            "description"   =>  "Email of the crazy author",
            "type"          =>  "VARCHAR",
            "default"       =>  "crazy@person.com",
            "required"      =>  true,
            "process"       =>  ['trim'],
            "validate"      =>  ['email'],
        ],
        # Authors
        "authors"       =>  [
            "name"          =>  "authors",
            "description"   =>  "Authors of your app",
            "type"          =>  "ARRAY",
        ],
        # Funding
        "funding"       =>  [
            "name"          =>  "funding",
            "description"   =>  "Funding information of your app",
            "type"          =>  "ARRAY",
        ],
        # Dependencies
        "devDependencies"  =>  [
            "name"          =>  "devDependencies",
            "description"   =>  "Dev Dependencies of your app",
            "type"          =>  "ARRAY",
            "default"       =>  self::DEFAULT_DEV_DEPENDENCIES,
            "required"      =>  true,
        ]
    ];

    /** @const array DEFAULT_DEPENDENCIES */
    public const DEFAULT_DEV_DEPENDENCIES = [
        # Front
        "@fortawesome/fontawesome-free"             =>  "^6.5.1",
        "@materializecss/materialize"               =>  "2.1.0",
        "material-dynamic-colors"                   =>  "*",
        "@material/material-color-utilities"        =>  "^0.3.0",
        "clipboard"                                 =>  "*",
        "handlebars"                                =>  "*",
        "handlebars-helpers"                        =>  "^0.9.8",
        "handlebars-utils"                          =>  "^1.0.6",
        "sweetalert2"                               =>  "*",
        "@sweetalert2/theme-dark"                   =>  "*",
        "@types/quill"                              =>  "*",
        "tippy.js"                                  =>  "*",
        "material-symbols"                          =>  "*",
        "material-icons"                            =>  "*",
        "@material-design-icons/font"               =>  "^0.14.2",
        "i18next"                                   =>  "^22.0.8",
        "material-dynamic-colors"                   =>  "*",
        "tom-select"                                =>  "*",
        "@simonwep/pickr"                           =>  "^1.9.1",
        "maska"                                     =>  "^3.0.2",
        "filepond"                                  =>  "^4.32.6",
        "filepond-plugin-image-exif-orientation"    =>  "^1.0.11",
        "filepond-plugin-image-preview"             =>  "^4.6.12",
        # Cache
        "localforage"                               =>  "^1.10.0",
        "object-hash"                               =>  "^3.0.0",
        # Back | Webpack            
        "webpack"                                   =>  "*",
        "webpack-cli"                               =>  "*",
        "webpack-dev-server"                        =>  "*",
        "url-loader"                                =>  "*",
        "file-loader"                               =>  "*",
        "html-loader"                               =>  "*",
        "style-loader"                              =>  "*",
        "css-loader"                                =>  "*",
        "sass-loader"                               =>  "*",
        "ts-loader"                                 =>  "*",
        "yaml-loader"                               =>  "*",
        "handlebars-loader"                         =>  "*",
        "svg-inline-loader"                         =>  "*",
        "svg-loader"                                =>  "*",
        # Custom file
        "js-yaml"                                   =>  "*",
        "repair"                                    =>  "*",
        # Back | Sass           
        "sass"                                      =>  "*",
        # Back | Ts         
        "typescript"                                =>  "*",
        "tslib"                                     =>  "*",
        "fork-ts-checker-webpack-plugin"            =>  "*",
        "fork-ts-checker-notifier-webpack-plugin"   =>  "*",
    ];

    # Default value
    const DEFAULT_VALUE = [
    ];

    /** @const array DEFAULT_SCRIPTS */
    public const DEFAULT_SCRIPTS = [
        "build" =>  "webpack --mode production --config webpack.prod.js",
        "dev"   =>  "webpack --mode development --config webpack.dev.js",
        "watch" =>  "webpack --watch --mode development --config webpack.dev.js"
    ];

    /* @const array COMMAND_SUPPORTED supported command */
    public const COMMAND_SUPPORTED = [
        "install"   =>  [
            "command"   =>  "i"
        ],
        "update"   =>  [
            "command"   =>  "up"
        ],
        "uninstall"    =>  [
            "command"   =>  "r"
        ],
        "search"    =>  [
            "command"   =>  "s"
        ],
        "start"     =>  [
            "command"   =>  "start"
        ],
        "run"       =>  [
            "command"   =>  "run"
        ],
    ];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Read value in package.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function create(string $path):array{

        # Declare result
        $result = [];

        # Check parameter in path
        if(array_key_exists($path, self::PATH))

            # Update path
            $path = self::PATH[$path];

        # Get path
        $path = File::path($path);

        # Check if file already exists
        if(!file_exists($path))

            # Get collection of file
            $result = Json::create($path, self::DEFAULT_VALUE);
        
        # Return result
        return $result;

    }

    /**
     * Read value in package.json
     *
     * @param string $parameter Parameter to read
     * @param string $file File to read data
     * @return string
     */
    public static function read(string $parameter = "", string $file = "package.json") {

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # Get path
        $file = File::path($file);

        # update
        return Composer::read($parameter, $file);

    }
    
    /**
     * Set value in composer.json
     * 
     * Set value in composer.json from array :
     * 1. {parameter:"value",...}
     *
     * @param array $values Values to put on composer.json
     * @param string $file File composer.json
     * @return array
     */
    public static function set(array $values = [], string $file = "package.json"):array {

        # Set result
        $result = [];

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # Get reel path
        $file = File::path($file);

        # Process value
        self::process($values);

        # Set values in package.json
        $result = Json::set($file, $values, true);

        # Return result
        return $result;

    }

    /**
     * Set Value
     * 
     * Set value in package.json
     * 
     * @param string $key Parameter of config to set
     * @param mixed $data Content to set
     * @param bool $createIfNotExists Create parameter if not exists
     * @param string $path Path of the package.json
     * @return void
     */
    public static function setValue(string $key = "", mixed $data = [], bool $createIfNotExists = true, string $path = self::PATH["package.json"]):void {

        # Parse key
        $key = str_replace(FileConfig::SEPARATOR, "___", $key);

        # Explode keys 
        $keys = explode("___", $key);

        # Check config file
        if(!$path || empty($keys))

            # Stop script
            return;

        # Check not multiple file
        if(!File::exists($path))

            # New Exception
            throw new CrazyException(
                "No config file found for \"".$keys[0]."\".", 
                500,
                [
                    "custom_code"   =>  "package-001",
                ]
            );

        # Open File
        $fileData = Json::open($path);

        # Check if is array
        if(!is_array($fileData))

            # New Exception
            throw new CrazyException(
                "package.json isn't valid... Array awaited !", 
                500,
                [
                    "custom_code"   =>  "package-011",
                ]
            );

        

        # Declare cursor
        $cursor = &$fileData;

        # Iteration filedata
        $i=0;while(isset($keys[$i])){

            # Check cursor.key isset
            if(!isset($cursor[$keys[$i]]))

                # Check if key should be create
                if($createIfNotExists)

                    # Create key
                    $cursor[$keys[$i]] = [];

                # Else
                else

                    # Exit
                    return;

            # Update the cursor
            $cursor = &$cursor[$keys[$i]];

        $i++;}

        # Set value in cursor
        $cursor = $data;

        # Set last resultCursor
        $result = $fileData;

        # Set value
        Json::set($path, $result, true);

    }

    
    /**
     * Read value in package.json
     *
     * @param array $values Values to update on composer.json
     * @param bool $createIfNotExists create parameter if doesn't exists
     * @param string $file File composer.json
     * @return array
     */
    public static function update(array $values = [], bool $createIfNotExists = false, string $file = "composer.json"):array{

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # Get path
        $file = File::path($file);

        # update
        return Composer::update($values, $createIfNotExists, $file);

    }
    
    /**
     * Delete value in package.json
     *
     * @param array $values Values to update on composer.json
     * @return string
     */
    public static function delete(array $values = []):bool{

        # Delete
        return (bool) Composer::delete($values);

    }
    
    /**
     * Adapt Create Inputs
     * 
     * Adapt create inputs for package.json
     * 
     * @param array $inputs Input to process
     * @return void
     */
    public static function adaptCreateInputs(array &$inputs = []):void {

        # Table of conversion
        $conversionCollection = [
            "authors__name"     =>  "authors_name",
            "authors__email"    =>  "authors_email",
        ];

        # Check inputs
        if(!empty($inputs))

            # Iteration inputs
            foreach($inputs as &$input)

                # Iteration of conversionCollection
                foreach($conversionCollection as $search => $replacement)

                    # if search
                    if($input["name"] == $search)

                        # Replace name
                        $input["name"] = $replacement;

    }

    /**
     * Process value
     * 
     * Process value for composer.json
     *
     * @param array $inputs Values to process for composer.json
     * @return void
     */
    public static function process(array &$inputs = []):void{

        # Check name
        if(isset($inputs["name"]))

            # Clean name
            $inputs["name"] = Process::clean($inputs["name"])
            ;

    }

    /**
     * Exec
     * 
     * Execute command
     * 
     * @param string $commandName Command name to execute
     * @param string $argument Argument for the command
     * @param string $checkError Check error of exec
     * @param bool $liveResult Display result in live
     * @return
     */
    public static function exec(string $commandName = "", string $argument = "", bool $checkError = true, bool $liveResult = false) {

        # Result
        $result = null;

        # Check command
        if(!$commandName || !array_key_exists($commandName, self::COMMAND_SUPPORTED))
                
            # New error
            throw new CrazyException(
                "\"$commandName\” isn't supported with Npm (package)", 
                500,
                [
                    "custom_code"   =>  "package-001",
                ]
            );

        # Check docker config
        if(
            !FileConfig::getValue("App.local.npm") &&
            Config::exists("Docker") &&
            FileConfig::has("Docker.services.node.Service") &&
            $dockerNodeService = FileConfig::getValue("Docker.services.node.Service")
        )

                # Prepare docker
                $dockerCommand = "docker run $dockerNodeService ";

        # Else
        else

            # Empty docker command
            $dockerCommand = "";

        # Peepare command
        $argument = self::COMMAND_SUPPORTED[$commandName]["command"].($argument ? " $argument" : "");

        # Get root of your app
        $rootPath = FileConfig::getValue("App.root");

        # Get result of exec
        $result = Command::exec($dockerCommand."npm --prefix $rootPath", $argument, $liveResult);

        # Check result
        if($checkError && ($result["result_code"] !== null || $result["result_code"] > 0))
            
            # New error
            throw new CrazyException(
                "Npm (package) ".$argument." failed".(is_array($result["output"]) ? " : ".json_encode($result["output"]) : ""),
                500,
                [
                    "custom_code"   =>  "package-002",
                ]
            );

        return $result;

    }

    /** Public Static Methods | Dependencies
     ******************************************************
     */

    /**
     * Install Dependencies
     * 
     * Install all dependencies in package
     * 
     * @return void
     */
    public static function installDependencies():void {

        # Prepare command
        $result = self::exec("install");

    }

    /**
     * Update Dependencies
     * 
     * Update all dependencies in package
     * 
     * @return void
     */
    public static function updateDependencies():void {

        # Prepare command
        $result = self::exec("update");

    }

    /**
     * Add Dependency
     * 
     * Add dependency in package
     * 
     * @param string $name Name of the package
     * @param string $version Version of the package (optionnal)
     * @param bool $devDependency Set package as dev dependency
     * @return void
     */
    public static function addDependency(string $name = "", string $version = "latest", bool $devDependency = false):void {

        # Check name
        if(!$name)

            # Stop function
            return;

        # Append name in command
        $command = $name;

        # Check if version
        if($version)

            # Append version in command
            $command .= "@$version";

        # Prepare command
        $result = self::exec("install", $command.($devDependency ? " --save-dev" : ""));

    }

    /**
     * Update Dependency
     * 
     * Add dependency in package
     * 
     * @param string $name Name of the package
     * @param string $version Version of the package (optionnal)
     * @return void
     */
    public static function updateDependency(string $name = "", string $version = ""):void {

        # Check name
        if(!$name)

            # Stop function
            return;

        # Append name in command
        $command = $name;

        # Check if version
        if($version)

            # Append version in command
            $command .= "@$version";

        # Prepare command
        $result = self::exec($version ? "install" : "update", $command);

    }

    /**
     * Remove Dependency
     * 
     * Remove dependency in package
     * 
     * @param string $name Name of the package
     * @return void
     */
    public static function removeDependencies(string $name = ""):void {

        # Check name
        if(!$name)

            # Stop function
            return;

        # Append name in command
        $command = $name;

        # Prepare command
        $result = self::exec("uninstall", $command);

    }

    /** Public Static Methods | Read Package
     ******************************************************
     */

    /**
     * Get Scripts Name
     * 
     * Get Scripts Name inside Package
     * 
     * @return ?array
     */
    public static function getScriptsName():?array {

        # Set result
        $result = null;

        # Get package
        $package = File::open(static::PATH["package.json"]);

        # Check if scripts exists
        if(!array_key_exists("scripts", $package) || empty($package["scripts"]))

            # Return result
            return $result;

        # Get keys of package
        $result = array_keys($package["scripts"]);

        # Return null
        return $result;

    }

    /**
     * Has Script
     * 
     * Check Script has Script Name
     * 
     * @param string $name Name of the script
     * @return bool
     */
    public static function hasScript(string $name = ""):bool {

        # Set result
        $result = false;

        # Check name
        if(!$name)

            # Return result
            return $result;

        # Get Package content
        $package = File::open(static::PATH["package.json"]);

        # Check script
        if(array_key_exists("scripts", $package) && isset($package["scripts"][$name]))

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Set Default Script
     * 
     * Set Default script on package
     * @return void
     */
    public static function setDefaultScripts():void {

        # Set default config into package
        static::set(
            ["scripts"   =>  self::DEFAULT_SCRIPTS],
            "@app_root/package.json"
        );

        # Update front config
        FileConfig::setValue("Front.scripts", array_keys(self::DEFAULT_SCRIPTS));

    }

    /**
     * Set CrazyPHP Package
     * 
     * Set crazy php (js) on package.json
     * 
     * @param string $value Value to set to crazyphp package
     * @param bool $devDependency Set package as a dev dependency rather than a dependency
     * @return void
     */
    public static function setCrazyphpPackage(string $value = self::CRAZYPHP_PACKAGE_VALUE_DEFAULT, bool $devDependency = true):void {

        # Check value
        if(!$value)

            # Set default value
            $value = static::CRAZYPHP_PACKAGE_VALUE_DEFAULT;

        # Set key
        $key = "crazyphp";

        # Set parent
        $parent = $devDependency
            ? "devDependencies"
            : "dependencies"
        ;

        # Set package in package.json
        static::update([
            "devDependencies"   =>  [

            ]
        ]);

        # Set value in package.json
        static::setValue("$parent.$key", $value);

    }

}