<?php declare(strict_types=1);
/**
 * Cli
 *
 * Register and dispatch the application CLI commands.
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Cli;

/**
 * Dependances
 */
use CrazyPHP\Library\Extension\Extension;
use CrazyPHP\Library\Migration\Migration;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\File\Package;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use splitbrain\phpcli\Options;
use CrazyPHP\Core\Websocket;
use League\CLImate\CLImate;
use splitbrain\phpcli\CLI;
use CrazyPHP\Cli\Form;

/**
 * Core
 *
 * Coordinate command headers, confirmations, forms and execution.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Core extends CLI {

    /** Private Parameters
     ******************************************************
     */

    /** @var string $scriptName Name of the currently executed script */
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

        # Get register options
        $registeredOptions = File::open(static::CLI_REGISTERED_PATH)["CliRegister"] ?? [];

        # Get current name of file name
        $this->scriptName = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);

        # Check if script name is in REGISTER_OPTIONS
        if(!array_key_exists($this->scriptName, $registeredOptions))

            # Exit
            exit("🔴 Current script doesn't have any options associated...");

        # Set help
        $options->setHelp(Composer::read("description"));

        # Share runtime and confirmation options across all Crazy entrypoints
        $options->registerOption("docker", "Run PHP, Composer and Node in Docker");
        $options->registerOption("yes", "Skip yes/no confirmations (forms still require answers)", "y");

        # Iteration REGISTER_OPTIONS
        foreach($registeredOptions[$this->scriptName] as $option)

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
     * Display the command header, request confirmation and execute the action.
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
                    "args" => $options->getArgs(),
                    "cmd"  => $options->getCmd(),
                    "opt"  => $options->getOpt()
                ];

                # Try
                try{

                    # Display the script's logo and title before asking for confirmation
                    $climate = new CLImate();
                    $this->_displayHeader($input, $climate);

                    # Confirm before starting the command, including frontend builds
                    Runtime::$assumeYes = (bool) $options->getOpt("yes");

                    # Stop before entering the action when confirmation is declined
                    if(!(new Runtime(
                        $climate->lightBlue()->bold()
                    ))->confirm(
                        "✅ Run ".$this->scriptName." ".$options->getCmd()."? ✅"
                    )){

                        # Display the cancellation message with the command's styling
                        $climate
                            ->br()
                            ->bold()
                            ->red("✋ Action canceled ✋")
                            ->br()
                        ;

                        # Return without running the action
                        return;

                    }

                    # Execute action and pass input data
                    $this->{$methodName}($input);

                }catch(CrazyException $e){

                    # New climate
                    $climate = new CLImate();

                    # Get message
                    $message = $e->getMessage();

                    # Return error message
                    $climate->red("🔴 $message");

                    # Propagate command failures to the caller and automation
                    exit(1);

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

    /**
     * Action Version
     *
     * Print the application version.
     *
     * @return void
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

    /**
     * New Action
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

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]];

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

            # Fill result
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
        $confirmed = (new Runtime(
            $climate->br()->lightBlue()->bold()
        ))->confirm(
            '✅ Do you confirm your new '.$inputs['args'][0].' ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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

    /**
     * Update Project
     *
     * Collect and confirm the values required by the selected update command.
     *
     * @param array $inputs Command arguments and options
     * @return void
     */
    protected function actionCrazyCommandUpdate(array $inputs = []):void {

        # Declare result
        $result = [];

        # New climate
        $climate = new CLImate();

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]];

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

            # Fill result
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
        $confirmed = (new Runtime(
            $climate->br()->lightBlue()->bold()
        ))->confirm(
            '✅ Do you confirm your new '.$inputs['args'][0].' ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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
            ->lightGreen()
            ->bold()
            ->out("🎉 ".ucfirst($inputs['args'][0])." updated with success 🎉")
            ->br()
        ;

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

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]];

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

            # Fill result
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

        # Explain the application cleanup scope before its existing confirmation
        if($inputs["args"][0] === "project")

            # Describe the files affected by project deletion
            $climate->out(
                "Everything in the project folder will be removed, including configuration, local database data (.database) and .env. Only composer.json, vendor, .git and Docker volumes will be kept."
            );

        # Message
        $confirmed = (new Runtime(
            $climate->br()->lightRed()->bold()
        ))->confirm(
            '❎ Do you confirm the deletion of your '.$inputs['args'][0].' ? ❎'
        );

        # Check action confirmed
        if(!$confirmed){

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

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']];

        # Get class
        $class = $router["class"];

        # Check Docker before asking the server and security questions
        (new $class())->runCheck();

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

            # Fill result
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

        # Validate the chosen branch and preview proxy instructions before confirmation
        $instance = new $class($result);

        # Read the database services and check the target files
        $instance->runCheck();

        # Show the concrete server and TLS strategy without database passwords
        $climate->out($instance->describe());

        # Message
        $confirmed = (new Runtime(
            $climate->br()->lightBlue()->bold()
        ))->confirm(
            '✅ Do you really want to create Docker Composer ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']];

        # Get class
        $class = $router["class"];

        # Message
        $confirmed = (new Runtime(
            $climate->br()->lightRed()->bold()
        ))->confirm(
            '❎ Remove generated Docker files and stop containers? Database/certificate volumes and .env will be kept. ❎'
        );

        # Check action confirmed
        if(!$confirmed){

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

        # New climate
        $climate = new CLImate();

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']];

        # Get function
        $function = $router["function"];

        # Message
        $confirmed = (new Runtime(
            $climate->br()->lightMagenta()->bold()
        ))->confirm(
            '✅ Do you want register asset config ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Get router
        $router = self::getRouters()[$this->scriptName][$inputs['cmd']];

        # Resolve the shared Webpack/Vite runner; it validates the script before installing anything
        $class = $router["class"];

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

    /** Protected Methods Action | For CrazyMigration
     ******************************************************
     */

    /**
     * Action Check
     *
     * Check if migration of your crazy application is required
     *
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @param bool $endMessage Display the final success message
     * @return Migration Migration instance for the run command
     */
    protected function actionCrazyMigrationCheck(array $inputs = [], bool $endMessage = true):Migration {

        # New climate
        $climate = new CLImate();

        # Check command is in router
        $this->_checkInRouter($inputs);

        # New migration instance
        $migration = new Migration();

        # Enable cli on migration instance
        $migration->enableCliMessage(
            true,
            function(array $action, bool $preview = true) use ($climate){

                # Message start
                $climate
                    ->br()
                    ->yellow("🟠 Run ".strtolower(Process::spaceBeforeCapital((($preview == true) ? "Preview " : "").($action["name"] ?? ""))))
                ;

                # Check if description
                if($action["description"] ?? false)

                    # Display the preview description
                    $climate
                        ->out('>>> ℹ️  '.$action["description"])
                    ;

            },
            function(array $action, bool $preview = true) use ($climate){

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
        $confirmed = (new Runtime(
            $climate->lightBlue()->bold()
        ))->confirm(
            '✅ Do you want run migration ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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

    /** Protected Methods Action | For CrazyWebsocket
     ******************************************************
     */

    /**
     * Action Websocket Run
     *
     * Run websocket server
     *
     * @param array $inputs Collection of inputs with opts, args & cmd
     */
    protected function actionCrazyWebsocketRun(array $inputs = []){

        # Is Yes
        $isYes = $this->_checkOpt($inputs, 'yes');

        # New climate
        $climate = new CLImate();

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Check not is yes
        if(!$isYes){

            # Message
            $confirmed = (new Runtime(
                $climate->lightBlue()->bold()
            ))->confirm(
                '✅ Do you want run websocket server ? ✅'
            );

            # Check action confirmed
            if(!$confirmed){

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
                ->out("🚀 Run websocket")->br()
            ;

        }

        # Run websocket
        (new Websocket)->run();

        # Success message
        $climate
            ->br()
            ->lightGreen()
            ->bold()
            ->out("🎉 Websocket ran with success 🎉")
            ->br()
        ;

    }

    /**
     * Action Crazy Websocket Stop
     *
     * Action for stop websocket server
     *
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyWebsocketStop(array $inputs = []):void {

        # New climate
        $climate = new CLImate();

        # Check command is in router
        $this->_checkInRouter($inputs);

        # Message
        $confirmed = (new Runtime(
            $climate->lightBlue()->bold()
        ))->confirm(
            '✅ Do you want stop websocket server ? ✅'
        );

        # Check action confirmed
        if(!$confirmed){

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
            ->out("🚀 Run stop websocket")->br()
        ;

        # Run websocket
        (new Websocket)->run();

        # Success message
        $climate
            ->br()
            ->lightGreen()
            ->bold()
            ->out("🎉 Websocket stopped with success 🎉")
            ->br()
        ;

    }

    /** Protected Methods Action | For CrazyWorkers
     ******************************************************
     */

    /**
     * Action Workers Run
     *
     * Run workers server
     *
     * @param array $inputs Collection of inputs with opts, args & cmd
     */
    protected function actionCrazyWorkersRun(array $inputs = []){

        # Is Yes
        $isYes = $this->_checkOpt($inputs, 'yes');

        # New climate
        $climate = new CLImate();

        # Check CrazyWorkers extensions is installed
        if(Extension::getInstalledByName('CrazyWorkers') === null){

            # Stop message
            $climate
                ->br()
                ->bold()
                ->red("🛑 Crazy workers is not installed 🛑")
                ->br()
            ;

        }else{

            # Check command is in router
            $this->_checkInRouter($inputs);

            # Check not is yes
            if(!$isYes){

                # Message
                $confirmed = (new Runtime(
                    $climate->lightBlue()->bold()
                ))->confirm(
                    '✅ Do you want run workers server ? ✅'
                );

                # Check action confirmed
                if(!$confirmed){

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
                    ->out("🚀 Run workers")->br()
                ;

            }

            # Run workers
            class_exists("\App\Library\CrazyWorker") && (new \App\Library\CrazyWorker())->run();

            # Success message
            $climate
                ->br()
                ->lightGreen()
                ->bold()
                ->out("🎉 Workers ran with success 🎉")
                ->br()
            ;

        }

    }

    /**
     * Action Crazy Workers Stop
     *
     * Action for stop workers server
     *
     * @param array $inputs Collection of inputs with opts, args & cmd
     * @return void
     */
    protected function actionCrazyWorkersStop(array $inputs = []):void {

        # Is Yes
        $isYes = $this->_checkOpt($inputs, 'yes');

        # New climate
        $climate = new CLImate();

        # Check CrazyWorkers extensions is installed
        if(Extension::getInstalledByName('CrazyWorkers') === null){

            # Stop message
            $climate
                ->br()
                ->bold()
                ->red("🛑 Crazy workers is not installed 🛑")
                ->br()
            ;

        }else{

            # Check command is in router
            $this->_checkInRouter($inputs);

            # Check not is yes
            if(!$isYes){

                # Message
                $confirmed = (new Runtime(
                    $climate->lightBlue()->bold()
                ))->confirm(
                    '✅ Do you want stop workers server ? ✅'
                );

                # Check action confirmed
                if(!$confirmed){

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
                    ->out("🚀 Run stop workers")->br()
                ;

            }

            # Run workers
            class_exists("\App\Library\CrazyWorker") && (new \App\Library\CrazyWorker())->stop();

            # Success message
            $climate
                ->br()
                ->lightGreen()
                ->bold()
                ->out("🎉 Workers stopped with success 🎉")
                ->br()
            ;

        }

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Display Header
     *
     * @param array $inputs Command arguments and options
     * @param CLImate $climate Terminal used by the startup confirmation
     * @return void
     */
    private function _displayHeader(array $inputs, CLImate $climate):void {

        # Read the optional entity displayed by application commands
        $entity = $inputs["args"][0] ?? "";

        # Preserve each script's existing title and background color
        [$color, $title] = match($this->scriptName." ".$inputs["cmd"]){
            "CrazyCommand new"     => ["backgroundBlue", "🚀 Run new ".$entity],
            "CrazyCommand update"  => ["backgroundGreen", "🍿 Run update ".$entity],
            "CrazyCommand delete"  => ["backgroundRed", "🚀 Run delete ".$entity],
            "CrazyDocker new"      => ["backgroundBlue", "🚀 Run new Docker"],
            "CrazyDocker delete"   => ["backgroundRed", "🚀 Run delete Docker"],
            "CrazyAsset register"  => ["backgroundBlue", "🚀 Run Register Config Asset"],
            "CrazyFront run"       => ["backgroundBlue", "🚀 Run Frontend Build"],
            "CrazyMigration check" => ["backgroundBlue", "🚀 Run check migration"],
            "CrazyMigration run"   => ["backgroundBlue", "🚀 Run migration"],
            "CrazyWebsocket run"   => ["backgroundBlue", "🚀 Run Websocket Server"],
            "CrazyWebsocket stop"  => ["backgroundOrange", "🚀 Stop Websocket Server"],
            "CrazyWorkers run"     => ["backgroundBlue", "🚀 Run Workers Server"],
            "CrazyWorkers stop"    => ["backgroundOrange", "🚀 Stop Workers Server"],
            default                => ["backgroundBlue", "🚀 Run ".$inputs["cmd"]],
        };

        # Print the header once, before any prompt or action
        $climate->addArt(self::ASCII_ART["crazyphp"]);
        $climate->draw("crazyphp");

        # Display the title before the startup confirmation
        $climate
            ->{$color}()
            ->out($title)
            ->br()
        ;

    }

    /**
     * Check in router
     *
     * Check that the command and its arguments are registered.
     *
     * @param array $inputs Collection of data from cli
     * @return void
     */
    private function _checkInRouter(array $inputs = []):void {

        # Check inputs
        if(
            isset(self::getRouters()[$this->scriptName][$inputs['cmd']]["command"]) &&
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
                    "custom_code"   =>  "core-004",
                ]
            );

        # Get routers
        $routers = self::getRouters();

        # Check command given is in router
        if(
            isset($routers[$this->scriptName][$inputs['cmd']]["command"]) &&
            !isset($routers[$this->scriptName][$inputs['cmd']]["command"][$inputs['args'][0]])
        )

            # New error
            throw new CrazyException(
                "Please write a valid command and valid arguments",
                500,
                [
                    "custom_code"   =>  "core-005",
                ]
            );

    }

    /**
     * Check Opt
     *
     * Check whether an input option matches the expected value.
     *
     * @param array $inputs Command arguments and options
     * @param string $name Option name
     * @param bool $expected Expected option value
     * @return bool
     */
    private function _checkOpt(array $inputs, string $name, bool $expected = true):bool {

        # Set result
        $result = false;

        # Check
        if(
            !empty($inputs) &&
            $name &&
            is_array($inputs["opt"] ?? false) &&
            ($inputs["opt"][$name] ?? false) == ($expected ? 1 : 0)
        )

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Routers
     *
     * Read the command routing configuration.
     *
     * @return array
     * @throws CrazyException When the routing configuration cannot be read
     */
    public static function getRouters():array {

        # Set result
        $result = [];

        # Check file exists
        if(File::exists(static::ROUTERS_PATH))

            # Get content of the file
            $result = File::open(static::ROUTERS_PATH);

        # Check result
        if(!is_array($result))

            # New error
            throw new CrazyException(
                "Can't open routers config for CLI commands, please check the file \"".str_replace("@crazyphp_root", "", static::ROUTERS_PATH)."\"",
                500,
                [
                    "custom_code"   =>  "core-006",
                ]
            );

        # Return result
        return $result;

    }

    /** Public Constants
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
    public const ROUTERS_PATH = "@crazyphp_root/resources/Yml/CliRouter.yml";

    /**
     * CLI Registered Path
     */
    public const CLI_REGISTERED_PATH = "@crazyphp_root/resources/Yml/CliRegister.yml";

}
