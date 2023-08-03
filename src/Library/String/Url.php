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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\String;

/**
 * Form
 *
 * Methods for generate CLI form
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Url {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Check Url
     * 
     * @source https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php
     * 
     * Ingest data
     * 
     * @param string $url Url to check
     * @param bool $validUrl Check if url is valid
     * @return bool
     */
    public static function check(string $url = "", bool $validUrl = false):bool {

        # First do some quick sanity checks:
        if(!$url || !is_string($url))
            return false;

        # Quick check url is roughly a valid http request: ( http://blah/... ) 
        if(!preg_match('/^http(s)?:\/\/[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) )
            return false;

        # Check if valid url required
        if(!$validUrl)
            return true;

        # Noticed next bit could be slow:
        if(Url::getHttpResponseCode_using_curl($url) != 200)

            // If(Url::getHttpResponseCode_using_getheaders($url) != 200){  // use this one if you cant use curl
            return false;

        // all good!
        return true;

    }

    /**
     * @depending check
     * 
     * @source https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php
     * 
     */
    public static function getHttpResponseCode_using_curl($url, $followredirects = true){
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER         ,true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);    // catch output (do NOT print!)
        if($followredirects){
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        }else{
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
        }
        //      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        //      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        //      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);
        if(@curl_errno($ch)){   // should be 0
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);
        return $code;
    }
    
    /**
     * @depending check
     * 
     * @source https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php
     * 
     */
    public static function getHttpResponseCode_using_getheaders($url, $followredirects = true){
        // returns string responsecode, or false if no responsecode found in headers (or url does not exist)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $headers = @get_headers($url);
        if($headers && is_array($headers)){
            if($followredirects){
                // we want the last errorcode, reverse array so we start at the end:
                $headers = array_reverse($headers);
            }
            foreach($headers as $hline){
                // search for things like "HTTP/1.1 200 OK" , "HTTP/1.0 200 OK" , "HTTP/1.1 301 PERMANENTLY MOVED" , "HTTP/1.1 400 Not Found" , etc.
                // note that the exact syntax/version/output differs, so there is some string magic involved here
                if(preg_match('/^HTTP\/\S+\s+([1-9][0-9][0-9])\s+.*/', $hline, $matches) ){// "HTTP/*** ### ***"
                    $code = $matches[1];
                    return $code;
                }
            }
            // no HTTP/xxx found in headers:
            return false;
        }
        // no headers :
        return false;
    }

	/** 
     * Decompose
     * 
     * Decommpose path
     * - Exemple : "/toto/titi/tata" => [ "/toto/", "/toto/titi/" ]
     * 
     * @param string $path Path to process
     * @param string $delimiter Delimiter for decomposer path
	 * @return array
	 */
	public static function decompose(string $path = "", string $delimiter = "/"):array{

		# Check string
		if(!$path || !$delimiter)
			return [];

		# Explode string
		$explode = explode($delimiter, trim($path, $delimiter));

		# Declare result
		$result = [];

		# Remove last value in array
		array_pop($explode);

		# Boucle
		while(!empty($explode)):

			# Push value in result
			$result[] = $delimiter.implode($delimiter, $explode).$delimiter;

			# Remove last value in array
			array_pop($explode);

		endwhile;

		# Return result 
		return $result;

	}

}