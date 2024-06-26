<?php declare(strict_types=1);
/**
 * Manage trash
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Trash;

/**
 * Dependances
 */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\Trash;

/**
 * Delete Trash
 *
 * Remove elements in trash
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

    /** Parameters
     ******************************************************
     */

    /** @var array $inputs */
    private array $inputs = [];

    /**
     * Constructor
     * 
     * Construct current class
     * 
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Set inputs
        $this->inputs = $inputs;

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Get Required Values
     * 
     * Return required values
     * 
     * @return array
     */
    public static function getRequiredValues():array {

        # Declare result
        $result = [];

        # Return result
        return $result;

    }

    /** Public method
     ******************************************************
     */    
    
     /**
     * Run delete of project
     *
     * @return Delete
     */
    public function run():self {

        /**
         * Run Clean Trash Folder
         * - Clean trash folder
         */
        $this->runCleanTrashFolder();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */


    /**
     * Run Clean Trash Folder
     * 
     * Clean trash folder
     * 
     * @return void
     */
    public function runCleanTrashFolder():void {

        # Clear trash
        Trash::clear();

    }

}