<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cookie
 *
 * @author juanma
 */
class Cookie {

	private static $_instance;

	private function __construct() {}

	/**
	 * @return Cookie
	 */
	public static function instance() {
		if (!isset(self::$_instance))
			self::$_instance = new Cookie();
		return self::$_instance;
	}

	public function setCookie($name, $data, $expire = 31536000) {
		$res =  setcookie($name, $data, time()+$expire, "/", "");
		if ($res)
			$_COOKIE[$name] = $data;
		return $res;
	}

	public function getCookie($name) {
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
	}

	public function eraseCookie($name) {
		$_COOKIE[$name] = '';
		return setcookie($name, "", 1, "/", "");
	}
}
?>
