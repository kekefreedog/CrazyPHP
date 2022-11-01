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
namespace CrazyPHP\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;

/**
 * Asset
 *
 * Methods for interacting with assets
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Asset{

    /** Private parameters
     ******************************************************
     */

    /** @var ?string $context Context of the current asset */
    private ?string $context = null;

    /** @var array $result Result of search */
    private array $result = [];

    /** @var ?Cache $cache Cache instance */
    private ?Cache $cache = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Create new cache
        $this->cache = new Cache();

        # Set context
        $this->setContext();

    }

    /**
     * Public static methods
     */


    /**
     * Register
     * 
     * Register assets
     * 
     * @param bool $force Force register of the file
     * @param string $file Path of the config file
     * @return void
     */
    public static function registerConfig(bool $force = false, string $path = self::PATH):void {

        # Get path file
        $path = File::path($path);

        # Check file exists
        if(!File::exists($path))

            # Return
            return;

        # Key of updated date
        $keyUpdatedTime = Cache::getKeyWithCacheName(__CLASS__, "updatedTime");

        # Key of collection
        $keyCollection = Cache::getKeyWithCacheName(__CLASS__, "Collection");

        # Cache instance
        $cache = new Cache();

        # Get Updated Time
        $updatedTime = $cache->get($keyUpdatedTime);

        # Check updated time
        if(!$updatedTime || $updatedTime < DateTime::lastUpdateFile($path) || $force){

            # Parsed assets
            $parsedAssets = self::_parseAssets($path);

            # Cache File
            $cache->set($keyCollection, $parsedAssets);

            # Update time
            $updatedTime = $cache->set($keyUpdatedTime, new DateTime("now"), 0);

        }

    }  

    /** Public methods | Context
     ******************************************************
     */

    /**
     * Set Context
     * 
     * Set context means set current class name
     * 
     * @param string $context
     * @return void
     */
    public function setContext(string $context = __CLASS__):void {

        # Prepare context name
        # $context = $this->_prepareContextName($context);

        # Set context
        $this->context = $context.".Collection";

    }

    /**
     * Append Context
     * 
     * Append context means add current class name in context
     * 
     * @param string $context
     * @return void
     */
    public function appendContext(string $context = ""):void {

        # Prepare context name
        $context = $this->_prepareContextName($context);

        # Set context
        $this->context .= $context && $this->context ?
            ".$context" :
                $context
        ;

    }

    /**
     * Get Context
     * 
     * Get context
     * 
     * @param string $context
     * @return ?string
     */
    public function getContext():?string {

        # Set context
        $result = $this->context;

        # Return context
        return $result;

    }  
    
    /**
     * Get
     * 
     * Get assets
     * 
     * @param string|array $path
     * @return string|array|null
     */
    public function get(string|array $inputs = []):string|array|null {

       # Prepare result
       $result = [];

       # Check inputs
       if(empty($inputs))

           # Return result
           return $result;

       # Check inputs
       if(!is_array($inputs))

           # Convert inpits to array
           $inputs = [$inputs];

        # Key of collection
        $keyCollection = Cache::getKeyWithCacheName(__CLASS__, "Collection");

        # Check if key collection is in cache
        if(!$this->cache->has($keyCollection))

            # Register cache
            self::registerConfig();

        # Get cached collection
        $collection = $this->cache->get($keyCollection);

        # Check collection
        if(empty($collection))
            
            # New error
            throw new CrazyException(
                "Please register asset before use them", 
                500,
                [
                    "custom_code"   =>  "core-001",
                ]
            );

        # Iteration of inputs
        foreach($inputs as $input){

            # Check input
            if(!$input || !is_string($input))

                # Continue
                continue;

            # Prepare input
            $input = str_replace(self::SEPARATOR, "___", $input);

            # Explode input
            $explodedInput = explode("___", $input);

            # Set cursor
            $cursor = $collection;

            # Get value
            $i=0;while(isset($explodedInput[$i])){

                # Check of Assets
                if($explodedInput[$i] == "Asset")

                    # Continue iteration
                    continue;

                else
                # Check if isset
                if(isset($cursor[$explodedInput[$i]]))

                    $cursor = $cursor[$explodedInput[$i]];

            $i++;}

            # Check last cursor is collection
            if(isset($cursor["path"]))

                # Push to result
                $result[$input] = $cursor;

            else
            # If cursor is collection
            #
            #   Need to rewrite this part is more than 2 depth in get input
            #
            if(!empty($cursor))

                # Iteration cursor
                foreach($cursor as $k => $v)

                    # Push to result
                    $result[$input ? "$input.$k" : $k] = $v;

       }

       # Return result
       $this->result = count($result) === 1 ?
           array_pop($result) :
               $result;

        # Return result
        return $this->result;

    }

    /**
     * Open
     * 
     * Open File Content Cursor
     * 
     * @param string $name Name of the file to open
     * @return resource|false
     */
    public function open(string $name = "") {

        # Check result
        if(empty($this->result))

            # New error
            throw new CrazyException(
                "Please search media before open it", 
                500,
                [
                    "custom_code"   =>  "asset-001",
                ]
            );

        else
        # Check if name
        if(!isset($this->result["path"]) && !isset($this->result[$name]))

            # New error
            throw new CrazyException(
                "Searched asset not found in result of your get", 
                500,
                [
                    "custom_code"   =>  "asset-002",
                ]
            );

        elseif(!isset($this->result["path"]) && isset($this->result[$name]))

            # Set file
            $file = $this->result[$name];

        else

            # Set result
            $file = $this->result;

        # Get path
        $filePath = File::path($file["path"]);

        # Check file exists
        if(!$file["exists"] && !File::exists($filePath))

            # New error
            throw new CrazyException(
                "Asset your are looking for don't exists", 
                500,
                [
                    "custom_code"   =>  "asset-003",
                ]
            );

        # Set result
        $result = fopen($filePath, "r");

        # Return result
        return $result;

    }

    /**
     * Get Path
     * 
     * Get File path
     * 
     * @param string $name Name of the file to open
     * @return resource|false
     */
    public function getPath(string $name = "") {

        # Check result
        if(empty($this->result))

            # New error
            throw new CrazyException(
                "Please search media before open it", 
                500,
                [
                    "custom_code"   =>  "asset-001",
                ]
            );

        else
        # Check if name
        if(!isset($this->result["path"]) && !isset($this->result[$name]))

            # New error
            throw new CrazyException(
                "Searched asset not found in result of your get", 
                500,
                [
                    "custom_code"   =>  "asset-002",
                ]
            );

        elseif(!isset($this->result["path"]) && isset($this->result[$name]))

            # Set file
            $file = $this->result[$name];

        else

            # Set result
            $file = $this->result;

        # Get path
        $filePath = File::path($file["path"]);

        # Check file exists
        if(!$file["exists"] && !File::exists($filePath))

            # New error
            throw new CrazyException(
                "Asset your are looking for don't exists", 
                500,
                [
                    "custom_code"   =>  "asset-003",
                ]
            );

        # Set result
        $result = $filePath;

        # Return result
        return $result;

    }

    /**
     * Get Mome Type
     * 
     * Get File Mime Type
     * 
     * @param string $name Name of the file to open
     * @return resource|false
     */
    public function getMimeType(string $name = "") {

        # Check result
        if(empty($this->result))

            # New error
            throw new CrazyException(
                "Please search media before open it", 
                500,
                [
                    "custom_code"   =>  "asset-001",
                ]
            );

        else
        # Check if name
        if(!isset($this->result["path"]) && !isset($this->result[$name]))

            # New error
            throw new CrazyException(
                "Searched asset not found in result of your get", 
                500,
                [
                    "custom_code"   =>  "asset-002",
                ]
            );

        elseif(!isset($this->result["path"]) && isset($this->result[$name]))

            # Set file
            $file = $this->result[$name];

        else

            # Set result
            $file = $this->result;

        # Get path
        $filePath = File::path($file["path"]);

        # Check file exists
        if(!$file["exists"] && !File::exists($filePath))

            # New error
            throw new CrazyException(
                "Asset your are looking for don't exists", 
                500,
                [
                    "custom_code"   =>  "asset-003",
                ]
            );

        # Set result
        $result = $file["mimeType"];

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Parse Assets
     * 
     * Parse Assets config file
     * 
     * @param string $path Path of the file to parse
     * @return array
     */
    private static function _parseAssets(string $path = self::PATH):array {

        # Set result
        $result = [];

        # Open file
        $collection = File::open($path)["Asset"];

        # Prepare asset iterator
        static::_parseAssetIterator($collection);

        # Set result
        $result = $collection;

        # Return result
        return $result;

    }

    /**
     * Parse Asset Iterator
     * 
     * @param $input
     * @return void
     */
    private static function _parseAssetIterator(array &$inputs = []):void {

        # Check input
        if(!empty($inputs))

            # Iteration input
            foreach($inputs as &$input)

                # If is string
                if(is_string($input)){

                    # Get file path
                    $realPath = File::path($input);

                    # Update input
                    $input = [
                        "path"      =>  $input,
                        "extension" =>  File::getFileExtension($realPath),
                        "mimeType"  =>  File::guessMime($realPath),
                        "exists"    =>  File::exists($realPath),
                        "modified"  =>  File::getLastModifiedDate($realPath)
                    ];

                }else
                # If array
                if(is_array($input))

                    static::_parseAssetIterator($input);

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Prepare Context Name
     * 
     * Prepare name for context methods
     * 
     * @param string $name Context name
     * @return string
     */
    private function _prepareContextName(string $name = ""):string {

        # Set result
        $result = "";

        # Extract class name
        if(!$name || !strpos($name, self::_CONTEXT_SEPARATOR))

            # Return result
            return $result;

        # Explode context
        $contextExploded = explode(self::_CONTEXT_SEPARATOR, $name);

        # Set context
        $context = array_pop($contextExploded);

        # Check context
        if($context)

            # Set result
            $result = $context;

        # Return result
        return $result;

    }

    /** Public constants
     ******************************************************
     */

    /** @const string PATH */
    public const PATH = "@app_root/config/Asset.yml";

    /** @const array SEPARATOR */
    public const SEPARATOR = ["/", "."];

    /** Private constants
     ******************************************************
     */

    /** @const string CONTEXT_SEPARATOR */
    private const _CONTEXT_SEPARATOR = "\\";

}