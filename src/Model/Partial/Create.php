<?php declare(strict_types=1);
/**
 * New Partial
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
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Library\Model\CrazyModel;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\String\Strings;
use CrazyPHP\Interface\CrazyCommand;
use CrazyPHP\Library\Router\Router;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Model\Env;

/**
 * Create new Router
 *
 * Classe for create step by step new router
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Create extends CrazyModel implements CrazyCommand {

    /** Public constants
     ******************************************************
     */

    public const REQUIRED_VALUES = [
        # Name
        [
            "name"          =>  "name",
            "description"   =>  "Name of your crazy template partial",
            "type"          =>  "VARCHAR",
            "default"       =>  "partial",
            "required"      =>  true,
            "process"       =>  ['cleanPath', 'snakeToCamel', 'ucfirst', 'trim']
        ],
    ];

    /** Private Parameters
     ******************************************************
     */

    /**
     * Inputs
     */
    private $inputs = [];

    /**
     * Logs
     */
    private $logs = true;

    /** @var string $file */
    private string $file = "";

    /** @var array $partial */
    private $partial = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return Create
     */
    public function __construct(array $inputs = []){

        # Ingest data
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

        # Set result
        $result = self::REQUIRED_VALUES;

        # Return result
        return $result;

    }

    /** Public methods
     ******************************************************
     */
    
    /**
     * Run creation of project
     *
     * @return Create
     */
    public function run():self {

        /**
         * Run Prepare Partial
         * - Check partial not already exists
         */
        $this->runPreparePartial();


        /**
         * Run Create Script Partial
         * - Create the ts script file
         */
        $this->runCreateScriptPartial();


        /**
         * Run Create Style File
         * - Create the scss style file
         */
        $this->runCreateStylePartial();


        /**
         * Run Create Template Partial
         * - Create the hbs template
         */
        $this->runCreateTemplatePartial();

        /**
         * Run Create Controler File
         * - Append script into front index
         */
        $this->runScriptIntoIndex();

        /**
         * Run Style Into Index
         * - Append style into style index
         */
        $this->runStyleIntoIndex();

        # Return this
        return $this;

    }

    /** Public methods | Run
     ******************************************************
     */

    /**
     * Run Prepare Router
     * 
     * Process input to router object
     * 1. Check partial not already exists
     * 
     * @return void
     */
    public function runPreparePartial():void {

        # Process inputs
        $this->partial = Process::getResultSummary($this->inputs["partial"]);

        # Clean router name
        $partialName = $this->partial["Name"] = Process::snakeToCamel(str_replace(["/", "."], "_", $this->partial["Name"]), true);

        # Check file already exists
        if(File::exists("@app_root/app/Environment/Partials/$partialName.ts"))
            
            # New error
            throw new CrazyException(
                "Given name \"$partialName\" already exists as partial",
                500,
                [
                    "custom_code"   =>  "create-partial-001",
                ]
            );

        # Set file
        $this->file = Process::camelToPath($this->partial["Name"], true, "_");

        # Set up env for cache driver
        Env::set([
            "cache_driver"  =>  "Files"
        ]);

    }

    /**
     * Run Create Index File
     * 
     * Create the ts script file
     * 
     * @return void
     */
    public function runCreateScriptPartial():void {

        # Create template instance
        $template = new Handlebars([
            "template"  =>  Handlebars::PERFORMANCE_PRESET,
            "helpers"   =>  false
        ]);

        # Load template
        $template->load("@crazyphp_root/resources/Hbs/App/Partial/Partial.ts.hbs");

        # Render template with current partial value
        $result = $template->render($this->partial + [
            "file"  =>  $this->file
        ]);

        # Set file path
        $filePath = static::PARTIAL_SCRIPT_DIR.$this->partial["Name"].".ts";

        # Create file
        File::create($filePath, $result);

    }

    /**
     * Run Create Style Partial
     * 
     * Create the scss style file
     * 
     * @return void
     */
    public function runCreateStylePartial():void {

        # Create template instance
        $template = new Handlebars([
            "template"      =>  Handlebars::PERFORMANCE_PRESET,
            "helpers"       =>  false
        ]);

        # Load template
        $template->load("@crazyphp_root/resources/Hbs/App/Partial/Partial.scss.hbs");

        # Render template with current router value
        $result = $template->render($this->partial + [
            "file"  =>  $this->file
        ]);

        # Set file path
        $filePath = static::PARTIAL_STYLE_DIR."_".$this->file.".scss";

        # Create file
        File::create($filePath, $result);

    }

    /**
     * Run Create Template
     * 
     * Create the hbs template
     * 
     * @return void
     */
    public function runCreateTemplatePartial():void {

        # Create template instance
        $template = new Handlebars([
            "template"      =>  Handlebars::PERFORMANCE_PRESET,
            "helpers"       =>  false,
        ]);

        # Load template
        $template->load("@crazyphp_root/resources/Hbs/App/Partial/Partial.hbs.hbs");

        # Render template with current router value
        $result = $template->render($this->partial + [
            "file"  =>  $this->file
        ]);

        # Set file path
        $filePath = static::PARTIAL_TEMPLATE_DIR.$this->file.".hbs";

        # Iteration "[[" and "]]" to "{{" and "}}"
        foreach(["[[" => "{{", "]]" => "}}"] as $prev => $current) 
        
            # Switch
            $result = str_replace($prev, $current, $result);

        # Create file
        File::create($filePath, $result);

    }

    /**
     * Run Script Into Index
     * 
     * Append script into front index
     * 
     * @return void
     */
    public function runScriptIntoIndex(): void {

        if (!File::exists(static::FRONT_TS_FILE)) return;
    
        # Get content
        $contents = File::read("@app_root/app/Front/index.ts");
    
        # Set nmae
        $name = $this->partial["Name"];

        # Set new import
        $newImport = "import $name from \"../Environment/Partials/$name\";";
    
        /** STEP 1: Handle imports between Crazy Global Partials and Public Constants **/
    
        # Define import block pattern between Crazy Global Partials and globalPartials declaration
        $pattern = '/(\/\*\*\s*Crazy Global Partials[\s\S]+?\*\/)([\s\S]*?)(?=\nlet\s+globalPartials\s*=)/';

        # Check regex
        if(preg_match($pattern, $contents, $matches)) {

            # The doc comment header
            $sectionHeader = $matches[1];

            # The import block right after it
            $existingImports = $matches[2];

            // Get all import lines from this block only
            preg_match_all('/^import\s+\w+\s+from\s+"[^"]+";/m', $existingImports, $importMatches);

            # Clean imports
            $imports = array_unique($importMatches[0]);

            # Set name
            $name = $this->partial["Name"];

            # Prepare new import
            $newImport = "import $name from \"../Environment/Partials/$name\";";

            # Add new import
            if(!in_array($newImport, $imports)) $imports[] = $newImport;

            # Sort import lines by descending length
            usort($imports, fn($a, $b) => strlen($b) <=> strlen($a));

            # Reconstruct import block
            $newImportBlock = $sectionHeader . "\n" . implode("\n", $imports) . "\n";

            # Replace old section with new one
            $contents = preg_replace($pattern, $newImportBlock, $contents);

        }
    
        /** STEP 2: Handle globalPartials object **/
    
        # Check second regex
        if(preg_match('/let globalPartials\s*=\s*\{([\s\S]+?)\};/', $contents, $partialsMatch)) {
    
            # Set global partial content
            $globalPartialsContent = $partialsMatch[1];
    
            # Regex all
            preg_match_all('/"(.+?)":\s*(\w+),?/', $globalPartialsContent, $partialMatches, PREG_SET_ORDER);
    
            # Prepare partials
            $partials = [];

            # Push partials matches
            if(!empty($partialMatches)) foreach($partialMatches as $match) $partials[$match[1]] = $match[2];
    
            # Set partials
            $partials[$this->file] = $name;
    
            # Sort by key length descending
            uksort($partials, fn($a, $b) => strlen($b) <=> strlen($a));
    
            # New partial content
            $newPartialsContent = "let globalPartials = {\n";

            # Iteration partial
            foreach ($partials as $key => $val){

                # Push new partial
                $newPartialsContent .= "   \"$key\": $val,\n";

            }

            # Trim new partial content
            $newPartialsContent = rtrim($newPartialsContent, ",\n") . "\n};";
    
            # Update content
            $contents = preg_replace('/let globalPartials\s*=\s*\{[\s\S]+?\};/', $newPartialsContent, $contents);

        }
    
        # Save updated content
        file_put_contents(File::path(static::FRONT_TS_FILE), $contents);
        
    }
    

    /**
     * Run Router In Config
     * 
     * Integrate Router into config
     * 
     * @return void
     */
    public function runStyleIntoIndex():void {

        /* # Change key case
        $router = Arrays::changeKeyCaseRecursively($this->router);

        # Set type
        $type = $router["type"];

        # Remove type from router
        unset($router["type"]);

        # Get router collection count
        $routers = FileConfig::getValue("Router.".$type);

        # Count routers
        $routersKey = count($routers ?: []);

        # Set value in config
        FileConfig::setValue("Router.$type.$routersKey", $router); */

    }

    /** Public Constants
     ******************************************************
     */

    /** @var string PARTIAL_SCRIPT_DIR */
    public const PARTIAL_SCRIPT_DIR = "@app_root/app/Environment/Partials/";

    /** @var string PARTIAL_STYLE_DIR */
    public const PARTIAL_STYLE_DIR = "@app_root/app/Front/style/scss/partial/";

    /** @var string PARTIAL_TEMPLATE_DIR */
    public const PARTIAL_TEMPLATE_DIR = "@app_root/assets/Hbs/partials/";

    /** @var string FRONT_TS_FILE */
    public const FRONT_TS_FILE = "@app_root/app/Front/index.ts";

}