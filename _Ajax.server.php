<?php

require("_Ajax.comun.php"); // No modificar esta linea
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */

function normalizar_lista($valor)
{
    if (is_array($valor)) {
        return array_values(array_filter($valor, 'strlen'));
    }
    if (empty($valor) || $valor === '0') {
        return array();
    }
    return array($valor);
}

function normalizar_formulario($aForm)
{
    if (!is_array($aForm)) {
        return $aForm;
    }
    $normalizado = array();
    foreach ($aForm as $key => $value) {
        $normalizado[trim($key)] = $value;
    }
    return $normalizado;
}

function lista_sql($items)
{
    $items = array_map(function ($item) {
        return "'" . addslashes($item) . "'";
    }, $items);
    return implode(',', $items);
}

function obtener_grupos_empresa($oIfx, $empresa)
{
    $grupos = array();
    $sql = "select gact_cod_gact from saegact where gact_cod_empr = $empresa";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $grupos[] = $oIfx->f('gact_cod_gact');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    return $grupos;
}

function obtener_subgrupos_empresa($oIfx, $empresa, $grupos)
{
    $subgrupos = array();
    if (empty($grupos)) {
        $sql = "select sgac_cod_sgac from saesgac where sgac_cod_empr = $empresa";
    } else {
        $sql = "select sgac_cod_sgac from saesgac where sgac_cod_empr = $empresa and gact_cod_gact in (" . lista_sql($grupos) . ")";
    }
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $subgrupos[] = $oIfx->f('sgac_cod_sgac');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    return $subgrupos;
}

function calcular_meses($anio_desde, $mes_desde, $anio_hasta, $mes_hasta)
{
    $inicio = DateTime::createFromFormat('Y-n-j', $anio_desde . '-' . $mes_desde . '-1');
    $fin = DateTime::createFromFormat('Y-n-j', $anio_hasta . '-' . $mes_hasta . '-1');
    if (!$inicio || !$fin || $inicio > $fin) {
        return 0;
    }
    $meses = 0;
    $actual = clone $inicio;
    while ($actual <= $fin) {
        $meses++;
        $actual->modify('+1 month');
    }
    return $meses;
}

function formatear_periodo($anio, $mes)
{
    return sprintf('%04d-%02d', $anio, $mes);
}

function f_filtro_sucursal($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables formulario
    $aForm = normalizar_formulario($aForm);
    $empresa = $aForm['empresa'];
    if (empty($empresa)) {
        $empresa = $_SESSION['U_EMPRESA'];
    }

    // DATOS EMPRESA
    $sql = "select sucu_cod_sucu, sucu_nom_sucu
			from saesucu
			where sucu_cod_empr = '$empresa'			
			order by sucu_nom_sucu";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_sucursal();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_sucursal(' . $i++ . ',\'' . $oIfx->f('sucu_cod_sucu') . '\', \'' . $oIfx->f('sucu_nom_sucu') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign('sucursal', 'value', $data);
    return $oReturn;
}

function f_filtro_anio($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $aForm = normalizar_formulario($aForm);
    $idempresa = $_SESSION['U_EMPRESA'];
    //variables formulario
    $empresa = $aForm['empresa'];
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    // DATOS EMPRESA
    $sql = "select ejer_fec_inil, date_part('year',ejer_fec_inil) as anio_i 
			from saeejer 
			where ejer_cod_empr = $empresa
			order by anio_i desc";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_anio_desde();');
        $oReturn->script('eliminar_lista_anio_hasta();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_anio_desde(' . $i . ',\'' . $oIfx->f('anio_i') . '\',\'' . $oIfx->f('anio_i') . '\')'));
                $oReturn->script(('anadir_elemento_anio_hasta(' . $i . ',\'' . $oIfx->f('anio_i') . '\',\'' . $oIfx->f('anio_i') . '\')'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign('anio_desde', 'value', $data);
    $oReturn->assign('anio_hasta', 'value', $data);
    return $oReturn;
}

function f_filtro_activos_desde($aForm)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    // variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    //variables formulario
    $aForm = normalizar_formulario($aForm);
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $grupo = normalizar_lista($aForm['cod_grupo']);
    $subgrupo = normalizar_lista($aForm['cod_subgrupo']);
    $solo_vigentes = !empty($aForm['solo_vigentes']) ? 1 : 0;
    $generar_mensual = !empty($aForm['generar_mensual']) ? 1 : 0;
    $debug_filtros = !empty($aForm['debug_filtros']) ? 1 : 0;
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }
    if (empty($subgrupo)) {
        $oReturn->script('eliminar_lista_activo_desde();');
        $oReturn->script('eliminar_lista_activo_hasta();');
        return $oReturn;
    }
    // DATOS DEL ACTIVO
    $filtro_grupo = '';
    if (!empty($grupo)) {
        $filtro_grupo = " and gact_cod_gact in (" . lista_sql($grupo) . ")";
    }
    $sql = "select distinct act_cod_act, act_nom_act, act_clave_act
			from saeact
			where act_cod_empr = '$empresa'
            and act_cod_sucu = '$sucursal'
			and sgac_cod_sgac in (" . lista_sql($subgrupo) . ")
            $filtro_grupo
            and ($solo_vigentes = 0 or act_ext_act = 1)
			order by act_cod_act";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_activo_desde();');
        $oReturn->script('eliminar_lista_activo_hasta();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_activo_desde(' . $i++ . ',\'' . $oIfx->f('act_cod_act') . '\', \'' . $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act') . '\' )'));
                $oReturn->script(('anadir_elemento_activo_hasta(' . ($i - 1) . ',\'' . $oIfx->f('act_cod_act') . '\', \'' . $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        } else {
            $oReturn->script("Swal.fire({
                position: 'center',
                type: 'info',
                title: 'No existen activos fijos para los subgrupos seleccionados.',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            })");
        }
    }
    return $oReturn;
}

function f_filtro_activos_hasta($aForm)
{
    return f_filtro_activos_desde($aForm);
}

function f_filtro_mes($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $aForm = normalizar_formulario($aForm);

    $meses = array(
        '1' => 'Enero',
        '2' => 'Febrero',
        '3' => 'Marzo',
        '4' => 'Abril',
        '5' => 'Mayo',
        '6' => 'Junio',
        '7' => 'Julio',
        '8' => 'Agosto',
        '9' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    );
    $oReturn->script('eliminar_lista_mes_desde();');
    $oReturn->script('eliminar_lista_mes_hasta();');
    $i = 1;
    foreach ($meses as $codigo => $descripcion) {
        $oReturn->script(('anadir_elemento_mes_desde(' . $i . ',\'' . $codigo . '\', \'' . $descripcion . '\' )'));
        $oReturn->script(('anadir_elemento_mes_hasta(' . $i . ',\'' . $codigo . '\', \'' . $descripcion . '\' )'));
        $i++;
    }

    $oReturn->assign('mes_desde', 'value', $data);
    $oReturn->assign('mes_hasta', 'value', $data);

    return $oReturn;
}

function f_filtro_grupo($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $aForm = normalizar_formulario($aForm);
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    //variables formulario
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    // DATOS DEL GRUPO POR EMPRESA
    $sql = "select distinct gact_cod_gact, gact_des_gact 
			 from saegact 
			 where gact_cod_empr = '$empresa'
			 order by gact_des_gact";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_grupo();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_grupo(' . $i++ . ',\'' . $oIfx->f('gact_cod_gact') . '\', \'' . $oIfx->f('gact_des_gact') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oReturn->script('eliminar_lista_subgrupo();');
    $oReturn->assign('cod_activo_desde', 'value', null);
    $oReturn->assign('cod_activo_hasta', 'value', null);

    return $oReturn;
}

function f_filtro_subgrupo($aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $aForm = normalizar_formulario($aForm);
    $idempresa = $_SESSION['U_EMPRESA'];
    //variables formulario	
    $empresa = $aForm['empresa'];
    $codigoGrupo = normalizar_lista($aForm['cod_grupo']);
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($codigoGrupo)) {
        $oReturn->script('eliminar_lista_subgrupo();');
        $oReturn->script('f_filtro_activos_desde()');
        return $oReturn;
    }

    // DATOS DEL ACTIVO
    $sql = "select distinct sgac_cod_sgac, sgac_des_sgac 
			 from saesgac where sgac_cod_empr = $empresa                                                                  
			 and gact_cod_gact in (" . lista_sql($codigoGrupo) . ")
			 order by sgac_des_sgac";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_subgrupo();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_subgrupo(' . $i++ . ',\'' . $oIfx->f('sgac_cod_sgac') . '\', \'' . $oIfx->f('sgac_des_sgac') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        } else {
            $oReturn->script("Swal.fire({
                position: 'center',
                type: 'info',
                title: 'No existen subgrupos para los grupos seleccionados.',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            })");
        }
    }
    $oReturn->script('f_filtro_activos_desde()');
    return $oReturn;
}

function prevalidar_depreciacion($aForm = '')
{
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $aForm = normalizar_formulario($aForm);

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $grupo = normalizar_lista($aForm['cod_grupo']);
    $subgrupo = normalizar_lista($aForm['cod_subgrupo']);
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anio_desde = $aForm['anio_desde'];
    $mes_desde = $aForm['mes_desde'];
    $anio_hasta = $aForm['anio_hasta'];
    $mes_hasta = $aForm['mes_hasta'];
    $solo_vigentes = !empty($aForm['solo_vigentes']) ? 1 : 0;

    if (empty($anio_desde) || empty($mes_desde) || empty($anio_hasta) || empty($mes_hasta)) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'El rango de fechas es inválido. Verifique Año y Mes.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    $periodo_desde = (int)($anio_desde . str_pad($mes_desde, 2, '0', STR_PAD_LEFT));
    $periodo_hasta = (int)($anio_hasta . str_pad($mes_hasta, 2, '0', STR_PAD_LEFT));
    if ($periodo_desde > $periodo_hasta) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'El rango de fechas es inválido. Verifique Año y Mes.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    if (empty($grupo)) {
        $grupo = obtener_grupos_empresa($oIfx, $empresa);
    }
    if (empty($grupo)) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Con los filtros actuales no se encontraron activos. Revise grupo o subgrupo.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    if (empty($subgrupo)) {
        $subgrupo = obtener_subgrupos_empresa($oIfx, $empresa, $grupo);
    }

    $filtro_activo = '';
    if (!empty($grupo)) {
        $filtro_activo .= " and gact_cod_gact in (" . lista_sql($grupo) . ")";
    }
    if (!empty($subgrupo)) {
        $filtro_activo .= " and sgac_cod_sgac in (" . lista_sql($subgrupo) . ")";
    }
    if (!empty($activo_desde) && !empty($activo_hasta)) {
        $filtro_activo .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    } elseif (!empty($activo_desde)) {
        $filtro_activo .= " and act_cod_act >= " . $activo_desde;
    } elseif (!empty($activo_hasta)) {
        $filtro_activo .= " and act_cod_act <= " . $activo_hasta;
    }
    if ($solo_vigentes == 1) {
        $filtro_activo .= " and act_ext_act = 1";
    }
    $filtro_activo .= " and act_cod_sucu = $sucursal";

    $sql_activos = "select count(distinct act_cod_act) as total
        from saeact
        where act_cod_empr = $empresa
        and act_cod_sucu = $sucursal
        $filtro_activo";
    $total_activos = consulta_string_func($sql_activos, 'total', $oIfx, 0);
    if (empty($total_activos) || $total_activos == 0) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Con los filtros actuales no se encontraron activos. Revise grupo, subgrupo o vigencia.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    if ($debug_filtros == 1) {
        $oReturn->alert(
            "DEPURACION GENERAR\n" .
            "Empresa: {$empresa}\n" .
            "Sucursal: {$sucursal}\n" .
            "Grupos: " . implode(', ', $grupo) . "\n" .
            "Subgrupos: " . implode(', ', $subgrupo) . "\n" .
            "Activos: {$activo_desde} - {$activo_hasta}\n" .
            "Solo vigentes: {$solo_vigentes}\n" .
            "Generar mensual: {$generar_mensual}\n" .
            "Activos encontrados: {$total_activos}"
        );
    }

    $meses_procesar = calcular_meses($anio_desde, $mes_desde, $anio_hasta, $mes_hasta);
    $periodo_inicio = formatear_periodo($anio_desde, $mes_desde);
    $periodo_fin = formatear_periodo($anio_hasta, $mes_hasta);

    if ($debug_filtros == 1) {
        $oReturn->alert(
            "DEPURACION FILTROS\n" .
            "Empresa: {$empresa}\n" .
            "Sucursal: {$sucursal}\n" .
            "Grupos: " . implode(', ', $grupo) . "\n" .
            "Subgrupos: " . implode(', ', $subgrupo) . "\n" .
            "Activos: {$activo_desde} - {$activo_hasta}\n" .
            "Solo vigentes: {$solo_vigentes}\n" .
            "Generar mensual: {$generar_mensual}\n" .
            "Rango: {$periodo_inicio} a {$periodo_fin}\n" .
            "Activos encontrados: {$total_activos}"
        );
    }

    $operacion = $generar_mensual == 1 ? 'Completar y Recalcular depreciaciones' : 'Reprocesar depreciaciones existentes';
    $oReturn->script("Swal.fire({
        title: 'Confirmar depreciación masiva',
        html: '<div style=\"text-align:left;\">' +
              '<div><strong>Rango:</strong> {$periodo_inicio} a {$periodo_fin}</div>' +
              '<div><strong>Meses a procesar:</strong> {$meses_procesar}</div>' +
              '<div><strong>Activos:</strong> {$total_activos}</div>' +
              '<div><strong>Operación:</strong> {$operacion}</div>' +
              '</div>',
        showCancelButton: true,
        confirmButtonText: 'Procesar',
        cancelButtonText: 'Cancelar',
        type: 'warning'
    }).then(function(result){
        if(result.value){
            xajax_generar(xajax.getFormValues(\"form1\"));
        }
    });");

    return $oReturn;
}

// PROCESAR DEPRECICION
function generar($aForm = '')
{

    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oReturn = new xajaxResponse();

    //variables de sesion
    $aForm = normalizar_formulario($aForm);
    $array = ($_SESSION['ARRAY_PINTA']);
    $usuario_web = $_SESSION['U_ID'];
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables del formulario
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    //variables formulario
    $grupo = normalizar_lista($aForm['cod_grupo']);
    $subgrupo = normalizar_lista($aForm['cod_subgrupo']);
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anio_desde = $aForm['anio_desde'];
    $mes_desde = $aForm['mes_desde'];
    $anio_hasta = $aForm['anio_hasta'];
    $mes_hasta = $aForm['mes_hasta'];
    $solo_vigentes = !empty($aForm['solo_vigentes']) ? 1 : 0;

    if (empty($anio_desde) || empty($mes_desde) || empty($anio_hasta) || empty($mes_hasta)) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'El rango de fechas es inválido. Verifique Año y Mes.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    $periodo_desde = (int)($anio_desde . str_pad($mes_desde, 2, '0', STR_PAD_LEFT));
    $periodo_hasta = (int)($anio_hasta . str_pad($mes_hasta, 2, '0', STR_PAD_LEFT));
    if ($periodo_desde > $periodo_hasta) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'El rango de fechas es inválido. Verifique Año y Mes.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    if (empty($grupo)) {
        $grupo = obtener_grupos_empresa($oIfx, $empresa);
    }
    if (empty($subgrupo)) {
        $subgrupo = obtener_subgrupos_empresa($oIfx, $empresa, $grupo);
    }

    if (empty($grupo)) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Con los filtros actuales no se encontraron activos. Revise grupo o subgrupo.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    // ARMAR FILTROS
    $filtro_activo = '';
    if (!empty($grupo)) {
        $filtro_activo .= " and gact_cod_gact in (" . lista_sql($grupo) . ")";
    }
    if (!empty($subgrupo)) {
        $filtro_activo .= " and sgac_cod_sgac in (" . lista_sql($subgrupo) . ")";
    }
    if (!empty($activo_desde) && !empty($activo_hasta)) {
        $filtro_activo .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    } elseif (!empty($activo_desde)) {
        $filtro_activo .= " and act_cod_act >= " . $activo_desde;
    } elseif (!empty($activo_hasta)) {
        $filtro_activo .= " and act_cod_act <= " . $activo_hasta;
    }
    if ($solo_vigentes == 1) {
        $filtro_activo .= " and act_ext_act = 1";
    }

    $sql_activos = "select count(distinct act_cod_act) as total
        from saeact
        where act_cod_empr = $empresa
        and act_cod_sucu = $sucursal
        $filtro_activo";
    $total_activos = consulta_string_func($sql_activos, 'total', $oIfx, 0);
    if (empty($total_activos) || $total_activos == 0) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Con los filtros actuales no se encontraron activos. Revise grupo, subgrupo o vigencia.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }

    $fechaServer = date("Y-m-d");
    $warnings = array();
    $registros_generados = 0;
    $registros_reprocesados = 0;
    $meses_omitidos = 0;
    $valores_mensuales_creados = 0;

    $meses_procesados = calcular_meses($anio_desde, $mes_desde, $anio_hasta, $mes_hasta);
    $periodo_inicio = formatear_periodo($anio_desde, $mes_desde);
    $periodo_fin = formatear_periodo($anio_hasta, $mes_hasta);

    $fecha_desde_obj = DateTime::createFromFormat('Y-n-j', $anio_desde . '-' . $mes_desde . '-1');
    $fecha_hasta_obj = DateTime::createFromFormat('Y-n-j', $anio_hasta . '-' . $mes_hasta . '-1');
    $fecha_desde = $fecha_desde_obj->format('Y-m-d');
    $fecha_hasta_obj->modify('last day of this month');
    $fecha_hasta = $fecha_hasta_obj->format('Y-m-d');

    try {
        $oIfx->QueryT('BEGIN');
        $oIfx->Free();

        if ($generar_mensual == 1) {
            $sql_count_missing = "SELECT COUNT(1) AS total
                FROM saeact a
                CROSS JOIN generate_series(
                    DATE '$fecha_desde',
                    DATE '$fecha_hasta',
                    INTERVAL '1 month'
                ) AS gs(mes)
                LEFT JOIN saemet sm
                    ON sm.metd_cod_acti = a.act_cod_act
                    AND sm.metd_has_fech = (date_trunc('month', gs.mes) + INTERVAL '1 month - 1 day')::date
                    AND sm.metd_cod_empr = a.act_cod_empr
                    AND sm.act_cod_empr = a.act_cod_empr
                    AND sm.act_cod_sucu = a.act_cod_sucu
                WHERE a.act_cod_empr = $empresa
                  AND a.act_cod_sucu = $sucursal
                  $filtro_activo
                  AND a.act_vutil_act IS NOT NULL
                  AND a.act_vutil_act > 0
                  AND a.act_val_comp IS NOT NULL
                  AND a.act_val_comp > 0
                  AND sm.metd_cod_acti IS NULL";
            $pendientes = consulta_string_func($sql_count_missing, 'total', $oIfx, 0);

            if (!empty($pendientes) && $pendientes > 0) {
                $sql_insert_met = "INSERT INTO saemet (
                        met_anio_met,
                        metd_des_fech,
                        metd_has_fech,
                        metd_cod_empr,
                        metd_cod_acti,
                        act_cod_empr,
                        act_cod_sucu,
                        metd_val_metd,
                        met_num_dias
                    )
                    SELECT
                        EXTRACT(YEAR FROM gs.mes)::int AS met_anio_met,
                        date_trunc('month', gs.mes)::date AS metd_des_fech,
                        (date_trunc('month', gs.mes) + INTERVAL '1 month - 1 day')::date AS metd_has_fech,
                        a.act_cod_empr AS metd_cod_empr,
                        a.act_cod_act AS metd_cod_acti,
                        a.act_cod_empr,
                        a.act_cod_sucu,
                        ROUND(
                            (a.act_val_comp - COALESCE(a.act_vres_act, 0))
                            / NULLIF(a.act_vutil_act * 12, 0),
                            6
                        ) AS metd_val_metd,
                        EXTRACT(DAY FROM (date_trunc('month', gs.mes) + INTERVAL '1 month - 1 day'))::int AS met_num_dias
                    FROM saeact a
                    CROSS JOIN generate_series(
                        DATE '$fecha_desde',
                        DATE '$fecha_hasta',
                        INTERVAL '1 month'
                    ) AS gs(mes)
                    LEFT JOIN saemet sm
                        ON sm.metd_cod_acti = a.act_cod_act
                        AND sm.metd_has_fech = (date_trunc('month', gs.mes) + INTERVAL '1 month - 1 day')::date
                        AND sm.metd_cod_empr = a.act_cod_empr
                        AND sm.act_cod_empr = a.act_cod_empr
                        AND sm.act_cod_sucu = a.act_cod_sucu
                    WHERE a.act_cod_empr = $empresa
                      AND a.act_cod_sucu = $sucursal
                      $filtro_activo
                      AND a.act_vutil_act IS NOT NULL
                      AND a.act_vutil_act > 0
                      AND a.act_val_comp IS NOT NULL
                      AND a.act_val_comp > 0
                      AND sm.metd_cod_acti IS NULL";
                $oIfx->QueryT($sql_insert_met);
                $valores_mensuales_creados += (int)$pendientes;
            }
        }

        $sql_activos = "SELECT distinct
                act_cod_act,
                act_clave_act,
                gact_cod_gact,
                sgac_cod_sgac,
                tdep_cod_tdep,
                act_vutil_act,
                act_val_comp,
                act_vres_act
            FROM saeact
            WHERE act_cod_empr = $empresa
            and act_cod_sucu = $sucursal
            $filtro_activo
            ORDER BY act_cod_act";

        if (!$oIfxA->Query($sql_activos) || $oIfxA->NumFilas() == 0) {
            $oIfx->QueryT('ROLLBACK');
            $oReturn->script("Swal.fire({
                position: 'center',
                type: 'warning',
                title: 'No existen activos para los filtros seleccionados.',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            })");
            return $oReturn;
        }

        $activos = array();
        do {
            $activos[] = array(
                'codigo' => $oIfxA->f('act_cod_act'),
                'clave' => $oIfxA->f('act_clave_act'),
                'tipo_dep' => $oIfxA->f('tdep_cod_tdep'),
                'vida_util' => $oIfxA->f('act_vutil_act'),
                'valor_compra' => $oIfxA->f('act_val_comp'),
                'valor_residual' => $oIfxA->f('act_vres_act')
            );
        } while ($oIfxA->SiguienteRegistro());

        $meses = array();
        $cursor = clone $fecha_desde_obj;
        while ($cursor <= $fecha_hasta_obj) {
            $meses[] = array(
                'anio' => (int)$cursor->format('Y'),
                'mes' => (int)$cursor->format('n'),
                'fecha_inicio' => $cursor->format('Y-m-01'),
                'fecha_fin' => $cursor->format('Y-m-t')
            );
            $cursor->modify('+1 month');
        }

        foreach ($activos as $activo) {
            foreach ($meses as $mes_info) {
                $codigo_activo = $activo['codigo'];
                $clave_activo = $activo['clave'];
                $tipo_depreciacion = $activo['tipo_dep'];
                $anio = $mes_info['anio'];
                $mes = $mes_info['mes'];
                $fecha_mes = $mes_info['fecha_fin'];
                $periodo_mes = formatear_periodo($anio, $mes);

                $sql_valor = "select metd_val_metd
                    from saemet
                    where metd_cod_acti = $codigo_activo
                    and metd_has_fech = '$fecha_mes'
                    and metd_cod_empr = $empresa
                    and act_cod_empr = $empresa
                    and act_cod_sucu = $sucursal";
                $valor_mensual = consulta_string($sql_valor, 'metd_val_metd', $oIfx, 0);

                if (empty($valor_mensual) || $valor_mensual == 0) {
                    if ($generar_mensual == 1) {
                        $vida_util = (float)$activo['vida_util'];
                        $valor_compra = (float)$activo['valor_compra'];
                        $valor_residual = (float)$activo['valor_residual'];
                        $vida_util_meses = $vida_util * 12;
                        if ($vida_util_meses <= 0 || $valor_compra <= 0) {
                            $meses_omitidos++;
                            $warnings[] = "No se generó valor mensual para el activo {$clave_activo} en {$periodo_mes} porque no tiene vida útil o valor de compra.";
                            continue;
                        }
                        $valor_mensual = ($valor_compra - $valor_residual) / $vida_util_meses;
                        if ($valor_mensual <= 0) {
                            $meses_omitidos++;
                            $warnings[] = "No se generó valor mensual para el activo {$clave_activo} en {$periodo_mes} porque el cálculo resultó en cero.";
                            continue;
                        }

                        $sql_insert_met = "INSERT INTO saemet (
                            met_anio_met,
                            metd_des_fech,
                            metd_has_fech,
                            metd_cod_empr,
                            metd_cod_acti,
                            act_cod_empr,
                            act_cod_sucu,
                            metd_val_metd
                        ) VALUES (
                            $anio,
                            '{$mes_info['fecha_inicio']}',
                            '{$mes_info['fecha_fin']}',
                            $empresa,
                            $codigo_activo,
                            $empresa,
                            $sucursal,
                            $valor_mensual
                        )";
                        $oIfx->QueryT($sql_insert_met);
                        $valores_mensuales_creados++;
                    } else {
                        $meses_omitidos++;
                        $warnings[] = "No se generó depreciación para el activo {$clave_activo} en {$periodo_mes} porque no existe valor mensual en saemet.";
                        continue;
                    }
                }

                $sql_existe = "select count(cdep_gas_depn) as existe
                    from saecdep
                    where cdep_cod_acti = $codigo_activo
                    and cdep_ani_depr = $anio
                    and cdep_mes_depr = $mes
                    and act_cod_empr = $empresa";
                $existe = consulta_string($sql_existe, 'existe', $oIfx, 0);

                if ($existe > 0) {
                    $sql_borra = "delete from saecdep
                        where cdep_cod_acti = $codigo_activo
                        and cdep_ani_depr = $anio
                        and cdep_mes_depr = $mes
                        and act_cod_empr = $empresa";
                    $oIfx->QueryT($sql_borra);
                    $registros_reprocesados++;
                } else {
                    $registros_generados++;
                }

                $fechaAnterior = (new DateTime($fecha_mes))->modify('last day of previous month')->format('Y-m-d');
                $sql_dep_acumulada = "SELECT (coalesce(cdep_dep_acum, 0) + coalesce(cdep_gas_depn, 0)) as depr_acumulada
                    from saecdep
                    where cdep_cod_acti = $codigo_activo
                    and cdep_fec_depr = '$fechaAnterior'";
                $valor_acumulado = consulta_string($sql_dep_acumulada, 'depr_acumulada', $oIfx, 0);

                if ($valor_acumulado == 0) {
                    $valor_anterior = 0;
                    $valor_acumulado = $valor_mensual;
                } else {
                    $valor_anterior = $valor_acumulado - $valor_mensual;
                }

                $sql_cdep = "INSERT into saecdep (cdep_cod_acti, cdep_cod_tdep,     cdep_mes_depr, cdep_ani_depr,
                                                     cdep_fec_depr, act_cod_empr,       act_cod_sucu,  cdep_dep_acum,
                                                     cdep_gas_depn, cdep_tot_depr,      cdep_est_cdep, cdep_fec_cdep, cdep_val_rep1 )
                                    values ($codigo_activo, '$tipo_depreciacion', $mes, $anio,
                                                '$fecha_mes',  $empresa,            $sucursal,      $valor_acumulado ,
                                                $valor_mensual,      $valor_mensual, 'PE',           '$fechaServer',    $valor_anterior)";
                $oIfx->QueryT($sql_cdep);
            }
        }

        $oIfx->QueryT('COMMIT WORK;');

        $detalle_advertencias = '';
        if (!empty($warnings)) {
            $detalle_advertencias = '<div style=\"max-height:200px; overflow:auto; text-align:left;\">' .
                implode('<br>', $warnings) .
                '</div>';
        } else {
            $detalle_advertencias = '<div>No se registraron advertencias.</div>';
        }

        $oReturn->script("Swal.fire({
            title: 'Proceso finalizado',
            html: '<div style=\"text-align:left;\">' +
                  '<div><strong>Rango procesado:</strong> {$periodo_inicio} a {$periodo_fin}</div>' +
                  '<div><strong>Activos evaluados:</strong> {$total_activos}</div>' +
                  '<div><strong>Meses evaluados:</strong> {$meses_procesados}</div>' +
                  '<div><strong>Valores mensuales creados (saemet):</strong> {$valores_mensuales_creados}</div>' +
                  '<div><strong>Depreciaciones generadas:</strong> {$registros_generados}</div>' +
                  '<div><strong>Registros reprocesados:</strong> {$registros_reprocesados}</div>' +
                  '<div><strong>Meses omitidos (sin datos mínimos):</strong> {$meses_omitidos}</div>' +
                  '<hr>' +
                  '<div><strong>Advertencias:</strong></div>' +
                  '{$detalle_advertencias}' +
                  '</div>',
            confirmButtonText: 'OK',
            type: 'success',
            width: 700
        })");
    } catch (Exception $e) {
        $oCon->QueryT('ROLLBACK');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'error',
            title: 'Ocurrió un error al procesar la depreciación. Intente nuevamente.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
    }
    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
