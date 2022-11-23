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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\File\Package;
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
class Run implements CrazyCommand {

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

    }

    /** Private Parameters
     ******************************************************
     */

    /** @var ?string $script Script name to run  */
    private $script = null;

    /** @var bool $watch Bool for check if watch mode is enable */
    private $watch = false;

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
        $result = Package::exec("run", $this->script, false);

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

        # New finder
        $finder = new Finder();

        # Set result
        $result = [
            "files" =>  null,
            "hash"  =>  null,
            "date"  =>  null,
            "watch" =>  false,
        ];

        # Search new generated file
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

        # Set date
        $result["date"] = (new DateTime())->format("c");

        # Push value in front config
        Config::setValue("Front.lastBuild", $result);

        # Return self
        return $this;

    }

}