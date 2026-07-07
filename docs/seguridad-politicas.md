# Documento de Seguridad — Elyra

| Campo | Detalle |
|-------|---------|
| **Producto** | Elyra — Sistema de Gestión Documental y Trazabilidad de Ambulancias |
| **Cliente** | Hospital de Clínicas — DTI (Piso 6) |
| **Versión** | 1.0 |
| **Fecha** | 2026 |
| **Autores** | Alan, Kevin, Tom |
| **PRD asociado** | `docs/PRD-Product-Requirements-Document.md` |

---

## Tabla de contenidos

- [1. Políticas de Seguridad](#1-políticas-de-seguridad)
  - [1.1 Política de Contraseñas](#11-política-de-contraseñas)
  - [1.2 Política de Sesiones](#12-política-de-sesiones)
  - [1.3 Política de Control de Acceso](#13-política-de-control-de-acceso)
  - [1.4 Política de Subida de Archivos](#14-política-de-subida-de-archivos)
  - [1.5 Política de Logs y Auditoría](#15-política-de-logs-y-auditoría)
  - [1.6 Política de Datos Personales](#16-política-de-datos-personales)
- [2. Arquitectura de Seguridad](#2-arquitectura-de-seguridad)
  - [2.1 Capas de Defensa](#21-capas-de-defensa)
  - [2.2 Diagrama de Flujo Seguro](#22-diagrama-de-flujo-seguro)
  - [2.3 Seguridad en la Base de Datos](#23-seguridad-en-la-base-de-datos)
  - [2.4 Seguridad en la Red](#24-seguridad-en-la-red)
- [3. Principales Amenazas](#3-principales-amenazas)
  - [3.1 Metodología STRIDE](#31-metodología-stride)
  - [3.2 Matriz de Amenazas](#32-matriz-de-amenazas)
  - [3.3 Modelado de Amenazas por Módulo](#33-modelado-de-amenazas-por-módulo)
- [4. Controles Implementados](#4-controles-implementados)
  - [4.1 Controles Técnicos](#41-controles-técnicos)
  - [4.2 Controles de Configuración](#42-controles-de-configuración)
  - [4.3 Mapeo contra PRD](#43-mapeo-contra-prd)
- [5. Plan de Respuesta a Incidentes](#5-plan-de-respuesta-a-incidentes)
- [6. Recomendaciones para Producción](#6-recomendaciones-para-producción)

---

## 1. Políticas de Seguridad

### 1.1 Política de Contraseñas

| ID | Regla | Detalle |
|----|-------|---------|
| P-01 | Longitud mínima | 6 caracteres (validado en servidor y cliente) |
| P-02 | Almacenamiento | **bcrypt** con costo ≥ 12 (PASSWORD_BCRYPT) |
| P-03 | Comparación | `password_verify()` — nunca comparación en texto plano ni hash propio |
| P-04 | Cambio de contraseña | El usuario puede cambiarla desde su perfil; requiere ingresar la nueva dos veces para confirmar |
| P-05 | Contraseña por defecto | Asignada por superadmin al crear el funcionario; debe cambiarse en el primer inicio |
| P-06 | Sin límite de intentos | Actualmente no hay bloqueo por intentos fallidos (ver R-01 en matriz de riesgos) |

**Referencia PRD**: SEG-01

### 1.2 Política de Sesiones

| ID | Regla | Detalle |
|----|-------|---------|
| S-01 | Inicio de sesión | Autenticación por username + password contra tabla `funcionario` |
| S-02 | Cookies de sesión | Cookies nativas de PHP con `session.cookie_httponly = 1` y `session.use_only_cookies = 1` |
| S-03 | Timeout por inactividad | 30 minutos (configurable en `session.gc_maxlifetime`) |
| S-04 | Cierre de sesión | Destrucción completa de la sesión con `session_destroy()` |
| S-05 | Regeneración de ID | `session_regenerate_id(true)` después del login exitoso para evitar session fixation |
| S-06 | Acceso público | Los pacientes acceden sin sesión mediante tokens QR (`token_acceso`) |
| S-07 | CSRF | Token CSRF generado por `SessionManager::getCsrfToken()` validado en cada formulario POST |

**Referencia PRD**: SEG-02, SEG-03, SEG-06

### 1.3 Política de Control de Acceso

| ID | Regla | Detalle |
|----|-------|---------|
| A-01 | Autenticación obligatoria | Toda ruta protegida redirige a `/login` si no hay sesión |
| A-02 | Roles | Tres roles: `admin`, `superadmin`, `conductor` |
| A-03 | Verificación por rol | Cada controlador verifica permisos explícitamente con `requireRole()` |
| A-04 | Separación de módulos | No hay datos de paciente visibles para conductores a menos que estén en un traslado activo |
| A-05 | Principio de mínimo privilegio | Conductor solo ve traslados propios; admin no puede crear otros admins (solo superadmin) |

**Referencia PRD**: SEG-08, FR-05

### 1.4 Política de Subida de Archivos

| ID | Regla | Detalle |
|----|-------|---------|
| F-01 | Tipo MIME | Validación con `finfo(FILEINFO_MIME_TYPE)` — solo `application/pdf` |
| F-02 | Extensión | Validación de extensión contra allowlist: `.pdf` |
| F-03 | Tamaño máximo | 10 MB (configurable, validado en servidor) |
| F-04 | Almacenamiento | Archivos guardados en `storage/docs/` fuera del document root |
| F-05 | Acceso a archivos | Servidos a través de un controlador PHP (`/documentos/archivo`) que verifica permisos |
| F-06 | Nombre seguro | Los archivos se renombran con prefijo descriptivo + timestamp para evitar colisiones y path traversal |

**Referencia PRD**: SEG-11, FR-09

### 1.5 Política de Logs y Auditoría

| ID | Regla | Detalle |
|----|-------|---------|
| L-01 | Log de errores | Todos los errores PHP se registran en `storage/logs/YYYY-MM-DD.log` |
| L-02 | Log de autenticación | Intentos de login fallidos se registran con timestamp, username e IP |
| L-03 | Log de cambios de estado | Los traslados registran cada cambio de estado en `historial_estado` con usuario y timestamp |
| L-04 | Formato estructurado | Los logs incluyen nivel (CRITICAL, ERROR), mensaje, archivo, línea y stack trace |
| L-05 | Rotación de logs | Rotación diaria por archivo de fecha |

**Referencia PRD**: SEG-09

### 1.6 Política de Datos Personales

| ID | Regla | Detalle |
|----|-------|---------|
| D-01 | Residencia de datos | Almacenamiento exclusivo en servidores del DTI del Hospital de Clínicas |
| D-02 | Datos mínimos | Solo se recolectan datos estrictamente necesarios: nombre, apellido, email, documento de identidad |
| D-03 | Acceso a datos personales | Solo funcionarios autenticados con rol admin/superadmin pueden ver datos de pacientes |
| D-04 | Documento de identidad | Almacenado como `VARCHAR(20)` en `usuario.documento_identidad` con unique constraint |
| D-05 | Foto de perfil | Almacenada como `LONGBLOB` en `usuario.foto`; sin copia en disco |

**Referencia PRD**: NFR-11, RES-03

---

## 2. Arquitectura de Seguridad

### 2.1 Capas de Defensa

```
┌─────────────────────────────────────────────────────────┐
│                    CAPA 1: RED                           │
│  • Servidores del DTI (red interna del hospital)        │
│  • HTTPS forzado en producción                          │
│  • Sin exposición directa a Internet                    │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│                    CAPA 2: APLICACIÓN                    │
│  • Router con dispatch de rutas                         │
│  • Middleware: autenticación, CSRF, rate limiting        │
│  • Prepared statements para todas las consultas SQL      │
│  • Escape de salida con htmlspecialchars()              │
│  • Headers de seguridad (CSP, X-Frame-Options, etc.)    │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│                    CAPA 3: DOMINIO                       │
│  • Validación de tipos estricta (declare(strict_types=1))│
│  • Value objects para datos críticos (RolUsuario, etc.) │
│  • Entidades con métodos getter/setter tipados          │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│                    CAPA 4: DATOS                         │
│  • PDO con ERRMODE_EXCEPTION                            │
│  • Prepared statements en todas las queries             │
│  • Contraseñas con bcrypt                               │
│  • Archivos fuera del document root                     │
└─────────────────────────────────────────────────────────┘
```

### 2.2 Diagrama de Flujo Seguro

```
Solicitud HTTP
     │
     ▼
┌─────────────────────┐
│   Front Controller  │  ← index.php
│   (public/index.php)│
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│  Middleware Chain    │
│  ┌───────────────┐  │
│  │ Rate Limiting │  │  ← Límite de requests por IP (rutas públicas)
│  ├───────────────┤  │
│  │ CSRF Check    │  │  ← Token en formularios POST
│  ├───────────────┤  │
│  │ CSP Headers   │  │  ← Content-Security-Policy + otros headers
│  └───────────────┘  │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│   Autenticación     │  ← Verifica sesión (excepto rutas públicas)
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│   Verificación Rol  │  ← requireRole() en controladores
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│   Controlador       │  ← Validación de entrada, lógica de negocio
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│   Repositorio (PDO) │  ← Prepared statements
└─────────┬───────────┘
          │
          ▼
       MySQL DB
```

### 2.3 Seguridad en la Base de Datos

| Aspecto | Implementación |
|---------|---------------|
| **Conexión** | PDO con DSN, usuario y password desde `.env` (excluido del repo) |
| **SQL Injection** | 100% prepared statements con `PDO::prepare()` + `execute()` |
| **Errores** | `PDO::ERRMODE_EXCEPTION` — las excepciones se capturan y registran sin exponer detalles al usuario |
| **Contraseñas** | `VARCHAR(255)` con hash bcrypt (>60 caracteres) |
| **Unique constraints** | `email` y `documento_identidad` tienen UNIQUE — los errores de duplicado se capturan y muestran mensaje amigable |
| **Integridad referencial** | Claves foráneas con `ON DELETE CASCADE` donde corresponde (ej: pregunta → encuesta) |

### 2.4 Seguridad en la Red

| Aspecto | Estado |
|---------|--------|
| **HTTPS** | Forzado en producción (configuración de Apache/Nginx del DTI) |
| **HSTS** | Header `Strict-Transport-Security` incluido (ver PRD SEG-12) |
| **Content Security Policy** | Header CSP restrictivo: solo scripts y estilos de CDNs autorizadas |
| **X-Frame-Options** | `SAMEORIGIN` — previene clickjacking |
| **X-Content-Type-Options** | `nosniff` — previene MIME sniffing |
| **Rate limiting** | Control de requests por IP en rutas públicas (storage/rate-limit/) |

---

## 3. Principales Amenazas

### 3.1 Metodología STRIDE

Se aplica el modelo **STRIDE** (Microsoft) para categorizar amenazas por tipo:

| Tipo | Descripción |
|------|-------------|
| **S**poofing | Suplantación de identidad |
| **T**ampering | Manipulación de datos |
| **R**epudiation | Negación de acciones |
| **I**nformation Disclosure | Exposición de información |
| **D**enial of Service | Denegación de servicio |
| **E**levation of Privilege | Elevación de privilegios |

### 3.2 Matriz de Amenazas

| ID | Amenaza | STRIDE | Activo Afectado | Probabilidad | Impacto | Riesgo | Controles |
|----|---------|--------|-----------------|-------------|---------|--------|-----------|
| T-01 | **SQL Injection** en formularios de búsqueda | T | Base de datos | Baja | Crítico | Medio | Prepared statements en 100% de las queries |
| T-02 | **XSS** en campos de texto (documentos, encuestas) | T, I | Navegador del usuario | Media | Alto | Alto | `htmlspecialchars()` con ENT_QUOTES en toda salida |
| T-03 | **CSRF** en formularios de administración | T, E | Sesión del admin | Media | Alto | Alto | Token CSRF por formulario, validado en servidor |
| T-04 | **Session Fixation** | S | Sesión de usuario | Baja | Alto | Medio | `session_regenerate_id(true)` post-login |
| T-05 | **Ataque de diccionario** a login | S | Cuentas de funcionarios | Alta | Alto | Alto | Sin bloqueo actual (ver pendiente) |
| T-06 | **Subida de archivo malicioso** | T | Servidor de archivos | Baja | Crítico | Medio | Validación MIME + extensión + almacenamiento fuera de document root |
| T-07 | **Path Traversal** en descarga de archivos | I, T | Archivos del sistema | Baja | Alto | Medio | Nombres seguros con prefijo + timestamp |
| T-08 | **Acceso no autorizado** a rutas de admin | E, I | Datos del sistema | Media | Crítico | Alto | Verificación de autenticación + rol en cada endpoint |
| T-09 | **Fuga de información** en respuestas de error | I | Configuración del sistema | Baja | Medio | Bajo | ErrorHandler oculta detalles en producción, solo logs internos |
| T-10 | **Denegación de servicio** en rutas públicas | D | Disponibilidad del sistema | Media | Alto | Alto | Rate limiting en rutas públicas |
| T-11 | **Ataque de relleno (padding)** a bcrypt | S | Hash de contraseñas | Baja | Bajo | Bajo | bcrypt es inherentemente resistente a timing attacks |
| T-12 | **Inyección de cabeceras HTTP** | T | Respuesta HTTP | Baja | Medio | Bajo | Headers fijos en middleware, sin entrada de usuario en headers |
| T-13 | **Clickjacking** | E, I | Interfaz de usuario | Baja | Medio | Bajo | `X-Frame-Options: SAMEORIGIN` |
| T-14 | **Man-in-the-Middle** en red local | I, T | Datos en tránsito | Media | Alto | Alto | HTTPS obligatorio en producción |
| T-15 | **Exposición de datos sensibles** en logs | I | Datos de pacientes | Baja | Alto | Bajo | Logs no incluyen datos sensibles (passwords, tokens completos) |

### 3.3 Modelado de Amenazas por Módulo

#### Módulo de Identidad

```
Login ────────── T-05 (diccionario), T-04 (fixation)
  │
  ▼
Sesión ───────── T-03 (CSRF), T-08 (acceso no autorizado)
  │
  ▼
Perfil ───────── T-01 (SQLi en edición), T-02 (XSS en campos)
```

#### Módulo de Documentación

```
Subir archivo ── T-06 (archivo malicioso), T-07 (path traversal)
  │
  ▼
Vista pública ── T-10 (DoS en QR público), T-09 (fuga de info)
  │
  ▼
Encuestas ────── T-02 (XSS en preguntas/respuestas), T-01 (SQLi)
```

#### Módulo de Ambulancias

```
Registro ─────── T-01 (SQLi), T-02 (XSS)
  │
  ▼
Estados ──────── T-03 (CSRF en cambio de estado), T-08 (acceso no autorizado)
  │
  ▼
Historial ────── T-09 (exposición indebida de datos de pacientes)
```

---

## 4. Controles Implementados

### 4.1 Controles Técnicos

| ID Control | Tipo | Descripción | Localización |
|------------|------|-------------|--------------|
| C-01 | Preventivo | Prepared statements PDO | Todos los repositorios |
| C-02 | Preventivo | `htmlspecialchars()` en toda salida HTML | Todas las vistas |
| C-03 | Preventivo | Token CSRF por formulario | `SessionManager::getCsrfToken()`, `CsrfMiddleware` |
| C-04 | Preventivo | `session_regenerate_id(true)` post-login | `AuthController::doLogin()` |
| C-05 | Preventivo | Validación MIME con `finfo` + extensión + tamaño | `DocumentoController::handleUpload()`, `PerfilController::validarFoto()` |
| C-06 | Preventivo | Archivos fuera de document root (`storage/docs/`) | Configuración de upload |
| C-07 | Preventivo | Headers CSP, X-Frame-Options, X-Content-Type-Options | Middleware en `index.php` |
| C-08 | Detectivo | Logs de errores y excepciones | `ErrorHandler` |
| C-09 | Detectivo | Logs de cambios de estado de traslados | `TrasladoRepository` |
| C-10 | Preventivo | Rate limiting en rutas públicas | `RateLimiter` middleware |
| C-11 | Preventivo | `declare(strict_types=1)` en todo el código fuente | Todos los archivos PHP |
| C-12 | Preventivo | Hash bcrypt para contraseñas | `password_hash()` con `PASSWORD_BCRYPT` |

### 4.2 Controles de Configuración

| ID | Configuración | Archivo |
|----|--------------|---------|
| CF-01 | `session.cookie_httponly = 1` | `php.ini` o `SessionManager` |
| CF-02 | `session.use_only_cookies = 1` | `php.ini` o `SessionManager` |
| CF-03 | `session.cookie_secure = 1` (en producción) | `php.ini` o `SessionManager` |
| CF-04 | `session.gc_maxlifetime = 1800` (30 min) | `php.ini` o `SessionManager` |
| CF-05 | `display_errors = 0` en producción | `index.php` con `APP_DEBUG` |
| CF-06 | `.env` excluido del repositorio (`.gitignore`) | `.gitignore` |
| CF-07 | `storage/` excluido del repositorio | `.gitignore` |

### 4.3 Mapeo contra PRD

| PRD ID | Requisito | Control(es) | Estado |
|--------|-----------|-------------|--------|
| SEG-01 | Hash bcrypt (cost 12+) | C-11 | ✔ Implementado |
| SEG-02 | Timeout de sesión 30 min | CF-04 | ✔ Implementado |
| SEG-03 | Cerrar sesión al cerrar navegador | Cookies de sesión (session cookie) | ✔ Implementado |
| SEG-04 | Prepared statements | C-01 | ✔ Implementado |
| SEG-05 | Escape HTML | C-02 | ✔ Implementado |
| SEG-06 | Tokens CSRF | C-03 | ✔ Implementado |
| SEG-07 | HTTPS forzado | Configuración servidor DTI | ⬜ Pendiente (producción) |
| SEG-08 | Verificación por rol | A-03, T-08 | ✔ Implementado |
| SEG-09 | Logs de intentos fallidos | C-08 | ✔ Implementado |
| SEG-10 | Validación de entrada | C-11 (tipado estricto) + validación en controladores | ✔ Implementado |
| SEG-11 | Validación archivos (MIME, tamaño) | C-05 | ✔ Implementado |
| SEG-12 | Headers de seguridad | C-07 | ✔ Implementado |
| NFR-07 | Protección SQLi, XSS, CSRF | C-01, C-02, C-03 | ✔ Implementado |

---

## 5. Plan de Respuesta a Incidentes

### 5.1 Clasificación de Incidentes

| Nivel | Descripción | Ejemplo |
|-------|-------------|---------|
| **Bajo** | Sin impacto en datos o disponibilidad | Intento de login fallido aislado, error de validación |
| **Medio** | Impacto limitado, datos no sensibles | Subida de archivo inválido, CSRF detectado |
| **Alto** | Posible exposición de datos | Intento de SQL injection, XSS reportado |
| **Crítico** | Brecha de datos confirmada o DoS sostenido | Acceso no autorizado a datos de pacientes, caída del sistema |

### 5.2 Procedimiento

```
Detección (log / reporte de usuario / monitoreo)
     │
     ▼
Clasificar gravedad (Bajo / Medio / Alto / Crítico)
     │
     ▼
Contener:
  • Crítico → Detener servicio, revocar sesiones activas
  • Alto → Bloquear IP de origen, revisar logs
  • Medio/Bajo → Registrar y monitorear
     │
     ▼
Analizar:
  • Revisar logs de aplicación y servidor
  • Identificar vector de ataque
  • Determinar alcance (qué datos fueron accedidos)
     │
     ▼
Mitigar:
  • Aplicar parche si aplica
  • Reforzar controles existentes
  • Rotar credenciales afectadas
     │
     ▼
Documentar:
  • Reporte de incidente
  • Lecciones aprendidas
  • Actualizar este documento si corresponde
```

---

## 6. Recomendaciones para Producción

| ID | Recomendación | Prioridad | Referencia |
|----|--------------|-----------|------------|
| R-P-01 | Implementar **bloqueo por intentos fallidos** (ej: 5 intentos → bloqueo 15 min) | Alta | T-05 |
| R-P-02 | Forzar **HTTPS exclusivamente** con redirect automático de HTTP | Alta | SEG-07 |
| R-P-03 | Configurar **HSTS** con `max-age=31536000; includeSubDomains` | Alta | SEG-12 |
| R-P-04 | Agregar **2FA** (TOTP) para cuentas admin/superadmin | Media | SEG-01 |
| R-P-05 | Implementar **auditoría de acceso a datos**: registrar qué usuario consultó qué paciente | Media | L-03 |
| R-P-06 | Agregar **Content-Security-Policy-Report-Only** para monitorear violaciones antes de forzar | Media | C-07 |
| R-P-07 | Configurar **firewall a nivel de aplicación** (mod_security o similar) en el servidor web | Media | — |
| R-P-08 | Realizar **pruebas de penetración** periódicas (anuales) | Baja | — |
| R-P-09 | Agregar **versiones de documentos** para no perder archivos al reemplazarlos | Baja | FR-10 |
| R-P-10 | Implementar **límite de sesiones concurrentes** por usuario | Baja | S-03 |

---

*Documento alineado con el PRD v1.0 y los requisitos de seguridad SEG-01 a SEG-12. Revisar y actualizar tras cada sprint o cambio significativo en la arquitectura.*
