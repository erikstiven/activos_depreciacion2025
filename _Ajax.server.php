<?php

require("_Ajax.comun.php"); // No modificar esta linea

if (isset($_GET['action']) && $_GET['action'] === 'get_activos_rango') {
    header('Content-Type: application/json');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    global $DSN_Ifx;
    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $empresa = isset($_GET['empresa']) ? (int) $_GET['empresa'] : 0;
    $sucursal = isset($_GET['sucursal']) ? (int) $_GET['sucursal'] : 0;
    $soloVigentes = isset($_GET['solo_vigentes']) && (int) $_GET['solo_vigentes'] === 1;
    $grupos = isset($_GET['grupos']) ? $_GET['grupos'] : [];
    $subgrupos = isset($_GET['subgrupos']) ? $_GET['subgrupos'] : [];
    $termino = isset($_GET['q']) ? trim($_GET['q']) : '';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) {
        $page = 1;
    }

    $pageSize = 30;
    $offset = ($page - 1) * $pageSize;

    if (!$empresa || !$sucursal) {
        echo json_encode([
            'ok' => false,
            'message' => 'Seleccione empresa y sucursal para cargar los activos.',
            'results' => [],
        ]);
        exit;
    }

    $gruposFiltro = is_array($grupos) ? $grupos : [$grupos];
    $gruposFiltro = array_filter($gruposFiltro, function ($val) {
        return $val !== '' && $val !== '0';
    });

    $subgruposFiltro = is_array($subgrupos) ? $subgrupos : [$subgrupos];
    $subgruposFiltro = array_filter($subgruposFiltro, function ($val) {
        return $val !== '' && $val !== '0';
    });

    $filtro = " where a.act_cod_empr = $empresa";
    $filtro .= " and a.act_cod_sucu = $sucursal";
    if ($soloVigentes) {
        $filtro .= " and a.act_ext_act = 1";
    }
    if (!empty($gruposFiltro)) {
        $filtro .= " and sg.gact_cod_gact in ('" . implode("','", array_map('addslashes', $gruposFiltro)) . "')";
    }
    if (!empty($subgruposFiltro)) {
        $filtro .= " and sg.sgac_cod_sgac in ('" . implode("','", array_map('addslashes', $subgruposFiltro)) . "')";
    }
    if ($termino !== '') {
        $terminoLimpio = addslashes(strtoupper($termino));
        $filtro .= " and (".
            " upper(cast(a.act_cod_act as varchar(50))) like '%$terminoLimpio%'".
            " or upper(a.act_clave_act) like '%$terminoLimpio%'".
            " or upper(a.act_nom_act) like '%$terminoLimpio%')";
    }

    $sql = "select first $pageSize skip $offset a.act_cod_act, a.act_nom_act, a.act_clave_act".
           " from saeact a".
           " join saesgac sg on sg.sgac_cod_sgac = a.sgac_cod_sgac and sg.sgac_cod_empr = a.act_cod_empr".
           $filtro.
           " order by a.act_clave_act";

    $results = [];
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $results[] = [
                    'id' => $oIfx->f('act_cod_act'),
                    'text' => $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act'),
                ];
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $tieneMas = count($results) === $pageSize;
    echo json_encode([
        'ok' => true,
        'results' => $results,
        'pagination' => ['more' => $tieneMas],
    ]);
    exit;
}
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
        $oReturn->script('eliminar_lista_anio();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_anio(' . $i++ . ',\'' . $oIfx->f('anio_i') . '\',\'' . $oIfx->f('anio_i') . '\')'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign('anio_desde', 'value', $data);
    $oReturn->assign('anio_hasta', 'value', $data);
    return $oReturn;
}

function f_filtro_activos_desde($aForm)
{
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $grupo = $aForm['cod_grupo'];
    $subgrupo = $aForm['cod_subgrupo'];
    $soloVigentes = isset($aForm['solo_vigentes']) && $aForm['solo_vigentes'] == '1';

    if (empty($empresa)) {
        $empresa = $idempresa;
    }

    $filtro = "";
    if (!empty($subgrupo)) {
        $listaSub = is_array($subgrupo) ? $subgrupo : [$subgrupo];
        $listaSub = array_filter($listaSub, function ($val) {
            return !empty($val) && $val !== '0';
        });
        if (!empty($listaSub)) {
            $filtro .= " and a.sgac_cod_sgac in ('" . implode("','", $listaSub) . "')";
        }
    }
    if (!empty($grupo)) {
        $listaGru = is_array($grupo) ? $grupo : [$grupo];
        $listaGru = array_filter($listaGru, function ($val) {
            return !empty($val) && $val !== '0';
        });
        if (!empty($listaGru)) {
            $filtro .= " and sg.gact_cod_gact in ('" . implode("','", $listaGru) . "')";
        }
    }
    if (!empty($sucursal) && $sucursal != '0') {
        $filtro .= " and a.act_cod_sucu = '" . $sucursal . "'";
    }
    if ($soloVigentes) {
        $filtro .= " and a.act_ext_act = 1";
    }

    $sql = "select a.act_cod_act, a.act_nom_act, a.act_clave_act
              from saeact a
              join saesgac sg on sg.sgac_cod_sgac = a.sgac_cod_sgac and sg.sgac_cod_empr = a.act_cod_empr
             where a.act_cod_empr = '$empresa'
             $filtro
             order by a.act_clave_act";

    $items = [];
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $items[] = [
                    'id'   => $oIfx->f('act_cod_act'),
                    'text' => $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act'),
                ];
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oReturn->script('renderActivosDesde(' . json_encode(['ok' => true, 'items' => $items]) . ');');
    return $oReturn;
}

function f_filtro_activos_hasta($aForm)
{
    global $DSN, $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $grupo = $aForm['cod_grupo'];
    $subgrupo = $aForm['cod_subgrupo'];
    $soloVigentes = isset($aForm['solo_vigentes']) && $aForm['solo_vigentes'] == '1';

    if (empty($empresa)) {
        $empresa = $idempresa;
    }

    $filtro = "";
    if (!empty($subgrupo)) {
        $listaSub = is_array($subgrupo) ? $subgrupo : [$subgrupo];
        $listaSub = array_filter($listaSub, function ($val) {
            return !empty($val) && $val !== '0';
        });
        if (!empty($listaSub)) {
            $filtro .= " and a.sgac_cod_sgac in ('" . implode("','", $listaSub) . "')";
        }
    }
    if (!empty($grupo)) {
        $listaGru = is_array($grupo) ? $grupo : [$grupo];
        $listaGru = array_filter($listaGru, function ($val) {
            return !empty($val) && $val !== '0';
        });
        if (!empty($listaGru)) {
            $filtro .= " and sg.gact_cod_gact in ('" . implode("','", $listaGru) . "')";
        }
    }
    if (!empty($sucursal) && $sucursal != '0') {
        $filtro .= " and a.act_cod_sucu = '" . $sucursal . "'";
    }
    if ($soloVigentes) {
        $filtro .= " and a.act_ext_act = 1";
    }

    $sql = "select a.act_cod_act, a.act_nom_act, a.act_clave_act
              from saeact a
              join saesgac sg on sg.sgac_cod_sgac = a.sgac_cod_sgac and sg.sgac_cod_empr = a.act_cod_empr
             where a.act_cod_empr = '$empresa'
             $filtro
             order by a.act_clave_act";

    $items = [];
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $items[] = [
                    'id'   => $oIfx->f('act_cod_act'),
                    'text' => $oIfx->f('act_clave_act') . ' - ' . $oIfx->f('act_nom_act'),
                ];
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oReturn->script('renderActivosHasta(' . json_encode(['ok' => true, 'items' => $items]) . ');');
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

    $oReturn = new xajaxResponse();
    $meses = [
        ['id' => '01', 'text' => 'Enero'],
        ['id' => '02', 'text' => 'Febrero'],
        ['id' => '03', 'text' => 'Marzo'],
        ['id' => '04', 'text' => 'Abril'],
        ['id' => '05', 'text' => 'Mayo'],
        ['id' => '06', 'text' => 'Junio'],
        ['id' => '07', 'text' => 'Julio'],
        ['id' => '08', 'text' => 'Agosto'],
        ['id' => '09', 'text' => 'Septiembre'],
        ['id' => '10', 'text' => 'Octubre'],
        ['id' => '11', 'text' => 'Noviembre'],
        ['id' => '12', 'text' => 'Diciembre'],
    ];
    $oReturn->script('renderCatalogoMeses();');
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
    //variables formulario
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $anioDesde = isset($aForm['anio_desde']) ? $aForm['anio_desde'] : null;
    $mesDesde = isset($aForm['mes_desde']) ? $aForm['mes_desde'] : null;
    $anioHasta = isset($aForm['anio_hasta']) ? $aForm['anio_hasta'] : null;
    $mesHasta = isset($aForm['mes_hasta']) ? $aForm['mes_hasta'] : null;

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

    $filtroGrupo = '';
    if (!empty($codigoGrupo)) {
        $listaGru = is_array($codigoGrupo) ? $codigoGrupo : [$codigoGrupo];
        $listaGru = array_filter($listaGru, function ($val) {
            return !empty($val) && $val !== '0';
        });
        if (!empty($listaGru)) {
            $filtroGrupo = " and gact_cod_gact in ('" . implode("','", $listaGru) . "')";
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

    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $grupos = $aForm['cod_grupo'];
    $subgrupos = $aForm['cod_subgrupo'];
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anioDesde = isset($aForm['anio_desde']) ? (int) $aForm['anio_desde'] : null;
    $mesDesde = isset($aForm['mes_desde']) ? (int) $aForm['mes_desde'] : null;
    $anioHasta = isset($aForm['anio_hasta']) ? (int) $aForm['anio_hasta'] : null;
    $mesHasta = isset($aForm['mes_hasta']) ? (int) $aForm['mes_hasta'] : null;
    $soloVigentes = isset($aForm['solo_vigentes']) && $aForm['solo_vigentes'] == '1';

    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $gruposFiltro = [];
    if (!empty($grupos)) {
        $gruposFiltro = is_array($grupos) ? $grupos : [$grupos];
        $gruposFiltro = array_filter($gruposFiltro, function ($val) {
            return !empty($val) && $val !== '0';
        });
    }

    $subgruposFiltro = [];
    if (!empty($subgrupos)) {
        $subgruposFiltro = is_array($subgrupos) ? $subgrupos : [$subgrupos];
        $subgruposFiltro = array_filter($subgruposFiltro, function ($val) {
            return !empty($val) && $val !== '0';
        });
    }

    $fechaActual = date("Y-m-d");

    $sql_tipo = "select tdep_cod_tdep, tdep_tip_val from saetdep";
    $arrayTipoDepre = [];
    if ($oIfx->Query($sql_tipo)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $arrayTipoDepre[$oIfx->f('tdep_cod_tdep')] = $oIfx->f('tdep_tip_val');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $procesados = 0;
    $evaluados = 0;
    $warnings = [];
    $mesesProcesados = 0;

    try {
        $inicio = new DateTime(sprintf('%04d-%02d-01', $anioDesde, $mesDesde));
        $fin = new DateTime(sprintf('%04d-%02d-01', $anioHasta, $mesHasta));

        while ($inicio <= $fin) {
            $anio = (int) $inicio->format('Y');
            $mes = (int) $inicio->format('m');
            $dia = date("t", strtotime($inicio->format('Y-m-01')));
            $fecha_hasta = $inicio->format('Y-m') . '-' . $dia;

            $previo = clone $inicio;
            $previo->modify('-1 month');
            $diaAnterior = date("t", strtotime($previo->format('Y-m-01')));
            $fechaAnterior = $previo->format('Y-m') . '-' . $diaAnterior;

            $filtro = "";
            if (!empty($gruposFiltro)) {
                $filtro .= " and saesgac.gact_cod_gact in ('" . implode("','", $gruposFiltro) . "')";
            }
            if (!empty($subgruposFiltro)) {
                $filtro .= " and saesgac.sgac_cod_sgac in ('" . implode("','", $subgruposFiltro) . "')";
            }
            if (!empty($activo_desde) && $activo_desde != '0' && !empty($activo_hasta) && $activo_hasta != '0') {
                $filtro .= " and saeact.act_cod_act between '" . addslashes($activo_desde) . "' and '" . addslashes($activo_hasta) . "'";
            } elseif (!empty($activo_desde) && $activo_desde != '0') {
                $filtro .= " and saeact.act_cod_act >= '" . addslashes($activo_desde) . "'";
            } elseif (!empty($activo_hasta) && $activo_hasta != '0') {
                $filtro .= " and saeact.act_cod_act <= '" . addslashes($activo_hasta) . "'";
            }
            if (!empty($sucursal) && $sucursal != '0') {
                $filtro .= " and saeact.act_cod_sucu = '" . $sucursal . "'";
            }
            if ($soloVigentes) {
                $filtro .= " and saeact.act_ext_act = 1";
            }

            $sqlValores = "select metd_cod_acti, metd_val_metd from saemet where metd_has_fech = '$fecha_hasta' and metd_cod_empr = $empresa";
            $arrayValorDepre = [];
            if ($oIfx->Query($sqlValores)) {
                if ($oIfx->NumFilas() > 0) {
                    do {
                        $arrayValorDepre[$oIfx->f('metd_cod_acti')] = $oIfx->f('metd_val_metd');
                    } while ($oIfx->SiguienteRegistro());
                }
            }
            $oIfx->Free();

            $sqlActivos = "  SELECT saeact.act_cod_act,
                                                 saeact.tdep_cod_tdep,
                                                 saesgac.gact_cod_gact,
                                                 saeact.sgac_cod_sgac,
                                                 saeact.act_cod_sucu,
                                                 saeact.act_ext_act
                                        FROM saeact,
                                                 saesgac
                                   WHERE ( saesgac.sgac_cod_sgac = saeact.sgac_cod_sgac ) and
                                                 ( saesgac.sgac_cod_empr = saeact.act_cod_empr ) and
                                                 ( saeact.act_clave_padr is null or saeact.act_clave_padr = '') and
                                                 ( saeact.act_cod_empr = $empresa )
                                                 $filtro";

            $oIfx->QueryT('BEGIN');
            if ($oIfxA->Query($sqlActivos)) {
                if ($oIfxA->NumFilas() > 0) {
                    do {
                        $evaluados++;
                        $codigo_activo = $oIfxA->f('act_cod_act');
                        $tipo_depreciacion = $oIfxA->f('tdep_cod_tdep');
                        $sucursalActivo = $oIfxA->f('act_cod_sucu');

                        $valor_mesual = isset($arrayValorDepre[$codigo_activo]) ? $arrayValorDepre[$codigo_activo] : null;
                        if ($valor_mesual === null || $valor_mesual === '') {
                            $warnings[] = "No existe valor mensual en SAEMET para la fecha $fecha_hasta. Activo: $codigo_activo. Debe generar/cargar el valor antes de depreciar.";
                            continue;
                        }

                        $sql_existe = "select count(cdep_gas_depn) as existe from saecdep where cdep_cod_acti = $codigo_activo and cdep_fec_depr = '$fecha_hasta'";
                        $existe = consulta_string($sql_existe, 'existe', $oIfx, 0);
                        if ($existe == 1) {
                            $sql_borra = "delete from saecdep where cdep_cod_acti = $codigo_activo and cdep_fec_depr = '$fecha_hasta'";
                            $oIfx->QueryT($sql_borra);
                        }

                        $sql_dep_acumulada = "SELECT (coalesce(cdep_dep_acum, 0) +  coalesce(cdep_gas_depn, 0)) as depr_acumulada from saecdep where cdep_cod_acti = $codigo_activo and cdep_fec_depr = '$fechaAnterior'";
                        $valor_acumulado_anterior = consulta_string($sql_dep_acumulada, 'depr_acumulada', $oIfx, 0);

                        $valor_anterior = $valor_acumulado_anterior;
                        $valor_acumulado = $valor_acumulado_anterior + $valor_mesual;

                        $intervalo = isset($arrayTipoDepre[$tipo_depreciacion]) ? $arrayTipoDepre[$tipo_depreciacion] : 'M';
                        if (empty($intervalo)) {
                            $intervalo = 'M';
                        }

                        $sql_cdep = "INSERT into saecdep (cdep_cod_acti, cdep_cod_tdep,     cdep_mes_depr, cdep_ani_depr,
                                                     cdep_fec_depr, act_cod_empr,       act_cod_sucu,  cdep_dep_acum,
                                                     cdep_gas_depn, cdep_est_cdep,      cdep_fec_cdep, cdep_val_rep1 )
                                                                values ($codigo_activo, '$tipo_depreciacion', $mes,           $anio,
                                                    '$fecha_hasta',  $empresa,            '$sucursalActivo',      $valor_acumulado ,
                                                    $valor_mesual,      'PE',           '$fechaActual',    $valor_anterior)";
                        $oIfx->QueryT($sql_cdep);
                        $procesados++;
                    } while ($oIfxA->SiguienteRegistro());
                } else {
                    $warnings[] = "No se encontraron activos para procesar en $fecha_hasta con los filtros seleccionados.";
                }
            }
            $oIfx->QueryT('COMMIT WORK;');
            $mesesProcesados++;
            $inicio->modify('+1 month');
        }

        $payload = [
            'ok' => true,
            'mensaje' => 'Proceso terminado',
            'warnings' => $warnings,
            'procesados' => $procesados,
            'evaluados' => $evaluados,
            'meses' => $mesesProcesados,
            'rango' => [
                'desde' => sprintf('%04d-%02d', $anioDesde, $mesDesde),
                'hasta' => sprintf('%04d-%02d', $anioHasta, $mesHasta),
            ],
        ];
        $oReturn->script('mostrarResultado(' . json_encode($payload) . ');');
    } catch (Exception $e) {
        $oIfx->QueryT('ROLLBACK');
        $oReturn->script('mostrarResultado(' . json_encode(['error' => $e->getMessage()]) . ');');
    }
    return $oReturn;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
