<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of request
 *
 * @author juanma
 */
class Request {

	private static $_instance = null;

	private function __construct() {}

	/**
	 * @return Request
	 */
	public static function instance() {
		if (!isset(self::$_instance))
			self::$_instance = new Request ();
		return self::$_instance;
	}

	/**
	 * Obtains a value from default get method.
	 * This method should not be used, try instead the position ordered URL method
	 * with controller function parameters.
	 *
	 * @param string $parameterName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function get($parameterName, $defaultValue = false) {
		return isset($_GET[$parameterName]) ? $_GET[$parameterName] : $defaultValue;
	}

	/**
	 * Obtains a value from default post method.
	 *
	 * @param string $parameterName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function post($parameterName, $defaultValue = false) {
		return isset($_POST[$parameterName]) ? $_POST[$parameterName] : $defaultValue;
	}

	/**
	 * Complex POST parameter existance testing.
	 * If the parameter doesn't exists, you'll receive 0.
	 * If the parameter exists but is an empty string, you'll receive 1.
	 * If the parameter exists and isn't an empty string you'll get 2.
	 *
	 * Done this way so you can not only test for existance of one parameter but
	 * also check if it's empty at the same time (taking in account that most
	 * request parameters are just strings) and then you have a quick "required"
	 * validation method for a parameter.
	 *
	 * @param string $paramName
	 * @return integer
	 */
	public function postExists($paramName) {
		return $this->inputExists($paramName, $_POST);
	}

	/**
	 * @param string $paramName
	 * @param array $array
	 * @return integer
	 */
	private function inputExists($paramName, $array) {
		$return = 0;
		if (is_array($array) && isset($array[$paramName])) {
			$value = $array[$paramName];
			if (is_string($value) && trim($value) == '')
				$return = 1;
			else
				$return = 2;
		}
		return $return;
	}

	public function postAll() {
		//just for convenience... evaluate using some controls.
		return $_POST;
	}

	/**
	 * Function used to redirect the browser to another location.
	 *
	 * @param string $url
	 * @param integer $method
	 */
	public static function redirect($url, $method = false) {
		if (headers_sent ()) {
			echo "<script>document.location.href='{$url}';</script>\n";
		} else {
			if (!$method) {
				header("Location: {$url}");
			} else {
				header("Location: {$url}", false, $method);
			}
		}
		//I know this is not the right way and that I told gonza that you shouldn't
		//do it... but I'm not in the mood to think today... =(
		exit;
	}

	/**
	 * Gets the http referer for last request.
	 *
	 * @return string
	 */
	public static function referer() {
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
	}

	/**
	 * Moves an uploaded file identified by the input name to the destination
	 * specified (exclude actual file name from the destination directory).
	 * If you want to override the original filename with one of your own, set
	 * $newName to the new name value.
	 *
	 * @param string $inputName
	 * @param string $destination
	 * @param mixed $newName If not set, will use the uploaded file original name
	 * @return boolean
	 */
	public static function moveFile($inputName, $destination, $newName = false) {
		$success = false;
		if (self::checkFile($inputName)) {
			$name = $destination;
			$name .= $newName ? $newName : $_FILES[$inputName]['name'];
			$success = move_uploaded_file($_FILES[$inputName]['tmp_name'], $name);
		}
		return $success;
	}

	/**
	 * Checks if the given input name has generated an uploaded file.
	 *
	 * @param boolean $inputName
	 * @return boolean
	 */
	public static function checkFile($inputName) {
		return isset($_FILES[$inputName]);
	}

	/**
	 * Returns original uploaded file name.
	 *
	 * @param string $inputName
	 * @return mixed
	 */
	public static function getFileName($inputName) {
		 return (self::checkFile($inputName)
				? $_FILES[$inputName]['name']
				: false);
	}

	/**
	 * Checks if the uploaded file is of the $mimeType type.
	 *
	 * @param string $inputName
	 * @param string $mimeType (Ex: image/jpeg, application/zip, text/html, text/plain ...)
	 * @return boolean
	 */
	public static function fileIs($inputName, $mimeType) {
		$success = false;
		if (self::checkFile($inputName))
			return trim(strtolower($_FILES[$inputName]['type'])) == trim(strtolower($mimeType));

		return $success;
	}

	public static function getFileSize($inputName) {
		return (self::checkFile($inputName) ? $_FILES[$inputName]['size'] : false);
	}

	public static function fileError($inputName) {
		return self::checkFile($inputName) ? $_FILES[$inputName]["error"] != 0 : true;
	}

}
?>
