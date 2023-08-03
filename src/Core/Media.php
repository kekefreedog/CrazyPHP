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
namespace CrazyPHP\Core;

/**
 * Dependances
 */
use CrazyPHP\Model\Asset;

/**
 * Media
 *
 * Class for manage media...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Media extends Asset {

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Parent constructure
        parent::__construct();

        # Set context
        $this->appendContext(__CLASS__);

    }

}