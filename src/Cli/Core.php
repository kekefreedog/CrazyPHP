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
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Form\Process;
use splitbrain\phpcli\Options;
use League\CLImate\CLImate;
use splitbrain\phpcli\CLI;
use CrazyPHP\App\Create;
use CrazyPHP\App\Delete;
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
            "type"          =>  "command",
            "long"          =>  "version",
            "help"          =>  "Print version of CrazyPHP",
        ],
        # New Project
        [
            "type"          =>  "command",
            "long"          =>  "new",
            "help"          =>  "New crazy entity (project, page, component...)",
        ],
        # Update Project
        [
            "type"          =>  "command",
            "long"          =>  "update",
            "help"          =>  "Update crazy entity (project, page, component...)",
        ],
        # Delete Project
        [
            "type"          =>  "command",
            "long"          =>  "delete",
            "help"          =>  "Delete crazy entity (project, page, component...)",
        ],
        # Arguments
        [
            "type"          =>  "argument",
            "long"          =>  "entity",
            "help"          =>  "Entity (project, page, component...)",
            "command"       =>  ["new", "update", "delete"]
        ],
        /* [
            "type"          =>  "",
            "long"          =>  "",
            "help"          =>  "",
            "short"         =>  "",
            "argument"      =>  "",
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

            # Option
            if($option['type'] == "option")

                # Register current option
                $options->registerOption($option['long'], $option['help'], $option['short']);

            else
            # Command
            if($option['type'] == "command")

                # Register command
                $options->registerCommand($option['long'], $option['help']);

            else
            # Argument
            if($option['type'] == "argument"){

                # Check command
                if(!is_array($option['command']))

                    # Convert command to array if not array
                    $option["command"] = [$option["command"]];

                # Check command
                if(!empty($option["command"]))

                    # Iteration command
                    foreach($option['command'] as $command)

                        # Register argument
                        $options->registerArgument($option['long'], $option['help'], true, $command);

            }

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

        # Check command long
        if($options->getCmd()){

            # Get method name
            $methodName = "action".ucfirst(strtolower($options->getCmd()));

            # Check action is set
            if(method_exists($this, $methodName)){

                # Set noActionFound
                $noActionFound = false;

                # Set input
                $input = [
                    /* "opts"  =>  $options->getOpts, */
                    "args"  =>  $options->getArgs(),
                    "cmd"   =>  $options->getCmd()
                ];

                # Execute action and pass input data
                $this->{"action".$options->getCmd()}($input);

            }

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
     * @param array $inputs Collection of inputs with opts, args & cmd
     * 
     */
    protected function actionNew(array $inputs = []){

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundBlue()->out("🚀 Run ".$inputs['cmd']." ".$inputs['args'][0])->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$inputs['cmd']][$inputs['args'][0]];

        # Get class
        $class = $router["class"];

        # Get required values
        $requiredValues = $class::getRequiredValues();

        # Check required values
        if(!empty($requiredValues)){
            
            # Message
            $climate
                ->lightBlue()
                ->bold()
                ->out("👋 First we need informations about your new ".$inputs['args'][0]." 👋");
            ;
        
            # Display form
            $form = new Form($requiredValues);

            # Get form result
            $formResult = $form->getResult();

            # Process value
            $formResult = (new Process($formResult))->getResult();

            # Validate value
            $formResult = (new Validate($formResult))->getResult();

            # fill result
            $result[$router['parameter']] = $formResult;

            # Prepare display value
            $summary[$router['parameter']] = Validate::getResultSummary($formResult);
            
            # Message
            $climate
                ->br()
                ->lightBlue()
                ->bold()
                ->out("📝 Summary about the creation of your new ".$inputs['args'][0]." 📝")
                ->br()
            ;

            # Summary
            @$climate->table($summary);

        }
            
        # Message
        $input = $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->confirm('✅ Do you confirm your new '.$inputs['args'][0].' ? ✅')
        ;

        # Check action confirmed
        if (!$input->confirmed()){

            # Stop message
            $climate
                ->br()
                ->bold()
                ->red("✋ Action canceled ✋")
                ->br()
            ;

            # Stop action
            return;

        }

        # New instance of class
        $instance = new $class($result);

        # Get story line
        $storyline = $instance->getStoryline();

        # Iteration storyline
        foreach($storyline as $action){

            # Message start
            $climate
                ->br()
                ->yellow("🟠 Run ".str_replace("run", "", strtolower($action)))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".str_replace("run", "", $action)." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->out("🎉 New ".$inputs['args'][0]." created with success 🎉")
            ->br()
        ;

    }

    /** Update project
     * 
     */
    protected function actionUpdate(){

        # Display result
        $this->info("update");

    }

    /** Delete project
     * 
     */
    protected function actionDelete(){

        # New climate
        $climate = new CLImate();
        $climate->br();

        # Display result
        $this->warning("delete");

        ## First part
        usleep(400000);
        $climate
            ->br()
            ->green()
            ->border()
            ->br();
        usleep(300000);

        $input = $climate->confirm('Do you confirm the deletion of the project ?');

        // Continue? [y/n]
        if(!$input->confirmed()){

            usleep(200000);
            $climate
                ->br()
                ->red("✋ Project deletion has been canceled ✋")
                ->br()
            ;

            # Stop script
            return;

        }

        # New app delete instance
        $app = new Delete();

        # Iteration storyline
        foreach($app->getStoryline() as $action){

            # Message start
            $climate
                ->br()
                ->yellow("Run ".str_replace("run", "", strtolower($action)))
            ;

            # Execute
            $app->{$action}();

            # Message end
            $climate
                ->green(str_replace("run", "", $action)." ran with succes")
            ;

        }

    }

    /** Provate methods
     ******************************************************
     */

    /** Check in router
     * 
     * Check inputs data is in router
     * 
     * @param array $inputs Collection of data from cli
     * @return void
     */
    private function _checkInRouter(array $inputs = []):void {

        # Check inputs
        if(
            !isset($inputs['cmd']) || 
            !isset($inputs['args'][0]) ||
            !$inputs['cmd'] ||
            !$inputs['args'][0]
        )
            
            # New error
            throw new CrazyException(
                "Please fill a valid command and valid arguments", 
                500,
                [
                    "custom_code"   =>  "core-001",
                ]
            );

        # Check command given is in router
        if(!isset(self::ROUTERS[$inputs['cmd']][$inputs['args'][0]]))
            
            # New error
            throw new CrazyException(
                "Please write a valid command and valid arguments", 
                500,
                [
                    "custom_code"   =>  "core-002",
                ]
            );  

    }

    /** Public constants
     ******************************************************
     */

    /**
     * Ascii art collection
     */
    public const ASCII_ART = [
        "crazyphp"  =>  "vendor/kzarshenas/crazyphp/resources/Ascii"
    ];

    /**
     * Router Collection
     */
    public const ROUTERS = [
        # Command new
        "new"   =>  [
            # Options
            "project"   =>  [
                "class"     =>  "\CrazyPHP\App\Create",
                "parameter" =>  "application",
            ],
        ],
    ];

}