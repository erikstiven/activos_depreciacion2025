<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= path(DIR_INCLUDE) ?>css/dataTables/dataTables.buttons.min.css" media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>bower_components/select2/dist/css/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= path(DIR_COMPONENTES) ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?= path(DIR_INCLUDE) ?>css/dataTables/dataTables.bootstrap.min.css" media="screen">


    <!--JavaScript--> 
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= path(DIR_INCLUDE) ?>js/dataTables/dataTables.buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="<?= path(DIR_COMPONENTES) ?>bower_components/select2/dist/js/select2.full.min.js"></script>

    <!-- AdminLTE App -->
    <script src="<?= path(DIR_COMPONENTES) ?>dist/js/adminlte.min.js"></script>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?=path(DIR_INCLUDE)?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?=path(DIR_INCLUDE)?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?=path(DIR_INCLUDE)?>js/treeview/css/bootstrap-treeview.css" media="screen">
    <link rel="stylesheet" href="<?=path(DIR_INCLUDE)?>css/dataTables/dataTables.bootstrap.min.css">
    
    <style>
        .input-group-addon.primary {
            color: rgb(255, 255, 255);
            background-color: rgb(50, 118, 177);
            border-color: rgb(40, 94, 142);
        }
    </style>
	

    <script>
        function parametros(id) {
            
            $("#miModal").html("");
            $("#miModal").modal("show");
            xajax_parametros(id, xajax.getFormValues("form1"));
        }
        function borrar(id) {
            $("#miModal").html("");
            $("#miModal").modal("show");
            xajax_borrar(id,xajax.getFormValues("form1"));
        }

        function genera_formulario(){
            xajax_genera_formulario_pedido();
        }

        function guardar() {
           
                xajax_guardar(xajax.getFormValues("form1"));
           
        }
        function actualizar_finca(id) {
           
           xajax_actualiza_finca(id,xajax.getFormValues("form1"));
      
   }
   function borrar_finca(id) {
           
           xajax_borrar_finca(id,xajax.getFormValues("form1"));
      
   }

        //alertas
        function alerts(mensaje, tipo){
            if(tipo=='success'){
                Swal.fire({
                    type: tipo,
                    title: mensaje,                    
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000,
                    width: '600', 
                })
            }else{
                
                Swal.fire({
                    type: tipo,
                    title: mensaje,                    
                    showCancelButton: false,
                    showConfirmButton: true,
                    width: '600', 
                
                })
            }
            
        }
        // carga imagen a servidor
        function upload_image(id) { //Funcion encargada de enviar el archivo via AJAX
            $(".upload-msg").text('Cargando...');
            var inputFileImage = document.getElementById(id);
            var file = inputFileImage.files[0];
            var data = new FormData();
            data.append(id, file);

            $.ajax({
                url: "upload.php?id=" + id, // Url to which the request is send
                type: "POST", // Type of request to be send, called as method
                data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false, // The content type used when sending data to the server.
                cache: false, // To unable request pages to be cached
                processData: false, // To send DOMDocument or non processed data file it is set to false
                success: function(data) // A function to be called if request succeeds
                {
                    $(".upload-msg").html(data);
                    window.setTimeout(function() {
                        $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        }
        function consultar() {
            //jsShowWindowLoad();
            xajax_cargar_ord_compra_respaldo(xajax.getFormValues("form1"));
        }
        function cargarListaSector() {
        xajax_cargarListaSector(xajax.getFormValues("form1"));
    }  
    /*function cargarListaSector2() {
        xajax_cargarListaSector(xajax.getFormValues("form1"));
    }*/
        function anadirListaSector(x, i, elemento) {
    var lista = document.form1.lote;
    var option = new Option(elemento, i);
    lista.options[x] = option;
}
/*function anadirListaSector2(x, i, elemento) {
    var lista = document.form1.lote_up;
    var option = new Option(elemento, i);
    lista.options[x] = option;
}*/
function eliminarListaSector() {
    var sel = document.getElementById("lote");
    for (var i = (sel.length - 1); i >= 1; i--) {
        aBorrar = sel.options[i];
        aBorrar.parentNode.removeChild(aBorrar);
    }
}
/*function eliminarListaSector2() {
    var sel = document.getElementById("lote_up");
    for (var i = (sel.length - 1); i >= 1; i--) {
        aBorrar = sel.options[i];
        aBorrar.parentNode.removeChild(aBorrar);
    }*/


     
  
    </script>

    

    <!--DIBUJA FORMULARIO FILTRO-->
	<body>
        <div class="container-fluid">
            <form id="form1" name="form1" action="javascript:void(null);" novalidate="novalidate" >
				
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#divCompraMenu" aria-controls="divCompraMenu" role="tab" data-toggle="tab">Nueva Seccion</a></li>
                    </ul>

					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="divCompraMenu" style="margin-top: 5px !important;">                            
                            <div id="divFormularioCabecera" ></div>                            
                            <div id="divFormularioDetalle"  class="table-responsive"></div>
                            <div id="divTotal"></div>
                            <div id="divFormularioDetalle2"  class="table-responsive"></div>
						</div>
						<div role="tabpanel" class="tab-pane" id="divPagoMenu">
							<div id="divFormularioFp" 				class="table-responsive"></div>
							<div id="divFormularioDetalleFP_DET"    class="table-responsive"></div>
							<div id="divFormularioDetalle_FP"  		class="table-responsive"></div>
							<div id="divTotalFP"  					class="table-responsive"></div>
						</div>
						<div role="tabpanel" class="tab-pane" id="divRetencionMenu">
							<div id="divFormularioRET" 				class="table-responsive"></div>
							<div id="divFormularioCabeceraRET"     	class="table-responsive"></div>
							<div id="divFormularioDetalleRET"  		class="table-responsive"></div>
						</div>
					</div>
				</div>	

			
			
				<div style="width: 100%;">
					<div id="extra"></div>
					<div id="extra2"></div>
					<div id="extra3"></div>
					<div id="precio_modal"></div>
					<div id="miAdjunto"></div>
					<div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>	

                    <div class="modal fade" id="ModalClpv"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  aria-hidden="true"></div>	
                    <div class="modal fade" id="ModalProd"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>       
                    <div class="modal fade" id="ModalGrid"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>  
                    <div class="modal fade" id="ModalRECO"    tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>        				
                    <div class="modal fade" id="ModalRECOD"    tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true"></div>        			
				</div>
						
            </form>
        </div>
		<div id="divGrid" ></div>
        <br><br><br><br><br><br><br>
    </body>
    <script>
    genera_formulario();
    
    function init() {
            var search = '<?=$ruc?>';
            var table = $('#tbclientes').DataTable({
                dom: 'Bfrtip',
                processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
                "language": {
                    "search": "<i class='fa fa-search'></i>",
                    "searchPlaceholder": "Buscar",
                    'paginate': {
                        'previous': 'Anterior',
                        'next': 'Siguiente'
                    },
                    "zeroRecords": "No se encontro datos",
                    "info": "Mostrando _START_ a _END_ de  _TOTAL_ Total",
                    "infoEmpty": "",
                    "infoFiltered": "(Mostrando _MAX_ Registros Totales)",
                },
                "paging": true,
                "ordering": true,
                "info": true,
            });

            table.search(search).draw();
        }

        function init_prod() {
            var search = '<?=$ruc?>';
            var table = $('#tbclientes_prod').DataTable({
                dom: 'Bfrtip',
                processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
                "language": {
                    "search": "<i class='fa fa-search'></i>",
                    "searchPlaceholder": "Buscar",
                    'paginate': {
                        'previous': 'Anterior',
                        'next': 'Siguiente'
                    },
                    "zeroRecords": "No se encontro datos",
                    "info": "Mostrando _START_ a _END_ de  _TOTAL_ Total",
                    "infoEmpty": "",
                    "infoFiltered": "(Mostrando _MAX_ Registros Totales)",
                },
                "paging": true,
                "ordering": true,
                "info": true,
            });

            table.search(search).draw();
        }
    
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>