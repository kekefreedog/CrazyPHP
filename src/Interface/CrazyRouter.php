<?php declare(strict_types=1);
/**
 * Router Interface
 *
 * Define the routing operations shared by CrazyPHP vendor adapters.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Interface;

/**
 * Crazy Router
 *
 * Preserve CrazyPHP route patterns, callbacks and custom types across vendors.
 * This is a framework contract, not a PSR routing interface.
 * HTTP dispatch uses the current request method from the server environment.
 */
interface CrazyRouter {

    /**
     * Add Route
     *
     * Register a CrazyPHP pattern, callback, HTTP methods and optional name.
     *
     * @param string $route Pattern using the existing [type:name] syntax
     * @param callable|string|array $callback Controller callback
     * @param string|string[] $requestMethod Allowed HTTP methods
     * @param string $routeName Route name
     */
    public function addRoute(string $route, $callback, $requestMethod = "GET", string $routeName = ""):void;

    /**
     * Get Callback
     *
     * Find a callback for a path using the current HTTP request method.
     *
     * @param string|string[] $route Request path
     * @return callable|string|array|false
     */
    public function getCallback($route);

    /**
     * Call Route
     *
     * Dispatch a path through registered middleware and return the controller result.
     *
     * @param string|string[] $route Request path
     * @return mixed
     */
    public function callRoute($route);

    /**
     * Reverse
     *
     * Generate a URL from a registered route name and parameter values.
     *
     * @param string $routeName Route name
     * @param array $parameters Placeholder values
     * @return string
     */
    public function reverse(string $routeName, array $parameters = []):string;

    /**
     * Register Middleware
     *
     * Register middleware for a route pattern or * for every route.
     *
     * @param string $router Route pattern
     * @param callable $middleware Existing CrazyPHP middleware callback
     */
    public function registerMiddleware(string $router, callable $middleware):void;

    /**
     * Add Type
     *
     * Register a custom placeholder type without changing application YAML.
     *
     * @param string $typeName Placeholder type name
     * @param class-string<CrazyRouterType> $className Custom pattern implementation
     */
    public function addType(string $typeName, string $className):void;

    /**
     * Route Exists
     *
     * Check whether a route pattern is registered for any HTTP method.
     *
     * @return bool
     */
    public function routeExists(string $route):bool;

    /**
     * Get Route By Name
     *
     * Return the registered pattern for a route name.
     *
     * @return string
     */
    public function getRouteByName(string $routeName):string;

    /**
     * Get Param
     *
     * Read a parameter from the last matched route.
     *
     * @return mixed
     */
    public function getParam(string $name);

    /**
     * Has Param
     *
     * Check whether the last matched route contains a parameter.
     *
     * @return bool
     */
    public function hasParam(string $name):bool;

    /**
     * Get Called Route
     *
     * Return the pattern selected by the last route lookup.
     *
     * @return string
     */
    public function getCalledRoute():string;

    /**
     * Clear
     *
     * Clear registered routes and middleware.
     */
    public function clear():void;

    /**
     * Dump On Disk
     *
     * Write adapter-specific compiled routes to a trusted cache file.
     *
     * @param string $filePath Cache file; callbacks must be serializable
     */
    public function dumpOnDisk(string $filePath = "./cache/cache.php"):void;

    /**
     * Load From Disk
     *
     * Load routes written by the same adapter; register custom types before loading.
     *
     * @param string $filePath Trusted adapter cache file
     */
    public function loadFromDisk(string $filePath = "./cache/cache.php"):void;

    /**
     * Warm Cache
     *
     * Prepare registered routes for subsequent matching.
     */
    public function warmCache():void;

    /**
     * Set No Processor Found Error Handler
     *
     * Replace the missing-route handler and return the previous handler.
     *
     * @return callable|null
     */
    public function setNoProcessorFoundErrorHandler(callable $function):?callable;

    /**
     * Get No Processor Error Handler
     *
     * Return the active missing-route handler.
     *
     * @return callable
     */
    public function getNoProcessorErrorHandler():callable;

    /**
     * No Processor Found Error Handler
     *
     * Run the default missing-route error behavior.
     */
    public function noProcessorFoundErrorHandler(string $route):void;

    /**
     * Add Get Route
     *
     * Register an object method for GET requests.
     */
    public function addGetRoute(string $route, object $object, string $method):void;

    /**
     * Add Post Route
     *
     * Register an object method for POST requests.
     */
    public function addPostRoute(string $route, object $object, string $method):void;

    /**
     * Fetch Actions
     *
     * Register action methods from an object using an optional HTTP method map.
     */
    public function fetchActions(object $object, array $map = []):void;

}
