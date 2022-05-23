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
     * Logs (form results logs)
     */
    private $logs = [];

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
        if(!is_string($input['value']) && !is_numeric($input['value'])){

            # id number
            $id = rand()."-".time();

            # Fill log
            $this->log[$id] = [
                "message"   =>  "Value given is not a string nor numeric",
                "code"      =>  "form-001",
                "icon"      =>  [
                    "text"      =>  "error",
                    "class"     =>  "material-icons"
                ],
                "color"     =>  "red",
                "old_value" =>  $input['value'],
            ];

            # Set value null
            $input['value'] = null;

            # Create link of log in input
            $input["log"] = $id;

            # Stop function
            return;

        }

        # Check process
        if(!empty($input['process'] ?? null))

            # Iteration process
            foreach($input['process'] as $process)

                # Check methods exists
                if(method_exists($this, $process))

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
        if(!is_array($input['value'])){

            # id number
            $id = rand()."-".time();

            # Fill log
            $this->log[$id] = [
                "message"   =>  "Value given is not an array",
                "code"      =>  "form-002",
                "icon"      =>  [
                    "text"      =>  "error",
                    "class"     =>  "material-icons"
                ],
                "color"     =>  "red",
                "old_value" =>  $input['value'],
            ];

            # Set value null
            $input['value'] = null;

            # Create link of log in input
            $input["log"] = $id;

            # Stop function
            return;

        }

    }

    /**
     * Action for boolean
     * 
     * @return array
     */
    private function _actionBool(array &$input = []){



    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * String trim
     * 
     * Trim string
     * 
     * @param string $input
     * @return string
     */
    public static function trim(string $input = ""){

        return trim($input);

    }

}