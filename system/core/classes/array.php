<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Util functions for array handling.
 *
 * @author Juan Manuel Pereyra
 */
class ArrayHelper {
    
	/**
	 * Gets a value from the given array or returns the default value if parameter
	 * is not set.
	 * 
	 * @param string $param	Param name or offset to seek
	 * @param array $array	The array to seek into
	 * @param mixed $default Value to return if not found
	 * @return mixed 
	 */
	public static function getValue($param, $array, $default = false) {
		return is_array($array) ? (isset($array[$param]) ? $array[$param] : $default) : $default;
	}

	/**
	 * Checks if the required parameter name has a value associated in the array.
	 *
	 * @param string $param	Param name or offset to seek
	 * @param array $array	The array to seek into
	 * @param boolean $asInt Return the found status as an integer
	 * @return mixed
	 */
	public static function valueExists($param, $array, $asInt = false) {
		return $asInt ? (isset($array[$param]) ? 1 : 0) : (boolean)isset($array[$param]) ;
	}

	/**
	 * Gets a value from the given array or returns the default value if parameter
	 * is not set or if is considered empty (for a string or array currently).
	 *
	 * @param string $param	Param name or offset to seek
	 * @param array $array	The array to seek into
	 * @param mixed $default Value to return if not found
	 * @return mixed
	 */
	public static function getValueNotEmpty($param, $array, $default = false) {
		if (is_array($array)) {
			if (isset($array[$param])) {
				$value = $array[$param];
				if (is_string($value) && trim($value) == "")	//string validation
					return $default;
				if (is_array($value) && count($value) == 0)		//array validation
					return $default;
				return $value;
			}
		}
		return $default;
	}

	/**
	 * Turns an associative array into an object using the keys as property names
	 * and values as values.
	 * 
	 * @param array $array
	 * @return stdClass
	 */
	public static function toObject($array) {
		$obj = new stdClass();
		foreach($array as $key => $val) {
			$obj->{$key} = is_array($val) ? self::toObject($val) : $val;
		}
		return $obj;
	}
}
?>
