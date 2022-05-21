<?php declare(strict_types=1);
/**
 * Cli
 *
 * Core of the cli
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Cli;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Composer;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\CLI;

/**
 * Core
 *
 * Methods for controle the app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Core extends CLI {

    /** Constants
     ******************************************************
     */

    # Options to register
    protected const REGISTER_OPTIONS = [
        # Version
        [
            "long"          =>  "version",
            "help"          =>  "Print version of CrazyPHP",
            "short"         =>  "v",
        ],
        /* [
            "long"          =>  "",
            "help"          =>  "",
            "short"         =>  "",
        ], */
    ];

    /** Protected Methods
     ******************************************************
     */

    /**
     * Setup CLI
     * 
     * Register options and arguments
     * 
     * @param Options $options CLI Option
     */
    protected function setup(Options $options){

        # Set help
        $options->setHelp(Composer::read("description"));

        # Iteration REGISTER_OPTIONS
        foreach(self::REGISTER_OPTIONS as $option)

            # Register current option
            $options->registerOption($option['long'], $option['help'], $option['short']);

    }

    /**
     * Implement CLI
     * 
     * Implement your code
     * 
     * @param Options $options CLI Option
     */
    protected function main(Options $options){

        # Set var
        $noActionFound = true;

        # Display help
        $options->help();

        # Iteration REGISTER_OPTIONS
        foreach(self::REGISTER_OPTIONS as $option)

            # Check option long
            if($options->getOpt($option['long'])){

                # Get method name
                $methodName = "action".ucfirst(strtolower($option['long']));

                # Check action is set
                if(method_exists($this, $methodName)){

                    # Set noActionFound
                    $noActionFound = false;

                    # Execute action
                    $this->{"action".$option['long']}();

                }

                # Break iteration
                break;

            }

        # Check if no action found
        if($noActionFound)

            # Display help
            echo $options->help();

    }

    /** Protected Methods Action
     ******************************************************
     */

    /** Action Version
     * 
     * Print the version of the app
     * 
     */
    protected function actionVersion(){

        # Declare result
        $result = "";

        # Set result
        $result .= "Version : ";

        # Get version
        $result .= Composer::read("version");

        # Display result
        $this->info($result);

    }


}