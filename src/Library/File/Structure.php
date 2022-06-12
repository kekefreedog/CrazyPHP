<?php declare(strict_types=1);
/**
 * File
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Yaml\Yaml;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\File;
;
/**
 * Structure
 *
 * Methods for generate structure folder tree
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Structure{

    /** Variables
     ******************************************************
     */

    /**
     * Root where create files
     */
    private $root = "";

    /**
     * Template to use for app creation
     */
    private $template = "";

    /**
     * Structure array
     */
    private $structure = [];

    /**
     * Action
     */
    private $action = "";

    /**
     * Template to use for app creation
     */
    private $conditions = [
        "fileTypeAccepted"  =>  [
            "text/yaml"         =>  "_parseYaml",
            "application/json"  =>  "_parseJson",
            "text/php"          =>  "_parsePhp",
        ],
    ];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param string $root Root where create files
     * @param array $template Template to use for app creation
     */
    public function __construct(string $action = "create", string $root = "", string $template = self::DEFAULT_TEMPLATE){

        # Set action
        $this->setAction($action);

        # Set root
        $this->setRoot($root);

        # Set template
        $this->setTemplate($template);

    }

    /** Public method
     ******************************************************
     */

    /**
     * setRoot
     * 
     * Set root path
     * 
     * @param string $root Root where create files
     * @return void
     */
    public function setRoot(string $root = ""):void {

        # Check root is valid
        if(!$root || !is_dir($root))

            # New Exception
            throw new CrazyException(
                "Root directory \”$root\” doesn't exists...",
                500,
                [
                    "custom_code"   =>  "structure-001",
                ]
            );

        # Check admin right
        if(!is_writable($root))

            # New Exception
            throw new CrazyException(
                "Root directory \”$root\” isn't writable. Please check folder permission...",
                403,
                [
                    "custom_code"   =>  "structure-002",
                ]
            );
        
        # Set root
        $this->root = $root;


    }

    /**
     * getRoot
     * 
     * Get root path
     * 
     * @return string
     */
    public function getRoot():string {

        # Return root
        return $this->root;

    }

    /**
     * setTemplate
     * 
     * Set Template path
     * - {{root}} will be replace by root path
     * 
     * @param array $template Template to use for app creation
     * @return void
     */
    public function setTemplate(string $template = ""):void {

        # Check if variable root
        if(strpos($template, "{{root}}") !== false)

            # Replace root in template string
            $template = str_replace("{{root}}", $this->root, $template);

        # Check template is valid
        if(!$template || !file_exists($template))

            # New Exception
            throw new CrazyException(
                "Template \”$template\” doesn't exists...",
                500,
                [
                    "custom_code"   =>  "structure-003",
                ]
            );

        # Set template
        $this->template = $template;

    }

    /**
     * getTemplate
     * 
     * Get Template path
     * 
     * @return string
     */
    public function getTemplate():string {

        # Return root
        return $this->template;

    }    

    /**
     * getAction
     * 
     * Get root path
     * 
     * @return string
     */
    public function getAction():string {

        # Return root
        return $this->action;

    }
    
    /**
     * Run
     * 
     * Run creation of structure folder
     * 
     * @return void
     */
    public function run():void {
        
        # Get type of current file
        $type = File::getMime($this->root);

        # Check file type is accepted
        if(!in_array($type, $this->conditions['fileTypeAccepted']))

            # New Exception
            throw new CrazyException(
                "Template Mime type \”$type\” is unknown...",
                500,
                [
                    "custom_code"           =>  "structure-004",
                    "new_feature_request"   =>  true
                ]
            );

        # Check method exists
        $this->{$this->conditions['fileTypeAccepted'][$type]}();

        # Get structure to create
        $this->treeFolderGenerator($this->structure, $this->root, $this->action);

    }

    /** Public static method
     ******************************************************
     */

    /**
     * Creates directories based on the array given
     * 
     * @source https://gist.github.com/timw4mail/4172083
     *
     * @param array $structure
     * @param string $path
     * @param string $action 'create', 'update' or 'delete'
     * @return void
     */
    public function treeFolderGenerator($folders = [], $path = '/', $action = 'create'){

        # Check path has / at the end
        $path = rtrim($path, '/').'/';

        # Check arguments
        if(
            !in_array($action, ['create', 'update', 'delete'])
        )
            return false;

        # Iteration of folders
        foreach($folders as $folderName => $folderContent)

            # Create folder of the root folder if not exist
            if(in_array($action, ['create', 'update'])){
    
                # check path exist
                if(!is_dir($path.$folderName))
    
                    # Create current folder
                    mkdir(
                        $path.$folderName, 
                        $folderContent['permission'] ?? 0777,
                        true
                    );

                # Check files
                if(
                    isset($folderContent['files']) &&
                    is_array($folderContent['files']) &&
                    !empty($folderContent['files'])
                )

                    # Iteration des files
                    foreach ($folderContent['files'] as $filename => $fileContent) {

                        # Get path of the current file
                        $filepath = rtrim($path, '/').'/'.rtrim($folderName, '/').'/'.$filename;
                        
                        # Check source
                        if(
                            isset($fileContent['source']) && 
                            $fileContent['source'] !== null &&
                            $fileContent['source']
                        ){

                            if(file_exists($fileContent['source'])){

                                $filepathsource = $fileContent['source'];

                            }elseif(file_exists('vendor/kekefreedog/luckyphp'.$fileContent['source'])){

                                $filepathsource = 'vendor/kekefreedog/luckyphp'.$fileContent['source'];

                            }else{

                                continue;

                            }

                            # Check copy
                            if(!copy($filepathsource, $filepath)){

                                # Erreur de copy

                            }

                        }elseif(
                            isset($fileContent['function']['name']) && 
                            $fileContent['function']['name'] && 
                            method_exists($this, $fileContent['function']['name'])
                        ){

                            # Get new content
                            $newContent = $this->{$fileContent['function']['name']}(...(array_merge([$filepath], ($fileContent['function']['arguments'] ?? []))));

                            # Put new content in file
                            file_put_contents($filepath, $newContent);

                        }else{

                            # Check file no exist
                            if(!file_exists($filepath))

                                # Create empty file
                                file_put_contents($filepath, '');

                        }

                    }

                # Check if subfolders
                if(
                    isset($folderContent['folders']) &&
                    is_array($folderContent['folders']) &&
                    !empty($folderContent['folders'])
                )

                    # Call function
                    self::treeFolderGenerator($folderContent['folders'], $path.$folderName, $action);

            # Action delete
            }elseif($action == 'delete')

                # Check path is not root "/"
                if($path.$folderName !== "/")

                    # Delete folder
                    unlink($path.$folderName);
        
    }

    /** Private method
     ******************************************************
     */

    private function _parseYaml():void {

        # Declare results
        $result = [];

        # Ingest Yaml
        $parsed = Yaml::parseFile($this->template);

        # Set result
        $result['/'] = $parsed['Structure']['@root'] ?? null;

        # Check result
        if($result['/'] === null)

            # New Exception
            throw new CrazyException(
                "Yaml template file \”$this->template\” is not valid...",
                500,
                [
                    "custom_code"   =>  "structure-005",
                ]
            );

        # Check of difference
        if($this->structure !== $result)
            
            # Set structure
            $this->structure = $result;
    }

    private function _parseJson():void {

        # Declare results
        $result = [];

        # Ingest Json
        $parsed = Json::open($this->template);

        # Set result
        $result['/'] = $parsed['Structure']['@root'] ?? null;

        # Check result
        if($result['/'] === null)

            # New Exception
            throw new CrazyException(
                "Json template file \”$this->template\” is not valid...",
                500,
                [
                    "custom_code"   =>  "structure-006",
                ]
            );

        # Check of difference
        if($this->structure !== $result)
            
            # Set structure
            $this->structure = $result;

    }

    private function _parsePhp():void {

        # Declare results
        $result = [];

        # Ingest php
        $parsed = (array) require $this->template;

        # Set result
        $result['/'] = $parsed['Structure']['@root'] ?? null;

        # Check result
        if($result['/'] === null)

            # New Exception
            throw new CrazyException(
                "Php file \”$this->template\” is not valid...",
                500,
                [
                    "custom_code"   =>  "structure-007",
                ]
            );

        # Check of difference
        if($this->structure !== $result)
            
            # Set structure
            $this->structure = $result;

    }

    /** Constants
     ******************************************************
     */

    /**
     * Template to use for app creation
     */
    public const DEFAULT_TEMPLATE = "{{root}}/vendor/kzarshenas/crazyphp/resources/Yml/Structure.yml";

}