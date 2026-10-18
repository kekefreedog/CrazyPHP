<?php declare(strict_types=1);
/**
 * Cli
 *
 * Core of the cli
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Cli;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\System\Os;
use League\CLImate\CLImate;

/**
 * Form
 *
 * Methods for generate CLI form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Form {

    /** Variables
     ******************************************************
     */

    /** 
     * Result
     */
    private $result = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param array $valueCollection Collection of value to display
     * @return Form
     */
    public function __construct(array $valueCollection = []){

        # New Climate instance
        $climate = new CLImate();

        # Check required value
        if(!empty($valueCollection))

            # Break line
            $climate->br();

            # Declare key
            $key = 1;

            # Iteration of required values
            foreach($valueCollection as $value){

                # Skip questions excluded by answers to earlier questions
                if(!self::isVisible($value, $this->result))

                    # Continue with the next relevant question
                    continue;

                # Check select is callable
                if(isset($value['select']) && is_callable($value['select'])){

                    # Set select
                    $value['select'] = self::getChoices($value, $this->result);

                    # Check if empty
                    if(empty($value['select']))

                        # New error
                        throw new CrazyException(
                            "No items found",
                            500,
                            [
                                "custom_code"   =>  "form-001"
                            ]
                        );

                }

                # Check default is callable
                if(isset($value['default']) && is_callable($value['default']))

                    # Set default, passing previous answers only to providers that request them
                    $value['default'] = ($value['defaultWithAnswers'] ?? false)
                        ? $value['default']($this->result)
                        : $value['default']()
                    ;

                # Check description
                if($value['description'] ?? false)

                    # Prepare question
                    $question = $key.". ".$value['description'];

                # Prepare question
                $question .= 
                    " {".$value['name']."} ?".
                    (
                        ($value['default'] ?? false) ?
                            " <".$value['default'].">" :
                                ""
                    )
                ;

                # Select
                if(!empty($value['select'] ?? [])){

                    # Check os is windows
                    if(Os::isWindows()){

                        # Set windows question
                        $windowsQuestion = $question.PHP_EOL;

                        # Set accepted value
                        $acceptedValues = [];

                        # Set index
                        $index = "a";

                        # Set default
                        $default = null; 

                        # Set accept
                        $accept = null; 

                        # Iteration select
                        foreach($value['select'] as $k => $v){

                            # Push value in question
                            $windowsQuestion .= "   $index. $v".PHP_EOL;

                            # Push index in accepted value with value
                            $acceptedValues[$index] = $k;
                        
                            # Increment index
                            $index++;

                        }

                        # Check required
                        if(!isset($value["required"]) || !$value["required"]){
                           
                            # Set default
                            $acceptedValues[""] = "";

                        }

                        # Check $value['default'] 
                        if($value['default'] ?? false)

                            # Set default
                            $default = array_search($value["default"], $acceptedValues);

                        else
                        # Set default empty
                        if(!isset($value["required"]) || !$value["required"])

                            # Set default
                            $default = '';

                        # Check multiple
                        if(isset($value["multiple"]) && $value["multiple"]){

                            # Append to message
                            $windowsQuestion .= 
                                " [".
                                (count($acceptedValues) >= 1 ? "a" : "").
                                (count($acceptedValues) >= 2 ? " , a/b" : "").
                                (count($acceptedValues) >= 3 ? " , b/c/d" : "").
                                (count($acceptedValues) >= 2 ? "..." : "").
                                "]"
                            ;

                            # Append to message
                            if($value['default'])

                                # Add default
                                $windowsQuestion .= " <<b>".array_search($value["default"], $acceptedValues)."</b>> \"".$value["default"]."\"";

                        }else

                            # Set accept
                            $accept = array_keys($acceptedValues);

                        # Create input
                        $input = $climate->input($windowsQuestion);

                        # Check default
                        if($default !== null)

                            $input->defaultTo($default);

                        # Check default
                        if($accept !== null)

                            $input->accept($accept, true);

                        # Run prompt
                        $response = $input->prompt();

                        # Get value
                        if($value["multiple"] ?? false){
                            
                            # Set temp result
                            $resultTemp = [];

                            # Splt response by "/"
                            $splitted = explode("/", trim($response));

                            # Check splitted
                            if(empty($splitted) || (count($splitted) == 1 && $splitted[0] == "")){

                                # Check if requied
                                if($value["required"] ?? false){

                                    # New error
                                    throw new CrazyException(
                                        "Empty value for ".($value["label"] ?? $value["name"]),
                                        500,
                                        [
                                            "custom_code"   =>  "form-002"
                                        ]
                                    );

                                }

                            }else{

                                # Iteration of splitted
                                foreach($splitted as $v)

                                    # Check in accept
                                    if(array_key_exists(trim($v), $acceptedValues) && isset($acceptedValues[trim($v)]))

                                        # Push in temp result
                                        $resultTemp[] = $acceptedValues[trim($v)];

                                # Check if requied
                                if(empty($resultTemp) && $value["required"]){

                                    # New error
                                    throw new CrazyException(
                                        "Empty value for ".($value["label"] ?? $value["name"]),
                                        500,
                                        [
                                            "custom_code"   =>  "form-003"
                                        ]
                                    );

                                }

                            }

                            # Set splitted
                            $valueFromInput = $resultTemp;

                        }else{

                            # Set value from input
                            $valueFromInput = $acceptedValues[$response] ?? "";

                        }
                                
                        # Get result
                        $this->result[$value['name']] = array_merge(
                            $value,
                            [
                                "value" =>  $valueFromInput
                            ]
                        );
                    
                    # Is is linux or mac
                    }else

                        # Multiple
                        if($value['multiple'] ?? false){

                            # Prepare input
                            $input = $climate->checkboxes($question, $value['select']);
                            
                            # Get result
                            $this->result[$value['name']] = array_merge(
                                $value,
                                [
                                    "value" =>  $input->prompt()
                                ]
                            );

                        # Radio
                        }else{

                            # Prepare input
                            $input = $climate->radio($question, $value['select']);
                            
                            # Get result
                            $this->result[$value['name']] = array_merge(
                                $value,
                                [
                                    "value" =>  $input->prompt()
                                ]
                            );

                        }

                # Input
                }else{

                    # Parepare the input
                    $input = $value['type'] == "PASSWORD" ?
                        $climate->password($question) :
                            $climate->input($question);

                    # Check if default
                    if($value['default'] ?? false){

                        # Set default
                        $input->defaultTo($value['default']);

                    }

                    # Get result
                    $this->result[$value['name']] = array_merge(
                        $value,
                        [
                            "value" =>  $input->prompt()
                        ]
                    );

                }

                # Increment key
                $key++;

            }

        # Return instance
        return $this;

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get data
     * 
     * Send result of the form
     * 
     * @package bool $nameAsKey Keep name parameter as key of items
     * @return array
     */
    public function getResult(bool $nameAsKey = false){

        # Declare result
        $result = [];

        # Set result
        $result = $nameAsKey ?
            $this->result :
                array_values($this->result);

        # Return result
        return $result;

    }

    /**
     * Is Visible
     *
     * Match every condition against answers already collected by the form.
     *
     * @param array $field Question definition
     * @param array $answers Previous answers, keyed by name
     * @return bool
     */
    public static function isVisible(array $field, array $answers):bool {

        # Check all conditions before displaying the question
        foreach($field["when"] ?? [] as $name => $accepted){

            # Read the previous answer or its plain value
            $answer = $answers[$name]["value"] ?? $answers[$name] ?? null;

            # Exclude questions outside the selected branch, a multiple answer matches when any value is accepted
            if(
                is_array($answer)
                    ? !array_intersect($answer, $accepted)
                    : !in_array($answer, $accepted, true)
            )

                # Report that this question is hidden
                return false;

        }

        # Display unconditional questions and matching branches
        return true;

    }

    /**
     * Get Choices
     *
     * Preserve existing callback signatures unless answer context is requested.
     *
     * @param array $field Question definition
     * @param array $answers Previous form answers
     * @return array
     */
    public static function getChoices(array $field, array $answers = []):array {

        # Read static choices or the configured choice provider
        $choices = $field["select"] ?? [];

        # Keep ordinary dropdown definitions unchanged
        if(!is_callable($choices))

            # Return the existing static choices
            return $choices;

        # Pass previous answers only to providers that explicitly request them
        return ($field["selectWithAnswers"] ?? false) ? $choices($answers) : $choices();

    }

}
