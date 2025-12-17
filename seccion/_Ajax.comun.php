<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */
/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/mysql.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/informix.inc.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');
include_once(path(DIR_INCLUDE).'Clases/Formulario/Formulario.class.php');
require_once (path(DIR_INCLUDE).'Clases/xajax/xajax_core/xajax.inc.php');
require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleAsientoContable.class.php');
require_once (path(DIR_INCLUDE).'Clases/GeneraDetalleInventario.class.php');

include_once(path(DIR_INCLUDE).'comun.lib.rd.php');

/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding('ISO-8859-1');
/***************************************************/
//	FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//	Aqui registrar todas las funciones publicas del servidor ajax
//	Ejemplo,
//	$xajax->registerFunction("Nombre de la Funcion");
/***************************************************/
//	Fuciones de lista de pedido


$xajax->registerFunction("cargar_ord_compra_respaldo");
$xajax->registerFunction("guardar");
$xajax->registerFunction("borrar");
$xajax->registerFunction("parametros");
$xajax->registerFunction("actualiza_finca");
$xajax->registerFunction("borrar_finca");
$xajax->registerFunction("genera_formulario_pedido");
$xajax->registerFunction("cargarListaSector");
$xajax->registerFunction("cargarListaSector2");


/***************************************************/
?>