<?php declare(strict_types=1);
/**
 * League Route Adapter
 *
 * Match CrazyPHP routes with league/route while retaining framework dispatch.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Router\Adapter;

/**
 * Dependencies
 */
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use League\Route\Dispatcher as VendorDispatcher;
use FastRoute\RouteParser\Std as RouteParser;
use League\Route\Router as VendorRouter;
use Nyholm\Psr7\ServerRequest;
use FastRoute\RouteCollector;

/**
 * League Route
 *
 * League uses FastRoute internally; only its matcher participates in CrazyPHP dispatch.
 */
class LeagueRoute extends FastRoute {

    /** Protected Methods
     ******************************************************
     */

    /**
     * Compile Routes
     *
     * {@inheritdoc}
     */
    protected function compileRoutes(array $routes):array {

        # Create the FastRoute collector used by League's route registration
        $collector = new RouteCollector(new RouteParser(), new DataGenerator());

        # Create the League router using the shared FastRoute collector
        $router = new VendorRouter($collector);

        # Register each translated route in collection order
        foreach($routes as $route)

            # Register the route with a stable identifier for cache serialization
            $router->map($route["method"], $this->getVendorPath($route), (string)$route["id"])->setName((string)$route["id"]);

        # Prepare League routes using a neutral request without host conditions
        $router->prepareRoutes(new ServerRequest("GET", "/"));

        # Read the compiled matching tables from the collector
        $compiled = $collector->getData();

        # Replace runtime League route objects with IDs before caching the compiled tables
        foreach($compiled[0] as &$paths)

            # Visit each static handler registered for the current HTTP method
            foreach($paths as &$handler)

                # Replace the League route object with its numeric identifier
                $handler = (int)$handler->getName();

        # Visit compiled dynamic routes grouped by HTTP method
        foreach($compiled[1] as &$bunches)

            # Visit each compiled route batch for the current HTTP method
            foreach($bunches as &$bunch)

                # Visit each handler entry in the compiled route batch
                foreach($bunch["routeMap"] as &$entry)

                    # Replace the dynamic League route object with its numeric identifier
                    $entry[0] = (int)$entry[0]->getName();

        # Return the serializable matching tables with Unicode support
        return $this->enableUnicode($compiled);

    }

    /**
     * Create Matcher
     *
     * {@inheritdoc}
     */
    protected function createMatcher(array $compiled):object {

        # Use matching only; CrazyPHP invokes its own controllers and middleware
        return new VendorDispatcher($compiled);

    }

}
