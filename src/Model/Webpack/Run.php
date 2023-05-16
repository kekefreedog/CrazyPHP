<?php declare(strict_types=1);
/**
 * Model
 *
 * Classe for define framework models
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Webpack;

/**
 * Dependances
 */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\System\Os;
use DateTime;

/**
 * Webpack/Run
 *
 * Model for run wabpack
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Run extends CrazyModel implements CrazyCommand {

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return self
     */
    public function __construct(array $inputs = []){

        # Ingest data
        $this->inputs = $inputs;

        # Set scripts
        $this->script = $this->inputs["args"][0] ?? null;

        ## Set flag
        $this->setFlags($this->inputs["args"]);

    }

    /** Public Parameters
     ******************************************************
     */

    /** @var array $input Input data received */
    public array $inputs;

    /** Private Parameters
     ******************************************************
     */

    /** @var ?string $script Script name to run  */
    private $script = null;

    /** @var bool $watch Bool for check if watch mode is enable */
    private $watch = false;

    /** @var array $flags */
    private $flags = [];

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
        $result = [];

        # Return self
        return $result;

    }

    /** Public methods | Script
     ******************************************************
     */

    /**
     * Set Script
     * 
     * Set Script Name to Run
     * 
     * @param string $name Name of the script
     * @return self
     */
    public function setScript(string $name = ""):self {

        # Check name
        if($name){

            # Check script

            # /!\ Path of the package isn't valid

            if(!Package::hasScript($name))
                
                # New error
                throw new CrazyException(
                    "\"$name\” script doesn't exists into Npm package", 
                    500,
                    [
                        "custom_code"   =>  "run-001",
                    ]
                );

            # Set script
            $this->script = $name;

        }

        # Stop method
        return $this;


    }

    /** Public methods
     ******************************************************
     */

    /**
     * Run
     * 
     * Run current command
     *
     * @return self
     */
    public function run():self {

        /**
         * Run Install Npm Dependances
         * 1. Install npm dependances
         */
        $this->runNpmInstall();

        /**
         * Run Check Watch Script
         * 1. Check if watch script and add it to Config Front
         */
        $this->runCheckIfWatch();

        /**
         * Run Webpack Script
         * 1. Generate js file for js and ts front scripts
         */
        $this->runWebpackScript();

        /**
         * Run Set Generated Files on Config
         * 1. Put files generated on config Front
         */
        $this->runGeneratedFilesOnConfig();


        # Return self
        return $this;

    }

    /**
     * Run Npm Install
     * 
     * 1. Install npm dependances
     * 
     * @return self
     */
    public function runNpmInstall():self {

        # Install npm dependences
        Package::exec("install", "", false);

        # Return instance
        return $this;

    }

    /**
     * Run Check If Watch
     * 
     * Run Check Watch Script
     * 
     * Steps :
     * 1. Check if watch script and add it to Config Front
     * 
     * @return self
     */
    public function runCheckIfWatch():self {

        # Get read script
        $scripts = Package::read("scripts");

        # Get command of current script that will be executed
        $command = $scripts[$this->script];

        # Check if "--watch" in command
        if(strpos($command, "--watch") !== false){

            # Push value in front config
            Config::setValue("Front.lastBuild.watch", true);

            # Set watch
            $this->watch = true;

        }

        # Return self
        return $this;

    }

    /**
     * Run Webpack Script
     * 
     * Steps :
     * 1. Generate js file for js and ts front scripts
     * 
     * @return self
     */
    public function runWebpackScript():self {

        # Check if watch
        if($this->watch)

            # Watch Script In Progress 
            echo 
                "Current watch is in progress... (Press `Ctrl` + `C` to stop script)"
            ;

        # Run script
        $result = Package::exec(
            "run", 
            $this->script . (!empty($this->flags) ? " ".implode(" ", $this->flags) : ""),
            false, 
            $this->watch ? false : true
        );

        # Return self
        return $this;

    }

    /**
     * Run Generated File On Config
     * 
     * Steps :
     * 1. Run Set Generated Files on Config
     * 
     * @return self
     */
    public function runGeneratedFilesOnConfig():self {

        # Set result
        $result = [
            "files" =>  null,
            "pages" =>  null,
            "hash"  =>  null,
            "date"  =>  null,
            "watch" =>  false,
        ];

        ##  Search new generated files
        # New finder
        $finder = new Finder();

        # Prepare finder
        $finder
            ->files()
            ->name('*.js')
            ->depth('== 0')
            ->in(File::path("@app_root/public/dist"))
        ;

        # Check if finder has result
        if($finder->hasResults()){

            # Convert files
            $result["files"] = [];

            # Iteration of files
            foreach ($finder as $file) {

                # Set in files
                $result["files"][] = $file->getRelativePathname();

                # Get hash
                if(!$result["hash"]){

                    # Explode name of the file
                    $exploded = explode(".", str_replace(".js", "", $file->getFilename()));

                    # Set hash
                    $result["hash"] = array_pop($exploded);

                }

            }

        }

        ## Search new page generated files

        # New finder
        $finder = new Finder();

        # Prepare finder
        $finder
            ->files()
            ->name('*.js')
            ->depth('== 0')
            ->in(File::path("@app_root/public/dist/page/app"))
        ;

        # Check if finder has result
        if($finder->hasResults()){

            # Convert files
            $result["pages"] = [];

            # Iteration of files
            foreach ($finder as $file) {

                # Set in files
                $result["pages"][] = $file->getRelativePathname();

                # Get hash
                if(!$result["hash"]){

                    # Explode name of the file
                    $exploded = explode(".", str_replace(".js", "", $file->getFilename()));

                    # Set hash
                    $result["hash"] = array_pop($exploded);

                }

            }

        }

        # Set date
        $result["date"] = (new DateTime())->format("c");

        # Push value in front config
        Config::setValue("Front.lastBuild", $result);

        # Return self
        return $this;

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Set Flags
     * 
     * Set flags in current instance
     * 
     * @param array $arguments
     * @return void
     */
    private function setFlags(array $arguments = []) {

        # Delete first argument
        array_shift($arguments);

        # Check arguments
        if(empty($arguments))

            # Stop
            return;

        # Iteration arguments
        foreach($arguments as $argument)

            # Check if allowed
            if(in_array($argument, static::FLAGS_ALLOWED))

                # Push in globals flags
                $this->flags[] = $argument;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array FLAGS_ALLOWED */
    public const FLAGS_ALLOWED = [
        # Get more errors
        "--stats-error-details",
    ];

}