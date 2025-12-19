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
    $solo_vigentes = isset($aForm['solo_vigentes']) ? (int)$aForm['solo_vigentes'] : 1;
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }
    if (empty($grupo)) {
        $grupo = obtener_grupos_empresa($oIfx, $empresa);
    }
    if (empty($grupo)) {
        $oReturn->script('eliminar_lista_activo_desde();');
        $oReturn->script('eliminar_lista_activo_hasta();');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'No existen activos que cumplan con los filtros seleccionados.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }
    if (empty($subgrupo)) {
        $subgrupo = obtener_subgrupos_empresa($oIfx, $empresa, $grupo);
    }
    if (empty($subgrupo)) {
        $oReturn->script('eliminar_lista_activo_desde();');
        $oReturn->script('eliminar_lista_activo_hasta();');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'No existen activos que cumplan con los filtros seleccionados.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
        })");
        return $oReturn;
    }
    // DATOS DEL ACTIVO
    $sql = "select act_cod_act, act_nom_act, act_clave_act
			from saeact
			where act_cod_empr = '$empresa'
            and act_cod_sucu = '$sucursal'
			and gact_cod_gact in (" . lista_sql($grupo) . ")
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
                title: 'No existen activos que cumplan con los filtros seleccionados.',
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
    $oReturn->script('f_filtro_activos_desde()');

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
    $sql = "select sgac_cod_sgac, sgac_des_sgac 
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
    $grupo           = normalizar_lista($aForm['cod_grupo']);
    $subgrupo      = normalizar_lista($aForm['cod_subgrupo']);
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anio_desde           = $aForm['anio_desde'];
    $mes_desde           = $aForm['mes_desde'];
    $anio_hasta           = $aForm['anio_hasta'];
    $mes_hasta           = $aForm['mes_hasta'];
    $mes           = $mes_hasta;
    $anio           = $anio_hasta;
    $solo_vigentes = isset($aForm['solo_vigentes']) ? (int)$aForm['solo_vigentes'] : 1;
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
    if (empty($grupo)) {
        $grupo = obtener_grupos_empresa($oIfx, $empresa);
    }
    if (empty($subgrupo)) {
        $subgrupo = obtener_subgrupos_empresa($oIfx, $empresa, $grupo);
    }
    if (empty($subgrupo)) {
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Con los filtros actuales no se encontraron activos. Revise grupo, subgrupo o vigencia.',
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
    $dia           = date("d", (mktime(0, 0, 0, $mes + 1, 1, $anio) - 1));
    //$fecha_hasta = $dia.'/'.$mes.'/'.$anio;
    $fecha_hasta = $anio . '-' . $mes . '-' . $dia;
    $fechaServer = date("Y-m-d");
    //echo $fecha_hasta; 

    // ARMA FECHA ANTERIOR
    if ($mes > 1) {
        $mesAnterior = $mes - 1;
        $anioAnterior =  $anio;
    } else {
        $mesAnterior = 12;
        $anioAnterior =  $anio - 1;
    }
    $diaAnterior = date("d", (mktime(0, 0, 0, $mesAnterior + 1, 1, $anioAnterior) - 1));
    $fechaAnterior = $anioAnterior . '-' . $mesAnterior . '-' . $diaAnterior;
    // echo $fechaAnterior; exit;
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
    //echo $filtro; exit;
    $sql_activos = "select count(1) as total
        from saeact
        where act_cod_empr = $empresa
        and act_cod_sucu = $sucursal
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

    try {
        $oIfx->QueryT('BEGIN');
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
        // CALCULA GASTO DEPRECIACION
        $sql = "select metd_cod_acti, metd_val_metd 
					from saemet 
					where metd_has_fech = '$fecha_hasta'
					and metd_cod_empr   =  $empresa					
					";
        //echo $sql; exit;		
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayValorDepre);
                do {
                    $arrayValorDepre[$oIfx->f('metd_cod_acti')] = $oIfx->f('metd_val_metd');
                } while ($oIfx->SiguienteRegistro());
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
						 ( saeact.act_cod_sucu = $sucursal ) AND
						 ($solo_vigentes = 0 OR saeact.act_ext_act = 1)
						 $filtro";
        //echo $sql; exit;	
        if ($oIfxA->Query($sql)) {
            if ($oIfxA->NumFilas() > 0) {
                do {
                    // LEER DATOS AVTIVO
                    $codigo_activo        =    $oIfxA->f('act_cod_act');
                    $vida_util          =    $oIfxA->f('act_vutil_act');
                    $valor_compra        =    $oIfxA->f('act_val_comp');
                    $fecha_compra        =    $oIfxA->f('act_fcmp_act');
                    $tipo_depreciacion     =    $oIfxA->f('tdep_cod_tdep');
                    $fecha_depreciacion =   $oIfxA->f('act_fdep_act');
                    $cod_grupo          =    $oIfxA->f('gact_cod_gact');
                    $cod_subgrupo          =    $oIfxA->f('sgac_cod_sgac');
                    $valor_recidual        =     $oIfxA->f('act_vres_act');
                    $sucursal           =     $oIfxA->f('act_cod_sucu');


                    // ELIMINAR DEPRECIACION 
                    $sql_existe = "select count(cdep_gas_depn) as existe
									from saecdep
									where cdep_cod_acti = $codigo_activo 
									and cdep_fec_depr = '$fecha_hasta'";
                    $existe = consulta_string($sql_existe, 'existe', $oIfx, 0);

                    if ($existe == 1) {
                        $sql_borra = "delete from saecdep 
										where cdep_cod_acti = $codigo_activo 
										and cdep_fec_depr = '$fecha_hasta'";

                        $oIfx->QueryT($sql_borra);
                    }

                    // GASTO DEPRECIACION
                    $valor_mesual = $arrayValorDepre[$codigo_activo];
                    //if (empty($valor_mesual) or $valor_mesual == 0) continue;
                    if (empty($valor_mesual))  $valor_mesual = 0;

                    // TIPO DE DEPRESIACION MENSUAL(M) - DIARIA (D)				
                    $intervalo = $arrayTipoDepre[$tipo_depreciacion];

                    if (empty($intervalo)) {
                        $intervalo = 'M';
                    }
                    //echo $sql_valor_mesual; exit;
                    // CALCULA DEPRECIACION ACUMULADA


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



                    //echo $sql_dep_acumulada;exit;
                    $registros = consulta_string($sql_dep_acumulada, 'registros', $oIfx, 0);
                    // INSERTAR DEPRECIACION
                    $sql_cdep = "INSERT into saecdep (cdep_cod_acti, cdep_cod_tdep,     cdep_mes_depr, cdep_ani_depr, 
                                                     cdep_fec_depr, act_cod_empr,       act_cod_sucu,  cdep_dep_acum, 
                                                     cdep_gas_depn, cdep_est_cdep,      cdep_fec_cdep, cdep_val_rep1 )
					                        values ($codigo_activo, '$tipo_depreciacion', $mes,           $anio, 
                                                    '$fecha_hasta',  $empresa,            $sucursal,      $valor_acumulado , 
                                                    $valor_mesual,      'PE',           '$fechaServer',    $valor_anterior)";
                    $oIfx->QueryT($sql_cdep);
                } while ($oIfxA->SiguienteRegistro());
                $mensaje = 'Proceso Terminado con Exito';
            }
        }
        $oReturn->alert('Proceso Terminado con Exito');
        //$oReturn->script("recarga();"); 
        $oIfx->QueryT('COMMIT WORK;');
    } catch (Exception $e) {
        $oCon->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }
    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
