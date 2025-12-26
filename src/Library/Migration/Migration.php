<?php declare(strict_types=1);
/**
 * Migration
 *
 * Classes utilities for migration
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Migration;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Trash;
use CrazyPHP\Library\File\File;
use League\CLImate\CLImate;
use CrazyPHP\Model\Env;

/**
 * Migration
 *
 * Class for manage migration between different versions of crazy php
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Migration {

    /** Private parameters
     ******************************************************
     */

    /** @param bool $preview Preview mode */
    private bool $_preview;

    /** @param array $preview Check if preview has been ran */
    private bool $_previewRan = false;

    /** @param array $_actions List of actions */
    private array $_actions = [];

    /** @param bool $_isFrontBuildRequired */
    private bool $_isFrontBuildRequired = false;

    /** Private parameters | Cli
     ******************************************************
     */

    /** @param bool $_cliMessage */
    private bool $_cliMessage = false;

    /** @param array $_cliMessageSummary */
    private array $_cliMessageSummary = [];
    private array $_cliMessageSummaryTemp = [];

    /** @param array $_cliMessageCallable */
    private ?array $_cliMessageCallable = [
        "before"    =>  null,
        "after"     =>  null
    ];

    /** Private parameters | Trash
     ******************************************************
     */

    /** @param bool $_useTrash */
    private bool $_useTrash = true;

    /** @param string $_useTrash */
    private string $_trashSubFolder = "";

    /** @param array $_trashSummary */
    private array $_trashSummary = [];

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param bool $process Just instance the class or run all process
     * @return self
     */
    public function __construct(bool $process = false){

        # Set preview
        $this->_preview = !$process;

        # Load Migration Config Files
        $this->_loadConfigFiles();

        # Prepare trash
        $this->_prepareTrash();

        # Check process
        if($process){

            # Run preview
            $this->runPreviews();

            # Run actions
            $this->run();

        }

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Run
     * 
     * Run all actions
     * 
     * @param bool $previewRequired
     * @return void
     */
    public function run(bool $previewRequired = false):void {

        # Check previewRequired
        if(!$this->_previewRan)

            # New error
            throw new CrazyException(
                "You must run preview method before run actions of migration.",
                500,
                [
                    "custom_code"   =>  "migration-001"
                ]
            );

        # Iteration of actions
        foreach($this->_actions as &$action){

            # Check cli message
            if($this->_cliMessage && is_callable($this->_cliMessageCallable["before"] ?? false))

                # Message start
                $this->_cliMessageCallable["before"]($action, false);

            # Check _fromPreview
            if(
                !isset($action["_fromPreview"]) || 
                $action["_fromPreview"] === null ||
                !$action["_fromPreview"] || 
                empty($action["_fromPreview"])
            ){

                # Continue iteration
                continue;

            }

            # Check action
            if(!isset($action["action"]) || !isset($action["action"]["type"]))

                # Continue iteration
                continue;

            # Preview method
            $runMethodName = $this->_getMethodName($action["action"]["type"], "run");

            # Check method exists
            if(method_exists($this, $runMethodName)){

                # Iteration from preview
                foreach($action["_fromPreview"] as $preview){

                    # Message
                    $this->_cliMessageRun($preview);

                    # Check if file is in parameter
                    if($preview["parameters"]["file"] ?? false)

                        # Copy file to trash
                        $this->_sendToTrash($preview["parameters"]["file"]);

                    # Run action
                    $this->$runMethodName(...($preview["parameters"] ?? []));

                }

            }

            # Check cli message
            if($this->_cliMessage && is_callable($this->_cliMessageCallable["after"] ?? false))

                # Message end
                $this->_cliMessageCallable["after"]($action, false);

        }

        # Summary in trash
        $this->_summaryInTrash();

    }

    /**
     * Run Actions
     * 
     * Run all actions
     * 
     * @param bool $overwrite exisiting preview result
     * @return void
     */
    public function runPreviews(bool $overwrite = true):void {

        # Iteration of actions
        foreach($this->_actions as &$action){

            # Check cli message
            if($this->_cliMessage && is_callable($this->_cliMessageCallable["before"] ?? false))

                # Message start
                $this->_cliMessageCallable["before"]($action);

            # Check overwrite
            if($overwrite && isset($action["_fromPreview"]))

                # Continue
                continue;

            # Check action
            if(!isset($action["action"]) || !isset($action["action"]["type"]))

                # Continue iteration
                continue;

            # Preview method
            $previewMethodName = $this->_getMethodName($action["action"]["type"]);

            # Check method exists
            if(method_exists($this, $previewMethodName)){

                # Copy action
                $actionParameters = $action["action"];

                # Check if type
                if(isset($actionParameters["type"]))

                    # remove it
                    unset($actionParameters["type"]);

                # Run preview method
                $previewResult = $this->$previewMethodName(...$actionParameters);

            }

            # Push result in action
            $action["_fromPreview"] = $previewResult;

            # Check cli message
            if($this->_cliMessage && is_callable($this->_cliMessageCallable["after"] ?? false)){

                # Message end
                $this->_cliMessageCallable["after"]($action);

            }

            # Check summary temp
            $this->_cliMessageFillSummary($action["name"] ?? "");

        }

        # Switch 
        $this->_previewRan = true;

    }

    /**
     * Reset previews
     * 
     * Reset preview results on all actions
     * 
     * @return void
     */
    public function resetPreviews():void {

        # Iteration of actions
        foreach($this->_actions as &$action)

            # Check if isset $action["_fromPreview"]
            if(isset($action["_fromPreview"]))

                # Unset the parameter
                unset($action["_fromPreview"]);

        # Swith false the _previewRan parameter
        $this->_previewRan = false;

    }

    /**
     * Get Actions
     * 
     * Get All actions
     * 
     * @return array
     */
    public function getActions():array {

        # Set result
        $result = $this->_actions;

        # Return result
        return $result;

    }

    /**
     * Get Action By Name
     * 
     * Return action using name parameter
     * 
     * @param string actionName Name of the action
     * @return array|null
     */
    public function getActionByName(string $actionName):array|null {

        # Set result
        $result = null;

        # Check action name
        if(!$actionName)

            # Return result
            return $result;

        # Search in action
        $founded = Arrays::filterByKey($this->_actions, "name", $actionName);

        # Check founded
        if(!empty($founded))

            # Set result
            $result = $founded[array_key_last($founded)];

        # Return result
        return $result;

    }

    /**
     * Is Front Build Required
     * 
     * Check if front build required after execution of migration
     */
    public function isFrontBuildRequired():bool {

        # Set result
        $result = $this->_isFrontBuildRequired;

        # Return result
        return $result;

    }

    /**
     * Enable Cli Message
     * 
     * Enable message during execution
     * 
     * @param bool $enable Enable of disable cli message
     * @param callable|null $callBefore to execute before each message
     * @param callable|null $callAfter to execute after each message
     * @return void
     */
    public function enableCliMessage(bool $enable = true, callable|null $callBefore = null, callable|null $callAfter = null):void {

        # Check enable
        if($enable){

            # Check callBefore
            if($callBefore !== null && is_callable($callBefore))

                # Set in 
                $this->_cliMessageCallable["before"] = $callBefore;

            # Check callBefore
            if($callAfter !== null &&  is_callable($callAfter))

                # Set in 
                $this->_cliMessageCallable["after"] = $callAfter;

            # Set cli message
            $this->_cliMessage = true;

        # Check if disable
        }else

            # Set cli message
            $this->_cliMessage = false;

    }

    /**
     * Get Cli Summary For Table
     * 
     * Return summary formated for climate table
     * 
     * @return array|null
     */
    public function getCliSummaryForTable():array|null {

        # Set result
        $result = empty($this->_cliMessageSummary) ? null : $this->_cliMessageSummary;

        # Return result
        return $result;

    }

    /** Public methods | Preview
     ******************************************************
     */

    /**
     * Previw String Replace
     * 
     * Search file where we have to run a string replace
     * 
     * @param string $from 
     * @param string $to 
     * @param string|array $name = "*" Name of the file, can be regex
     * @param string|array $in
     * @param bool $_preview = true
     * @return array|null
     */
    public function previewStringReplace(
        string $from,
        string $to,
        string|array $in,
        string|array $name = "*"
    ):array|null {

        # Set result
        $result = null;

        # Check in exists
        if($in == "" || empty($in) || $from == "")

            # Stop function
            return $result;

        # New finder
        $finder = new Finder();

        ## In

        # Check in is array
        if(!is_array($in))

            # Convert in to array
            $in = [$in];

        # Set in clean
        $inClean = [];
        
        # Iteration of in
        foreach($in as $inCurrent)

            # Check in
            if(is_string($inCurrent) && $inCurrent && File::exists($inCurrent))

                # Push to in clean
                $inClean[] = File::path($inCurrent);

        # Check in
        if(empty($inClean))

            # Stop function
            return $result;

        # Set in
        $finder->in($inClean);

        ## Exclude

        # Set folder to exclude
        $foldersToExclude = [];

        # Check ignore folders
        if(!empty(self::FOLDERS_TO_IGNORE))

            # Iteration of folders
            foreach(self::FOLDERS_TO_IGNORE as $_folderIgnorePath)

                # Add to exclude
                $foldersToExclude[] = File::path($_folderIgnorePath);

        # Push exclude to finder
        $finder->exclude($foldersToExclude);

        ## Files
        $finder->files();

        ## Name

        # Check in is array
        if(!is_array($name))

            # Convert in to array
            $name = [$name];

        # Set in clean
        $nameClean = [];
        
        # Iteration of in
        foreach($name as $nameCurrent)

            # Check  name
            if(is_string($nameCurrent) && $nameCurrent)

                # Push to in clean
                $nameClean[] = $nameCurrent;

        # Check name clean
        if(!empty($nameClean))

            # Set name
            $finder->name($nameClean);

        ## Contains

        # Set contains
        $finder->contains($from);

        ## Result

        # Set result
        $result = [];

        # Check has result
        if($finder->hasResults())

            # Iteration file
            foreach($finder as $file){

                # Push value to result
                $temp = [
                    "description"   =>  "Will replace \"$from\" by \"$to\"",
                    "parameters"    =>  [
                        "file"          =>  $file->getRealPath(),
                        "search"        =>  $from,
                        "replace"       =>  $to
                    ],
                ];

                # Cli message
                $this->_cliMessagePreview($temp);
               

                # Push to result
                $result[] = $temp;

            }

        # Return result
        return $result;

    }

    /**
     * Preview Add Line
     * 
     * Check if some files are missing line
     * 
     * @param string $add Lines to add
     * @param string|array $in Which folder 
     * @param string|array $name = "*" Name of the file (can be regex)
     * @param string $position = "end",   # Top or end   
     * @return array|null
     */
    public function previewAddLine(
        string $add,
        string|array $in,
        string $position = "end",
        string|array $name = "*"
    ):array|null {

        # Set result
        $result = null;

        # Check in exists
        if($in == "" || empty($in) || $add == "" || !in_array($position, ["start", "end"]))

            # Stop function
            return $result;

        # New finder
        $finder = new Finder();

        ## In

        # Check in is array
        if(!is_array($in))

            # Convert in to array
            $in = [$in];

        # Set in clean
        $inClean = [];
        
        # Iteration of in
        foreach($in as $inCurrent)

            # Check in
            if(is_string($inCurrent) && $inCurrent && File::exists($inCurrent))

                # Push to in clean
                $inClean[] = File::path($inCurrent);

        # Check in
        if(empty($inClean))

            # Stop function
            return $result;

        # Set in
        $finder->in($inClean);

        ## Exclude

        # Set folder to exclude
        $foldersToExclude = [];

        # Check ignore folders
        if(!empty(self::FOLDERS_TO_IGNORE))

            # Iteration of folders
            foreach(self::FOLDERS_TO_IGNORE as $_folderIgnorePath)

                # Add to exclude
                $foldersToExclude[] = File::path($_folderIgnorePath);

        # Push exclude to finder
        $finder->exclude($foldersToExclude);

        ## Files
        $finder->files();

        ## Name

        # Check in is array
        if(!is_array($name))

            # Convert in to array
            $name = [$name];

        # Set in clean
        $nameClean = [];
        
        # Iteration of in
        foreach($name as $nameCurrent)

            # Check  name
            if(is_string($nameCurrent) && $nameCurrent){

                # Push to in clean
                $nameClean[] = $nameCurrent;

                ## Check if .gitignore
                if(strpos($nameCurrent, ".") !== false){}

                    # Disable ignore .gitignore on the finder instance
                    $finder->ignoreDotFiles(false);

            }

        # Check name clean
        if(!empty($nameClean))

            # Set name
            $finder->name($nameClean);

        ## Contains

        # Set contains
        $finder->notContains($add);

        ## Result

        # Set result
        $result = [];

        # Check has result
        if($finder->hasResults())

            # Iteration file
            foreach($finder as $file){

                # Push value to result
                $temp = [
                    "description"   =>  "Will add new line \"".preg_replace('~[\r\n]~', '<br>', $add)."\" at the \"$position\"",
                    "parameters"    =>  [
                        "file"          =>  $file->getRealPath(),
                        "add"           =>  $add,
                        "position"      =>  $position
                    ],
                ];

                # Cli message
                $this->_cliMessagePreview($temp);

                # Push to result
                $result[] = $temp;

            }

        # Return result
        return $result;

    }

    /**
     * Preview Reduce Path
     * 
     * Check if some files need to have a reduce path
     *
     * @param array $in Key with folder path, app with the attribute
     * @param string|array $env Env to use in redice path function (by default app_root)
     * @return array|null
     */
    public function previewReducePath(
        array $in = [],
        string|array $env = ""
    ):array|null {

        # Set result
        $result = null;

        # Global check
        if(!$env || empty($env) || empty($in))

            # Return result
            return $result;

        # Check en is array
        if(!is_array($env))

            # Convert to array
            $env = [$env];

        # Set clean env
        $envClean = [];

        # Backup env
        $envBackup = [];

        # Iteration of env
        foreach($env as $currentEnv)

            # Check env exists
            if($currentEnv && Env::has($currentEnv)){

                # Push in env clean
                $envClean[] = Env::get($currentEnv);

                # Push to env backup
                $envBackup[] = $currentEnv;

            }

        # Iteration of in
        foreach($in as $filePath => $key)

            # Check if file exists
            if(File::exists($filePath)){

                # Check key is array
                if(!is_array($key))

                    # Convert to array
                    $key = [$key];

                # Check key
                if(!empty($key))

                    # Iteration des keys
                    foreach($key as $currentKey)

                        # Check key
                        if($currentKey && File::hasKey($filePath, $currentKey)){

                            # Get value
                            $value = File::getKey($filePath, $currentKey);

                            # Set continue
                            $continue = true;

                            # Check env is in value
                            foreach($envClean as $envTemp)

                                # Set env
                                if(strpos($value, $envTemp) !== false)

                                    # Set continue
                                    $continue = false;

                            # Check continue
                            if($continue)

                                # Continue iteration
                                continue;

                            # Push value to result
                            $temp = [
                                "description"   =>  "Will reduce path of \"$value\" from key \"$currentKey\"",
                                "parameters"    =>  [
                                    "file"          =>  File::resolve($filePath),
                                    "key"           =>  $currentKey,
                                    "env"           =>  count($envBackup) <= 1
                                        ? ( $envBackup[0] ?? "" )
                                        : $envBackup
                                    ,
                                    "value"         =>  $value
                                ],
                            ];

                            # Cli message
                            $this->_cliMessagePreview($temp);

                            # Push to result
                            $result[] = $temp;

                        }

            }


        # Return result
        return $result;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run String Replace
     * 
     * Run string replacement on specific file
     * 
     * @param string $file
     * @param string $search
     * @param string $replace
     * @return bool
     */
    public function runStringReplace(
        string $file,
        string $search,
        string $replace
    ):bool {

        # Ser result
        $result = false;

        # check file and search
        if($file && File::exists($file) && $search){

            # Open file
            $fileContent = file_get_contents($file);

            # Check search if regex
            if(Validate::isRegex($search))

                # New content
                $fileContent = preg_replace($search, $replace, $fileContent);

            # If search not regex
            else

                # Replace content
                $fileContent = str_replace($search, $replace, $fileContent);

            # Replace content
            $result = file_put_contents($file, $fileContent) ? true : false;

        }

        # Return result
        return $result;

    }

    /**
     * Run Add Line
     * 
     * Run add line on file
     * 
     * @param string $file
     * @param string $add
     * @param string $position
     * @return bool
     */
    public function runAddLine(
        string $file,
        string $add,
        string $position
    ):bool {

        # Set result
        $result = false;

        # Check file exists
        if(File::exists($file) && in_array($position, ["start", "end"])){

            # Open file
            $fileContent = file_get_contents($file);

            # Check position
            if($position == "start")

                # Push add in file content
                $fileContent = $add.PHP_EOL.$fileContent;

            else

                # Push add in end of file content
                $fileContent = $fileContent.PHP_EOL.$add;

            # Push to file
            $result = file_put_contents($file, $fileContent) ? true : false;

        }

        # Return result
        return $result;

    }

    /**
     * Run Reduce Path
     * 
     * Run reduce absolute path to relative path
     * 
     * @param string $file
     * @param string $key
     * @param string|array $env
     * @param string $value
     * @return bool
     */
    public function runReducePath(
        string $file,
        string $key,
        string|array $env,
        string $value
    ):bool {

        # Set result
        $result = true;

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Load Config Files
     * 
     * Load Crazy PHP migrations actions and app migrations
     * 
     * @return void
     */
    private function _loadConfigFiles():void {

        # Check CRAZY_PHP_MIGRATION_PATH exits
        if(File::exists(self::CRAZY_PHP_MIGRATION_PATH)){

            # Open file
            $content = File::open(self::CRAZY_PHP_MIGRATION_PATH);

            # Check actions are sets
            if(!isset($content["Migration"]["actions"]))

                # New error
                throw new CrazyException("Actions is missing in your Crazy php migrations config file.", 500);

            # Iterations of actions
            foreach($content["Migration"]["actions"] as $action)

                # Check action is array
                if(is_array($action)){

                    # Add source in action
                    $action["_source"] = "CRAZY_PHP_MIGRATION_PATH";

                    # Push action in _action
                    $this->_actions[] = $action;

                    # Check if frontBuildRequired
                    if(Process::bool($action["frontBuildRequired"] ?? false) === true)

                        # Set _isFrontBuildRequired
                        $this->_isFrontBuildRequired = true;

                }

        }

    }

    /**
     * Get Method Name
     * 
     * Return method name from action type to preview or run
     * 
     * @param string $actionType
     * @param string $prefix
     * @return string
     */
    private function _getMethodName(string $actionType, string $prefix = "preview"):string {

        # Set result
        $result = "";

        # Check action type
        if(!$actionType)

            # Stop function
            return $result;

        # Set result
        $result = strtolower($prefix).Process::snakeToCamel($actionType, true);

        # Return result
        return $result;

    }

    /** Private methods | Cli
     ******************************************************
     */

    /**
     * Cli Message Run
     * 
     * Display message for preview method
     * 
     * @param array $temp
     * @return void
     */
    private function _cliMessageRun(array $temp = []):void {

        # Check cli message is enable
        if($this->_cliMessage){

            $message =  
                "<blue>[✓] Action run</blue>".
                (
                    ($temp["description"] ?? false) 
                        ? " : ".$temp["description"]
                        : ""
                ).
                (
                    ($temp["parameters"]["file"] ?? false)
                        ? " in \"".str_replace(
                            File::path("@app_root"),
                            "",
                            str_replace(
                                File::path("@crazyphp_root"),
                                "",
                                $temp["parameters"]["file"]
                            )
                        )."\""
                        : ""
                )
            ;

            # Display message
            (new CLImate())->out($message);

            # Push message in summary temp
            $this->_cliMessageSummaryTemp[] = str_replace(
                [
                    "[✓] ",
                    "<blue>Action run</blue> : ",
                    "<blue>Action run</blue> "
                ],
                "",
                $message
            );

        }

    }

    /**
     * Cli Message Preview
     * 
     * Display message for preview method
     * 
     * @param array $temp
     * @return void
     */
    private function _cliMessagePreview(array $temp = []):void {

        # Check cli message is enable
        if($this->_cliMessage){

            $message =  
                "<blue>[-] New action found</blue>".
                (
                    ($temp["description"] ?? false) 
                        ? " : ".$temp["description"]
                        : ""
                ).
                (
                    ($temp["parameters"]["file"] ?? false)
                        ? " in \"".str_replace(
                            File::path("@app_root"),
                            "",
                            str_replace(
                                File::path("@crazyphp_root"),
                                "",
                                $temp["parameters"]["file"]
                            )
                        )."\""
                        : ""
                )
            ;

            # Display message
            (new CLImate())->out($message);

            # Push message in summary temp
            $this->_cliMessageSummaryTemp[] = str_replace(
                [
                    "[-] ",
                    "<blue>New action found</blue> : ",
                    "<blue>New action found</blue> "
                ],
                "",
                $message
            );

        }

    }

    /**
     * Cli Message Fill Summary
     * 
     * Spread summary temp in summary with action name
     * > Will be use for cli table to get summary
     * 
     * @param string $actionName
     * @return void
     */
    private function _cliMessageFillSummary(string $actionName):void {

        # Check cli message is enable
        if($this->_cliMessage && !empty($this->_cliMessageSummaryTemp))

            # Iteration _cliMessageSummaryTemp
            foreach($this->_cliMessageSummaryTemp as $summary)

                # Push value in summary
                $this->_cliMessageSummary[] = [
                    "Action"        =>  $actionName ? ucfirst(strtolower(Process::spaceBeforeCapital($actionName))) : "Migration Action",
                    "Description"   =>  $summary
                ];

        # Clean _cliMessageSummary
        $this->_cliMessageSummaryTemp = [];

    }

    /** Private methods | Trash
     ******************************************************
     */

    /**
     * Prepare Trash
     * 
     * Prepare trash
     * > To use in constructor
     * 
     * @return void
     */
    private function _prepareTrash():void {

        # Check if use trash is enable
        if($this->_useTrash)

            # Set subfolder trash name
            $this->_trashSubFolder = "migration/".(new DateTime())->format('Uu');

    }

    /**
     * Send To Trash
     * 
     * Send file to trash
     * 
     * @param string filePath
     * @return void
     */
    private function _sendToTrash(string $filePath):void {

        # Check if use trash is enable
        if($this->_useTrash){

            # Sent file to trash
            $trashPath = Trash::send($filePath, $this->_trashSubFolder, false);

            # Push trash to summary
            $this->_trashSummary[] = [
                "before"    =>  $trashPath,
                "now"       =>  File::pathReverse($filePath, ["app_root", "crazyphp_root"])
            ];

        }

    }
    
    /**
     * Summary In Trash
     * 
     * Send summury to the trash
     * 
     * @return void
     */
    private function _summaryInTrash():void {

        # Check trash is use
        if($this->_useTrash && !empty($this->_trashSummary))

            # Send summary in trash
            Trash::sendAnObject(
                [
                    "date"          =>  explode("/", $this->_trashSubFolder)[1],
                    "filesMigrated" =>  $this->_trashSummary
                ],
                "migration",
                $this->_trashSubFolder
            );

    }

    /**
     * Get Last Trash Summary
     * 
     * Return array of the last trash summary
     * 
     * @return void
     */


    /** Public constants
     ******************************************************
     */

    /** @const string CONFIG_PATH */
    public const CRAZY_PHP_MIGRATION_PATH = "@crazyphp_root/resources/Yml/Migration.yml";

    /** Private constants
     ******************************************************
     */

    /** @var const FOLDERS_TO_IGNORE */
    private const FOLDERS_TO_IGNORE = [
        "vendor", 
        "node_modules"
    ];

}