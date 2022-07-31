<?php declare(strict_types=1);
/**
 * Model
 *
 * Classe for define framework models
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Model;

/** Dependances
 * 
 */
use CrazyPHP\Library\Form\Process;

/**
 * Config
 *
 * Methods for interacting with config file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Env{

    /** Public constants
     ******************************************************
     */

    /** Public static methods
     ******************************************************
     */

    /**
     * Set
     * 
     * Set env, exemple input :
     * ```php
     * $input = [
     *  "app_root"      =>  "/sites/CrazyProject",
     *  "crazyphp_root" =>  "/sites/CrazyProject/vendor/kekefreedog/crazyphp"
     * ];
     * ```
     * 
     * @param array $input Input to process
     * 
     * @return void
     */
    public static function set(array $input = []):void {

        # Check input
        if(!empty($input))

            # Iteration input
            foreach($input as $k => $v){

                # Process key
                $k = Process::clean($k);

                # Check key
                if(!$k)

                    # Continue iteration
                    continue;

                # Add double underscores
                $k = "__".trim($k, "_")."__";

                # Define env constant
                define($k, $v);

            }

    }

}