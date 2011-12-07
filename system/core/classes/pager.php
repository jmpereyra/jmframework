<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pager
 *
 * @author Pc
 */
class Pager {

	const MAX_PAGES_TO_RENDER = 150;

	private $viewName;
	private $totalElements = null;
	private $elementsPerPage = null;
	private $actualPage = null;
	private $url = null;
	private $queryData = array();
	/**
	 * @var IPagerRenderer
	 */
	private $renderer = null;

	/**
	 * Will use standard provided paginator view (you have to copy it) and
	 * renderer unless specified otherwise. As for view, you may set it here,
	 * but for renderer you may use setRenderer() method.
	 * You can add more views in your general dir (ALWAYS THERE) to
	 * change pagination layout.
	 *
	 * @param string $viewName
	 */
	public function __construct($viewName = "pager_standard") {
		$this->viewName = $viewName;
		$this->renderer = new StandardPagerRenderer();
	}

	/**
	 * @param IPagerRenderer $renderer
	 * @return Pager
	 */
	public function setRenderer(IPagerRenderer $renderer) {
		$this->renderer = $renderer;
		return $this;
	}
	/**
	 * @param int $totalElements
	 * @return Pager
	 */
	public function setTotalElements($totalElements) {
		$this->totalElements = $totalElements;
		return $this;
	}
	/**
	 * @param int $elementsPerPage
	 * @return Pager
	 */
	public function setElementsPerPage($elementsPerPage) {
		$this->elementsPerPage = $elementsPerPage;
		return $this;
	}
	/**
	 * @param int $actualPage
	 * @return Pager
	 */
	public function setActualPage($actualPage) {
		$this->actualPage = $actualPage;
		return $this;
	}
	/**
	 * CAUTION: only set final url portion, without URL_BASE or /, like
	 * client/search will produce: URL_BASE/client/search/#pagenumber
	 * @param <type> $url
	 * @return Pager
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
	/**
	 * Assoc array of parameters that will be added as get params.
	 * @param array $queryData
	 * @return Pager
	 */
	public function setQueryData($queryData) {
		$this->queryData = $queryData;
		return $this;
	}

	public function __toString() {
		$view = new View("general".DIRECTORY_SEPARATOR.$this->viewName);
		$view->setData($this->renderer->getPaginationData($this->totalElements,
														$this->elementsPerPage,
														$this->actualPage,
														$this->url,
														$this->queryData));
		return $view->render();
	}
}
/**
 * Forces Pager Renderers to implement the method needed by the pager to get the
 * data that will be used for a pagination view.
 */
interface IPagerRenderer {
	/**
	 * This method has to return an associative array of "index" => val, being
	 * index, the name of the var that you want to have available in the view
	 * you'll use and val it's value.
	 *
	 * @param int $totalElements
	 * @param int $elementsPerPage
	 * @param int $actualPage
	 * @param string $url
	 * @param array $queryData
	 * @return array()
	 */
	public function getPaginationData($totalElements, $elementsPerPage, $actualPage, $url, $queryData = array());
}
class StandardPagerRenderer implements IPagerRenderer {

	/**
	 * Returns associative data for standard pager view.
	 *
	 * @param int $totalElements
	 * @param int $elementsPerPage
	 * @param int $actualPage
	 * @param string $url
	 * @return array()
	 */
	public function getPaginationData($totalElements, $elementsPerPage, $actualPage, $url, $queryData = array()) {
		$data = array();
		$totalElements = $totalElements < ($elementsPerPage*Pager::MAX_PAGES_TO_RENDER) ? $totalElements : ($elementsPerPage*Pager::MAX_PAGES_TO_RENDER);
		$lastPage = fmod($totalElements, $elementsPerPage)==0 ? (integer)($totalElements/$elementsPerPage) : (integer)floor($totalElements/$elementsPerPage)+1;
		$previousPage = $actualPage-1;
		$nextPage = $actualPage+1;
		$pages = new stdClass();
		$pages->previous = false;
		$pages->pages = array();
		$pages->next = false;
		$query = $this->getQueryString($queryData);
		if ($actualPage > 1) {
			$previous = new stdClass();
			$previous->title = "<< Anterior";
			$previous->url = PROTOCOL_METHOD.URL_BASE.$url."/{$previousPage}{$query}";
			$pages->previous = $previous;
		}
		$i = 1;
		while ($i<($lastPage+1)) {
			$page = new stdClass();
			$page->title = $i;
			$page->url = PROTOCOL_METHOD.URL_BASE.$url."/{$i}{$query}";
			$page->link = $i != $actualPage;
			$pages->pages[$i] = $page;
			$i = ($i == 2 && ($actualPage-1) > 2
					? $actualPage - 1
					: ($i == ($actualPage+1) && ($lastPage-1) > ($actualPage+1)
						? $lastPage-1
						: $i+1));
		}
		$this->addPoints($pages->pages, $lastPage, $actualPage);
		if ($actualPage*$elementsPerPage < $totalElements) {
			$next = new stdClass();
			$next->title = "Siguiente >>";
			$next->url = PROTOCOL_METHOD.URL_BASE.$url."/{$nextPage}{$query}";
			$pages->next = $next;
		}

		$data["pages"] = $pages;
		return $data;
	}

	private function addPoints(&$pages, $lastPage, $actualPage) {
		$count = count($pages);
		$points = new stdClass();
		$points->title = "...";
		$points->link = false;
		foreach($pages as $pageNumber => $page) {
			for($i = 4; $i<8; $i++) {
				if ($count == $i && $lastPage > $i) {
					switch ($actualPage) {
						case $i-3:
							$pages[$actualPage+2] = $points;
							break;
						case $lastPage-($i-4):
							$pages[$actualPage-2] = $points;
							break;
						default:
							if (!isset($pages[$actualPage+2]))
								$pages[$actualPage+2] = $points;
							if (!isset($pages[$actualPage-2]))
								$pages[$actualPage-2] = $points;
					}
				}
			}
		}
		ksort($pages);
	}

	private function getQueryString($queryData) {
		$query = "";
		if (count($queryData) > 0) {
			$query .= "?";
			foreach ($queryData as $key => $val) {
				$query .= "{$key}={$val}&";
			}
			$query = trim($query, "&");
		}
		return $query;
	}
}
?>
