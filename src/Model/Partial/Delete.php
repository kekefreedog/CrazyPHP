<?php declare(strict_types=1);
/**
 * Model Partial
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Model\Partial;

/**
 * Dependances
 */
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\Trash;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Partial;

/**
 * Delete Router
 *
 * Classe for deletion of router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Delete extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Type
        [
            "name"          =>  "partials",
            "description"   =>  "Partials to delete",
            "type"          =>  "ARRAY",
            "required"      =>  true,
            "multiple"      =>  true,
            "select"        =>  "CrazyPHP\Library\File\Partial::getSummary"
        ],
    ];

    /** Parameters
     ******************************************************
     */

    /** @var array $inputs */
    private array $inputs = [];

    /** @var array $partials */
    private array $partials = [];

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
        $result = self::REQUIRED_VALUES;

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
         * Run Retrieve Router
         * - Process input to retrieve object
         */
        $this->runRetrieveRouter();
    
        /**
         * Run Remove Script From Index
         * - Remove Script From Index
         */
        $this->runRemoveScriptFromIndex();
    
        /**
         * Run Remove Style From Index
         * - Remove Style From Index
         */
        $this->runRemoveStyleFromIndex();
    
        /**
         * Run Delete Script File
         * - Delete the ts script file
         */
        $this->runDeleteScriptFile();
    
        /**
         * Run Delete Style File
         * - Delete the scss style file
         */
        $this->runDeleteStyleFile();
    
        /**
         * Run Delete Template File
         * - Selete the hbs template file
         */
        $this->runDeleteTemplateFile();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Retrieve Router
     * 
     * Process input to retrieve object
     * 
     * @return void
     */
    public function runRetrieveRouter():void {

        # Get routers to delete
        $partials = $this->inputs["partial"][0]["value"];

        # Check routers
        if(empty($partials))

            # New error
            throw new CrazyException(
                "No partial selected.",
                500,
                [
                    "custom_code"   =>  "create-partial-001",
                ]
            );

        # Iteration of routers
        foreach($partials as $partial){

            # Push in inputs
            $this->partials[] = Partial::get($partial);

        }

    }

    /**
     * Run Remove Script From Index
     * 
     * Remove Script From Index
     * 
     * @return void
     */
    public function runRemoveScriptFromIndex():void {

        # Iteration routers
        if(File::exists(Partial::FRONT_TS_FILE) && !empty($this->partials)){
    
            # Get content
            $contents = File::read(Partial::FRONT_TS_FILE);
    
            # Explode content to lines
            $lines = explode("\n", $contents);

            # Set new to delete
            $linesToDelete = [];
            
            if(!empty($lines)) foreach($this->partials as $partial) foreach($lines as $i => $line){

                # Check lines includes name or file : name
                if(
                    # Check if import line
                    (
                        strpos($line, "import ".$partial["name"]." from \"") !== false &&
                        strpos($line, "/".$partial["name"]."\";") !== false
                    ) ||
                    # Check if declaration
                    (
                        strpos($line, "\"".$partial["file"]."\": ".$partial["name"]) !== false
                    )
                ){

                    # Remove line from array
                    $linesToDelete[] = $i;

                }

            }

            # Check if lines to delete
            if(!empty($linesToDelete)){

                # Iteration line to delete
                foreach(array_reverse($linesToDelete) as $i) if(array_key_exists($i, $lines)) {

                    # Delete line
                    unset($lines[$i]);

                }
        
                # Write back
                file_put_contents(File::path(Partial::FRONT_TS_FILE), implode("\n", $lines));

            }

        }

    }

    /**
     * Run Remove Style From Index
     * 
     * Remove Style From Index
     * 
     * @return void
     */
    public function runRemoveStyleFromIndex():void {

        # Iteration routers
        if(File::exists(Partial::FRONT_SCSS_FILE) && !empty($this->partials)){
    
            # Get content
            $contents = File::read(Partial::FRONT_SCSS_FILE);
    
            # Explode content to lines
            $lines = explode("\n", $contents);

            # Set new to delete
            $linesToDelete = [];
            
            if(!empty($lines)) foreach($this->partials as $partial) foreach($lines as $i => $line){

                # Check lines includes name or file : name
                if(
                    strpos($line, "@import '") !== false &&
                    strpos($line, "/".$partial["file"]."';") !== false
                ){

                    # Remove line from array
                    $linesToDelete[] = $i;

                }

            }

            # Check if lines to delete
            if(!empty($linesToDelete)){

                # Iteration line to delete
                foreach(array_reverse($linesToDelete) as $i) if(array_key_exists($i, $lines)) {

                    # Delete line
                    unset($lines[$i]);

                }
        
                # Write back
                file_put_contents(File::path(Partial::FRONT_SCSS_FILE), implode("\n", $lines));

            }

        }

    }

    /**
     * Run Delete Script File
     * 
     * Delete the ts script file
     * 
     * @return void
     */
    public function runDeleteScriptFile():void {

        # Iteration routers
        foreach($this->partials as $partial) if($partial["script"] ?? false && File::exists($partial["script"])){

            # Send script to trash
            Trash::send(
                $partial["script"], 
                "partial/".$partial["name"]
            );

        }

    }

    /**
     * Run Delete Style File
     * 
     * Delete the scss style file
     * 
     * @return void
     */
    public function runDeleteStyleFile():void {

        # Iteration routers
        foreach($this->partials as $partial) if($partial["style"] ?? false && File::exists($partial["style"])){

            # Send script to trash
            Trash::send(
                $partial["style"], 
                "partial/".$partial["name"]
            );

        }

    }

    /**
     * Run Delete Template File
     * 
     * Delete the hbs template file
     * 
     * @return void
     */
    public function runDeleteTemplateFile():void {

        # Iteration routers
        foreach($this->partials as $partial) if($partial["template"] ?? false && File::exists($partial["template"])){

            # Send script to trash
            Trash::send(
                $partial["template"], 
                "partial/".$partial["name"]
            );

        }

    }

}