<?php declare(strict_types=1);
/**
 * String
 *
 * Usefull class for manipulate strings
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\String;

/**
 * Dependances
 */
use CrazyPHP\Library\Array\Arrays;

/**
 * Jwt
 *
 * Methods for manipulate Jwt
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Jwt {

    /** Private parameters
     ******************************************************
     */

    /** @var string $_token */
    private string $_token;

    /** @var array $_info */
    private array $_info = [];

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param string $color (Can be hsl / rgb / hex...)
     * @return self
     */
    public function __construct(string $token) {
        
        # Set token
        $this->_token = trim($token);

        # Set info
        $this->_getInfo();

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Get
     * 
     * If key empty : array<string,mixed>  ['header'=>..., 'payload'=>...] or ['error'=>...]
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key = ""):mixed {

        # Set result
        $result = $key
            ? Arrays::getKey($this->_info, $key)
            : $this->_info
        ;

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Get Info
     * 
     * Decode the JWT and return header + payload
     *
     * @return void
     */
    private function _getInfo():void {

        # Chck info
        if(empty($this->_info)){

            # Set result
            $result = [];

            # Set parts
            $parts = explode('.', $this->_token);

            # Check result
            if(count($parts) !== 3)

                $result = ['error' => 'Not a valid JWT (expected 3 parts).'];
            
            # If valid
            else{

                # Set header and payload encoded
                [$header64, $payload64] = $parts;

                # Decode parts
                $decodePart = static function (string $data):?array {

                    # Set result
                    $result = null;

                    # Set reminder
                    $remainder = strlen($data) % 4;

                    # Check reminder
                    if($remainder)

                        # Set data
                        $data .= str_repeat('=', 4 - $remainder);

                    # Set decoded
                    $decoded = base64_decode(strtr($data, '-_', '+/'));

                    # Set result
                    $result = json_decode($decoded, true);

                    # Return result
                    return $result;

                };

                # Set header
                $header = $decodePart($header64);

                # Set payload
                $payload = $decodePart($payload64);

                # Chek header and payload
                if(!is_array($header) || !is_array($payload))

                    # Set result
                    $result = ['error' => 'Unable to decode header or payload.'];

                else

                    # Set result
                    $result = [
                        'header'  => $header,
                        'payload' => $payload,
                    ];

            }

            # Set info 
            $this->_info = $result;

        }

    }

}
