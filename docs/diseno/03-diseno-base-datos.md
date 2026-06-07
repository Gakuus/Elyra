# 12 - Diseño de Solución — Diseño de Base de Datos

## Modelo Relacional — Esquema Completo

A continuación se detallan las **11 tablas** del sistema, organizadas por módulo.

---

### Tabla de Identidad

**USUARIO** — almacena tanto a funcionarios (empleados con login) como a pacientes (acceso por QR). El campo `tipo` discrimina qué subtipo es.

```
USUARIO
├── id: INT (PK, AUTO_INCREMENT)
├── tipo: ENUM('funcionario', 'paciente') (NOT NULL)
├── nombre: VARCHAR(100) (NOT NULL)
├── apellido: VARCHAR(100) (NOT NULL)
├── email: VARCHAR(150) (UNIQUE)
├── username: VARCHAR(50) (UNIQUE)                   /* solo funcionario */
├── password_hash: VARCHAR(255)                      /* solo funcionario */
├── documento_identidad: VARCHAR(20) (UNIQUE)
├── licencia: VARCHAR(50)                            /* solo conductor */
├── telefono: VARCHAR(20)
├── token_acceso: VARCHAR(64) (UNIQUE)               /* solo paciente */
├── activo: BOOLEAN (DEFAULT TRUE)
├── rol: ENUM('admin', 'superadmin', 'conductor')    /* solo funcionario */
└── created_at: TIMESTAMP
```

---

### Módulo de Documentación (5 tablas)

**CATEGORIA** — clasificación de documentos por área médica

```
CATEGORIA
├── id: INT (PK, AUTO_INCREMENT)
├── nombre: VARCHAR(100) (UNIQUE, NOT NULL)
└── descripcion: TEXT
```

**ENCUESTA** — formularios de satisfacción

```
ENCUESTA
├── id: INT (PK, AUTO_INCREMENT)
├── titulo: VARCHAR(200) (NOT NULL)
├── descripcion: TEXT
├── activa: BOOLEAN (DEFAULT TRUE)
├── creada_por: INT (FK → USUARIO.id, NOT NULL)
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```

**PREGUNTA** — cada pregunta dentro de una encuesta

```
PREGUNTA
├── id: INT (PK, AUTO_INCREMENT)
├── encuesta_id: INT (FK → ENCUESTA.id, NOT NULL)
├── tipo: ENUM('multiple_choice', 'escala', 'texto_libre') (NOT NULL)
├── texto: VARCHAR(500) (NOT NULL)
├── opciones: JSON (NULLABLE)
├── requerida: BOOLEAN (DEFAULT TRUE)
├── orden: INT (NOT NULL)
└── created_at: TIMESTAMP
```

**DOCUMENTO** — archivos PDF informativos con código QR

```
DOCUMENTO
├── id: INT (PK, AUTO_INCREMENT)
├── titulo: VARCHAR(200) (NOT NULL)
├── descripcion: TEXT
├── archivo_path: VARCHAR(255) (NOT NULL)
├── archivo_nombre: VARCHAR(100) (NOT NULL)
├── qr_codigo: VARCHAR(64) (UNIQUE, NOT NULL)
├── qr_path: VARCHAR(255)
├── categoria_id: INT (FK → CATEGORIA.id, NOT NULL)
├── encuesta_id: INT (FK → ENCUESTA.id, NULLABLE, UNIQUE)
├── subido_por: INT (FK → USUARIO.id, NOT NULL)
├── activo: BOOLEAN (DEFAULT TRUE)
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```

**RESPUESTA** — respuestas de pacientes a encuestas, agrupadas por sesion_token

```
RESPUESTA
├── id: INT (PK, AUTO_INCREMENT)
├── sesion_token: VARCHAR(64) (NOT NULL)
├── encuesta_id: INT (FK → ENCUESTA.id, NOT NULL)
├── pregunta_id: INT (FK → PREGUNTA.id, NOT NULL)
├── token_paciente: VARCHAR(64)
├── valor_opcion: INT (NULLABLE)
├── valor_texto: TEXT (NULLABLE)
├── valor_numerico: INT (NULLABLE)
├── UNIQUE (sesion_token, pregunta_id)
└── created_at: TIMESTAMP
```

---

### Módulo de Ambulancias (5 tablas)

**VEHICULO** — flota de ambulancias

```
VEHICULO
├── id: INT (PK, AUTO_INCREMENT)
├── patente: VARCHAR(20) (UNIQUE, NOT NULL)
├── modelo: VARCHAR(100)
├── anio: YEAR
└── created_at: TIMESTAMP
```

**RUTA** — rutas predefinidas del circuito nacional

```
RUTA
├── id: INT (PK, AUTO_INCREMENT)
├── nombre: VARCHAR(150) (NOT NULL)
├── origen: VARCHAR(200) (NOT NULL)
├── destino: VARCHAR(200) (NOT NULL)
├── distancia_km: DECIMAL(10,2)
├── descripcion: TEXT
└── created_at: TIMESTAMP
```

**TRASLADO** — cada solicitud de viaje en ambulancia

```
TRASLADO
├── id: INT (PK, AUTO_INCREMENT)
├── codigo: VARCHAR(20) (UNIQUE, NOT NULL)
├── conductor_id: INT (FK → USUARIO.id, NOT NULL)
├── copiloto_id: INT (FK → USUARIO.id, NULLABLE)
├── vehiculo_id: INT (FK → VEHICULO.id, NULLABLE)
├── ruta_id: INT (FK → RUTA.id, NULLABLE)
├── origen: VARCHAR(200) (NOT NULL)
├── destino: VARCHAR(200) (NOT NULL)
├── hora_salida_estimada: DATETIME
├── hora_salida_efectiva: DATETIME
├── hora_llegada_destino: DATETIME
├── hora_inicio_retorno: DATETIME
├── hora_llegada_hospital: DATETIME
├── estado: ENUM('pendiente','en_curso','en_destino','en_retorno','completado','cancelado')
├── motivo_cancelacion: TEXT (NULL)
├── registrado_por: INT (FK → USUARIO.id, NOT NULL)
├── observaciones: TEXT
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```

**ELEMENTO_TRASLADO** — tabla polimórfica: qué se traslada en cada viaje. Una sola tabla reemplaza los 4 catálogos (órgano, equipo, insumo) y las 4 tablas puente de la versión anterior.

```
ELEMENTO_TRASLADO
├── id: INT (PK, AUTO_INCREMENT)
├── traslado_id: INT (FK → TRASLADO.id, NOT NULL)
├── tipo: ENUM('paciente', 'organo', 'equipamiento', 'insumo') (NOT NULL)
├── paciente_id: INT (FK → USUARIO.id, NULLABLE)     /* solo si tipo=paciente */
├── descripcion: VARCHAR(255)                         /* órgano, equipo o insumo */
├── cantidad: INT (DEFAULT 1)                         /* para equipos e insumos */
└── created_at: TIMESTAMP
```

**HISTORIAL_ESTADO** — bitácora de cambios de estado del traslado

```
HISTORIAL_ESTADO
├── id: INT (PK, AUTO_INCREMENT)
├── traslado_id: INT (FK → TRASLADO.id, NOT NULL)
├── estado_anterior: VARCHAR(20)
├── estado_nuevo: VARCHAR(20) (NOT NULL)
├── observacion: TEXT
├── actualizado_por: INT (FK → USUARIO.id, NOT NULL)
└── created_at: TIMESTAMP
```

---

## Índices

| Índice | Tabla | Columnas | ¿Para qué consulta? |
|---|---|---|---|
| idx_documento_categoria | DOCUMENTO | categoria_id | Filtrar documentos por categoría |
| idx_documento_activo | DOCUMENTO | activo | Mostrar solo documentos activos |
| idx_documento_qr | DOCUMENTO | qr_codigo | Buscar documento por QR (escaneo) |
| idx_encuesta_activa | ENCUESTA | activa | Mostrar encuestas disponibles |
| idx_encuesta_creada_por | ENCUESTA | creada_por | Filtrar encuestas por funcionario |
| idx_pregunta_encuesta | PREGUNTA | encuesta_id | Obtener preguntas de una encuesta |
| idx_respuesta_sesion | RESPUESTA | sesion_token | Agrupar respuestas de una entrega |
| idx_respuesta_pregunta | RESPUESTA | pregunta_id | Filtrar respuestas por pregunta |
| uk_respuesta_sesion_pregunta | RESPUESTA | (sesion_token, pregunta_id) | Evitar doble respuesta |
| idx_traslado_estado | TRASLADO | estado | Filtrar traslados por estado |
| idx_traslado_conductor | TRASLADO | conductor_id | Buscar traslados de un conductor |
| idx_traslado_vehiculo | TRASLADO | vehiculo_id | Historial de uso de un vehículo |
| idx_traslado_fecha | TRASLADO | created_at | Ordenar/filtrar por fecha |
| idx_traslado_codigo | TRASLADO | codigo | Búsqueda exacta por código |
| idx_elemento_traslado | ELEMENTO_TRASLADO | traslado_id | Qué lleva un traslado |
| idx_elemento_paciente | ELEMENTO_TRASLADO | paciente_id | Historial de viajes de un paciente |
| idx_historial_timeline | HISTORIAL_ESTADO | (traslado_id, created_at) | Línea de tiempo del viaje |

---

## Política de Borrado (CASCADE)

| Tabla | FK | Al borrar el padre... |
|---|---|---|
| ENCUESTA | creada_por → USUARIO | RESTRICT |
| PREGUNTA | encuesta_id → ENCUESTA | CASCADE |
| DOCUMENTO | categoria_id → CATEGORIA | RESTRICT |
| DOCUMENTO | encuesta_id → ENCUESTA | SET NULL |
| DOCUMENTO | subido_por → USUARIO | RESTRICT |
| RESPUESTA | encuesta_id → ENCUESTA | CASCADE |
| RESPUESTA | pregunta_id → PREGUNTA | CASCADE |
| TRASLADO | conductor_id → USUARIO | RESTRICT |
| TRASLADO | copiloto_id → USUARIO | SET NULL |
| TRASLADO | vehiculo_id → VEHICULO | SET NULL |
| TRASLADO | ruta_id → RUTA | SET NULL |
| TRASLADO | registrado_por → USUARIO | RESTRICT |
| ELEMENTO_TRASLADO | traslado_id → TRASLADO | CASCADE |
| ELEMENTO_TRASLADO | paciente_id → USUARIO | SET NULL |
| HISTORIAL_ESTADO | traslado_id → TRASLADO | CASCADE |
| HISTORIAL_ESTADO | actualizado_por → USUARIO | RESTRICT |

> **Regla general**: RESTRICT protege datos históricos (no se borra un conductor con viajes). CASCADE limpia datos hijos (si se borra un traslado, también sus elementos e historial). SET NULL conserva el registro aunque el padre se borre.

---

## DDL

El script SQL completo se encuentra en `database/schema.sql`. Contiene:

- 11 tablas con todos los campos, tipos y constraints
- Claves foráneas con las políticas de borrado definidas arriba
- 17 índices optimizados para las consultas más frecuentes
- Base de datos `elyra` con charset utf8mb4
