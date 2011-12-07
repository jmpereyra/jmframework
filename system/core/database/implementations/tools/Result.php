<?php

/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
abstract class Result {
    
	protected $_query_result;

	protected function  __construct($result) {
		$this->_query_result = $result;
	}

	/**
	 * Fetchs all the rows of the result as an array of associative
	 * "field_name" => "value" arrays.
	 * @return array
	 */
	public abstract function fetchAll();

	/**
	 * Fetchs next row from a result as an associative array of
	 * "field_name" => "value".
	 * When there's no more rows returns false, so can be used as an iterator.
	 * @return mixed
	 */
	public abstract function fetchAssoc();

	/**
	 * Fetchs all the row of the result as an array of instances of stdClass
	 * with $row->field_name => value properties.
	 * @return array
	 */
	public abstract function fetchAllObjects();

	/**
	 * Fetchs next row from a result as an instance of stdClass with
	 * $row->field_name => value properties.
	 * When there's no more rows returns false, so can be used as an iterator
	 * @return mixed
	 */
	public abstract function fetchObject();

	/**
	 * Returns true if the result is a succesful execution of a query or false if
	 * not.
	 * @return boolean
	 */
	public function checkResult() {
		return ($this->_query_result !== false);
	}

	/**
	 * Returns the number of results for current link.
	 * @return int
	 */
	public abstract function count();

	/**
	 * Returns the number of results for current link.
	 * @return int
	 */
	public abstract function affectedRows();
}
?>
