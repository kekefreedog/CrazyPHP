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
namespace  CrazyPHP\Cli;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
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

                # Check select is callable
                if(isset($value['select']) && is_callable($value['select'])){

                    # Set select
                    $value['select'] = $value['select']();

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

                # Check select is callable
                if(isset($value['default']) && is_callable($value['default']))

                    # Set select
                    $value['default'] = $value['default']();

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

}