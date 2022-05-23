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
use League\CLImate\CLImate;
use splitbrain\phpcli\CLI;
use CrazyPHP\App\Create;
use CrazyPHP\Cli\Form;

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
        # New Project
        [
            "long"          =>  "new",
            "help"          =>  "New crazy project",
            "short"         =>  "n",
        ],
        # Upgrade Project
        [
            "long"          =>  "upgrade",
            "help"          =>  "Upgrade your crazy project",
            "short"         =>  "u",
        ],
        # Delete Project
        [
            "long"          =>  "delete",
            "help"          =>  "Delete your crazy project",
            "short"         =>  "d",
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

    /** New project
     * 
     * New project
     * 
     */
    protected function actionNew(){

        # Display result
        $this->success("New project");

        # New climate
        $climate = new CLImate();

        ## First part
        usleep(400000);
        $climate
            ->br()
            ->green()
            ->border()
            ->br();
        usleep(300000);
        $climate
            ->out("First we need informations about your new project 👋");
        usleep(300000);
        
        # Display form
        $form = new Form(Create::REQUIRED_VALUES);

        # Get form result
        $formResult = $form->getResult();

        # Process value
        $formResultProcessed = new ProcessFormValues($formResult);

        ## Second part
        $climate
            ->br()
            ->green()
            ->border()
            ->br();
        usleep(400000);
        $climate
            ->json([
                'name' => 'Gary',
                'age'  => 52,
                'job'  => 'Engineer',
            ]);
          

    }

    /** Upgrade project
     * 
     */
    protected function actionUpgrade(){

        # Display result
        $this->info("upgrade");

    }

    /** Delete project
     * 
     */
    protected function actionDelete(){

        # Display result
        $this->info("delete");

    }

}