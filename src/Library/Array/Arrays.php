<?php declare(strict_types=1);
/**
 * Array
 *
 * Classes for manipulate arrays
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Array;

/**
 * Arrays
 *
 * Methods for interacting with array
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Arrays{

    /** Public Static Methods
     ******************************************************
     */

	/** 
	 * Stretch array
	 * 
	 * Use separator to convert 1 dimension array to multi dimension array
	 * 
     * @param array $array array to process
	 * @param string $separator by default "_"
	 * @return array
	 */
	public static function stretch(array $array = [], string $separator = "_"):array {

		# Declare result as array
		$result = [];
	
		# Check array
		if(is_array($array) && !empty($array))
		
			# Iteration
			foreach($array as $k => $v)

				# Check if separator in key
				if(!is_string($k) && strpos( (string) $k, $separator) !== false){

					# Explode key
					$explode = explode($separator, (string) $k, 2);
					
					# Declare new dimension array
					if(!isset($result[$explode[0]]))
						$result[$explode[0]] = [];
						
					# Set value and call function recursively
					$result[$explode[0]] = array_merge_recursive(
						$result[$explode[0]],
						self::stretch([$explode[1]=>$v], $separator)
					);
				
				}else
					
					# Set value of the current key
					$result[$k] = $v;
	
		# Return array
		return $result;
	
	}

    /** 
	 * Filter array by key value
     * 
	 * Find parameter than some child parameter correspond to key value
	 * 
	 * @param array $array Array to process
	 * @param any $key Child parameter to use in filter
	 * @param any $keyValue Child parameter value's to use in filter
	 * @return array 
     */
    public static function filterByKey(array $array = [], $key = "", $keyValue = ""):array {

		# Process and return result
		return array_filter(
			$array, 
			function ($var) use ($keyValue, $key) {
				return ($var[$key] == $keyValue);
			}
		);

	}

    /**
     * Merge multidimensional array
	 * 
     * @param bool $createIfNotExists Create parameter if not exists in precedents arrays
     * @param array ...$inputs All arrays to merge
     * @return array
     */
    public static function mergeMultidimensionalArrays(bool $createIfNotExists = false, ...$inputs = []):array {

		# Declare result
		$result = [];

		# Check inputs
		if(empty($inputs))
			return $result;

		# Iteration inputs
		foreach($inputs as $input){

			# Check inputs is array and not empty
			if(!is_array($input) || empty($input))
				continue;

			###

		}
		

    }

}