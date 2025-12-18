# Auditoría y plan de depuración — Activos Depreciación Masivo

## 1) Lógica actual: causas de fallas
- **Reinicio de acumulado**: el acumulado de `saecdep` se arma usando el registro del mes anterior (`cdep_dep_acum + cdep_gas_depn`). Si el mes previo no existe, se inicializa con el gasto del mes, dejando el histórico inconexo y reiniciando la secuencia.【F:_Ajax.server.php†L518-L533】
- **Dependencias incorrectas**: el gasto se toma de `saemet.metd_val_metd` sin validar la existencia del mes antes de iterar activos, por lo que un mes faltante termina insertando gasto 0 silenciosamente.【F:_Ajax.server.php†L438-L510】
- **Validaciones faltantes**:
  - No se consulta el último mes generado por activo (`saecdep`) ni se valida continuidad temporal del rango solicitado.【F:_Ajax.server.php†L395-L410】【F:_Ajax.server.php†L491-L546】
  - El reproceso borra e inserta para la misma fecha, pero no previene duplicados si cambian claves (p. ej. mes/año distintos) ni alerta al usuario sobre meses omitidos.【F:_Ajax.server.php†L491-L506】

## 2) Solución backend (cambios mínimos)
- **Detección de último mes**: para cada activo, obtener `max(cdep_fec_depr)` en `saecdep`; derivar `fechaInicio = max(ultimoMes + 1, fecha_desde_ui)`.
- **Generación secuencial**: iterar mes a mes desde `fechaInicio` hasta `fechaHastaUI`, construyendo `fecha_hasta = Y-m-t` y `fechaAnterior = -1 mes` en cada vuelta.
- **Validar `saemet` antes de grabar**: antes de procesar el bucle de activos del mes, confirmar que existe `metd_val_metd` para `metd_has_fech = fecha_hasta`; si no existe, abortar la transacción y reportar el mes faltante.
- **Reutilizar inserción actual**: mantener el borrado/control de duplicado por `(activo, fecha_depr)` y la estructura actual de `INSERT` sin cambiar columnas ni fórmulas.

## 3) Mejora de UI
- Campos obligatorios: `Desde Año/Mes`, `Hasta Año/Mes`, `Empresa`, `Sucursal`.
- **Meses desacoplados del año**: poblar siempre con catálogo fijo enero–diciembre; no limpiar al cambiar año, solo validar rango al procesar.【F:depreciacion.php†L65-L121】【F:depreciacion.php†L275-L322】
- **Bloqueo de meses sin `saemet`**: al presionar Procesar, pedir al backend una prevalidación que detecte meses sin valor y devuelva mensaje de error antes de grabar.
- **Dependencias correctas entre selects**: recargar activos cuando cambie empresa, sucursal, grupo o subgrupo; ambos selects “Desde/Hasta” deben usar el mismo dataset filtrado y ordenado.【F:depreciacion.php†L50-L121】【F:_Ajax.server.php†L23-L152】
- **Evitar combinaciones inválidas**: validar en frontend/backend que `(anio_desde, mes_desde) <= (anio_hasta, mes_hasta)` y que `activo_desde <= activo_hasta` cuando ambos existen.

## 4) Pseudocódigo del proceso masivo
```
for each activo filtrado:
    ultimo = max(saecdep.cdep_fec_depr where act = activo)
    fecha_inicio = max(ultimo + 1 mes, fecha_desde_ui)
    for fecha in meses(fecha_inicio .. fecha_hasta_ui):
        if not exists (saemet where metd_has_fech = fin_mes(fecha) and metd_cod_acti = activo):
            abortar("Falta saemet para fecha")
        gasto = saemet.metd_val_metd
        acumulado_prev = saecdep.cdep_dep_acum + cdep_gas_depn de fecha anterior (0 si no existe)
        acumulado = acumulado_prev + gasto
        delete saecdep where cdep_cod_acti = activo and cdep_fec_depr = fin_mes(fecha)
        insert saecdep(..., cdep_fec_depr = fin_mes(fecha), cdep_mes_depr = mes, cdep_ani_depr = anio,
                       cdep_dep_acum = acumulado, cdep_gas_depn = gasto, cdep_val_rep1 = acumulado_prev)
```

## 5) Reglas de reproceso
- Si ya existe `(activo, fecha_depr)` dentro del rango: **borrar y volver a insertar** (política actual) en la misma transacción.
- No permitir huecos: si falta `saemet` o el mes anterior requerido para continuidad, abortar todo el rango y reportar el primer mes faltante.
- Evitar duplicados: la clave lógica es `(cdep_cod_acti, cdep_fec_depr)`; mantener el `DELETE + INSERT` atómico para que solo quede un registro por mes.

## 6) Checklist de pruebas
- **Técnicas**
  - Procesar rango 1 mes con datos existentes → inserta un registro por activo y mes.
  - Procesar rango que incluye un mes ya generado → se borra y regenera sin duplicados.
  - Procesar rango con mes sin `saemet` → transacción abortada, sin inserciones parciales.
  - Procesar rango multi-mes con salto inicial (faltan meses previos) → arranca desde el siguiente a `ultimo_mes` y mantiene acumulado continuo.
- **Contables**
  - Verificar que `cdep_dep_acum` del mes N = `cdep_dep_acum` mes N-1 + `cdep_gas_depn` mes N.
  - Confirmar que no se generan meses con `cdep_gas_depn = 0` salvo que `saemet` tenga valor 0 explícito.
  - Validar que `cdep_val_rep1` almacena el acumulado previo para trazabilidad.
  - Revisar que no existan huecos en la secuencia mensual por activo tras procesar un rango.
