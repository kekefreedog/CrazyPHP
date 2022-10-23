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

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cache;
use CrazyPHP\Model\Env;

/**
 * Context
 *
 * Methods for interacting with context of current route
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Context{

    /** Public static methods | Context
     ******************************************************
     */

    /**
     * Get
     * 
     * Get context
     * 
     * @return array
     */
    public static function get():array {

        # Set result
        $result = $GLOBALS[static::PREFIX] ?? [];
        
        # Return result
        return $result;

    }

    /** Public static methods | Routes
     ******************************************************
     */

    /**
     * Set Current Route
     * 
     * @param string $routeName Name of the current route
     * @return void
     */
    public static function setCurrentRoute(string $routeName = ""):void {

        # Check route name
        if(!$routeName)

            # Return
            return;

        # Route collection
        $data = Router::getByName($routeName);

        # Fill context
        $GLOBALS[static::PREFIX]["ROUTES"]["CURRENT"] = $data;

    }

    /** Public Constants
     ******************************************************
     */

    /** @const PREFIX */
    public const PREFIX = "__CRAZY_CONTEXT";


}