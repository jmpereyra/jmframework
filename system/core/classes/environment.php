<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Provides acces to various util environment vars. (Get IP, user agent, etc).
 *
 * @author juanma
 */
class Environment {

	public static function callerIp() {
		$ip = getenv("REMOTE_ADDR");
		return (boolean) $ip ? $ip : "";
	}

	public static function calledURL() {
		$path = getenv("PATH_INFO");
		$path = (boolean) $path ? substr($path, 1) : false;
		return PROTOCOL_METHOD.URL_BASE.((boolean)$path ? $path : "");
	}

	public static function callerUserAgent() {
		$agent = getenv("HTTP_USER_AGENT");
		return (boolean)$agent ? $agent : "";
	}

	public static function getVar($name) {
		$val = getenv($name);
		return (boolean)$val ? $val : "";
	}
}
