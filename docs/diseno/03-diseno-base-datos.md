# 12 - Diseño de la Solución — Diseño de Base de Datos

## Modelo Relacional (MR)

A continuación se presentan las 13 tablas del sistema con sus claves primarias (PK), foráneas (FK) y restricciones.

### Tablas Globales

```
USUARIO (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── username: VARCHAR(50) (UNIQUE, NOT NULL)
├── password_hash: VARCHAR(255) (NOT NULL)
├── nombre: VARCHAR(100) (NOT NULL)
├── apellido: VARCHAR(100) (NOT NULL)
├── email: VARCHAR(150) (UNIQUE)
├── rol: ENUM('admin', 'superadmin') (DEFAULT 'admin')
└── created_at: TIMESTAMP
```

### Tablas del Módulo de Documentación

```
CATEGORIA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── nombre: VARCHAR(100) (UNIQUE, NOT NULL)
└── descripcion: TEXT

DOCUMENTO (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── titulo: VARCHAR(200) (NOT NULL)
├── descripcion: TEXT
├── archivo_path: VARCHAR(255) (NOT NULL)
├── archivo_nombre: VARCHAR(100) (NOT NULL)
├── qr_codigo: VARCHAR(64) (UNIQUE, NOT NULL)
├── qr_path: VARCHAR(255)
├── categoria_id: INT (FK → CATEGORIA.id) (NOT NULL)
├── subido_por: INT (FK → USUARIO.id) (NOT NULL)
├── activo: BOOLEAN (DEFAULT TRUE)
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP

ENCUESTA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── titulo: VARCHAR(200) (NOT NULL)
├── descripcion: TEXT
├── activa: BOOLEAN (DEFAULT TRUE)
├── creada_por: INT (FK → USUARIO.id) (NOT NULL)
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP

PREGUNTA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── encuesta_id: INT (FK → ENCUESTA.id) (NOT NULL)
├── tipo: ENUM('multiple_choice', 'escala', 'texto_libre') (NOT NULL)
├── texto: VARCHAR(500) (NOT NULL)
├── requerida: BOOLEAN (DEFAULT TRUE)
├── orden: INT (NOT NULL)
└── created_at: TIMESTAMP

OPCION (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── pregunta_id: INT (FK → PREGUNTA.id) (NOT NULL)
├── texto: VARCHAR(255) (NOT NULL)
├── valor: INT
└── orden: INT (NOT NULL)

RESPUESTA_ENCUESTA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── encuesta_id: INT (FK → ENCUESTA.id) (NOT NULL)
├── token_paciente: VARCHAR(64)
├── completada: BOOLEAN (DEFAULT FALSE)
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP

RESPUESTA_PREGUNTA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── respuesta_encuesta_id: INT (FK → RESPUESTA_ENCUESTA.id) (NOT NULL)
├── pregunta_id: INT (FK → PREGUNTA.id) (NOT NULL)
├── opcion_id: INT (FK → OPCION.id) (NULL)
├── valor_texto: TEXT (NULL)
├── valor_numerico: INT (NULL)
└── created_at: TIMESTAMP

ENCUESTA_DOCUMENTO (PK: compuesta)
├── encuesta_id: INT (FK → ENCUESTA.id) (NOT NULL)
└── documento_id: INT (FK → DOCUMENTO.id) (NOT NULL)
```

### Tablas del Módulo de Ambulancias

```
CONDUCTOR (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── nombre: VARCHAR(100) (NOT NULL)
├── apellido: VARCHAR(100) (NOT NULL)
├── documento_identidad: VARCHAR(20) (UNIQUE)
├── licencia: VARCHAR(50)
├── telefono: VARCHAR(20)
├── activo: BOOLEAN (DEFAULT TRUE)
└── created_at: TIMESTAMP

RUTA (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── nombre: VARCHAR(150) (NOT NULL)
├── origen: VARCHAR(200) (NOT NULL)
├── destino: VARCHAR(200) (NOT NULL)
├── distancia_km: DECIMAL(10,2)
├── descripcion: TEXT
└── created_at: TIMESTAMP

TRASLADO (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── codigo: VARCHAR(20) (UNIQUE, NOT NULL)
├── conductor_id: INT (FK → CONDUCTOR.id) (NOT NULL)
├── copiloto_nombre: VARCHAR(150)
├── elemento_trasladado: VARCHAR(200) (NOT NULL)
├── tipo_elemento: ENUM('paciente','equipamiento','insumo') (NOT NULL)
├── origen: VARCHAR(200) (NOT NULL)
├── destino: VARCHAR(200) (NOT NULL)
├── ruta_id: INT (FK → RUTA.id) (NULL)
├── hora_salida_estimada: DATETIME
├── hora_salida_efectiva: DATETIME
├── hora_llegada_destino: DATETIME
├── hora_inicio_retorno: DATETIME
├── hora_llegada_hospital: DATETIME
├── estado: ENUM('pendiente','en_curso','en_destino','en_retorno','completado','cancelado') (DEFAULT 'pendiente')
├── motivo_cancelacion: TEXT (NULL)
├── registrado_por: INT (FK → USUARIO.id) (NOT NULL)
├── observaciones: TEXT
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP

HISTORIAL_ESTADO (PK: id)
├── id: INT (PK, AUTO_INCREMENT)
├── traslado_id: INT (FK → TRASLADO.id) (NOT NULL)
├── estado_anterior: VARCHAR(20)
├── estado_nuevo: VARCHAR(20) (NOT NULL)
├── observacion: TEXT
├── actualizado_por: INT (FK → USUARIO.id)
└── created_at: TIMESTAMP
```

## Diagrama de Relaciones

```
┌──────────┐     ┌──────────────┐     ┌──────────────┐
│ USUARIO  │     │  CATEGORIA   │     │  CONDUCTOR   │
├──────────┤     ├──────────────┤     ├──────────────┤
│ id (PK)  │1──<N│ id (PK)      │     │ id (PK)      │
└──────────┘     │ nombre       │1──<N│ nombre       │
       │         └──────────────┘     │ apellido     │
       │1               │             └──────────────┘
       │                │1                   │1
       ├────────────────┤                    │
       │                │                    │
       ▼                ▼                    ▼
┌──────────┐     ┌──────────────┐     ┌──────────────┐
│ ENCUESTA │     │  DOCUMENTO   │     │   TRASLADO   │
├──────────┤     ├──────────────┤     ├──────────────┤
│ id (PK)  │     │ id (PK)      │     │ id (PK)      │
└────┬─────┘     │ (FK)         │     │ (FK)         │
     │1          └──────────────┘     └──────┬───────┘
     │                M┼─────N               │1
     ▼                 │                     │
┌──────────┐     ┌──────────────┐            │
│ PREGUNTA │     │ ENCUESTA_DOC │            │
├──────────┤     ├──────────────┤            ▼
│ id (PK)  │     │ (PK compuesta)│    ┌──────────────┐
└────┬─────┘     └──────────────┘    │HISTORIAL_    │
     │1                              │   ESTADO     │
     ▼                               ├──────────────┤
┌──────────┐                         │ id (PK)      │
│  OPCION  │                         └──────────────┘
├──────────┤
│ id (PK)  │
└──────────┘

         N
         │
         ▼
┌──────────────────┐
│RESPUESTA_ENCUESTA│
├──────────────────┤
│ id (PK)          │1──<N
└──────────────────┘
         │1
         ▼
┌──────────────────┐
│RESPUESTA_PREGUNTA│
├──────────────────┤
│ id (PK)          │
└──────────────────┘

Además: RUTA (1)──<N TRASLADO
```

## DDL

El script SQL completo para crear la base de datos se encuentra en:

```
database/schema.sql
```

Incluye:
- Creación de la base de datos `elyra`
- 13 tablas con tipos, constraints, claves foráneas
- Índices para optimizar consultas frecuentes
- Engine InnoDB con charset utf8mb4
