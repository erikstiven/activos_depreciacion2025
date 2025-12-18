//-------------------------------------------------------------------------
//ALTER Y CREATES DEL JIRE WEB
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
//ALTER PARA SAEEMPR - USO MODULO CONF EMPRESA
//-------------------------------------------------------------------------

ALTER TABLE SAEEMPR ADD emmpr_uafe_cprov boolean;

//-------------------------------------------------------------------------
//CREATE TABLA PARA ARCHIVOS UAFE - USO MODULO CONF PARAMETROS PROVEEDOR
//-------------------------------------------------------------------------

CREATE TABLE comercial.archivos_uafe (
id SERIAL PRIMARY KEY,
empr_cod_empr INTEGER NOT NULL,
titulo VARCHAR(200) NOT NULL,
ruta VARCHAR(500) NOT NULL,
estado VARCHAR(2) DEFAULT 'AC',
usuario_ingresa INTEGER NOT NULL,
fecha_ingresa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
usuario_actualiza INTEGER,
fecha_actualiza TIMESTAMP
);
//-------------------------------------------------------------------------
//CREATE TABLA comercial.adjuntos_clpv
//-------------------------------------------------------------------------
ALTER TABLE comercial.adjuntos_clpv ADD COLUMN id_archivo_uafe INTEGER;
ALTER TABLE comercial.adjuntos_clpv ADD COLUMN fecha_entrega timestamp NULL;
ALTER TABLE comercial.adjuntos_clpv ADD COLUMN periodo_uafe SMALLINT;
ALTER TABLE comercial.adjuntos_clpv ADD COLUMN fecha_vencimiento_uafe DATE;
//-------------------------------------------------------------------------
//CREATE TABLA saetprov 
//-------------------------------------------------------------------------
ALTER TABLE saetprov ADD COLUMN tprov_venc_uafe date;

//-------------------------------------------------------------------------
// MODULO DE REPORTE APROBACION PROVEEDORES.
//-------------------------------------------------------------------------
INSERT INTO "comercial"."menu_rd" ("menu_id", "menu_codigo", "menu_nombre", "menu_link", "menu_target", "menu_imagen", "menu_ayuda_titulo", "menu_ayuda_texto", "menu_activo", "menu_perfil", "menu_orden", "menu_tipo", "menu_conti", "menu_tip_rd", "menu_cont_adm") VALUES (9876, '060511', 'Aprobacion Proveedores', 'reporte_aprobacion_proveedores/reporte.php', 'main', 'fa fa-table', 'Aprobacion Proveedores', 'Aprobacion Proveedores', 'S', '110011010000000000000000000000', 1, 'M', 0, 'L', 'C');