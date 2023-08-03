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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */

/**
 * Instance
 *
 * Class where declare instances like routers...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Instance {

    /** Parameters
     ******************************************************
     */

    /* Router */
    public $router;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Iteration of list
        foreach(self::LIST as $parameter => $instance)

            # Check
            if(isset($instance['class']) && class_exists($instance['class']))

                # Create instance
                $this->{$parameter} = new $instance['class'];

    }

    /** Public constants
     ******************************************************
     */

    /* @const array LIST List of instances loaded */
    public const LIST = [
        "router"    =>  [
            "class"     =>  "CrazyPHP\Core\Router"
        ],
    ];

}