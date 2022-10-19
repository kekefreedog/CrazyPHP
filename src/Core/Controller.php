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
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Router\Router as LibraryRouter;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Mezon\Router\Router as VendorRouter;
use CrazyPHP\Exception\CrazyException;
use Nyholm\Psr7\Factory\Psr17Factory;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use Nyholm\Psr7\Request;

/**
 * Controller
 *
 * Class for manage you app controllers'...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Controller {

    /** Public static methods | Response
     ******************************************************
     */
    

}