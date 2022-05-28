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

	/** Stretch array
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
				if(strpos($k, $separator) !== false){

					# Explode key
					$explode = explode($separator, $k, 2);
					
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

}