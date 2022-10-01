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
use CrazyPHP\Core\Instance;

/**
 * Core
 *
 * Interface between application and crazy php framework
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Core {

    /** Parameter
     ******************************************************
     */

    /**
     * @var Instance $instance Instance of your app
     * ->router()
     */
    public $instance = null;

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Load instances
        $instance = new Instance();

    }

}