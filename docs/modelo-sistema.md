# Modelado del Sistema Elyra

> **Sistema de Gestión Hospitalaria — Hospital de Clínicas**
> **Metodología:** Modelo Esencial, Ambiental y de Comportamiento + UML
> **Versión:** 1.0 — Julio 2026

---

## Índice

1. [Modelo Esencial](#1-modelo-esencial)
2. [Modelo Ambiental](#2-modelo-ambiental)
3. [Modelo de Comportamiento](#3-modelo-de-comportamiento)
4. [Listas de Acontecimientos](#4-listas-de-acontecimientos)
5. [Diagramas UML](#5-diagramas-uml)

---

# 1. Modelo Esencial

> *¿Qué es el sistema? ¿Qué entidades existen? ¿Qué reglas de negocio rigen?*

## 1.1 Descripción Esencial del Sistema

**Elyra** es un sistema de gestión hospitalaria que administra:

- **Traslados de pacientes** entre hospitales con tracking GPS en tiempo real
- **Documentos médicos** con generación automática de códigos QR
- **Encuestas de satisfacción** para pacientes
- **Directorio de funcionarios** y conductores de ambulancias
- **Noticias internas** del hospital

**Frontera del sistema:** Elyra NO gestiona historial clínico, recetas médicas, facturación ni turnos. Se limita a trazabilidad documental, traslados y encuestas.

## 1.2 Modelo de Dominio (Entidades)

### Diagrama de Clases Simplificado

```
                        ┌──────────────┐
                        │   Usuario    │
                        │──────────────│
                        │ id: int      │
                        │ tipo: enum   │
                        │ nombre: str  │
                        │ apellido: str│
                        │ email: Email │
                        │ documento: str│
                        │ foto: blob   │
                        │ createdAt    │
                        └──────┬───────┘
                               │
                  ┌────────────┴────────────┐
                  │                         │
           ┌──────▼───────┐         ┌───────▼──────┐
           │ Funcionario  │         │   Paciente   │
           │──────────────│         │──────────────│
           │ username     │         │ tokenAcceso  │
           │ passwordHash │         │ username     │
           │ rol: Rol     │         │ passwordHash │
           │ licencia     │         │ telefono     │
           │ licConducir  │         │ activo       │
           │ telefono     │         └──────────────┘
           │ activo       │
           │ resetToken   │
           └──────────────┘
```

### Entidades del Dominio

| Entidad | Atributos Clave | Reglas de Negocio |
|---------|----------------|-------------------|
| **Usuario** | id, tipo, nombre, apellido, email, documentoIdentidad, foto | Base de herencia (STI). Email único. CI única. |
| **Funcionario** | username, passwordHash, rol, licencia, licenciaConducir, activo | Extiende Usuario. Rol controla permisos. bcrypt cost 12. Baja lógica (activo=false). |
| **Paciente** | tokenAcceso, username, passwordHash, telefono, activo | Extiende Usuario. Token UUID v4 para acceso público QR. |
| **Traslado** | codigo, conductorId, copilotoId, vehiculoId, rutaId, origen, destino, coordenadas, estado, horas | Máquina de estados estricta. Código único (TR-XXX). Origen ≠ destino. |
| **ElementoTraslado** | trasladoId, tipo, pacienteId, descripcion, cantidad | Un traslado tiene 1 elemento. Tipo: paciente/organo/equipamiento/insumo. |
| **HistorialEstado** | trasladoId, estadoAnterior, estadoNuevo, observacion, actualizadoPor | Inmutable. Se preserva al borrar traslado (SET NULL). |
| **Documento** | titulo, archivoPath, archivoNombre, categoriaId, pacienteId, subidoPor, codigoQrId | Solo PDF. Máx 10MB. QR generado automáticamente. |
| **Categoria** | nombre, tipo | Tipo: especialidad o tipo_documento. Nombre único. |
| **Encuesta** | titulo, descripcion, activa, creadaPor | Solo admin crea. Pacientes responden. |
| **Pregunta** | encuestaId, tipo, texto, opciones, requerida, orden | Tipo: multiple_choice/escala/texto_libre. Opciones en JSON. |
| **Respuesta** | sesionToken, encuestaId, preguntaId, tokenPaciente | Unique por (sesion_token, pregunta_id). Anónima si es público. |
| **Ruta** | nombre, origen, destino, distancia_km | Origen y destino son strings (direcciones). Distancia en km. |
| **Vehiculo** | patente, modelo, anio | Patente única. |
| **UbicacionConductor** | conductorId, trasladoId, latitud, longitud, heading, velocidad | Un upsert por conductor (una fila). |
| **HistorialUbicacion** | conductorId, trasladoId, latitud, longitud | Append-only. Nunca se borra. |
| **Noticia** | titulo, contenido, imagen, autorId, activo | Imagen opcional (JPG/PNG/WebP/GIF, máx 5MB). |
| **CodigoQR** | nombre, descripcion | Generado por API externa + fallback GD. |
| **CatalogoElemento** | tipo, nombre, descripcion, activo | Tipo: insumo/equipamiento/organo. Semilla: 37 elementos. |

### Value Objects

| Value Object | Atributos | Validación |
|-------------|-----------|------------|
| **Email** | value: string | `filter_var(FILTER_VALIDATE_EMAIL)` |
| **EstadoTraslado** | value: string | Enum de 6 valores. FSM con transiciones válidas. |
| **RolUsuario** | value: string | Enum de 9 roles. `esAdmin()`, `esConductor()`, `esCopiloto()`. |
| **Coordenada** | latitud: float, longitud: float | Lat: -90/+90. Lng: -180/+180. Precisión 7 decimales. |
| **TipoElemento** | value: string | Enum: paciente, organo, equipamiento, insumo. |
| **TipoPregunta** | value: string | Enum: multiple_choice, escala, texto_libre. |
| **CategoriaLicenciaConducir** | value: string | Enum: B1, B2, C1, C2, D1, D2. |
| **LicenciaProfesional** | value: string | 29 licencias válidas en Uruguay. |
| **CodigoQR** | value: string | UUID v4 o hash único. |

## 1.3 Invariants del Negocio

| # | Invariante | Ubicación |
|---|-----------|-----------|
| I1 | Origen ≠ Destino en un traslado | `Traslado::__construct()` |
| I2 | Estado cancelado requiere motivo obligatorio | `Traslado::actualizarEstado()` |
| I3 | Transiciones de estado solo hacia adelante (FSM) | `EstadoTraslado::puedeTransicionarA()` |
| I4 | Contraseña mínimo 8 caracteres | `AuthController::doRegistro()` |
| I5 | Documento solo puede ser PDF | `FileStorageService` |
| I6 | Tamaño máximo de archivo: 10MB | `FileStorageService` |
| I7 | Tamaño máximo de imagen noticia: 5MB | `NoticiaController` |
| I8 | Token de reset expira en 1 hora | `SolicitarResetPasswordUseCase` |
| I9 | Un solo GPS activo por conductor (upsert) | `UbicacionConductorRepository::upsert()` |
| I10 | Respuesta única por (sesion_token, pregunta) | `respuesta` UNIQUE KEY |
| I11 | Solo pacientes con cuenta activa pueden iniciar sesión | `AuthService::login()` |
| I12 | Auditoría inmutable: sin UPDATE/DELETE en audit_log | `AuditLogger` (aplicación) |
| I13 | historial_estado preservado al borrar traslado | `TrasladoRepository::delete()` → UPDATE SET NULL |
| I14 | 5 intentos fallidos → bloqueo 15 minutos | `AuthService` + `RateLimiter` |
| I15 | Sesión vinculada a User-Agent | `SessionManager::checkTimeout()` |

## 1.4 Diagrama de Relaciones ER

```
usuario (1)────(0..1) funcionario
usuario (1)────(0..1) paciente
usuario (1)────(0..N) documento        [como subido_por]
usuario (1)────(0..N) noticia          [como autor_id]

funcionario (1)────(0..N) traslado     [como conductor_id]
funcionario (1)────(0..N) traslado     [como copiloto_id]
funcionario (1)────(0..N) traslado     [como registrado_por]
funcionario (1)────(0..N) encuesta     [como creada_por]
funcionario (1)────(0..N) historial_estado [como actualizado_por]

vehiculo (1)────(0..N) traslado
ruta (1)────(0..N) traslado

traslado (1)────(1) elemento_traslado
traslado (1)────(0..N) historial_estado
traslado (1)────(0..1) ubicacion_conductor
traslado (1)────(0..N) historial_ubicacion

paciente (1)────(0..N) documento
paciente (1)────(0..N) elemento_traslado

categoria (1)────(0..N) documento
codigo_qr (1)────(0..1) documento
codigo_qr (1)────(0..1) paciente

encuesta (1)────(0..N) pregunta
encuesta (1)────(0..N) respuesta
pregunta (1)────(0..N) respuesta
```

---

# 2. Modelo Ambiental

> *¿Qué rodea al sistema? ¿Quiénes interactúan? ¿Qué dependencias externas tiene?*

## 2.1 Actores del Sistema

| Actor | Tipo | Descripción | Permisos Principales |
|-------|------|-------------|---------------------|
| **Super Admin** | Primario | Acceso total al sistema | CRUD total, configuración, auditoría |
| **Admin** | Primario | Gestión administrativa | CRUD documentos, traslados, usuarios, encuestas |
| **Conductor** | Primario | Conductor de ambulancia | Ver/actualizar traslados asignados, GPS tracking |
| **Copiloto** | Primario | Copiloto de ambulancia | Ver traslados asignados, GPS tracking |
| **Médico** | Primario | Personal médico | Dashboard, perfil |
| **Enfermero** | Primario | Personal de enfermería | Dashboard, perfil |
| **Técnico** | Primario | Personal técnico | Dashboard, perfil |
| **Recepcionista** | Primario | Personal de recepción | Dashboard, perfil |
| **Farmacéutico** | Primario | Personal de farmacia | Dashboard, perfil |
| **Paciente** | Primario | Paciente del hospital | Documentos propios, encuestas, perfil |
| **Visitante** | Primario | Persona sin cuenta | Página pública, QR de documentos, encuestas públicas |
| **GPS (navegador)** | Secundario | API de geolocalización | Proporciona coordenadas al conductor |
| **OSRM** | Secundario | API de rutas | Calcula rutas reales por calles |
| **QR API** | Secundario | api.qrserver.com | Genera códigos QR |
| **Gmail SMTP** | Secundario | PHPMailer + Gmail | Envío de emails (reset password) |
| **MySQL** | Secundario | Base de datos | Persistencia |

## 2.2 Diagrama de Contexto

```
┌─────────────────────────────────────────────────────────────────┐
│                        SISTEMA ELYRA                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │ Documentos│ │Traslados │ │Encuestas │ │ Noticias │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
└────────┬───────────┬───────────┬───────────┬────────────────────┘
         │           │           │           │
    ┌────▼────┐ ┌────▼────┐ ┌────▼────┐ ┌────▼────┐
    │  Admin  │ │Conductor│ │Paciente │ │Visitante│
    │  Staff  │ │ Copiloto│ │         │ │  (QR)   │
    └─────────┘ └─────────┘ └─────────┘ └─────────┘

    Servicios Externos:
    ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
    │  MySQL   │ │  OSRM    │ │ Gmail    │ │ QR API   │
    │  8.0+    │ │ Routing  │ │  SMTP    │ │ Ext.     │
    └──────────┘ └──────────┘ └──────────┘ └──────────┘

    Dispositivos:
    ┌──────────┐ ┌──────────┐
    │GPS/SSE   │ │Navegador │
    │(Celular) │ │(Desktop) │
    └──────────┘ └──────────┘
```

## 2.3 Hardware y Software

| Componente | Especificación |
|------------|---------------|
| **Servidor web** | PHP 8.5+ built-in server (desarrollo) / Apache o Nginx (producción) |
| **Base de datos** | MySQL 8.0+ |
| **SO del servidor** | Linux (Ubuntu/Debian) |
| **Navegador del cliente** | Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ |
| **Celular del conductor** | iOS 14+ o Android 10+ con GPS |
| **Dependencias PHP** | PHPMailer, PDO MySQL, GD, mbstring, fileinfo |
| **Dependencias JS** | Bootstrap 5, Leaflet.js, QRCode.js |
| **CDN** | Bootstrap CSS/JS, Bootstrap Icons, Leaflet, QRCode.js |

## 2.4 Interfaces Externas

| Interfaz | Protocolo | Propósito |
|----------|-----------|-----------|
| **MySQL** | TCP/IP o Unix Socket | Persistencia de datos |
| **OSRM API** | HTTPS GET | Cálculo de rutas reales |
| **QR Server API** | HTTPS GET | Generación de códigos QR |
| **Gmail SMTP** | SMTP/TLS | Envío de emails |
| **Browser Geolocation** | JavaScript API | Obtención de GPS |
| **Server-Sent Events** | HTTP (text/event-stream) | Difusión GPS en tiempo real |

## 2.5 Restricciones del Entorno

| Restricción | Descripción |
|-------------|-------------|
| **Conectividad** | El sistema requiere conexión a internet (mapas, rutas, GPS) |
| **GPS** | El tracking requiere GPS habilitado en el dispositivo |
| **Almacenamiento** | PDFs e imágenes se guardan en disco (no en DB) |
| **SMTP** | El reset de contraseña requiere servicio de email activo |
| **CORS** | API GPS solo acepta requests del mismo dominio |
| **Sesiones** | PHP file-based sessions (no Redis ni Memcached) |

---

# 3. Modelo de Comportamiento

> *¿Cómo se comporta el sistema? ¿Qué hace cada caso de uso?*

## 3.1 Diagrama de Casos de Uso

### Módulo de Autenticación

```
┌─────────────────────────────────────────────────────────┐
│                    SISTEMA ELYRA                         │
│                                                          │
│  ┌─────────────────┐                                    │
│  │   Iniciar Sesión │◄──── [Visitante]                  │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Cerrar Sesión    │◄──── [Cualquier usuario]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Registrarse      │◄──── [Paciente]                   │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Recuperar Pass   │◄──── [Cualquier usuario]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Crear Funcionario│◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Editar Func.     │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Desactivar Func. │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
└─────────────────────────────────────────────────────────┘
```

### Módulo de Documentos

```
┌─────────────────────────────────────────────────────────┐
│                    SISTEMA ELYRA                         │
│                                                          │
│  ┌─────────────────┐                                    │
│  │ Subir Documento  │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘         «include» Generar QR       │
│  ┌─────────────────┐                                    │
│  │ Editar Documento │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Eliminar Doc.    │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Ver Documento    │◄──── [Admin, SuperAdmin, Paciente]│
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Descargar PDF    │◄──── [Admin, SuperAdmin, Paciente]│
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Ver Doc. por QR  │◄──── [Visitante]                  │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Ver Mis Docs     │◄──── [Paciente]                   │
│  └─────────────────┘         «include» Verificar Token  │
└─────────────────────────────────────────────────────────┘
```

### Módulo de Traslados

```
┌─────────────────────────────────────────────────────────┐
│                    SISTEMA ELYRA                         │
│                                                          │
│  ┌─────────────────┐                                    │
│  │ Crear Traslado   │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Actualizar Estado│◄──── [Admin, SuperAdmin, Conductor]│
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Ver Detalle      │◄──── [Admin, SuperAdmin, Conductor]│
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Historial        │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Mapa en Vivo     │◄──── [Admin, SuperAdmin, Conductor]│
│  └─────────────────┘         «include» Ver GPS          │
│  ┌─────────────────┐                                    │
│  │ Tracking GPS     │◄──── [Conductor, Copiloto]        │
│  └─────────────────┘         «include» Enviar Ubicación │
└─────────────────────────────────────────────────────────┘
```

### Módulo de Encuestas

```
┌─────────────────────────────────────────────────────────┐
│                    SISTEMA ELYRA                         │
│                                                          │
│  ┌─────────────────┐                                    │
│  │ Crear Encuesta   │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Ver Resultados   │◄──── [Admin, SuperAdmin]          │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Responder Enc.   │◄──── [Paciente]                   │
│  └─────────────────┘                                    │
│  ┌─────────────────┐                                    │
│  │ Responder (Público)│◄──── [Visitante]                │
│  └─────────────────┘         «include» Verificar IP     │
└─────────────────────────────────────────────────────────┘
```

## 3.2 Diagrama de Actividad — Flujo de Registro de Traslado

```mermaid
flowchart TD
    A[Admin selecciona Nuevo Traslado] --> B[Completa formulario]
    B --> C{¿Tipo de elemento?}
    C -->|Paciente| D[Selecciona paciente de lista]
    C -->|Órgano| E[Selecciona órgano del catálogo]
    C -->|Equipamiento| F[Selecciona equipo del catálogo]
    C -->|Insumo| G[Selecciona insumo del catálogo]
    D --> H[Selecciona conductor]
    E --> H
    F --> H
    G --> H
    H --> I[Selecciona copiloto opcional]
    I --> J[Selecciona origen y destino]
    J --> K{Origen ≠ Destino?}
    K -->|No| L[Error: mostrar mensaje]
    L --> B
    K -->|Sí| M[Selecciona ruta]
    M --> N[Selecciona fecha/hora salida]
    N --> O[Agregar observaciones]
    O --> P[Crear TrasladoUseCase]
    P --> Q[Validar conductor activo]
    Q --> R[Crear entidad Traslado]
    R --> S[Generar código TR-XXX]
    S --> T[Crear ElementoTraslado]
    T --> U[Guardar en transacción]
    U --> V[Redirigir a lista de traslados]
```

## 3.3 Diagrama de Actividad — Flujo de Tracking GPS

```mermaid
flowchart TD
    A[Conductor abre Tracking] --> B[Browser pide permiso GPS]
    B --> C{Permiso otorgado?}
    C -->|No| D[Mostrar error, sugerir configuración]
    C -->|Sí| E[watchPosition cada 5s]
    E --> F[Obtener lat, lng, heading, velocidad]
    F --> G[POST /api/ubicacion]
    G --> H[Controller valida auth]
    H --> I{¿Es conductor/admin?}
    I -->|No| J[403 Forbidden]
    I -->|Sí| K{Rate limit OK?}
    K -->|No| L[429 Too Many Requests]
    K -->|Sí| M[RegistrarUbicacionUseCase]
    M --> N[Validar coordenadas]
    N --> O{¿Tiene traslado activo?}
    O -->|Sí| P[Asignar traslado_id]
    O -->|No| Q[traslado_id = null]
    P --> R[Upsert ubicacion_conductor]
    Q --> R
    R --> S[Insert historial_ubicacion]
    S --> T[LocationBroadcaster]
    T --> U[SSE → Mapa admin se actualiza]
```

## 3.4 Diagrama de Actividad — Flujo de Login

```mermaid
flowchart TD
    A[Usuario ingresa usuario + contraseña] --> B[CsrfMiddleware valida token]
    B --> C[RateLimiter verifica intentos]
    C --> D{¿Bloqueado?}
    D -->|Sí| E[Mostrar: cuenta bloqueada 15 min]
    D -->|No| F[AuthService::login]
    F --> G[Buscar usuario en BD]
    G --> H{¿Existe?}
    H -->|No| I[Incrementar contador fallidos]
    I --> J[Log intento fallido]
    J --> K[Mostrar: credenciales inválidas]
    H -->|Sí| L{¿Está activo?}
    L -->|No| M[Mostrar: cuenta desactivada]
    L -->|Sí| N[password_verify]
    N --> O{¿Password correcto?}
    O -->|No| I
    O -->|Sí| P[SessionManager::login]
    P --> Q[session_regenerate_id]
    Q --> R[CSRF token rotation]
    R --> S[Log login exitoso]
    S --> T[Redirigir a dashboard]
```

---

# 4. Listas de Acontecimientos

> *Todos los eventos que ocurren en el sistema, clasificados por tipo.*

## 4.1 Eventos Externos (iniciados por actores)

| # | Evento | Actor | Trigger | Respuesta del Sistema |
|---|--------|-------|---------|----------------------|
| E01 | Iniciar sesión | Cualquier usuario | Formulario POST /login | Validar credenciales, crear sesión, CSRF rotation |
| E02 | Cerrar sesión | Usuario autenticado | POST /logout | Destruir sesión, limpiar cookies |
| E03 | Registrarse | Paciente | Formulario POST /registro | Validar datos, crear usuario+paciente, login automático |
| E04 | Solicitar reset password | Cualquier usuario | POST /recuperar-contrasena | Generar token SHA-256, enviar email, rate limit |
| E05 | Ejecutar reset password | Cualquier usuario | POST /restablecer-contrasena | Validar token, actualizar password, invalidar sesiones |
| E06 | Crear funcionario | Admin | POST /funcionarios/crear | Validar datos, bcrypt, crear usuario+funcionario, audit log |
| E07 | Editar funcionario | Admin | POST /funcionarios/editar | Actualizar campos, audit log |
| E08 | Desactivar funcionario | Admin | POST /funcionarios/desactivar | activo=false, audit log |
| E09 | Reactivar funcionario | Admin | POST /funcionarios/reactivar | activo=true, audit log |
| E10 | Crear conductor | Admin | POST /conductores/crear | Validar licencia, bcrypt, crear usuario+funcionario, audit log |
| E11 | Editar conductor | Admin | POST /conductores/editar | Actualizar campos, audit log |
| E12 | Subir documento | Admin | POST /documentos/subir | Validar PDF, MIME, tamaño, generar QR, guardar archivo, audit log |
| E13 | Editar documento | Admin | POST /documentos/editar | Actualizar metadata, audit log |
| E14 | Eliminar documento | Admin | POST /documentos/eliminar | Eliminar archivo, desactivar QR, audit log |
| E15 | Crear encuesta | Admin | POST /encuestas/crear | Crear encuesta + preguntas en transacción, audit log |
| E16 | Responder encuesta | Paciente/Visitante | POST /publico/encuesta | Validar required, guardar respuestas, rate limit |
| E17 | Crear traslado | Admin | POST /traslados/nuevo | Validar conductor, vehiculo, ruta, crear en transacción, audit log |
| E18 | Actualizar estado traslado | Admin/Conductor | POST /traslados/actualizar-estado | Validar FSM, actualizar timestamp, historial, audit log |
| E19 | Registrar GPS | Conductor | POST /api/ubicacion | Validar auth, rate limit, upsert ubicación, broadcast SSE |
| E20 | Crear ruta | Admin | POST /rutas/crear | Validar datos, audit log |
| E21 | Crear noticia | Admin | POST /noticias/crear | Validar título/contenido, subir imagen, audit log |
| E22 | Editar noticia | Admin | POST /noticias/editar | Actualizar campos, reemplazar imagen si existe, audit log |
| E23 | Eliminar noticia | Admin | POST /noticias/eliminar | Eliminar imagen, borrar registro, audit log |
| E24 | Toggle noticia | Admin | POST /noticias/toggle | Cambiar activo, audit log |
| E25 | Ver documento por QR | Visitante | GET /publico/doc?id=X | Buscar documento, verificar activo, mostrar PDF |
| E26 | Ver mis documentos por token | Paciente | GET /publico/mis-documentos?token=X | Validar token, listar documentos del paciente |
| E27 | Ver QR de documento | Admin | GET /documentos/ver?id=X | Mostrar código QR con enlace público |

## 4.2 Eventos Temporales

| # | Evento | Frecuencia | Acción del Sistema |
|---|--------|-----------|-------------------|
| T01 | Expiración de sesión | Cada 30 min de inactividad | `SessionManager::checkTimeout()` destruye sesión |
| T02 | Expiración de token reset | 1 hora después de generación | Token inválido, usuario debe solicitar nuevo |
| T03 | Auto-refresh del mapa | Cada 5 segundos | JavaScript polls `/api/ubicaciones/activas` |
| T04 | Envío GPS del conductor | Cada 5 segundos | `watchPosition()` → POST a `/api/ubicacion` |
| T05 | Limpieza de sesiones stale | Al inicio de cada request | `SessionManager` limpia archivos >30min en `storage/sessions/` |
| T06 | Limpieza de SSE listeners | Cada 30 segundos | `LocationBroadcaster` elimina listeners stale |
| T07 | Expiración de caché de rutas | 30 días | `RouteCacheService` reutiliza rutas cacheadas |
| T08 | Rate limit window reset | 15 min (login), 60s (GPS), 1hr (uploads) | Contadores se resetean automáticamente |

## 4.3 Eventos de Estado (cambios internos)

| # | Evento | Entidad | Transición |
|---|--------|---------|-----------|
| S01 | Traslado creado | Traslado | → pendiente |
| S02 | Traslado iniciado | Traslado | pendiente → en_curso |
| S03 | Traslado en destino | Traslado | en_curso → en_destino |
| S04 | Traslado en retorno | Traslado | en_destino → en_retorno |
| S05 | Traslado completado | Traslado | en_retorno → completado |
| S06 | Traslado cancelado | Traslado | cualquier → cancelado |
| S07 | Funcionario desactivado | Funcionario | activo=true → activo=false |
| S08 | Funcionario reactivado | Funcionario | activo=false → activo=true |
| S09 | Noticia activada | Noticia | activo=false → activo=true |
| S10 | Noticia desactivada | Noticia | activo=true → activo=false |
| S11 | Documento eliminado | Documento | activo=true → DELETE |
| S12 | GPS posición actualizada | UbicacionConductor | upsert (misma fila) |
| S13 | Auditoría registrada | AuditLog | INSERT (nunca UPDATE/DELETE) |

## 4.4 Eventos de Señal (notificaciones)

| # | Evento | Origen | Destino | Mecanismo |
|---|--------|--------|---------|-----------|
| N01 | Nueva posición GPS | Conductor | Mapa admin | SSE (Server-Sent Events) |
| N02 | Estado de traslado cambiado | Conductor/Admin | Dashboard admin | Polling cada 5s |
| N03 | Email de reset password | Sistema | Usuario | PHPMailer + Gmail SMTP |
| N04 | Toast de éxito/error | Sistema | Usuario | JavaScript toast notification |
| N05 | Modal de confirmación | Sistema | Usuario | JavaScript modal (acciones destructivas) |

---

# 5. Diagramas UML

## 5.1 Diagrama de Clases

```mermaid
classDiagram
    class Usuario {
        +int id
        +string tipo
        +string nombre
        +string apellido
        +Email email
        +string documentoIdentidad
        +blob foto
        +string createdAt
    }

    class Funcionario {
        +string username
        +string passwordHash
        +RolUsuario rol
        +string licencia
        +string licenciaConducir
        +string telefono
        +bool activo
        +string resetToken
        +verificarPassword(string) bool
        +setPasswordHash(string) void
    }

    class Paciente {
        +string tokenAcceso
        +int codigoQrId
        +string username
        +string passwordHash
        +string telefono
        +bool activo
        +verificarPassword(string) bool
    }

    class Traslado {
        +int id
        +string codigo
        +int conductorId
        +int copilotoId
        +string origen
        +string destino
        +Coordenada origenCoordenada
        +Coordenada destinoCoordenada
        +EstadoTraslado estado
        +string motivoCancelacion
        +string horaSalidaEstimada
        +string horaSalidaEfectiva
        +string horaLlegadaDestino
        +string horaInicioRetorno
        +string horaLlegadaHospital
        +actualizarEstado(EstadoTraslado, string) void
    }

    class ElementoTraslado {
        +int trasladoId
        +TipoElemento tipo
        +int pacienteId
        +string descripcion
        +int cantidad
    }

    class Documento {
        +int id
        +string titulo
        +string descripcion
        +string archivoPath
        +string archivoNombre
        +int categoriaId
        +int pacienteId
        +int subidoPor
        +int codigoQrId
        +bool activo
        +getExtension() string
    }

    class Encuesta {
        +int id
        +string titulo
        +string descripcion
        +bool activa
        +int creadaPor
    }

    class Pregunta {
        +int id
        +int encuestaId
        +TipoPregunta tipo
        +string texto
        +array opciones
        +bool requerida
        +int orden
    }

    class Respuesta {
        +int id
        +string sesionToken
        +int encuestaId
        +int preguntaId
        +string tokenPaciente
        +int valorOpcion
        +string valorTexto
        +int valorNumerico
    }

    class HistorialEstado {
        +int id
        +int trasladoId
        +string estadoAnterior
        +string estadoNuevo
        +string observacion
        +int actualizadoPor
    }

    class UbicacionConductor {
        +int id
        +int conductorId
        +int trasladoId
        +Coordenada coordenada
        +int heading
        +float velocidad
    }

    class Noticia {
        +int id
        +string titulo
        +string contenido
        +string imagen
        +int autorId
        +bool activo
    }

    class Ruta {
        +int id
        +string nombre
        +string origen
        +string destino
        +float distanciaKm
    }

    class Vehiculo {
        +int id
        +string patente
        +string modelo
        +int anio
    }

    class AuditLog {
        +int id
        +string createdAt
        +int userId
        +string userType
        +string username
        +string ipAddress
        +string userAgent
        +string action
        +string entityType
        +string entityId
        +json details
    }

    class EstadoTraslado {
        +string value
        +puedeTransicionarA(EstadoTraslado) bool
        +transicionesPermitidas() list
        +esTerminal() bool
    }

    class Coordenada {
        +float latitud
        +float longitud
        +distanciaHaversine(Coordenada) float
    }

    class RolUsuario {
        +string value
        +esAdmin() bool
        +esConductor() bool
        +esCopiloto() bool
    }

    Usuario <|-- Funcionario
    Usuario <|-- Paciente
    Funcionario "1" --> "0..N" Traslado : conduce
    Funcionario "1" --> "0..N" Traslado : copiloto
    Traslado "1" --> "1" ElementoTraslado
    Traslado "1" --> "0..N" HistorialEstado
    Traslado "1" --> "0..1" UbicacionConductor
    Traslado "1" --> "0..1" Ruta
    Traslado "1" --> "0..1" Vehiculo
    Documento "1" --> "0..1" Categoria
    Encuesta "1" --> "0..N" Pregunta
    Encuesta "1" --> "0..N" Respuesta
    Pregunta "1" --> "0..N" Respuesta
    Traslado --> EstadoTraslado
    UbicacionConductor --> Coordenada
    Funcionario --> RolUsuario
```

## 5.2 Diagrama de Estados — Traslado

```mermaid
stateDiagram-v2
    [*] --> pendiente : Crear traslado

    pendiente --> en_curso : Conductor inicia\n(hora_salida_efectiva)
    pendiente --> cancelado : Cancelar\n(motivo obligatorio)

    en_curso --> en_destino : Llegar al destino\n(hora_llegada_destino)
    en_curso --> cancelado : Cancelar\n(motivo obligatorio)

    en_destino --> en_retorno : Iniciar retorno\n(hora_inicio_retorno)
    en_destino --> cancelado : Cancelar\n(motivo obligatorio)

    en_retorno --> completado : Llegar al hospital\n(hora_llegada_hospital)
    en_retorno --> cancelado : Cancelar\n(motivo obligatorio)

    completado --> [*]
    cancelado --> [*]

    note right of pendiente
        Estado inicial
        Código TR-XXX generado
    end note

    note right of completado
        Estado terminal
        Historial preservado
    end note

    note left of cancelado
        Estado terminal
        Motivo obligatorio
        historial_estado → SET NULL
    end note
```

## 5.3 Diagrama de Secuencia — Login

```mermaid
sequenceDiagram
    actor U as Usuario
    participant B as Navegador
    participant C as AuthController
    participant SM as SessionManager
    participant RL as RateLimiter
    participant AS as AuthService
    participant DB as MySQL
    participant AL as AuditLogger

    U->>B: Ingresa usuario + contraseña
    B->>C: POST /login (usuario, password, _csrf_token)
    C->>C: CsrfMiddleware::validate()
    C->>RL: checkLoginAttempts(ip)
    RL-->>C: ¿Bloqueado?

    alt Bloqueado
        C-->>B: 429 "Cuenta bloqueada 15 min"
    else No bloqueado
        C->>AS: login(username, password, ip)
        AS->>DB: SELECT * FROM funcionario WHERE username = ?
        DB-->>AS: usuario | null

        alt No existe
            AS->>RL: incrementLoginAttempts(ip, username)
            AS->>AL: logLogin(username, 'failed', ip, ua)
            AS-->>C: Exception "Credenciales inválidas"
        else Existe pero inactivo
            AS-->>C: Exception "Cuenta desactivada"
        else Válido
            AS->>AS: password_verify(password, hash)
            AS->>SM: login(userId, username, rol)
            SM->>SM: session_regenerate_id(true)
            SM->>SM: unset($_SESSION['_csrf_token'])
            SM->>SM: $_SESSION['_user_agent'] = UA
            AS->>AL: logLogin(username, 'success', ip, ua)
            AS-->>C: Login OK
        end

        C-->>B: 302 → /dashboard
    end
```

## 5.4 Diagrama de Secuencia — Crear Traslado

```mermaid
sequenceDiagram
    actor A as Admin
    participant B as Navegador
    participant TC as TrasladoController
    participant UC as RegistrarTrasladoUseCase
    participant CR as ConductorRepository
    participant TR as TrasladoRepository
    participant DB as MySQL
    participant AL as AuditLogger

    A->>B: Completa formulario de traslado
    B->>TC: POST /traslados/nuevo
    TC->>TC: requireRole('admin', 'superadmin')
    TC->>UC: execute(RegistrarTrasladoRequest)

    UC->>CR: findById(conductorId)
    CR->>DB: SELECT * FROM funcionario WHERE id = ?
    DB-->>CR: conductor

    alt Conductor no existe o inactivo
        UC-->>TC: Exception "Conductor no válido"
    else Válido
        UC->>UC: new Traslado(estado='pendiente')
        UC->>UC: new ElementoTraslado(tipo, descripcion)
        UC->>TR: beginTransaction()
        UC->>TR: save(traslado)
        TR->>DB: INSERT INTO traslado (...)
        TR->>DB: INSERT INTO elemento_traslado (...)
        UC->>TR: commit()
        UC->>AL: logCreate('noticia', ...)
        UC-->>TC: RegistrarTrasladoResponse(id, codigo)
    end

    TC-->>B: 302 → /traslados
```

## 5.5 Diagrama de Secuencia — Tracking GPS

```mermaid
sequenceDiagram
    actor C as Conductor
    participant手机 as Celular
    participant JS as tracking-conductor.js
    participant API as UbicacionController
    participant UC as RegistrarUbicacionUseCase
    participant Repo as UbicacionConductorRepo
    participant DB as MySQL
    participant SSE as LocationBroadcaster
    participant Map as Mapa Admin (JS)

    loop Cada 5 segundos
        C->>手机: GPS envía posición
        手机->>JS: watchPosition(callback)
        JS->>API: POST /api/ubicacion<br/>{latitud, longitud, heading, velocidad}
        API->>API: requireAuth() + requireRole(conductor)
        API->>API: RateLimiter::checkGPS()
        API->>UC: execute(RegistrarUbicacionRequest)
        UC->>UC: Validar coordenadas
        UC->>UC: findTrasladoActivo(conductorId)
        UC->>Repo: upsert(UbicacionConductor)
        Repo->>DB: INSERT ... ON DUPLICATE KEY UPDATE
        UC->>Repo: insertHistorial(conductorId, coordenada)
        Repo->>DB: INSERT INTO historial_ubicacion
        UC->>SSE: broadcast(event)
        SSE-->>Map: Event: nueva posición
        Map->>Map: Actualizar marcador en Leaflet
    end
```

## 5.6 Diagrama de Componentes

```mermaid
graph TB
    subgraph "Capa de Presentación"
        PV[Vistas PHP<br/>views/]
        JS[JavaScript Vanilla<br/>public/js/]
        CSS[CSS Web 2.0<br/>public/css/]
    end

    subgraph "Capa de Adaptadores de Entrada"
        Router[Router.php]
        CSRF[CsrfMiddleware]
        HP[HoneypotMiddleware]
        Controllers[14 Controladores]
    end

    subgraph "Capa de Aplicación"
        UC[25 Use Cases<br/>7 bounded contexts]
    end

    subgraph "Capa de Dominio"
        ENT[17 Entidades]
        VO[9 Value Objects]
        RI[11 Interfaces de Repo]
    end

    subgraph "Capa de Adaptadores de Salida"
        Repos[12 Repos MySQL]
        SM[SessionManager]
        AS[AuthService]
        AL[AuditLogger]
        RL[RateLimiter]
        QR[QRGeneratorService]
        FS[FileStorageService]
        RC[RouteCacheService]
        LB[LocationBroadcaster]
        EV[EmailService]
    end

    subgraph "Infraestructura Externa"
        MySQL[(MySQL 8.0)]
        OSRM[OSRM API]
        QRServer[QR Server API]
        Gmail[Gmail SMTP]
        GPS[Browser Geolocation]
    end

    PV --> Controllers
    JS --> Controllers
    Router --> Controllers
    Controllers --> CSRF
    Controllers --> HP
    Controllers --> UC
    UC --> ENT
    UC --> VO
    UC --> RI
    Repos --> RI
    Repos --> MySQL
    SM --> MySQL
    AS --> SM
    AL --> MySQL
    RC --> OSRM
    QR --> QRServer
    EV --> Gmail
    LB --> JS
```

## 5.7 Diagrama de Despliegue

```mermaid
graph TB
    subgraph "Cliente"
        Browser[Navegador Web<br/>Chrome/Firefox/Safari]
        Celular[Celular Conductor<br/>iOS/Android]
    end

    subgraph "Servidor"
        PHP[PHP 8.5<br/>Built-in Server / Apache]
        App[Elyra Application<br/>public/]
        Storage[storage/<br/>logs, sessions,<br/>uploads, cache]
    end

    subgraph "Base de Datos"
        MySQL[(MySQL 8.0<br/>elyra)]
    end

    subgraph "Servicios Externos"
        OSRM[OSRM<br/>Routing API]
        QR[QR Server<br/>api.qrserver.com]
        Gmail[Gmail SMTP<br/>PHPMailer]
        OSM[OpenStreetMap<br/>Tiles]
    end

    Browser -->|HTTP :8000| PHP
    Celular -->|HTTP :8000| PHP
    PHP --> App
    App --> Storage
    PHP --> MySQL
    App -->|HTTPS| OSRM
    App -->|HTTPS| QR
    App -->|SMTP| Gmail
    Browser -->|HTTPS| OSM
```

## 5.8 Diagrama de Paquetes

```mermaid
graph TB
    subgraph "Elyra"
        subgraph "Domain"
            Entity[Entity<br/>17 clases]
            VO[ValueObject<br/>9 clases]
            RepoInt[Repository<br/>11 interfaces]
        end

        subgraph "Application"
            UC[UseCases<br/>25 casos en 7 contextos]
            Auth[Auth/ — 7 cases]
            Doc[Documento/ — 5 cases]
            Tras[Traslado/ — 5 cases]
            Enc[Encuesta/ — 4 cases]
            Cond[Conductor/ — 3 cases]
            Rut[Ruta/ — 3 cases]
            Ubi[Ubicacion/ — 3 cases]
        end

        subgraph "Infrastructure"
            MySQLRepo[Persistence/MySQL<br/>12 repositorios]
            Services[Service<br/>12 servicios]
            Web[Web/Controller<br/>14 controladores]
            MW[Web/Middleware<br/>CsrfMiddleware, Honeypot]
            Router[Web/Router.php]
            Routes[Web/Routes/web.php<br/>94 rutas]
        end
    end

    subgraph "Presentation"
        Views[views/<br/>~30 templates PHP]
        JS[public/js/<br/>5 archivos JS]
        CSS[public/css/web20.css]
    end

    UC --> Entity
    UC --> VO
    UC --> RepoInt
    MySQLRepo --> RepoInt
    Web --> UC
    Web --> Services
    Views --> Web
    JS --> Web
```

---

# Anexo: Resumen de Métricas del Modelo

| Categoría | Cantidad |
|-----------|----------|
| Entidades de dominio | 17 |
| Value Objects | 9 |
| Interfaces de repositorio | 11 |
| Casos de uso | 25 |
| Controladores | 14 |
| Repositorios MySQL | 12 |
| Servicios de infraestructura | 12 |
| Rutas HTTP | 94 |
| Tablas en BD | 16 |
| Eventos externos | 27 |
| Eventos temporales | 8 |
| Eventos de estado | 13 |
| Eventos de señal | 5 |
| Invariants de negocio | 15 |
| Roles de actor | 11 |
| Servicios externos | 6 |

---

*Documento generado en Julio 2026.*
*Elyra — Sistema de Gestión Hospitalaria — Hospital de Clínicas, Montevideo, Uruguay.*
