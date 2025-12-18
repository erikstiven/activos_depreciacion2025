<?php

require("_Ajax.comun.php"); // No modificar esta linea
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */

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
    $idsucursal = $_SESSION['U_SUCURSAL'];
    //variables formulario
    $empresa = $aForm['empresa'];
    $target = !empty($data) ? $data : 'anio_desde';
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
        $oReturn->script('eliminar_lista_anio(\'' . $target . '\');');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_anio(' . $i++ . ',\'' . $oIfx->f('anio_i') . '\',\'' . $oIfx->f('anio_i') . '\', \'" . $target . "\')'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign($target, 'value', $aForm[$target]);
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
    $subgrupo = $aForm['cod_subgrupo'];
    $grupo    = $aForm['cod_grupo'];
    if (is_array($grupo)) {
        $grupo = array_values(array_filter($grupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }
    if (is_array($subgrupo)) {
        $subgrupo = array_values(array_filter($subgrupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }

    if (is_array($grupo)) {
        $grupo = array_values(array_filter($grupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }
    if (is_array($subgrupo)) {
        $subgrupo = array_values(array_filter($subgrupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $filtro = "where act_cod_empr = '$empresa'";
    if (!empty($sucursal) && $sucursal != '0') {
        $filtro .= " and act_cod_sucu = '$sucursal'";
    }
    if (!empty($subgrupo)) {
        if (is_array($subgrupo)) {
            $valores = implode("','", $subgrupo);
            $filtro .= " and sgac_cod_sgac  in ('$valores')";
        } elseif ($subgrupo != '0') {
            $filtro .= " and sgac_cod_sgac  = '$subgrupo'";
        }
    } elseif (!empty($grupo)) {
        if (is_array($grupo)) {
            $valoresGrupo = implode("','", $grupo);
            $filtro .= " and sgac_cod_sgac in (select sgac_cod_sgac from saesgac where gact_cod_gact in ('$valoresGrupo') and sgac_cod_empr = '$empresa')";
        } elseif ($grupo != '0') {
            $filtro .= " and sgac_cod_sgac in (select sgac_cod_sgac from saesgac where gact_cod_gact = '$grupo' and sgac_cod_empr = '$empresa')";
        }
    }

    // DATOS DEL ACTIVO
    $sql = "select act_cod_act, act_nom_act, act_clave_act
                        from saeact
                        $filtro
                        order by act_cod_act";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_activo_desde();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_activo_desde(' . $i++ . ',\'' . $oIfx->f('act_cod_act') . '\', \'' . $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    return $oReturn;
}

function f_filtro_activos_hasta($aForm)
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
    $subgrupo = $aForm['cod_subgrupo'];
    $grupo    = $aForm['cod_grupo'];
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $filtro = "where act_cod_empr = '$empresa'";
    if (!empty($sucursal) && $sucursal != '0') {
        $filtro .= " and act_cod_sucu = '$sucursal'";
    }
    if (!empty($subgrupo)) {
        if (is_array($subgrupo)) {
            $valores = implode("','", $subgrupo);
            $filtro .= " and sgac_cod_sgac  in ('$valores')";
        } elseif ($subgrupo != '0') {
            $filtro .= " and sgac_cod_sgac  = '$subgrupo'";
        }
    } elseif (!empty($grupo)) {
        if (is_array($grupo)) {
            $valoresGrupo = implode("','", $grupo);
            $filtro .= " and sgac_cod_sgac in (select sgac_cod_sgac from saesgac where gact_cod_gact in ('$valoresGrupo') and sgac_cod_empr = '$empresa')";
        } elseif ($grupo != '0') {
            $filtro .= " and sgac_cod_sgac in (select sgac_cod_sgac from saesgac where gact_cod_gact = '$grupo' and sgac_cod_empr = '$empresa')";
        }
    }

    // DATOS DEL ACTIVO
    $sql = "select act_cod_act, act_nom_act, act_clave_act
                        from saeact
                        $filtro
                        order by act_cod_act";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_activo_hasta();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_activo_hasta(' . $i++ . ',\'' . $oIfx->f('act_cod_act') . '\', \'' . $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    //$oReturn->assign('cod_activo_hasta', 'value', $data);
    return $oReturn;
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

    $empresa = $_SESSION['U_EMPRESA'];
    $target = !empty($data) ? $data : 'mes_desde';
    $anioField = ($target === 'mes_hasta') ? 'anio_hasta' : 'anio_desde';
    $anio = $aForm[$anioField];

    if (empty($anio)) {
        return $oReturn;
    }
    // DATOS DEL ACTIVO
    $sql = "select prdo_num_prdo, prdo_nom_prdo
                        from saeprdo
                        where prdo_cod_empr = '$empresa'
                        and prdo_cod_ejer  = (select ejer_cod_ejer
									from saeejer 
									where ejer_cod_empr = '$empresa' 
									and date_part('year',ejer_fec_inil) = $anio)
			order by prdo_num_prdo";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_mes(\'' . $target . '\');');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_mes(' . $i++ . ',\'' . $oIfx->f('prdo_num_prdo') . '\', \'' . $oIfx->f('prdo_nom_prdo') . '\', \'" . $target . "\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign($target, 'value', $aForm[$target]);

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

    $oReturn->assign('cod_grupo', 'value', $data);
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
    $codigoGrupo = $aForm['cod_grupo'];
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (is_array($codigoGrupo)) {
        $codigoGrupo = array_values(array_filter($codigoGrupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }

    $filtroGrupo = '';
    if (!empty($codigoGrupo)) {
        if (is_array($codigoGrupo)) {
            $valores = implode("','", $codigoGrupo);
            $filtroGrupo = " and gact_cod_gact in ('$valores')";
        } else {
            $filtroGrupo = " and gact_cod_gact = '$codigoGrupo'";
        }
    }

    // DATOS DEL ACTIVO
    $sql = "select sgac_cod_sgac, sgac_des_sgac
                         from saesgac where sgac_cod_empr = $empresa
                         $filtroGrupo
                         order by sgac_des_sgac";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_subgrupo();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_subgrupo(' . $i++ . ',\'' . $oIfx->f('sgac_cod_sgac') . '\', \'' . $oIfx->f('sgac_des_sgac') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->script('f_filtro_activos_desde()');
    $oReturn->script('f_filtro_activos_hasta()');
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
    $grupo           = $aForm['cod_grupo'];
    $subgrupo      = $aForm['cod_subgrupo'];
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anio_desde    = $aForm['anio_desde'];
    $mes_desde     = $aForm['mes_desde'];
    $anio_hasta    = $aForm['anio_hasta'];
    $mes_hasta     = $aForm['mes_hasta'];

    if (is_array($grupo)) {
        $grupo = array_values(array_filter($grupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }
    if (is_array($subgrupo)) {
        $subgrupo = array_values(array_filter($subgrupo, function ($item) {
            return !empty($item) && $item !== '0';
        }));
    }

    if (empty($anio_desde) || empty($mes_desde) || empty($anio_hasta) || empty($mes_hasta)) {
        $oReturn->alert('Debe ingresar el rango completo de meses y años.');
        return $oReturn;
    }

    $fechaDesde = DateTime::createFromFormat('Y-n-j', $anio_desde . '-' . $mes_desde . '-01');
    $fechaHasta = DateTime::createFromFormat('Y-n-j', $anio_hasta . '-' . $mes_hasta . '-01');

    if (!$fechaDesde || !$fechaHasta) {
        $oReturn->alert('Rango de fechas inválido.');
        return $oReturn;
    }

    if ($fechaDesde > $fechaHasta) {
        $oReturn->alert('La fecha inicial debe ser menor o igual a la fecha final.');
        return $oReturn;
    }

    // ARMAR FILTROS
    $filtro = '';
    if (!empty($grupo)) {
        if (is_array($grupo)) {
            $valoresGrupo = implode("','", $grupo);
            $filtro = " and saeact.gact_cod_gact in ('" . $valoresGrupo . "')";
        } else {
            $filtro = " and saeact.gact_cod_gact = '" . $grupo . "'";
        }
    }
    if (!empty($subgrupo)) {
        if (is_array($subgrupo)) {
            $valoresSub = implode("','", $subgrupo);
            $filtro .= " and saeact.sgac_cod_sgac in ('" . $valoresSub . "')";
        } else {
            $filtro .= " and saeact.sgac_cod_sgac = '" . $subgrupo . "'";
        }
    }
    if (!empty($activo_desde) && !empty($activo_hasta)) {
        if ($activo_desde > $activo_hasta) {
            $oReturn->alert('El rango de activos es inválido. El valor Desde debe ser menor o igual que Hasta.');
            return $oReturn;
        }
        $filtro .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    }

    $fechasProceso = [];
    $periodo = new DatePeriod($fechaDesde, new DateInterval('P1M'), (clone $fechaHasta)->modify('+1 month'));
    foreach ($periodo as $fecha) {
        $fechasProceso[] = $fecha;
    }

    if (empty($fechasProceso)) {
        $oReturn->alert('No hay meses que procesar en el rango seleccionado.');
        return $oReturn;
    }

    // Validar valores en saemet para cada mes antes de iniciar
    foreach ($fechasProceso as $fecha) {
        $fecha_hasta = $fecha->format('Y-m-t');
        $sqlExisteMetd = "select count(1) as existe from saemet where metd_has_fech = '$fecha_hasta' and metd_cod_empr =  $empresa";
        $existeMetd = consulta_string($sqlExisteMetd, 'existe', $oIfx, 0);
        if ($existeMetd == 0) {
            $oReturn->alert('No existe valor mensual en saemet para la fecha ' . $fecha_hasta . '.');
            return $oReturn;
        }
    }

    // Obtener lista de activos una sola vez
    $sqlActivos = "  SELECT saeact.act_cod_act,
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
                                                 saeact.act_ext_act = 1
                                                 $filtro";

    $activos = [];
    if ($oIfxA->Query($sqlActivos)) {
        if ($oIfxA->NumFilas() > 0) {
            do {
                $activos[] = [
                    'act_cod_act'     => $oIfxA->f('act_cod_act'),
                    'act_vutil_act'   => $oIfxA->f('act_vutil_act'),
                    'act_val_comp'    => $oIfxA->f('act_val_comp'),
                    'act_fcmp_act'    => $oIfxA->f('act_fcmp_act'),
                    'tdep_cod_tdep'   => $oIfxA->f('tdep_cod_tdep'),
                    'act_fdep_act'    => $oIfxA->f('act_fdep_act'),
                    'act_fcorr_act'   => $oIfxA->f('act_fcorr_act'),
                    'gact_cod_gact'   => $oIfxA->f('gact_cod_gact'),
                    'sgac_cod_sgac'   => $oIfxA->f('sgac_cod_sgac'),
                    'act_cod_sucu'    => $oIfxA->f('act_cod_sucu'),
                    'act_vres_act'    => $oIfxA->f('act_vres_act'),
                ];
            } while ($oIfxA->SiguienteRegistro());
        }
    }

    if (count($activos) === 0) {
        $oReturn->alert('No se encontraron activos para los filtros seleccionados.');
        return $oReturn;
    }

    $oReturn->alert('Se procesarán ' . count($fechasProceso) . ' meses y ' . count($activos) . ' activos.');

    try {
        $oIfx->QueryT('BEGIN');

        // TIPO DE DEPRECIACION
        $arrayTipoDepre = [];
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
        $fechaServer = date("Y-m-d");

        foreach ($fechasProceso as $fechaProceso) {
            $anio = (int)$fechaProceso->format('Y');
            $mes  = (int)$fechaProceso->format('n');
            $fecha_hasta = $fechaProceso->format('Y-m-t');
            $fechaAnterior = (clone $fechaProceso)->modify('-1 month')->format('Y-m-t');

            // CALCULA GASTO DEPRECIACION POR MES
            $arrayValorDepre = [];
            $sql = "select metd_cod_acti, metd_val_metd
                                        from saemet
                                        where metd_has_fech = '$fecha_hasta'
                                        and metd_cod_empr   =  $empresa
                                        ";
            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas() > 0) {
                    unset($arrayValorDepre);
                    do {
                        $arrayValorDepre[$oIfx->f('metd_cod_acti')] = $oIfx->f('metd_val_metd');
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            foreach ($activos as $activo) {
                $codigo_activo        = $activo['act_cod_act'];
                $tipo_depreciacion    = $activo['tdep_cod_tdep'];
                $sucursalActivo       = $activo['act_cod_sucu'];

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
                }

                // GASTO DEPRECIACION
                if (!isset($arrayValorDepre[$codigo_activo])) {
                    throw new Exception('No existe valor mensual en saemet para el activo ' . $codigo_activo . ' en ' . $fecha_hasta . '.');
                }
                $valor_mesual = $arrayValorDepre[$codigo_activo];
                if (empty($valor_mesual))  $valor_mesual = 0;

                // TIPO DE DEPRESIACION MENSUAL(M) - DIARIA (D)
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
                                                    '$fecha_hasta',  $empresa,            $sucursalActivo,      $valor_acumulado ,
                                                    $valor_mesual,      'PE',           '$fechaServer',    $valor_anterior)";
                $oIfx->QueryT($sql_cdep);
            }
        }
        $oReturn->alert('Proceso Terminado con Exito');
        $oIfx->QueryT('COMMIT WORK;');
        error_log('Usuario ' . $usuario_web . ' generó depreciación desde ' . $fechaDesde->format('Y-m') . ' hasta ' . $fechaHasta->format('Y-m') . ' para ' . count($activos) . ' activos.');
    } catch (Exception $e) {
        $oIfx->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }
    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
