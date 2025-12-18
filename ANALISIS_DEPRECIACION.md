# Activos Depreciación - Documento de análisis y diseño

## Fase 1: Comportamiento actual al presionar "Procesar"

### 1. Parámetros recibidos desde la UI
El formulario de `depreciacion.php` envía los siguientes campos al llamar a `generar()`:
- **empresa** (`empresa`): lista de empresas, requerida. 【F:depreciacion.php†L270-L296】
- **sucursal** (`sucursal`): lista filtrada por empresa, requerida. 【F:depreciacion.php†L270-L296】
- **año** (`anio`): catálogo de años del ejercicio contable, requerido. 【F:depreciacion.php†L283-L296】
- **mes** (`mes`): catálogo de periodos del ejercicio. 【F:depreciacion.php†L291-L296】
- **grupo** (`cod_grupo`): filtro opcional. 【F:depreciacion.php†L303-L308】
- **subgrupo** (`cod_subgrupo`): dependiente del grupo, opcional. 【F:depreciacion.php†L309-L315】
- **activo desde / activo hasta** (`cod_activo_desde`, `cod_activo_hasta`): rangos opcionales de código de activo. 【F:depreciacion.php†L316-L329】

### 2. Tablas consultadas en el procesamiento
La función `generar()` en `_Ajax.server.php` usa varias tablas:
- **saetdep**: catálogo de tipos de depreciación para conocer si es mensual o diaria (`tdep_tip_val`). 【F:_Ajax.server.php†L424-L435】
- **saemet**: origen del gasto mensual, consultando por fecha_hasta (`metd_has_fech`) y empresa. 【F:_Ajax.server.php†L436-L452】
- **saeact / saesgac**: obtiene activos vigentes (act_ext_act = 1), su tipo de depreciación y pertenencia a grupo/subgrupo. 【F:_Ajax.server.php†L454-L474】
- **saecdep**: consulta acumulados previos y limpia/crea registros de depreciación para el activo y mes. 【F:_Ajax.server.php†L491-L546】

### 3. Cálculo de fechas
- **fecha_hasta**: último día del mes solicitado; se calcula con `mktime` llevando el día 0 del mes siguiente. 【F:_Ajax.server.php†L388-L392】
- **fechaAnterior**: último día del mes anterior; si el mes es enero se retrocede al diciembre del año previo. 【F:_Ajax.server.php†L395-L405】

### 4. Qué significa "generar depreciación"
- **Inserción en saecdep**: se inserta `cdep_cod_acti`, `cdep_cod_tdep`, `cdep_mes_depr`, `cdep_ani_depr`, `cdep_fec_depr`, `act_cod_empr`, `act_cod_sucu`, `cdep_dep_acum`, `cdep_gas_depn`, `cdep_est_cdep`, `cdep_fec_cdep`, `cdep_val_rep1`. 【F:_Ajax.server.php†L540-L546】
- **Valores desde saemet**: el gasto mensual (`cdep_gas_depn`) se obtiene de `metd_val_metd` para el activo y la fecha seleccionada. Si no existe registro en `saemet`, el código actual coloca 0 sin avisar porque el `continue` está comentado y se asigna 0 cuando el arreglo no tiene entrada. 【F:_Ajax.server.php†L436-L452】【F:_Ajax.server.php†L506-L510】
- **Cálculo del acumulado**: busca la suma de `cdep_dep_acum + cdep_gas_depn` del mes anterior en `saecdep`; si no hay valor previo, el acumulado arranca con el valor mensual y `cdep_val_rep1` se fija en 0. 【F:_Ajax.server.php†L521-L533】【F:_Ajax.server.php†L540-L546】
- **Ausencia de mes anterior**: si no existe registro previo, el acumulado parte en cero (se asigna el gasto mensual como acumulado). 【F:_Ajax.server.php†L528-L533】
- **Reproceso del mismo mes**: si ya existe registro para el mismo activo/fecha, se elimina antes de insertar nuevamente. 【F:_Ajax.server.php†L491-L505】

### 5. Filtros aplicados
- **empresa** y **sucursal**: la empresa sí filtra la consulta de activos, pero la sucursal solo se guarda en el insert final sin filtrar la lista de activos (riesgo de procesar activos de otras sucursales). 【F:_Ajax.server.php†L371-L392】【F:_Ajax.server.php†L454-L474】【F:_Ajax.server.php†L540-L546】
- **grupo** y **subgrupo**: filtran la lista de activos (`saeact`) por `gact_cod_gact` y `sgac_cod_sgac`. 【F:_Ajax.server.php†L382-L419】【F:_Ajax.server.php†L454-L474】
- **activo desde / hasta**: si ambos vienen definidos, limitan el rango de activos por código. 【F:_Ajax.server.php†L416-L419】
- Otros parámetros (año, mes) determinan `fecha_hasta` pero no se guardan explícitamente más allá de `cdep_mes_depr`, `cdep_ani_depr` y `cdep_fec_depr`. 【F:_Ajax.server.php†L388-L405】【F:_Ajax.server.php†L540-L546】

### 6. Errores detectados en el flujo actual
- **Gasto 0 silencioso**: si falta `saemet` para un activo/mes, el proceso inserta 0 porque el `continue` está comentado; no se avisa al usuario. 【F:_Ajax.server.php†L436-L452】【F:_Ajax.server.php†L506-L510】
- **Dependencia del mes anterior sin validar continuidad**: el acumulado toma el valor del mes previo exacto (`fechaAnterior`), por lo que si hay meses sin procesar el acumulado queda incompleto y no se alerta. 【F:_Ajax.server.php†L395-L405】【F:_Ajax.server.php†L521-L533】
- **Sucursal no filtra datos**: aunque se captura y se almacena en `saecdep`, no restringe la consulta de activos. Esto puede generar registros para sucursales no deseadas. 【F:_Ajax.server.php†L371-L392】【F:_Ajax.server.php†L454-L474】【F:_Ajax.server.php†L540-L546】
- **Catálogo de meses vulnerable**: el select de meses depende del cambio de año y puede quedar vacío, ocasionando errores de selección en la UI. 【F:depreciacion.php†L284-L296】

## Fase 2: Lógica base a preservar
- Se genera **un registro en saecdep por activo y mes** solicitado. 【F:_Ajax.server.php†L491-L546】
- **El gasto mensual proviene de saemet**; no se recalcula. 【F:_Ajax.server.php†L436-L452】【F:_Ajax.server.php†L506-L510】
- **El acumulado** (`cdep_dep_acum`) toma el acumulado real del mes anterior + gasto mensual; si no hay histórico, inicia en 0 y suma el gasto del mes. 【F:_Ajax.server.php†L521-L533】
- **Sin registro previo**: acumulado empieza desde el gasto del mes, equivalente a acumulado 0 + gasto. 【F:_Ajax.server.php†L528-L533】
- **Reproceso**: si existe el mismo mes/activo se borra y se inserta nuevamente. 【F:_Ajax.server.php†L491-L505】
- **Manejo indispensable en el masivo**: cuando falte `saemet`, no se debe insertar nada y se debe informar (para revertir el “gasto 0 silencioso” actual) sin alterar la lectura directa del gasto. 【F:_Ajax.server.php†L506-L510】

_Esta lógica es la que no debe romperse al implementar el proceso masivo._

## Fase 3: Extensión masiva propuesta (sin alterar la lógica base)
1. **Rango de fechas**: aceptar "Desde Año/Mes" y "Hasta Año/Mes"; iterar mes a mes calculando `fecha_hasta` y `fechaAnterior` igual que hoy. Para cada iteración, ejecutar la misma secuencia de lectura de `saemet`, cálculo de acumulado y reproceso sobre `saecdep`.
2. **Filtros masivos**:
   - Empresa obligatoria.
   - Grupo y Subgrupo como multiselección dependiente (Subgrupo filtrado por Grupo).
   - Activo Desde/Hasta opcionales.
   - Opción "solo activos vigentes" aplicando `act_ext_act = 1` como en la consulta actual. 【F:_Ajax.server.php†L470-L474】
3. **Comportamiento por mes**:
   - Leer `saemet` para el mes concreto; si falta un activo, no insertar registro y reportar error indicando activo y mes faltante.
   - Buscar acumulado real en `saecdep` del mes anterior real (considerando reprocesos).
   - Si existe registro del mismo mes, eliminar y volver a insertar con los valores obtenidos, replicando el reproceso actual.
4. **Resultado esperado**: la ejecución masiva debe producir exactamente los mismos registros que ejecutar manualmente mes por mes el módulo actual.

## Fase 4: UI y validaciones para el módulo masivo
- **Selects**: asegurar carga AJAX de Activo Desde/Hasta y preservar catálogo de meses estático para evitar que desaparezcan al cambiar el año. El select de meses debe ser un catálogo fijo de 12 valores y no depender de la consulta de ejercicios para evitar vacíos al cambiar de año. 【F:depreciacion.php†L284-L296】
- **Endpoints AJAX necesarios** (parámetros):
  - `f_filtro_sucursal(empresa)`.
  - `f_filtro_anio(empresa)`.
  - `f_filtro_mes(anio, empresa)` devolviendo siempre los 12 meses.
  - `f_filtro_grupo(empresa)`.
  - `f_filtro_subgrupo(empresa, grupo[])` con soporte multiselect.
  - `f_filtro_activos_desde/hasta(empresa, subgrupo[], rango opcional)`.
- **Validaciones**:
  - Rango de meses: Desde <= Hasta (comparando año/mes).
  - Confirmación previa mostrando cantidad total de meses x activos a procesar.
- **Mensajería**: si falta gasto en `saemet` para algún activo/mes, mostrar error específico y no generar registro vacío (corrigiendo el comportamiento actual que inserta gasto 0 en silencio). 【F:_Ajax.server.php†L506-L510】

## Cambios necesarios (diseño)
### Backend (PHP)
- Nueva ruta/acción para el proceso masivo que recorra el rango mes a mes reutilizando la lógica actual de `generar()`.
- Función utilitaria para calcular `fecha_hasta` y `fechaAnterior` dado año/mes, compartida con el proceso masivo.
- Validar y formatear filtros multiselect (grupo/subgrupo) en la consulta de activos, manteniendo `act_ext_act = 1` y opción de rango de activos.
- Antes de insertar por mes, eliminar registros existentes (`saecdep`) del mismo activo/fecha, igual que hoy.
- Al encontrar ausencia de `saemet` para un activo/mes, registrar error y saltar la inserción de ese caso.

### Frontend (JS/UI)
- Formularios separados para rango Desde/Hasta de año/mes y multiselects de Grupo/Subgrupo.
- Catálogo fijo de 12 meses en los selects; al cambiar año solo se recalcula el ejercicio pero no se ocultan meses.
- AJAX para cargar activos desde/hasta según subgrupos seleccionados; garantizar que ambos selects se llenan.
- Validar que la combinación Desde (año/mes) no supere Hasta y solicitar confirmación con el total estimado de operaciones.

## Checklist de pruebas
- Procesar un solo mes y comparar contra resultado actual (saecdep vs. Excel/reporte) para un activo.
- Procesar varios meses consecutivos y verificar que el acumulado coincide con la sumatoria mes a mes manual.
- Reproceso del mismo mes: confirmar que elimina e inserta de nuevo conservando valores de `saemet`.
- Caso sin mes anterior: primer mes procesado debe iniciar acumulado en gasto del mes.
- Filtros por grupo/subgrupo y rango de activos: validar que solo se generan registros de los activos filtrados.
- Opción "solo vigentes": incluir únicamente activos con `act_ext_act = 1`.
- Escenario con dato faltante en `saemet`: el sistema debe informar activo/mes y no crear registro en `saecdep`.
