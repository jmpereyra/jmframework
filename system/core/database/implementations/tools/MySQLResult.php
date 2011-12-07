<?php
chdir(dirname(__FILE__));
require_once (".".DIRECTORY_SEPARATOR."Result.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class MySQLResult extends Result {

	public function __construct($result) {
		parent::__construct($result);
	}

	public function fetchAll() {
		$result = array();
		while ($row = mysql_fetch_assoc($this->_query_result)) {
			$result[] = $row;
		}
		return $result;
	}

	public function fetchAssoc() {
		return mysql_fetch_assoc($this->_query_result);
	}

	public function  fetchAllObjects() {
		$result = array();
		while ($row = mysql_fetch_object($this->_query_result)) {
			$result[] = $row;
		}
		mysql_free_result($this->_query_result);
		return $result;
	}

	public function  fetchObject() {
		return mysql_fetch_object($this->_query_result);
	}

	public function count() {
		return $this->_query_result ? mysql_num_rows($this->_query_result) : 0;
	}

	public function affectedRows() {
		return mysql_affected_rows($this->_query_result);
	}
}
?>