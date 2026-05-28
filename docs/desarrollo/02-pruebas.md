# 14 - Pruebas del Sistema

## Estrategia de Pruebas

Se aplicarán pruebas funcionales (caja negra) sobre los casos de uso definidos, complementadas con validación con usuarios del hospital.

## Pruebas Funcionales — Módulo de Documentación

| ID | Caso de Prueba | Entrada | Resultado Esperado |
|---|---|---|---|
| CP-01 | Subir documento válido | PDF < 10 MB, título, categoría | Documento guardado, QR generado, mensaje de éxito |
| CP-02 | Subir archivo inválido | Archivo .exe | Rechazar con mensaje: "Solo se permiten archivos PDF" |
| CP-03 | Subir archivo muy grande | PDF > 10 MB | Rechazar con mensaje: "El archivo excede el tamaño máximo" |
| CP-04 | Editar título de documento | Nuevo título válido | Título actualizado en BD, mensaje de éxito |
| CP-05 | Eliminar documento | Documento existente | Documento marcado como inactivo, QR deja de funcionar |
| CP-06 | Acceder a documento vía QR | QR válido escaneado | Documento mostrado en vista móvil |
| CP-07 | Acceder con QR inválido | QR falso o caducado | Mensaje: "Documento no disponible" |
| CP-08 | Crear encuesta | Título + preguntas | Encuesta guardada con estado activa |
| CP-09 | Responder encuesta | Respuestas válidas | Respuestas almacenadas, confirmación al paciente |
| CP-10 | Ver resultados de encuesta | Encuesta con respuestas | Estadísticas visibles (cantidad, promedios) |

## Pruebas Funcionales — Módulo de Ambulancias

| ID | Caso de Prueba | Entrada | Resultado Esperado |
|---|---|---|---|
| CP-11 | Registrar traslado | Datos completos y válidos | Traslado creado con estado "pendiente" |
| CP-12 | Registrar traslado sin conductor | Conductor vacío | Rechazar: "Conductor es requerido" |
| CP-13 | Actualizar estado En curso | Traslado en pendiente | Estado cambiado, hora de salida registrada |
| CP-14 | Actualizar estado cancelado | Traslado en cualquier estado | Estado cambiado a cancelado, motivo requerido |
| CP-15 | Consultar traslados activos | Sin filtros | Lista de traslados en curso y pendientes |
| CP-16 | Filtrar historial por fecha | Rango de fechas | Traslados en ese período |
| CP-17 | Cancelar traslado sin motivo | Estado cancelado sin motivo | Rechazar: "Motivo de cancelación es requerido" |

## Pruebas de Integración

| ID | Escenario | Verificación |
|---|---|---|
| CI-01 | Inicio de sesión con sistema centralizado | Redirección correcta al panel, token de sesión válido |
| CI-02 | Acceso público a documento sin autenticación | Documento visible sin login |
| CI-03 | Acceso a rutas administrativas sin sesión | Redirección al login |
| CI-04 | Generación de QR y acceso posterior | QR escaneable, lleva al documento correcto |

## Validación con Usuarios

Se realizarán sesiones de prueba con personal administrativo del Hospital de Clínicas para:
- Verificar que los flujos de trabajo se corresponden con la operativa real.
- Identificar problemas de usabilidad en la interfaz.
- Recoger retroalimentación para ajustes antes del despliegue final.

## Registro de Errores y Correcciones

| ID | Fecha | Error Detectado | Corrección Aplicada |
|---|---|---|---|
| — | — | — | — |

> *(Esta tabla se completará durante la fase de pruebas)*
