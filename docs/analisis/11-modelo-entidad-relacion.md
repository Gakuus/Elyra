# 11 - Modelo Entidad Relación (MER)

## ¿Qué es este modelo?

Este documento describe todas las tablas de la base de datos del Sistema Elyra para el Hospital de Clínicas. El sistema maneja **dos servicios**:

1. **Documentación digital para pacientes**: un funcionario sube documentos PDF, se genera un código QR, el paciente escanea y lee desde su celular. También incluye encuestas de satisfacción.
2. **Trazabilidad de ambulancias**: se registra cada viaje de ambulancia: quién conduce, qué se traslada (paciente, órgano, equipo o insumo), la ruta, y se sigue el estado paso a paso.

El diseño usa **11 tablas** (contra 17 de la versión anterior) porque simplificamos el modelo de carga: en vez de crear una tabla por cada tipo de elemento trasladado, usamos una sola tabla `ELEMENTO_TRASLADO` con un campo `tipo` que discrimina qué es (paciente, órgano, equipo o insumo).

---

## Las entidades explicadas en orden lógico

### 1. ¿Quiénes usan el sistema? → USUARIO

El hospital tiene dos tipos de personas, ambos guardados en la misma tabla `USUARIO`:

| Tipo | ¿Qué hace? | Campos que usa |
|---|---|---|
| **FUNCIONARIO** | Entra con usuario y contraseña. Puede ser admin, superadmin o conductor. Tiene licencia de conducir. | username, password_hash, licencia, telefono, rol |
| **PACIENTE** | No necesita login. Accede a documentos por QR. Se registra cuando viaja en ambulancia. | token_acceso |

---

### 2. Módulo de Documentación — paso a paso

**Paso 1:** Un funcionario crea categorías médicas
```
CATEGORIA → "Cardiología", "Nefrología", etc.
```

**Paso 2:** El funcionario sube un PDF y lo categoriza
```
DOCUMENTO → título + archivo + QR único + categoría
```

**Paso 3:** El paciente escanea el QR desde su celular y lee el documento (sin login).

**Paso 4 (opcional):** El funcionario crea una encuesta con preguntas y la asocia al documento.
```
ENCUESTA → contiene PREGUNTAS (múltiple opción, escala o texto libre)
```

**Paso 5:** El paciente responde la encuesta desde su celular.
```
RESPUESTA → una por pregunta, agrupadas por sesion_token
```

---

### 3. Módulo de Ambulancias — paso a paso

**Paso 1:** Se registra un traslado con quién maneja, qué ambulancia, la ruta y los tiempos.
```
TRASLADO → conductor, copiloto, vehículo, ruta, origen, destino, estado
```

**Paso 2:** Se cargan los elementos que lleva el traslado en una sola tabla:

| tipo | ¿Qué se traslada? | paciente_id | descripcion | cantidad |
|---|---|---|---|---|
| paciente | Una persona | ✓ (FK a USUARIO) | — | 1 |
| organo | Un órgano para trasplante | — | "Riñón derecho" | 1 |
| equipamiento | Un equipo médico | — | "Respirador portátil" | 2 |
| insumo | Insumos varios | — | "Suero fisiológico" | 10 |

Una sola tabla `ELEMENTO_TRASLADO` reemplaza lo que antes eran 7 tablas (ORGANO, EQUIPAMIENTO, INSUMO + 4 tablas puente).

**Paso 3:** El funcionario va actualizando el estado del viaje y cada cambio queda en `HISTORIAL_ESTADO`:
```
PENDIENTE → EN CURSO → EN DESTINO → EN RETORNO → COMPLETADO
                             \
                              → CANCELADO
```

---

## Listado completo de entidades

### Tabla de Identidad

```
USUARIO
├── id: INT (PK)
├── tipo: ENUM('funcionario', 'paciente')       ← discrimina subtipo
├── nombre, apellido, email
├── username, password_hash                     ← solo funcionario (login)
├── documento_identidad, licencia, telefono
├── token_acceso                                ← solo paciente (QR)
├── activo: BOOLEAN
├── rol: ENUM('admin', 'superadmin', 'conductor')  ← solo funcionario
└── created_at
```

### Módulo de Documentación (5 tablas)

```
CATEGORIA
├── id (PK), nombre (UNIQUE), descripcion

ENCUESTA
├── id (PK), titulo, descripcion, activa
├── creada_por: FK → USUARIO
└── timestamps

PREGUNTA
├── id (PK), encuesta_id (FK), tipo, texto, opciones (JSON), orden

DOCUMENTO
├── id (PK), titulo, descripcion
├── archivo_path, archivo_nombre, qr_codigo (UNIQUE), qr_path
├── categoria_id (FK), encuesta_id (FK, NULL, UNIQUE), subido_por (FK)
├── activo
└── timestamps

RESPUESTA
├── id (PK), sesion_token, encuesta_id (FK), pregunta_id (FK)
├── token_paciente, valor_opcion, valor_texto, valor_numerico
├── UNIQUE (sesion_token, pregunta_id)          ← evita responder dos veces
└── created_at
```

### Módulo de Ambulancias (5 tablas)

```
VEHICULO
├── id (PK), patente (UNIQUE), modelo, anio

RUTA
├── id (PK), nombre, origen, destino, distancia_km, descripcion

TRASLADO
├── id (PK), codigo (UNIQUE)
├── conductor_id (FK), copiloto_id (FK), vehiculo_id (FK), ruta_id (FK)
├── origen, destino
├── hora_salida_estimada, hora_salida_efectiva
├── hora_llegada_destino, hora_inicio_retorno, hora_llegada_hospital
├── estado: pendiente/en_curso/en_destino/en_retorno/completado/cancelado
├── motivo_cancelacion, registrado_por (FK), observaciones
└── timestamps

ELEMENTO_TRASLADO                              ← reemplaza 7 tablas viejas
├── id (PK), traslado_id (FK)
├── tipo: paciente / organo / equipamiento / insumo
├── paciente_id (FK → USUARIO, NULL)           ← solo cuando tipo=paciente
├── descripcion                                ← para órganos, equipos, insumos
├── cantidad (DEFAULT 1)
└── created_at

HISTORIAL_ESTADO
├── id (PK), traslado_id (FK)
├── estado_anterior, estado_nuevo
├── actualizado_por (FK → USUARIO)
└── created_at
```

---

## Resumen rápido (11 tablas)

| # | Tabla | Módulo | ¿Qué guarda? |
|---|---|---|---|
| 1 | USUARIO | Identidad | Funcionarios (con login) y pacientes (con token QR) |
| 2 | CATEGORIA | Documentación | Clasificación de documentos por área médica |
| 3 | ENCUESTA | Documentación | Formularios de satisfacción |
| 4 | PREGUNTA | Documentación | Cada pregunta de una encuesta |
| 5 | DOCUMENTO | Documentación | PDF informativos con su código QR |
| 6 | RESPUESTA | Documentación | Respuestas de pacientes agrupadas por sesión |
| 7 | VEHICULO | Ambulancias | Flota de ambulancias |
| 8 | RUTA | Ambulancias | Rutas del circuito nacional |
| 9 | TRASLADO | Ambulancias | Solicitudes de viaje en ambulancia |
| 10 | ELEMENTO_TRASLADO | Ambulancias | Qué se traslada (paciente, órgano, equipo o insumo) |
| 11 | HISTORIAL_ESTADO | Ambulancias | Bitácora de cambios de estado del viaje |

---

## Relaciones entre entidades

### Módulo de Documentación

```
CATEGORIA (1) ──── (N) DOCUMENTO
  · Una categoría agrupa varios documentos.

FUNCIONARIO (1) ──── (N) DOCUMENTO
  · Un funcionario sube muchos documentos.

FUNCIONARIO (1) ──── (N) ENCUESTA
  · Un funcionario crea muchas encuestas.

ENCUESTA (1) ──── (N) PREGUNTA
  · Una encuesta tiene varias preguntas.

ENCUESTA (0..1) ──── (0..1) DOCUMENTO
  · Una encuesta puede evaluar un documento (relación opcional en ambos lados).

ENCUESTA (1) ──── (N) RESPUESTA
  · Una encuesta recibe respuestas de pacientes.

PREGUNTA (1) ──── (N) RESPUESTA
  · Una pregunta se responde muchas veces.

PACIENTE (1) ──── (N) RESPUESTA
  · Un paciente (por su token) completa muchas respuestas.
```

### Módulo de Ambulancias

```
FUNCIONARIO (1) ──── (N) TRASLADO
  · Un funcionario puede: registrar, conducir o copilotear muchos traslados.

VEHICULO (1) ──── (N) TRASLADO
  · Una ambulancia se usa en muchos traslados.

RUTA (1) ──── (N) TRASLADO
  · Una ruta se referencia en muchos traslados.

TRASLADO (1) ──── (N) ELEMENTO_TRASLADO
  · Un traslado puede llevar varios elementos (pacientes, órganos, equipos, insumos).

PACIENTE (1) ──── (N) ELEMENTO_TRASLADO
  · Un paciente puede viajar en varios traslados (cuando tipo='paciente').

TRASLADO (1) ──── (N) HISTORIAL_ESTADO
  · Un traslado tiene varios cambios de estado a lo largo de su viaje.

FUNCIONARIO (1) ──── (N) HISTORIAL_ESTADO
  · Un funcionario actualiza el estado de muchos traslados.
```
