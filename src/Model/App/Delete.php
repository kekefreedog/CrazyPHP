<?php declare(strict_types=1);
/**
 * New application
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model\App;

/**
 * Dependances
 */
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use CrazyPHP\Model\Docker\Delete as DockerDelete;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Structure;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;

/**
 * Delete Application
 *
 * Classe for deletion application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

    /**
     * Constructor
     * 
     * Construct current class
     * 
     * @return Create
     */
    public function __construct(){

    }

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

        # Return result
        return $result;

    }

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run delete of project
     *
     * @return Delete
     */
    public function run():self {

        /**
         * Run Cache Cleaner
         * - Clear Cache Files
         */
        $this->runCacheCleaner();

        /**
         * Run Retrieve Original Composer
         * 1. Get the last new composer backup and copy it on the root od the app
         */
        $this->runRetrieveOriginalComposer();

        /**
         * Run Structure Folder
         * 1. Delete structure folder
         */
        $this->runStructureFolder();

        /**
         * Run Docker Compose
         * 1. Remove docker composer
         */
        $this->runDockerCompose();

        /**
         * Run Clean Front Files
         * 1. Clean public and package / vendor files
         */
        $this->runCleanFrontFiles();

        # Return this
        return $this;

    }

    /**
     * Run Cache Cleaner
     * 
     * Steps :
     * - Clear Cache Files
     * 
     * @return Delete
     */
    public function runCacheCleaner():Delete {

        # Try catch Check Driver Exception
        try{

            # New cache instance
            $cache = new Cache();

            # Clear cache
            $cache->clear();

        }catch(PhpfastcacheDriverCheckException $e){

            # Echo message
            echo "〜 No Mongodb driver for cache... continue".PHP_EOL;

        }

        # Clear cache path
        File::removeAll(Cache::PATH);

        # Return instance
        return $this;

    }

    /**
     * Run Retrieve Original Composer
     * 
     * Steps
     * 1. Get the last new composer backup and copy it on the root od the app
     * 
     * @return self
     */
    public function runRetrieveOriginalComposer():self {

        # New finder
        $finder = New Finder();

        # Prepare finder
        $finder
            ->files()
            ->name('/^\d+-new-composer\.json$/')
            ->in(File::path("@app_root/assets/Json/backup/composer/"))
        ;

        # Check finder has result
        if(!$finder->hasResults()){

            # Echo that not backup has been found
            echo "Not composer backup has been found ⚠️";

            # Return instance
            return $this;

        }

        # Prepare sorted files
        $sortedFiles = iterator_to_array($finder);

        # Sort the matching files by timestamp (DESC)
        usort($sortedFiles, function ($a, $b) {
            preg_match('/^(\d+)-/', $a->getFilename(), $aMatches);
            preg_match('/^(\d+)-/', $b->getFilename(), $bMatches);
            $aTimestamp = intval($aMatches[1]);
            $bTimestamp = intval($bMatches[1]);
            # return $aTimestamp - $bTimestamp; # ASC
            return $bTimestamp - $aTimestamp; # DESC
        });

        # Get filepath of the file to copy
        $composerFilepath = $sortedFiles[0];

        # Replace composer at the root of the app
        File::copy($composerFilepath->getRealPath(), "@app_root/composer.json");

        # Return instance
        return $this;

    }

    /**
     * Run Structure Folder
     * 
     * Steps : 
     * 1. Delete structure folder
     * 
     * @return self
     */
    public function runStructureFolder():self {

        # Get path of structure
        $structurePath = File::path(Structure::DEFAULT_TEMPLATE);

        # Run creation of docker structure
        Structure::remove($structurePath);
        Structure::remove($structurePath);

        # Remove app folder
        File::removeAll("@app_root/app");

        # Remove .database folder
        File::removeAll("@app_root/.database");

        # Remove asset folder
        File::removeAll("@app_root/assets");

        # Return instance
        return $this;

    }

    /**
     * Run Remove of Docker if exists
     * 
     * Steps:
     * 1. Remove docker composer
     * 
     * @return self
     */
    public function runDockerCompose():self {

        # New docker compose delete instance
        $instance = new DockerDelete();

        # Catch error
        try {

            # Run instance
            $instance->run();

        } catch (CrazyException $e) { }

        # Return this
        return $this;

    }

    /**
     * Run Clean Front Files
     * 
     * Steps :
     * 1. Clean Front Files
     */
    public function runCleanFrontFiles():self {

        # Remove all inside file
        File::removeAll("@app_root/public");

        # Return this
        return $this;

    }

}