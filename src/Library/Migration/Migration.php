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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Migration;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;

/**
 * Migration
 *
 * Class for manage migration between different versions of crazy php
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param bool $process Process migration or just preview
     * @return self
     */
    public function __construct(bool $process = false){

        # Set preview
        $this->_preview = !$process;

        # Load Migration Config Files
        $this->_loadConfigFiles();

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
                foreach($action["_fromPreview"] as $preview)

                    # Run action
                    call_user_func_array(self::class."::$runMethodName", $preview["parameters"] ?? []);

            }

        }

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
                $previewResult = call_user_func_array(self::class."::$previewMethodName", $actionParameters);

            }

            # Push result in action
            $action["_fromPreview"] = $previewResult;

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
     * 
     */
    public function isFrontBuildRequired():bool {

        # Set result
        $result = $this->_isFrontBuildRequired;

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
     * @param string|array $name = "*" 
     * @param string|array $in
     * @param bool $_preview = true
     * @return array
     */
    public static function previewStringReplace(
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
            if(is_string($name) && $name)

                # Push to in clean
                $nameClean[] = $nameCurrent;

        # Check name clean
        if(!empty($nameClean))

            # Set name
            $finder->name($inClean);

        ## Contains

        # Set contains
        $finder->contains($from);

        ## Result

        # Set result
        $result = [];

        # Check has result
        if($finder->hasResults())

            # Iteration file
            foreach($finder as $file)

                # Push value to result
                $result[] = [
                    "description"   =>  "Will replace \"$from\" by \"$to\"",
                    "parameters"    =>  [
                        "file"          =>  $file->getRealPath(),
                        "search"        =>  $from,
                        "replace"       =>  $to
                    ],
                ];

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
     */
    public static function runStringReplace(
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
            file_put_contents($file, $fileContent);

        }

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

    /** Public constants
     ******************************************************
     */

    /** @const string CONFIG_PATH */
    public const CRAZY_PHP_MIGRATION_PATH = "@crazyphp_root/resources/Yml/Migration.yml";

}