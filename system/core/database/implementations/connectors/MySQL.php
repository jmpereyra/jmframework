<?php
chdir(dirname(__FILE__));
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."configuration".DIRECTORY_SEPARATOR."db_config.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."interfaces".DIRECTORY_SEPARATOR."IConnector.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."DBException.php");
require_once ("..".DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."MySQLResult.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class MySQL implements IConnector {

	private $_connection = null;
	private $_active_base;
	private $_class_name = 'MySQL';
	private $_host;
	private $_port;
	private $_base;
	private $_user;
	private $_pass;

	public function __construct($host, $port, $base, $user, $pass) {
		$this->_host = $host;
		$this->_port = $port;
		$this->_base = $base;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_active_base = $base;
	}

	public function connect() {
		if (!$this->isConnected()) {
			$this->_connection = mysql_connect($this->_host.':'.$this->_port, $this->_user, $this->_pass, true);
			mysql_select_db($this->_base);
		}
	}

	public function close() {
		if ($this->isConnected() && is_resource($this->_connection)) {
			mysql_close($this->_connection);
			unset($this->_connection);
		}
	}

	public function isConnected() {
		return isset($this->_connection) && $this->_connection;
	}

	public function getActiveBase() {
		return $this->_active_base;
	}

	public function getActiveDBMS(){
		return $this->_class_name;
	}

	/**
	 * Get next automatic sequence id for given table.
	 * @param string $table
	 * @return integer
	 */
	public function getNextId($table) {
		$query_string = "SELECT sb_get_next_id('$table') AS next_id;\n";
		$result = $this->executeQuery(new MySQLQuery($query_string))->fetchAssoc();
		return (integer) $result['next_id'];
	}

	/**
	 * @param Query $query
	 * @return Result
	 * @throws DBException
	 */
	public function executeQuery(Query $query) {
		$this->connect();
		$result = new MySQLResult(mysql_query($query->getQueryString(), $this->_connection));
		if (!$result->checkResult()) 
			throw new DBException($query->getQueryString()." - ".mysql_error($this->_connection));
		return $result;
	}

	public function lastInsertId() {
		return mysql_insert_id($this->_connection);
	}
}