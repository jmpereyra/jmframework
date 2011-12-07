<?php

defined("SYSTEM") or die("Can't execute directly");

/**
 * Custom session manager.
 * Avoid mixing use of this class and direct $_SESSION access. This class puts
 * the data serialized into the env var, and unserializes it to return, mixing
 * use would result in obtaining serialized data or trying to deserialize data
 * that isn't serialized.
 *
 * @author juanma
 */
class Session {

	private static $_instance;

	private function __construct() {

	}

	/**
	 * @return Session
	 */
	public static function instance() {
		if (!isset(self::$_instance))
			self::$_instance = new Session ();
		return self::$_instance;
	}

	/**
	 * Call this function to ensure session is open before performing any session
	 * operation. Won't be called in constructor to avoid opening innesesary
	 * session cookies whenever a controller is called.
	 */
	private function checkLifetime() {
		if (session_id () == "")
			session_start();
	}

	public function setAttribute($name, $value) {
		$this->checkLifetime();
		$_SESSION[$name] = serialize($value);
	}

	public function getAttribute($name, $defaultValue = false) {
		$this->checkLifetime();
		$value = isset($_SESSION[$name]) ? unserialize($_SESSION[$name]) : $defaultValue;
		return $value;
	}

	public function attributeExists($name) {
		$this->checkLifetime();
		return isset($_SESSION[$name]);
	}
	
	public function destroy() {
		$this->checkLifetime();

		return session_destroy();
	}
}