<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of controller
 *
 * @author juanma
 */
class Controller {

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @var Cookie
	 */
	protected $cookie;


	public function __construct() {
		$this->request = Request::instance();
		$this->session = Session::instance();
		$this->cookie = Cookie::instance();
	}
}
?>
