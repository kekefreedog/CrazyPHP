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
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use Phpfastcache\CacheManager;
use LightnCandy\LightnCandy;
use DateTime;

/**
 * Handlebars
 *
 * Methods for use Handlebars engine
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Handlebars{

    /**
     * @var array $options Default options value of the constructor
     */
    private $options = [
        # Template available "crazy_preset" or "performance_preset"
        'template'  =>  "crazy_preset",
        'useCache'  =>  true,
    ];

    /**
     * @var string|null $key Key og the cache of the current template
     */
    private $key = null;

    /**
     * @var string|null $key Key og the cache of the current template
     */
    private $renderableClass = null;

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $options Options
     * @return Handlebars
     */
    public function __construct(array $options = []){
        
        # Check options
        if(!empty($options))

            # Iterations of options
            foreach($options as $key => $option)

                # Puhs option
                $this->options[$key] = $option;

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
     * @return void
     */
    public function load(string|array $inputs = ""):void {

        # Check inputs
        if(empty($inputs))
            return;

        # Process inputs
        $inputs = Process::shortcutsByFile($inputs);

        # Check inputs is array
        if(!is_array($inputs))

            # Convert inputs to array
            $inputs = [$inputs];

        # Get key
        $this->key = self::getKey($inputs);

        # Check cache is enable
        if($this->options["useCache"]){

            # New cache instance
            $cache = new Cache();

            # Check cache is valid
            if(!$cache->hasUpToDate($this->key, File::getLastModifiedDate($inputs)))

                # Set Cache
                $cache->set($this->key, $this->compile($inputs));

        }

    }

    /**
     * Render
     * 
     * Render template
     */
    public function render(array $inputs = ""):string {

        # Declare result
        $result = "";

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
    public static function getKey(string|array $inputs = ""):string {

        # Declare result
        $result = "";

        # Check inputs
        if(empty($inputs))
            return;

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
        $inputs = implode("/", $inputs);

        # Check $inputs
        if($inputs)

            # Push in result
            $result = $inputs;

        # Return result
        return $result;

    }

    /**
     * Compile
     * 
     * Compile Handlebars template
     * 
     * @param string $inputs Input to compile
     * @return void
     */
    public static function compile(string|array $inputs = "") {

        # Declare result
        $result = fn() => "";

        // $this->getFlags()

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
        "flags" =>  LightnCandy::FLAG_HANDLEBARSJS,
    ];

    /**
     * Performance Preset with the maximum of performance
     */
    public const PERFORMANCE_PRESET = [
        "flags" =>  LightnCandy::FLAG_BESTPERFORMANCE,
    ];
    

}