<?php declare(strict_types=1);
/**
 * New application
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\App;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Structure;

/**
 * Delete Application
 *
 * Classe for deletion application
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Delete{

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run delete of project
     *
     * @return Delete
     */
    public function run(){

        /**
         * Run Structure Folder
         * 1. Delete structure folder
         */
        $this->runStructureFolder();

    }

    /**
     * Get story line
     * 
     * Used for execute each method one after another
     * 
     * @return array
     */
    public function getStoryline():array {

        # Declare result
        $result = [];

        # New reflection
        $reflection = new \ReflectionClass($this);

        # Get methods
        $methods = $reflection->getMethods();

        # Check methods
        if($methods)

            # Iteration of methods
            foreach($methods as $method)

                # Check run children methods
                if(
                    substr($method->name, 0, 3) == "run" && 
                    strlen($method->name) > 3
                )

                    # Push result in result
                    $result[] = $method->name;

        # Return result
        return $result;

    }

    /**
     * Run Structure Folder
     * 
     * Steps : 
     * 1. Delete structure folder
     * 
     * @return Delete
     */
    public function runStructureFolder():Delete {

        # Define Root
        $root = getcwd();

        # New structure instance
        $structure = new Structure($root, Structure::DEFAULT_TEMPLATE, "delete");

        # Create new structure
        $structure->run();

        # Return instance
        return $this;

    }

}