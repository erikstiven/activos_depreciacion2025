# Análisis del Proceso de Cálculo de Depreciaciones

## Fase 1 — ¿Qué revisar y hallazgos

- **Tablas y relaciones**
  - La ejecución se basa en `saemet` (valor mensual calculado por activo y fecha), `saetdep` (tipos de depreciación y periodicidad) y `saeact/saesgac` (activos, grupos y subgrupos) como fuentes, e inserta en `saecdep` el resultado del cálculo.【F:_Ajax.server.php†L424-L473】【F:_Ajax.server.php†L491-L546】
  - La inserción en `saecdep` almacena claves de activo, empresa, sucursal, tipo de depreciación, mes, año y fecha de depreciación, junto con gasto del mes (`cdep_gas_depn`), acumulado (`cdep_dep_acum`) y un valor previo (`cdep_val_rep1`).【F:_Ajax.server.php†L540-L546】
- **Granularidad y cálculos**
  - Se genera un registro por **activo y mes** (derivado de `cdep_mes_depr`, `cdep_ani_depr` y `cdep_fec_depr`).【F:_Ajax.server.php†L540-L545】
  - El gasto mensual se toma directamente de `saemet.metd_val_metd` según la fecha de corte `metd_has_fech`. No hay recálculo en línea, solo lectura del valor ya generado en otra fase.【F:_Ajax.server.php†L438-L449】
  - El acumulado se calcula como el acumulado anterior (`cdep_dep_acum` + `cdep_gas_depn` del **mes previo**) más el gasto mensual actual; si no existe mes previo, el acumulado se inicializa igual al gasto mensual y el valor anterior se fija en cero.【F:_Ajax.server.php†L521-L533】
- **Control de ejecución**
  - Antes de insertar, si existe un registro para el mismo activo y fecha de depreciación se **elimina** y luego se vuelve a insertar; esto permite reprocesar pero no impide duplicados si cambian las claves (p. ej. si se modifica mes).【F:_Ajax.server.php†L491-L504】
  - No hay validación de continuidad de meses ni consulta del último mes procesado; el cálculo del acumulado depende de que exista el registro del mes anterior, de lo contrario arranca desde cero.【F:_Ajax.server.php†L395-L533】
- **Dependencias y filtros**
  - El proceso filtra por empresa, sucursal, grupo, subgrupo y rango de activos, pero no por estado contable ni cierres; solo requiere que el activo esté marcado como vigente (`saeact.act_ext_act = 1`).【F:_Ajax.server.php†L406-L489】
  - Depende de que `saemet` tenga el gasto mensual precalculado para la fecha solicitada; si falta, inserta gasto 0 sin alerta.【F:_Ajax.server.php†L438-L510】

## Fase 2 — Diagnóstico

- **Soporte para proceso masivo**: El diseño escribe un registro por activo/mes y recalcula acumulado a partir del registro previo. Puede iterar activos, pero **no** meses faltantes; el cálculo asume un único mes de entrada.
- **Riesgos de duplicación e inconsistencias**:
  - Reprocesar el mismo mes reemplaza el registro existente, pero ejecutar meses salteados reinicia el acumulado porque no encuentra el mes anterior, generando montos acumulados erróneos y huecos de trazabilidad.【F:_Ajax.server.php†L395-L533】
  - Si `saemet` no tiene valor para la fecha, el gasto se graba en cero, escondiendo faltantes de configuración.【F:_Ajax.server.php†L438-L510】
- **Naturaleza del problema**: Mezcla de **operativo** (el usuario debe recordar cada mes) y **estructural** (el modelo de acumulado depende de la presencia del mes anterior y no valida continuidad). No hay control automático del último mes generado.

## Fase 3 — Propuesta de mejora

### Opción recomendada: "Generar hasta fecha"

- **Idea**: Determinar el último mes registrado en `saecdep` por activo y recorrer internamente cada mes faltante hasta el mes/año solicitado, recalculando y registrando uno a uno para mantener trazabilidad mensual.
- **Cambios mínimos**
  - Agregar una consulta por activo al máximo `cdep_fec_depr` existente y derivar mes/año inicial del bucle.
  - Incorporar un bucle de meses que construya `fecha_hasta` y `fechaAnterior` en cada iteración, reutilizando la lógica actual de inserción (incluyendo recalculo de acumulado y borrado/insert).【F:_Ajax.server.php†L395-L546】
  - Validar existencia de valores en `saemet` por cada mes; si falta, abortar o alertar antes de grabar 0.
- **Impacto técnico**: Cambios localizados en la función `generar` sin modificar el esquema; requiere iterar meses y consultar `saemet`/`saecdep` varias veces, pero respeta la estructura actual y la trazabilidad por mes.
- **Impacto contable**: Mantiene asiento mensual por activo. Evita reinicios de acumulado al reconstruir secuencias faltantes; reduce riesgo de depreciaciones en cero por omisión.
- **Riesgos**: Mayor tiempo de ejecución al procesar varios meses y dependencia de que `saemet` tenga valores históricos completos; se debe definir política de reproceso (p. ej. permitir reemplazar meses ya generados dentro del rango).
- **Ventajas vs. proceso actual**: El usuario indica un destino (mes/año) y el sistema completa automáticamente los periodos intermedios, evitando huecos, manteniendo el acumulado correcto y sin romper la lógica de un registro mensual por activo.

### Alternativas

- **Proceso anual**: Ejecutar un bucle fijo de 12 meses leyendo valores de `saemet` existentes, útil para cierres o reprocesos completos; requiere las mismas validaciones de continuidad.
- **Job automático mensual**: Programar la misma función para correr cada mes; reduce errores operativos, pero sigue necesitando lógica de detección de último mes para reponerse ante fallas.

## Resultado

- Diagnóstico: El proceso actual es manual por mes, no valida continuidad y reinicia acumulados si falta un mes.
- Limitaciones: Dependencia de `saemet` sin alertas, ausencia de control de último mes y manejo de huecos al calcular acumulados.
- Propuesta: Implementar un proceso "generar hasta fecha" que recorra meses faltantes por activo reutilizando la inserción actual, con validaciones de valores faltantes y opcional reproceso controlado.
