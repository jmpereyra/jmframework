<?php
chdir(dirname(__FILE__));
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."configuration".DIRECTORY_SEPARATOR."db_config.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."interfaces".DIRECTORY_SEPARATOR."IConnector.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."DBException.php");
require_once ("..".DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."PostgreSQLResult.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class PostgreSQL implements IConnector {

	private $_connection = null;
	private $_active_base;
	private $_class_name = 'PostgreSQL';
	private $_connection_string;

	public function __construct($host, $port, $base, $user, $pass) {
		$this->_connection_string = "host=$host port=$port user=$user password=$pass dbname=$base";
		$this->_active_base = $base;
	}

	public function connect() {
		if (!$this->isConnected()) 
			$this->_connection = pg_connect($this->_connection_string, PGSQL_CONNECT_FORCE_NEW);
	}

	public function close() {
		if ($this->isConnected() && is_resource($this->_connection)) {
			pg_close($this->_connection);
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
		$result = $this->executeQuery(new PostgreSQLQuery($query_string))->fetchAssoc();
		return (integer) $result['next_id'];
	}

	/**
	 * @param Query $query
	 * @return Result
	 * @throws DBException
	 */
	public function executeQuery(Query $query) {
		$this->connect();
		$result = new PostgreSQLResult(pg_query($query->getQueryString(), $this->_connection));
		if (!$result->checkResult()) 
			throw new DBException($query->getQueryString()." - ".  pg_errormessage($this->_connection));
		return $result;
	}

	public function lastInsertId() {
		return false;
	}
}
?>
