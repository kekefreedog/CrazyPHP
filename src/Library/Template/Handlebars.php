<?php declare(strict_types=1);
/**
 * Template
 *
 * Classe for templating supports
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Template;

/**
 * Dependances
 */
use Phpfastcache\Exceptions\PhpfastcacheIOException;
use CrazyPHP\Library\Template\Handlebars\Helpers;
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use LightnCandy\LightnCandy;
use Closure;

/**
 * Handlebars
 *
 * Methods for use Handlebars engine
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Handlebars {

    /**
     * @var array $options Default options value of the constructor
     */
    private $options = [
        # Template available "crazy_preset" or "performance_preset"
        'template'      =>  self::CRAZY_PRESET,
        # Enable helpers stored in "CrazyPHP\Library\Template\Handlebars\Helpers"
        'helpers'       =>  true,
        # Delimiters
        'delimiters'    =>  [
            "{{",
            "}}"
        ]
        # 'useCache'  =>  true, // Not working
        /**
         * If you don't want use cache, please set this env before to avoid mongo error
         * Env::set([
         *  "cache_driver"  =>  "Files"
         * ]);
         */
    ];

    /**
     * @var ?\Closure $renderableClass Compiled template renderer
     */
    private ?\Closure $renderableClass = null;

    /**
     * @var ?array $partials Partial
     */
    private $partials = null;

    /** @var Cache|null Cache client shared by the current document */
    private ?Cache $_cache = null;

    /**
     * Construct Template Renderer
     *
     * Accept explicit compiler options and an optional document cache client.
     *
     * @param array $options Template options
     * @param Cache|null $cache Shared cache client
     */
    public function __construct(array $options = [], ?Cache $cache = null){

        # Keep the cache client separate from compiler options and their cache key
        $this->_cache = $cache;

        # Apply each caller-supplied option
        foreach($options as $key => $option){

            # Keep explicit partial sources available to the ordinary template loader
            if($key === "partials" && ($option === null || is_array($option))){

                # Store the partial map
                $this->partials = $option;

            }else{

                # Override the corresponding compiler option
                $this->options[$key] = $option;

            }

        }

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Get Partials
     * 
     * @return null|array
     */
    public function getPartials():?array {

        # Set result
        $result = $this->partials;

        # Return result
        return $result;

    }

    /**
     * Load
     * 
     * Load template from cache
     * 
     * @param string|array $inputs List of templates to load
     * @param string $customName Custom name to get difference with differents files with same name
     * @return void
     */
    public function load(string|array $inputs = "", string $customName = ""):void {

        # Preserve the current renderer when no template was requested
        if(!empty($inputs)){

            # Resolve the same ordered source paths used by the compiler
            $paths = (array)Process::shortcutsByFile($inputs);

            # Development builds and watchers always load the current template contents
            $live = Config::getValueOrDefault("Front.lastBuild.watch", false)
                || Config::getValueOrDefault("Front.lastBuild.mode", null) === "development"
            ;

            # Keep live templates local to this load
            $cache = $live ? null : ($this->_cache ?? new Cache());

            # Compile directly when there is no persistent cache for this load
            if($cache === null){

                # Read fresh source bytes for the live renderer
                $compiled = $this->_compileTemplate(File::read($paths), $this->partials);

            }else{

                # Preserve the order of the caller's nonempty template names
                $names = array_map(
                    static fn($path) => File::path(pathinfo($path, PATHINFO_FILENAME)),
                    array_filter($paths)
                );

                # Retain compatibility with compiled entries already in the pool
                $key = self::getKey(
                    $names,
                    Cache::getCacheName(__CLASS__).($customName ? ".$customName." : "").".templateCached."
                );

                # Retrieve the compiled bytes only while their sources are up to date
                $compiled = $cache->hasUpToDate($key, File::getLastModifiedDate($paths))
                    ? $cache->get($key)
                    : null
                ;

                # Compile expired, missing or concurrently cleared entries
                if(!is_string($compiled)){

                    # Compile this snapshot with the explicit partials
                    $compiled = $this->_compileTemplate(File::read($paths), $this->partials);

                    # Store the generated program under the same lookup key
                    $cache->set($key, $compiled);

                }

            }

            # Install the renderer for this load, including when an instance is reused
            $this->renderableClass = $this->_loadRenderer($compiled, $cache);

        }

    }

    /**
     * Load a page or email template with the application's recursive partial collection.
     *
     * @param string|array $inputs Ordered template sources
     * @param string $customName Caller-supplied template namespace
     */
    public function loadWithAppPartials(string|array $inputs, string $customName = ""):void {

        # Resolve the same input paths as the ordinary template loader
        $paths = (array)Process::shortcutsByFile($inputs);

        # Hash and compile the same source snapshot, including same-timestamp edits
        $source = File::read($paths);

        # Identify a completed production build or select live discovery
        $partialsKey = $this->_getPartialBuildKey();

        # Reuse the document client only for an identifiable production build
        $cache = $partialsKey === null 
            ? null 
            : ($this->_cache ?? new Cache())
        ;

        # Keep live discovery independent of production cache entries
        if($cache === null || $partialsKey === null){

            # Compile with the current partial sources, including newly added files
            $compiled = $this->_compileTemplate($source, $this->_readAppPartials());

        }else{

            # Include the source, preset and delimiters in the compiled template identity
            try{

                # Keep templates with different compilation options independent
                $templateKey = $partialsKey.".".hash("sha256", serialize([
                    $paths,
                    $customName,
                    $this->options,
                    $source
                ]));

            }catch(\Exception){

                # Compile presets containing closures locally
                $templateKey = null;

            }

            # A warm renderer needs neither partial discovery nor the stored partial map
            $compiled = $templateKey === null ? null : $cache->get($templateKey);

            # Compile the page when its program cannot be reused
            if(!is_string($compiled)){

                # Share the partial map across distinct templates from this build
                $partials = $cache->get($partialsKey);

                # Discover partials on the first load for this build
                if(!is_array($partials)){

                    # Read the current recursive partial collection
                    $partials = $this->_readAppPartials();

                    # Store the snapshot, including an empty directory
                    $cache->set($partialsKey, $partials);

                }

                # Compile the page with the selected snapshot
                $compiled = $this->_compileTemplate($source, $partials);

                # Publish a reusable renderer for this production template
                if($templateKey !== null)

                    # Store the compiled program alongside its partial map
                    $cache->set($templateKey, $compiled);

            }

        }

        # Use this exact program even if another request clears the cache before rendering
        $this->renderableClass = $this->_loadRenderer($compiled, $cache);

    }

    /**
     * Render
     * 
     * Render template
     * 
     */
    public function render(array $data = []):string {

        # Declare result
        $result = "";

        # Check key
        if(!$this->renderableClass)

            # New Exception
            throw new CrazyException(
                "You have to load template before render it ! No templates defined...",
                500,
                [
                    "custom_code"    =>  "handlebars-001",
                ]
            );

        # Render the loaded program with the current request data
        $result = ($this->renderableClass)($data);

        # Return result
        return $result;

    }

    /**
     * Get Flags
     * 
     * Return flags set
     * 
     * @return null
     */
    public function getFlags():null {

        # Declare result
        $result = null;

        # Return result
        return $result;

    }



    /** Public static methods
     ******************************************************
     */

    /**
     * Is Valid
     * 
     * Check template is valid
     * 
     * @param string|array $inputs List of inputs to check, can be a file path of handlebars content
     * @return bool
     */
    public static function isValid(string|array $inputs = ""):bool {

        # Declare result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * get Key
     * 
     * Get key of layout or multiple layouts
     */
    public static function getKey(string|array $inputs = "", $prefix = ""):string {

        # Declare result
        $result = "";

        # Check inputs
        if(empty($inputs))
            return $inputs;

        # Check inputs is array
        if(!is_array($inputs))

            # Convert inputs to array
            $inputs = [
                $inputs
            ];

        # Process inputs
        $inputs = array_map(
            # Process value
            fn($v) => $v ? pathinfo($v, PATHINFO_BASENAME) : "",
            $inputs
        );

        # Filter inputs
        $inputs = array_filter($inputs);

        # Implode inputs
        $inputs = implode(".", $inputs);

        # Check $inputs
        if($inputs)

            # Push in result
            $result = $inputs;

        # Replace {}()/\@: by dot
        $result = str_replace([
            "{",
            "}",
            "(",
            ")",
            "/",
            "\\",
            "@",
            ":"
        ], ".", $prefix.$result);

        # Return result
        return $result;

    }

    /**
     * Compile
     * 
     * Compile Handlebars template
     * 
     * @param string $inputs Input to compile
     * @param array $preset Preset to use for compilation
     * @return mixed
     */
    public static function compile(string $inputs = "", array $preset = self::CRAZY_PRESET):mixed {

        # Declare result
        $result = LightnCandy::compile($inputs, $preset);

        # Check result
        if($result === false){

            # New Exception
            throw new CrazyException(
                "An error is present in your hbs file",
                500,
                [
                    "custom_code"    =>  "handlebars-002",
                ]
            );

        }

        # Return result
        return $result;

    }

    /**
     * Load App Partials
     * 
     * Load all app partials
     * 
     * @param string|array $partialDirs
     * @return array|null
     */
    public static function loadAppPartials(string|array $partialDirs = self::PARTIAL_DIR, string|array $extensions = self::EXTENSIONS):array|null {

        # Set result
        $result = null;

        # Check partials dir is array
        if(is_string($partialDirs))

            # Convert to array
            $partialDirs = [
                $partialDirs
            ];

        # Prepare extensions
        if(is_string($extensions))

            # Set extensions
            $extensions = [
                $extensions
            ];

        # Prepare names
        $names = [];

        # Check extension
        if(!empty($extensions))

            # Iterations extensions
            foreach($extensions as $extension)

                # Add in name
                $names[] = "*.".ltrim($extension, ".");

        # Check partial dirs
        if(!empty($partialDirs))

            # Iterations partials dirs
            foreach($partialDirs as $partialDir)

                # Check partial dir
                if($partialDir){

                    # New finder
                    $finder = new Finder();

                    # Preapre finder
                    $finder
                        ->files()
                        ->name($names)
                        ->in(File::path($partialDir))
                    ;
                    
                    # Iteration result
                    foreach($finder as $file){

                        # Get relative path (includes any subfolders), e.g. "form/form_color.hbs" or "hello.hbs"
                        $relativePath = str_replace(DIRECTORY_SEPARATOR, "/", $file->getRelativePathname());

                        # Strip the matched extension (".hbs" / ".handlebars")
                        $extension = $file->getExtension();

                        # Extract name without extension
                        $nameNoExt = rtrim(substr($relativePath, 0, -strlen($extension)), ".");

                        # Explide segment
                        $segments = explode("/", $nameNoExt);

                        # Trim a leading underscore only off the final path segment (filename), not directory parts
                        $segments[count($segments) - 1] = ltrim(end($segments), "_");

                        # Recreate path
                        $name = implode("/", $segments);

                        # Get content
                        $content = $file->getContents();

                        # Preserve the filename used by browser partial imports
                        $result[$nameNoExt] = $content;

                        # Keep the legacy alias without a leading underscore
                        $result[$name] = $content;

                    }

                }

        # Return result
        return $result;

    }
    

    /** Private methods
     ******************************************************
     */

    /**
     * Load trusted compiler output from an immutable PHP file when the pool supports it.
     */
    private function _loadRenderer(string $compiled, ?Cache $cache):Closure {

        # Keep in-memory execution available for live loads and non-file drivers
        $result = null;

        # Storage can become unavailable after the pool has returned the compiled bytes
        try{

            # Resolve the directory cleared by the active Files pool
            $directory = $cache?->getFileCacheDirectory();

        }catch(PhpfastcacheIOException){

            # Render from the bytes already held by this request
            $directory = null;

        }

        # Share the generated program across PHP workers
        if($directory !== null){

            # Give distinct programs distinct paths even when OPcache skips timestamp checks
            $path = $directory."/handlebars-".hash("sha256", $compiled).".php";

            # Publish legacy cached strings as well as newly compiled programs
            if(!is_file($path))

                # Stage the complete program before exposing its final path
                $this->_publishRenderer($directory, $path, "<?php\n".$compiled);

            # A concurrent pool clear may remove the file before it can be opened
            $result = @include $path;

        }

        # Use the already available bytes when file execution is unavailable
        if(!$result instanceof Closure)

            # Build the renderer from the same trusted compiler output
            $result = eval($compiled);

        # Return the callable for this template
        return $result;

    }

    /**
     * Atomically publish a complete program in the cache pool on a cold load.
     */
    private function _publishRenderer(string $directory, string $path, string $program):void {

        # Allocate a private staging file beside the final path
        $temporary = @tempnam($directory, ".handlebars-");

        # Clean up the staging file after failed or concurrent publication
        if($temporary !== false){

            # Limit publication to complete writes on the same filesystem
            try{

                # Reject tempnam's fallback directory and respect the application umask
                if(dirname($temporary) === $directory && @file_put_contents($temporary, $program) === strlen($program) && @chmod($temporary, 0640 & ~umask())){

                    # Concurrent writers for this digest have identical contents
                    @rename($temporary, $path);

                }

            }finally{

                # Release only this worker's unpublished file
                if(is_file($temporary))

                    # Remove the staging file after an I/O failure
                    @unlink($temporary);

            }

        }

    }

    /**
     * Read Application Partials
     *
     * Include nested templates and aliases when the app has a partial directory.
     *
     * @return array
     */
    private function _readAppPartials():array {

        # Treat an application without partials as an empty collection
        $result = is_dir(File::path(self::PARTIAL_DIR[0])) ? (self::loadAppPartials() ?? []) : [];

        # Return the current partial collection
        return $result;

    }

    /**
     * Identify the partial collection for a production deployment.
     *
     * @return string|null Null selects recursive discovery on each load
     */
    private function _getPartialBuildKey():?string {

        # Fall back to live discovery for development, watch and unbuilt apps
        $result = null;

        # Check the recorded build mode before consulting its identity
        if(Config::getValueOrDefault("Front.lastBuild.mode", null) === "production" && !Config::getValueOrDefault("Front.lastBuild.watch", false)){

            # Read the asset hash recorded by the bundler
            $hash = Config::getValueOrDefault("Front.lastBuild.hash", null);

            # Distinguish builds that produce identical assets after a PHP partial edit
            $date = Config::getValueOrDefault("Front.lastBuild.date", null);

            # Reuse a snapshot only when the build can be identified
            if($hash || $date)

                # Isolate apps sharing a cache backend as well as successive builds
                $result = "Handlebars.partials.v1.".hash("sha256", serialize([
                    File::path(self::PARTIAL_DIR[0]),
                    $hash,
                    $date
                ]));

        }

        # Return the active build key
        return $result;

    }

    /**
     * Compile a source snapshot with the caller's preset, partials and helper registry.
     */
    private function _compileTemplate(string $source, ?array $partials):string {

        # Prepare template
        if(
            isset($this->options["template"]) &&
            !empty($this->options["template"])
        ){

            # Set template
            $template = $this->options["template"];

            # Check partials
            if(!empty($partials))

                # Set partials
                $template["partials"] = $partials;

        }else

            # Set default template
            $template = self::CRAZY_PRESET;

        # Check helpers
        if(isset($this->options["helpers"]) && $this->options["helpers"]){

            # Set helpers
            $template["helpers"] = Helpers::listArray();

            # Check if App\Library\Handlebars\Helpers is valid class
            if(class_exists(static::APP_HELPERS) && method_exists(static::APP_HELPERS, "listArray")){

                # Get app list array
                $appListArray = static::APP_HELPERS::listArray();

                # Check app list array
                if(is_array($appListArray) && !empty($appListArray))

                    # Set $template["helpers"]
                    $template["helpers"] = is_array($template["helpers"])
                        ? array_merge($template["helpers"], $appListArray)
                        : $appListArray
                    ;

            }

        }

        # Compile with the configured delimiters
        $result = $this->compile($source, [
            "delimiters"    =>  $this->options["delimiters"] ?? [
                "{{",
                "}}"
            ]
        ] + $template);

        # Return the generated PHP program
        return $result;

    }

    /** Public constants | Options preset
     ******************************************************
     */

    /**
     * Crazy Preset with the maximum of compatibility
     */
    public const CRAZY_PRESET = [
        "flags"    =>  LightnCandy::FLAG_HANDLEBARSJS_FULL|LightnCandy::FLAG_RUNTIMEPARTIAL|LightnCandy::FLAG_PARENT|LightnCandy::FLAG_ADVARNAME/*  | LightnCandy::FLAG_ERROR_LOG */,
    ];

    /**
     * Crazy Preset with the maximum of compatibility
     */
    public const HTML_STRUCTURE = [
        "flags"    =>  LightnCandy::FLAG_HANDLEBARSJS|LightnCandy::FLAG_RUNTIMEPARTIAL|LightnCandy::FLAG_SPVARS|LightnCandy::FLAG_NAMEDARG|LightnCandy::FLAG_ERROR_LOG|LightnCandy::FLAG_PARENT|LightnCandy::FLAG_ADVARNAME,
    ];

    /**
     * Performance Preset with the maximum of performance
     */
    public const PERFORMANCE_PRESET = [
        "flags"    =>  LightnCandy::FLAG_BESTPERFORMANCE,
    ];

    /**
     * Extension of handlebars template
     */
    public const EXTENSIONS = [
        "handlebars",
        "hbs"
    ];
    
    /**
     * Directories of partials
     */
    public const PARTIAL_DIR = [
        "@app_root/assets/Hbs/partials",
    ];

    /**
     * App Herlpers
     */
    public const APP_HELPERS = "\App\Library\Handlebars\Helpers";

}
