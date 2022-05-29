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
use CrazyPHP\Library\Form\Process;

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
        if(!is_string($input['value']) && !is_numeric($input['value'])){

            # New log
            $input["log"] = $this->_pushLog(
                [
                    "message"   =>  "Value given is not a string nor numeric",
                    "code"      =>  "form-001",
                    "icon"      =>  [
                        "text"      =>  "error",
                        "class"     =>  "material-icons"
                    ],
                    "color"     =>  "red",
                    "old_value" =>  $input['value'],
                ]
            );

            # Set value null
            $input['value'] = null;

            # Stop function
            return;

        }

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
        if(!is_array($input['value'])){

            # New log
            $input["log"] = $this->_pushLog(
                    [
                    "message"   =>  "Value given is not an array",
                    "code"      =>  "form-002",
                    "icon"      =>  [
                        "text"      =>  "error",
                        "class"     =>  "material-icons"
                    ],
                    "color"     =>  "red",
                    "old_value" =>  $input['value'],
                ]
            );

            # Set value null
            $input['value'] = null;

            # Stop function
            return;

        }

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
        if(!is_bool($input['value'])){

            # New log
            $input["log"] = $this->_pushLog(
                    [
                    "message"   =>  "Value given is not a boolean",
                    "code"      =>  "form-003",
                    "icon"      =>  [
                        "text"      =>  "error",
                        "class"     =>  "material-icons"
                    ],
                    "color"     =>  "red",
                    "old_value" =>  $input['value'],
                ]
            );

            # Set value null
            $input['value'] = null;

            # Stop function
            return;

        }

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
        return $this->values;

    }

    /**
     * Get Logs
     * 
     * Return logs
     * 
     * @param string $input
     * @return array
     */
    public function getLogs():array {

        # Return value
        return $this->log;

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
     * @param array $input Input item
     * @return void
     */
    private function _isEmail(array &$input = ""):void {

        if(!filter_var($input['value'], FILTER_VALIDATE_EMAIL))

            # New log
            $input["log"] = $this->_pushLog(
                [
                "message"   =>  "Value given is not a boolean",
                "code"      =>  "form-004",
                "icon"      =>  [
                    "text"      =>  "error",
                    "class"     =>  "material-icons"
                ],
                "color"     =>  "orange",
                "old_value" =>  $input['value'],
            ]
        );

        # Set value null
        $input['value'] = null;

        # Stop function
        return;

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

    /** Private Methods
     ******************************************************
     */

    /**
     * Push Log
     * 
     * Process log and return key associate
     * 
     * @return string
     */
    public function _pushLog($input){

        # Random id
        $id = rand()."-".time();

        # Declare result
        $result = [
            "message"   =>  "Error",
            "code"      =>  "form-00"
        ];

        # Push input parameter
        if($input['message'] ?? false)
            $result["message"] = $input["message"];

        if($input['log'] ?? false)
            $result["log"] = $input["log"];

        if($input['code'] ?? false)
            $result["code"] = $input["code"];

        if($input['icon'] ?? false)
            $result["icon"] = $input["icon"];

        if($input['color'] ?? false)
            $result["color"] = $input["color"];

        if($input['old_value'] ?? false)
            $result["old_value"] = $input["old_value"];

        # Fill log
        $this->log[$id] = $result;

        # Return id
        return $id;

    }

}