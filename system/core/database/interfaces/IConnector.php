<?php

/**
 * Connector specified signatures, definition of available methods for any 
 * DBMS connectors to be developed.
 *
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
interface IConnector {

	//Publics
	/**
	 * Opens database connection (if not already open).
	 */
	public function connect();
	/**
	 * Closes database connection (when opened).
	 */
	public function close();
	/**
	 * Returns active database system.
	 * @return string
	 */
	public function getActiveDBMS();
	/**
	 * Returns active database name.
	 * @return string
	 */
	public function getActiveBase();
    /**
	 * Tests wether the connection is active.
	 * @return boolean
	 */
	public function isConnected();
	/**
	 * Get next automatic sequence id for given table.
	 * @param string $table
	 * @return integer
	 */
	public function getNextId($table);
	/**
	 * Execute given query against the active database connection, always returns
	 * a checkable custom database Result object.
	 * IE: in case of postgres INSERT syntax where you can specify "RETURNING"
	 * value to receive the insertion id, you can fetch that value from the
	 * Result object.
	 *
	 * @param Query $sql
	 * @return Result
	 * @throws DBException
	 */
	public function executeQuery(Query $query);
}
?>
