<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of answer
 *
 * @author juanma
 */
class Answer {

	public static function request() {
		if (isset($_SERVER['PATH_INFO']))
			$_SERVER['ORIG_PATH_INFO'] = $_SERVER['PATH_INFO'];
		$pathInfo = isset($_SERVER['ORIG_PATH_INFO']) ? trim ($_SERVER['ORIG_PATH_INFO'], "/") : DEFAULT_CONTROLLER;
		$pathParts = explode("/", $pathInfo);
		count($pathParts) > 0 or die("Invalid URL.");
		
		if (!Loader::mainController($pathParts[0])) {
			$mapper = new Mapper(self::loadMappings());
			$mapper->map($pathParts[0], count($pathParts) > 1 ? array_slice($pathParts, 1) : array());
			exit;
		}
		
		$controllerName = ucfirst($pathParts[0]);
		unset($pathParts[0]);
		if (count($pathParts) > 0) {
			$function = $pathParts[1];
			unset ($pathParts[1]);
		} else {
			$function = "index";
		}
		$params = count($pathParts) > 0 ? $pathParts : false;
		try {
			$class = new ReflectionClass($controllerName);
			$controller = $class->newInstance();
			$method = $class->getMethod($function);
		
			if ($params) {
				$method->invokeArgs($controller, $params);
			} else {
				$method->invoke($controller);
			}
		} catch (ReflectionException $ex) {
			die ($ex->getMessage().", controller: {$controllerName}, method: {$function}");
		}
	}
	
	private static function loadMappings() {
		if (file_exists(CONFIG_FILES.DIRECTORY_SEPARATOR."mappings.php")) {
			include CONFIG_FILES.DIRECTORY_SEPARATOR."mappings.php";
			return $mappings;
		} else {
			return array();
		}
	}
}

/**
 * Description of mapper
 *
 * @author juanma
 */
class Mapper {

	private $mappings = array();
	
	public function __construct($mappings= array()) {
		$this->mappings = $mappings;
	}
	
	public function map($page, $additional = array()) {
		if ((boolean)count($additional) && isset($this->mappings[$mixedPage = $page."/".$additional[0]])) {
			unset($additional[0]);
			$this->loadPage($mixedPage, $additional);
		} else {
			if (isset($this->mappings[$page])) 
				$this->loadPage($page, $additional);
			else 
				Request::redirect(PROTOCOL_METHOD.URL_BASE);
			
		}
	}
	
	
	private function loadPage($page, $additional) {
		$data = $this->mappings[$page];
		try {
			Loader::controller($data["class"]);
			$class = new ReflectionClass($data["class"]);
			$controller = $class->newInstance();
			$method = $class->getMethod($data["function"]);
			$params = $data["params"];
			foreach ($additional as $param) 
				$params[] = str_replace(".html", "", $param);
			if (count($params) > $method->getNumberOfParameters() || count($params) < $method->getNumberOfRequiredParameters())
				Request::redirect(PROTOCOL_METHOD.URL_BASE);
			if (count($params)) 
				$method->invokeArgs($controller, $params);
			else 
				$method->invoke($controller);
			
		} catch (ReflectionException $ex) {
			Request::redirect(PROTOCOL_METHOD.URL_BASE);
		}
	}
}
