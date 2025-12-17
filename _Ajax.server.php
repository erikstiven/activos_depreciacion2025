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
    $oReturn->assign('anio', 'value', $data);
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
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }
    // DATOS DEL ACTIVO
    $sql = "select act_cod_act, act_nom_act, act_clave_act
			from saeact
			where act_cod_empr = '$empresa'	
			and sgac_cod_sgac  = '$subgrupo'
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
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }
    // DATOS DEL ACTIVO
    $sql = "select act_cod_act, act_nom_act, act_clave_act
			from saeact
			where act_cod_empr = '$empresa'		
			and sgac_cod_sgac  = '$subgrupo'
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

    //variables formulario
    $anio = $aForm['anio'];
    $empresa = $_SESSION['U_EMPRESA'];
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
        $oReturn->script('eliminar_lista_mes();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_mes(' . $i++ . ',\'' . $oIfx->f('prdo_num_prdo') . '\', \'' . $oIfx->f('prdo_nom_prdo') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $oReturn->assign('mes', 'value', $data);

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


    // DATOS DEL ACTIVO
    $sql = "select sgac_cod_sgac, sgac_des_sgac 
			 from saesgac where sgac_cod_empr = $empresa                                                                  
			 and gact_cod_gact = '$codigoGrupo'
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
    $grupo           = $aForm['cod_grupo'];
    $subgrupo      = $aForm['cod_subgrupo'];
    $activo_desde = $aForm['cod_activo_desde'];
    $activo_hasta = $aForm['cod_activo_hasta'];
    $anio           = $aForm['anio'];
    $mes           = $aForm['mes'];
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
    if (empty($grupo)) {
    } else {
        $filtro = " and saeact.gact_cod_gact = '" . $grupo . "'";
    }
    if (empty($subgrupo)) {
    } else {
        $filtro .= " and saeact.sgac_cod_sgac = '" . $subgrupo . "'";
    }
    if (empty($activo_desde) || empty($activo_hasta)) {
    } else {
        $filtro .= " and act_cod_act between " . $activo_desde . " and " . $activo_hasta;
    }
    //echo $filtro; exit;
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
						 --(saeact.act_ext_act <> 0 OR saeact.act_ext_act IS NULL  )    
						 saeact.act_ext_act = 1
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
