/------------tabla de activos
TABLA:saeact
act_cod_act	integer	NO	
tact_cod_tact	integer	YES	
act_cod_empr	integer	NO	
act_cod_sucu	integer	NO	
sgac_cod_sgac	character varying	YES	7
eact_cod_eact	integer	YES	
act_clave_act	character varying	YES	50
act_nom_act	character varying	YES	200
act_marc_act	character varying	YES	50
act_colr_act	character varying	YES	50
act_seri_act	character varying	YES	50
act_mode_act	character varying	YES	50
act_fcmp_act	date	YES	
act_refr_act	character	YES	250
act_comp_act	character varying	YES	20
act_vutil_act	numeric	YES	
act_vres_act	numeric	YES	
act_part_act	smallint	YES	
act_tcam_act	numeric	YES	
act_cant_act	integer	YES	
tdep_cod_tdep	character varying	YES	7
gact_cod_gact	character varying	YES	7
sgac_cod_empr	integer	YES	
ccos_cod_ccos	character varying	YES	35
act_prov_act	character varying	YES	30
act_fiman_act	date	YES	
act_foto_act	character varying	YES	100
act_fdep_act	date	YES	
act_fcorr_act	date	YES	
act_ext_act	smallint	YES	
act_gar_act	integer	YES	
act_usua_act	integer	YES	
act_val_comp	numeric	YES	
act_cod_ramo	integer	YES	
act_nom_prop	character varying	YES	255
act_cod_area	integer	YES	
act_des_ubic	character varying	YES	255
act_cod_pres	character varying	YES	20
act_path_foto	character varying	YES	255
act_cod_rela	integer	YES	
act_clave_padr	character varying	YES	13
act_est_reva	character	YES	1
act_flo_act	character	YES	1
act_pla_act	character varying	YES	20
act_kms_act	numeric	YES	
act_ani_act	integer	YES	
act_otras_esp	character varying	YES	255

/------------tabla de Grupos
TABLA:saegact
gact_cod_gact	character varying	NO	7
gact_des_gact	character varying	YES	60
gact_cta1_gact	character varying	YES	35
gact_cta2_gact	character varying	YES	35
gact_cta3_gact	character varying	YES	35
gact_cta4_gact	character varying	YES	35
gact_cta5_gact	character varying	YES	35
gact_cta6_gact	character varying	YES	35
gact_cta7_gact	character varying	YES	35
gact_cta8_gact	character varying	YES	35
gact_cod_empr	integer	NO	
gact_ban1_gact	character varying	YES	1
gact_ban2_gact	character varying	YES	1
gact_ban3_gact	character varying	YES	1
gact_ban4_gact	character varying	YES	1
gact_ban5_gact	character varying	YES	1
gact_ban6_gact	character varying	YES	1
gact_ban7_gact	character varying	YES	1
gact_ban8_gact	character varying	YES	1
gact_act_mant	character varying	YES	1
gact_cod_ini	character varying	YES	3
gact_cta_reva	character varying	YES	35
gact_cta_desv	character varying	YES	35
gact_cta_supe	character varying	YES	35
gact_gto_reva	character varying	YES	35
gact_dep_reva	character varying	YES	35
gact_cta_defi	character varying	YES	35
gact_gto_desv	character varying	YES	35
gact_dep_desv	character varying	YES	35

/------------tabla de Subgrupos
TABLA:saesgac
sgac_cod_sgac	character varying	NO	7
sgac_cod_empr	integer	NO	
gact_cod_gact	character varying	YES	7
sgac_des_sgac	character varying	YES	30
sgac_cod_ini	character varying	YES	10
sgac_act_vehi	character varying	YES	1

/------------tabla de Valor mensual
TABLA:saemet
met_anio_met	integer	NO	
metd_des_fech	date	NO	
metd_has_fech	date	NO	
metd_cod_empr	integer	NO	
metd_cod_acti	integer	NO	
act_cod_empr	integer	NO	
act_cod_sucu	integer	NO	
met_porc_met	numeric	YES	
metd_val_metd	numeric	YES	
met_num_dias	integer	YES	
metd_cod_reva	integer	YES	

/------------tabla de Depreciación generada
TABLA:saecdep
cdep_cod_acti	integer	NO	
cdep_cod_tdep	character varying	NO	7
cdep_mes_depr	integer	NO	
cdep_ani_depr	integer	NO	
cdep_fec_depr	date	NO	
act_cod_empr	integer	NO	
act_cod_sucu	integer	NO	
cdep_dep_acum	numeric	YES	
cdep_val_reex	numeric	YES	
cdep_val_repr	numeric	YES	
cdep_tot_reex	numeric	YES	
cdep_dep_arex	numeric	YES	
cdep_gas_depn	numeric	YES	
cdep_gas_dere	numeric	YES	
cdep_tot_depr	numeric	YES	
cdep_val_rep1	numeric	YES	
cdep_cod_empr	integer	YES	
cdep_ind_reex	numeric	YES	
cdep_num_comp	character varying	YES	50
cdep_est_cdep	character varying	YES	2
cdep_fec_cdep	date	YES	
cdep_cod_reva	integer	YES	

/------------tabla de Empresa
TABLA:saeempr
empr_cod_empr	integer	NO	
empr_cod_ciud	integer	YES	
empr_nom_empr	character varying	YES	255
empr_dir_empr	character varying	YES	60
empr_ruc_empr	character varying	YES	20
empr_cpo_empr	character varying	YES	20
empr_mai_empr	character varying	YES	40
empr_repres	character varying	YES	40
empr_opc_empr	integer	YES	
empr_tel_resp	character varying	YES	9
empr_fax_empr	character varying	YES	13
empr_ced_repr	character varying	YES	13
empr_num_dire	character varying	YES	200
empr_nom_cont	character varying	YES	40
empr_ruc_cont	character varying	YES	15
empr_lic_cont	character varying	YES	15
empr_tip_empr	character varying	YES	1
empr_num_resu	character varying	YES	15
empr_fec_resu	date	YES	
empr_tip_iden	character varying	YES	1
empr_mdse_pcon	character varying	YES	1
empr_cod_prov	character varying	YES	2
empr_cod_cant	character varying	YES	5
empr_ema_repr	character varying	YES	80
empr_ac1_empr	character varying	YES	100
empr_ac2_empr	character varying	YES	100
empr_nom_mzon	character varying	YES	255
empr_dir_mzon	character varying	YES	255
empr_pais_ruc	character varying	YES	3
empr_iva_empr	numeric	YES	
empr_con_pres	character varying	YES	1
empr_cta_extr	character varying	YES	1
empr_tipo_empr	character varying	YES	1
empr_cod_codigo	character varying	YES	10
empr_num_estab	character varying	YES	3
empr_conta_sn	character varying	YES	1
empr_path_logo	character varying	YES	255
empr_tie_espera	integer	YES	
empr_ema_comp	character varying	YES	100
empr_cod_cesa	character varying	YES	50
empr_cod_aduna	character varying	YES	20
empr_cm1_empr	character	YES	1000
empr_cm2_empr	character varying	YES	255
empr_sitio_web	character varying	YES	100
empr_menu_sucu	character varying	YES	255
empr_prec_mat	character varying	YES	255
empr_prec_sucu	character varying	YES	255
empr_prec_bode	integer	YES	
empr_nom_toke	character varying	YES	255
empr_pass_toke	character varying	YES	255
empr_tip_firma	character varying	YES	2
empr_cod_pais	integer	YES	
empr_reso_recau	character varying	YES	50
empr_web_color	character	YES	50
empr_web_color2	character	YES	50
empr_img_rep	character	YES	255
empr_ema_test	text	YES	
empr_ema_sn	character varying	YES	1
empr_precios_sn	character varying	YES	1
empr_ac3_empr	character varying	YES	50
empr_rrhh_nom	character varying	YES	50
empr_rol_sn	character varying	YES	1
empr_token_api	character varying	YES	80
empr_ws_sri_sn	character varying	YES	1
empr_ws_sri_url	character varying	YES	255
empr_rimp_sn	character varying	YES	1
empr_rete_sn	character varying	YES	1
empr_item_agru_sn	character varying	YES	1
empr_noti_sn	character varying	YES	1
empr_tip_impr	integer	YES	
empr_fac_empr	character varying	YES	1
empr_det_fac	text	YES	
empr_tip_comp	integer	YES	
empr_tip_agri	integer	YES	
empr_dig_celu	smallint	YES	
empr_for_rdep	character varying	YES	1
empr_dataico_id	character varying	YES	50
empr_dataico_token	character varying	YES	50
empr_whatsapp_sn	integer	YES	
empr_whatsapp_url	character varying	YES	50
empr_sms_sn	integer	YES	
empr_sms_token	character varying	YES	50
empr_sms_url	character varying	YES	50
empr_sms_key	character varying	YES	100
empr_sms_cant	integer	YES	
empr_sms_tipo	integer	YES	
empr_datafast_sn	integer	YES	
empr_datafast_url	character varying	YES	50
empr_datafast_token	character varying	YES	50
empr_portal_link	character varying	YES	100
empr_bi_link	text	YES	
empr_token_tienda	character varying	YES	255
empr_cod_parr	integer	YES	
empr_nomcome_empr	character varying	YES	255
empr_ncue_scotia	character varying	YES	50
empr_ncue_abono	character varying	YES	50
empr_cod_unico_interbank	character varying	YES	50
empr_cod_empresa_interbank	character varying	YES	50
empr_cod_rubro_interbank	character varying	YES	50
empr_cod_ftdr	integer	YES	
empr_url_kardex	character varying	YES	255
empr_punto_venta	integer	YES	
empr_gmaps_sn	character varying	YES	1
empr_cta_sn	character varying	YES	50
empr_det_cta	character varying	YES	500
empr_rsoc_empr	character varying	YES	1
empr_frso_empr	integer	YES	
empr_key_maps	text	YES	
empr_servi_sn	integer	YES	
empr_servi_url	character varying	YES	100
empr_servi_user	character varying	YES	100
empr_servi_pass	character varying	YES	100
empr_laar_sn	character varying	YES	2
empr_laar_url	character varying	YES	255
empr_laar_user	character varying	YES	100
empr_laar_pass	character varying	YES	100
empr_laar_cod	character varying	YES	100
empr_bpi_sftp_sn	character varying	YES	2
empr_bpi_sftp_user	character varying	YES	50
empr_bpi_sftp_ip	character varying	YES	50
empr_bpi_sftp_port	character varying	YES	5
empr_bpi_sftp_ppk_f_dir	character varying	YES	500
empr_bpi_sftp_remote_dir	character varying	YES	500
empr_bpi_sftp_local_dir	character varying	YES	500
empr_cod_bgy	character varying	YES	5
empr_bus_pers	character varying	YES	1
empr_sms_remitente	character varying	YES	255
empr_sn_conta	character varying	YES	1
empr_ozmap_sn	character varying	YES	2
empr_ozmap_url	character varying	YES	500
empr_ozmap_user	character varying	YES	50
empr_ozmap_pass	character varying	YES	100
empr_ozmap_api_token	character varying	YES	500
empr_enti_code	character varying	YES	100
empr_det_rinf	character varying	YES	500
empr_rinf_sn	character varying	YES	1
empr_cfe_contr	character varying	YES	1
empr_siigo_sn	character varying	YES	2
empr_siigo_api_url	character varying	YES	200
empr_siigo_username	character varying	YES	100
empr_siigo_access_token	character varying	YES	500
empr_siigo_partnerid	character varying	YES	500
empr_siigo_autoenvio	character varying	YES	2
empr_siigo_autoenvio_mail	character varying	YES	2
empr_openpay_sn	character varying	YES	2
empr_openpay_api_url	character varying	YES	500
empr_openpay_idempresa	character varying	YES	50
empr_openpay_publick	character varying	YES	1000
empr_openpay_privatek	character varying	YES	1000
empr_whatsapp_token	character varying	YES	255
empr_whatsapp_reintentos	smallint	YES	
empr_whatsapp_cant	bigint	YES	
empr_sms_reintentos	smallint	YES	
empr_ws_iden_sn	character varying	YES	2
empr_ws_iden_url	text	YES	
empr_ws_iden_renueva	integer	YES	
empr_ws_iden_token	text	YES	
empr_cod_uni	character varying	YES	255
empr_mone_fxfp	character varying	YES	1
empr_mod_fiscal	character varying	YES	1
empr_asum_igtf	character varying	YES	1
empr_cod_alt	smallint	YES	
emmpr_uafe_cprov	boolean	YES	

/------------tabla de Sucursal
TABLA:saesucu
sucu_cod_sucu	integer	NO	
sucu_cod_empr	integer	NO	
sucu_cod_ciud	integer	YES	
sucu_nom_sucu	character varying	YES	60
sucu_dir_sucu	character varying	YES	100
sucu_ban_deft	character varying	YES	1
sucu_ser_fact	character varying	YES	3
sucu_num_fact	character varying	YES	3
sucu_ruc_sucu	character varying	YES	20
sucu_num_dir	character varying	YES	10
sucu_telf_secu	character varying	YES	10
sucu_fax_secu	character varying	YES	10
sucu_email_secu	character varying	YES	60
sucu_sigl_sucu	character varying	YES	5
sucu_resp_sucu	character varying	YES	60
sucu_tip_emis	integer	YES	
sucu_tip_ambi	integer	YES	
sucu_cod_toke	integer	YES	
sucu_dir_gene	character varying	YES	250
sucu_dir_firm	character varying	YES	250
sucu_dir_auto	character varying	YES	250
sucu_dir_naut	character varying	YES	250
sucu_tip_toke	character varying	YES	15
sucu_fac_elec	character varying	YES	1
sucu_tip_fac	character	YES	1
sucu_dsct_soli	character varying	YES	2
sucu_iva_comp	numeric	YES	
sucu_tip_esqu	integer	YES	
sucu_path_logo	character varying	YES	255
sucu_cod_agen	character varying	YES	5
sucu_cod_cant	integer	YES	
sucu_fec_fact	character varying	YES	50
sucu_fpag_sucu	character varying	YES	1
sucu_fpag_cod	integer	YES	
sucu_pref_num	character varying	YES	50
sucu_resol_num	character varying	YES	50
sucu_alias_sucu	character varying	YES	50
sucu_cod_prec	character varying	YES	255
sucu_cod_cost	character varying	YES	255
sucu_ubi_geo	character varying	YES	10
sucu_cod_site	character varying	YES	255
sucu_val_site	character varying	YES	255


