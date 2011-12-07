<?php
chdir(dirname(__FILE__));
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."configuration".DIRECTORY_SEPARATOR."db_config.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."interfaces".DIRECTORY_SEPARATOR."IConnector.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."DBException.php");

/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class Connector implements IConnector {

	/**
	 * @var IConnector
	 */
	private $_dbms_connector;

	public function __construct($type=_DB_SYST, $host=_DB_HOST, $port=_DB_PORT, $base=_DB_NAME, $user=_DB_USER, $pass=_DB_PASS) {
		//Dinamically load the needed connector.
        chdir(dirname(__FILE__));
		require_once (".".DIRECTORY_SEPARATOR."{$type}.php");

		$this->_dbms_connector = new $type($host, $port, $base, $user, $pass);
	}

	public function connect() {
		$this->_dbms_connector->connect();
	}

	public function close() {
		$this->_dbms_connector->close();
	}

	public function getActiveBase() {
		return $this->_dbms_connector->getActiveBase();
	}

	public function getActiveDBMS(){
		return $this->_dbms_connector->getActiveDBMS();
	}

	/**
	 * Get next automatic sequence id for given table.
	 * @param string $table
	 * @return integer
	 */
	public function getNextId($table) {
		return $this->_dbms_connector->getNextId($table);
	}

	/**
	 * @param Query $sql
	 * @return Result
	 * @throws DBException
	 */
	public function executeQuery(Query $query) {
		try {
			return $this->_dbms_connector->executeQuery($query);
		} catch (DBException $e) {
			throw $e;
		}
	}

    public function isConnected() {
        return $this->_dbms_connector->isConnected();
    }

	/**
	 * Returns last inserted id.
	 * @return integer
	 */
	public function lastInsertId() {
		return $this->_dbms_connector->lastInsertId();
	}
}
?>
