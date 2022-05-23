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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Cli;

/**
 * Dependances
 */
use League\CLImate\CLImate;

/**
 * Form
 *
 * Methods for generate CLI form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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

            # Iteration of required values
            foreach($valueCollection as $value){

                # Check description
                if($value['description'] ?? false)

                    # Prepare question
                    $question = $value['description'];

                $question .= 
                    " {".$value['name']."} ?".
                    (
                        $value['default'] ?
                            " <".$value['default'].">" :
                                ""
                    )
                ;

                # Select
                if(!empty($value['select'] ?? [])){

                    # Multiple
                    if($value['multiple'] ?? false){


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
                    $input = $climate->input($question);

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
     * @return array
     */
    public function getResult(){

        # Declare result
        $result = [];

        # Set result
        $result = $this->result;

        # Return result
        return $result;

    }

}