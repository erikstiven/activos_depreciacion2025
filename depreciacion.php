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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const catalogoMeses = [
            { id: '01', text: 'Enero' },
            { id: '02', text: 'Febrero' },
            { id: '03', text: 'Marzo' },
            { id: '04', text: 'Abril' },
            { id: '05', text: 'Mayo' },
            { id: '06', text: 'Junio' },
            { id: '07', text: 'Julio' },
            { id: '08', text: 'Agosto' },
            { id: '09', text: 'Septiembre' },
            { id: '10', text: 'Octubre' },
            { id: '11', text: 'Noviembre' },
            { id: '12', text: 'Diciembre' }
        ];

        function generaSelect2(){
            $('.select2').not('#cod_activo_desde, #cod_activo_hasta').select2();
            inicializarSelect2Activos();
        }
        function genera_cabecera_formulario() {
            xajax_genera_cabecera_formulario('nuevo', xajax.getFormValues("form1"));
        }

 
        function genera_cabecera_filtro() {
            xajax_genera_cabecera_formulario('filtro', xajax.getFormValues("form1"));
        }
        function generar(){
            if (!validarFiltros()) {
                return;
            }

            const meses = calcularCantidadMeses();
            const activosEstimados = 'Según filtros (se calculará en el servidor)';
            const operaciones = 'Estimación en servidor';
            const rango = `${$('#anio_desde').val() || ''}-${$('#mes_desde').val() || ''} a ${$('#anio_hasta').val() || ''}-${$('#mes_hasta').val() || ''}`;

            Swal.fire({
                icon: 'info',
                title: 'Confirmar depreciación',
                html: `<p><strong>Rango:</strong> ${rango}</p>` +
                      `<p><strong>Meses:</strong> ${meses}</p>` +
                      `<p><strong>Activos (estimado):</strong> ${activosEstimados}</p>` +
                      `<p><strong>Operaciones:</strong> ${operaciones}</p>`,
                showCancelButton: true,
                confirmButtonText: 'Procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    xajax_generar(xajax.getFormValues("form1"));
                }
            });
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
   
        function eliminar_lista_anio() {
            var sel = document.getElementById("anio");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }
        
        function anadir_elemento_anio(x, i, elemento) {
            var lista = document.form1.anio;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.anio.value = i;
        }

		
        function f_filtro_mes(){
            renderCatalogoMeses();
        }
   
        function renderCatalogoMeses() {
            const selects = ['#mes_desde', '#mes_hasta'];
            selects.forEach((selector) => {
                const $mes = $(selector);
                $mes.empty();
                $mes.append(new Option('Seleccione una opcion..', ''));
                catalogoMeses.forEach((mes) => {
                    $mes.append(new Option(mes.text, mes.id));
                });
                $mes.trigger('change');
            });
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
            document.form1.cod_grupo.value = i;
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
            if(x == '1'){
                var option = new Option(elemento, i, true,true);
            }else{
                var option = new Option(elemento, i);
            }
            lista.options[x] = option;
        }
        function limpiarSelectActivos() {
            $('#cod_activo_desde').val(null).trigger('change');
            $('#cod_activo_hasta').val(null).trigger('change');
        }
        function f_filtro_activos_desde(){
            limpiarSelectActivos();
        }
        function f_filtro_activos_hasta(data){
            limpiarSelectActivos();
        }
        function inicializarSelect2Activos() {
            const requiereContexto = (e) => {
                const empresa = $('#empresa').val();
                const sucursal = $('#sucursal').val();
                if (!empresa || empresa === '0' || !sucursal || sucursal === '0') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione empresa y sucursal',
                        text: 'Debe escoger Empresa y Sucursal antes de buscar activos.'
                    });
                    e.preventDefault();
                    return true;
                }
                return false;
            };

            const baseConfig = {
                width: '100%',
                placeholder: 'Seleccione una opción..',
                allowClear: true,
                ajax: {
                    url: '_Ajax.server.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        const empresa = $('#empresa').val();
                        const sucursal = $('#sucursal').val();
                        if (!empresa || empresa === '0' || !sucursal || sucursal === '0') {
                            return false;
                        }
                        return {
                            action: 'get_activos_rango',
                            empresa: empresa,
                            sucursal: sucursal,
                            grupos: $('#cod_grupo').val() || [],
                            subgrupos: $('#cod_subgrupo').val() || [],
                            solo_vigentes: $('#solo_vigentes').is(':checked') ? 1 : 0,
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        if (!data) {
                            return { results: [] };
                        }
                        const results = data.results || [];
                        if (data.ok) {
                            if (results.length === 0 && (!params || !params.page || params.page === 1)) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sin activos',
                                    text: data.message || 'No se encontraron activos con los filtros seleccionados.'
                                });
                            }
                            return {
                                results: results,
                                pagination: data.pagination || { more: false }
                            };
                        }
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin activos',
                            text: data.message || 'No se encontraron activos con los filtros seleccionados.'
                        });
                        return { results: [] };
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar activos',
                            text: 'Revise la conexión o contacte al administrador.'
                        });
                    }
                },
                language: {
                    noResults: function () {
                        return 'Sin resultados';
                    }
                }
            };

            $('#cod_activo_desde').select2(baseConfig).on('select2:opening', function (e) {
                requiereContexto(e);
            });
            $('#cod_activo_hasta').select2(baseConfig).on('select2:opening', function (e) {
                requiereContexto(e);
            });
        }

        function validarFiltros() {
            const empresa = $('#empresa').val();
            const sucursal = $('#sucursal').val();
            const anioDesde = $('#anio_desde').val();
            const mesDesde = $('#mes_desde').val();
            const anioHasta = $('#anio_hasta').val();
            const mesHasta = $('#mes_hasta').val();
            const activoDesde = $('#cod_activo_desde').val();
            const activoHasta = $('#cod_activo_hasta').val();

            if (!empresa || empresa === '0') {
                Swal.fire({ icon: 'error', title: 'Empresa requerida', text: 'Seleccione la empresa a procesar.' });
                return false;
            }
            if (!sucursal || sucursal === '0') {
                Swal.fire({ icon: 'error', title: 'Sucursal requerida', text: 'Seleccione la sucursal a procesar.' });
                return false;
            }
            if (!anioDesde || !mesDesde) {
                Swal.fire({ icon: 'error', title: 'Periodo inicial requerido', text: 'Seleccione año y mes Desde.' });
                return false;
            }
            if (!anioHasta || !mesHasta) {
                Swal.fire({ icon: 'error', title: 'Periodo final requerido', text: 'Seleccione año y mes Hasta.' });
                return false;
            }

            const periodoDesde = (parseInt(anioDesde, 10) * 12) + (parseInt(mesDesde, 10));
            const periodoHasta = (parseInt(anioHasta, 10) * 12) + (parseInt(mesHasta, 10));

            if (periodoDesde > periodoHasta) {
                Swal.fire({ icon: 'error', title: 'Rango inválido', text: 'El periodo Desde no puede ser mayor al periodo Hasta.' });
                return false;
            }
            if (activoDesde && activoDesde !== '0' && activoHasta && activoHasta !== '0') {
                if (activoDesde > activoHasta) {
                    Swal.fire({ icon: 'error', title: 'Rango de activos inválido', text: 'El activo Desde no puede ser mayor al activo Hasta.' });
                    return false;
                }
            }
            return true;
        }

        function calcularCantidadMeses() {
            const anioDesde = parseInt($('#anio_desde').val() || '0', 10);
            const mesDesde = parseInt($('#mes_desde').val() || '0', 10);
            const anioHasta = parseInt($('#anio_hasta').val() || '0', 10);
            const mesHasta = parseInt($('#mes_hasta').val() || '0', 10);

            if (!anioDesde || !mesDesde || !anioHasta || !mesHasta) {
                return 0;
            }

            const inicio = (anioDesde * 12) + mesDesde;
            const fin = (anioHasta * 12) + mesHasta;
            return (fin - inicio) + 1;
        }

        function mostrarResultado(payload) {
            Swal.close();
            if (!payload) {
                Swal.fire({ icon: 'error', title: 'Respuesta inválida', text: 'No se pudo interpretar la respuesta del servidor.' });
                return;
            }
            if (payload.error) {
                Swal.fire({ icon: 'error', title: 'Error al procesar', text: payload.error });
                return;
            }

            const warningHtml = (payload.warnings || []).map(w => `<li>${w}</li>`).join('');
            const warningsTotales = (payload.warnings || []).length;
            const warningsList = warningsTotales > 20 ? (payload.warnings || []).slice(0, 20) : (payload.warnings || []);
            const warningHtmlLimitado = warningsList.map(w => `<li>${w}</li>`).join('');
            const resumen = `<p><strong>Activos evaluados:</strong> ${payload.evaluados || 0}</p>` +
                            `<p><strong>Registros generados:</strong> ${payload.procesados || 0}</p>` +
                            `<p><strong>Meses procesados:</strong> ${payload.meses || 0}</p>` +
                            `<p><strong>Rango:</strong> ${(payload.rango && payload.rango.desde) ? payload.rango.desde : ''} a ${(payload.rango && payload.rango.hasta) ? payload.rango.hasta : ''}</p>`;

            if (payload.procesados === 0 && (!payload.evaluados || payload.evaluados === 0) && !warningHtml) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin resultados',
                    html: resumen + '<p>No se encontraron activos con los filtros seleccionados.</p>'
                });
                return;
            }

            if (warningHtml) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Proceso finalizado con advertencias',
                    html: `${resumen}<div style="max-height:180px;overflow:auto;text-align:left;"><ul>${warningHtmlLimitado}</ul></div>${warningsTotales > 20 ? `<p>... y ${warningsTotales - 20} más</p>` : ''}`
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: payload.mensaje || 'Proceso terminado',
                html: resumen
            });
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
                                $sql = "select ejer_cod_ejer from saeejer where date_part('year',ejer_fec_inil) = $id_anio and ejer_cod_empr = $idempresa ";
                                $ejer_cod_ejer = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 0);

                                $sql = "select ejer_cod_ejer,  date_part('year',ejer_fec_inil) as anio from saeejer where
                                                ejer_cod_empr = $idempresa order by 2 desc ";
                                $lista_ejer = lista_boostrap_func($oIfx, $sql, $id_anio, 'anio',  'anio' );   

                                $catalogo_meses = [
                                    '01' => 'Enero',
                                    '02' => 'Febrero',
                                    '03' => 'Marzo',
                                    '04' => 'Abril',
                                    '05' => 'Mayo',
                                    '06' => 'Junio',
                                    '07' => 'Julio',
                                    '08' => 'Agosto',
                                    '09' => 'Septiembre',
                                    '10' => 'Octubre',
                                    '11' => 'Noviembre',
                                    '12' => 'Diciembre'
                                ];
                                $lista_mes = '';
                                foreach ($catalogo_meses as $codMes => $nomMes) {
                                    $selected = ($codMes == $id_mes) ? 'selected' : '';
                                    $lista_mes .= "<option value='$codMes' $selected>$nomMes</option>";
                                }
                                // LISTA GRUPOS
                                $sql = " SELECT gact_cod_gact, gact_des_gact
                                        FROM saegact
                                        WHERE gact_cod_empr  = $idempresa ";                               								
                                $listaGrupo = lista_boostrap_func($oIfx, $sql, '', 'gact_cod_gact',  'gact_des_gact' );

                                // LISTA SUBGRUPOS
                                $sql = " SELECT sgac_cod_sgac, sgac_des_sgac from saesgac where sgac_cod_empr = $idempresa ";
                                $listaSubGrupo = lista_boostrap_func($oIfx, $sql, '', 'sgac_cod_sgac',  'sgac_des_sgac' );
                                $listaActivos = '';
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
                                <select id="empresa" name="empresa" class="form-control input-sm select2" onchange="f_filtro_sucursal(); f_filtro_activos_desde(); f_filtro_activos_hasta();" required>
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$lista_empr;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sucursal">* Sucursal </label>
                                <select id="sucursal" name="sucursal" class="form-control input-sm select2" onchange="f_filtro_anio(); f_filtro_grupo(); f_filtro_activos_desde(); f_filtro_activos_hasta();"  required>
                                    <option value="0">Seleccione una opcion..</option>  
                                    <?=$lista_sucu;?>                                  
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="anio_desde"> * Año Desde </label>
                                <select id="anio_desde" name="anio_desde" class="form-control input-sm select2"  onchange="f_filtro_mes();" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_ejer;?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="mes_desde"> * Mes Desde </label>
                                <select id="mes_desde" name="mes_desde" class="form-control input-sm select2">
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_mes;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="anio_hasta"> * Año Hasta </label>
                                <select id="anio_hasta" name="anio_hasta" class="form-control input-sm select2"  onchange="f_filtro_mes();" required>
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_ejer;?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="mes_hasta"> * Mes Hasta </label>
                                <select id="mes_hasta" name="mes_hasta" class="form-control input-sm select2">
                                    <option value="">Seleccione una opcion..</option>
                                    <?=$lista_mes;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-row">                            
                            <div class="col-md-3">
                                <label for="cod_grupo"> Grupo </label>
                                <select id="cod_grupo" name="cod_grupo[]" class="form-control input-sm select2" multiple onchange="f_filtro_subgrupo();">
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$listaGrupo;?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="cod_subgrupo"> Subgrupo </label>
                                <select id="cod_subgrupo" name="cod_subgrupo[]" class="form-control input-sm select2" multiple onchange="f_filtro_activos_desde();f_filtro_activos_hasta();">
                                    <option value="0">Seleccione una opcion..</option>
                                    <?=$listaSubGrupo;?>
                                </select>
                            </div>
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
                            <div class="col-md-3">
                                <label for="solo_vigentes"> Solo vigentes </label>
                                <div class="checkbox" style="margin-top: 6px;">
                                    <label>
                                        <input type="checkbox" id="solo_vigentes" name="solo_vigentes" value="1" checked> Activos con vigencia
                                    </label>
                                </div>
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
         
    <script>genera_cabecera_formulario(); generaSelect2(); renderCatalogoMeses(); f_filtro_activos_desde(); f_filtro_activos_hasta();/*genera_detalle();genera_form_detalle();*/</script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>