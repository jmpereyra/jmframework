<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of loader
 *
 * @author juanma
 */
class Loader {

	/**
	 * Controller names are cast to lower case, remember naming your files
	 * accordingly.
	 * @param string $controllerName
	 */
	public static function controller($controllerName) {
		$controllerName = strtolower($controllerName);
		file_exists(CONTROLLERS.DIRECTORY_SEPARATOR."{$controllerName}.php") or die("Controller {$controllerName} not found.");
		require_once CONTROLLERS.DIRECTORY_SEPARATOR."{$controllerName}.php";
	}
	
	/**
	 * Controller names are cast to lower case, remember naming your files
	 * accordingly.
	 * @param string $controllerName
	 */
	public static function mainController($controllerName) {
		$controllerName = strtolower($controllerName);
		if (!file_exists(CONTROLLERS.DIRECTORY_SEPARATOR."{$controllerName}.php")) 
			return false;
		require_once CONTROLLERS.DIRECTORY_SEPARATOR."{$controllerName}.php";
		return true;
	}

	/**
	 * Model names are cast to lower case, remember naming your files
	 * accordingly.
	 * @param string $modelName
	 */
	public static function model($modelName) {
		$modelName = strtolower($modelName);
		file_exists(MODELS.DIRECTORY_SEPARATOR."{$modelName}.php") or die("Model {$modelName} not found.");
		require_once MODELS.DIRECTORY_SEPARATOR."{$modelName}.php";
	}

	/**
	 * Util names are cast to lower case, remember naming your files
	 * accordingly.
	 * @param string $utilName
	 */
	public static function util($utilName) {
		$utilName = strtolower($utilName);
		file_exists(UTILS.DIRECTORY_SEPARATOR."{$utilName}.php") or die("Util {$utilName} not found.");
		require_once UTILS.DIRECTORY_SEPARATOR."{$utilName}.php";
	}

	/**
	 * Loads SwiftMailer library to send mails.
	 * Thanks to the Swift Mailer guys!!!
	 */
	public static function mailing() {
		require_once SYSTEM_MAILER.DIRECTORY_SEPARATOR."swift_required.php";
	}

	public static function other($dir, $fileName) {
		$dir = strtolower($dir);
		$fileName = strtolower($fileName);
		file_exists(APP_PATH.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$fileName.".php") or die("Requested file not found");
		require_once APP_PATH.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$fileName.".php";
	}
}