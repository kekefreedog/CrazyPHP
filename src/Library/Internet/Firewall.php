<?php declare(strict_types=1);
/**
 * Model
 *
 * Classes utilities for Internet
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Internet;

/**
 * Dependances
 */
use Psr\Http\Message\ServerRequestInterface;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;

/**
 * Firewall
 *
 * Class to manage IP firewall (whitelist / blacklist)
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Firewall {

    /** Public static methods
     ******************************************************
     */

    /**
     * Firewall
     * 
     * Check client IP against whitelist / blacklist
     * 
     * @param ServerRequestInterface $request PSR-7 request
     * @return void
     */
    public static function firewall(ServerRequestInterface $request):void {

        # Get client IP
        $ip = self::getClientIp($request);

        # Check firewall
        if($ip === null || self::isBlocked($ip))

            # Deny access
            self::deny();

    }

    /**
     * Whitelist
     * 
     * Define allowed IPs / CIDR ranges
     * 
     * @return array
     */
    public static function whitelist():array {

        # Set result
        $result = Config::getValue("Firewall.host.allowed");

        # Return result
        return $result;

    }

    /**
     * Blacklist
     * 
     * Define denied IPs / CIDR ranges
     * 
     * @return array
     */
    public static function blacklist():array {

        # Set result
        $result = Config::getValue("Firewall.host.excluded");

        # Return result
        return $result;

    }

    /**
     * Is Blocked
     * 
     * Check if IP should be blocked
     * 
     * @param string $ip Client IP
     * @return bool
     */
    public static function isBlocked(string $ip):bool {

        # Set result
        $result = false;

        # Get black list
        $blocklist = self::blacklist();

        # Blacklist always wins
        if(!empty($blocklist) && self::_match($ip, $blocklist))

            # Set result
            $result = true;

        else{

            # Get white list
            $whitelist = self::whitelist();
                
            # If whitelist exists, IP must match
            if(!empty($whitelist) && !self::_match($ip, $whitelist)){

                $result = false;

            }

        }

        # Return result
        return $result;

    }

    /**
     * Get Client IP
     * 
     * Retrieve client IP from request
     * 
     * @param ServerRequestInterface $request
     * @return string|null
     */
    public static function getClientIp(ServerRequestInterface $request):?string {

        # Get current ip adress
        return $request->getServerParams()['REMOTE_ADDR'] ?? null;

    }

    /**
     * Deny
     * 
     * Deny access
     * 
     * @return void
     */
    public static function deny():void {

        # New error
        throw new CrazyException("Request forbidden", 403, [
            "custom_code"   =>  "firewall-010"
        ]);

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Match
     * 
     * Match IP against rules (IPv4 + CIDR)
     * 
     * @param string $ip Client IP
     * @param array $rules Rules list
     * @return bool
     */
    private static function _match(string $ip, array $rules):bool {

        # Set result
        $result = false;

        # Convert IP to long
        $ipLong = ip2long($ip);

        # Check ip long
        if($ipLong === false)

            # Set result
            $result = false;

        # Loop rules
        else foreach($rules as $rule){

            # Check if "*"
            if($rule === "*"){

                # Set result
                $result = true;

                # Break
                break;

            }else
            # Exact match
            if(strpos($rule, '/') === false){

                # Check match
                if($ip === $rule){

                    # Set result
                    $result = true;

                    # Break
                    break;

                }else

                    # Continue
                    continue;

            }else{

                # CIDR match
                [$subnet, $maskBits] = explode('/', $rule, 2);

                # Get long
                $subnetLong = ip2long($subnet);

                # Get mask
                $mask = -1 << (32 - (int)$maskBits);

                # Check results
                if(($ipLong & $mask) === ($subnetLong & $mask)){

                    # Set result
                    $result = true;

                    # Break
                    break;

                }else

                    # Continue
                    continue;

            }

        }

        # Return result
        return $result;

    }

}
