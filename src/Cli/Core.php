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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Cli;

/**
 * Dependances
 */
use CrazyPHP\Exception\MongodbException;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\Docker;
use splitbrain\phpcli\Options;
use CrazyPHP\Model\App\Create;
use CrazyPHP\Model\App\Delete;
use League\CLImate\CLImate;
use splitbrain\phpcli\CLI;
use CrazyPHP\Cli\Form;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\Migration\Migration;

/**
 * Core
 *
 * Methods for controle the app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Core extends CLI {

    /** Constants
     ******************************************************
     */

    # Options to register
    protected const REGISTER_OPTIONS = [
        # Application
        "CrazyCommand"  =>  [
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
        ],
        # Docker
        "CrazyDocker"   =>  [
            # New
            [
                "type"          =>  "command",
                "long"          =>  "new",
                "help"          =>  "Install docker compose for your crazy project",
            ],
            # Delete
            [
                "type"          =>  "command",
                "long"          =>  "delete",
                "help"          =>  "Delete docker compose from your crazy project",
            ],
            # Up
            [
                "type"          =>  "command",
                "long"          =>  "up",
                "help"          =>  "Up docker composer",
            ],
            # Down
            [
                "type"          =>  "command",
                "long"          =>  "down",
                "help"          =>  "Shut down compose instance",
            ],
        ],
        # Asset
        "CrazyAsset"    =>  [
            # Register Config
            [
                "type"          =>  "command",
                "long"          =>  "register",
                "help"          =>  "Register config asset in your crazy application"
            ]
        ],
        # Front
        "CrazyFront"    =>  [
            # Register Config
            [
                "type"          =>  "command",
                "long"          =>  "run",
                "help"          =>  "Register config asset in your crazy application"
            ]
        ],
        # Migration
        "CrazyMigration"    =>  [
            # Check migration
            [
                "type"          =>  "command",
                "long"          =>  "check",
                "help"          =>  "Check if migration is required."
            ],
            # Run migration
            [
                "type"          =>  "command",
                "long"          =>  "run",
                "help"          =>  "Run migration of your crazy application."
            ]
        ],

    ];

    /** Arguments
     ******************************************************
     */

    /* @var string @scriptName Name of the current script executed */
    private $scriptName = "";

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

        # Get current name of file name
        $this->scriptName = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);

        # Check if script name is in REGISTER_OPTIONS
        if(!array_key_exists($this->scriptName, self::REGISTER_OPTIONS))

            # Exit
            exit("🔴 Current script doesn't have any options associated...");

        # Set help
        $options->setHelp(Composer::read("description"));

        # Iteration REGISTER_OPTIONS
        foreach(self::REGISTER_OPTIONS[$this->scriptName] as $option)

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
            $methodName = "action".ucfirst($this->scriptName).ucfirst($options->getCmd());

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

                # Try
                try{

                    # Execute action and pass input data
                    $this->{$methodName}($input);

                }catch(CrazyException $e){

                    # New climate
                    $climate = new CLImate();

                    # Return error message
                    echo $climate->red("🔴 ".$e->getMessage());

                }

            }

        }

        # Check if no action found
        if($noActionFound)

            # Display help
            echo $options->help();

    }

    /** Protected Methods Action | For CrazyCommand
     ******************************************************
     */

    /** Action Version
     * 
     * Print the version of the app
     * 
     */
    protected function actionCrazyCommandVersion(){

        # Declare result
        $result = "";

        # Set result
        $result .= "Version : ";

        # Get version
        $result .= Composer::read("version");

        # Display result
        $this->info($result);

    }

    /** New action
     * 
     * New entity action
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyCommandNew(array $inputs = []):void {

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
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]];

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
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
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
    protected function actionCrazyCommandUpdate(){

        # Display result
        $this->info("update");

    }

    /** 
     * Delete action
     * 
     * Delete entity action
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyCommandDelete(array $inputs = []):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundRed()->out("🚀 Run ".$inputs['cmd']." ".$inputs['args'][0])->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]];

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
                ->out("👋 First we need informations about deletion of your ".$inputs['args'][0]." 👋");
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
                ->out("📝 Summary about the deletion of your ".$inputs['args'][0]." 📝")
                ->br()
            ;

            # Summary
            @$climate->table($summary);

        }

        # Message
        $input = $climate
            ->br()
            ->lightRed()
            ->bold()
            ->confirm('❎ Do you confirm the deletion of your '.$inputs['args'][0].' ? ❎')
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
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightRed()
            ->bold()
            ->out("🎉 ".$inputs['args'][0]." removed with success 🎉")
            ->br()
        ;

    }

    /** Protected Methods Action | For CrazyDocker
     ******************************************************
     */

    /**
     * Action Crazy Docker New
     * 
     * ction for create new docker compose for the current app
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyDockerNew(array $inputs = []):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundBlue()->out("🚀 Run ".$inputs['cmd']." Docker")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

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
                ->out("👋 First we need informations about your new Docker Compose 👋");
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
            $result[$router['parameter'] ?? "input"] = $formResult;

            # Prepare display value
            $summary[$router['parameter'] ?? "input"] = Validate::getResultSummary($formResult);
            
            # Message
            $climate
                ->br()
                ->lightBlue()
                ->bold()
                ->out("📝 Summary about the creation of your new Docker config 📝")
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
            ->confirm('✅ Do you really want to create Docker Composer ? ✅')
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
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->out("🎉 Docker compose installed with success 🎉")
            ->br()
        ;

    }

    /**
     * Action Crazy Docker Up
     * 
     * Action for up docker compose for the current app
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @param bool $relaunch Check if the current method is the relaunch of itself...
     * @return void
     */
    protected function actionCrazyDockerUp(array $inputs = [], bool $relaunch = false):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Check if relaunch
        if(!$relaunch){

            # Add asci folder
            $climate->addArt(self::ASCII_ART["crazyphp"]);
            
            # Draw crazy php logo
            $climate->draw('crazyphp');

            # Title of current action
            $climate->backgroundBlue()->out("🚀 Run up Docker Compose")->br();

        }else{

            # Title of current action
            $climate->backgroundBlue()->out("Relaunch up Docker Compose")->br();

        }
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

        # Get class
        $class = $router["class"];

        # Check if relaunch
        if(!$relaunch){

            # Message
            $input = $climate
                ->br()
                ->lightBlue()
                ->bold()
                ->confirm('✅ Do you want run up docker compose ? ✅')
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

        }

        # New instance of class
        $instance = new $class();

        # Get story line
        $storyline = $instance->getStoryline();

        # Iteration storyline
        foreach($storyline as $action){

            # Check if runIsPortTaken and restart
            if($relaunch && $action == "runIsPortTaken") continue;

            # Message start
            $climate
                ->br()
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Try catch error
            try{

                # Execute
                $instance->{$action}();

            }catch(MongodbException $e){

                # Check code
                if($e->getCode() == 255){

                    # Message end
                    $climate
                        ->green("🟡 Relaunch ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action)))."...")
                        ->br()
                    ;

                    # Relaunch current method
                    $this->actionCrazyDockerUp($inputs, true);

                    # Stop current function
                    return;


                }else{

                    # Display message
                    $e->getMessageForTerminal();

                }

            }

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
                ->br()
            ;

        }

        # Get port
        $port = Docker::getLocalHostPort();

        # Get server name
        $servername = Config::getValue("App.server.name");

        # Check server name
        if($servername !== null && $servername)

            # Message about port
            $climate
                ->br()
                ->out("ℹ️  Open your browser and navigate to \"<bold><underline>http://$servername/</underline></black>\". If everything is working, you'll see a welcome page.")
            ;

        else
        # Check port
        if($port)

            # Message about port
            $climate
                ->br()
                ->out("ℹ️  Open your browser and navigate to \"<bold><underline>http://localhost:$port/</underline></black>\". If everything is working, you'll see a welcome page.")
            ;

        # Success message
        $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->out("🎉 Docker compose run up with success 🎉")
            ->br()
        ;

    }

    /**
     * Action Crazy Docker New
     * 
     * ction for create new docker compose for the current app
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyDockerDown(array $inputs = []):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundOrange()->out("🚀 Down Docker Compose")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

        # Get class
        $class = $router["class"];

        # Message
        $input = $climate
            ->br()
            ->lightMagenta()
            ->bold()
            ->confirm('✴️ Do you want down docker compose ? ✴️')
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
        $instance = new $class();

        # Get story line
        $storyline = $instance->getStoryline();

        # Iteration storyline
        foreach($storyline as $action){

            # Message start
            $climate
                ->br()
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightMagenta()
            ->bold()
            ->out("🎉 Docker compose down with success 🎉")
            ->br()
        ;


    }

    /** 
     * Delete action
     * 
     * Delete Docker
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyDockerDelete(array $inputs = []):void {

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundRed()->out("🚀 Run ".$inputs['cmd']." Docker")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

        # Get class
        $class = $router["class"];

        # Message
        $input = $climate
            ->br()
            ->lightRed()
            ->bold()
            ->confirm('❎ Do you confirm the deletion of Docker ? ❎')
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

        # Get class
        $class = $router["class"];

        # New instance of class
        $instance = new $class();

        # Get story line
        $storyline = $instance->getStoryline();

        # Iteration storyline
        foreach($storyline as $action){

            # Message start
            $climate
                ->br()
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightRed()
            ->bold()
            ->out("🎉 Docker removed with success 🎉")
            ->br()
        ;

    }

    /** Protected Methods Action | For CrazyAsset
     ******************************************************
     */

    /**
     * Action Crazy Asset Register
     * 
     * Action for register current asset config
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyAssetRegister(array $inputs = []):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundBlue()->out("🚀 Run Register Config Asset")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

        # Get function
        $function = $router["function"];

        # Message
        $input = $climate
            ->br()
            ->lightMagenta()
            ->bold()
            ->confirm('✅ Do you want register asset config ? ✅')
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

        # Message start
        $climate
            ->br()
            ->yellow("🟠 Run Register Config")
        ;

        # Run function
        $function();

        # Message end
        $climate
            ->green("🟢 Register Config ran with success")
        ;

        # Success message
        $climate
            ->br()
            ->lightGreen()
            ->bold()
            ->out("🎉 Asset registered with success 🎉")
            ->br()
        ;

    }

    /** Protected Methods Action | For CrazyFront
     ******************************************************
     */

    /** 
     * Action Run
     * 
     * Run script of NPM package file
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     */
    protected function actionCrazyFrontRun(array $inputs = []){

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundBlue()->out("🚀 Run Register Config Asset")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::ROUTERS[$this->scriptName][$inputs['cmd']];

        # Check script name
        if(!isset($inputs["args"][0]) || empty($inputs["args"][0]))
            
            # New error
            throw new CrazyException(
                "Command empty, please run the script with the npm script name after \"run\" option", 
                500,
                [
                    "custom_code"   =>  "core-001",
                ]
            );

        # Get script name
        $scriptName = $inputs["args"][0];

        if(!Package::hasScript($inputs["args"][0]))

            # New error
            throw new CrazyException(
                "Script given \"$scriptName\" doesn't exists in your package.json File", 
                500,
                [
                    "custom_code"   =>  "core-002",
                ]
            );

        # Get function
        $class = $router["class"];

        # Message
        $input = $climate
            ->br()
            ->lightMagenta()
            ->bold()
            ->confirm("✅ Do you want run NPM \"$scriptName\" script ? ✅")
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
        $instance = new $class($inputs);

        # Get story line
        $storyline = $instance->getStoryline();

        # Iteration storyline
        foreach($storyline as $action){

            # Message start
            $climate
                ->br()
                ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))
            ;

            # Execute
            $instance->{$action}();

            # Message end
            $climate
                ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital(str_replace("run", "", $action))))." ran with success")
            ;

        }

        # Success message
        $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->out("🎉 Front JS generated with success 🎉")
            ->br()
        ;

    }

    /** Protected Methods Action | For CrazyFront
     ******************************************************
     */

    /** 
     * Action Check
     * 
     * Check if migration of your crazy application is required
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return Migration for run / upgrade command
     */
    protected function actionCrazyMigrationCheck(array $inputs = [], bool $endMessage = true):Migration{

        # New climate
        $climate = new CLImate();

        # Add asci folder
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        
        # Draw crazy php logo
        $climate->draw('crazyphp');

        # Title of current action
        $climate->backgroundBlue()->out("🚀 Run check migration")->br();
          
        # Check command is in router
        $this->_checkInRouter($inputs);

        # New migration instance
        $migration = new Migration();

        # Enable cli on migration instance
        $migration->enableCliMessage(
            true,
            function (array $action, bool $preview = true) use ($climate) {
                # Message start
                $climate
                    ->br()
                    ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital((($preview == true) ? "Preview " : "").($action["name"] ?? ""))))
                ;
                # Check if description
                if($action["description"] ?? false)
                    # Message
                    $climate
                        ->out('>>> ℹ️  '.$action["description"])
                    ;
            },
            function (array $action, bool $preview = true) use ($climate) {
                # Message end
                $climate
                    ->green("🟢 ".ucfirst(strtolower(Process::spaceBeforeCapital((($preview == true) ? "Preview " : "").($action["name"] ?? ""))))." ran with success")
                    ->br()
                ;
            }
        );

        # Run preview 
        $migration->runPreviews();

        # Message for summary
        $climate
            ->br()
            ->lightBlue()
            ->bold()
            ->out("📝 Summary about the check migration 📝")
            ->br()
        ;

        # Get summary
        $summary = $migration->getCliSummaryForTable();

        # Check summary
        if($summary === null)

            # New error
            throw new CrazyException(
                "No migration action found",
                200,
                [
                    "custom_code"   =>  "core-003"
                ]
            );

        # Error detect
        else

            # Display summary
            $climate
                ->table($summary)
                ->br()
            ;

        # Check if front build required
        if($migration->isFrontBuildRequired())

            # Flank
            $climate
                ->bold()
                ->yellow()
                ->out('>>> <underline>Front build will be required after migration.</underline>')
                ->br()
            ;

        # Check end message
        if($endMessage)

            # Success message
            $climate
                ->lightGreen()
                ->bold()
                ->out("🎉 Migration checked with success 🎉")
                ->br()
            ;

        # Return migration
        return $migration;

    }

    /** 
     * Action Run
     * 
     * Run migration of your crazy application
     * 
     * @param array $inputs Collection of inputs with opts, args & cmd
     */
    protected function actionCrazyMigrationRun(array $inputs = []){

        # Retrieve migration from check command
        $migration = $this->actionCrazyMigrationCheck($inputs, false);

        # New climate
        $climate = new CLImate();

        # Message
        $input = $climate
            ->lightBlue()
            ->bold()
            ->confirm('✅ Do you want run migration ? ✅')
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

        # Title of current action
        $climate
            ->br()
            ->backgroundBlue()
            ->out("🚀 Run migration")->br()
        ;

        $migration->run();

        # Success message
        $climate
            ->br()
            ->lightGreen()
            ->bold()
            ->out("🎉 Migration ran with success 🎉")
            ->br()
        ;

    }

    /** Private methods
     ******************************************************
     */

    /** 
     * Check in router
     * 
     * Check inputs data is in router
     * 
     * @param array $inputs Collection of data from cli
     * @return void
     */
    private function _checkInRouter(array $inputs = []):void {

        # Check inputs
        if(
            isset(self::ROUTERS[$this->scriptName][$inputs['cmd']]["command"]) && 
            (
                !isset($inputs['cmd']) || 
                !isset($inputs['args'][0]) ||
                !$inputs['cmd'] ||
                !$inputs['args'][0]
            )
        )
            
            # New error
            throw new CrazyException(
                "Please fill a valid command and valid arguments", 
                500,
                [
                    "custom_code"   =>  "core-003",
                ]
            );

        # Check command given is in router
        if(isset(self::ROUTERS[$this->scriptName][$inputs['cmd']]["command"]) && !isset(self::ROUTERS[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]]))
            
            # New error
            throw new CrazyException(
                "Please write a valid command and valid arguments", 
                500,
                [
                    "custom_code"   =>  "core-004",
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
        "CrazyCommand"  =>  [
            # Command new
            "new"   =>  [
                # Command
                "command"   =>  [
                    # Project
                    "project"   =>  [
                        "class"     =>  "\CrazyPHP\Model\App\Create",
                        "parameter" =>  "application",
                    ],
                    # Router
                    "router"   =>  [
                        "class"     =>  "\CrazyPHP\Model\Router\Create",
                        "parameter" =>  "router",
                    ],
                    # Router
                    "routerType"   =>  [
                        "class"     =>  "\CrazyPHP\Model\RouterType\Create",
                        "parameter" =>  "router",
                    ],
                ],
            ],
            # Command delete
            "delete"   =>  [
                # Command
                "command"   =>  [
                    # Project
                    "project"   =>  [
                        "class"     =>  "\CrazyPHP\Model\App\Delete",
                    ],
                    # Router
                    "router"    =>  [
                        "class"     =>  "\CrazyPHP\Model\Router\Delete",
                        "parameter" =>  "routers",
                    ],
                    # Router
                    "routerType"   =>  [
                        "class"     =>  "\CrazyPHP\Model\RouterType\Delete",
                        "parameter" =>  "router",
                    ],
                    # Trash
                    "trash"     =>  [
                        "class"     =>  "\CrazyPHP\Model\Trash\Delete",
                    ],
                ],
            ],
        ],
        "CrazyDocker"   =>  [
            # Command new
            "new"   =>  [
                "class"     =>  "\CrazyPHP\Model\Docker\Install",
            ],
            # Command delete
            "delete"=>  [
                "class"     =>  "\CrazyPHP\Model\Docker\Delete",
            ],
            # Command run
            "up"   =>  [
                "class"     =>  "\CrazyPHP\Model\Docker\Up",
            ],
            # Command down
            "down"  =>  [
                "class"     =>  "\CrazyPHP\Model\Docker\Down",
            ]
        ],
        "CrazyAsset"    =>  [
            # Command register
            "register"  =>  [
                "function"  =>  "\CrazyPHP\Model\Asset::registerConfig",
            ]
        ],
        "CrazyFront"    =>  [
            # Command run
            "run"       =>  [
                "class"     =>  "\CrazyPHP\Model\Webpack\Run",
            ]
        ]
    ];

}