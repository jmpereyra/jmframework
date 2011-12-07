<?php
chdir(dirname(__FILE__));
require_once (".".DIRECTORY_SEPARATOR."Result.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class PostgreSQLResult extends Result {
    
	public function __construct($result) {
		parent::__construct($result);
	}

	public function fetchAll() {
		return pg_fetch_all($this->_query_result);
	}

	public function fetchAssoc() {
		return pg_fetch_assoc($this->_query_result);
	}

	public function fetchAllObjects() {
		$result = array();
		while ($row = pg_fetch_object($this->_query_result)) {
			$result[] = $row;
		}
		return $result;
	}

	public function fetchObject() {
		return pg_fetch_object($this->_query_result);
	}

	public function count() {
		return $this->_query_result ? pg_num_rows($this->_query_result) : 0;
	}

	public function affectedRows() {
		return $this->_query_result ? pg_affected_rows($this->_query_result) : 0;
	}
}
?>
