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
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $grupo = normalizar_lista($aForm['cod_grupo']);
    $subgrupo = normalizar_lista($aForm['cod_subgrupo']);
    $solo_vigentes = !empty($aForm['solo_vigentes']) ? 1 : 0;
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
    $sql = "select distinct act_cod_act, act_nom_act, act_clave_act
			from saeact
			where act_cod_empr = '$empresa'
			and sgac_cod_sgac in (" . lista_sql($subgrupo) . ")
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
                type: 'warning',
                title: 'No existen activos para los filtros seleccionados.',
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
    $sql = "select gact_cod_gact, gact_des_gact 
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
    if (empty($subgrupo)) {
        $subgrupo = obtener_subgrupos_empresa($oIfx, $empresa, $grupo);
    }

    $filtro = '';
    if (!empty($grupo)) {
        $filtro = " and gact_cod_gact in (" . lista_sql($grupo) . ")";
    }
    if (!empty($subgrupo)) {
        $filtro .= " and sgac_cod_sgac in (" . lista_sql($subgrupo) . ")";
    }
    if (!empty($activo_desde) && !empty($activo_hasta)) {
        $filtro .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    } elseif (!empty($activo_desde)) {
        $filtro .= " and act_cod_act >= " . $activo_desde;
    } elseif (!empty($activo_hasta)) {
        $filtro .= " and act_cod_act <= " . $activo_hasta;
    }

    $sql_activos = "select count(distinct act_cod_act) as total
        from saeact
        where act_cod_empr = $empresa
        and ($solo_vigentes = 0 or act_ext_act = 1)
        $filtro";
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

    $meses_procesar = calcular_meses($anio_desde, $mes_desde, $anio_hasta, $mes_hasta);
    $periodo_inicio = formatear_periodo($anio_desde, $mes_desde);
    $periodo_fin = formatear_periodo($anio_hasta, $mes_hasta);

    $oReturn->script("Swal.fire({
        title: 'Confirmar depreciación masiva',
        html: '<div style=\"text-align:left;\">' +
              '<div><strong>Rango:</strong> {$periodo_inicio} a {$periodo_fin}</div>' +
              '<div><strong>Meses a procesar:</strong> {$meses_procesar}</div>' +
              '<div><strong>Activos:</strong> {$total_activos}</div>' +
              '<div><strong>Operación:</strong> Recalcular / Generar depreciaciones</div>' +
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

    // ARMAR FILTROS
    $filtro = '';
    if (!empty($grupo)) {
        $filtro = " and saeact.gact_cod_gact in (" . lista_sql($grupo) . ")";
    }
    if (!empty($subgrupo)) {
        $filtro .= " and saeact.sgac_cod_sgac in (" . lista_sql($subgrupo) . ")";
    }
    if (!empty($activo_desde) && !empty($activo_hasta)) {
        $filtro .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    } elseif (!empty($activo_desde)) {
        $filtro .= " and act_cod_act >= " . $activo_desde;
    } elseif (!empty($activo_hasta)) {
        $filtro .= " and act_cod_act <= " . $activo_hasta;
    }

    $sql_activos = "select count(distinct act_cod_act) as total
        from saeact
        where act_cod_empr = $empresa
        and ($solo_vigentes = 0 or act_ext_act = 1)
        $filtro";
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

    $activos_omitidos_vigencia = 0;
    if ($solo_vigentes == 1) {
        $sql_omitidos = "select count(distinct act_cod_act) as total
            from saeact
            where act_cod_empr = $empresa
            and (act_ext_act <> 1 or act_ext_act is null)
            $filtro";
        $activos_omitidos_vigencia = consulta_string_func($sql_omitidos, 'total', $oIfx, 0);
    }

    $fechaServer = date("Y-m-d");
    $warnings = array();
    $registros_generados = 0;
    $registros_reprocesados = 0;
    $meses_sin_saemet = 0;

    $meses_procesados = calcular_meses($anio_desde, $mes_desde, $anio_hasta, $mes_hasta);
    $periodo_inicio = formatear_periodo($anio_desde, $mes_desde);
    $periodo_fin = formatear_periodo($anio_hasta, $mes_hasta);

    try {
        $oIfx->QueryT('BEGIN');
        $arrayTipoDepre = array();
        // TIPO DE DEPRECIACION
        $sql_tipo = "select tdep_cod_tdep, tdep_tip_val 
						from saetdep";

        if ($oIfx->Query($sql_tipo)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayTipoDepre);
                do {
                    $arrayTipoDepre[$oIfx->f('tdep_cod_tdep')] = $oIfx->f('tdep_tip_val');
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $oIfx->Free();

        $inicio = DateTime::createFromFormat('Y-n-j', $anio_desde . '-' . $mes_desde . '-1');
        $fin = DateTime::createFromFormat('Y-n-j', $anio_hasta . '-' . $mes_hasta . '-1');
        $actual = clone $inicio;

        while ($actual <= $fin) {
            $anio = (int)$actual->format('Y');
            $mes = (int)$actual->format('n');
            $periodo_mes = formatear_periodo($anio, $mes);

            $fecha_hasta = (clone $actual)->modify('last day of this month')->format('Y-m-d');
            $fechaAnterior = (clone $actual)->modify('last day of previous month')->format('Y-m-d');

            $sql = "select metd_cod_acti, metd_val_metd 
					from saemet 
					where metd_has_fech = '$fecha_hasta'
					and metd_cod_empr   =  $empresa";
            $arrayValorDepre = array();
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $arrayValorDepre[$oIfx->f('metd_cod_acti')] = $oIfx->f('metd_val_metd');
                    } while ($oIfx->SiguienteRegistro());
                } else {
                    $meses_sin_saemet++;
                    $warnings[] = "No se encontraron datos en saemet para el mes {$periodo_mes}.";
                    $actual->modify('+1 month');
                    continue;
                }
            }
            $oIfx->Free();

            $sql = "  SELECT saeact.act_cod_act,   
						 saeact.act_vutil_act,   
						 saeact.act_val_comp,   
						 saeact.act_fcmp_act,   
						 saeact.tdep_cod_tdep,   
						 saeact.act_fdep_act,   
						 saeact.act_fcorr_act,   
						 saeact.act_clave_act,
						 saesgac.gact_cod_gact,   
						 saeact.sgac_cod_sgac,   
						 saeact.act_cod_sucu,   
						 saeact.act_vres_act  
					FROM saeact,   
						 saesgac  
				   WHERE ( saesgac.sgac_cod_sgac = saeact.sgac_cod_sgac ) and  
						 ( saesgac.sgac_cod_empr = saeact.act_cod_empr ) and  
						 ( saeact.act_clave_padr is null or saeact.act_clave_padr = '') and
						 ( saeact.act_cod_empr = $empresa ) AND  
						 ($solo_vigentes = 0 OR saeact.act_ext_act = 1)
						 $filtro";
            if ($oIfxA->Query($sql)) {
                if ($oIfxA->NumFilas() > 0) {
                    do {
                        $codigo_activo = $oIfxA->f('act_cod_act');
                        $tipo_depreciacion = $oIfxA->f('tdep_cod_tdep');
                        $clave_activo = $oIfxA->f('act_clave_act');

                        $valor_mesual = isset($arrayValorDepre[$codigo_activo]) ? $arrayValorDepre[$codigo_activo] : null;
                        if (empty($valor_mesual) || $valor_mesual <= 0) {
                            $warnings[] = "No se generó depreciación para el activo {$clave_activo} en {$periodo_mes} porque no existe valor mensual en saemet.";
                            continue;
                        }

                        $sql_existe = "select count(cdep_gas_depn) as existe
									from saecdep
									where cdep_cod_acti = $codigo_activo 
									and cdep_fec_depr = '$fecha_hasta'";
                        $existe = consulta_string($sql_existe, 'existe', $oIfx, 0);

                        if ($existe > 0) {
                            $sql_borra = "delete from saecdep 
										where cdep_cod_acti = $codigo_activo 
										and cdep_fec_depr = '$fecha_hasta'";
                            $oIfx->QueryT($sql_borra);
                            $registros_reprocesados++;
                        }

                        $intervalo = $arrayTipoDepre[$tipo_depreciacion];
                        if (empty($intervalo)) {
                            $intervalo = 'M';
                        }

                        $sql_dep_acumulada = "SELECT (coalesce(cdep_dep_acum, 0) +  coalesce(cdep_gas_depn, 0)) as depr_acumulada
										from saecdep
										where cdep_cod_acti = $codigo_activo 
										and cdep_fec_depr = '$fechaAnterior'";
                        $valor_acumulado = consulta_string($sql_dep_acumulada, 'depr_acumulada', $oIfx, 0);

                        if ($valor_acumulado == 0) {
                            $valor_anterior = 0;
                            $valor_acumulado = $valor_mesual;
                        } else {
                            $valor_anterior = $valor_acumulado - $valor_mesual;
                        }

                        $sql_cdep = "INSERT into saecdep (cdep_cod_acti, cdep_cod_tdep,     cdep_mes_depr, cdep_ani_depr, 
                                                     cdep_fec_depr, act_cod_empr,       act_cod_sucu,  cdep_dep_acum, 
                                                     cdep_gas_depn, cdep_est_cdep,      cdep_fec_cdep, cdep_val_rep1 )
					                        values ($codigo_activo, '$tipo_depreciacion', $mes,           $anio, 
                                                    '$fecha_hasta',  $empresa,            $sucursal,      $valor_acumulado , 
                                                    $valor_mesual,      'PE',           '$fechaServer',    $valor_anterior)";
                        $oIfx->QueryT($sql_cdep);
                        $registros_generados++;
                    } while ($oIfxA->SiguienteRegistro());
                } else {
                    $warnings[] = "No se encontraron activos para el mes {$periodo_mes} con los filtros seleccionados.";
                }
            }

            $actual->modify('+1 month');
        }

        if ($activos_omitidos_vigencia > 0) {
            $warnings[] = "Activos omitidos por vigencia: {$activos_omitidos_vigencia}.";
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
                  '<div><strong>Meses procesados:</strong> {$meses_procesados}</div>' +
                  '<div><strong>Activos evaluados:</strong> {$total_activos}</div>' +
                  '<div><strong>Registros generados:</strong> {$registros_generados}</div>' +
                  '<div><strong>Registros reprocesados:</strong> {$registros_reprocesados}</div>' +
                  '<div><strong>Meses sin datos saemet:</strong> {$meses_sin_saemet}</div>' +
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
