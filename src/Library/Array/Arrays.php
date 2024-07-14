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
 * @copyright  2022-2024 Kévin Zarshenas
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
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Arrays{

    /** Public Static Methods | Exploration
     ******************************************************
     */



    /**
     * Has
     * 
     * Check if file has parameter
     * 
	 * @param array $array content
     * @param string $input Name in the array
     * 
     * @return array
     */
    public static function has(array $array, string $input = "") :bool {

        # Declare result
        $result = false;

        # Check input
        if(!$input)

            # Return result
            return $result;

        # Replace separator
        $input = str_replace(static::SEPARATOR, "___", $input);

        # Get value
        $valueToCheck = Arrays::parseKey($input, $array, static::SEPARATOR);

        # Check value to check
        if($valueToCheck !== null)

            # Update result
            $result = true;

        # Return result
        return $result;

    }

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
	 * @param mixed $key Child parameter to use in filter
	 * @param mixed $keyValue Child parameter value's to use in filter
	 * @return array 
     */
    public static function filterByKey(array $array = [], string $key = "", mixed $keyValue = ""):array {

		# Process and return result
		return array_filter(
			$array, 
			function ($var) use ($keyValue, $key) {
				return (isset($var[$key]) ? $var[$key] == $keyValue : []);
			}
		);

	}

	/**
	 * Remove By Key
	 * 
	 * Remove items from an array where the specified parameter is equal to the given value.
	 *
	 * @param array $array The input array.
	 * @param mixed $key The parameter to check.
	 * @param mixed $keyValue The value to compare against.
	 * @return array The filtered array with items removed.
	 */
	public static function removeByKey(array $array = [], mixed $key = "", mixed $keyValue = ""):array {

		# Set result
		$result =  array_filter($array, function($item) use ($key, $keyValue) {

			# Check if item is an associative array and parameter exists
			if(is_array($item) && isset($item[$key]))

				# Return match
				return $item[$key] !== $keyValue;

			# Check if item is an object and parameter exists as property
			if(is_object($item) && isset($item->$key))

				# Return match
				return $item->$key !== $keyValue;

			# If parameter does not exist, do not remove the item
			return true;

		});

		# Reset keys
		$result = array_values($result);

		# Return result
		return $result;

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
     * Merge multidimensional array bis
	 * 
     * @param bool $createIfNotExists Create parameter if not exists in precedents arrays
     * @param array ...$inputs All arrays to merge
     * @return array
     */
	public static function mergeMultidimensionalArraysBis(bool $createIfNotExists = false, array ...$inputs): array {

		# Take the first array as the starting point
		$merged = array_shift($inputs);
	
		foreach ($inputs as $array) {
			foreach ($array as $key => $value) {
				if (isset($merged[$key]) && is_array($merged[$key]) && is_array($value)) {
					// If the key exists in both arrays and both values are arrays, merge them
					$merged[$key] = self::mergeMultidimensionalArrays($createIfNotExists, $merged[$key], $value);
				} else {
					if ($createIfNotExists && isset($merged[$key])) {
						// If createIfNotExists is true and the key exists, create a new key
						$merged[] = $value;
					} else {
						// Otherwise, overwrite the value in the merged array
						$merged[$key] = $value;
					}
				}
			}
		}
	
		return $merged;
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
		return array_map(function($item)use($case){
			if(is_array($item))
				$item = static::changeKeyCaseRecursively($item, $case);
			return $item;
		},array_change_key_case($array, $case));
	}

	/**
	 * Remove Column
	 * 
	 * @param array $array Array to process
	 * @param string $column_key Name of key of the column to delete
	 * @return void
	 */
	public static function removeColumn(array &$array = [], string|array $column_key = "") {

		# Check column key
		if($column_key && !empty($column_key))

			# Warlk into array
			array_walk(
				$array, 
				function (&$v) use ($column_key) {
					if(is_string($column_key))
						unset($v[$column_key]);
					else
						foreach($column_key as $key)
							unset($v[$key]);
				}
			);

	}

	/**
	 * Get Key
	 * 
	 * Extract key in array
	 * 
	 * @param array $array Array to process
	 * @param string $key Key of the item to extract "titi.toto"
	 * @param string|array $separators Separator for key
	 * @return mixed
	 */
	public static function getKey(array $array = [], string $key = "", string|array $separators = [".", "/"]):mixed {

		# Set result
		$result = $array;

		# Check array
		if(empty($array) || empty($key))

			# Return result
			return $result;

		# Replace separator
		$key = str_replace($separators, "___", $key);

		# Explode
		$keyExploded = explode("___", $key);

		# Set current array depth
		$arrayDepth = &$array;

		# Get value
		$i=0;while(isset($keyExploded[$i]))

			# Check
			if(isset($arrayDepth[$keyExploded[$i]])){

				# Set value
				$arrayDepth = $arrayDepth[$keyExploded[$i]];

				# Increment i
				$i++;

			}else{

				# Set result as null
				$arrayDepth = null;

				# Stop loop
				break;

			}

		# Set result
		$result = $arrayDepth;

		# Return result
		return $result;
		
	}

	/**
	 * Has Key
	 * 
	 * Check if key in array
	 * 
	 * @param array $array Array to process
	 * @param string $key Key of the item to extract "titi.toto"
	 * @param string|array $separators Separator for key
	 * @return bool
	 */
	public static function hasKey(array $array = [], string $key = "", string|array $separators = [".", "/"]):bool {

		# Set result
		$result = false;

		# Check array
		if(empty($array) || empty($key))

			# Return result
			return $result;

		# Replace separator
		$key = str_replace($separators, "___", $key);

		# Explode
		$keyExploded = explode("___", $key);

		# Set current array depth
		$arrayDepth = &$array;

		# Get value
		$i=0;while(isset($keyExploded[$i]))

			# Check
			if(isset($arrayDepth[$keyExploded[$i]])){

				# Set value
				$arrayDepth = $arrayDepth[$keyExploded[$i]];

				# Increment i
				$i++;

			}else{

				# Set result as null
				$arrayDepth = null;

				# Stop loop
				break;

			}

		# Check $arrayDepth
		if($arrayDepth !== null)

			# Set result
			$result = true;

		# Return result
		return $result;
		
	}

	/**
	 * Set Key
	 * 
	 * Extract key in array
	 * 
	 * @param array &$array Array to process
	 * @param string $key Key of the item to extract "titi.toto"
	 * @param mixed $value Value to pish
	 * @param bool $createIfNotExists Create if not exists
	 * @param string|array $separators Separator for key
	 * @return bool
	 */
	public static function setKey(array &$array, string $key, mixed $value, bool $createIfNotExists = true, string|array $separators = [".", "/"]):bool {

		# Set result
		$result = false;

		# Check array
		if(empty($key))

			# Return result
			return $result;

		# Replace separator
		$key = str_replace($separators, "___", $key);

		# Explode
		$keyExploded = explode("___", $key);

		# Set current array depth
		$arrayDepth = &$array;

		# Push value 
		$pushValue = true;

		# Get value
		$i=0;while(isset($keyExploded[$i]))

			# Check
			if(isset($arrayDepth[$keyExploded[$i]])){

				# Set value
				$arrayDepth = &$arrayDepth[$keyExploded[$i]];

				# Increment i
				$i++;

			}else{

				# Check createIfNotExists
				if($createIfNotExists){

					# Create key on array
					$arrayDepth[$keyExploded[$i]] = [];

					# Set value
					$arrayDepth = &$arrayDepth[$keyExploded[$i]];

					# Increment i
					$i++;

				# Stop iteration
				}else{

					# Push value 
					$pushValue = false;

					# Set result as null
					$arrayDepth = null;

					# Stop loop
					break;

				}

			}

		# Chech push value
		if($pushValue){

			# Push value in $arrayDepth
			$arrayDepth = $value;

			# Set result
			$result = true;

		}

		# Return result
		return $result;
		
	}

	/**
	 * String Replace Recursively
	 * 
	 * Replace all occurrences of the search string with the replacement string
	 * 
	 * @param string|array $search 
	 * @param string $replace
	 * @param array $subject
	 * @return array
	 */
	public static function stringReplaceRecursively(string|array $search, string $replace, array &$subject):array {
		
		# Check search and subject
		if(empty($search) && empty($subject))

			# Return subject
			return $subject;

		else
		# Check search is string
		if(is_string($search)) 

			# Convert to array
			$search = [$search];

		# Iteration subject
		foreach($subject as &$value){

			# Iteration search
			foreach($search as $needle)

				# Check current value is string and contain needle
				if(is_string($value) && strpos($value, $needle) !== false)

					# Update value
					$value = str_replace($needle, $replace, $value);

				else
				# Check if value is array
				if(is_array($value))

					# Call itself
					static::stringReplaceRecursively($search, $replace, $value);
		}

		# Return subject
		return $subject;
	}

	/**
	 * Convert Strings to Integers
	 * 
	 * Concert all string compose only of integer to integer variable
	 * 
	 * @param array $array
	 * @return array
	 */
	public static function convertStringsToIntegers(array $array = []):array {

		# Check array
		if(!empty($array))

			# Iteration array
			foreach ($array as $key => $value)

				# Check if is array
				if (is_array($value))

					# Call himself
					$array[$key] = static::convertStringsToIntegers($value);

				else 
				# Check if string
				if ((is_string($value) && ctype_digit($value)) || (is_string($value) && preg_match('/^-\d+/', $value)))
					$array[$key] = (int) $value;

		# Return array
		return $array;

	}

	/**
	 * Flatten
	 * 
	 * Convert nested structure to a flat structure with separator
	 * 
	 * @param array $array to flatten
	 * @param string $prefix if needed
	 * @param string $separator by default "."
	 * @return array
	 */
	public static function flatten(array $array = [], string $prefix = '', string $separator = "."):array {
		
		# Prepare result
		$result = [];

		# Check array
		if(!empty($array))

			# Iteration array
			foreach ($array as $key => $value) {

				# New key
				$new_key = $prefix . (empty($prefix) ? '' : $separator) . $key;

				# Check if array
				if (is_array($value))

					# Recursively flatten the array
					$result = array_merge($result, static::flatten($value, $new_key, $separator));

				else
					
					# Set result
					$result[$new_key] = $value;
					
			}

		# Return result
		return $result;
	}

	/**
	 * Unflatten
	 * 
	 * Unflatten array
	 * 
	 * @param array $array
	 * @param string $separator
	 * @return array
	 */
	public static function unflatten(array $array = [], string $separator = '.'):array {

		# Declare result;
		$result = [];
	
		# Check array
		if(!empty($array))

			# Iteration array
			foreach($array as $key => $value) {

				# Split the key based on the separator
				$parts = explode($separator, $key);
		
				# Start from the root of the result array
				$temp = &$result;
		
				# Iterate over each part of the key except the last
				foreach ($parts as $part){

					# If the part isn't set, or isn't an array, initialize it as an array
					if (!isset($temp[$part]) || !is_array($temp[$part]))

						# Prepare temp
						$temp[$part] = [];
		
					# Move deeper into the result array
					$temp = &$temp[$part];

				}
		
				# Set the value at the deepest level
				$temp = $value;
			}
	
		# Return result
		return $result;

	}

    /** Public Constants
     ******************************************************
     */

    /** @const separator */
    public const SEPARATOR = ["/", ".", "___"];

}