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


}