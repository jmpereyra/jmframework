<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of i18n
 *
 * @author juanma
 */
class I18n {

	const DEFAULT_LANG = "en";
	private static $_lang;
	private static $_words;

	/**
	 * Loads a file containing i18n word translations.
	 * The lang parameter applies for the directory under i18n that will be used
	 * for that lang translation files.
	 * Please note that those files have to have an array (need not be defined)
	 * of associative entries "word" => "translation". This array must have the
	 * same name you pass on the string $arrayName, or may be used as:
	 * ${$arrayName}["key"] => "translation" due to PHP magic string evaluation.
	 *
	 * @param string $file | File name to check
	 * @param string $lang | Directory under i18n
	 * @param string $arrayName | Name of the associative array used
	 * @return boolean | wether the file was loaded or not.
	 */
	public static function loadWords($file, $lang = false, $arrayName = "lang") {
		if (!$lang)
			$lang = self::getActiveLang ();
		$file = I18N.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR.$file.".php";
		$success = false;
		${$arrayName} = array();
		if (($success = file_exists($file)))
			include_once $file;
		self::$_words = ${$arrayName};
		return $success;
	}

	/**
	 * Gets translated word
	 *
	 * @param string $name | Word or phrase to obtain the translation
	 * @return string | The translated word or phrase
	 */
	public static function word($name) {
		if (isset(self::$_words) && isset (self::$_words[$name]))
			return self::$_words[$name];
		return "Unrecognized word definition";
	}

	public static function getActiveLang() {
		if (!isset (self::$_lang))
			return defined ("SITE_DEFAULT_LANG") ? SITE_DEFAULT_LANG : self::DEFAULT_LANG;
		return self::$_lang;
	}
}
?>
