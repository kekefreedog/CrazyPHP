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
use Mezon\Router\Router as VendorRouter;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;

/**
 * Router
 *
 * Class for dispatch client request...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Router extends VendorRouter {

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructor
        parent::__construct();

    }

    /**
     * PushCollection
     * 
     * Push collection of router in current instance
     * @return void
     */
    public function pushCollection(string $collectionPath = "l"):void {

        # Check collection path
        if($collectionPath)

            # Set collection
            $collection = File::open($collectionPath);

        # Else read config router
        else

            # Set collection
            $collection = Config::get("Router");

        /* Add Pages */

        # Check router.page
        if(!isset($collection["Router"]["pages"]) && empty($collection["Router"]["pages"]))

            # Stop function
            return;

        # Parse collection
        $collectionParsed = LibraryRouter::parseCollection($collection);

        # Check collection
        if(empty($collectionParsed))

            # New Exception
            throw new CrazyException(
                "Collection of router is empty... Check your router config file.",
                500,
                [
                    "custom_code"   =>  "router-001",
                ]
            );

        # Iteration of collection
        foreach($collectionParsed as $item)

            # Check type
            if($item["type"] == "router")

                # Add router
                $this->addRoute($item["pattern"], $item["controller"], strtolower($item["method"]), $item["name"]);

    }

}