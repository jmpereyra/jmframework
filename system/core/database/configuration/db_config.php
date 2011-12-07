<?php
/**
 * @author Juan Manuel Pereyra, SkyBlue Ideas (jmpereyra@gmail.com)
 */

defined ("_DB_SYST") or die ("No database definition found");

//STANDARD QUERY TYPES
define ('_SELECT_QUERY', 0);
define ('_INSERT_QUERY', 1);
define ('_UPDATE_QUERY', 2);

//JOIN TYPES
define ('_PRIMARY_TABLE', 'NO JOIN');
define ('_INNER_JOIN', 'INNER JOIN');
define ('_LEFT_JOIN', 'LEFT JOIN');
define ('_RIGHT_JOIN', 'RIGHT JOIN');

//INTERNAL QUERY TYPES
define ('_QUERY_AS_PRIMARY', 0);
define ('_QUERY_AS_FIELD', 1);
define ('_QUERY_AS_TABLE', 2);
define ('_QUERY_AS_CLAUSE', 3);

//CLAUSE OPERATORS
define ('_CLAUSE_AND', 'AND');
define ('_CLAUSE_OR', 'OR');
define ('_CLAUSE_NOT', 'NOT');

//CLAUSE TYPES
define ('_CLAUSE_WHERE', 0);
define ('_CLAUSE_HAVING', 1);

//Error labels
define ('_GENERAL_ERROR', '[GENERAL ERROR] ');
define ('_TABLE_ERROR', '[TABLE ERROR] ');
define ('_FIELD_ERROR', '[FIELD ERROR] ');
define ('_CLAUSE_ERROR', '[CLAUSE ERROR] ');

//Order types
define ('_ORDER_ASC', 'ASC');
define ('_ORDER_DESC', 'DESC');

$__available_dbms = array ('PostgreSQL', 'MySQL');
?>
