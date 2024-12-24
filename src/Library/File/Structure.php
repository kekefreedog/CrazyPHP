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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\String\Url;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\Yaml;
use CrazyPHP\Library\File\File;

/**
 * Structure
 *
 * Methods for generate structure folder tree
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
        "actionsAccepted"   =>  [
            'create', 
            'update', 
            'delete',
        ],
    ];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param string $root Root where create files
     * @param string $template Template to use for app creation
     * @param string $action Action of the strucure
     *  - action
     *  - update
     *  - delete
     * 
     */
    public function __construct(string $root = "", string $template = self::DEFAULT_TEMPLATE, string $action = "create"){

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
     * @param string $template Template to use for app creation
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
     * setAction
     * 
     * Set action of the current structure :
     *  - action
     *  - update
     *  - delete
     * 
     * @param string $action Action of the current structure
     * @return void
     */
    public function setAction(string $action = "create"):void {

        $action = strtolower($action);
        
        # Check action 
        if(!$action || !in_array($action, $this->conditions['actionsAccepted']))

            # New Exception
            throw new CrazyException(
                "Action given \”$action\” isn't valid...",
                500,
                [
                    "custom_code"   =>  "structure-008",
                ]
            );

        # Set action
        $this->action = $action;

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
     * getStructure
     * 
     * Get structure
     * 
     * @return array
     */
    public function getStructure():array {

        # Return root
        return $this->structure;

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
        $type = File::guessMime($this->template);

        # Check file type is accepted
        if(!array_key_exists($type, $this->conditions['fileTypeAccepted']))

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
     * @deprecated
     *
     * @param array $structure
     * @param string $path
     * @param string $action 'create', 'update' or 'delete'
     * @return void
     */
    public static function treeFolderGenerator($folders = [], $path = '/', $action = 'create'){

        # Check path has / at the end
        $path = rtrim($path, '/').'/';

        # Check of delete action
        if($action == "delete"){

            # Delete folders and files
            self::treeFolderDeletion($folders, $path);

            # Stop function
            return;

        }

        # Iteration of folders
        foreach($folders as $folderName => $folderContent){

                # Set currentPath
                $currentPath = $path.$folderName;

                # Clean current path
                $currentPath = rtrim($currentPath, '/').'/';

            # Create folder of the root folder if not exist
            if(in_array($action, ['create', 'update'])){
    
                # check path exist
                if(!is_dir($currentPath))
    
                    # Create current folder
                    mkdir(
                        $currentPath, 
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

                        }/* elseif(
                            isset($fileContent['function']['name']) && 
                            $fileContent['function']['name'] && 
                            method_exists($this, $fileContent['function']['name'])
                        ){

                            # Get new content
                            $newContent = $this->{$fileContent['function']['name']}(...(array_merge([$filepath], ($fileContent['function']['arguments'] ?? []))));

                            # Put new content in file
                            file_put_contents($filepath, $newContent);

                        } */else{

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
                    self::treeFolderGenerator($folderContent['folders'], $currentPath, $action);

            }
                
        }
        
    }    
    
    /**
    * Delete directories based on the array given
    * 
    * @deprecated
    * 
    * @source https://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
    *
    * @param array $structure
    * @param string $path
    * @param bool $deleteFirst Private params
    * @return void
    */
   public static function treeFolderDeletion($folders = [], $path = '/', bool $deleteFirst = false){

        # Iteration folders
        foreach($folders as $folderName => $folderContent){

            # Set currentPath
            $currentPath = $path.$folderName;

            # Clean current path
            $currentPath = rtrim($currentPath, '/').'/';

            # Check if file
            if(!is_dir($currentPath)) 
                continue;

            # Check if subfolders
            if(
                isset($folderContent['folders']) &&
                is_array($folderContent['folders']) &&
                !empty($folderContent['folders'])
            ){

                # Delete content of folders
                self::treeFolderDeletion($folderContent['folders'], $currentPath, true);

            }

            # If not first level
            if($deleteFirst){

                # Check hidden files
                $notDeclared = scandir($currentPath);

                # Check not declared
                if(!empty($notDeclared))

                    # Iteration des items
                    foreach($notDeclared as $item){

                        # Check not dot
                        if(in_array($item, ['.', ".."]))

                            # Continue iteration
                            continue;

                        # if is file
                        if(is_file("$currentPath/$item"))

                            # Fill $folderContent['files']
                            $folderContent['files'][$item] = null;

                        # If is folder
                        if(is_dir("$currentPath/$item"))

                            # Delete content of folders
                            self::treeFolderDeletion([$item => []], "$currentPath", true);

                }

            }

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

                    # Check current file exists
                    if(!$filepath || !file_exists($filepath))
                        continue;

                    # Remove current file
                    unlink($filepath);

            }

            # Check if current folder have to be deleted
            if($deleteFirst && !empty(scandir($currentPath))){

                # Remove dir
                rmdir($currentPath);

            }

        }

   }

    /**
     * Creates directories array for preview and check based on the array given
     * 
     * @deprecated
     * 
     * @source https://gist.github.com/timw4mail/4172083
     *
     * @param array $structure
     * @param string $path
     * @return array
     */
    public static function treeFolderGeneratorPreview($folders = [], $action = 'create'):array {

        # Declare result
        $result = [];

        # Iteration of folders
        foreach($folders as $folderName => $folderContent)

            # Create folder of the root folder if not exist
            if(in_array($action, ['create', 'update'])){

                # Push folder in result
                $result[$folderName] = null;

                # Check files
                if(
                    isset($folderContent['files']) &&
                    is_array($folderContent['files']) &&
                    !empty($folderContent['files'])
                )

                    # Iteration des files
                    foreach ($folderContent['files'] as $filename => $fileContent)

                        # Push folder in result
                        $result[$folderName][$filename] = null;

                # Check if subfolders
                if(
                    isset($folderContent['folders']) &&
                    is_array($folderContent['folders']) &&
                    !empty($folderContent['folders'])
                ){

                    # Call function
                    $temp = self::treeFolderGeneratorPreview($folderContent['folders'], $action);

                    # Check temp is array
                    if(is_array($temp) && !empty($temp)){

                        # Transform $result
                        if($result[$folderName] === null)

                            # Transform to array
                            $result[$folderName] = [];

                        # Merge current result
                        $result[$folderName] += $temp;

                    }

                }

            # Action delete
            }/* elseif($action == 'delete')

                # Check path is not root "/"
                if($path.$folderName !== "/")

                    # Delete folder
                    unlink($path.$folderName); */

            # Return result
            return empty($result) ?
                false :
                    $result;
        
    }    
    
    /**
     * Get File Tree Simple
     * 
     * Get a simple file tree of folders and files contains inside given path of structure array :
     * [
     *      "folder1"   =>  null,
     *      "folder2"   =>  [
     *          "folder3"   =>  null,
     *          "filee.yml" =>  null,
     *      ]
     * ]
     * 
     * @param string|array $input Parameter to read
     * @return array|false
     */
    public static function getFileTreeSimple(string|array $input = ""):array|false {

        # Declare result
        $result = false;

        # Check if input is valid
        if(empty($input))

            # Return result
            return $result;

        /**
         * If input is path
         */
        if(is_string($input) && is_dir($input)){

            function _loop(string $path = ""):array|null {

                # Declare result
                $result = null;

                # New finder
                $finder = new Finder();

                # Prepare finder
                $finder->in($path)->ignoreDotFiles(false)->depth("== 0");

                # Check finder find something
                if($finder->hasResults())

                    # Iteration 
                    foreach($finder as $item){

                        # Check item is file
                        if($item->isFile())

                            # Push item in result
                            $result[$item->getFilename()] = null;

                        elseif($item->isDir()){

                            # Get subfolders
                            $temp = _loop($item->getRealPath());

                            # Check result 
                            if(!$temp || empty($temp))

                                # Update result
                                $result[$item->getFilename()] = null;

                            else

                                # Push temps in result
                                $result[$item->getFilename()] = $temp;

                        }

                    }

                # Return result
                return $result;

            }

            $result = _loop($input);

        }else
        /**
         * If input is array
         */
        if(is_array($input)){

            # Get tree folder
            $result = Structure::treeFolderGeneratorPreview($input);

            # Check if root is defined
            if($result["/"] ?? false)

                # Update result
                $result = array_pop($result);

        }

        # Return result
        return $result;

    }

    /** Public static method - versions 2
     ******************************************************
     */

    /**
     * Create
     * 
     * Create structure
     * 
     * @param string|array $schema Path of the schema or schema itself
     * @param array $data Data to use for compilate
     * @param bool $preview Preview will return array create but create nor folder
     * @return array
     */
    public static function create(string|array $schema = "", array $data = [], bool $preview = false):array {

        # Declare result
        $result = [];

        # Get schema
        $collection = self::_getSchema($schema);

        # check collection has structure parameter
        if(empty($collection) || !array_key_exists("Structure", $collection))

            # New Exception
            throw new CrazyException(
                "Schema content isn't valid...",
                500,
                [
                    "custom_code"   =>  "structure-012",
                ]
            );

        # Function for folder
        $folder = function($folder, $path, $preview){

            # Check preview
            if($preview)

                # Stop function
                return;

            # Check folder is set
            if(!File::exists($path))

                # Create folder
                mkdir(
                    $path,
                    # $folder['permission'] ?? static::DEFAULT_PERMISSION,
                    0777,
                    true
                );

            # Check permission
            if($folder['permission'] ?? false)

                # Changer permission
                chmod($path, 0777/* $folder['permission'] */);

        };

        # Function for file
        $file = function($file, $path, $preview) use ($data) {

            # Check preview
            if($preview)

                # Stop function
                return;

            # Check if source
            if($file['source'] ?? false){

                # Get reel path
                $file['source'] = File::path($file['source']);

                # Check if engine is valid
                if(
                    ( $file['engine'] ?? false ) &&
                    class_exists($file['engine'])
                ){

                    # New engine instance
                    $engineInstance = new $file['engine']();

                    # Load file
                    $engineInstance->load($file['source']);

                    # Render content of file and set content variable
                    $content = $engineInstance->render($data);

                }else
                # Juste read content of the file

                    # Get content of file
                    $content = File::read($file['source']);

                # Create or update file
                file_put_contents(
                    $path,
                    $content
                );

                # Check permission
                if($folder['permission'] ?? false)
    
                    # Changer permission
                    chmod($path, $file['permission']);

            }

            # Check if source
            if($file['link'] ?? false){

                # Check link is valid and exists
                if($file['link'] && File::exists($file['link'])){

                    # Create symlink
                    symlink(realpath(File::path($file['link'])), $path);

                }

            }

        };

        # Execute loop
        $result = static::_loopInsideSchema(
            ["folders" => $collection["Structure"]],
            $folder,
            $file,
            $preview
        );

        # Return result
        return $result;

    }
    
    /**
    * Check
    * 
    * Check structure has been correctly check
    * 
    * @param string|array $schema Path of the schema or schema itself
    * @return array
    */
    public static function check(string|array $schema = ""):bool {

        # Set result
        $result = true;

        # Get schema
        $collection = self::_getSchema($schema);

        # check collection has structure parameter
        if(empty($collection) || !array_key_exists("Structure", $collection))

            # New Exception
            throw new CrazyException(
                "Schema content isn't valid...",
                500,
                [
                    "custom_code"   =>  "structure-013",
                ]
            );

        # Function for folder
        $folder = function($folder, $path, $preview) use (&$result){

            # Unset unsused arguments
            unset($folder, $preview);

            # Check folder exists
            if(!File::exists($path)){

                /* print_r($path); */

                # Set result false
                $result = false;

            }

        };

        # Function for file
        $file = function($file, $path, $preview) use (&$result){
            
            # Unset unsused arguments
            unset($file, $preview);

            # Check folder exists
            if(!File::exists($path)){

                /* print_r($path); */

                # Set result false
                $result = false;

            }

        };

        # Execute loop
        static::_loopInsideSchema(
            ["folders" => $collection["Structure"]],
            $folder,
            $file,
            true
        );

        # Return result
        return $result;

    }
    
    /**
    * remove
    * 
    * Remove structure
    * 
    * @param string|array $schema Path of the schema or schema itself
    * @return array
    */
    public static function remove(string|array $schema = ""):void {

        # Get schema
        $collection = self::_getSchema($schema);

        # check collection has structure parameter
        if(empty($collection) || !array_key_exists("Structure", $collection))

            # New Exception
            throw new CrazyException(
                "Schema content isn't valid...",
                500,
                [
                    "custom_code"   =>  "structure-013",
                ]
            );

        # Get root
        $root = File::path(array_key_first($collection["Structure"]))."/";

        # Function for folder
        $folder = function($folder, $path, $preview) use ($root){

            # Unset unused arguments
            unset($folder);

            # Check preview
            if($preview)

                # Stop function
                return;

            # Check folder exists
            if(File::exists($path)){

                # Set path collection
                $pathCollection = [$path];

                # Decompose path
                $pathCollection += Url::decompose($path);

                # Sort by length descending
                usort($pathCollection, function($a, $b) {
                    return strlen($b) <=> strlen($a);
                });

                # Remove all path outside root
                $pathCollection = array_filter(
                    $pathCollection, 
                        function($v) use ($root) {
                            return strpos($v, $root) !== false && $v != $root;
                        }
                );

                # Iteration of path collection
                foreach($pathCollection as $v){
                        
                    # Check if current folder is empty
                    if(File::isEmpty($v)){

                        # Remove current folder
                        rmdir($v);

                    }

                }

            }

        };

        # Function for file
        $file = function($file, $path, $preview) use ($folder){

            # Unset unused arguments
            unset($file);

            # Check preview
            if($preview)

                # Stop function
                return;

            # Check folder exists
            if(File::exists($path)){

                # Remove file
                unlink($path);

                # Get folder path of the file
                $folderPath = implode("/", explode("/", $path, -1));

                # Try to remove folder
                $folder([], $folderPath, $preview);

            }

        };

        # Execute loop
        static::_loopInsideSchema(
            ["folders" => $collection["Structure"]],
            $folder,
            $file,
            false
        );

        /* Recreate first folder if deleted */

        # Get path
        $path = File::path(array_key_first($collection['Structure']));

        # Check if is dir
        if(!is_dir($path))

            # Create it
            mkdir($path, 0777);

    }

    /** Private static method - version 2
     ******************************************************
     */

    /**
     * Get Schema
     * 
     * Get schema path and parse it
     * 
     * @param string|array $schema path or collection
     * @return array
     */
    private static function _getSchema(string|array $schema = ""):array {

        # Check schema
        if(!$schema || empty($schema))

            # New Exception
            throw new CrazyException(
                "Schema given isn't valid...",
                500,
                [
                    "custom_code"   =>  "structure-009",
                ]
            );

        # Check if is array
        if(is_array($schema))

            # Set result
            $result = $schema;

        else{

            # Get clean path
            $path = File::path($schema);

            # Check path is defined
            if(!File::exists($path))

                # New Exception
                throw new CrazyException(
                    "Path of the schema given isn't valid...",
                    500,
                    [
                        "custom_code"   =>  "structure-010",
                    ]
                );

            # Guess mimetype of file
            $mimeType = File::guessMime($path);

            # Check if yaml
            if($mimeType == "text/yaml"){

                # Read yaml file
                $result = Yaml::open($path);

            }else
            # Check if json
            if($mimeType == "application/json"){

                # Read json file
                $result = Json::open($path);

            }else
            # Check if php
            if($mimeType == "text/php"){

                # Read php file
                $result = require $path;

            }else

                # New Exception
                throw new CrazyException(
                    "File type of schema is unknown \"".$mimeType."\"",
                    500,
                    [
                        "custom_code"   =>  "structure-011",
                    ]
                );

        }

        # Return result
        return $result;

    }

    /**
     * Loop
     * 
     * Loop for iterate inside structure
     * 
     * @param array $collection
     * @param ?callable $folder Function to execute for folder
     * @param ?callable $file Function to execute for file
     * @param bool $preview Preview mode
     * @param string $root Current root
     * @param array $result Current array
     * 
     * @return array
     */
    private static function _loopInsideSchema(?array $collection, ?callable $folder = null, ?callable $file = null, bool $preview = false, string $root = "", array &$result = []):array {

        # Check collection not valid
        if($collection === null)

            # Stop loop
            return [];

        # Check folders
        if(isset($collection['folders']) && !empty($collection['folders']))

            # Iteration of folders
            foreach($collection['folders'] as $folderName => $content){

                # Check foldername
                if(!$folderName) continue;

                # Path of folder
                $currentPath = File::path($root?"$root/$folderName":$folderName);

                # Push path in result
                $result = Arrays::mergeMultidimensionalArrays(
                    true,
                    $result, 
                    Arrays::stretch([$currentPath], "/")
                );

                # Execute file
                if(is_callable($folder)) 
                    $folder(
                        $collection['folders'][$folderName],
                        $currentPath,
                        $preview
                    );

                # Recursive
                static::_loopInsideSchema($content, $folder, $file, $preview, $currentPath, $result);

                
            }

        # Check files
        if(isset($collection['files']) && !empty($collection['files']))

            # Iteration of files
            foreach($collection['files'] as $fileName => $option){

                # Check foldername
                if(!$fileName) continue;

                # Path of folder
                $currentPath = File::path("$root/$fileName");

                # Push path in result
                $result = Arrays::mergeMultidimensionalArrays(
                    true,
                    $result, 
                    Arrays::stretch([$currentPath], "/")
                );

                # Execute file
                if(is_callable($file)) 
                    $file(
                        $collection['files'][$fileName],
                        $currentPath,
                        $preview
                    );

            }

        # Return result
        return $result;

    }

    /** Private method
     ******************************************************
     */

    private function _parseYaml():void {

        # Declare results
        $result = [];

        # Ingest Yaml
        $parsed = Yaml::open($this->template);

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
    public const DEFAULT_TEMPLATE = "@crazyphp_root/resources/Yml/Structure.yml";

    /**
     * Template for docker
     */
    public const DOCKER_TEMPLATE = "@crazyphp_root/resources/Docker/Structure.yml";

    /**
     * Default permission
     */
    public const DEFAULT_PERMISSION = 0777;

}