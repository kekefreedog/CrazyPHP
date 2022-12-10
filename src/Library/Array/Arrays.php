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
 * Dependances
 */

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
				if(is_string($k) && strpos( (string) $k, $separator) !== false){

					# Explode key
					$explode = explode($separator, (string) $k, 2);

					# Check explode first value not empty
					if(!$explode[0])

						$explode[0] = 0;

					# Declare new dimension array
					if(!isset($result[$explode[0]]))
						$result[$explode[0]] = [];
						
					# Set value and call function recursively
					$result[$explode[0]] = self::mergeMultidimensionalArrays(
						true,
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
				return (isset($var[$key]) ? $var[$key] == $keyValue : []);
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
    public static function mergeMultidimensionalArrays(bool $createIfNotExists = false, array ...$inputs):array {

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

			# Check if result is empty
			if(empty($result)){

				# Set result
				$result = $input;

				# Continue iteration
				continue;

			}

			# Iteration items in input
			foreach($input as $key => $item){

				# Check if value have to bet set or create
				if(isset($result[$key]) || $createIfNotExists)

					# Check item is array
					if(is_array($item)){

						# Prepare old
						$resultLegacy = isset($result[$key]) ?
							( 
								is_array($result[$key]) ?
									$result[$key] : 
										[]
							) :
								[];

						# Check createIfNotExists or not empty current result
						if(!empty($resultLegacy) || $createIfNotExists)

							# Loop
							$result[$key] = self::mergeMultidimensionalArrays(
								$createIfNotExists,
								$resultLegacy,
								$item
							);

					}else{

							# Push new value
							$result[$key] = $item;

					}

			}

		}
		
		# Return result
		return $result;

    }

	/**
	 * Parse Key
	 * 
	 * Convert "hello/toto" or "hello.toto" to ["hello"]["toto"]
	 * 
	 * @param string $key Key to parse
	 * @param array $array
	 * @param string|array $separator
	 * @return
	 */
	public static function parseKey(string $key = "", array $array = [], string|array $separators = [".", "/"]) {

		# Set result
		$result = null;

		# Check key, array, separator
		if(empty($key) || empty($separators) || empty($array))

			# Return
			return $result;

		# Replace separator
		$key = str_replace($separators, "___", $key);

		# Explode
		$keyExploded = explode("___", $key);

		# Set result
		$resultTemp = $array;

		# Get value
		$i=0;while(isset($keyExploded[$i])){

			# Check
			if(isset($resultTemp[$keyExploded[$i]]))

				# Set value
				$resultTemp = $resultTemp[$keyExploded[$i]];

			# Return
			else

				# Stop function
				return $result;

		$i++;}

		# Update result
		$result = $resultTemp;

		# Return result
		return $result;

	}

	/**
	 * fill
	 * 
	 * Fille array with parsed key
	 * 
	 * @param array &$array Array to process
	 * @param string $key Key to parse
	 * @param $value Value to fill in array
	 * @param string|array $separators Separator for key
	 * @return void
	 */
	public static function fill(array &$array = [], string $key = "", $value = null,  string|array $separators = [".", "/"]):void {

		# Check key, array, separator
		if(empty($key))

			# Return
			return;

		# Replace separator
		$key = str_replace($separators, "___", $key);

		# Explode
		$keyExploded = explode("___", $key);

		# Set current array depth
		$arrayDepth = &$array;

		# Get value
		$i=0;while(isset($keyExploded[$i])){

			# Check
			if(!isset($arrayDepth[$keyExploded[$i]]))

				# Create value
				$arrayDepth[$keyExploded[$i]] = null;

			# Update arrayDepth
			$arrayDepth = &$arrayDepth[$keyExploded[$i]];

		$i++;}

		# Push value to the current array depth
		$arrayDepth = $value;

	} 

	/**
	 * Change Key Case Recursively
	 * 
	 * @source https://www.php.net/manual/en/function.array-change-key-case.php#114914
	 * 
	 * @param array $array
	 * @param string $case CASE_UPPER or CASE_LOWER  
	 * @return array
	 */
	public static function changeKeyCaseRecursively(array $array = [], int $case = CASE_LOWER):array {
		$case = in_array($case, [CASE_UPPER, CASE_LOWER]) ? $case : 1; 
		return array_map(function($item){
			if(is_array($item))
				$item = static::changeKeyCaseRecursively($item);
			return $item;
		},array_change_key_case($array, $case));
	}

}