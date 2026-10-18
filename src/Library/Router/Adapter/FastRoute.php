<?php declare(strict_types=1);
/**
 * FastRoute Adapter
 *
 * Match CrazyPHP routes with nikic/fast-route.
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
use FastRoute\Dispatcher\GroupCountBased as VendorDispatcher;
use FastRoute\RouteParser\Std as RouteParser;
use CrazyPHP\Library\Router\Adapter;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

/**
 * FastRoute
 *
 * Cache the vendor's compiled dispatcher tables alongside CrazyPHP metadata.
 */
class FastRoute extends Adapter {

    /** Protected Methods
     ******************************************************
     */

    /**
     * Compile Routes
     *
     * {@inheritdoc}
     */
    protected function compileRoutes(array $routes):array {

        # Create the FastRoute collector with its parser and data generator
        $collector = new RouteCollector(new RouteParser(), new DataGenerator());

        # Register each translated route in collection order
        foreach($routes as $route)

            # Add the method, vendor pattern and route identifier
            $collector->addRoute($route["method"], $this->getVendorPath($route), $route["id"]);

        # Return compiled matching tables with Unicode support
        return $this->enableUnicode($collector->getData());

    }

    /**
     * Create Matcher
     *
     * {@inheritdoc}
     */
    protected function createMatcher(array $compiled):object {

        # Create the vendor dispatcher directly from compiled matching tables
        return new VendorDispatcher($compiled);

    }

    /**
     * Match Route
     *
     * {@inheritdoc}
     */
    protected function matchRoute(object $matcher, string $method, string $path):?array {

        # Match the request method and path with the vendor dispatcher
        $result = $matcher->dispatch($method, $path);

        # Return the matched identifier and parameters, or no match
        return $result[0] === Dispatcher::FOUND ? ["id" => $result[1], "parameters" => $result[2]] : null;

    }

    /**
     * Get Vendor Path
     *
     * @param array $route Translated path and parameter requirements
     * @return string FastRoute placeholder syntax
     */
    protected function getVendorPath(array $route):string {

        # Prepare placeholder substitutions for the vendor syntax
        $replacements = [];

        # Read each placeholder and its parser expression
        foreach($route["requirements"] as $key => $regex)

            # Embed the parser expression in the vendor placeholder
            $replacements["{".$key."}"] = "{".$key.":".$regex."}";

        # Return the path with vendor-specific placeholder syntax
        return strtr($route["path"], $replacements);

    }

    /**
     * Enable Unicode
     *
     * @param array $compiled FastRoute matching tables
     * @return array Tables supporting CrazyPHP's Unicode parameter expressions
     */
    protected function enableUnicode(array $compiled):array {

        # FastRoute 1.x omits the Unicode flag required by the existing [s:name] type
        foreach($compiled[1] as &$bunches)

            # Visit each compiled route batch for the current HTTP method
            foreach($bunches as &$bunch)

                # Enable Unicode matching for the compiled regular expression
                $bunch["regex"] .= "u";

        # Return the adjusted compiled matching tables
        return $compiled;

    }

}
