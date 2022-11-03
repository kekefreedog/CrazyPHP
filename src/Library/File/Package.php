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
 * @copyright  2022-2022 Kévin Zarshenas
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
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\App\Create;

/**
 * Package
 *
 * Methods for interacting with Npm files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Package{

    /** Constants
     ******************************************************
     */

    # Path of composer
    public const PATH = [
        "package.json" =>  __DIR__."/../../../package.json",
        "package-lock.json" =>  __DIR__."/../../../package-lock.json",
    ];

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
        "@materializecss/materialize"   =>  "*",
        "handlebars"                    =>  "*",
        "sweetalert2"                   =>  "*",
        "tippy.js"                      =>  "*",
        "@fortawesome/fontawesome-free" =>  "*",
        "material-symbols"              =>  "*",
        "material-icons"                =>  "*",
        "@clipboard"                    =>  "*",
        # Back | Webpack
        "webpack"                       =>  "*",
        "webpack-cli"                   =>  "*",
        "url-loader"                    =>  "*",
        "file-loader"                   =>  "*",
        "rimraf"                        =>  "*",
        "remove-files-webpack-plugin"   =>  "*",
        "style-loader"                  =>  "*",
        "css-loader"                    =>  "*",
        "mini-css-extract-plugin"       =>  "*",
        "sass-loader"                   =>  "*",
        # Back | Sass
        "sass"                          =>  "*",
    ];

    # Default value
    const DEFAULT_VALUE = [
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
    public static function read(string $parameter = "", string $file = "package.json"):string {

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

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

        # Process value
        self::process($values);

        # Set values in package.json
        $result = Json::set($file, $values, true);

        # Return result
        return $result;

    }
    
    /**
     * Read value in package.json
     *
     * @param string  $values Values to update on composer.json
     * @param string $createIfNotExists create parameter if doesn't exists
     * @param string $file File composer.json
     * @return array
     */
    public static function update(array $values = [], bool $createIfNotExists = false, string $file = "composer.json"):array{

        # Check parameter in path
        if(array_key_exists($file, self::PATH))

            # Get value of index
            $file = self::PATH[$file];

        # update
        return Composer::update($values, $createIfNotExists, $file);

    }
    
    /**
     * Delete value in package.json
     *
     * @param string  $values Values to update on composer.json
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
     * @return
     */
    public static function exec(string $commandName = "", string $argument = "", bool $checkError = true) {

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
        if(Config::exists("Docker") && FileConfig::has("Docker.services.node.Service") && $dockerNodeService = FileConfig::getValue("Docker.services.node.Service"))

                # Prepare docker
                $dockerCommand = "docker run $dockerNodeService ";

        # Else
        else

            # Empty docker command
            $dockerCommand = "";

        # Peepare command
        $argument = self::COMMAND_SUPPORTED[$commandName]["command"].($argument ? " $argument" : "");

        # Get result of exec
        $result = Command::exec($dockerCommand."npm", $argument);

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
     * @return void
     */
    public static function addDependency(string $name = "", string $version = "latest"):void {

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
        $result = self::exec("install", $command);

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

}