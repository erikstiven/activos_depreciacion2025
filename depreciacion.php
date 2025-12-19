<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>
    	
    <!--CSS--> 
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/css/bootstrap-treeview.css" media="screen"> 
	
    <!-- Select2 -->
    <link rel="stylesheet" href="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/select2/dist/css/select2.min.css">
    
    <!-- Theme style -->
    <link rel="stylesheet" href="<?=$_COOKIE["JIREH_COMPONENTES"]?>dist/css/AdminLTE.min.css">
    <!--Javascript--> 
    
  
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>          
    <script src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/bootstrap.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/js/bootstrap-treeview.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	
    <!-- Select2 -->
    <script src="<?=$_COOKIE["JIREH_COMPONENTES"]?>bower_components/select2/dist/js/select2.full.min.js"></script>
    
    <script>
        function generaSelect2(){
            $('.select2').select2();
        }
        function genera_cabecera_formulario() {
            xajax_genera_cabecera_formulario('nuevo', xajax.getFormValues("form1"));
        }

 
        function genera_cabecera_filtro() {
            xajax_genera_cabecera_formulario('filtro', xajax.getFormValues("form1"));
        }		
        function generar(){
            if(ProcesarFormulario() == true && validar_rango_fechas()){
                xajax_prevalidar_depreciacion(xajax.getFormValues("form1"));
            }
        }
		
        function f_filtro_sucursal(data){
            xajax_f_filtro_sucursal(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_sucursal() {
            var sel = document.getElementById("sucursal");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_sucursal(x, i, elemento) {
            var lista = document.form1.sucursal;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.sucursal.value = i;
        }

        function f_filtro_anio(data){
            xajax_f_filtro_anio(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_anio_desde() {
            var sel = document.getElementById("anio_desde");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_anio_desde(x, i, elemento) {
            var lista = document.form1.anio_desde;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function eliminar_lista_anio_hasta() {
            var sel = document.getElementById("anio_hasta");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_anio_hasta(x, i, elemento) {
            var lista = document.form1.anio_hasta;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

		
        function f_filtro_mes(data){
            xajax_f_filtro_mes(xajax.getFormValues("form1"), data);           
        }
   
        function eliminar_lista_mes_desde() {
            var sel = document.getElementById("mes_desde");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_mes_desde(x, i, elemento) {
            var lista = document.form1.mes_desde;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function eliminar_lista_mes_hasta() {
            var sel = document.getElementById("mes_hasta");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_mes_hasta(x, i, elemento) {
            var lista = document.form1.mes_hasta;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		function f_filtro_grupo(data){
            xajax_f_filtro_grupo(xajax.getFormValues("form1"), data);           
        }
   
		function eliminar_lista_grupo() {
            var sel = document.getElementById("cod_grupo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_grupo(x, i, elemento) {
            var lista = document.form1.cod_grupo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		
		function f_filtro_subgrupo(){         
            xajax_f_filtro_subgrupo(xajax.getFormValues("form1"));
        }
   
		function eliminar_lista_subgrupo() {
            var sel = document.getElementById("cod_subgrupo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_subgrupo(x, i, elemento) {
            var lista = document.form1.cod_subgrupo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		function f_filtro_activos(){
            xajax_f_filtro_activos_desde(xajax.getFormValues("form1"));           
        }
   
		function eliminar_lista_activo_desde() {
            var sel = document.getElementById("cod_activo_desde");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_activo_desde(x, i, elemento) {
            var lista = document.form1.cod_activo_desde;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
		function f_filtro_activos_hasta(data){
            xajax_f_filtro_activos_hasta(xajax.getFormValues("form1"));           
        }
   
		function eliminar_lista_activo_hasta() {
            var sel = document.getElementById("cod_activo_hasta");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_activo_hasta(x, i, elemento) {
            var lista = document.form1.cod_activo_hasta;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function validar_rango_fechas() {
            var anioDesde = document.form1.anio_desde.value;
            var mesDesde = document.form1.mes_desde.value;
            var anioHasta = document.form1.anio_hasta.value;
            var mesHasta = document.form1.mes_hasta.value;

            if (!anioDesde || !mesDesde || !anioHasta || !mesHasta) {
                return true;
            }

            var desde = parseInt(anioDesde + (mesDesde.length === 1 ? '0' + mesDesde : mesDesde), 10);
            var hasta = parseInt(anioHasta + (mesHasta.length === 1 ? '0' + mesHasta : mesHasta), 10);

            if (desde > hasta) {
                Swal.fire({
                    position: 'center',
                    type: 'warning',
                    title: 'El rango de fechas es inválido. Verifique Año y Mes.',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }
            return true;
        }
		

    </script>
    <!--DIBUJA FORMULARIO FILTRO-->
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <body>
        <div class="row" id="Div_Principal">
            <form id="form1" class="form-horizontal" name="form1" action="javascript:void(null);">
                <div class="main-row col-md-12">
                    <div class="col-md-12">
                        <h4 class="text-primary">PROCESO <small> CALCULO DEPRECIACIONES </small></h4>
                            <?
                                global $DSN_Ifx, $DSN;
                                if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
                                $idempresa  = $_SESSION['U_EMPRESA'];
                                $idsucursal = $_SESSION['U_SUCURSAL'];
                                $idPerfil   = $_SESSION['U_PERFIL'];

                                $oCon = new Dbo;
                                $oCon->DSN = $DSN;
                                $oCon->Conectar();
                                
                                $oIfx = new Dbo;
                                $oIfx->DSN = $DSN_Ifx;
                                $oIfx->Conectar();

                                $fu = new Formulario;
                                $fu->DSN = $DSN;

                                $sql_empr = '';
                                if ($idPerfil != 1 && $idPerfil != 2) {
                                    $sql_empr = " where empr_cod_empr = $idempresa ";
                                }

                                // EMPRESA
                                $sql = "select empr_cod_empr, empr_nom_empr from saeempr $sql_empr ";
                                $lista_empr = lista_boostrap_func($oIfx, $sql, $idempresa, 'empr_cod_empr',  'empr_nom_empr' );

                                $sqlSucu = "";
                                if ($idPerfil != 1 && $idPerfil != 2) {
                                    $sqlSucu = " and sucu_cod_sucu = $idsucursal";
                                }

                                $sql = "select sucu_cod_sucu, sucu_nom_sucu
                                        from saesucu  where sucu_cod_empr = $idempresa
                                        $sqlSucu";
                                $lista_sucu = lista_boostrap_func($oIfx, $sql, $idsucursal, 'sucu_cod_sucu',  'sucu_nom_sucu' );    
                                // FECHAS
                                $id_anio = date("Y");
                                $id_mes  = date("m");
								$fechaActual = date("Y-m-d");
                                $sql = "select ejer_cod_ejer,  date_part('year',ejer_fec_inil) as anio from saeejer where
                                                ejer_cod_empr = $idempresa order by 2 desc ";
                                $lista_ejer = lista_boostrap_func($oIfx, $sql, $id_anio, 'anio',  'anio' );   

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
                                $lista_mes = '';
                                foreach ($meses as $codigo => $descripcion) {
                                    $selected = ($id_mes == $codigo) ? ' selected' : '';
                                    $lista_mes .= "<option value=\"{$codigo}\"{$selected}>{$descripcion}</option>";
                                }
                                // LISTA GRUPOS
                                $sql = " SELECT gact_cod_gact, gact_des_gact
                                        FROM saegact
                                        WHERE gact_cod_empr  = $idempresa ";                               								
                                $listaGrupo = lista_boostrap_func($oIfx, $sql, '', 'gact_cod_gact',  'gact_des_gact' );

                                // LISTA SUBGRUPOS
                                $sql = " SELECT sgac_cod_sgac, sgac_des_sgac from saesgac where sgac_cod_empr = $idempresa ";
                                $listaSubGrupo = lista_boostrap_func($oIfx, $sql, '', 'sgac_cod_sgac',  'sgac_des_sgac' );
                            ?>
                    </div>
                    <div class="col-md-12">
                            <div class="btn-group">
                                <div class="btn btn-primary btn-sm" onclick="location.reload();">
                                    <span class="glyphicon glyphicon-file"></span>
                                    Nuevo
                                </div>                                
                            </div>                
                    </div>                  

                    <div class="col-md-12">
                        <div class="form-row">
                            <div class="col-md-3">
                                <label for="empresa">* Empresa </label>
                                <select id="empresa" name="empresa" class="form-control input-sm select2" onchange="f_filtro_sucursal(); f_filtro_anio(); f_filtro_grupo();" required>
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$lista_empr;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sucursal">* Sucursal </label>
                                <select id="sucursal" name="sucursal" class="form-control input-sm select2" onchange="f_filtro_anio(); f_filtro_grupo(); f_filtro_activos();"  required>
                                    <option value="0">Seleccione una opcion..</option>  
                                    <?=$lista_sucu;?>                                  
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="cod_grupo"> Grupo </label>
                                <select id="cod_grupo" name="cod_grupo[]" class="form-control input-sm select2" multiple="multiple" onchange="f_filtro_subgrupo();">
                                    <?=$listaGrupo;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="cod_subgrupo"> Subgrupo </label>
                                <select id="cod_subgrupo" name="cod_subgrupo[]" class="form-control input-sm select2" multiple="multiple" onchange="f_filtro_activos();">
                                    <?=$listaSubGrupo;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-row">
                            <div class="col-md-2">
                                <label for="solo_vigentes">Solo activos vigentes</label>
                                <div>
                                    <input type="checkbox" id="solo_vigentes" name="solo_vigentes" value="1" checked onchange="f_filtro_activos();">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="anio_desde">* Año Desde </label>
                                <select id="anio_desde" name="anio_desde" class="form-control input-sm select2" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_ejer;?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="mes_desde">* Mes Desde </label>
                                <select id="mes_desde" name="mes_desde" class="form-control input-sm select2" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_mes;?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="anio_hasta">* Año Hasta </label>
                                <select id="anio_hasta" name="anio_hasta" class="form-control input-sm select2" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_ejer;?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="mes_hasta">* Mes Hasta </label>
                                <select id="mes_hasta" name="mes_hasta" class="form-control input-sm select2" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_mes;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-row">
                            <div class="col-md-3">
                                <label for="cod_activo_desde"> Activo Desde </label>
                                <select id="cod_activo_desde" name="cod_activo_desde" class="form-control input-sm select2" >
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$listaActivos;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="cod_activo_hasta"> Activo Hasta </label>
                                <select id="cod_activo_hasta" name="cod_activo_hasta" class="form-control input-sm select2" >
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$listaActivos;?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row"> 
                            <div class="col-md-12">
                                    <div><label for="consultar">* Consultar:</label></div>
                                    <div class="btn btn-primary btn-sm" onclick="generar();" style="width: 100%">
                                        <span class="glyphicon glyphicon-cog"></span>
                                        Procesar
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </form>
        </div>
    </body>
         
    <script>genera_cabecera_formulario(); generaSelect2();/*genera_detalle();genera_form_detalle();*/</script> 
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>
