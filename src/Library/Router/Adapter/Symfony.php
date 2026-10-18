<?php declare(strict_types=1);
/**
 * Symfony Routing Adapter
 *
 * Match CrazyPHP routes with symfony/routing.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Router\Adapter;

/**
 * Dependencies
 */
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Route as VendorRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use CrazyPHP\Library\Router\Adapter;

/**
 * Symfony
 *
 * Store Symfony's compiled matching tables in the existing CrazyPHP route cache.
 */
class Symfony extends Adapter {

    /** Protected Methods
     ******************************************************
     */

    /**
     * Compile Routes
     *
     * {@inheritdoc}
     */
    protected function compileRoutes(array $routes):array {

        # Create the Symfony route collection
        $collection = new RouteCollection();

        # Register each translated route in collection order
        foreach($routes as $route)

            # Add the vendor path, parameter requirements and allowed HTTP method
            $collection->add((string)$route["id"], new VendorRoute(
                $route["path"],
                [],
                $route["requirements"],
                ["utf8" => true],
                "",
                [],
                [$route["method"]]
            ));

        # Return Symfony's compiled matching tables for caching
        return (new CompiledUrlMatcherDumper($collection))->getCompiledRoutes();

    }

    /**
     * Create Matcher
     *
     * {@inheritdoc}
     */
    protected function createMatcher(array $compiled):object {

        # Create the Symfony matcher directly from compiled routes
        return new CompiledUrlMatcher($compiled, new RequestContext());

    }

    /**
     * Match Route
     *
     * {@inheritdoc}
     */
    protected function matchRoute(object $matcher, string $method, string $path):?array {

        # Set the HTTP method used by Symfony's matching context
        $matcher->getContext()->setMethod($method);

        # Attempt matching while allowing CrazyPHP to handle missing routes
        try{

            # Paths are already decoded by CrazyPHP; protect percent signs from a second decode
            $result = $matcher->match(str_replace("%", "%25", $path));

            # Return the original route identifier and matched parameter values
            return ["id" => (int)$result["_route"], "parameters" => $result];

        }catch(ResourceNotFoundException|MethodNotAllowedException){

            # Let the common adapter apply the configured wildcard or missing-route handler
            return null;

        }

    }

}
