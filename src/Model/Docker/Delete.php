<?php declare(strict_types=1);
/**
 * Delete Docker Environment
 *
 * Stop Compose before removing generated files; preserve persistent data.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Docker;

/** Dependencies */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Docker;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;

/**
 * Delete
 *
 * Remove generated Docker configuration after a successful Compose shutdown.
 */
class Delete extends CrazyModel implements CrazyCommand {

    /** @var array STORYLINE Shared CLI and direct-call execution order */
    public const STORYLINE = ["runCheck", "runDockerComposeDown", "runUpdateDatabaseConfig", "runStructureFolder"];

    /** @var Runtime $_runtime Checked Docker execution */
    private Runtime $_runtime;

    /** @var string $_root Validated application directory */
    private string $_root;

    /**
     * Constructor
     *
     * @param array $inputs CLI inputs
     * @param Runtime|null $runtime Injectable tool runtime
     */
    public function __construct(array $inputs = [], ?Runtime $runtime = null){

        # Share the local-first checked tool runner
        $this->_runtime = $runtime ?? new Runtime();

        # Resolve all cleanup paths against this application's root
        $this->_root = File::path("@app_root");

    }

    /**
     * Get Required Values
     *
     * @return array
     */
    public static function getRequiredValues():array {

        # Deletion has no additional configuration questions
        return [];

    }

    /**
     * Run
     *
     * @return self
     */
    public function run():self {

        # Follow the same explicit order as the CLI
        foreach(self::STORYLINE as $step)
            $this->{$step}();

        # Return the completed command
        return $this;

    }

    /**
     * Run Check
     *
     * @return self
     */
    public function runCheck():self {

        # Refuse to delete framework sources or operate outside a real project directory
        if(!is_dir($this->_root) || realpath($this->_root) === realpath(File::path("@crazyphp_root")))
            throw new CrazyException("Run Docker deletion from an application directory.", 400);

        # Require the framework marker before stopping an existing Compose project
        if(is_file($this->_root."/docker-compose.yml") && !is_file($this->_root."/config/Docker.yml"))
            throw new CrazyException("No config/Docker.yml found; this Compose project is not identified as a CrazyPHP Docker environment.", 400);

        # Refuse linked configuration rather than modifying files outside this project
        foreach(["docker", "config", "docker-compose.yml", "config/Docker.yml", "config/Database.yml"] as $path)
            if(is_link($this->_root."/".$path))
                throw new CrazyException("Docker cleanup requires local configuration: $path is a symlink.", 400);

        # Return the validated command
        return $this;

    }

    /**
     * Run Docker Compose Down
     *
     * @return self
     */
    public function runDockerComposeDown():self {

        # Validate even when this step is called directly
        $this->runCheck();

        # Allow cleanup of a partial installation without requiring a Docker daemon
        if(is_file($this->_root."/docker-compose.yml"))
            $this->_runtime->execute("docker", ["compose", "down"]);

        # Preserve named volumes and stop immediately if Compose fails
        return $this;

    }

    /**
     * Run Docker Compose Remove (Compatibility)
     *
     * @return self
     */
    public function runDockerComposeRemove():self {

        # Compose down already removes containers; retain the old public method without a second command
        return $this;

    }

    /**
     * Run Update Database Config
     *
     * @return self
     */
    public function runUpdateDatabaseConfig():self {

        # Skip projects that have no database document
        $path = $this->_root."/config/Database.yml";
        if(!is_file($path))
            return $this;

        # Preserve all local endpoints, credentials and unrelated engine metadata
        $document = Yaml::open($path);
        $changed = false;
        foreach($document["Database"]["collection"] ?? [] as $engine => $database){

            # Remove service metadata only for engines managed by the framework
            if(isset(Docker::DATABASE_TO_SERVICE[$engine]) && is_array($database) && isset($database["docker"])){

                # Leave the original local connection settings intact
                unset($document["Database"]["collection"][$engine]["docker"]);
                $changed = true;

            }

        }

        # Avoid rewriting an unchanged user document
        if($changed)
            Yaml::create($path, $document);

        # Drop process-local configuration cached before cleanup
        unset($GLOBALS[Config::PREFIX]["Database"]);

        # Return the command
        return $this;

    }

    /**
     * Run Structure Folder
     *
     * @return self
     */
    public function runStructureFolder():self {

        # Read the generated file inventory, including the current server and preparation scripts
        $structure = Yaml::open(File::path(Docker::STRUCTURE_PATH))["Structure"]["@app_root"];

        # Delete only inventoried files and empty directories, preserving custom Docker additions
        File::removeStructure($this->_root, $structure);

        # Remove the framework marker after generated files have been cleaned successfully
        File::removeWithin($this->_root, "config/Docker.yml");

        # Invalidate only the process-local Docker configuration
        unset($GLOBALS[Config::PREFIX]["Docker"]);

        # Keep .env, host database files and named Docker volumes available for reuse
        return $this;

    }

}
