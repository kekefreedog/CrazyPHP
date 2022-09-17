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
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;

/**
 * Validate form values
 *
 * Check form values respect rules and return error / log message for client
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Validate {

    /** Variables
     ******************************************************
     */

    /** 
     * Input (form results)
     */
    private $values = [];

    /**
     * Only One Input
     */
    private $oneItemOnly = false;

    /**
     * Dispatch of action
     */
    private $dispatch = [
        "VARCHAR"   =>  [
            "email"
        ],
        "ARRAY"     =>  [
        ], 
        "BOOL"      =>  [
        ]
    ];

    /**
     * Logs
     */
    private $log = [];

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

        # Check if only one value
        if( ( $formResult['name'] ?? false ) || ( $formResult['type'] ?? false )):

            # Put input in an array
            $formResult = [$formResult];

            # Set 
            $this->oneItemOnly = true;
        
        endif;

        # Iteration inputs
        foreach($this->values as &$input)

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

            # New Exception
            throw new CrazyException(
                "Value of \”".$input["name"]."\” isn't a string of characters...",
                500,
                [
                    "custom_code"   =>  "validate-001",
                ]
            );

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process){

                # Prepare method name
                $methodName = "_is".ucfirst($process);

                # Check methods exists
                if(
                    method_exists($this, $methodName) && 
                    in_array($process, $this->dispatch['VARCHAR'])
                )

                    # Process value
                    $input['value'] = $this->{$methodName}($input);

            }

        # Check if required
        if( ( $input["required"] ?? false ) && !$input['value'])

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = $input['default'];

    }

    /**
     * Action for array
     * 
     * @return array
     */
    private function _actionArray(array &$input = []){

        # Check value is same type
        if(!is_array($input['value']))

            # New Exception
            throw new CrazyException(
                "Value of \”".$input["name"]."\” isn't an array...",
                500,
                [
                    "custom_code"   =>  "validate-002",
                ]
            );

        # Check if required
        if( ( $input["required"] ?? false ) && !$input['value'])

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = $input['default'];

    }

    /**
     * Action for boolean
     * 
     * @return array
     */
    private function _actionBool(array &$input = []){

        # Check value is same type
        if(!is_bool($input['value']))

            # New Exception
            throw new CrazyException(
                "Value of \”".$input["name"]."\” isn't an boolean...",
                500,
                [
                    "custom_code"   =>  "validate-003",
                ]
            );

        # Check if required
        if( ( $input["required"] ?? false ) && !$input['value'])

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = $input['default'];

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
        return $this->oneItemOnly ?
            $this->values[array_key_first($this->values)] :
                $this->values;

    }

    /** Public Static Methods
     ******************************************************
     */

    /** 
     * Get Result Summary
     * 
     * Return a result summary as {<parameter>:<value>}
     * 
     * @param array $input
     * @return array
     */
    public static function getResultSummary(array $inputs = []):array {

        # Declare result
        $result = [];

        # Check empty
        if(empty($inputs))
            return $result;

        # Iteration of input
        foreach($inputs as $input)

            # Check name of input
            if($input['name'] ?? false){

                # Prepare name
                $name = ucwords(
                    str_replace(
                        "_",
                        " ",
                        Process::camelToSnake($input['name'])
                    )
                );

                # Check value is a password
                if($input['type'] === "PASSWORD")
                    
                    # Transform 
                    $value = $input['value'] ?
                        "*****" :
                            null;

                else
                # Check value is a password
                if($input['type'] === "ARRAY")
                    
                    # Transform 
                    $value = json_encode($input['value']);

                # Process value
                else
                    
                    # Check value
                    $value = $input['value'] ?
                        $input['value'] :
                            null;

                # Add current input in result collection
                $result[$name] = $value;

            }

        # Return result
        return $result;

    }

    /**
     * Is Items In
     *  
     * Check items is in conditions collection
     * Check also if required value isn't missing
     * 
     * @param array $inputs Input to check
     * @param array $conditions Collection of data to compare with
     * @return bool
     */
    public static function isItemsIn(array $inputs = [], array $conditions = []):bool {

        # Declare Result & inputMissing & inputExtra
        $result = true;

        # Check inputs and conditions
        if(empty($inputs) || empty($conditions))
            return $result;

        # Check if only one value
        if( ( $inputs['name'] ?? false ) || ( $inputs['type'] ?? false ))

            # Put input in an array
            $inputs = [$inputs];

        # Iteration of input
        foreach($inputs as $kInput => $input){
        
            # Get corresponding condition
            $condition = Arrays::filterByKey($conditions, "name", $input["name"]);
            
            # Check condition
            if(!empty($condition)){

                # Iteration des condition
                foreach($condition as $k => $v)

                    # Unset value
                    unset($conditions[$k]);

                # Unset current input
                unset($inputs[$kInput]);

            }

        }

        # Check inputs
        if(!empty($inputs))

            $result = false;

        # Check required conditions
        elseif(!empty($conditions))

            # Iteration des conditions
            foreach($conditions as $condition)

                # Check required
                if($condition["required"] ?? false)

                    # Update result
                    $result = false;

        # Return result
        return $result;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Is Email
     * 
     * Check string is email
     * 
     * @param string $input String to check
     * @return bool
     */
    public static function isEmail(string $input = ""):bool {

        return filter_var($input, FILTER_VALIDATE_EMAIL);

    }

    /** Private Methods | is...
     ******************************************************
     */

    /**
     * Is Email
     * 
     * Check string is email
     * 
     * @param string $input Input to validate
     * @return void
     */
    private function _isEmail(string &$input = ""):void {

        if(!filter_var($input['value'], FILTER_VALIDATE_EMAIL))

            # New Exception
            throw new CrazyException(
                "\”".$input["value"]."\” isn't a valid email...",
                500,
                [
                    "custom_code"   =>  "validate-004",
                ]
            );

    }

    /**
     * Is Http
     * 
     * Check string is url
     * 
     * @param string $input
     * @return bool
     */
    public static function isHttp(string $input = ""):bool {

        return true;

    }

    /**
     * Is Https
     * 
     * Check string is url
     * 
     * @param string $input
     * @return bool
     */
    public static function isHttps(string $input = ""):bool{

        return true;

    }

    /**
     * Is valid url
     * 
     * Check string is valid url
     * 
     * @param string $input
     * @return bool
     */
    public static function isValidUrl(string $input = ""):bool{

        return true;

    }

    /**
     * Is clean string
     * 
     * Check string is clean (no sepecials characters...)
     * 
     * @param string $input
     * @return bool
     */
    public static function isClean(string $input = ""):bool{

        return true;

    }

}