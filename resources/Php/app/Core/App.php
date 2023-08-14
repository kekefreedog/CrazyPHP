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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace App\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Exception\ExceptionResponse;
use CrazyPHP\Core\Core;
use Exception;

 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class App extends Core {

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        try{

            /* Parent construct */
            parent::__construct();

            /**
             * - Set Env Variable of the app
             */
            $this->setEnv([
                # Write your custom env here
            ]);

            /**
             * - Run Router Preparation
             */
            $this->runRoutersPreparation();

            /**
             * - Run Middlewares Preparation
             */
            $this->runMiddlewaresPreparation();

            /**
             * - Router redirection to controller
             */
            $this->runRouterRedirection();
        
        }catch(Exception $e){

            # New exception response
            new ExceptionResponse($e);

        }

    }

}