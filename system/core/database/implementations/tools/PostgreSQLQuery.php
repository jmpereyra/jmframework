<?php
chdir(dirname(__FILE__));
require_once (".".DIRECTORY_SEPARATOR."Query.php");
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */
class PostgreSQLQuery extends Query {

	/**
	 * @param string $query_string
	 */
	public function  __construct($query_string = '') {
		$this->_query_string = $query_string;
	}

	/**
	 * @return string
	 * @throws QueryException
	 */
	public function resolveQuery() {
		try {
			switch ($this->_active_query_type) {
				case _SELECT_QUERY:
					$this->_query_string = $this->resolveSelect();
					break;
			}
		} catch (QueryException $e) {
			throw $e;
		}
	}

	/**
	 * @return string
	 * @throws QueryException
	 * @todo add table alias checking for fields against present table aliases
	 */
	private function resolveSelect() {
		$query = "SELECT ";
		//Check minimum database items for selecting.
		if (count($this->_tables) == 0) throw new QueryException(_GENERAL_ERROR."No tables found for selection. See tables array.");
		if (count($this->_fields) == 0) throw new QueryException(_GENERAL_ERROR."No fields found for selection. See fields array.");
		try {
            //Resolve fields info.
            $this->resolveSelectFields($query);
            //Resolve main table info
            $this->resolveSelectTable($query);
            //Resolve joins
            $this->resolveJoins($query);
            //Resolve clauses
            $this->resolveClauses($query, $this->_clauses, _CLAUSE_WHERE);
            //Resolve groupings
            $this->resolveSelectGroupings($query);
            //Resolve orders
            $this->resolveSelectOrders($query);
            //Resolve pagination
            $this->resolveSelectPagination($query);
        } catch (QueryException $qe) { throw $qe; }
		$query = trim($query).";";
		return $query;
	}

	private function resolveSelectFields(&$query) {
		foreach ($this->_fields as $field) {
			if (!isset($field['field']) || trim($field['field']) == '') throw new QueryException(_FIELD_ERROR."Invalid field name. See ['field'] value for field in array.");
			if (isset($field['table_alias']) && trim($field['table_alias']) == '') throw new QueryException(_FIELD_ERROR."Invalid table alias. See ['table_alias'] value for field < ".$field['field']." > in array.");
			if (isset($field['alias'])) {
				if (trim($field['alias']) == '') throw new QueryException(_FIELD_ERROR."Invalid field alias. See ['alias'] value for field < ".$field['field']." > in array.");
				if (trim($field['field']) == '*' && ! isset($field['applied_function'])) throw new QueryException(_FIELD_ERROR."Can't assign alias to generic multiple field selection. Select a field or assign goruping function.");
			}
			$closeFunction = '';
			if (isset ($field['applied_function'])) {
				if (trim ($field['applied_function']) == '') throw new QueryException(_FIELD_ERROR."Invalid function name. See ['applied_function'] value for field < ".$field['field']." > in array.");
				$query .= trim($field['applied_function']).(isset ($field['distinct']) ? "(DISTINCT " : "(");
				$closeFunction = ")";
			}
			$query .= (isset ($field['table_alias'])) ?
					trim($field['table_alias']).".".trim($field['field']) :
					trim($field['field']) ;
			$query .= $closeFunction;
			$query .= (isset ($field['alias'])) ?
					" AS ".trim($field['alias']).", " :
					", ";
		}
	}

	private function resolveSelectTable(&$query) {
		$query = trim($query, ", ")." \n\tFROM ";
		if (!isset($this->_tables[0]['table']) || trim($this->_tables[0]['table']) == '') throw new QueryException(_TABLE_ERROR."Invalid table name. See ['table'] value in array.");
		if (isset($this->_tables[0]['alias']) && trim($this->_tables[0]['alias']) == '') throw new QueryException(_TABLE_ERROR."Invalid table alias. See ['alias'] value for table < ".$this->_tables[0]['table']." > in array.");
		$query .= trim($this->_tables[0]['table']);
		$query .= isset($this->_tables[0]['alias']) ?
				" AS ".trim($this->_tables[0]['alias'])." " : " ";
	}

	private function resolveClauses(&$query, $clauseArray, $clauseType = _CLAUSE_WHERE) {
		if (count($clauseArray) > 0) {
			switch ($clauseType) {
				case _CLAUSE_WHERE:
					$query .= "\n\tWHERE ";
					break;
				case _CLAUSE_HAVING:
					$query .= "\n\tHAVING ";
					break;
				default:
					$query .= "\n\tWHERE ";
			}
			for ($i=0; $i<count($clauseArray); $i++) {
				$compareValues = false;
				$closeFunction = '';
				if (!isset ($clauseArray[$i]['field']) || trim ($clauseArray[$i]['field']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid clause field. See ['field'] value for clause in array.");
				if ($clauseType === _CLAUSE_HAVING && (!isset ($clauseArray[$i]['applied_function']) || trim ($clauseArray[$i]['applied_function']) == '')) throw new QueryException(_CLAUSE_ERROR."Function needed for having clause. See ['applied_function'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
				if (isset($clauseArray[$i]['table_alias']) && trim($clauseArray[$i]['table_alias']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid table alias. See ['table_alias'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
				if (isset($clauseArray[$i]['clause_operator'])) {
					if (trim($clauseArray[$i]['clause_operator']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid clause operator. See ['clause_operator'] value for clause in field < ".$clauseArray[$i]['field']." > in array and use defined constants preferently.");
					$query .= trim ($clauseArray[$i]['clause_operator'])." ";
				} else {
					if ($i > 0) throw new QueryException(_CLAUSE_ERROR."Clause operator ommited. See ['clause_operator'] value for clause in field < ".$clauseArray[$i]['field']." > in array and use defined constants preferently. Operator required when more than one clauses.");
				}
				if (isset($clauseArray[$i]['applied_function'])) {
					if (trim($clauseArray[$i]['applied_function']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid function name. See ['applied_function'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
					$query .= trim($clauseArray[$i]['applied_function'])."(";
					$closeFunction = ')';
				}
				if (isset($clauseArray[$i]['comparison_value'])) {
					if (trim($clauseArray[$i]['comparison_value'])=="") throw new QueryException(_CLAUSE_ERROR."Invalid comparison value. See ['comparison_value'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
					if (!isset($clauseArray[$i]['comparison_sign']) || trim($clauseArray[$i]['comparison_sign']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid comparison sign. See ['comparison_sign'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
					if (isset($clauseArray[$i]['comparison_table_alias']) && trim($clauseArray[$i]['comparison_table_alias']) == '') throw new QueryException(_CLAUSE_ERROR."Invalid table alias. See ['comparison_table_alias'] value for clause in field < ".$clauseArray[$i]['field']." > in array.");
					$compareValues = true;
				}
				$query .= ((isset ($clauseArray[$i]['table_alias'])) ?
						trim($clauseArray[$i]['table_alias']).".".trim($clauseArray[$i]['field']) :
						trim($clauseArray[$i]['field'])) . $closeFunction ;
				if ($compareValues) {
					$query .= " ".trim($clauseArray[$i]['comparison_sign'])." ";
					$query .= (isset ($clauseArray[$i]['comparison_table_alias'])) ?
							trim($clauseArray[$i]['comparison_table_alias']).".".trim($clauseArray[$i]['comparison_value']) :
							trim($clauseArray[$i]['comparison_value']);
				}
				$query .= " ";
			}
		}
	}

	private function resolveSelectGroupings(&$query) {
		if (count($this->_groupings) > 0) {
			$query .= "\n\tGROUP BY ";
			foreach ($this->_groupings AS $field) {
				if (!isset($field['field']) || trim($field['field']) == '') throw new QueryException(_FIELD_ERROR."Invalid field name. See ['field'] value for grouping in array.");
				if (isset($field['table_alias']) && trim($field['table_alias']) == '') throw new QueryException(_FIELD_ERROR."Invalid table alias. See ['table_alias'] value for grouping < ".$field['field']." > in array.");
				$query .= ((isset ($field['table_alias'])) ?
					trim($field['table_alias']).".".trim($field['field']) :
					trim($field['field'])) . ', ' ;
			}
			$query = trim($query, ", ")." ";
			//And in case set... resolve havings
			$this->resolveClauses($query, $this->_having_clauses, _CLAUSE_HAVING);
		} else {
			if (count ($this->_having_clauses) > 0) throw new QueryException(_CLAUSE_ERROR."Invalid HAVING clauses. Add groupings to be able to use HAVING clauses.");
		}
	}

	private function resolveSelectOrders(&$query) {
		if (count($this->_orders) > 0) {
			$query .= "\n\tORDER BY ";
			foreach ($this->_orders AS $field) {
				if (!isset($field['field']) || trim($field['field']) == '') throw new QueryException(_FIELD_ERROR."Invalid field name. See ['field'] value for ordering in array.");
				if (isset($field['table_alias']) && trim($field['table_alias']) == '') throw new QueryException(_FIELD_ERROR."Invalid table alias. See ['table_alias'] value for ordering < ".$field['field']." > in array.");
				if (isset($field['order_type']) && trim($field['order_type']) == '') throw new QueryException(_FIELD_ERROR."Invalid order type. See ['order_type'] value for ordering < ".$field['field']." > in array.");
				$closeFunction = '';
				if (isset ($field['applied_function'])) {
					if (trim ($field['applied_function']) == '') throw new QueryException(_FIELD_ERROR."Invalid function name. See ['applied_function'] value for field < ".$field['field']." > in array.");
					$query .= trim($field['applied_function']).(isset ($field['distinct']) ? "(DISTINCT " : "(");
					$closeFunction = ")";
				}
				$query .= ((isset ($field['table_alias'])) ?
					trim($field['table_alias']).".".trim($field['field']) :
					trim($field['field'])).$closeFunction;
				$query .= isset($field['order_type']) ? ' '.$field['order_type'].', ' : ', ';
			}
			$query = trim($query, ", ")." ";
		}
	}

	private function resolveSelectPagination(&$query) {
		if (count ($this->_pagination) > 0) {
			if (isset ($this->_pagination['result_limit'])) {
				if (trim($this->_pagination['result_limit']) === '') throw new QueryException(_GENERAL_ERROR."Invalid value for pagination. See ['result_limit'] value for pagination array.");
				$query .= "LIMIT ".trim($this->_pagination['result_limit'])." ";
			}
			if (isset ($this->_pagination['result_offset'])) {
				if (trim($this->_pagination['result_offset']) === '') throw new QueryException(_GENERAL_ERROR."Invalid value for pagination. See ['result_offset'] value for pagination array.");
				$query .= "OFFSET ".trim($this->_pagination['result_offset'])." ";
			}
		}
	}

	private function resolveJoins(&$query) {
		if (count($this->_tables) > 1) {
			if (!isset($this->_tables[0]['alias']) || trim($this->_tables[0]['alias']=='')) throw new QueryException(_TABLE_ERROR."Cannot join against a table whose alias has not been set. See ['alias'] value for table < ".$this->_tables[0]['table']." > in array.");
			$joinClose = true;
			$joinClauses = array();
			for($i=1; $i<count($this->_tables); $i++) {
				$query .= "\n\t".$this->_tables[$i]['join_type']." "; 
				if (isset ($this->_tables[$i+1]) && isset ($this->_tables[$i+1]['inner_to_previous']) && $this->_tables[$i+1]['inner_to_previous'] !== false) {
					if ($this->_tables[$i+1]['foreign_join_alias'] != $this->_tables[$i]['alias']) throw new QueryException(_TABLE_ERROR."Encapsulated join must reference previous table, ['foreign_join_alias'] must be same as ['alias'] for previous table.");
					$query .= "( ";
					$joinClose = false;
				} else {
					$joinClose = true;
				}
				$query .= $this->_tables[$i]['table']." ".$this->_tables[$i]['alias'];
				$joinClauses[] = " ON ".$this->_tables[$i]['alias'].".".$this->_tables[$i]['local_join_field'].
							" = ".$this->_tables[$i]['foreign_join_alias'].".".$this->_tables[$i]['foreign_join_field']." ";
				if ($joinClose) {
					$valueCount = count($joinClauses);
					$enc = "";
					for ($j=0; $j<$valueCount; $j++) {
						$clause = array_pop($joinClauses);
						$query .= $enc.$clause;
						$enc = ")";
					}
				}
			}
		}
	}

	public function escape($value) {
		return pg_escape_string($value);
	}
}
?>
