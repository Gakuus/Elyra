# 11 - Modelado del Sistema — Modelo Entidad Relación (MER)

## Entidades y Atributos

### Módulo de Documentación para Pacientes

```
USUARIO
├── id (PK, INT, AUTO_INCREMENT)
├── username (VARCHAR(50), UNIQUE, NOT NULL)
├── password_hash (VARCHAR(255), NOT NULL)
├── nombre (VARCHAR(100), NOT NULL)
├── apellido (VARCHAR(100), NOT NULL)
├── email (VARCHAR(150), UNIQUE)
├── rol (ENUM('admin', 'superadmin'), DEFAULT 'admin')
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

CATEGORIA
├── id (PK, INT, AUTO_INCREMENT)
├── nombre (VARCHAR(100), NOT NULL, UNIQUE)
└── descripcion (TEXT)

DOCUMENTO
├── id (PK, INT, AUTO_INCREMENT)
├── titulo (VARCHAR(200), NOT NULL)
├── descripcion (TEXT)
├── archivo_path (VARCHAR(255), NOT NULL)
├── archivo_nombre (VARCHAR(100), NOT NULL)
├── qr_codigo (VARCHAR(64), UNIQUE, NOT NULL)
├── qr_path (VARCHAR(255))
├── categoria_id (FK → CATEGORIA.id)
├── subido_por (FK → USUARIO.id)
├── activo (BOOLEAN, DEFAULT TRUE)
├── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
└── updated_at (TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP)

ENCUESTA
├── id (PK, INT, AUTO_INCREMENT)
├── titulo (VARCHAR(200), NOT NULL)
├── descripcion (TEXT)
├── activa (BOOLEAN, DEFAULT TRUE)
├── creada_por (FK → USUARIO.id)
├── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
└── updated_at (TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP)

PREGUNTA
├── id (PK, INT, AUTO_INCREMENT)
├── encuesta_id (FK → ENCUESTA.id, NOT NULL)
├── tipo (ENUM('multiple_choice', 'escala', 'texto_libre'), NOT NULL)
├── texto (VARCHAR(500), NOT NULL)
├── requerida (BOOLEAN, DEFAULT TRUE)
├── orden (INT, NOT NULL)
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

OPCION
├── id (PK, INT, AUTO_INCREMENT)
├── pregunta_id (FK → PREGUNTA.id, NOT NULL)
├── texto (VARCHAR(255), NOT NULL)
├── valor (INT)
└── orden (INT, NOT NULL)

RESPUESTA_ENCUESTA
├── id (PK, INT, AUTO_INCREMENT)
├── encuesta_id (FK → ENCUESTA.id, NOT NULL)
├── token_paciente (VARCHAR(64))  /* identificador anónimo del paciente */
├── completada (BOOLEAN, DEFAULT FALSE)
├── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
└── updated_at (TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP)

RESPUESTA_PREGUNTA
├── id (PK, INT, AUTO_INCREMENT)
├── respuesta_encuesta_id (FK → RESPUESTA_ENCUESTA.id, NOT NULL)
├── pregunta_id (FK → PREGUNTA.id, NOT NULL)
├── opcion_id (FK → OPCION.id, NULLABLE)
├── valor_texto (TEXT, NULLABLE)        /* para texto_libre */
├── valor_numerico (INT, NULLABLE)      /* para escala */
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

ENCUESTA_DOCUMENTO
├── encuesta_id (FK → ENCUESTA.id, NOT NULL)
└── documento_id (FK → DOCUMENTO.id, NOT NULL)
    /* PK compuesta: (encuesta_id, documento_id) */
```

### Módulo de Trazabilidad de Ambulancias

```
CONDUCTOR
├── id (PK, INT, AUTO_INCREMENT)
├── nombre (VARCHAR(100), NOT NULL)
├── apellido (VARCHAR(100), NOT NULL)
├── documento_identidad (VARCHAR(20), UNIQUE)
├── licencia (VARCHAR(50))
├── telefono (VARCHAR(20))
├── activo (BOOLEAN, DEFAULT TRUE)
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

RUTA
├── id (PK, INT, AUTO_INCREMENT)
├── nombre (VARCHAR(150), NOT NULL)
├── origen (VARCHAR(200), NOT NULL)
├── destino (VARCHAR(200), NOT NULL)
├── distancia_km (DECIMAL(10,2))
├── descripcion (TEXT)
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

TRASLADO
├── id (PK, INT, AUTO_INCREMENT)
├── codigo (VARCHAR(20), UNIQUE, NOT NULL)     /* ej: TR-2026-0001 */
├── conductor_id (FK → CONDUCTOR.id, NOT NULL)
├── copiloto_nombre (VARCHAR(150))
├── elemento_trasladado (VARCHAR(200), NOT NULL)
├── tipo_elemento (ENUM('paciente', 'equipamiento', 'insumo'), NOT NULL)
├── origen (VARCHAR(200), NOT NULL)
├── destino (VARCHAR(200), NOT NULL)
├── ruta_id (FK → RUTA.id, NULLABLE)
├── hora_salida_estimada (DATETIME)
├── hora_salida_efectiva (DATETIME)
├── hora_llegada_destino (DATETIME)
├── hora_inicio_retorno (DATETIME)
├── hora_llegada_hospital (DATETIME)
├── estado (ENUM('pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado', 'cancelado'), DEFAULT 'pendiente')
├── motivo_cancelacion (TEXT, NULLABLE)
├── registrado_por (FK → USUARIO.id, NOT NULL)
├── observaciones (TEXT)
├── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
└── updated_at (TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP)

HISTORIAL_ESTADO
├── id (PK, INT, AUTO_INCREMENT)
├── traslado_id (FK → TRASLADO.id, NOT NULL)
├── estado_anterior (ENUM(...))
├── estado_nuevo (ENUM(...), NOT NULL)
├── observacion (TEXT)
├── actualizado_por (FK → USUARIO.id)
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

## Relaciones

### Módulo de Documentación

```
USUARIO (1) ────< (N) DOCUMENTO
  · Un usuario puede subir muchos documentos.
  · Cada documento es subido por un usuario.

CATEGORIA (1) ────< (N) DOCUMENTO
  · Una categoría puede agrupar muchos documentos.
  · Cada documento pertenece a una categoría.

USUARIO (1) ────< (N) ENCUESTA
  · Un usuario puede crear muchas encuestas.
  · Cada encuesta es creada por un usuario.

ENCUESTA (1) ────< (N) PREGUNTA
  · Una encuesta contiene muchas preguntas.
  · Cada pregunta pertenece a una encuesta.

PREGUNTA (1) ────< (N) OPCION
  · Una pregunta puede tener muchas opciones (multiple_choice).
  · Cada opción pertenece a una pregunta.

ENCUESTA (1) ────< (N) RESPUESTA_ENCUESTA
  · Una encuesta puede recibir muchas respuestas.
  · Cada respuesta pertenece a una encuesta.

RESPUESTA_ENCUESTA (1) ────< (N) RESPUESTA_PREGUNTA
  · Una respuesta de encuesta contiene respuestas a cada pregunta.
  · Cada respuesta de pregunta pertenece a una respuesta de encuesta.

PREGUNTA (1) ────< (N) RESPUESTA_PREGUNTA
  · Una pregunta puede ser respondida muchas veces.
  · Cada respuesta de pregunta corresponde a una pregunta.

OPCION (1) ────< (N) RESPUESTA_PREGUNTA
  · Una opción puede ser seleccionada en muchas respuestas.
  · Cada respuesta de pregunta puede apuntar a una opción (nullable).

ENCUESTA (N) ────< (M) DOCUMENTO
  · Muchas encuestas pueden asociarse a muchos documentos.
  · Muchos documentos pueden tener muchas encuestas asociadas.
  · Tabla puente: ENCUESTA_DOCUMENTO
```

### Módulo de Ambulancias

```
CONDUCTOR (1) ────< (N) TRASLADO
  · Un conductor puede realizar muchos traslados.
  · Cada traslado tiene un conductor responsable.

RUTA (1) ────< (N) TRASLADO
  · Una ruta puede ser usada en muchos traslados.
  · Cada traslado puede seguir una ruta.

USUARIO (1) ────< (N) TRASLADO
  · Un usuario puede registrar muchos traslados.
  · Cada traslado es registrado por un usuario.

TRASLADO (1) ────< (N) HISTORIAL_ESTADO
  · Un traslado tiene muchos cambios de estado.
  · Cada cambio de estado pertenece a un traslado.

USUARIO (1) ────< (N) HISTORIAL_ESTADO
  · Un usuario puede actualizar el estado de muchos traslados.
  · Cada cambio de estado es realizado por un usuario.
```

## Diagrama Entidad-Relación (Texto)

```
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│   USUARIO    │     │    CATEGORIA     │     │    CONDUCTOR     │
├──────────────┤     ├──────────────────┤     ├──────────────────┤
│ id (PK)      │1──<N│ id (PK)          │     │ id (PK)          │
│ username     │     │ nombre           │1──<N│ nombre           │
│ password_hash│     │ descripcion      │     │ apellido         │
│ nombre       │     └──────────────────┘     │ documento_id     │
│ apellido     │           │                  │ licencia         │
│ email        │           │                  │ telefono         │
│ rol          │           │                  │ activo           │
└──────┬───────┘           │                  └──────────────────┘
       │                   │                          │
       │1                  │1                         │1
       │                   │                          │
       │                   │                          │
       ├───────────────────┤                          │
       │                   │                          │
       ▼                   ▼                          ▼
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  ENCUESTA    │     │   DOCUMENTO      │     │    TRASLADO      │
├──────────────┤     ├──────────────────┤     ├──────────────────┤
│ id (PK)      │     │ id (PK)          │     │ id (PK)          │
│ titulo       │     │ titulo           │     │ codigo           │
│ descripcion  │     │ descripcion      │<────┤─ conductor_id(FK)│
│ activa       │     │ archivo_path     │     │ copiloto         │
│ creada_por   │     │ qr_codigo        │     │ elemento         │
└──────┬───────┘     │ qr_path          │     │ tipo_elemento    │
       │             │ categoria_id(FK) │     │ origen           │
       │1            │ subido_por(FK)   │     │ destino          │
       │             │ activo           │     │ ruta_id(FK)      │
       ▼             └──────────────────┘     │ estado           │
┌──────────────┐            │                 │ registrado_por   │
│  PREGUNTA    │            │                 └──────────────────┘
├──────────────┤            │                         │1
│ id (PK)      │            │                         │
│ encuesta_id  │            │                         ▼
│ tipo         │            │                 ┌──────────────────┐
│ texto        │            │                 │ HISTORIAL_ESTADO │
│ orden        │            │                 ├──────────────────┤
└──────┬───────┘            │                 │ id (PK)          │
       │1                   │                 │ traslado_id(FK)  │
       │                   N┼────M            │ estado_anterior  │
       ▼                   │                 │ estado_nuevo      │
┌──────────────┐     ┌──────────────┐        │ actualizado_por  │
│   OPCION     │     │ ENCUESTA_DOC │        └──────────────────┘
├──────────────┤     ├──────────────┤
│ id (PK)      │     │ encuesta_id  │
│ pregunta_id  │     │ documento_id │
│ texto        │     └──────────────┘
│ valor        │
│ orden        │            N
│              │            │
└──────────────┘            ▼
                    ┌──────────────────┐
                    │RESPUESTA_ENCUESTA│
                    ├──────────────────┤
                    │ id (PK)          │1──<N
                    │ encuesta_id(FK)  │
                    │ token_paciente   │
                    └──────────────────┘
                           │1
                           │
                           ▼
                    ┌──────────────────┐
                    │RESPUESTA_PREGUNTA│
                    ├──────────────────┤
                    │ id (PK)          │
                    │ respuesta_id(FK) │
                    │ pregunta_id(FK)  │
                    │ opcion_id(FK)    │
                    │ valor_texto      │
                    │ valor_numerico   │
                    └──────────────────┘

También existe RUTA
┌──────────────┐
│    RUTA      │
├──────────────┤
│ id (PK)      │1──<N TRASLADO.ruta_id
│ nombre       │
│ origen       │
│ destino      │
│ distancia_km │
│ descripcion  │
└──────────────┘
```

## Resumen de Tablas

| # | Tabla | Módulo | Propósito |
|---|---|---|---|
| 1 | USUARIO | Global | Autenticación y registro de acciones |
| 2 | CATEGORIA | Documentación | Clasificación de documentos |
| 3 | DOCUMENTO | Documentación | Archivos PDF informativos |
| 4 | ENCUESTA | Documentación | Formularios de satisfacción |
| 5 | PREGUNTA | Documentación | Preguntas de cada encuesta |
| 6 | OPCION | Documentación | Opciones de preguntas multi-choice |
| 7 | RESPUESTA_ENCUESTA | Documentación | Respuestas enviadas por pacientes |
| 8 | RESPUESTA_PREGUNTA | Documentación | Valor de cada respuesta individual |
| 9 | ENCUESTA_DOCUMENTO | Documentación | Asociación encuesta-documento |
| 10 | CONDUCTOR | Ambulancias | Conductores de ambulancia |
| 11 | RUTA | Ambulancias | Rutas del circuito nacional |
| 12 | TRASLADO | Ambulancias | Solicitudes de traslado |
| 13 | HISTORIAL_ESTADO | Ambulancias | Trazabilidad de cambios de estado |
