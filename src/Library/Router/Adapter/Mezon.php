<?php declare(strict_types=1);
/**
 * Mezon Router Adapter
 *
 * Adapt Mezon routing to CrazyPHP's router and parameter type contracts.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Router\Adapter;

/**
 * Dependencies
 */
use Mezon\Router\Router as VendorRouter;
use CrazyPHP\Interface\CrazyRouterType;
use CrazyPHP\Interface\CrazyRouter;
use InvalidArgumentException;

/**
 * Mezon
 *
 * Keep vendor behavior and URL generation fixes inside the adapter.
 */
class Mezon extends VendorRouter implements CrazyRouter {

    /** Public Methods
     ******************************************************
     */

    /**
     * Add Type
     *
     * @param string $typeName Placeholder type name
     * @param class-string<CrazyRouterType> $className Custom pattern implementation
     * @return void
     * @throws InvalidArgumentException When the class does not implement CrazyRouterType
     */
    public function addType(string $typeName, string $className):void {

        # Validate the static type contract without constructing the class
        if(!is_a($className, CrazyRouterType::class, true))

            # New exception
            throw new InvalidArgumentException("Router types must implement ".CrazyRouterType::class.".");

        # Register the existing search and parser expressions with Mezon
        parent::addType(strtolower($typeName), $className);

        # A type registered after warming must be considered by the next compilation
        $this->clearOtherData();

    }

    /**
     * Reverse
     *
     * Preserve URL generation for custom parameter types.
     *
     * @param string $routeName Route name
     * @param array $parameters Placeholder values
     * @return string
     */
    public function reverse(string $routeName, array $parameters = []):string {

        # Read the vendor's registered pattern
        $result = $this->getRouteByName($routeName);

        # Resolve built-in and custom placeholders without interpreting replacement values as regex syntax
        foreach($parameters as $name => $value)

            # Set result
            $result = preg_replace_callback(
                "/\\[[A-Za-z0-9_-]+:".preg_quote((string)$name, "/")."\\]/",
                static fn(array $matches):string => (string)$value,
                $result
            );

        # Return the generated URL
        return $result;

    }

    /** Protected Methods
     ******************************************************
     */

    /**
     * Add Param Route
     *
     * Keep registrations after cache restoration from reusing stale matching tables.
     *
     * @param string $requestMethod HTTP method
     * @param string $route Original route pattern
     * @param callable|string|array $callback Controller callback
     * @return void
     */
    protected function addParamRoute(string $requestMethod, string $route, $callback):void {

        # Compiled bunch keys count captures; restore insertion keys before appending a route
        foreach($this->paramRoutes[$requestMethod] as &$bunch)

            # Check whether this batch contains compiled capture-count indexes
            if(isset($bunch["regexp"]) && $bunch["bunch"]){

                # Restore sequential insertion keys for the registered routes
                $bunch["bunch"] = array_combine(range(1, count($bunch["bunch"])), array_values($bunch["bunch"]));

                # Discard the stale batch expression before appending a route
                unset($bunch["regexp"]);

            }

        # Append the parameterized route through Mezon's registration method
        parent::addParamRoute($requestMethod, $route, $callback);

        # Invalidate Mezon's compiled matching data after registration
        $this->clearOtherData();

    }

}
