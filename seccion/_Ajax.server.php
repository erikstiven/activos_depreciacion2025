<?php

require("_Ajax.comun.php"); // No modificar esta linea
include_once './mayorizacion.inc.php';
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */


/* * **************************************************************** */
/* DF01 :: G E N E R A    F O R M U L A R I O    P E D I D O       */
/* * **************************************************************** */

function genera_formulario_pedido($tmp = 0, $sAccion = 'nuevo', $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $idempresa          = $_SESSION['U_EMPRESA'];
    $idsucursal         = $_SESSION['U_SUCURSAL'];

    switch ($sAccion) {
        case 'nuevo':
            // EMPRESA
 
    $sql = "select finc_cod_finc, finc_nom_finc from agricola.saefinc ";
            $lista_empr = lista_boostrap_func($oIfx, $sql, $idempresa, 'finc_cod_finc',  'finc_nom_finc');
            $finc = consulta_string($sql, 'finc_cod_finc', $oIfx, '');

    $sql = "select lote_cod_lote, lote_des_lote from agricola.saeplote  ";
            $lista_lote = lista_boostrap_func($oIfx, $sql, $idempresa, 'lote_cod_lote',  'lote_des_lote');
            //echo $sql;exit;
            break;
    }

    $sql = "select  empr_num_dire, empr_ema_comp, empr_tel_resp,empr_repres, empr_ruc_empr from saeempr ";


    $sHtml_cab .= '<h3>Crear nueva SECCION</h3>';

    $sHtml_cab .= '<div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <div class="btn btn-primary btn-sm" onclick="genera_formulario();">
                                    <span class="glyphicon glyphicon-file"></span>
                                    Nuevo
                                </div>
                                
                                <div id ="imagen1" class="btn btn-primary btn-sm" onclick="guardar();">
                                    <span class="glyphicon glyphicon-floppy-disk"></span>
                                    Guardar
                                </div>
                     
                            </div> 
                            
                        </div><br><br>';


    $sHtml_cab .= '<div class="col-md-12" style="margin-top: 5px !important">

                        <div class="form-row">
                            <div class="col-md-3">
                                <label for="empresa">* Finca:</label>
                                <select id="finca" name="finca" class="form-control input-sm" onchange="cargarListaSector();">
                                    <option value="0">Seleccione una opcion..</option>
                                    ' . $lista_empr . '
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="lote">* Sector:</label>
                                <select id="lote" name="lote" class="form-control input-sm" required>
                                    <option value="">Seleccione una opcion..</option>
                                  
                                </select>
                            </div>
                            <div class="col-md-3">
                            <label >Ubicacion Seccion</label>
                            <input  id="ubicacion" name="ubicacion" type="text"  class="form-control"  placeholder="Ejm: terraza1" " required>
                        </div>
                       
                           
                        </div>
                    </div>';

    $sHtml_cab .= '<div class="col-md-12"><br>

                                    </div>
                                    <div class="col-md-4">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="btn btn-primary btn-sm" onclick="consultar();" style="width: 100%">
                                            <span class="glyphicon glyphicon-search"></span>
                                            Consultar
                                        </div>
                        </div>
                    </div>';
    $sHtml_cab .= '</div>';

    $oReturn->assign("divFormularioCabecera", "innerHTML", $sHtml_cab);

    return $oReturn;
}
function cargarListaSector($aForm = '')
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	session_start();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();
	$finca = $aForm['finca'];
    
	$sql = "SELECT lote_cod_lote, lote_des_lote from agricola.saeplote  where lote_cod_finc = '$finca'";
	$i = 1;
	if ($oIfx->Query($sql)) {
		$oReturn->script('eliminarListaSector();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$oReturn->script(('anadirListaSector(' . $i++ . ',\'' . $oIfx->f('lote_cod_lote') . '\', \'' . $oIfx->f('lote_des_lote') . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	return $oReturn;
}

function parametros($id=0,$aForm = '')
{
	
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;
    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
   
    try {
        $sql = "select finc_cod_finc, finc_nom_finc from agricola.saefinc ";
            $lista_empr = lista_boostrap_func($oIfx, $sql, $idempresa, 'finc_cod_finc',  'finc_nom_finc');
            $cod = consulta_string($sql, 'finc_cod_finc', $oIfx, '');
            
            
            $sql = "select lote_cod_lote, lote_des_lote from agricola.saeplote ";
            $lista_lote = lista_boostrap_func($oIfx, $sql, $idempresa, 'lote_cod_lote',  'lote_des_lote');
      
           
            $sql ="SELECT  psec_nom_psec, psec_cod_finc,psec_cod_sect from agricola.saepsec  where psec_cod_psec ='$id'";
            //echo $sql;exit;
        $nombre = consulta_string($sql, 'psec_nom_psec', $oIfx, '');
        $psec_cod_sect = consulta_string($sql, 'psec_cod_sect', $oIfx, '');
        $psec_cod_finc = consulta_string($sql, 'psec_cod_finc', $oIfx, '');

        //echo $psec_cod_sect;exit;

		
        $sHtml = '<div class="modal-dialog" role="document" style="width: 60%;">
        <div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
								</button>
								
							</div>
							<div class="modal-body" id="classModalBody">';

		$sHtml .= '<div class="row">';
		$sHtml .= '<div class="col-md-12">';
        $sHtml .= ' <div class="tab-content">
        <h3 style="position:relative; left:25px; ">Editar Seccion</h3>
                        <div role="tabpanel" class="tab-pane active" id="tabData">
                        <div class="col-md-12" style="margin-top: 5px !important">

                        <div class="form-row">

                       <div class="form-row">
                            <div class="col-md-3">
                                <label for="empresa">* Finca:</label>
                                <select id="empresa_up" name="empresa_up" class="form-control input-sm"">
                                    <option value="0">Seleccione una opcion..</option>
                                    ' . $lista_empr . '
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="lote">* Sector:</label>
                                <select id="lote_up" name="lote_up" class="form-control input-sm" required>
                                    <option value="">Seleccione una opcion..</option>
                                    ' . $lista_lote. '
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                            <label >Ubicacion Seccion</label>
                            <input  id="ubicacion_up" name="ubicacion_up" type="text"  class="form-control"  placeholder="Ejm: terraza1" value="'.$nombre.'"" required>
                        </div>
                      
                           
                        </div>
                    </div>
                
               
                <button style="position:relative; left:25px; top: 10px;"type="button" class="btn btn-success"  data-dismiss="modal" onclick="actualizar_finca('.$id.')" >Actualizar</button>
                </div>
					</div>';

                   
					
		$sHtml .= '</div>';
		$sHtml .= '</div>';

        $sHtml .= '</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
						</div>
					</div>
				</div>';
        
                
        $oReturn->assign("miModal", "innerHTML", $sHtml);
        $oReturn->assign("empresa_up", "value", $psec_cod_finc);
        $oReturn->assign("lote_up", "value", $psec_cod_sect);
       
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}
function borrar($id=0,$aForm = '')
{
	
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    
    try {
	
		//query actividades
       
        $sHtml = '<div class="modal-dialog" role="document" style="width: 60%;">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
								</button>
								
							</div>
							<div class="modal-body" id="classModalBody">';

                            
		$sHtml .= '<div class="row">';
		$sHtml .= '<div class="col-md-12">
                   
                    <h4> ESTAS SEGURO DE ELIMINAR ESTA SECCION?</h4><br>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"  onclick="borrar_finca('.$id.')">Eliminar</button>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancelar</button>
                        
                        ';
		$sHtml .= '</div>';
		$sHtml .= '</div>';

        $sHtml .= '</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
						</div>
					</div>
				</div>';

        $oReturn->assign("miModal", "innerHTML", $sHtml);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function cargar_ord_compra_respaldo($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();



    $sql = "SELECT a.psec_cod_psec,b.lote_des_lote, a.psec_nom_psec, a.psec_num_hect,c.finc_nom_finc from agricola.saepsec a, agricola.saeplote b, agricola.saefinc c  where psec_cod_finc=finc_cod_finc and psec_cod_sect=lote_cod_lote";

    //echo $sql;exit;
    $html_finc .= '<table id="tbclientes" class="table table-bordered table-hover table-striped table-condensed" style="margin-top: 30px">
    <thead>         <tr>
                       <th colspan="6" class="bg-primary">Lista de Secciones</th>
                    </tr>
                   <tr class="info">
                   <td style="width: 4.5%;">N.-</td>
                   <td style="width: 4.5%;">FINCA</td>
                   <td style="width: 4.5%;">SECTOR</td>
                   <td style="width: 4.5%;">UBICACION SECCION</td>
                   <td style="width: 4.5%;">EDITAR</td>
                   <td style="width: 4.5%;">ELIMINAR</td>
                   </tr>';


    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $nombre_sect = $oIfx->f('lote_des_lote');
              
                $finca = $oIfx->f('finc_nom_finc');
                $nombre_sec = $oIfx->f('psec_nom_psec');
                $psec_cod_psec = $oIfx->f('psec_cod_psec');
                $html_finc .= '<tr>
          
    <td style="width: 4.5%;">'.$i.'</td> 
    </td>
    <td  style="width: 4.5%;">' . strtoupper($finca). '</td>
    <td  style="width: 4.5%;">' . strtoupper($nombre_sect) . '</td>
    <td  style="width: 4.5%;">' . strtoupper($nombre_sec) . '</td>

   
    <td  style="width: 4.5%;"><div class="btn btn-info btn-sm" onclick="parametros(\'' . $psec_cod_psec . '\')">
    <span class="glyphicon glyphicon-pencil"><span>
    </div></td></>
    <td  style="width: 4.5%;"><div class="btn btn-danger btn-sm" onclick="borrar(\'' . $psec_cod_psec . '\')">
    <span class="glyphicon glyphicon-remove"><span>
    </div></td></>
                       
                   </tr>';
                $i++;
            } while ($oIfx->SiguienteRegistro());
        }
    }

    $html_finc .= "</table>";;
    $oReturn->assign("divFormularioDetalle2", "innerHTML", $html_finc);

    return $oReturn;
}

// Guardar precios
function guardar($aForm = '')
{


    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();
    $idempresa         = $aForm['finca'];

    $ubicacion_sec      = $aForm['ubicacion'];
    $hectareas         = $aForm['hect'];
    $lote         = $aForm['lote'];
    
    try {

        $oIfx->QueryT('BEGIN WORK;');
        
        if(empty($hectareas)){
            $hectareas=0;
        }


        $sql    = "INSERT INTO agricola.saepsec (psec_cod_sect, psec_nom_psec, psec_cod_finc)
        VALUES ($lote,'$ubicacion_sec',$idempresa)";
        
        $oIfx->QueryT($sql);

        $oIfx->QueryT('COMMIT WORK');

        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'success',
            title: 'Ingresado Correctamente...!',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            timer: 2000
        })");

        $oReturn->assign("ubicacion", "value", '');

        $oReturn->script("consultar()");
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        $oReturn->assign("ctrl", "value", 1);
    }


    return $oReturn;
}


function actualiza_finca($id=0,$aForm = '')
{

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();
    $idempresa         = $aForm['finca'];

    $ubicacion_sec      = $aForm['ubicacion_up'];
    $hectareas         = $aForm['hect_up'];
    $lote         = $aForm['lote_up'];
    try {

        $oIfx->QueryT('BEGIN WORK;');
        $sql_update= "UPDATE agricola.saepsec  SET  
        psec_cod_sect = $lote, 
        psec_nom_psec = '$ubicacion_sec',
        psec_cod_finc = $idempresa
         WHERE psec_cod_psec = $id ";
        
        
        $oIfx->QueryT($sql_update);
        $oIfx->QueryT('COMMIT WORK');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'success',
            title: 'Ingresado Correctamente...!',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            timer: 2000
        })");
        $oReturn->script("consultar()");
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        $oReturn->assign("ctrl", "value", 1);
    }


    return $oReturn;
}
function borrar_finca($id=0,$aForm = '')
{

    global $DSN, $DSN_Ifx;
    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $oReturn = new xajaxResponse();
   

    try {

        $oIfx->QueryT('BEGIN WORK;');
        $sql_update= "Delete from agricola.saepsec where psec_cod_psec=$id";
        
        $oIfx->QueryT($sql_update);
        $oIfx->QueryT('COMMIT WORK');
        $oReturn->script("Swal.fire({
            position: 'center',
            type: 'warning',
            title: 'Eliminado Correctamente...!',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            timer: 2000
        })");
        $oReturn->script("consultar()");
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oReturn->alert($e->getMessage());
        $oReturn->assign("ctrl", "value", 1);
    }


    return $oReturn;
}

function lista_boostrap($oIfx, $sql, $campo_defecto, $campo_id, $campo_nom)
{
    $optionEmpr = '';
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $empr_cod_empr = $oIfx->f($campo_id);
                $empr_nom_empr = ($oIfx->f($campo_nom));

                $selectedEmpr = '';
                if ($empr_cod_empr == $campo_defecto) {
                    $selectedEmpr = 'selected';
                }

                $optionEmpr .= '<option value="' . $empr_cod_empr . '" ' . $selectedEmpr . '>' . $empr_nom_empr . '</option>';
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    return $optionEmpr;
}



/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */