<?php declare(strict_types=1);
/**
 * Form
 *
 * Useful class for manipulate form
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Form;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Config;
use CrazyPHP\Model\Env;

/**
 * Process form values
 *
 * Process form values return error / log message for client
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Process {

    /** Variables
     ******************************************************
     */

    /** 
     * Input (form results)
     */
    private array $values = [];

    /**
     * Dispatch of action
     */
    private array $dispatch = [
        "INT"       =>  [
        ],
        "DECIMAL"   =>  [
        ],
        "SELECT"       =>  [
            "integer",
        ],
        "VARCHAR"   =>  [
            "trim",
            "clean",
            "https",
            "bool",
            "email",
            "camelToSnake",
            "snakeToCamel",
            "spaceBeforeCapital",
            "ucfirst",
            "ucwords",
            "cleanPath",
            "strtolower"
        ],
        "ARRAY"     =>  [
        ], 
        "BOOL"      =>  [
            "bool",
        ],
        "FILE"      =>  [
            "storeInLocalAndGetPath"
        ],
        "JSON"      =>  [
        ]
    ];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $formResult Collection of value to process
     * @return Form
     */
    public function __construct(array $formResult = []){

        # Set input
        $this->values = $formResult;

        # Iteration inputs
        foreach($this->values as $key => &$input):

            # Type integer
            if(strtoupper(substr(trim($input['type']), 0, 3)) == "INT")

                # Action for varchar
                $this->_actionInt($input);

            # Type integer
            if(strtoupper(substr(trim($input['type']), 0, 7)) == "DECIMAL")

                # Action for decimal
                $this->_actionDecimal($input);

            # Type varchar
            if(strtoupper(substr(trim($input['type']), 0, 7)) == "VARCHAR")

                # Action for varchar
                $this->_actionVarchar($input);

            # Type array
            elseif(strtoupper(substr(trim($input['type']), 0, 5)) == "ARRAY")

                # Action for array
                $this->_actionArray($input);
                
            # Type Boolean
            elseif(strtoupper(substr(trim($input['type']), 0, 4)) == "BOOL")

                # Action for bool
                $this->_actionBool($input);
                
            # Type Boolean
            elseif(strtoupper(substr(trim($input['type']), 0, 4)) == "FILE")

                # Action for bool
                $this->_actionFile($input);
                
            # Type Boolean
            elseif(strtoupper(substr(trim($input['type']), 0, 4)) == "JSON")

                # Action for bool
                $this->_actionJson($input);

        endforeach;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Action for int
     * 
     * @return void
     */
    private function _actionInt(array &$input = []):void {

        # Check value is same type
        if(!is_int($input['value']) && !@ctype_digit($input['value'])){

            # Check requierd
            if(
                (
                    isset($input['required']) &&
                    $input['required'] == false
                ) ||
                !isset($input['required'])
            ){

                # Set value to null
                $input['value'] = null;

            }

            # Stop function
            return;

        }

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["INT"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }

    }

    /**
     * Action for decimal
     * 
     * @return void
     */
    private function _actionDecimal(array &$input = []):void {

        # Check value is same type
        if(!is_float($input['value']) && !preg_match('/^-?\d+\.\d+$/', (string) $input['value'])){

            # Check requierd
            if(
                (
                    isset($input['required']) &&
                    $input['required'] == false
                ) ||
                !isset($input['required'])
            ){

                # Set value to null
                $input['value'] = null;

            }

            # Stop function
            return;

        }

        # Parse the value
        $input['value'] = floatval($input['value']);

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["DECIMAL"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }

    }

    /**
     * Action for varchar
     * 
     * @return void
     */
    private function _actionVarchar(array &$input = []):void {

        # Check value is same type
        if(!is_string($input['value']) && !is_numeric($input['value']))

            # Stop function
            return;

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["VARCHAR"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }

    }

    /**
     * Action for array
     * 
     * @return array
     */
    private function _actionArray(array &$input = []){

        # Check value is same type
        if(!is_array($input['value']))

            # Stop function
            return;

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["ARRAY"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }

    }

    /**
     * Action for boolean
     * 
     * @return array
     */
    private function _actionBool(array &$input = []){

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["BOOL"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }


    }

    /**
     * Action for file
     * 
     * @return array
     */
    private function _actionFile(array &$input = []){

        # Was not text
        $wasNotText = false;

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process){

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["FILE"]) &&
                    method_exists($this, $process) &&
                    (!is_string($input["value"]) || $wasNotText)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                    # Set was not text
                    $wasNotText = true;

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process) &&
                    (!is_string($input["value"]) || $wasNotText)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                    # Set was not text
                    $wasNotText = true;

                }

            }


    }

    /**
     * Action for json
     * 
     * @return void
     */
    private function _actionJson(array &$input = []):void {

        # Check value is same type
        if(!is_string($input['value']) && !is_numeric($input['value']))

            # Stop function
            return;

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(
                    $process &&
                    in_array($process, $this->dispatch["JSON"]) &&
                    method_exists($this, $process)
                ){

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

                }else
                # Check is callable
                if(
                    $process &&
                    is_callable($process)
                ){

                    # Process value
                    $input['value'] = $process($input['value']);

                }

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Recursive array exploration
     * 
     * @param array $inputs Input to explore
     * @param ?callable $callable Function for !array value
     * @return array
     */
    private static function _recursiveArrayExploration(array &$inputs = [], ?callable $callable = null):void {

        # Check callable
        if(!$callable)

            # Stop function
            return;

        # Iteration of inputs
        foreach($inputs as $key => $input)

            # Check input is array
            if(is_array($input))

                # Call itself
                static::_recursiveArrayExploration($inputs[$key], $callable);

            # Else call function
            else{

                # Call finction
                $inputs[$key] = $callable($input);

            }

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get Result
     * 
     * Return result
     * 
     * @param string $input
     * @return array
     */
    public function getResult():array {

        # Return value
        return $this->values;

    }

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Get Result Summary
     * 
     * Return a result summary as {<parameter>:<value>}
     * 
     * Warning, requierd valuue missing caused error
     * 
     * @param array $input
     * @return array
     */
    final public static function getResultSummary(array $inputs = []):array {

        # Get Get Result Summary
        return Validate::getResultSummary($inputs);

    }    
    
    /**
     * Items In
     *  
     * Process items is in conditions collection
     * @param array $inputs Input to check
     * @param array $conditions Collection of data to compare with
     * @return array
     */
    public static function itemsIn(array $inputs = [], array $conditions = []):array {

        # Declare Result & inputMissing
        $result = [];
        $inputsMissing = "";

        # Check inputs and conditions
        if(empty($inputs) || empty($conditions))
            return $result;

        # Check if only one value
        if( ( $inputs['name'] ?? false ) || ( $inputs['type'] ?? false ))

            # Put input in an array
            $inputs = [$inputs];

        # Iteration of input
        foreach($inputs as $input){
        
            # Get corresponding condition
            $condition = Arrays::filterByKey($conditions, "name", $input["name"]);
            
            # Check condition
            if(!empty($condition)){

                # Iteration des condition
                foreach($condition as $k => $v)

                    # Unset value
                    unset($conditions[$k]);

                # Push current input in result
                $result[] = $input;

            }

        }

        # Check required conditions
        if(!empty($conditions))

            # Iteration des conditions
            foreach($conditions as $condition)

                # Check required
                if($condition["required"] ?? false)

                    # Push name of required parameter in inputsMissing
                    $inputsMissing .= ($inputsMissing ? ", " : "").
                        "\"".$condition['name']."\"";

        # Check input missing
        if($inputsMissing)

            # New Exception
            throw new CrazyException(
                "$inputsMissing ".
                    (
                        strpos($inputsMissing, ",") !== false ?
                            "are " :
                                 ""
                    ).
                        "missing in processed inputs...",
                500,
                [
                    "custom_code"   =>  "process-010",
                ]
            );

        # Return result
        return $result;

    }

    /**
     * Trim string
     * 
     * Trim string
     * 
     * @param string $input
     * @return string
     */
    public static function trim(string $input = ""){

        # Return trimed string
        return trim($input);

    }

    /**
     * Clean string 
     * 
     * Trim string
     * 
     * @param string $input
     * @return string
     */
    public static function clean(string $input = ""):string {

		# Rules
		$rules = [
			'/[áàâãªä]/u'       =>  'a',
			'/[ÁÀÂÃÄ]/u'        =>  'a',
			'/[ÍÌÎÏ]/u'         =>  'i',
			'/[íìîï]/u'         =>  'i',
			'/[éèêë]/u'         =>  'e',
			'/[ÉÈÊË]/u'         =>  'e',
			'/[óòôõºö]/u'       =>  'o',
			'/[ÓÒÔÕÖ]/u'        =>  'o',
			'/[úùûü]/u'         =>  'u',
			'/[ÚÙÛÜ]/u'         =>  'u',
			'/ç/'               =>  'c',
			'/Ç/'               =>  'c',
			'/ñ/'               =>  'n',
			'/Ñ/'               =>  'n',
			'/\s+/'             =>  '_',
			'/–/'               =>  '-', // UTF-8 hyphen to "normal" hyphen
			'/[’‘‹›‚]/u'        =>  ' ', // Literally a single quote
			'/[“”«»„]/u'        =>  ' ', // Double quote
			'/ /'               =>  ' ', // nonbreaking space (equiv. to 0x160),
			'/[\'"“”‘’„”«»]/u'  =>  '',	
			'/[(]/'			    =>	'',  // Round brackets
			'/[)]/'			    =>	'',  // Round brackets
			'/(_-_)/'		    =>	'_',
		];

		# Return result value
		return strtolower(
            preg_replace(
                array_keys($rules), 
                array_values($rules), 
                $input
            )
        );

    }

    /**
     * Clean string
     * 
     * Alternative to clean function but keep "/"
     * 
     * @param string $input
     * @return string
     */
    public static function cleanPath(string $input):string {

        # Declare result
        $result = "";

        # Convert input to ASCII encoding
        $result = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $input);
        
        # Remove all non-alphanumeric characters (except apostrophes, quotes, and backticks)
        $result = preg_replace('/[^a-zA-Z0-9\/]/', '', $input);
        
        # Replace all spaces with underscores
        $result = preg_replace('/\s+/', '_', $result);
        
        # Trim any leading or trailing underscores
        $result = trim($result, '_');
        
        # Return result
        return $result;
    }

    /**
     * Reduce Path
     * 
     * Reduce path using given env
     * 
     * @param string $input
     * @param string|array $env Exemple @app_root or @crazyphp_root
     * @param string $replaceBy By default replace path by current folder "."
     * @return string
     */
    public static function reducePath(string $input, string|array $env = "@app_root", string $replaceBy = "."):string {

        # Set result
        $result = $input;

        # Check input and env
        if(!$input || !$env || empty($env) || !File::exists($input))

            # Stop function
            return $result;

        # Check env is not array
        if(!is_array($env))

            # Convert to array
            $env = [$env];

        # Set clean env
        $cleanEnv = [];

        # Iteration of env
        foreach($env as $value){

            # Check env exists
            if($value && Env::has($value)){

                # Get path
                $currentEnv = Env::get($value);
            
                # Check current env
                if($currentEnv && File::exists($currentEnv) && !in_array($currentEnv, $cleanEnv)){

                    # Push in current en as is
                    $cleanEnv[] = $currentEnv;

                    # Get real path
                    $currentEnvReal = realpath($currentEnv);

                    # Check if in array
                    if(!in_array($currentEnvReal, $cleanEnv))

                        # Push in clean env
                        $cleanEnv[] = $currentEnvReal;

                }

            }

        }

        # Check clean env
        if(!empty($cleanEnv))

            # Iteration clean env
            foreach($cleanEnv as $currentEnv)

                # Str replace
                $result = str_replace($currentEnv, $replaceBy, $result);
        
        # Return result
        return $result;

    }

    /**
     * Https
     * 
     * Add or replace http to https 
     * 
     * @param string $input
     * @return string
     */
	public static function https(string $input = ''):string {
        
		# Check url start by https
        if(!substr($input, 0, 8) == "https://")
        
            if(substr($input, 0, 7) == "http://")
            
                return str_replace('http://', 'https://', $input);
            
            else
                
                return 'https://'.$input;

        # Return input
        return $input;

	}

    /**
     * Bool
     * 
     * Process value to bool 
     * 
     * @param $input
     * @return bool
     */
    public static function bool($input = ""):bool {

        # check if email
		return filter_var($input, FILTER_VALIDATE_BOOL) ? true : false;

    }	
    
    /**
     * Email
     * 
     * Process email
     * 
     * @param string $string
     * @return string
     */
    public static function email(string $input = ''):string {

        # Process email
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? 
            $input : 
                '';

    }

	/**
     * Camel to Snake
     * 
     * HelloWorld => hello_world 
     * 
     * @param $input
     * @return string
	 * 
	 */
	public static function camelToSnake(string $input = ''):string {

        # Process input
		preg_match_all(
            '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', 
            $input,
            $matches
        );

		$ret = $matches[0];

		foreach($ret as &$match)

		  $match = $match == strtoupper($match) ?
            strtolower($match) :
                lcfirst($match);

		return implode('_', $ret);

	}

    /**
     * Camel To Path
     * 
     * Convert Camel case to path
     * Exemple : HelloWorldTest -> Hello/World/Test
     * 
     * @param string $input Input
     * @param bool $lowerCase Output in lowercase
     * @param string $separator Bu default "/"
     * @return string
     */
    public static function camelToPath(string $input = "", bool $lowerCase = false, string $separator = "/"):string {

        # Set result
        $result = "";

        # Check input
        if(!$input)

            # Return result
            return $result;

        # Convert string
        $result = preg_replace('/([a-z])([A-Z])/', "$1$separator$2", $input);

        # Check lower case
        if($lowerCase)

            # Lower case result
            $result = strtolower($result);

        # Return result
        return $result;

    }

	/**
     * Snake to Camel
     * 
     * hello_tout_le_monde : {
     *  helloToutLeMonde
     *  HelloToutLeMonde (capitalize)
     * }
     * 
     * @param $input
     * @return string
	 * 
	 */
	public static function snakeToCamel($input = '', bool $capitalizeFirstCharacter = false):string {
        
        # Process string
        $input = str_replace(' ', '_', $input);
		$input = str_replace('_', '', ucwords($input, '_'));

        # Check if we keep capital on first character
		if (!$capitalizeFirstCharacter)
			$input = lcfirst($input);

		return $input;
	}

    /**
     * Space Before Capital
     * 
     * Insert a space before each capital letter, like this :
     * - HelloWorld <=> Hello World
     * 
     * > Exception if it is the first character
     * 
     * @param string $input
     * @return string
     */
    public static function spaceBeforeCapital(string $input):string {

        # Set result
        $result = $input;

        # Check input
        if($input)

            # Process input
            $result = preg_replace('/(?<!^)([A-Z])/', ' $1', $input);

        # Return result
        return $result;

    }

    /**
     * UCFirst
     * 
     * Upper Case First
     * 
     * @param string
     * @return string
     */
    public static function ucfirst(string $input):string {

        # Return result
        return ucfirst($input);

    }

    /**
     * UCWords
     * 
     * Upper Case First For each Words
     * 
     * @param string
     * @return string
     */
    public static function ucwords(string $input):string {

        # Return result
        return ucwords($input);

    }

    /**
     * Lower Case
     * 
     * @param string
     * @return string
     */
    public static function strtolower(string $input):string {

        # Return result
        return strtolower($input);

    }

    /**
     * Integer
     * 
     * @param string|int
     * @return int
     */
    public static function integer(string|int $input):int {

        # Return result
        return is_int($input) 
            ? $input 
            : intval($input)
        ;

    }

    /**
     * Alphanumeric
     * 
     * @param string
     * @param bool lowerCase
     * @return string
     */
    public static function alphanumeric(string $input, bool $lowerCase = false):string {

        # Set result
        $result = $input;

        # Check lower case
        if($lowerCase)

            # Convert the string to lowercase
            $result = strtolower($result);
            
        # Replace spaces with an empty string (or another character if you prefer)
        $result = str_replace(" ", "", $result);
        
        # Remove unwanted characters, only allow alphanumeric characters
        $result = preg_replace('/[^a-z0-9]/', '', $result);
        
        # Return result
        return $result;

    }

    /**
     * Wash
     * 
     * Wash items by condtions
     * 
     * @param array $inputs Input to process
     * @param array $conditions Conditon to respect
     * @return array
     */
    final public static function wash(array $inputs = [], array $conditions = []):array {

        # Declare result
        $result = [];

        # Check inputs & conditions
        if(empty($inputs) || empty($conditions))

            # Set result
            $result = $inputs;

        # Start wash
        else

            # Iteration inputs
            foreach($inputs as $key => $value){

                # Check condition with same name exists
                $condition = Arrays::filterByKey($conditions, "name", $value['name']);

                # Check condition and type
                if(
                    !empty($condition) &&
                    $value['type'] == $condition[array_key_first($condition)]['type'] 
                )

                    # Push value in result
                    $result[] = $value;

            }

        # Return result
        return $result;

    }

    /**
     * Compilate
     * 
     * Compilate items to array with key => values
     * 
     * @param array $input Input to process
     * @param bool $keepNull Keep null or delete them from compilation
     * @return array
     */
    final public static function compilate(array $inputs = [], bool $keepNull = false):array {

        # Declare result
        $result = [];

        # Check inputs
        if(!empty($inputs))

            # Iteration inputs
            foreach($inputs as $value)

                # Check if not null or keepNull
                if(
                    (
                        (
                            isset($value['value']) && 
                            $value['value'] !== null
                        ) || 
                        $keepNull
                    ) ||
                    (
                        isset($value['required']) &&
                        $value['required']
                    )
                ){

                    # check required and default
                    if(isset($value["required"]) && $value["required"] && !isset($value['default']))
            
                        # New error
                        throw new CrazyException(
                            "The default value is missing for the requierd field \"".$value['name']."\"",
                            500,
                            [
                                "custom_code"   =>  "process-020",
                            ]
                        );

                    # Push value in result
                    $result[$value['name']] = $value['value'] ?? self::setDefault($value['default']);

                }

        # Return result
        return $result;

    }

    /**
     * Sort by conditions
     * 
     * Sort items to array by conditions (all other items will but at the end
     * - Only sort first dimension array
     * 
     * @param array $inputs Input to process
     * @param array $conditions Conditon to respect
     * @param string $separator Separator to avoid multidimensional array
     * @return array
     */
    final public static function sortByConditions(array $array = [], array $conditions = [], string $separator = "_"):array {

        # Declare result
        $result = [];

        # Check condition
        if(!empty($conditions))

            # Iteration conditions
            foreach($conditions as $condition){

                # Check not multidimensional conditions
                if(strpos($condition['name'], $separator) !== false)
                    continue;

                # Check array as current condition
                if(isset($array[$condition['name']])){

                    # Push value in result
                    $result[$condition['name']] = $array[$condition['name']];

                    # Remove current name to array
                    unset($array[$condition['name']]);

                }
            
            }

        # Check not orther value in array
        if(!empty($array))

                # Merge them to array
                $result += $array;

        # Return result
        return $result;

    }

    /**
     * Shortcuts By File
     * 
     * Process Shortcuts in array with equivalent in file
     * 
     * @param string|array $inputs List of items to search
     * @param string $file File with equivalent shortcut to value
     * @return array
     */
    public static function shortcutsByFile(string|array $inputs = [], string $file = ""):string|array {

        # Declare result
        $result = [];

        $result = $inputs;

        # Return result
        return $result;

    }

    /**
     * Env And Cache Values
     * 
     * Process value or value in array and replace values depending of following rules :
     * - Value get @
     * - Value get "{{" & "}}"
     * 
     * @param any $inputs Input to process
     * @return any
     */
    public static function envAndConfigValues($inputs = []) {

        # Check result
        if(!is_array($inputs) && !is_string($inputs))

            # Return result
            return $inputs;

        # Check if string
        if(is_string($inputs)){

            # Check env
            $inputs = File::path((string)$inputs);

            # Check config
            $inputs = static::configValue($inputs);
            
        # Check config
        }else{

            # Check conig
            static::_recursiveArrayExploration(
                $inputs, 
                function($input){
                    $result = $input;
                    if(is_string($input) && $input){
                        $result = File::path($input);
                        $result = static::configValue($result);
                    }
                    return $result;
                }
            );

        }

        # Return result
        return $inputs;

    }

    /**
     * Config Value
     * 
     * @param string $input Input to process
     * @return string
     */
    public static function configValue(string $input = ""):string {

        # Set result
        $result = $input;

        # Check has "{{" and "}}"
        if(strpos($input, "{{") !== false && strpos($input, "}}") !== false){
        
            # Search values
            preg_match_all(
                Config::REGEX,
                $input,
                $results
            );

            # Clean result
            $results = array_unique(
                array_map(
                    function($v){
                        return is_string($v) ? trim($v, "{}") : $v;
                    },
                    $results[1]
                )
            );

            # Check results
            if(!empty($results))

                # Iteration of result
                foreach($results as $v){

                    # Get config value
                    $configValue = FileConfig::getValue($v);

                    # Check if is bool
                    if(is_bool($configValue))

                        # Set value
                        $configValue = $configValue ? "true" : "false";

                    # Check config value is string
                    if(!is_string($configValue))

                        # Set config value
                        $configValue = "";

                    # Replace result
                    $result = str_replace("{{".$v."}}", $configValue, $result);

                }
        }

        # Return result
        return $result;

    }

    /**
     * Set Default
     * 
     * Set default and check if default is callable
     * 
     * @param mixed $valueRequired Default Value
     * @param array $arguments Argument to pass in case the default value is callable
     * @param array $items Current items exisitng
     * @return mixed
     */
    public static function setDefault(mixed $valueRequired = null, array $arguments = [], array $currentItems = []):mixed {

        # Set result
        $result = null;

        # Check valueRequired
        if($valueRequired === null)

            # Return null
            return $result;

        # Check id the string is value
        if(is_callable($valueRequired)){

            $result = $valueRequired($arguments, $currentItems);

        }else{

            # Set result
            $result = $valueRequired;

        }

        # Return result
        return $result;

    }

}