<?php
chdir(dirname(__FILE__));
require_once (".".DIRECTORY_SEPARATOR."configuration".DIRECTORY_SEPARATOR."db_config.php");
require_once (".".DIRECTORY_SEPARATOR."implementations".DIRECTORY_SEPARATOR."connectors".DIRECTORY_SEPARATOR."Connector.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class ConnectionManager {

	private static $_connectors = array();
	private static $_references = array();

	public function __destruct() {
		self::closeAllConnectors();
	}

	/**
	 * @return boolean
	 */
	public static function testConfig() {
		if (defined('_DB_SYST') && defined('_DB_HOST') && defined('_DB_PORT') && defined('_DB_NAME') && defined('_DB_USER') & defined('_DB_PASS')) {
			return true;
		}
		return false;
	}

	public static function getDBConnector($type = _DB_SYST, $host=_DB_HOST, $port=_DB_PORT, $base=_DB_NAME, $user=_DB_USER, $pass=_DB_PASS) {
		for ($i=0; $i<count(self::$_connectors); $i++) {
			$connector = null;
			if (self::$_connectors[$i]->getActiveDBMS() == $type && self::$_connectors[$i]->getActiveBase() == $base) {
				$connector = self::$_connectors[$i];
				self::$_references[$i]++;
				return $connector;
			}
		}
		$connector = new Connector($type, $host, $port, $base, $user, $pass);
		self::$_connectors[] = $connector;
		self::$_references[] = 1;
		return $connector;
	}

	public static function closeConnector(IConnector $connector) {
		$key = array_search($connector, self::$_connectors);
		if ($key !== false) {
			if ((self::$_references[$key]-- <= 0)) {
				unset(self::$_connectors[$key]);
				unset(self::$_references[$key]);
				$connector->close();
			}
		}
	}

	public static function closeAllConnectors() {
		foreach (self::$_connectors as $connector) {
			$connector->close();
		}
		self::$_connectors = null;
	}

	public static function getPossibleDBMSs() {
		global $__available_dbms;
		return $__available_dbms;
	}

	/**
	 * Returns a Query tool.
	 * If no connector is given, a Query tool for the default connector is returned
	 * else, a Query tool for the given connector is returned.
	 * @param IConnector $connector
	 * @return Query
	 */
	public static function getQuery(IConnector $connector = null) {
		$type = (isset ($connector) ? $connector->getActiveDBMS() : _DB_SYST).'Query';
		if (isset($connector)) {
			$connector->connect();
		} else {
			foreach (self::$_connectors as $connector) {
				if ($connector->getActiveDBMS() == _DB_SYST)
					$connector->connect ();
			}
		}
        chdir(dirname(__FILE__));
		require_once (".".DIRECTORY_SEPARATOR."implementations".DIRECTORY_SEPARATOR."tools".DIRECTORY_SEPARATOR."{$type}.php");
		return new $type();
	}
}
?>
