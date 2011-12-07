<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cache
 *
 * @author juanma
 */
class Cache {

	private static $_instance;

	private function __construct() {
		file_exists(CACHE_FILES) or die ("Inaccesible cache dir");
	}

	/**
	 * @return Cache
	 */
	public static function instance() {
		if (!isset (self::$_instance))
			self::$_instance = new Cache();
		return self::$_instance;
	}

	public function load($file, $inDir = "", $expire = 1) {
		$data = false;
		$inDir = DIRECTORY_SEPARATOR. ((boolean) $inDir ? $inDir.DIRECTORY_SEPARATOR : "");
		if ($this->checkFile(CACHE_FILES.$inDir.$file, $expire)) {
			$data = file_get_contents(CACHE_FILES.$inDir.$file);
			if ($data)
				$data = unserialize($data);
		}
		return $data;
	}

	public function save($data, $file, $inDir = "", $expire = 1, $override = false) {
		$result = false;
		$fullPath = CACHE_FILES.((boolean)$inDir ? DIRECTORY_SEPARATOR.$inDir : "").DIRECTORY_SEPARATOR.$file;
		if ($this->checkCreateDir(CACHE_FILES.DIRECTORY_SEPARATOR.$inDir) && (!$this->checkFile($fullPath, $expire) || $override)) {
			if (($file = fopen($fullPath, "w"))) {
				if (flock($file, LOCK_EX | LOCK_NB)) {
					$result = (boolean)fwrite($file, serialize($data));
					flock($file, LOCK_UN);
					chmod($fullPath, 0777);
				}
			}
		}
		return $result;
	}

	private function checkFile($fullPath, $expire) {
		$result = false;
		if (file_exists($fullPath)) {
			if (is_readable($fullPath)) {
				$mtime = filemtime($fullPath);
				if ($mtime) {
					$modified = time() - $mtime;
					$result = ($modified <= $expire * 60);
					if (!$result)
						@unlink($fullPath);
				} else 
					@unlink($fullPath);
			} else 
				@unlink($fullPath);
		}
		return $result;
	}

	private function checkCreateDir($fullPath) {
		if (file_exists($fullPath) && is_writable($fullPath) && is_dir($fullPath))
			return true;
		if (mkdir($fullPath, 0777, true) && chmod($fullPath, 0777))
			return true;
		return false;
	}
}
