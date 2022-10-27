<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Core;

/**
 * Dependances
 */

use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;

use function PHPUnit\Framework\returnSelf;

/**
 * Media
 *
 * Class for manage media...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Media {

    /** Private parameters
     ******************************************************
     */

    /** @var Cache $cache */
    private $cache = null;

    /** @var string $prefix */
    private $prefix = "";

    /** Public methods
     ******************************************************
     */

    /**
     * Register From Folder
     * 
     * Register all media in folder
     * 
     * @param string $path
     * @param array $options
     * @return self
     */
    public function registerFromFolder(string $path = "", array $options = [
        "extension" =>  [],
        "subfolders"=>  true,
        "prefix"    =>  ""
    ]):self {

        # Prepare path
        $path = File::path($path);

        # Check path
        if(!$path)

            # Return
            return $this;

        # New finder
        $finder = new Finder();

        # Prepare finder
        $finder
            ->files()
            ->in($path)
        ;

        # Check subfolders
        if(!isset($options["subfolders"]) || !$options["subfolders"])

            # Add depth
            $finder->depth('== 0');

        # Check extension
        if(isset($options["extension"]) && !empty($options["extension"])){

            # Set extensions
            $extensions = [];

            # Iteration of extensions
            foreach($options["extension"] as $extension)

                # Add in extension
                $extensions[] = "*.$extension";

            # Add in finder
            $finder->name($extensions);

        }

        # Check result
        if(!$finder->hasResults())

            # Return
            return $this;

        # Prepare cache
        if(!$this->cache)

            # New cache instance
            $this->cache = new Cache();

        # Iteration of file
        foreach($finder as $file){

            # Prepare name
            $name = $this->_prepareName($file->getFilename(), $options["prefix"] ?? "");

            # Prepare key
            $key = Cache::getKeyWithCacheName(__CLASS__, $name);

            # Set current file in cache
            $this->cache->set($key, $file->getRealPath());

        }

        # Return self
        return $this;

    }

    /**
     * Set Prefix
     * 
     * Set Prefix for register and get
     * 
     * @param string $name Name of the prefix
     * @return self
     */
    public function setPrefix(string $name = ""):self {

        # Check prefix
        if($name)

            # Fill prefix
            $this->prefix = $name;

        # Return this
        return $this;

    }

    /**
     * Register
     * 
     * Register list of media
     * 
     * @param string|array $path
     * @return self
     */
    public function register(string|array $inputs = []):self {

        # Return self
        return $this;

    }

    /**
     * Get
     * 
     * Get list of media
     * 
     * @param string|array $path
     * @return string|array
     */
    public function get(string|array $inputs = []):string|array {

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

        # Prepare cache
        if(!$this->cache)

            # New cache instance
            $this->cache = new Cache();

        # Iteration of inputs
        foreach($inputs as $input){

            # Check input
            if(!$input || !is_string($input))

                # Continue
                continue;

            # Name
            $name = $this->_prepareName($input);

            # Set key
            $key = Cache::getKeyWithCacheName(__CLASS__, $name);

            # Push value in result
            $result[$input] = $this->cache->get($key);

        }

        # Return result
        return count($result) === 1 ?
            array_pop($result) :
                $result;

    }

    /**
     * Read Content
     * 
     * Get Content of media
     * 
     * @param string $path
     * @return self
     */
    public function readContent(string $input = ""):self {

        # Return self
        return $this;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Prepare name
     * 
     * @param string $name
     * @param string $prefix
     * @return string
     */
    private function _prepareName(string $name = "", string $prefix = ""):string {

        # Prepare result
        $result = "";

        # Check name
        if(!$name)

            # Return 
            return $result;

        # Check if prefix
        if($prefix)

            # Set result
            $result = trim($prefix, ".").".".trim($name, ".");

        else
        # Check global prefix
        if($this->prefix)

            # Set result
            $result = trim($this->prefix, ".").".".trim($name, ".");

        # No Prefix
        else

            # Set result
            $result = $name;

        # Return name
        return $name;


    }

}