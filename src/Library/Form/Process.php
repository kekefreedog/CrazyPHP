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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Form;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Array\Arrays;

/**
 * Process form values
 *
 * Process form values return error / log message for client
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Process {

    /** Variables
     ******************************************************
     */

    /** 
     * Input (form results)
     */
    private $values = [];

    /**
     * Dispatch of action
     */
    private $dispatch = [
        "VARCHAR"   =>  [
            "trim",
            "clean",
            "https",
            "bool",
            "email",
            "camelToSnake",
            "snakeToCamel"
        ],
        "ARRAY"     =>  [
        ], 
        "BOOL"      =>  [
            "bool",
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

        endforeach;

    }

    /** Private Methods
     ******************************************************
     */

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
                    in_array($process, $this->dispatch["VARCHAR"]) &&
                    method_exists($this, $process)
                )

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

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
                    in_array($process, $this->dispatch["ARRAY"]) &&
                    method_exists($this, $process)
                )

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);

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
                    in_array($process, $this->dispatch["BOOL"]) &&
                    method_exists($this, $process)
                )

                    # Process value
                    $input['value'] = $this->{$process}($input['value']);


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
    public static function getResultSummary(array $inputs = []):array {

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
                    "custom_code"   =>  "process-001",
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
     * @return string
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
     * Wash
     * 
     * Wash items by condtions
     * 
     * @param array $inputs Input to process
     * @param array $conditions Conditon to respect
     * @return array
     */
    public static function wash(array $inputs = [], array $conditions = []):array {

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
    public static function compilate(array $inputs = [], bool $keepNull = false):array {

        # Declare result
        $result = [];

        # Check inputs
        if(!empty($inputs))

            # Iteration inputs
            foreach($inputs as $value)

                # Check if not null or keepNull
                if($value['value'] !== null || $keepNull)

                    # Push value in result
                    $result[$value['name']] = $value['value'];

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
    public static function sortByConditions(array $array = [], array $conditions = [], string $separator = "_"):array {

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

}