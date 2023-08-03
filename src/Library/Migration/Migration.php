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
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
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

        /* Load Migration Config Files */
        $this->_loadConfigFiles();

        /** Run actions */
        $this->_runActions();



    }

    /** Public methods
     ******************************************************
     */

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
     * 
     */
    public function isFrontBuildRequired():bool {

        # Set result
        $result = $this->_isFrontBuildRequired;

        # Return result
        return $result;

    }

    /** Public methods | Actions
     ******************************************************
     */

    /**
     * 
     */
    public function actionStringReplace(
        string|array $from, 
        string|array $to, 
        string|array $name = "*", 
        string|array $in,
        bool $_preview = true
    ):void {



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
     * Run Actions
     * 
     * Run all actions
     * 
     * @return void
     */
    private function _runActions():void {

        

    }

    /** Public constants
     ******************************************************
     */

    /** @const string CONFIG_PATH */
    public const CRAZY_PHP_MIGRATION_PATH = "@crazyphp_root/resources/Yml/Migration.yml";

}