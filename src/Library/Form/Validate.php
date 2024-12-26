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
namespace  CrazyPHP\Library\Form;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use Exception;

/**
 * Validate form values
 *
 * Check form values respect rules and return error / log message for client
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
        "INT"       =>  [
            "isValidHttpStatusCode",
            "isMobilePhone"
        ],
        "VARCHAR"   =>  [
            "isEmail",
            "isIpAddress",
            "isValidUrl",
            "isSemanticVersioning",
            "isMobilePhone"
        ],
        "ARRAY"     =>  [
        ], 
        "BOOL"      =>  [
        ],
        "FILE"      =>  [
            "isValidFile"
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

            # Type int
            if(strtoupper(substr(trim($input['type']), 0, 3)) == "INT")

                # Action for varchar
                $this->_actionInt($input);
                
            # Type Boolean
            elseif(strtoupper(substr(trim($input['type']), 0, 4)) == "BOOL")

                # Action for bool
                $this->_actionBool($input);
                
            # Type Fil
            elseif(strtoupper(substr(trim($input['type']), 0, 4)) == "FILE")

                # Action for file
                $this->_actionFile($input);

            # Type array
            elseif(strtoupper(substr(trim($input['type']), 0, 5)) == "ARRAY")

                # Action for array
                $this->_actionArray($input);

            # Type varchar
            if(strtoupper(substr(trim($input['type']), 0, 7)) == "VARCHAR")

                # Action for varchar
                $this->_actionVarchar($input);

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
        if(
            (
                $input['value'] !== null && !is_int($input['value']) && !ctype_digit($input['value'])
            ) && (
                (
                    isset($input['required']) &&
                    $input['required'] == false &&
                    $input['value'] !== null
                ) ||
                (
                    !isset($input['required']) &&
                    $input['value'] !== null
                )
            )
        ){

            # New Exception
            throw new CrazyException(
                "Value of \”".$input["name"]."\” isn't a integer...",
                500,
                [
                    "custom_code"   =>  "validate-010",
                ]
            );

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

            # New Exception
            throw new CrazyException(
                "Value of \”".$input["name"]."\” isn't a string of characters...",
                500,
                [
                    "custom_code"   =>  "validate-020",
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
                $input['value'] = Process::setDefault($input["default"]);

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
                    "custom_code"   =>  "validate-030",
                ]
            );

        # Check if required
        if( ( $input["required"] ?? false ) && !$input['value'])

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = Process::setDefault($input["default"]);

    }

    /**
     * Action for boolean
     * 
     * @return array
     */
    private function _actionBool(array &$input = []){

        # Check value is same type
        if(!is_bool(filter_var($input['value'], FILTER_VALIDATE_BOOLEAN)))

            # New Exception
            throw new CrazyException(
                "Value of \"".$input["name"]."\" isn't an boolean...",
                500,
                [
                    "custom_code"   =>  "validate-040",
                ]
            );

        # Check if required
        if(($input["required"] ?? false ) && !$input['value'])

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = Process::setDefault($input["default"]);

    }

    /**
     * Action for File
     * 
     * @return array
     */
    private function _actionFile(array &$input = []){

        # Check input value
        if(!$input["value"] || empty($input["value"])){

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = Process::setDefault($input["default"]);

        }

        # Check if required
        if(($input["required"] ?? false ) && !$input['value'] && !empty($input['value']))

            # If default value
            if($input['default'] ?? false)

                # Set default value
                $input['value'] = Process::setDefault($input["default"]);

        # Check if array file
        if(is_array($input["value"]) && File::isFileArray($input["value"])){

            # Check file is valid
            self::isValidFile($input["value"], true);

            # Set ext first time
            $ext = null;

            # Check if extension allowed
            if(isset($input["extAllow"]) && !empty($input["extAllow"]) && $input["extAllow"]){

                # set ext allow
                $extAllow = $input["extAllow"];

                # Check extAllow is array
                if(!is_array($extAllow))

                    # Convert to array
                    $extAllow = [$extAllow];

                # Guess the mime type of the file
                $mimeType = File::guessMime($input["value"]["tmp_name"]);

                # Set ext
                $ext = array_search($extAllow, File::EXTENSION_TO_MIMETYPE) 
                    ? array_search($extAllow, File::EXTENSION_TO_MIMETYPE)
                    : @end(explode("/", $mimeType))
                ;
                
                # Check mime type is in the extAllow
                if(!in_array(strtolower($ext), array_map("strtolower", $extAllow)))

                    # New Exception
                    throw new CrazyException(
                        "Extension \"$ext\" of the file \"".$input["value"]["name"]."\" given isn't allowed... Here the file extension allowed : ".implode(", ", $extAllow),
                        500,
                        [
                            "custom_code"   =>  "validate-050",
                        ]
                    );

            }

            # Check if omit extension
            if(isset($input["extOmit"]) && !empty($input["extOmit"]) && $input["extOmit"]){

                # set ext allow
                $extOmit = $input["extOmit"];

                # Check extOmit is array
                if(!is_array($extOmit))

                    # Convert to array
                    $extOmit = [$extOmit];

                # check if ext already set
                if($ext === null){

                    # Guess the mime type of the file
                    $mimeType = File::guessMime($input["value"]["tmp_name"]);
        
                    # Set ext
                    $ext = array_search($extOmit, File::EXTENSION_TO_MIMETYPE) 
                        ? array_search($extOmit, File::EXTENSION_TO_MIMETYPE)
                        : @end(explode("/", $mimeType))
                    ;

                }
            
                # Check mime type is in the extAllow
                if(in_array(strtolower($ext), array_map("strtolower", $extOmit)))

                    # New Exception
                    throw new CrazyException(
                        "Extension \"$ext\" of the file \"".$input["value"]["name"]."\" given is omited... Here the other file extension not allowed : ".implode(", ", $extOmit),
                        500,
                        [
                            "custom_code"   =>  "validate-060",
                        ]
                    );

            }

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
     * @param bool $upperCaseOnName Define if name will be process with upper case
     * @param bool $rawName Keep name as is
     * @return array
     */
    public static function getResultSummary(array $inputs = [], bool $upperCaseOnName = true, bool $rawName = false):array {

        # Declare result
        $result = [];

        # Check empty
        if(empty($inputs))
            return $result;

        # Iteration of input
        foreach($inputs as $input)

            # Check name of input
            if($input['name'] ?? false){

                # Check raw name
                if($rawName)

                    # Set name
                    $name = $input["name"];

                # Beautify the name
                else{

                    # Prepare name
                    $name = str_replace(
                        "_",
                        " ",
                        Process::camelToSnake($input['name'])
                    );

                    # Check upper case
                    if($upperCaseOnName)

                        # Uppper cas on name
                        $name = ucwords($name);

                }

                # Check value is a password
                if($input['type'] === "PASSWORD")
                    
                    # Transform 
                    $value = $input['value'] ?
                        "*****" :
                            null;

                else
                # Check value is a password
                if($input['type'] === "ARRAY"){

                    # Check raw
                    if($input["raw"] ?? false)

                        # Set value
                        $value = $input['value'];

                    # Else encode in json
                    else
                    
                        # Transform 
                        $value = json_encode($input['value']);

                # Process value
                }else
                    
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
     * Is Valid Http Status Code
     * 
     * Check int is valid http status code
     * 
     * @param int $code
     * @return bool
     */
    public static function isValidHttpStatusCode(int $code = 200):bool {

        # Set result
        $result = false;

        # Check code
        if($code && $code >= 100 && $code <= 599)

            # Set result
            $result = true;

        # Return result
        return $result;

    }

    /**
     * Is Email
     * 
     * Check string is email
     * 
     * @param string $input String to check
     * @return bool
     */
    public static function isEmail(string $input = ""):bool {

        return filter_var($input, FILTER_VALIDATE_EMAIL) ? true : false;

    }

    /**
     * Is Ip Adress
     * 
     * Check string is ip adress
     * 
     * @param string $input String to check
     * @return bool
     */
    public static function isIpAddress(string $input = ""):bool {

        return filter_var($input, FILTER_VALIDATE_IP) ? true : false;

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
                    "custom_code"   =>  "validate-070",
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

        // Check value
        if(!filter_var($input['value'], FILTER_VALIDATE_URL))

            # New Exception
            throw new CrazyException(
                "\”".$input["value"]."\” isn't a valid url...",
                500,
                [
                    "custom_code"   =>  "validate-070",
                ]
            );

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

    /**
     * Is Valid File
     * 
     * Check if the file is a valid file
     * 
     * @param array give content from file $_FILE
     * @param bool
     */
    public static function isValidFile(array $file = [], bool $exception = false):bool {

        # Set result
        $result = true;

        try {
    
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($file['error']) ||
                is_array($file['error'])
            ) {
                throw new Exception('File contains errors');
            }
        
            // Check $file['error'] value.
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('No file sent');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('Exceeded filesize limit');
                default:
                    throw new Exception('Unknown errors');
            }
        
            // You should also check filesize here. 
            if ($file['size'] > 1000000) {
                throw new Exception('Exceeded filesize limit');
            }
        
            // DO NOT TRUST $file['mime'] VALUE !!
            // Check MIME Type by yourself.
            /* $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($file['tmp_name']),
                array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                ),
                true
            )) {
                throw new Exception('Invalid file format');
            } */
        
            // You should name it uniquely.
            // DO NOT USE $file['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            /* if (!move_uploaded_file(
                $file['tmp_name'],
                sprintf('./uploads/%s.%s',
                    sha1_file($file['tmp_name']),
                    $ext
                )
            )) {
                throw new Exception('Failed to move uploaded file.');
            } */
        
        } catch (Exception $e) {
        
            # Check Exception
            if($exception)
            
                # Set result
                throw new CrazyException($e->getMessage(), 500);

            else

                # Set result
                $result = false;
        
        }
        
        # Return result
        return $result;

    }

    /**
     * Is Regex
     * 
     * Check if string given is regex
     * 
     * @param string $pattern
     * @return bool
     */
    public static function isRegex(string $pattern):bool {

        # Set result
        $result = false;

        # Check pattern
        if($pattern){

            # Test the pattern by using it in preg_match
            $result = @preg_match($pattern, '') !== false;

        }
    
        # Return result
        return $result;

    }

    /**
     * Is Semantic Versioning
     * 
     * @param string $version
     * @return bool
     */
    public static function isSemanticVersioning(string $version):bool {

        # Set result
        $result = false;

        # Set regex
        $regex = 
            '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)' .
            '(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?' .
            '(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/'
        ;

        # Check regex 
        if(preg_match($regex, $version) > 0)

            # Set result
            $result = true;

        # Return regex
        return $result;

    }

    /**
     * Is Mobile Phone
     * 
     * @source https://stackoverflow.com/questions/22378736/regex-for-mobile-number-validation
     * 
     * @param string|int $input
     * @return bool
     */
    public static function isMobilePhone(string|int $input):bool {

        # Set result
        $result = false;

        # Check input
        if($input){

            # Set pattern
            $pattern = '/^(\+\d{1,3}[- ]?)?\d{10}$/';

            # Regex test
            if(preg_match($pattern,(string)$input))

                # Set result
                $result = true;

        }

        # Return result
        return $result;
           
    }

}