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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Template;

/** 
 * Dependances
 */
use CrazyPHP\Library\Template\Handlebars\Helpers;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use LightnCandy\LightnCandy;

/**
 * Handlebars
 *
 * Methods for use Handlebars engine
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Handlebars {

    /**
     * @var array $options Default options value of the constructor
     */
    private $options = [
        # Template available "crazy_preset" or "performance_preset"
        'template'  =>  self::CRAZY_PRESET,
        # Enable helpers stored in "CrazyPHP\Library\Template\Handlebars\Helpers"
        'helpers'   =>  true,
        # 'useCache'  =>  true, // Not working
        /**
         * If you don't want use cache, please set this env before to avoid mongo error
         * Env::set([
         *  "cache_driver"  =>  "Files"
         * ]);
         */
    ];

    /**
     * @var ?string $key Key og the cache of the current template
     */
    private $key = null;

    /**
     * @var ?string $key Key og the cache of the current template
     */
    private $renderableClass = null;

    /**
     * @var ?array $partials Partial
     */
    private $partials = null;

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $options Options
     * @return self
     */
    public function __construct(array $options = []){
        
        # Check options
        if(!empty($options))

            # Iterations of options
            foreach($options as $key => $option){

                # Check if partials
                if(
                    $key == "partials" && 
                    (
                        is_null($option) ||
                        is_array($option)
                    )
                )

                    # Set partials
                    $this->partials = $option;

                # Regular option
                else

                    # Puhs option
                    $this->options[$key] = $option;

            }

    }

    /** Public methods
     ******************************************************
     */

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

        # Check inputs
        if(empty($inputs))
            return;

        # Process inputs
        $inputs = Process::shortcutsByFile($inputs);

        # Check inputs is array
        if(!is_array($inputs))

            # Convert inputs to array
            $inputs = [$inputs];

        # Add class in top of inputs
        $collectionKey = [];

        # Iteration des inputs
        foreach($inputs as $input)

            # Check input
            if($input)

                # Push filename in collection
                $collectionKey[] = File::path(pathinfo($input, PATHINFO_FILENAME));

        # Get key
        $this->key = self::getKey($collectionKey, Cache::getCacheName(__CLASS__).($customName ? ".$customName." : "").".templateCached.");

        # Prepare template
        if(
            isset($this->options["template"]) &&
            !empty($this->options["template"])
        ){

            # Set template
            $template = $this->options["template"];

            # Check partials
            if($this->partials && !empty($this->partials))

                # Set partials
                $template["partials"] = $this->partials;

        }else

            # Set default template
            $template = self::CRAZY_PRESET;

        # Check helpers
        if(isset($this->options["helpers"]) && $this->options["helpers"]){

            # Set helpers
            $template["helpers"] = Helpers::listArray();

        }

        # New cache instance
        $cache = new Cache();

        # Set lastModifiedDate
        $lastModifiedDate = File::getLastModifiedDate($inputs);

        # Check cache is valid
        if(!$cache->hasUpToDate($this->key, $lastModifiedDate)){

            # Read file
            $file = File::read($inputs);

            # Compile
            $compile = $this->compile($file, $template);

            # Set Cache
            $cache->set($this->key, $compile);

        }

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
        if(!$this->key)

            # New Exception
            throw new CrazyException(
                "You have to load template before render it ! No templates defined...",
                500,
                [
                    "custom_code"   =>  "handlebars-001",
                ]
            );

        # Check compilate already load
        if(!$this->renderableClass){

            # New cache instance
            $cache = new Cache();

            # Get cached compilate
            $compilate = $cache->get($this->key);

            # COnvert cache to php
            $this->renderableClass = eval($compilate);

        }

        # Get rendarable class
        $function = $this->renderableClass;

        # Render compilate
        $result = $function($data);

        # Return result
        return $result;

    }

    /**
     * Get Flags
     * 
     * Return flags set
     * 
     * @return Constant
     */
    public function getFlags() {

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
            $inputs = [$inputs];

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
        $result = str_replace(["{", "}", "(", ")", "/", "\\", "@", ":"], ".", $prefix.$result);

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
     * @return
     */
    public static function compile(string $inputs = "", array $preset = self::CRAZY_PRESET) {

        # Declare result
        $result = LightnCandy::compile($inputs, $preset);

        # Check result
        if($result === false){

            # New Exception
            throw new CrazyException(
                "An error is present in your hbs file",
                500,
                [
                    "custom_code"   =>  "handlebars-002",
                ]
            );

        }

        # Return result
        return $result;

    }


    /** Public constants | Options preset
     ******************************************************
     */

    /**
     * Crazy Preset with the maximum of compatibility
     */
    public const CRAZY_PRESET = [
        "flags"     =>  LightnCandy::FLAG_HANDLEBARSJS/*  | LightnCandy::FLAG_ERROR_LOG */,
    ];

    /**
     * Crazy Preset with the maximum of compatibility
     */
    public const HTML_STRUCTURE = [
        "flags" =>  LightnCandy::FLAG_HANDLEBARSJS|LightnCandy::FLAG_RUNTIMEPARTIAL|LightnCandy::FLAG_SPVARS| LightnCandy::FLAG_ERROR_LOG,
    ];

    /**
     * Performance Preset with the maximum of performance
     */
    public const PERFORMANCE_PRESET = [
        "flags" =>  LightnCandy::FLAG_BESTPERFORMANCE,
    ];

    /**
     * Extension of handlebars template
     */
    public const EXTENSIONS = [
        "handlebars", "hbs"
    ];
    

}