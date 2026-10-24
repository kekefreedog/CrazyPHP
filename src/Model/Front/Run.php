<?php declare(strict_types=1);
/**
 * Frontend Runner
 *
 * Run the configured Webpack or Vite scripts with the tools in the selected local or Docker runtime.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Front;

/**
 * Dependencies
 */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Front;
use CrazyPHP\Library\File\File;
use Throwable;

/**
 * Run
 *
 * Execute package scripts and publish successful build metadata for either bundler.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
class Run extends CrazyModel implements CrazyCommand {

    /** Public Constants
     ******************************************************
     */

    /** @var array STORYLINE Shared CLI and direct execution order */
    public const STORYLINE = [
        "runCheck",
        "runNpmInstall",
        "runCheckIfWatch",
        "runFrontScript",
        "runGeneratedFilesOnConfig",
    ];

    /** @var string PHASE_ENV Legacy environment variable for callers splitting PHP and npm execution */
    public const PHASE_ENV = "CRAZY_FRONT_PHASE";

    /** @var string PREVIOUS_BUILD_FILE Previous build kept between the split phases */
    public const PREVIOUS_BUILD_FILE = "@app_root/.cache/front/previous_build.json";

    /** Public Parameters
     ******************************************************
     */

    /** @var array $inputs Original command inputs */
    public array $inputs;

    /** Private Parameters
     ******************************************************
     */

    /** @var Runtime $_runtime Checked tool runner */
    private Runtime $_runtime;

    /** @var string $_script Requested package script */
    private string $_script;

    /** @var array $_flags Arguments forwarded after npm's separator */
    private array $_flags;

    /** @var array|null $_previousBuild Metadata to restore after compiler failure */
    private ?array $_previousBuild = null;

    /**
     * Constructor
     *
     * @param array $inputs CLI inputs
     * @param Runtime|null $runtime Injectable execution runtime
     */
    public function __construct(array $inputs = [], ?Runtime $runtime = null){

        # Preserve inputs
        $this->inputs = $inputs;

        # Use the same runtime as application creation
        $this->_runtime = $runtime ?? new Runtime();

        # Get arguments
        $arguments = $inputs["args"] ?? [];

        # Default to the production build when no script was supplied
        $this->_script = array_shift($arguments) ?? "build";

        # Accept an optional separator without forwarding it twice
        if(($arguments[0] ?? null) === "--")

            # Remove separator
            array_shift($arguments);

        # Keep flag values as separate arguments rather than constructing a shell command
        $this->_flags = array_values($arguments);

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Required Values
     *
     * @return array
     */
    public static function getRequiredValues():array {

        # Script selection comes from CLI arguments
        return [];

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Set Script
     *
     * @param string $name Package script name
     * @return self
     */
    public function setScript(string $name = ""):self {

        # Retain the existing script when no replacement is requested
        if($name !== "")

            # Set script
            $this->_script = $name;

        # Validate the script against this application's package manifest
        return $this->runCheck();

    }

    /**
     * Run
     *
     * @return self
     */
    public function run():self {

        # Execute the same explicit steps used by CrazyFront
        foreach(self::STORYLINE as $step)

            # Run step
            $this->{$step}();

        # Return result
        return $this;

    }

    /**
     * Run Check
     *
     * @return self
     */
    public function runCheck():self {

        # Require the frontend document before resolving its bundler
        if(!is_file(File::path("@app_root/config/Front.yml")))

            # New Exception
            throw new CrazyException("No frontend configured; config/Front.yml is missing.", 400);

        # Keep older projects without a bundler field on their established Webpack scripts
        $bundler = Config::has("Front.bundler") ? Config::getValue("Front.bundler") : "webpack";

        # Check bundler
        if(!in_array($bundler, ["webpack", "vite"], true))

            # New Exception
            throw new CrazyException("Front.bundler must be webpack or vite to run frontend scripts.", 400);

        # Get scripts before installing dependencies or changing build metadata
        $scripts = $this->_scripts();

        # Check script
        if(
            $this->_script === "" ||
            str_starts_with($this->_script, "-") ||
            !is_string($scripts[$this->_script] ?? null) ||
            trim($scripts[$this->_script]) === ""
        )

            # New Exception
            throw new CrazyException("Frontend script \"{$this->_script}\" was not found in package.json.", 400);

        # Return result
        return $this;

    }

    /**
     * Run Npm Install
     *
     * @return self
     */
    public function runNpmInstall():self {

        # Validate direct step calls as well as the complete storyline
        $this->runCheck();

        # Check whether a legacy caller handles npm in a separate phase
        if($this->_phase() !== "")

            # Return result
            return $this;

        # Stop on installation errors in the selected runtime
        $this->_runtime->execute("npm", ["install"]);

        # Return result
        return $this;

    }

    /**
     * Run Check If Watch
     *
     * @return self
     */
    public function runCheckIfWatch():self {

        # Check the watch metadata was already prepared in a previous phase
        if(in_array($this->_phase(), ["finish", "failed"], true))

            # Return result
            return $this;

        # Preserve the last successful build in case compilation fails
        $this->_previousBuild = Config::getValue("Front.lastBuild") ?? [];

        # Keep the previous build for the finish or failed phase run after the host script
        if($this->_phase() === "prepare")

            # Save previous build
            File::create(self::PREVIOUS_BUILD_FILE, json_encode($this->_previousBuild, JSON_THROW_ON_ERROR));

        # Read script defaults followed by CLI overrides
        $command = $this->_scripts()[$this->_script]." ".implode(" ", $this->_flags);

        # Detect watch options
        preg_match_all('/(?:^|\s)(--watch(?:=(?:true|false))?|--no-watch|-w)(?=\s|$)/', $command, $watchOptions);

        # Detect watch mode while explicitly clearing stale metadata for ordinary builds
        $watch = $watchOptions[1] && !in_array(end($watchOptions[1]), ["--watch=false", "--no-watch"], true);

        # Detect mode options
        preg_match_all('/--mode(?:=|\s+)[\'"]?([^\s\'"]+)/', $command, $matches);

        # Use the last mode option so command-line arguments override script defaults
        $mode = $matches[1]
            ? end($matches[1])
            : (in_array($this->_script, ["dev", "watch"], true) ? "development" : "production")
        ;

        # Make watch discovery available to middleware while a long-running build is active
        Config::setValue("Front.lastBuild", array_replace($this->_previousBuild, ["watch" => $watch, "mode" => $mode]));

        # Return result
        return $this;

    }

    /**
     * Run Front Script
     *
     * @return self
     */
    public function runFrontScript():self {

        # Validate script selection even when compilation is invoked directly
        $this->runCheck();

        # Check the script runs on the host between the prepare and finish phases
        if(in_array($this->_phase(), ["prepare", "finish"], true))

            # Return result
            return $this;

        # Check the host script failed or was stopped
        if($this->_phase() === "failed"){

            # Restore the build saved by the prepare phase
            $this->_previousBuild = $this->_readPreviousBuild();
            $this->_restorePreviousBuild();

            # New Exception
            throw new CrazyException("Frontend script \"{$this->_script}\" failed or was stopped.", 500);

        }

        try{

            # Forward all bundler arguments through npm without losing their boundaries
            $this->_runtime->execute(
                "npm",
                ["run", $this->_script, ...($this->_flags ? ["--", ...$this->_flags] : [])]
            );

        }catch(Throwable $exception){

            # Restore the previous build unless a stopped watcher published a newer one
            $this->_restorePreviousBuild();

            # Propagate the original tool failure to the caller
            throw $exception;

        }

        # Return result after a successful compiler exit
        return $this;

    }

    /**
     * Run Generated Files On Config
     *
     * Both bundlers use the same index/hash/page contract and runtime ordering.
     * Mode and watch state are preserved while the helper updates files and build time.
     *
     * @return self
     */
    public function runGeneratedFilesOnConfig():self {

        # Check the host script has not run yet
        if($this->_phase() === "prepare")

            # Return result
            return $this;

        # Register generated files
        Front::getHash();

        # Remove the build kept between split phases
        if($this->_phase() === "finish")

            # Read and remove previous build
            $this->_readPreviousBuild();

        # Return result
        return $this;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Phase
     *
     * @return string prepare, finish, failed, or empty for a complete run
     */
    private function _phase():string {

        # Get phase
        $phase = getenv(self::PHASE_ENV);

        # Return a known phase only
        return in_array($phase, ["prepare", "finish", "failed"], true) ? $phase : "";

    }

    /**
     * Read Previous Build
     *
     * Read and remove the build saved by the prepare phase
     *
     * @return array|null
     */
    private function _readPreviousBuild():?array {

        # Get path
        $path = File::path(self::PREVIOUS_BUILD_FILE);

        # Check file
        if(!is_file($path))

            # Return null
            return null;

        # Read previous build
        $result = json_decode((string) file_get_contents($path), true);

        # Remove file
        unlink($path);

        # Return result
        return is_array($result) ? $result : null;

    }

    /**
     * Restore Previous Build
     *
     * @return void
     */
    private function _restorePreviousBuild():void {

        # A stopped watcher may already have emitted a newer build consumed by live requests
        if($this->_previousBuild === null)

            # Stop function
            return;

        # Retain published watch outputs instead of restoring references to deleted bundles
        $hash = Config::getValue("Front.lastBuild.watch") ? Front::getHash() : "";

        # Restore the previous state when compilation never produced a newer watch build
        if($hash === "" || $hash === ($this->_previousBuild["hash"] ?? null))

            # Restore previous build
            Config::setValue("Front.lastBuild", $this->_previousBuild);

    }

    /**
     * Scripts
     *
     * @return array
     */
    private function _scripts():array {

        # Resolve package.json from the application rather than the library package
        $path = File::path("@app_root/package.json");

        # Check package.json
        if(!is_file($path))

            # New Exception
            throw new CrazyException("No package.json found for frontend compilation.", 400);

        # Reject malformed JSON rather than silently executing a different script
        $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        # Return the project's declared scripts
        return $manifest["scripts"] ?? [];

    }

}
