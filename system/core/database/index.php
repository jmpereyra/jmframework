<?php
defined ("_DB_SYST") or die ("Ahora hay que crear el archivo de configuración de base de datos en las carpetas de configuración del proyecto");
chdir(dirname(__FILE__));
require_once ".".DIRECTORY_SEPARATOR."ConnectionManager.php";

//LOS PARAMETROS DE CONEXION SE CONFIGURAN EN EL ARCHIVO db_config.php dentro de database/configuration/
echo "TEST DE CONEXION: <br/>";
echo "datos:<br/>";
echo (ConnectionManager::testConfig() ? "TRUE" : "FALSE" ).'<br/>';
echo "conexión:<br/>";
$conn = ConnectionManager::getDBConnector();
echo ($conn->isConnected() ? "TRUE" : "FALSE" ).'<hr/>';

echo "SISTEMA DE CONEXION: <br/>";

//EL OBJETO CONEXION ESTE ES EL QUE ENCARA, PORQUE A EL LE VAMOS A PEDIR TODO, YA QUE AL SABER A QUE
//TIPO DE DBMS SE CONECTA, LES VA A DEVOLVER LOS OBJETOS CORRESPONDIENTES A LO QUE UDS QUIEREN

echo $conn->getActiveDBMS().'<hr/>';

$query = ConnectionManager::getQuery($conn);

//POR AHORA LA MAYORIA DE LAS COSAS SI QUIEREN USAR EL GENERADOR DE SQL SE TIENEN QUE USAR ASI:
//(NO ES LA FORMA OPTIMA PERO POR AHORA ES LO QUE HAY, DESPUES VA A QUEDAR COMO LOS JOINS ABAJO)

//LAS TABLAS PRINCIPALES (ASI COMO LOS JOINS) SE AGREGAN EN UN ARRAY DE ESTE TIPO:
// <tablas>
$table = array ('table' => 'sb_test_join_A', 'alias' => 'tjA');
// </tablas>

//LOS CAMPOS A SELECCIONAR (SE LES PUEDE AGREGAR FUNCIONES) EN UN ARRAY ASI:
// <campos>
/*$fields = array(array('field' => 'previous_version', 'alias' => 'item_count', 'table_alias' => 'dbv', 'applied_function' => 'COUNT'),
					array('field' => 'actual_version', 'alias' => 'version', 'table_alias' => 'dbv'));*/
$fields = array(array('field' => 'a_id', 'table_alias' => 'tjA', 'alias' => 'counter', 'applied_function' => 'COUNT', 'distinct' => 1), array('field' => 'b_info', 'table_alias' => 'tjB'));
// </campos>

//LAS CLAUSULAS (TANTO PARA WHERE COMO PARA HAVING Y TAMBIEN PUEDEN TENER FUNCIONES) ASI:
// <clausulas>
/*$clauses = array(array('field' => 'previous_version', 'table_alias' => 'dbv', 'comparison_value' => "0", 'comparison_sign' => '='),
			array('field' => 'actual_version', 'table_alias' => 'dbv', 'comparison_value' => "1", 'comparison_sign' => '=', 'clause_type' => _CLAUSE_HAVING, 'applied_function' => 'COUNT'));*/
$clauses = array(array('field' => 'b_a_id', 'table_alias' => 'tjB', 'comparison_value' => 'NULL', 'comparison_sign' => 'IS NOT'),
				array('field' => 'a_id', 'table_alias' => 'tjA', 'comparison_value' => 2, 'comparison_sign' => '<', 'clause_operator' => 'AND'),
				array('field' => 'a_id', 'table_alias' => 'tjA', 'comparison_value' => 1, 'comparison_sign' => '>', 'clause_type' => _CLAUSE_HAVING, 'applied_function' => 'COUNT'));
// </clausulas>

//SI QUEREMOS AGRUPAR, ORDENAR O PAGINAR:
// <resto de las cosas>
/*$groupings = array(array('field' => 'actual_version', 'table_alias' => 'dbv'),
					array('field' => 'previous_version', 'table_alias' => 'dbv'));*/
$groupings = array(array('field' => 'a_info', 'table_alias' => 'tjA'));

$orders = array(array('field' => 'a_id', 'table_alias' => 'tjA', 'order_type' => _ORDER_DESC, 'applied_function' => 'COUNT'));

$pagination = array('result_limit' => 10, 'result_offset' => 0);
// </resto de las cosas>

//CREAN LA CONSULTA DE SELECT (COMO VERAN LO UNICO OBLIGATORIO ES UNA TABLA):
echo "Consulta simple:";
$query->selectQuery($table);
var_dump($query->getQueryString());
echo '<hr/>';
//ACA VA UNA CONSULTA CON MAS COMPLEJIDAD (SOLO A MODO INFORMATIVO USANDO LAS COSAS CREADAS ARRIBA):
//NOTESE QUE CADA VEZ QUE LLAMAMOS AL METODO INICIAL DE UNA CONSULTA SE RESETEAN LOS VALORES DE ESTA
//METODOS COMO ESTE SON "CHAINABLE"
echo "Consulta mas compleja:";
$query->selectQuery($table, $clauses, $fields, $groupings, $orders, $pagination);
var_dump($query->getQueryString());
echo '<hr/>';
//PARA LOS JOINS SI ESTA PRONTO UN METODO MAS BONITO PARA AGREGARLOS QUE CREAR EL ARRAY DE TABLAS
//DESPUES TODOS VAN A SER ASI (NO ME PEGUEN, SOY GIORDANO)
echo "Agregamos unos joins (como ven por ahi, se pueden anidar):";
$query->addExplicitJoin(_RIGHT_JOIN, 'sb_test_join_b', 'tjB', 'b_a_id', 'tjA', 'a_id')
    ->addExplicitJoin(_INNER_JOIN, 'sb_test_join_b', 'tjB2', 'b_id', 'tjB', 'b_id', true)
    ->addExplicitJoin(_LEFT_JOIN, 'sb_test_join_a', 'tjA2', 'a_id', 'tjA', 'a_id');
var_dump($query->getQueryString());
echo '<hr/>';
//Y SI NO... SI LES PARECE QUE TODO ESTO ES UNA TOMADA DE PELO 
//PUEDEN USAR EL METODO SIMPLE.
echo "Y si no:";
$query->setQueryString("SELECT trabajadores FROM skyblueideas WHERE trabajador IN (se_manejan, XD);");
//EN REALIDAD ESE METODO TAMBIEN TE DEJA METER "SUBCONSULTAS" O SEA, BASICAMENTE
//UNA CONSULTA COMO PARTE DE UN CAMPO, TABLA DE JOIN O PARTE DE UNA CLAUSULA. POR AHORA LISTO
var_dump ($query->getQueryString());
ConnectionManager::closeConnector($conn);
