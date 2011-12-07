<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of view
 *
 * @author juanma
 */
class View {

	private $loadedView;
	private $viewData = array();

	public function __construct($viewName = false) {
		if ($viewName) {
			if (file_exists(VIEWS . DIRECTORY_SEPARATOR . "{$viewName}" . VIEWS_SUFFIX)) {
				$this->loadedView = VIEWS . DIRECTORY_SEPARATOR . "{$viewName}" . VIEWS_SUFFIX;
			} else {
				die("View: '{$viewName}' not found, are you sure you put it in: '" . VIEWS . "' dir, and used: '" . VIEWS_SUFFIX . "' suffix?");
			}
		}
	}

	public function load($viewName) {
		if (file_exists(VIEWS . DIRECTORY_SEPARATOR . "{$viewName}" . VIEWS_SUFFIX)) {
			$this->loadedView = VIEWS . DIRECTORY_SEPARATOR . "{$viewName}" . VIEWS_SUFFIX;
		} else {
			die("View: '{$viewName}' not found, are you sure you put it in: '" . VIEWS . "' dir, and used: '" . VIEWS_SUFFIX . "' suffix?");
		}
	}

	public function setData($data = array()) {
		$this->viewData = $data;
	}

	public function render($toScreen = false, $compress = false) {
		$data = array();
		foreach ($this->viewData as $key => $value) {
			if ($value instanceof View)
				$newValue = $value->render();
			else
				$newValue = $value;
			$data[$key] = $newValue;
		}
		extract($data);
		if ((boolean) $this->loadedView) {
			ob_start();
				include $this->loadedView;
				$render = ob_get_contents();
			ob_end_clean();
		} else {
			$render = "";
		}
		if ($compress)
			$render = self::compress($render);
		if ($toScreen)
			print $render;
		else
			return $render;
	}

	/**
	 * Tryes to load a view found in "general/" dir inside views, called "layout".
	 * You have to provide both of them (example provided).
	 *
	 * The view then will try to render the layout with the view you provide as
	 * content for it.
	 *
	 * You also have to set the title as a string, and may set wether the view will
	 * render to screen or not, an array with the names of the javascript files
	 * to use (located in "themes/js/" dir) without ".js" extension and another
	 * array with the css files to use without ".css" (located in "themes/css/").
	 *
	 * @param View $inView
	 * @param string $title
	 * @param boolean $toScreen
	 * @param array $javaScript
	 * @param array $css
	 * @param array $data
	 */
	public static function defaultLayoutRender(View $inView, $title, $toScreen=false, $javaScript = array(), $css = array(), $data = array(), $compress = false) {
		$view = new View("general".DIRECTORY_SEPARATOR."layout");
		$inData = array(	"title"		=> $title,
						"content"	=> $inView,
						"javaScript"=> $javaScript,
						"css"		=> $css);
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				$inData[$key] = $val;
			}
		}
		$view->setData($inData);
		if ($toScreen)
			$view->render($toScreen, $compress);
		else
			return $view->render(false, $compress);

	}

	private static function compress(&$html) {
		$html = preg_replace("/(>)[\t|\s|\n]+(.+)?([\t|\s|\n]+)?(<)/", "$1$2$4", $html);
		$html = preg_replace("/(>.+[a-z]+)[\n|\t|\s]+(<)/", "$1$2", $html);
		$html = preg_replace("/(>)[\s|\n|\t]+(<)/", "$1$2", $html);
		return $html;
	}

}

?>
