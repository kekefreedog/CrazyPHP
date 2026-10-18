<?php declare(strict_types=1);
/**
 * Delete Application
 *
 * Remove the application scaffold after stopping its Docker environment.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Model\App;

/**
 * Dependencies
 */
use CrazyPHP\Model\Docker\Delete as DockerDelete;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Structure;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;

/**
 * Delete
 *
 * Preserve dependency access and database data while removing the application.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

    /** Public Constants
     ******************************************************
     */

    /** @var array STORYLINE Stop Docker while its configuration still exists */
    public const STORYLINE = [
        "runCheck",
        "runDockerCompose",
        "runRetrieveOriginalComposer",
        "runCacheCleaner",
        "runStructureFolder",
        "runCleanFrontFiles",
        "runCleanRoot",
    ];

    /** @var array KEEP Root entries kept so Composer and the crazy command still work */
    public const KEEP = ["composer.json", "vendor", ".git"];

    /** Private Parameters
     ******************************************************
     */

    /** @var Runtime $_runtime Checked Docker execution */
    private Runtime $_runtime;

    /** @var string $_root Application root */
    private string $_root;

    /** @var string $_public Captured document root before config removal */
    private string $_public = "public";

    /**
     * Constructor
     *
     * @param array $inputs CLI inputs
     * @param Runtime|null $runtime Injectable tool runtime
     */
    public function __construct(array $inputs = [], ?Runtime $runtime = null){

        # Resolve deletion against the same project as creation
        $this->_root = File::path("@app_root");

        # Use the same runtime as creation
        $this->_runtime = $runtime ?? new Runtime();

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

        # The CLI already confirms application deletion
        return [];

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Run
     *
     * @return self
     */
    public function run():self {

        # Use the same ordered steps for direct calls and the CLI
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

        # Require an initialized application and protect the framework checkout
        if(
            !is_file($this->_root."/config/App.yml") ||
            realpath($this->_root) === realpath(File::path("@crazyphp_root"))
        )

            # New Exception
            throw new CrazyException("Run project deletion from an initialized application directory.", 400);

        # Refuse linked configuration before reading deletion targets
        if(is_link($this->_root."/config") || is_link($this->_root."/config/App.yml"))

            # New Exception
            throw new CrazyException("Application deletion requires local configuration files.", 400);

        # Capture the public directory before removing App.yml
        $this->_public = Yaml::open($this->_root."/config/App.yml")["App"]["public"] ?? "public";

        # Require a relative public directory outside preserved project data and dependencies
        if(
            !preg_match('/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/D', $this->_public) ||
            in_array(explode("/", $this->_public)[0], ["vendor", "config", "docker", "node_modules", "assets", "app"], true)
        )

            # New Exception
            throw new CrazyException("App.public is not a safe application document root.", 400);

        # Return result
        return $this;

    }

    /**
     * Run Docker Compose
     *
     * Stop Docker before deleting any of the files required by Compose.
     * Shutdown failures propagate so all application files remain available.
     *
     * @return self
     */
    public function runDockerCompose():self {

        # Check Docker files
        if(is_file($this->_root."/docker-compose.yml") || is_file($this->_root."/config/Docker.yml"))

            # Stop Docker
            (new DockerDelete([], $this->_runtime))->run();

        # Return result
        return $this;

    }

    /**
     * Run Retrieve Original Composer
     *
     * Restore the newest composer.json backup and keep vendor installed,
     * so the command and subsequent project creation can run.
     *
     * @return self
     */
    public function runRetrieveOriginalComposer():self {

        # Set backup directory
        $directory = $this->_root."/assets/Json/backup/composer";

        # Set backups
        $backups = [];

        # Ignore absent backups and never follow an externally linked backup directory
        if(
            is_dir($directory) &&
            !is_link($this->_root."/assets") &&
            !is_link($this->_root."/assets/Json") &&
            !is_link($this->_root."/assets/Json/backup") &&
            !is_link($directory)
        ){

            # Iteration of backup files
            foreach(scandir($directory) as $name){

                # Support both legacy timestamp backups and current uniqid-based backups
                if(
                    preg_match('/^(?:[0-9]+-new|[0-9a-f]{13})-composer\.json$/D', $name) &&
                    is_file("$directory/$name") &&
                    !is_link("$directory/$name")
                )

                    # Push backup
                    $backups[] = "$directory/$name";

            }

        }

        # Keep the current manifest when no original manifest is available
        if(!$backups)

            # Return result
            return $this;

        # Prefer the newest backup, resolving equal timestamps by filename
        usort(
            $backups,
            static fn(string $left, string $right):int =>
                (filemtime($right) <=> filemtime($left)) ?: strcmp(basename($right), basename($left))
        );

        # Get recovered content
        $content = file_get_contents($backups[0]);

        # Validate the recovered document before replacing the active manifest
        json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        # Unlink a manifest symlink rather than writing through it
        File::removeWithin($this->_root, "composer.json");

        # Check manifest restored
        if(file_put_contents($this->_root."/composer.json", $content) === false)

            # New Exception
            throw new CrazyException("Could not restore composer.json.", 500);

        # The current lock belongs to the generated manifest, not the recovered bootstrap
        File::removeWithin($this->_root, "composer.lock");

        # Return result
        return $this;

    }

    /**
     * Run Cache Cleaner
     *
     * @return self
     */
    public function runCacheCleaner():self {

        # Remove only this application's file cache without clearing shared database caches
        File::removeWithin($this->_root, ".cache/app");

        # Return result
        return $this;

    }

    /**
     * Run Structure Folder
     *
     * @return self
     */
    public function runStructureFolder():self {

        # Start with the scaffold inventory to retain custom configuration and editor files
        $structure = Yaml::open(File::path(Structure::DEFAULT_TEMPLATE))["Structure"]["@app_root"];

        # Keep repository and editor configuration
        unset(
            $structure["folders"][".vscode"],
            $structure["files"][".gitignore"]
        );

        # Handle the captured document root separately instead of assuming public/
        unset($structure["folders"]["public"]);

        # Remove generated files, including database settings, without traversing linked directories
        File::removeStructure($this->_root, $structure);

        # Remove application-owned source, assets, database data and environment while preserving linked targets
        foreach(["app", "assets", ".database", ".env"] as $path)

            # Remove path
            File::removeWithin($this->_root, $path);

        # Release cached config documents referring to the deleted application
        unset($GLOBALS[Config::PREFIX]);

        # Return result
        return $this;

    }

    /**
     * Run Clean Front Files
     *
     * @return self
     */
    public function runCleanFrontFiles():self {

        # Cover both current bundlers and their installed frontend dependencies
        foreach([
            $this->_public,
            "vite.build.mjs",
            "webpack.config.js",
            "webpack.dev.js",
            "webpack.prod.js",
            "tsconfig.json",
            "package.json",
            "package-lock.json",
            "node_modules",
        ] as $path)

            # Remove path
            File::removeWithin($this->_root, $path);

        # Return result
        return $this;

    }

    /**
     * Run Clean Root
     *
     * Remove every remaining root entry except Composer's manifest, vendor and the repository.
     *
     * @return self
     */
    public function runCleanRoot():self {

        # Iteration of root entries
        foreach(scandir($this->_root) as $entry){

            # Check entry is not kept
            if(in_array($entry, [".", "..", ...self::KEEP], true))

                # Continue
                continue;

            # Remove entry without following linked directories
            File::removeWithin($this->_root, $entry);

        }

        # Return result
        return $this;

    }

}
