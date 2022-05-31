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
use CrazyPHP\Library\Database\Create as CreateDatabase;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Form\Process;
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

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();
        $climate->br();

        # Display result
        $this->success("New project");

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

        # Break line
        $climate
            ->br()
            ->orange("Processing values given")
            ->br();

        # Progress bar
        $progress = $climate->progress()->total(100);

            # Get form result
            $formResult = $form->getResult();

                # Update progress bar
                $progress->current(33);

            # Process value
            $formResult = (new Process($formResult))->getResult();

                # Update progress bar
                $progress->current(66);

            # Validate value
            $formResult = (new Validate($formResult))->getResult();

            # Prepare display value
            $dispayValues = Validate::getResultSummary($formResult);
                
                # Update progress bar
                $progress->current(100);

        # Success message
        $climate
            ->br()
            ->green("Values processed with success 🎉")
        ;

        ## Second part
        $climate
            ->br()
            ->green()
            ->border()
            ->br();
        usleep(400000);
        $climate
            ->json($dispayValues)
            ->br();
        usleep(200000);

        $input = $climate->confirm('Do you confirm the creation of the projet ?');

        // Continue? [y/n]
        if(!$input->confirmed()){

            usleep(200000);
            $climate
                ->br()
                ->red("✋ Project creation has been canceled ✋")
                ->br()
            ;

            # Stop script
            return;

        }
        
        # Push form result in result
        $result['application'] = $formResult;

        ## Third part (database)
        usleep(400000);
        $climate
            ->br()
            ->cyan()
            ->border()
            ->br();
        usleep(300000);
        $input = $climate->confirm('Do you need a database ? 📒');

        // Continue? [y/n]
        if ($input->confirmed()) {

            usleep(300000);
        
            # Display form
            $form = new Form(CreateDatabase::REQUIRED_VALUES);

            # Break line
            $climate
                ->br()
                ->orange("Processing values given")
                ->br();

            # Progress bar
            $progress = $climate->progress()->total(100);

                # Get form result
                $formResultDb = $form->getResult();

                    # Update progress bar
                    $progress->current(33);

                # Process value
                $formResultDb = (new Process($formResultDb))->getResult();

                    # Update progress bar
                    $progress->current(66);

                # Validate value
                $formResultDb = (new Validate($formResultDb))->getResult();

                # Prepare display value
                $dispayValues = Validate::getResultSummary($formResultDb);
                    
                    # Update progress bar
                    $progress->current(100);

            # Success message
            $climate
                ->br()
                ->green("Values processed with success 🎉")
            ;

            ## Third part
            $climate
                ->br()
                ->green()
                ->border()
                ->br();
            usleep(400000);
            $climate
                ->json($dispayValues)
                ->br();
            usleep(200000);
            
            # Continue? [y/n]
            $input = $climate->confirm('Do you confirm the creation of the database ?');
            
            # Check answer
            if($input->confirmed()){
        
                # Push form result in result
                $result['database'] = $formResult;

                ## Part 4 (auth)
                usleep(400000);
                $climate
                    ->br()
                    ->magenta()
                    ->border()
                    ->br();
                usleep(300000);
                $input = $climate->confirm('Do you need authentication library ? <delight-im/PHP-Auth> 🔐');

                # Continue? [y/n]
                if ($input->confirmed())

                    # Push form result in result
                    $result['authentication'] = [
                        [
                            "name"          =>  "library_author",
                            "type"          =>  "VARCHAR",
                            "value"         =>  "delight-im",
                        ],
                        [
                            "name"          =>  "library_repository",
                            "type"          =>  "VARCHAR",
                            "value"         =>  "PHP-Auth",
                        ],
                    ];

            }else{

                usleep(200000);
                $climate
                    ->br()
                    ->yellow("🟠 Database creation has been canceled, project creation continue...")
                    ->br()
                ;

            }

        }

        ## Part 5 Create application
        
        # New app create instance
        $app = new Create($result);

        
        print_r($app->getStoryline());

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