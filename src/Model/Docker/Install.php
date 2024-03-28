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
namespace CrazyPHP\Model\Docker;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\Mkcert;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\System\Os;
use CrazyPHP\Library\File\File;
use League\CLImate\CLImate;
use CrazyPHP\Model\Env;

/**
 * Create new Application
 *
 * Classe for create step by step new application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Install implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Configuration
        [
            "name"          =>  "configuration",
            "description"   =>  "Type of configuration to set up on your crazy docker",
            "type"          =>  "ARRAY",
            "default"       =>  "https",
            "multiple"      =>  true,
            "select"        =>  [
                "http"          =>  "Http",
                "https-online"  =>  "Https (Online using Certbot)",
                "https-local"   =>  "Https (Local using Mkcert)"
            ],
        ],
    ];

    /** Private Parameters
     ******************************************************
     */

    /**
     * Inputs
     */
    private $inputs = [];

    /**
     * Server Name
     */
    private $serverName = null;

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

        # Add env that declare cache must use FILES
        Env::set(["cache_driver"=>"Files"]);

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
     * Run
     * 
     * Run current command
     *
     * @return self
     */
    public function run():self {

        /**
         * Run Check Vhost
         * 1. Run Check and Set if needed vhost depending of the OS
         */
        $this->runCheckVhost();

        /**
         * Run Mkcert setup
         * 1. Prepare cert for htttps on local
         */
        $this->runMkcertSetup();

        /**
         * Run Structure Folder
         * 1. Prepare folder structure
         */
        $this->runStructureFolder();

        /**
         * Run Certbot Dry Run
         * 1. Run Certbot Dry Run to check everything is working well regarding Certbot :
         * `docker compose run --rm  certbot certonly --webroot --webroot-path /var/www/certbot/ --dry-run -d localhost`
         */
        $this->runCertbotDryRun();

        /**
         * Run Update Database Config
         * 1. Set docker information in config of databases
         */
        $this->runUpdateDatabaseConfig();

        /**
         * Run Docker Compose Build
         * 1. Build docker-compose container
         */
        $this->runDockerComposeBuild();

        # Return instance
        return $this;

    }

    /**
     * Run Check Vhost
     * 
     * Run Check and Set if needed vhost depending of the OS
     * 
     * @return self
     */
    public function runCheckVhost():self {

        # Get data
        $data = $this->_getData(true);

        # Check https in configuration
        if(in_array("https-online", $data["configuration"]) || in_array("https-local", $data["configuration"])){
            
            # Check website value
            if(Config::has("App.server.name")){

                # Get servername
                $serverName = Config::getValue("App.server.name");

                # Check servername
                if(!$serverName)

                    # New error
                    throw new CrazyException(
                        "Server name (App.server.name) in config/App.yml is empty, please fill it and retry.", 
                        500,
                        [
                            "custom_code"   =>  "install-docker-001",
                        ]
                    );

                # Get host
                $host = Config::getValue("App.server.host") ?: "127.0.0.1";

            }else{

                # Get app name
                $appName = Config::getValue("App.name");

                # Check app name
                if(!$appName || !is_string($appName))

                    # New error
                    throw new CrazyException(
                        "App name (App.name) in config/App.yml is empty, please fill it and retry.", 
                        500,
                        [
                            "custom_code"   =>  "install-docker-002",
                        ]
                    );

                # Keep last part
                $exploded = explode("/", $appName);

                # Clean appname
                $appName = Process::alphanumeric(end($exploded));

                # Set server name
                $serverName = "$appName.com";

                # Set servername
                Config::setValue("App.server.name", $serverName);

                # Set host
                $host = "127.0.0.1";

                # Set ip
                Config::setValue("App.server.host", $host);

            }

            # Check if is in hosts path
            if(Os::isInHostsFile($host, $serverName)){

                # Message
                echo "✅ $serverName well set on the hosts file of your ".Os::getOs().PHP_EOL;;

            }else{

                # Echo alert
                echo "ℹ️  Root or admin password will be ask for update the hosts file".PHP_EOL;

                if(Os::appendToHostsFile($host, $serverName)){

                    # Echo
                    echo "✅ Server name successfully appended to the hosts file.".PHP_EOL;
                
                }else{

                    # New error
                    throw new CrazyException(
                        "Failed to append `$host $serverName` to the hosts file `".Os::getHostPath()."`. Please add it manually and retry.", 
                        500,
                        [
                            "custom_code"   =>  "install-docker-003",
                        ]
                    );
                
                }

            }
            
        }else{

            # Message
            echo "ℹ️  Step disabled".PHP_EOL;

        }

        # Return instance
        return $this;

    }

    /**
     * Run Mkcert setup
     * 
     * Prepare cert for htttps on local
     * 
     * @return self
     */
    public function runMkcertSetup():self {

        # Get data
        $data = $this->_getData(true);

        # Check https in configuration
        if(in_array("https-local", $data["configuration"])){

            # Check mkcert is installed
            if(!Mkcert::isInstalled()){

                # Echo
                echo "🔴 Ensure mkcert is installed on your system".PHP_EOL;
                
                # Check os
                if(Os::isMac()){

                    # Echo
                    echo 'Install with homebrew :'.PHP_EOL;
                    echo '- Command `brew install mkcert`'.PHP_EOL;
                    echo '- Command `brew install nss # if you use Firefox`'.PHP_EOL;
                    echo 'Install with MacPorts :'.PHP_EOL;
                    echo '- Command `sudo port selfupdate`'.PHP_EOL;
                    echo '- Command `sudo port install mkcert`'.PHP_EOL;
                    echo '- Command `sudo port install nss # if you use Firefox`'.PHP_EOL;

                }else{

                    # Echo
                    echo 'Visit page https://github.com/FiloSottile/mkcert#installation for more information'.PHP_EOL;

                }

                # Stop method
                exit;
            
            }else{

                # Echo
                echo "✅ Mkcert is well installed".PHP_EOL;

                # Echo alert
                echo "ℹ️  Root or admin password will be ask for install certificates".PHP_EOL;

                # Run mkcert setup
                $resultMkcertRun = Mkcert::run();

            }
            
        }else{

            # Message
            echo "ℹ️  Step disabled".PHP_EOL;

        }

        # Return instance
        return $this;

    }

    /**
     * Run Structure Folder
     * 
     * Prepare folder structure
     * 
     * @return self
     */
    public function runStructureFolder():self {

        # Get path of structure
        $structurePath = File::path(Docker::STRUCTURE_PATH);

        # Get data for render
        $data = self::_getData();

        # Run creation of docker structure
        Structure::create($structurePath, $data);

        # Return instance
        return $this;

    }

    /**
     * Run Certbot Dry Run
     * 
     * Run Certbot Dry Run to check everything is working well regarding Certbot :
     * `docker compose run --rm  certbot certonly --webroot --webroot-path /var/www/certbot/ --dry-run -d localhost`
     * 
     * @return self
     */
    public function runCertbotDryRun():self {

        # Get data
        $data = $this->_getData(true);

        # Check https in configuration
        if(in_array("https-online", $data["configuration"])){

            # Set serverName
            $serverName = Config::getValue("App.server.name");

            # Check server name
            if(!is_string($serverName) || !$serverName || strpos($serverName, ".") === false)

                # New error
                throw new CrazyException(
                    "Server name (App.server.name) in config/App.yml is empty, please fill it and retry.", 
                    500,
                    [
                        "custom_code"   =>  "install-docker-003Ò",
                    ]
                );

            # Exec command
            $result = Docker::run("--rm certbot certonly --webroot --webroot-path /var/www/certbot/ --dry-run -d ".$serverName);

            # Echo
            echo "🔴 Feature not fully implemented yet !";
            exit;

        }else{

            # Message
            echo "ℹ️  Step disabled".PHP_EOL;

        }

        # Return instance
        return $this;

    }

    /**
     * Run Update Database Config
     * 
     * Set docker service name in config of databases
     * 
     * @return self
     */
    public function runUpdateDatabaseConfig():self {

        # Get database config
        $databaseConfig = Config::getValue("Database.collection");

        # Iteration config of databse
        foreach($databaseConfig as $name => $config)

            # If name is in parameters
            if(array_key_exists($name, Docker::DATABASE_TO_SERVICE) && $databaseConfig[$name]){

                # Set config
                Config::setValue("Database.collection.$name.docker.service.name", Docker::DATABASE_TO_SERVICE[$name], true);

            }

        # Return instance
        return $this;

    }
    
    /**
     * Run Docker Compose Build
     * 
     * Build docker compose containes
     * 
     * @return self
     */
    public function runDockerComposeBuild():self {

        # Set env file
        $envFile = Docker::ENV_FILE;

        # Exec command
        Command::exec("docker-compose", (($envFile && File::exists($envFile)) ? " --env-file '".$envFile."' " : "")."build", true);

        # Return self
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
     * @param bool $onlyInput Return only input
     * @return array
     */
    private function _getData(bool $onlyInput = false):array {

        # Set result
        $result = [];

        # Check onlyInput
        if(!$onlyInput){

            # Set config
            $config = Config::get([
                "App", "Database"
            ]);

            # Push config in result
            $result['_config'] = $config;

            # Add ";" at the end of the last package
            $packages = $result["_config"]["App"]["dependencies"]["php"]["packages"];

            # Check packages
            if(is_array($packages) && !empty($packages)){

                # Extract last package
                $lastPackage = array_pop($packages);

                # Push it
                $packages[] = "$lastPackage;";

                # Push packages
                $result["_config"]["App"]["dependencies"]["php"]["packages"] = $packages;

            }

            # Check if windows
            if(Os::isWindows()){

                # Update _config.App.root
                $value = $config['_config']['App']['root'];
                
                # Change \ or \\ by /
                $value = str_replace(["\\", "\\\\"], "/", $value);

                # Split by : of disk
                $explodedValue = explode(":", $value, 2);

                # Set new value
                $value = "/mnt/".strtolower($explodedValue[0]).$explodedValue[1];

                # Set $value = _config.App.root
                $result['_config']['App']['root'] = $value;

            }
        
        }

        # Check inputs
        if(isset($this->inputs[array_key_first($this->inputs)]) && !empty($this->inputs[array_key_first($this->inputs)]))

            # Iterations of inputs
            foreach($this->inputs[array_key_first($this->inputs)] as $input)

                # Set key => value
                $result[$input["name"]] = $input["value"];

        # Return result
        return $result;

    }

}