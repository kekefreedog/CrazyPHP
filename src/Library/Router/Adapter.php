<?php declare(strict_types=1);
/**
 * Router Adapter
 *
 * Share CrazyPHP route syntax, dispatch and cache behavior across routing engines.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Router;

/**
 * Dependencies
 */
use CrazyPHP\Interface\CrazyRouterType;
use CrazyPHP\Interface\CrazyRouter;
use InvalidArgumentException;
use RuntimeException;

/**
 * Adapter
 *
 * Vendors compile and match paths; CrazyPHP owns callbacks and middleware.
 */
abstract class Adapter implements CrazyRouter {

    /** Private Parameters
     ******************************************************
     */

    /** @var array $_routes Registered routes in application order */
    private array $_routes = [];

    /** @var array $_names Original patterns indexed by name */
    private array $_names = [];

    /** @var array $_types Custom parameter type classes */
    private array $_types = [];

    /** @var array $_middleware Runtime middleware, excluded from route caches */
    private array $_middleware = [];

    /** @var array $_parameters Parameters of the last matched route */
    private array $_parameters = [];

    /** @var string $_calledRoute Original pattern of the last matched route */
    private string $_calledRoute = "";

    /** @var ?array $_compiled Vendor data and parameter maps, ready for disk caching */
    private ?array $_compiled = null;

    /** @var ?object $_matcher Runtime vendor matcher, recreated from compiled data */
    private ?object $_matcher = null;

    /** @var ?callable $_errorHandler Missing-route handler */
    private $_errorHandler = null;

    /** Public Methods
     ******************************************************
     */

    /**
     * Add Route
     *
     * {@inheritdoc}
     */
    public function addRoute(string $route, $callback, $requestMethod = "GET", string $routeName = ""):void {

        # Preserve application registration order and the final pattern for each name
        $pattern = $this->_prepareRoute($route);

        # Register the route for each requested HTTP method
        foreach((array)$requestMethod as $method){

            # Validate the HTTP method against the application contract
            $this->_validateMethod($method);

            # Append the callback and pattern in registration order
            $this->_routes[] = ["pattern" => $pattern, "callback" => $callback, "method" => $method];

        }

        # Check whether a route name was provided
        if($routeName !== "")

            # Associate the name with the final registered pattern
            $this->_names[$routeName] = $pattern;

        # Invalidate matching tables after registration changes
        $this->_invalidate();

    }

    /**
     * Get Callback
     *
     * {@inheritdoc}
     */
    public function getCallback($route):mixed {

        # Match once without invoking the controller or middleware
        $path = $this->_prepareRoute($route);

        # Read the current request method, defaulting to GET
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";

        # Validate the HTTP method against the application contract
        $this->_validateMethod($method);

        # Compile matching tables if no valid cache is available
        $this->warmCache();

        # Create the runtime matcher from compiled vendor data when needed
        $this->_matcher ??= $this->createMatcher($this->_compiled["vendor"]);

        # Match the normalized path with the selected vendor
        $match = $this->matchRoute($this->_matcher, $method, "/".$path);

        # Select the matched route or the method-specific wildcard
        $id = $match["id"] ?? $this->_compiled["fallback"][$method] ?? null;

        # Clear parameters from the previous request
        $this->_parameters = [];

        # Reset the previously selected pattern
        $this->_calledRoute = "";

        # Check whether matching and wildcard fallback both failed
        if($id === null){

            # Invoke the configured missing-route handler
            ($this->getNoProcessorErrorHandler())($path);

            # Report that no callback was found
            return false;

        }

        # Translate synthetic vendor parameter names back to application names
        foreach($this->_compiled["parameters"][$id] ?? [] as $key => $name)

            # Store the matched value under its original application parameter name
            $this->_parameters[$name] = $match["parameters"][$key];

        # Record the original pattern selected by the matcher
        $this->_calledRoute = $this->_routes[$id]["pattern"];

        # Return the registered callback without executing it
        return $this->_routes[$id]["callback"];

    }

    /**
     * Call Route
     *
     * {@inheritdoc}
     */
    public function callRoute($route):mixed {

        # Use the same lookup and arguments for every engine
        $callback = $this->getCallback($route);

        # Check whether lookup found a callable route
        if($callback === false)

            # Stop dispatch when no callback matched
            return null;

        # Normalize the path passed to middleware and the controller
        $path = $this->_prepareRoute($route);

        # Read middleware registered for every route
        $middleware = $this->_middleware["*"] ?? [];

        # Avoid applying wildcard middleware twice for a wildcard match
        if($this->_calledRoute !== "*")

            # Append route-specific middleware after global middleware
            $middleware = array_merge($middleware, $this->_middleware[$this->_calledRoute] ?? []);

        # Check whether dispatch can call the controller directly
        if(!$middleware)

            # Call the controller with the path and named parameter array
            return call_user_func($callback, $path, $this->_parameters);

        # Retain the established middleware argument transformation contract
        $this->_parameters = array_merge([$path], $this->_parameters);

        # Apply middleware in registration order
        foreach($middleware as $handler){

            # Call middleware with the arguments prepared by the preceding step
            $result = call_user_func_array($handler, $this->_parameters);

            # Check whether middleware returned a replacement argument list
            if(is_array($result)){

                # Use the returned array as the next argument list
                $this->_parameters = $result;

            }elseif($result !== null){

                # Wrap a non-null middleware result as the single next argument
                $this->_parameters = [$result];

            }

        }

        # Call the controller with the final middleware arguments
        return call_user_func_array($callback, $this->_parameters);

    }

    /**
     * Reverse
     *
     * {@inheritdoc}
     */
    public function reverse(string $routeName, array $parameters = []):string {

        # Keep CrazyPHP names and literal replacement values independent of vendor syntax
        $result = $this->getRouteByName($routeName);

        # Substitute each supplied parameter in the original pattern
        foreach($parameters as $name => $value)

            # Replace the placeholder while preserving literal replacement values
            $result = preg_replace_callback(
                "/\\[[A-Za-z0-9_-]+:".preg_quote((string)$name, "/")."\\]/",
                static fn(array $matches):string => (string)$value,
                $result
            );

        # Return the generated URL
        return $result;

    }

    /**
     * Register Middleware
     *
     * {@inheritdoc}
     */
    public function registerMiddleware(string $router, callable $middleware):void {

        # Append middleware under its normalized route pattern
        $this->_middleware[trim($router, "/")][] = $middleware;

    }

    /**
     * Add Type
     *
     * {@inheritdoc}
     */
    public function addType(string $typeName, string $className):void {

        # Register static types without constructing application classes
        if(!is_a($className, CrazyRouterType::class, true))

            # Reject classes that do not implement the custom type contract
            throw new InvalidArgumentException("Router types must implement ".CrazyRouterType::class.".");

        # Store the custom type under its normalized name
        $this->_types[strtolower($typeName)] = $className;

        # Invalidate matching tables after registration changes
        $this->_invalidate();

    }

    /**
     * Route Exists
     *
     * {@inheritdoc}
     */
    public function routeExists(string $route):bool {

        # Check registered patterns across all HTTP methods
        return in_array(trim($route, "/"), array_column($this->_routes, "pattern"), true);

    }

    /**
     * Get Route By Name
     *
     * {@inheritdoc}
     */
    public function getRouteByName(string $routeName):string {

        # Return the named pattern or report an unknown route name
        return $this->_names[$routeName] ?? throw new RuntimeException("Route with name ".$routeName." does not exist");

    }

    /**
     * Get Param
     *
     * {@inheritdoc}
     */
    public function getParam(string $name):mixed {

        # Return the matched parameter or report its absence
        return $this->_parameters[$name] ?? throw new RuntimeException("Route parameter ".$name." does not exist");

    }

    /**
     * Has Param
     *
     * {@inheritdoc}
     */
    public function hasParam(string $name):bool {

        # Check whether the last match contains the requested parameter
        return isset($this->_parameters[$name]);

    }

    /**
     * Get Called Route
     *
     * {@inheritdoc}
     */
    public function getCalledRoute():string {

        # Return the original pattern of the last match
        return $this->_calledRoute;

    }

    /**
     * Clear
     *
     * {@inheritdoc}
     */
    public function clear():void {

        # Custom types and the error handler remain registered
        $this->_routes = $this->_names = $this->_middleware = $this->_parameters = [];

        # Reset the previously selected pattern
        $this->_calledRoute = "";

        # Invalidate matching tables after registration changes
        $this->_invalidate();

    }

    /**
     * Dump On Disk
     *
     * {@inheritdoc}
     */
    public function dumpOnDisk(string $filePath = "./cache/cache.php"):void {

        # Cache compiled vendor tables, never runtime middleware or matcher objects
        $this->warmCache();

        # Prepare the adapter identity, route metadata and compiled tables for export
        $data = [
            "adapter"  => static::class,
            "version"  => 1,
            "routes"   => $this->_routes,
            "names"    => $this->_names,
            "compiled" => $this->_compiled,
        ];

        # YAML controllers are strings; object callbacks cannot be exported as portable PHP
        array_walk_recursive($data, static function(mixed $value):void {

            # Check for runtime values that cannot be exported to the route cache
            if(is_object($value) || is_resource($value))

                # Reject callbacks whose runtime state cannot be restored from this cache
                throw new InvalidArgumentException("Cached router callbacks must use class names or static method arrays.");

        });

        # Write the compiled cache as executable PHP and check for failure
        if(file_put_contents($filePath, "<?php return ".var_export($data, true).";\n", LOCK_EX) === false)

            # Report an unsuccessful cache write
            throw new RuntimeException("Unable to write router cache: ".$filePath);

    }

    /**
     * Load From Disk
     *
     * {@inheritdoc}
     */
    public function loadFromDisk(string $filePath = "./cache/cache.php"):void {

        # Only load trusted files produced by the same adapter and cache format
        $data = require $filePath;

        # Check the cached adapter identity and format version
        if(!is_array($data) || ($data["adapter"] ?? null) !== static::class || ($data["version"] ?? null) !== 1)

            # Reject a cache produced by another adapter or format
            throw new InvalidArgumentException("Incompatible router cache for ".static::class.".");

        # Restore route patterns, callbacks and methods
        $this->_routes = $data["routes"];

        # Restore the original patterns indexed by route name
        $this->_names = $data["names"];

        # Restore compiled vendor tables and parameter mappings
        $this->_compiled = $data["compiled"];

        # Discard the runtime matcher so it uses the current compiled tables
        $this->_matcher = null;

        # Clear parameters from the previous request
        $this->_parameters = [];

        # Reset the previously selected pattern
        $this->_calledRoute = "";

    }

    /**
     * Warm Cache
     *
     * {@inheritdoc}
     */
    public function warmCache():void {

        # Check whether compiled matching tables are already available
        if($this->_compiled !== null)

            # Keep the existing compiled tables without rebuilding them
            return;

        # Preserve static precedence, last static registration, and first dynamic registration
        $static = $dynamic = $fallback = $parameters = [];

        # Translate routes in their application registration order
        foreach($this->_routes as $id => $route){

            # Check whether the pattern is a wildcard fallback
            if($route["pattern"] === "*"){

                # Retain the last wildcard registered for this HTTP method
                $fallback[$route["method"]] = $id;

                # Continue with the next registration
                continue;

            }

            # Translate the existing application pattern to vendor placeholders
            $translated = $this->_translateRoute($route["pattern"]);

            # Record the original parameter names for this route
            $parameters[$id] = $translated["parameters"];

            # Attach the route identifier and HTTP method to the translated definition
            $entry = $translated + ["id" => $id, "method" => $route["method"]];

            # Identify equivalent patterns within the same HTTP method
            $key = $route["method"]." ".$translated["path"].serialize($translated["requirements"]);

            # Check whether the translated route contains parameters
            if($translated["parameters"]){

                # Preserve the first registration of an equivalent dynamic pattern
                $dynamic[$key] ??= $entry;

            }else{

                # Preserve the last registration of an equivalent static pattern
                $static[$key] = $entry;

            }

        }

        # Compile static routes before dynamic routes and retain the application metadata
        $this->_compiled = [
            "vendor"     => $this->compileRoutes(array_merge(array_values($static), array_values($dynamic))),
            "parameters" => $parameters,
            "fallback"   => $fallback,
        ];

    }

    /**
     * Set No Processor Found Error Handler
     *
     * {@inheritdoc}
     */
    public function setNoProcessorFoundErrorHandler(callable $function):?callable {

        # Keep the previously configured error handler
        $previous = $this->_errorHandler;

        # Install the replacement missing-route handler
        $this->_errorHandler = $function;

        # Return the previous handler to the caller
        return $previous;

    }

    /**
     * Get No Processor Error Handler
     *
     * {@inheritdoc}
     */
    public function getNoProcessorErrorHandler():callable {

        # Return the active handler, initializing the default when needed
        return $this->_errorHandler ??= [$this, "noProcessorFoundErrorHandler"];

    }

    /**
     * No Processor Found Error Handler
     *
     * {@inheritdoc}
     */
    public function noProcessorFoundErrorHandler(string $route):void {

        # Report an unmatched route using the default error behavior
        throw new RuntimeException("The processor was not found for the route ".$route, -1);

    }

    /**
     * Add Get Route
     *
     * {@inheritdoc}
     */
    public function addGetRoute(string $route, object $object, string $method):void {

        # Register the object method for GET requests
        $this->addRoute($route, [$object, $method], "GET");

    }

    /**
     * Add Post Route
     *
     * {@inheritdoc}
     */
    public function addPostRoute(string $route, object $object, string $method):void {

        # Register the object method for POST requests
        $this->addRoute($route, [$object, $method], "POST");

    }

    /**
     * Fetch Actions
     *
     * {@inheritdoc}
     */
    public function fetchActions(object $object, array $map = []):void {

        # Retain the existing actionName to action-name convention
        foreach(get_class_methods($object) as $method){

            # Skip methods without the action prefix
            if(!str_starts_with($method, "action"))

                # Continue with the next registration
                continue;

            # Extract the action name used by the HTTP method map
            $name = str_replace("action", "", $method);

            # Convert the action name to the existing kebab-case route convention
            $route = strtolower(preg_replace("/[A-Z]/", "-$0", lcfirst($name)));

            # Register the action with its configured methods or the GET and POST defaults
            $this->addRoute($route, [$object, $method], $map[$name] ?? ["GET", "POST"]);

        }

    }

    /** Protected Methods | Vendor
     ******************************************************
     */

    /**
     * Compile Routes
     *
     * @param array $routes Translated paths, requirements, methods and numeric IDs
     * @return array Exportable vendor matching tables
     */
    abstract protected function compileRoutes(array $routes):array;

    /**
     * Create Matcher
     *
     * @param array $compiled Previously compiled vendor tables
     * @return object Runtime matcher without route recompilation
     */
    abstract protected function createMatcher(array $compiled):object;

    /**
     * Match Route
     *
     * @param object $matcher Vendor matcher
     * @param string $method HTTP method
     * @param string $path Normalized request path
     * @return ?array Matched id and parameters, or null for no matching method/path
     */
    abstract protected function matchRoute(object $matcher, string $method, string $path):?array;

    /** Private Methods
     ******************************************************
     */

    /**
     * Invalidate
     *
     * Discard compiled data when route definitions or custom types change.
     */
    private function _invalidate():void {

        # Discard compiled route tables after a definition change
        $this->_compiled = null;

        # Discard the runtime matcher so it uses the current compiled tables
        $this->_matcher = null;

    }

    /**
     * Prepare Route
     *
     * @param string|array $route Request path or path segments
     * @return string Legacy CrazyPHP path normalization
     */
    private function _prepareRoute(string|array $route):string {

        # Check whether empty path segments request the current server URI
        if(is_array($route) && ($route[0] ?? null) === "" && isset($_SERVER["REQUEST_URI"]))

            # Read the current request URI
            $route = $_SERVER["REQUEST_URI"];

        # Check for the application root path
        if($route === "/")

            # Preserve the legacy root-to-index mapping
            $route = "index";

        # Join path segments, decode once and remove surrounding slashes
        return trim(urldecode(is_array($route) ? implode("/", $route) : $route), "/");

    }

    /**
     * Validate Method
     *
     * @param string $method HTTP method from the existing application schema
     */
    private function _validateMethod(string $method):void {

        # Check the methods supported by the existing route schema
        if(!in_array($method, ["GET", "POST", "PUT", "DELETE", "OPTION", "PATCH"], true))

            # Reject an unsupported request method
            throw new InvalidArgumentException("Unsupported request method: \"".$method."\"", -1);

    }

    /**
     * Translate Route
     *
     * @param string $pattern Original CrazyPHP pattern
     * @return array Vendor path with synthetic placeholders and original parameter names
     */
    private function _translateRoute(string $pattern):array {

        # Preserve built-in matching; [\s\S] also works in vendor prefix analysis without Unicode mode
        $types = [
            "i"  => '[-+]{0,1}[0-9]+',
            "a"  => '[a-z0-9A-Z_.\-@]+',
            "il" => '[0-9,]+',
            "fp" => '[-+]{0,1}[0-9]+.[0-9]+',
            "s"  => '[\s\S][^/]+',
        ];

        # Prepare parameter requirements and original-name mappings
        $requirements = $parameters = [];

        # Translate each typed placeholder without modifying the application pattern
        $path = preg_replace_callback(
            "/\\[([A-Za-z0-9_-]+):(".CrazyRouterType::PARAMETER_NAME_REGEX.")\\]/",
            function(array $matches) use (&$requirements, &$parameters, $types):string {

                # Normalize the matched parameter type name
                $type = strtolower($matches[1]);

                # Find the registered custom type class, if any
                $class = $this->_types[$type] ?? null;

                # Check whether the custom type recognizes this placeholder
                if($class !== null && preg_match("~^".$class::searchRegExp()."$~", $matches[0])){

                    # Read the parser expression from the custom type
                    $regex = $class::parserRegExp();

                }else{

                    # Use a built-in expression or reject an unknown type
                    $regex = $types[$type] ?? throw new InvalidArgumentException("Unknown router parameter type: ".$type);

                }

                # Generate a vendor-safe placeholder name
                $key = "cp".count($parameters);

                # Map the vendor placeholder to the original parameter name
                $parameters[$key] = $matches[2];

                # Associate the parser expression with the vendor placeholder
                $requirements[$key] = $regex;

                # Return the translated placeholder
                return "{".$key."}";

            },
            "/".$pattern
        );

        # Return the vendor path, requirements and original parameter names
        return ["path" => $path, "requirements" => $requirements, "parameters" => $parameters];

    }

}
