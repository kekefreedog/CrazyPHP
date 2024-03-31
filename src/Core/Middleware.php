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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
use CrazyPHP\Library\File\Webpack;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\Header;
use CrazyPHP\Model\Context;

/**
 * Middleware
 *
 * Class for manage framework middleware...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Middleware{

    /**
     * Fill Context
     * 
     * Fill context with current router settings
     */
    public static function FillContext(string $route, ...$parameters){

        # Set current route in context
        Context::set(
            [
                "route"         =>  $route,
                "parameters"    =>  $parameters
            ],
            "routes.current",
            true
        );

    }
    
    /**
     * Lucky Php Specials Parameters
     * 
     * Check Lucky Php Special Parameters :
     * - LUCKYPHP___CLEAR_CACHE
     */
    public static function LuckyPhpSpecialsParameters(string $route, ...$parameters) {

        # Check get values
        if(empty($_GET))

            # Stop function
            return;

        # Check LUCKYPHP___CLEAR_CACHE
        if(isset($_GET["LUCKYPHP___CLEAR_CACHE"])){

            # New cache
            $cache = new Cache();

            # Clear cache
            $cache->clear();

        }

    }

    /**
     * Update front config is in watch mode
     */
    public static function UpdateFrontConfigIfWatchMode(string $route, ...$parameters) {

        # If watch mode, search the last hash and set it in the config front
        if(Config::getValue("Front.lastBuild.watch"))

            # Get hash
            Webpack::getHash();

    }

    /**
     * Server Request Creator
     * 
     * Create server request
     * 
     * @return 
     */
    public static function ServerRequestCreator(string $route, ...$parameters){

        # Create PSR-7 Object
        $psr17Factory = new Psr17Factory();

        # New server request
        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        # Create server request
        $serverRequest = $creator->fromGlobals();

        # Fill headers given in context
        Context::set(
            Header::clean($serverRequest->getHeaders()),
            "routes.current.headers",
            true,
            true
        );

        # Return global
        return $serverRequest;

    }

}