<?php declare(strict_types=1);
/**
 * App
 *
 * Workflow of your app
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace App\Core;

/**
 * Dependances
 */
use App\Core\Kernel;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class App extends Kernel {

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        /* Parent construct */
        parent::__construct();

        /* Messahe */
        echo "Your app is working !";

    }

}