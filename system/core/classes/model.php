<?php
defined("SYSTEM") or die("Can't execute directly");
/**
 * Description of model
 *
 * @author juanma
 */
class Model {

	/**
	 * @var IConnector
	 */
	protected $db;

	public function __construct() {
		$this->db = ConnectionManager::getDBConnector();
	}

	public function __destruct() {
		ConnectionManager::closeConnector($this->db);
	}
}
?>
