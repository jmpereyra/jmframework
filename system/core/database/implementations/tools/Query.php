<?php
chdir(dirname(__FILE__));
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."configuration".DIRECTORY_SEPARATOR."db_config.php");
require_once ("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."QueryException.php");
/**
 * Table = array('table' => '', 'alias' => '', 'local_join_field' => '', 'foreign_join_alias' => '', 'foreign_join_field' => '', 'join_type' => '', 'inner_to_previous' => false);
 * Field = array('field' => '', 'alias' => '', 'table_alias' => '', 'value' => '', 'applied_function' => '');
 * Clause = array('field' => '', 'table_alias' => '', 'comparison_sign' => '', 'comparison_value' => "", 'comparison_table_alias' => '', 'applied_function' => '', 'clause_operator' => '', $clause_type = '');
 * Groupings = array('field' => '', 'table_alias' => '');
 * Orders = array('field' => '', 'table_alias' => '', 'order_type' => '', 'applied_function' => '');
 * Pagination = array('result_limit' => '', 'result_offset' => 0);
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
abstract class Query {

	protected $_query_string;
	protected $_active_query_type;
	/*
	 * I'll use theese as arrays of arrays because of Object creation overhead...
	 * But maybe later... I'll make Table, Field and Clause.
	 * If I make those, maybe using the framework will be much easier with objects.
	 */
	protected $_tables = array();
	protected $_fields = array();
	protected $_clauses = array();
	protected $_having_clauses = array();
	protected $_groupings = array();
	protected $_orders = array();
	protected $_pagination = array();

    private $_resolved_version = 0;
    private $_modified_version = 1;

	public function  __toString() {
		try {
			return $this->getQueryString();
		} catch (Exception $e) {
			var_dump($e->getTrace());
		}
	}

	/**
	 * Warning: use of this implementation is not encouraged, parameters inside
	 * queries should be escaped before setting. Use on your own responsibility.-
	 * @param string $sql
	 * @param integer $queryAs (how to use this query <use _QUERY_AS constants>)
	 * @param string $alias (if used as field or join, alias to identify query)
	 * @param string $localField (field from this query to use on join when used as table)
	 * @param string $foreignAlias (alias of foreign table to be joined or to compare in clause)
	 * @param string $foreignField (foreign field in join or to compare in clause)
	 * @param string $joinType (join type when used as table <use _JOIN constants>)
	 */
	public function setQueryString($sql, $queryAs = _QUERY_AS_PRIMARY, $alias = '', $localField = '', $foreignAlias = '', $foreignField = '', $joinType = _INNER_JOIN) {
		$this->clear();
        switch ($queryAs) {
			case _QUERY_AS_PRIMARY:
				$this->_query_string = $sql;
				break;
			case _QUERY_AS_FIELD:
				$this->_fields[] = array(
					'field' => "($sql)",
					'alias' => $alias
				);
				break;
			case _QUERY_AS_TABLE:
				$this->_tables[] = array(
					'table' => "($sql)",
					'alias' => $alias,
					'local_join_field' => $localField,
					'foreign_join_alias' => $foreignAlias,
					'foreign_join_field' => $foreignField,
					'join_type' => $joinType
				);
				break;
			case _QUERY_AS_CLAUSE:
				$this->_clauses[] = array(
					'field' => $foreignField,
					'table_alias' => $foreignAlias,
					'comparison_sign' => 'IN',
					'comparison_value' => "($sql)"
				);
			default:
				$this->_query_string = $sql;
		}
		return $this;
	}

	public function getQueryString() {
		if (trim($this->_query_string) == '' || ($this->_modified_version > $this->_resolved_version && count($this->_tables) > 0)) {
            $this->_resolved_version = $this->_resolved_version < $this->_modified_version
                                        ? $this->_resolved_version + 1
                                        : $this->_resolved_version;
			$this->resolveQuery();
       	}
		return $this->_query_string;
	}

	/**
	 * You can add a query with a minimum of at least a table, by default, if you
	 * add only that, query won't have clauses and will be selecting *.
	 * @param array $table :> array('table' => '', 'alias' => '');
	 * @param array $clauses :> array( array('field' => '', 'table_alias' => '', 'comparison_sign' => '', 'comparison_value' => "", 'comparison_table_alias' => '', 'applied_function' => '', 'clause_operator' = '', clause_type = ''));
	 * @param array $fields :> array( array('field' => '', 'alias' => '', 'table_alias' => '', 'value' => '', 'applied_function' => '', 'distinct' => ''));
	 * @param array $groupings :> array( array('field' => '', 'table_alias' => ''));
	 * @param array $orders :> array( array('field' => '', 'table_alias' => '', 'order_type' => ''));
	 * @param array $pagination :> array('result_limit' => '', 'result_offset' => 0);
     * @return Query
	 */
	public function selectQuery($table, $clauses = array(), $fields = array(array('field' => '*')), $groupings = array(), $orders = array(), $pagination = array()) {
		$this->_active_query_type = _SELECT_QUERY;
        $this->clear();
       	$this->_tables[] = array (
			'table' => $table['table'],
			'alias' => $table['alias'],
			'join_type' => _PRIMARY_TABLE
		);
		foreach ($clauses as $clause) {
			if (!isset ($clause['clause_type']) || $clause['clause_type'] == _CLAUSE_WHERE) {
				$this->_clauses[] = $clause;
			} else if ($clause['clause_type'] == _CLAUSE_HAVING) {
				$this->_having_clauses[] = $clause;
			}
		}
		$this->_fields = $fields;
		$this->_groupings = $groupings;
		$this->_orders = $orders;
		$this->_pagination = $pagination;
        return $this;
	}

    private function clear() {
        $this->_tables = array();
        $this->_fields = array();
        $this->_clauses = array();
        $this->_having_clauses = array();
        $this->_groupings = array();
        $this->_orders = array();
        $this->_pagination = array();
        $this->_query_string = "";
    }

    /**
	 * Chainable JOIN method.
	 * Used to add all types of joins.
	 * Can be encapsulated joins (when you want to "pre-join" to some table before
	 * joining with outer).
	 * Remember to use the same aliases that you used in the selectQuery or tableFrom
	 * methods.
	 * @param string $joinType (use _<type>_JOIN constants)
	 * @param string $localTableName
	 * @param string $localTableAlias
	 * @param string $localJoinField
	 * @param string $foreignTableAlias
	 * @param string $foreignJoinField
	 * @param boolean $innerToPrevious (true if you want to encapsulate to previous join)
     * @return Query;
	 */
	public function addExplicitJoin($joinType, $localTableName, $localTableAlias, $localJoinField, $foreignTableAlias, $foreignJoinField, $innerToPrevious = false) {
		$table = array ('join_type' => $joinType,
						'table' => $localTableName,
						'alias' => $localTableAlias,
						'local_join_field' => $localJoinField,
						'foreign_join_alias' => $foreignTableAlias,
						'foreign_join_field' => $foreignJoinField,
						'inner_to_previous' => $innerToPrevious);
		$this->addJoin($table);
        return $this;
	}
	/**
	 * @param array $tables :> array(array('table' => '', 'alias' => '', 'local_join_field' => '', 'foreign_join_alias' => '', 'foreign_join_field' => '', 'join_type' => '', 'inner_to_previous' => false));
	 * @throws QueryException
	 */
	public function addMultipleJoins($tables) {
		foreach ($tables as $table) try { $this->addJoin($table); } catch (QueryException $qe) { throw $qe; }
	}
	/**
	 * @param array $table :> array('table' => '', 'alias' => '', 'local_join_field' => '', 'foreign_join_alias' => '', 'foreign_join_field' => '', 'join_type' => '', 'inner_to_previous' => false);
	 * @throws QueryException
	 */
	public function addJoin($table) {
        $this->_modified_version = $this->_resolved_version == $this->_modified_version
                                        ? $this->_modified_version + 1
                                        : $this->_modified_version;
		if (count($this->_tables) == 0) throw new QueryException(_TABLE_ERROR."Cannot add joins to empty query.");
		if (!isset($table['table']) || trim($table['table']) == '') throw new QueryException(_TABLE_ERROR."Invalid table name. See ['table'] value in array.");
		if (!isset($table['alias']) || trim($table['alias']) == '') throw new QueryException(_TABLE_ERROR."Invalid table alias, alias required for join. See ['alias'] value for table < ".$table['table']." > in array.");
		if (!isset($table['local_join_field']) || trim($table['local_join_field']) == '') throw new QueryException(_TABLE_ERROR."Invalid table join field, local_join_field required for join. See ['local_join_field'] value for table < ".$table['table']." > in array.");
		if (!isset($table['foreign_join_alias']) || trim($table['foreign_join_alias']) == '') throw new QueryException(_TABLE_ERROR."Invalid foreign table alias, foreign_join_alias required for join. See ['foreign_join_alias'] value for table < ".$table['table']." > in array.");
		if (!isset($table['foreign_join_field']) || trim($table['foreign_join_field']) == '') throw new QueryException(_TABLE_ERROR."Invalid table join field, foreign_join_field required for join. See ['foreign_join_field'] value for table < ".$table['table']." > in array.");
		if (!isset($table['join_type']) || trim($table['join_type']) == '') throw new QueryException(_TABLE_ERROR."Invalid join type, join_type required for join. See ['join_type'] value for table < ".$table['table']." > in array.");
		$this->_tables[] = $table;
	}

	/**
	 * Insert string generator.
	 *
	 * @param string $tableName
	 * @param array $data An array of field_name => value for the insert.
	 *					  Value types should be specified using "chr_, num_, bln_"
	 *					  prefixes in field names.
	 * @return string
	 */
	public function insertQuery($tableName, $data) {
        if (is_array($data)) {
            $insert = "INSERT INTO {$tableName} (";
            $values = "VALUES (";
            foreach ($data as $field => $value) {
                $limiter = strpos($field, "chr_") !== false ? "'" : "";
				$value = trim($value) == '' && $limiter != "'" ? 'NULL' : $value;
				$insert .= preg_replace("/^(chr|num|bln)_/", "", $field) . ", ";
                $values .= $limiter . $this->escape($value) . $limiter . ", ";
            }
            $insert = trim($insert, ", ") . ") " . trim($values, ", ") . ");";
			$this->setQueryString($insert);
            return $insert;
        }
        return false;
    }

	/**
	 * Update string generator.
	 *
	 * @param string $tableName
	 * @param array $data An array of field_name => value for the update.
	 *					  Value types should be specified using "chr_, num_, bln_"
	 *					  prefixes in field names. Also "key_" prefix should be
	 *					  added if a where clause is to be used.
	 * @return string
	 */
    public function updateQuery($tableName, $data) {
        if (is_array($data)) {
            $update = "UPDATE {$tableName} SET ";
            $where = '';
            foreach ($data as $field => $value) {
                if (strpos($field, "key_") === false) {
					$limiter = strpos($field, "chr_") !== false ? "'" : "";
					$value = trim($value) == '' && $limiter != "'" ? 'NULL' : $value;
                    $update .= preg_replace("/^(chr|num|bln)_/", "", $field) . " = " . $limiter . $this->escape($value) . $limiter . ", ";
                } else {
					$field = str_replace("key_", "", $field);
					$limiter = strpos($field, "chr_") !== false ? "'" : "";
					$field = preg_replace("/^(chr|num|bln)_/", "", $field);
                    $where = " WHERE {$field} = " .$limiter. $this->escape($value) .$limiter. ";";
                }
            }
            $update = trim($update, ", ") . $where;
			$this->setQueryString($update);
            return $update;
        }
        return false;
    }

	/**
	 * Chainable select query start.
	 * Use this methods as you would with normal sql.
	 * For simplification use aliases as part of the field name (kept number of
	 * needed parameters lower), but is encouraged to use them, because table aliases
	 * are obligatory to keep join compatibility.
	 * IE: If you need to select a field named foo from table get and with a bar
	 * alias, you should call this function this way:
	 * $query->select("get.foo AS bar", ... more fields);
	 *
	 * @param string $field
	 * @param string $anotherField [optional]
	 * @param string $moreFields [..(optional)] keep adding
	 * @return Query
	 */
	public function select($field, $anotherField = false, $moreFields = false) {
		$this->clear();
		$this->_active_query_type = _SELECT_QUERY;
		for ($i=0; $i<func_num_args(); $i++) {
			//maybe I'll explode using the custom delimiters to set aliases later.
			$this->_fields[] = array("field" => func_get_arg($i));
		}
		return $this;
	}
	/**
	 *
	 * Chainable field adding for select
	 *
	 * @param string $field
	 * @param string $anotherField [optional]
	 * @param string $moreFields [..(optional)] keep adding
	 * @return Query
	 */
	public function addFields($field, $anotherField = false, $moreFields = false) {
		for ($i=0; $i<func_num_args(); $i++) {
			//maybe I'll explode using the custom delimiters to set aliases later.
			$this->_fields[] = array("field" => func_get_arg($i));
		}
		return $this;
	}

	/**
	 * Chainable FROM method.
	 * Remember to set the aliases equal to the used in the select method.
	 * Alias is MANDATORY.
	 * @param string $table
	 * @param string $alias
	 * @return Query
	 * @throws QueryException
	 */
	public function fromTable($table, $alias) {
		if (trim($table)=="" || trim($alias)=="")
			throw new QueryException (_TABLE_ERROR." Malformed table sentence, should set table AND alias.");
		$this->_tables[] = array (
			'table' => $table,
			'alias' => $alias,
			'join_type' => _PRIMARY_TABLE
		);
		return $this;
	}

	/**
	 * Chainable Where method
	 * You can precede the field or the operator with "NOT"
	 * @param string $field Use as the select fields (with table alias)
	 * @param string $comparator A comparison sign (=, >, <, LIKE, IS, ...)
	 * @param string $value	The value to be compared, can be preceded with NOT
	 * @return Query
	 * @throws QueryException
	 */
	public function where($field, $comparator=false, $value=false) {
		$this->addClause(_CLAUSE_WHERE, $field, false, $comparator, $value);
		return $this;
	}
	
	/**
	 * Chainable Having method
	 * You can precede the field or the operator with "NOT"
	 * @param string $function Grouping function to apply to having clause 
	 * @param string $field Use as the select fields (with table alias)
	 * @param string $comparator A comparison sign (=, >, <, LIKE, IS, ...)
	 * @param string $value	The value to be compared, can be preceded with NOT
	 * @return Query
	 * @throws QueryException
	 */
	public function having($function, $field, $comparator, $value) {
		$this->addClause(_CLAUSE_HAVING, $field, false, $comparator, $value, $function);
		return $this;
	}

	/**
	 * Chainable Where AND method
	 * You can precede the field or the operator with "NOT"
	 * @param string $field Use as the select fields (with table alias)
	 * @param string $comparator A comparison sign (=, >, <, LIKE, IS, ...)
	 * @param string $value	The value to be compared, can be preceded with NOT
	 * @return Query
	 * @throws QueryException
	 */
	public function wand($field, $comparator=false, $value=false) {
		$this->addClause(_CLAUSE_WHERE, $field, _CLAUSE_AND, $comparator, $value);
		return $this;
	}

	/**
	 * Chainable Where OR method
	 * You can precede the field or the operator with "NOT"
	 * @param string $field Use as the select fields (with table alias)
	 * @param string $comparator A comparison sign (=, >, <, LIKE, IS, ...)
	 * @param string $value	The value to be compared, can be preceded with NOT
	 * @return Query
	 * @throws QueryException
	 */
	public function wor($field, $comparator=false, $value=false) {
		$this->addClause(_CLAUSE_WHERE, $field, _CLAUSE_OR, $comparator, $value);
		return $this;
	}

	/**
	 * Chainable order by method.
	 * If you add more than one field, always remember to add the order type for
	 * subsequent fields.
	 *
	 * @param string $field
	 * @param string $type May be ASC or DESC
	 * @param string $anotherField [...(keep adding)]
	 * @param string $anotherType  [...(keep adding)]
	 * @return Query
	 */
	public function orderBy($field, $type, $anotherField = false, $anotherType=false) {
		for ($i=0; $i<func_num_args(); $i++) {
			if ($i % 2 == 0)
				$this->_orders[] = array("field" => func_get_arg($i), "order_type" => func_get_arg($i+1));
		}
		return $this;
	}

	/**
	 * Chainable group by method.
	 * If you add more than one field, always remember to add the order type for
	 * subsequent fields.
	 *
	 * @param string $field
	 * @param string $anotherField [...(keep adding)]
	 * @return Query
	 */
	public function groupBy($field, $anotherField = false, $moreFields = false) {
		for ($i=0; $i<func_num_args(); $i++) {
			//maybe I'll explode using the custom delimiters to set aliases later.
			$this->_groupings[] = array("field" => func_get_arg($i));
		}
		return $this;
	}
	/**
	 * Chainable limit method
	 * @param int $value
	 * @return Query
	 */
	public function limit($value) {
		if (!isset($this->_pagination))
			$this->_pagination = array();
		$this->_pagination['result_limit'] = $value;
		return $this;
	}

	/**
	 * Chainable offset method
	 * @param int $value
	 * @return Query
	 */
	public function offset($value) {
		if (!isset($this->_pagination))
			$this->_pagination = array();
		$this->_pagination['result_offset'] = $value;
		return $this;
	}


	/**
	 *
	 * @param <type> $type
	 * @param <type> $field
	 * @param <type> $operator
	 * @param <type> $comparator
	 * @param <type> $value
	 */
	private function addClause($type, $field, $operator = false, $comparator=false, $value=false, $appliedFunction = false) {
		$clause = array(
			"field" => $field,
			"clause_type" => $type
		);
		if ($operator)
			$clause["clause_operator"] = $operator;
		if ($comparator !== false) {
			if ($value === false)
				throw new QueryException (_CLAUSE_ERROR." Comparator set without a comparison value.");
			$clause["comparison_sign"] = $comparator;
		}
		if ($value !== false)
			$clause["comparison_value"] = $value;
		if ($appliedFunction)
			$clause["applied_function"] = $appliedFunction;
			
		if ($type == _CLAUSE_WHERE)
			$this->_clauses[] = $clause;
		else 
			$this->_having_clauses[] = $clause;
	}

	abstract public function escape($value);

	abstract public function resolveQuery();
}
