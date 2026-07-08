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

- [1. Marco Legal Uruguayo](#1-marco-legal-uruguayo)
  - [1.1 Ley N° 18.331 — Protección de Datos Personales](#11-ley-n-18331--protección-de-datos-personales)
  - [1.2 Estrategia Nacional de Ciberseguridad 2024-2030](#12-estrategia-nacional-de-ciberseguridad-2024-2030)
  - [1.3 Decretos y Normativas Complementarias](#13-decretos-y-normativas-complementarias)
  - [1.4 Organismos Rectores](#14-organismos-rectores)
  - [1.5 Implicancias para Elyra](#15-implicancias-para-elyra)
- [2. Políticas de Seguridad](#2-políticas-de-seguridad)
  - [2.1 Política de Contraseñas y Autenticación](#21-política-de-contraseñas-y-autenticación)
  - [2.2 Política de Sesiones](#22-política-de-sesiones)
  - [2.3 Política de Control de Acceso y Roles](#23-política-de-control-de-acceso-y-roles)
  - [2.4 Política de Subida y Gestión de Archivos](#24-política-de-subida-y-gestión-de-archivos)
  - [2.5 Política de Logs, Auditoría y Trazabilidad](#25-política-de-logs-auditoría-y-trazabilidad)
  - [2.6 Política de Protección de Datos Personales](#26-política-de-protección-de-datos-personales)
- [3. Reglas de Desarrollo Backend](#3-reglas-de-desarrollo-backend)
  - [3.1 Estándares de Código](#31-estándares-de-código)
  - [3.2 Reglas de Arquitectura (Hexagonal)](#32-reglas-de-arquitectura-hexagonal)
  - [3.3 Reglas de Base de Datos](#33-reglas-de-base-de-datos)
  - [3.4 Reglas de Validación de Entrada](#34-reglas-de-validación-de-entrada)
  - [3.5 Reglas de Manejo de Errores](#35-reglas-de-manejo-de-errores)
  - [3.6 Reglas de Sesión y Autenticación](#36-reglas-de-sesión-y-autenticación)
  - [3.7 Reglas de Archivos](#37-reglas-de-archivos)
  - [3.8 Reglas de Logging](#38-reglas-de-logging)
  - [3.9 Reglas de Git y Commits](#39-reglas-de-git-y-commits)
  - [3.10 Reglas de Dependencias](#310-reglas-de-dependencias)
  - [3.11 Reglas de Vistas (Frontend PHP)](#311-reglas-de-vistas-frontend-php)
- [4. Arquitectura de Seguridad](#4-arquitectura-de-seguridad)
  - [4.1 Capas de Defensa (Defense in Depth)](#41-capas-de-defensa-defense-in-depth)
  - [4.2 Diagrama de Flujo Seguro](#42-diagrama-de-flujo-seguro)
  - [4.3 Seguridad en la Base de Datos](#43-seguridad-en-la-base-de-datos)
  - [4.4 Seguridad en la Red y Transporte](#44-seguridad-en-la-red-y-transporte)
- [5. Protocolos de Seguridad Implementados](#5-protocolos-de-seguridad-implementados)
  - [5.1 Protección contra CSRF](#51-protección-contra-csrf)
  - [5.2 Protección contra XSS](#52-protección-contra-xss)
  - [5.3 Protección contra SQL Injection](#53-protección-contra-sql-injection)
  - [5.4 Protección de Sesión](#54-protección-de-sesión)
  - [5.5 Protección de Contraseñas](#55-protección-de-contraseñas)
  - [5.6 Content Security Policy (CSP)](#56-content-security-policy-csp)
  - [5.7 Headers de Seguridad HTTP](#57-headers-de-seguridad-http)
  - [5.8 Rate Limiting](#58-rate-limiting)
  - [5.9 Validación y Sanitización de Entrada](#59-validación-y-sanitización-de-entrada)
  - [5.10 Subida Segura de Archivos](#510-subida-segura-de-archivos)
  - [5.11 Protección contra Path Traversal](#511-protección-contra-path-traversal)
  - [5.12 Protección contra Session Fixation](#512-protección-contra-session-fixation)
  - [5.13 Protección contra Clickjacking](#513-protección-contra-clickjacking)
  - [5.14 MIME Sniffing Prevention](#514-mime-sniffing-prevention)
  - [5.15 Manejo Seguro de Errores](#515-manejo-seguro-de-errores)
- [6. Principales Amenazas](#6-principales-amenazas)
  - [6.1 Metodología STRIDE](#61-metodología-stride)
  - [6.2 Matriz de Amenazas](#62-matriz-de-amenazas)
  - [6.3 Modelado de Amenazas por Módulo](#63-modelado-de-amenazas-por-módulo)
- [7. Controles Implementados](#7-controles-implementados)
  - [7.1 Controles Técnicos](#71-controles-técnicos)
  - [7.2 Controles de Configuración](#72-controles-de-configuración)
  - [7.3 Mapeo contra PRD](#73-mapeo-contra-prd)
  - [7.4 Mapeo contra Ley 18.331](#74-mapeo-contra-ley-18331)
- [8. Plan de Respuesta a Incidentes](#8-plan-de-respuesta-a-incidentes)
- [9. Recomendaciones para Producción](#9-recomendaciones-para-producción)

---

## 1. Marco Legal Uruguayo

### 1.1 Ley N° 18.331 — Protección de Datos Personales

La **Ley N° 18.331** de 18 de agosto de 2008 (Ley de Protección de Datos Personales y Acción de Habeas Data) regula el tratamiento de datos personales en Uruguay. Uruguay es reconocido por la Unión Europea como país con nivel adecuado de protección (Decisión 2012/484/UE), siendo el segundo país latinoamericano en obtenerlo.

#### Principios Rectores (Capítulo II, Arts. 5-12)

| Principio | Descripción | Aplicación en Elyra |
|-----------|-------------|---------------------|
| **Legalidad** | Los datos deben obtenerse y tratarse de forma lícita | Toda recolección de datos tiene una finalidad explícita documentada |
| **Veracidad** | Los datos deben ser exactos y, si es necesario, actualizados | Validación de CI (8 dígitos), email, teléfono |
| **Finalidad** | Los datos solo pueden usarse para la finalidad declarada | Datos de pacientes usados solo para gestión documental y traslados |
| **Consentimiento previo e informado** | El titular debe autorizar el tratamiento | El paciente acepta términos al recibir su token de acceso |
| **Seguridad** | Deben adoptarse medidas técnicas y organizativas para proteger los datos | Todo este documento |
| **Confidencialidad** | Obligación de secreto profesional sobre datos personales | Control de acceso por roles, logs de auditoría |

#### Derechos de los Titulares (Capítulo III, Arts. 13-17)

| Derecho | Descripción | Cómo se cumple |
|---------|-------------|----------------|
| **Acceso** | Conocer qué datos personales están siendo tratados | Perfil de usuario visible en `/perfil` |
| **Rectificación** | Solicitar corrección de datos inexactos | Edición de perfil (email, teléfono, foto) |
| **Cancelación** | Solicitar eliminación de datos cuando corresponda | Baja de usuario (no implementado aún, ver R-P-11) |
| **Oposición** | Oponerse al tratamiento de sus datos | No aplica a datos necesarios para la operación hospitalaria |
| **Habeas Data** (Art. 37) | Acción judicial para proteger datos personales | Mecanismo externo al sistema |

#### Datos Especialmente Protegidos (Capítulo IV, Arts. 18-23)

Se consideran datos sensibles aquellos relativos a: origen racial o étnico, preferencias políticas, convicciones religiosas, estado de salud, vida sexual. **Elyra no almacena datos sensibles** según esta clasificación. Los documentos médicos subidos al sistema no se analizan en su contenido; la responsabilidad del contenido recae en el funcionario que los sube.

#### Obligaciones Específicas

| Obligación | Requisito | Estado en Elyra |
|-----------|-----------|-----------------|
| Registro de bases de datos ante URCDP | Toda base de datos con datos personales debe registrarse | ⬜ Pendiente (producción) |
| Notificación de violaciones de datos | Reportar a URCDP en un plazo razonable | Pendiente (procedimiento) |
| Designación de DPO | Si procesa datos sensibles o >35.000 registros | ⬜ Pendiente evaluar |
| Transferencia internacional de datos | Restringida a países con nivel adecuado | ✔ No aplica (datos residentes en servidores del hospital) |

### 1.2 Estrategia Nacional de Ciberseguridad 2024-2030

Uruguay cuenta con una **Estrategia Nacional de Ciberseguridad** (ENC) para el período 2024-2030, estructurada en 8 pilares:

| Pilar | Descripción | Pertinencia para Elyra |
|-------|-------------|------------------------|
| **Gobernanza** | Marco institucional y coordinación | Seguir lineamientos de AGESIC |
| **Marco normativo** | Actualización legal en materia de ciberseguridad | Cumplir con Ley 18.331 y decretos |
| **Ciberdelitos** | Prevención, detección y sanción | Controles CSRF, XSS, SQLi en el sistema |
| **Ciberdefensa** | Protección de infraestructuras críticas | El hospital es infraestructura crítica |
| **Infraestructuras de información crítica** | Protección de sistemas esenciales | Elyra corre en servidores del DTI |
| **Cultura de ciberseguridad** | Concientización y capacitación | Capacitación a usuarios administrativos |
| **Ecosistema e industria** | Fomento de la industria de ciberseguridad | Uso de estándares y buenas prácticas |
| **Política internacional** | Cooperación y alineación con estándares globales | Alineación con NIST CSF y OWASP |

La ENC se basa en el **Marco de Ciberseguridad del Uruguay**, adaptado del **NIST Cybersecurity Framework (CSF)**, que define funciones clave: Identificar, Proteger, Detectar, Responder y Recuperar.

### 1.3 Decretos y Normativas Complementarias

| Normativa | Contenido | Relevancia |
|-----------|-----------|------------|
| **Decreto N° 66/025** | Disposiciones sobre seguridad de la información en organismos del Estado | Aplica al Hospital de Clínicas como organismo público |
| **Decreto N° 92/014** | Reglamentación de la Ley 18.331 | Detalla obligaciones de seguridad técnicas y organizativas |
| **Ley N° 20.212** (Arts. 78-79) | Refuerza obligaciones de seguridad digital | Implementación de medidas de seguridad en servicios digitales |
| **Ley N° 19.723** | Ley de Delitos Informáticos (2019) | Tipifica acceso ilegítimo, daño informático, violación de comunicaciones |

### 1.4 Organismos Rectores

| Organismo | Rol |
|-----------|-----|
| **AGESIC** | Agencia de Gobierno Electrónico y Sociedad de la Información — rectoría en ciberseguridad |
| **URCDP** | Unidad Reguladora y de Control de Datos Personales — control y fiscalización de datos personales |
| **CERTuy** | Centro Nacional de Respuesta a Incidentes de Seguridad Informática |
| **DTI del Hospital de Clínicas** | Responsable de la infraestructura tecnológica donde se aloja Elyra |

### 1.5 Implicancias para Elyra

Elyra, como sistema que procesa datos personales de pacientes y funcionarios de un hospital público, debe:

1. **Cumplir con la Ley 18.331** en todas las etapas del tratamiento de datos.
2. **Registrar las bases de datos** ante la URCDP antes de su puesta en producción.
3. **Implementar medidas de seguridad técnicas y organizativas** adecuadas al riesgo (Arts. 9 y 12 de la Ley 18.331).
4. **Garantizar los derechos de acceso, rectificación y cancelación** de los titulares de datos.
5. **Notificar violaciones de seguridad** a la URCDP y a los afectados.
6. **Seguir los lineamientos del Marco de Ciberseguridad del Uruguay** (basado en NIST CSF).

---

## 2. Políticas de Seguridad

### 2.1 Política de Contraseñas y Autenticación

| ID | Regla | Detalle | Fundamento |
|----|-------|---------|------------|
| P-01 | Longitud mínima | 6 caracteres (validado en servidor y cliente) | OWASP ASVS |
| P-02 | Almacenamiento | **bcrypt** con costo ≥ 12 (`PASSWORD_BCRYPT`) | Ley 18.331 Art. 9 (seguridad) |
| P-03 | Verificación | `password_verify()` — nunca comparación en texto plano ni hash propio | OWASP |
| P-04 | Cambio de contraseña | El usuario puede cambiarla desde su perfil; requiere confirmación escribiéndola dos veces | NIST SP 800-63 |
| P-05 | Contraseña por defecto | Asignada por superadmin al crear el funcionario; debe cambiarse en el primer inicio | OWASP |
| P-06 | Sin almacenamiento en texto plano | Nunca se loguea ni muestra la contraseña original | Ley 18.331 |
| P-07 | Política de sesión | Timeout por inactividad a los 30 minutos | NIST |

### 2.2 Política de Sesiones

| ID | Regla | Detalle |
|----|-------|---------|
| S-01 | Inicio de sesión | Autenticación por username + password contra tabla `funcionario` |
| S-02 | Cookies de sesión | `session.cookie_httponly = 1`, `session.use_only_cookies = 1` |
| S-03 | Secure flag | `session.cookie_secure = 1` cuando se sirve sobre HTTPS |
| S-04 | Timeout por inactividad | 30 minutos (`session.gc_maxlifetime = 1800`) |
| S-05 | Cierre de sesión | Destrucción completa con `session_destroy()` + limpieza de cookies |
| S-06 | Regeneración de ID | `session_regenerate_id(true)` inmediatamente después del login exitoso |
| S-07 | Sesión por cookie | No se aceptan ID de sesión por URL o GET |
| S-08 | Acceso público | Pacientes acceden sin sesión mediante tokens QR (`token_acceso` UUID v4) |
| S-09 | CSRF | Token generado por `SessionManager::getCsrfToken()` y validado en cada formulario POST |

### 2.3 Política de Control de Acceso y Roles

| ID | Regla | Detalle |
|----|-------|---------|
| A-01 | Autenticación obligatoria | Toda ruta protegida redirige a `/login` si no hay sesión activa |
| A-02 | Roles del sistema | `superadmin` (acceso total), `admin` (gestión completa), `conductor` (solo traslados y perfil) |
| A-03 | Verificación por rol | Cada controlador verifica permisos explícitamente con `requireRole()` antes de ejecutar acciones |
| A-04 | Mínimo privilegio | Conductor solo ve traslados propios; admin no puede crear otros admins |
| A-05 | Separación paciente-funcionario | Pacientes no tienen sesión en el sistema; acceden solo por QR a sus documentos |
| A-06 | Denegación por defecto | Si el rol no está explícitamente autorizado, se deniega el acceso |
| A-07 | Rutas públicas explícitas | Solo `/login`, `/registro` y `/publico/*` son accesibles sin autenticación |

### 2.4 Política de Subida y Gestión de Archivos

| ID | Regla | Detalle |
|----|-------|---------|
| F-01 | Tipo MIME | Validación con `finfo(FILEINFO_MIME_TYPE)` — solo `application/pdf` |
| F-02 | Extensión | Allowlist: solo `.pdf` |
| F-03 | Tamaño máximo | 10 MB, validado en servidor (en producción) |
| F-04 | Almacenamiento | Archivos guardados en `storage/docs/` — fuera del document root público |
| F-05 | Acceso controlado | Servidos a través de controlador PHP (`/documentos/archivo`) con verificación de permisos |
| F-06 | Nombre seguro | Renombrado automático con prefijo descriptivo + timestamp, sin extensión ejecutable |
| F-07 | Foto de perfil | Validación MIME (JPEG, PNG, GIF, WebP), verificación con `imagecreatefromstring()`, máx 2MB |
| F-08 | Sin ejecución | Los archivos se almacenan sin permisos de ejecución en el sistema de archivos |

### 2.5 Política de Logs, Auditoría y Trazabilidad

| ID | Regla | Detalle |
|----|-------|---------|
| L-01 | Log de errores | Todos los errores PHP se registran en `storage/logs/YYYY-MM-DD.log` |
| L-02 | Log de autenticación | Intentos de login fallidos se registran con timestamp, username e IP |
| L-03 | Log de cambios de estado | Traslados registran cada cambio de estado en `historial_estado` con usuario, fecha y estado anterior/nuevo |
| L-04 | Formato estructurado | Logs incluyen: nivel (CRITICAL, ERROR), mensaje, archivo, línea, stack trace y contexto |
| L-05 | Rotación diaria | Archivo de log por día, evitando crecimiento indefinido |
| L-06 | Sin datos sensibles en logs | No se registran passwords, tokens completos ni contenido de documentos |
| L-07 | Log de subida de documentos | Se registra quién subió cada documento y cuándo |

### 2.6 Política de Protección de Datos Personales

| ID | Regla | Detalle | Ley 18.331 |
|----|-------|---------|------------|
| D-01 | Residencia de datos | Almacenamiento exclusivo en servidores del DTI del Hospital de Clínicas | Art. 23 (transferencia internacional) |
| D-02 | Datos mínimos | Solo se recolectan datos estrictamente necesarios: nombre, apellido, email, documento | Art. 5 (calidad de datos) |
| D-03 | Acceso restringido | Solo funcionarios autenticados con rol admin/superadmin pueden ver datos de pacientes | Art. 9 (seguridad) |
| D-04 | Documento de identidad | Almacenado como `VARCHAR(20)` con UNIQUE constraint; evita duplicados | Art. 5 (veracidad) |
| D-05 | Foto de perfil | Almacenada como `LONGBLOB` en la base de datos, sin copia en disco | Art. 9 (seguridad) |
| D-06 | Consentimiento implícito | El paciente acepta el tratamiento de sus datos al recibir su token de acceso | Art. 8 (consentimiento) |
| D-07 | Derecho de acceso | El usuario puede ver sus datos en `/perfil` en cualquier momento | Art. 13 (acceso) |
| D-08 | Derecho de rectificación | El usuario puede modificar email, teléfono, foto y contraseña desde su perfil | Art. 14 (rectificación) |

---

## 3. Reglas de Desarrollo Backend

### 3.1 Estándares de Código

| ID | Regla | Detalle | Obligatorio |
|----|-------|---------|-------------|
| B-01 | **Tipado estricto** | Todo archivo PHP debe comenzar con `declare(strict_types=1)` | Sí |
| B-02 | **PSR-4 autoloading** | Namespaces y directorios deben coincidir con PSR-4 | Sí |
| B-03 | **PSR-12 estilo** | El código debe seguir PSR-12 (llaves, indentación, espacios) | Sí |
| B-04 | **Sin errores silenciados** | No usar `@` para suprimir errores | Sí |
| B-05 | **Sin funciones deprecadas** | No usar `mysql_*`, `ereg_*`, `create_function()`, etc. | Sí |
| B-06 | **Sin eval, extract, compact** | Estas funciones están prohibidas | Sí |
| B-07 | **Sin goto** | Prohibido | Sí |
| B-08 | **Variables definidas** | Toda variable debe ser inicializada antes de usarse | Sí |
| B-09 | **Comentarios mínimos** | El código debe ser autoexplicativo; no agregar comentarios obvios | Recomendado |
| B-10 | **Nombres en inglés** | Clases, métodos y variables en inglés (salvo vistas y contenido de UI) | Sí |
| B-11 | **Métodos cortos** | Un método no debe superar ~40 líneas | Recomendado |
| B-12 | **Una responsabilidad por clase** | Principio de responsabilidad única (SRP) | Sí |

### 3.2 Reglas de Arquitectura (Hexagonal)

| ID | Regla | Detalle | Sanción |
|----|-------|---------|---------|
| B-20 | **Capa Domain** | Entidades, Value Objects e interfaces de repositorio. Sin dependencias de infraestructura | ❌ Error |
| B-21 | **Capa Application** | Casos de uso y servicios de aplicación. Depende solo de Domain | ❌ Error |
| B-22 | **Capa Infrastructure** | Implementaciones concretas (controladores, repositorios PDO, servicios). Depende de Domain y Application | ❌ Error |
| B-23 | **Entities solo getters/setters** | Las entidades no deben contener lógica de negocio compleja ni acceso a BD | ❌ Error |
| B-24 | **Controladores delgados** | Los controladores solo validan entrada, llaman servicios y renderizan vistas | ❌ Error |
| B-25 | **Repositorios solo datos** | Los repositorios solo hacen CRUD, no lógica de negocio | ❌ Error |
| B-26 | **Vistas sin lógica** | Las vistas solo renderizan datos recibidos del controlador; sin queries ni lógica de negocio | ❌ Error |
| B-27 | **Inyección de dependencias** | Las dependencias se pasan por constructor, no se instancian dentro de la clase | Recomendado |

### 3.3 Reglas de Base de Datos

| ID | Regla | Detalle |
|----|-------|---------|
| B-30 | **Prepared statements siempre** | Toda consulta SQL debe usar `PDO::prepare()` + `execute()`. Prohibida la concatenación de strings para construir queries |
| B-31 | **PDO exceptions** | `PDO::ATTR_ERRMODE` debe ser `PDO::ERRMODE_EXCEPTION` en la conexión |
| B-32 | **Fetch mode explícito** | Usar `PDO::FETCH_ASSOC` o `fetchObject()`; no confiar en el default |
| B-33 | **Transacciones** | Las operaciones que modifican múltiples tablas deben ejecutarse dentro de `beginTransaction()/commit()/rollBack()` |
| B-34 | **Tipos en execute** | Los parámetros deben pasarse en el orden correcto a `execute([$p1, $p2, ...])` |
| B-35 | **Columnas explícitas** | En lo posible, evitar `SELECT *`; nombrar las columnas necesarias |
| B-36 | **Sin procedimientos almacenados** | Toda la lógica en PHP, no en la BD | Recomendado |
| B-37 | **Migraciones** | Los cambios de esquema deben documentarse; no modificar la BD directamente en producción |

### 3.4 Reglas de Validación de Entrada

| ID | Regla | Detalle | Dónde |
|----|-------|---------|-------|
| B-40 | **Validar en servidor siempre** | Nunca confiar solo en validación del cliente (HTML/JS) | Controlador |
| B-41 | **Sanitizar antes de usar** | `trim()`, casting de tipos, filtros (`filter_var()`) antes de procesar | Controlador |
| B-42 | **Escape en la salida** | `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` en toda salida HTML | Vista |
| B-43 | **Validar formato** | Email: `filter_var($email, FILTER_VALIDATE_EMAIL)`. Teléfono: regex. CI: regex 8 dígitos | Controlador |
| B-44 | **Validar rangos** | IDs numéricos: `(int)` + verificar > 0. Paginación: mínimo 1 | Controlador |
| B-45 | **Rechazar datos inválidos** | Si un campo no pasa validación, mostrar error al usuario y no procesar | Controlador |
| B-46 | **No confiar en métodos HTTP** | Validar datos POST/GET/REQUEST con el mismo rigor independientemente del método | Controlador |
| B-47 | **CSRF en todo POST** | Todo formulario POST debe incluir y validar token CSRF | Middleware |

### 3.5 Reglas de Manejo de Errores

| ID | Regla | Detalle |
|----|-------|---------|
| B-50 | **Excepciones, no return codes** | Usar `try/catch`/`throw` en lugar de códigos de error numéricos |
| B-51 | **No silenciar excepciones** | Capturar excepciones solo si se va a manejar; no dejar `catch` vacíos |
| B-52 | **Loggear siempre** | Toda excepción capturada debe registrarse en el log |
| B-53 | **No exponer detalles al usuario** | En producción, mostrar mensaje genérico; los detalles van al log |
| B-54 | **ErrorHandler global** | Usar `set_exception_handler()` y `set_error_handler()` para capturar no capturadas |
| B-55 | **HTTP status codes correctos** | 200 (éxito), 400 (bad request), 401 (no auth), 403 (forbidden), 404 (not found), 500 (error) |
| B-56 | **Recursos liberados** | En `finally` o después de usar, cerrar recursos (archivos, streams) si no se usa GC automático |

### 3.6 Reglas de Sesión y Autenticación

| ID | Regla | Detalle |
|----|-------|---------|
| B-60 | **Password hashing** | Siempre `password_hash($pwd, PASSWORD_BCRYPT)`, nunca hash propio |
| B-61 | **Password verification** | Siempre `password_verify()`, nunca comparación directa de hashes |
| B-62 | **Regenerar ID post-login** | `session_regenerate_id(true)` después de autenticar |
| B-63 | **Destruir sesión en logout** | `session_destroy()` + eliminar cookie |
| B-64 | **Timeout de sesión** | Verificar `$_SESSION['last_activity']` en cada request; redirigir a login si excede 30 min |
| B-65 | **Verificar auth en cada ruta protegida** | No asumir que el middleware ya verificó; verificar en el controlador si es necesario |
| B-66 | **No almacenar contraseñas en sesión** | Guardar solo `user_id`, `user_nombre` y `user_rol` |
| B-67 | **CSRF token por sesión** | Generar `bin2hex(random_bytes(32))` una vez por sesión; validar en cada POST |

### 3.7 Reglas de Archivos

| ID | Regla | Detalle |
|----|-------|---------|
| B-70 | **Validar con finfo** | Usar `finfo(FILEINFO_MIME_TYPE)`, no confiar en `$_FILES['file']['type']` |
| B-71 | **Allowlist de extensiones** | Solo extensiones permitidas; no usar denylist |
| B-72 | **Límite de tamaño** | Validar `$_FILES['file']['size']` contra máximo definido |
| B-73 | **Renombrar archivos** | No usar el nombre original del usuario; generar nombre seguro (prefijo + timestamp) |
| B-74 | **Fuera de document root** | Almacenar en `storage/` (fuera de `public/`), servidos por controlador |
| B-75 | **Imágenes: verificar contenido** | Usar `imagecreatefromstring()` para validar que es una imagen real |
| B-76 | **Sin permisos de ejecución** | Los archivos subidos no deben tener permisos 755 (usar 644) |

### 3.8 Reglas de Logging

| ID | Regla | Detalle |
|----|-------|---------|
| B-80 | **Loggear errores** | Toda excepción no manejada debe ir al log |
| B-81 | **Loggear autenticación** | Intentos de login fallidos (username, IP, timestamp) |
| B-82 | **Loggear operaciones críticas** | Altas, bajas y modificaciones de datos sensibles |
| B-83 | **Sin datos sensibles** | No loguear passwords, tokens completos, contenido de documentos |
| B-84 | **Formato estructurado** | Incluir: timestamp, nivel, mensaje, archivo, línea, contexto |
| B-85 | **Rotación de logs** | Un archivo por día; configurar rotación para evitar llenar disco |

### 3.9 Reglas de Git y Commits

| ID | Regla | Detalle |
|----|-------|---------|
| B-90 | **Commits atómicos** | Un commit por cambio lógico; no mezclar cambios no relacionados |
| B-91 | **Mensajes descriptivos** | Formato: `tipo: descripción breve` (ej: `fix: validar CI duplicado en registro`) |
| B-92 | **Sin secrets en el repo** | No committear `.env`, contraseñas, tokens, claves privadas |
| B-93 | **No committear dependencias** | `vendor/` y `node_modules/` en `.gitignore` |
| B-94 | **No committear storage** | `storage/logs/`, `storage/rate-limit/`, `storage/docs/` en `.gitignore` |
| B-95 | **Ramas features** | Trabajar en ramas, no directamente en `main` |
| B-96 | **PR con revisión** | Todo merge a `main` debe pasar por Pull Request |

### 3.10 Reglas de Dependencias

| ID | Regla | Detalle |
|----|-------|---------|
| B-100 | **Composer privado** | Solo usar paquetes de Packagist oficial; no agregar repositorios VCS no verificados |
| B-101 | **Versionado explícito** | Definir versiones en `composer.json` con constraint (`^x.y`) |
| B-102 | **Lock file** | Committear `composer.lock` para entornos reproducibles |
| B-103 | **Auditar dependencias** | Revisar `composer audit` periódicamente en busca de vulnerabilidades |
| B-104 | **Sin dependencias innecesarias** | Evaluar si realmente se necesita antes de agregar un paquete |

### 3.11 Reglas de Vistas (Frontend PHP)

| ID | Regla | Detalle |
|----|-------|---------|
| B-110 | **Sin lógica de negocio en vistas** | Las vistas solo renderizan datos; no hacen queries ni procesan datos |
| B-111 | **Escape obligatorio** | Toda variable que contenga entrada de usuario debe pasar por `htmlspecialchars()` |
| B-112 | **Sin PHP tags cortos** | Usar `<?php` en lugar de `<?` o `<?=` (excepto `<?=` con `echo` implícito en PHP 8+) |
| B-113 | **Separación de layout** | Usar `require __DIR__ . '/../layout/base.php'` con `$contenido` para heredar layout |
| B-114 | **Sin SQL en vistas** | Prohibido hacer consultas a la BD dentro de archivos de vista |

---

## 4. Arquitectura de Seguridad

### 4.1 Capas de Defensa (Defense in Depth)

```
┌─────────────────────────────────────────────────────────────┐
│                CAPA 1: FÍSICA / INFRAESTRUCTURA              │
│  • Servidores del DTI del Hospital de Clínicas (Piso 6)     │
│  • Red interna del hospital (sin exposición directa a       │
│    Internet en equipos administrativos)                     │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                CAPA 2: RED Y TRANSPORTE                     │
│  • HTTPS forzado en producción                              │
│  • HSTS (Strict-Transport-Security)                         │
│  • Firewall perimetral del DTI                              │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                CAPA 3: APLICACIÓN (CÓDIGO)                   │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Middleware Chain:                                    │    │
│  │  • Rate Limiting (rutas públicas)                    │    │
│  │  • CSRF Token Validation (formularios POST)          │    │
│  │  • CSP + Security Headers                            │    │
│  ├─────────────────────────────────────────────────────┤    │
│  │ Router:                                              │    │
│  │  • Autenticación obligatoria (excepto rutas públicas)│    │
│  │  • Verificación de rol por endpoint                  │    │
│  ├─────────────────────────────────────────────────────┤    │
│  │ Controladores:                                       │    │
│  │  • Validación y sanitización de toda entrada         │    │
│  │  • Escape de toda salida con htmlspecialchars()      │    │
│  │  • declare(strict_types=1) en todo el código         │    │
│  ├─────────────────────────────────────────────────────┤    │
│  │ Repositorios:                                        │    │
│  │  • Prepared statements (PDO) en 100% de las queries  │    │
│  │  • PDO::ERRMODE_EXCEPTION (sin silenciar errores)    │    │
│  └─────────────────────────────────────────────────────┘    │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                CAPA 4: DATOS                                 │
│  • Contraseñas con bcrypt (PASSWORD_BCRYPT, cost 12+)       │
│  • Archivos fuera del document root (storage/docs/)         │
│  • Foto de perfil en BLOB (sin archivos en disco)           │
│  • Unique constraints en email y documento_identidad        │
│  • Claves foráneas con integridad referencial               │
└─────────────────────────────────────────────────────────────┘
```

### 4.2 Diagrama de Flujo Seguro

```
Cliente (navegador)
     │
     │ HTTPS
     ▼
┌─────────────────────────────┐
│  Servidor Web (Apache/Nginx)│  ← Filtro IP del DTI
│  puerto 443 (HTTPS)         │
└───────────┬─────────────────┘
            │
            ▼
┌─────────────────────────────┐
│  Front Controller           │
│  public/index.php           │
│  • set_error_handler()      │
│  • Session::start()         │
│  • Carga de rutas           │
└───────────┬─────────────────┘
            │
            ▼
┌─────────────────────────────┐
│  Middleware Chain (orden)   │
│                             │
│  1. Rate Limiting           │  ← Protege rutas públicas
│     (límite por IP/minuto)  │     de DoS/DDoS
│                             │
│  2. CSRF Check              │  ← Verifica token en todo
│     (CsrfMiddleware)         │     formulario POST
│                             │
│  3. Security Headers        │  ← CSP, X-Frame-Options,
│     (CSP, HSTS, XSS, etc.)  │     X-Content-Type-Options
└───────────┬─────────────────┘
            │
            ▼
┌─────────────────────────────┐
│  Verificación de Sesión     │
│                             │
│  ¿Ruta pública? ──Sí──►    │  ← /login, /registro,
│      │                      │     /publico/*
│      No                     │
│      ▼                      │
│  ¿Hay sesión activa? ──No──►│  ← Redirige a /login
│      │                      │
│      Sí                     │
│      ▼                      │
│  Cargar controlador         │
└───────────┬─────────────────┘
            │
            ▼
┌─────────────────────────────┐
│  Controlador específico     │
│                             │
│  • requireRole() si aplica  │
│  • Validación de entrada    │
│  • Lógica de negocio        │
│  • Renderizado de vista     │
│    con htmlspecialchars()   │
└───────────┬─────────────────┘
            │
            ▼
┌─────────────────────────────┐
│  Repositorio (PDO)          │
│                             │
│  • Prepared statements      │
│  • Manejo de excepciones    │
│  • Escape de errores        │
│    (sin leak de info)       │
└───────────┬─────────────────┘
            │
            ▼
       MySQL/MariaDB
    (red interna del hospital)
```

### 4.3 Seguridad en la Base de Datos

| Aspecto | Implementación |
|---------|---------------|
| **Conexión** | PDO con credenciales desde `.env` (archivo excluido del repositorio vía `.gitignore`) |
| **SQL Injection** | 100% prepared statements con `PDO::prepare()` + `execute()` — nunca concatenación de strings |
| **Modo de errores** | `PDO::ERRMODE_EXCEPTION` — las excepciones se capturan en el ErrorHandler y se registran en logs sin exponer detalles al usuario |
| **Contraseñas** | `VARCHAR(255)` con hash bcrypt (>60 caracteres, costo 12) |
| **Datos sensibles** | Foto almacenada como `LONGBLOB`; contraseñas hasheadas; documentos PDF en disco fuera del document root |
| **Unique constraints** | `email` (UNIQUE) y `documento_identidad` (UNIQUE) — los errores de duplicado se capturan y muestran mensaje amigable sin leak de información |
| **Integridad referencial** | Claves foráneas con `ON DELETE CASCADE` donde corresponde (ej: `pregunta → encuesta`, `respuesta → pregunta`) |
| **Tipo de tabla** | InnoDB (transaccional, con soporte de FK) |

### 4.4 Seguridad en la Red y Transporte

| Aspecto | Estado | Detalle técnico |
|---------|--------|-----------------|
| **HTTPS** | ⬜ Producción | Configuración de Apache/Nginx del DTI con certificado válido |
| **HSTS** | ✔ Implementado | Header `Strict-Transport-Security: max-age=31536000; includeSubDomains` |
| **Content Security Policy** | ✔ Implementado | `default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'self'; object-src 'self'; base-uri 'self'; form-action 'self'` |
| **X-Frame-Options** | ✔ Implementado | `SAMEORIGIN` — previene clickjacking |
| **X-Content-Type-Options** | ✔ Implementado | `nosniff` — previene MIME sniffing |
| **X-XSS-Protection** | ✔ Implementado | `0` (desactivado en favor de CSP) |
| **Referrer-Policy** | ✔ Implementado | `strict-origin-when-cross-origin` |
| **Rate Limiting** | ✔ Implementado | Almacenamiento en `storage/rate-limit/`, límite configurable por IP en rutas públicas |
| **Red** | ✔ Interna | Servidores del DTI, red interna del hospital |

---

## 5. Protocolos de Seguridad Implementados

### 5.1 Protección contra CSRF (Cross-Site Request Forgery)

**Qué es**: Un atacante engaña al navegador de un usuario autenticado para que ejecute acciones no deseadas en una aplicación web.

**Implementación**:

```
1. En la vista:
   <input type="hidden" name="_csrf_token"
          value="<?= SessionManager::getCsrfToken() ?>">

2. En el middleware:
   CsrfMiddleware::handle()
     → Verifica que el token POST coincida con el de sesión
     → Si no coincide: HTTP 400, registro en log, rechazo

3. Generación del token:
   SessionManager::getCsrfToken()
     → Si no existe en sesión, genera bin2hex(random_bytes(32))
     → Lo almacena en $_SESSION['csrf_token']
```

**Cobertura**: Todos los formularios del sistema (login, registro, perfil, documentos, encuestas, traslados, conductores, rutas).

### 5.2 Protección contra XSS (Cross-Site Scripting)

**Qué es**: Un atacante inyecta scripts maliciosos en páginas web vistas por otros usuarios.

**Implementación**:

```
Todas las vistas PHP usan htmlspecialchars($variable, ENT_QUOTES, 'UTF-8')
en cada salida de datos generados por el usuario:

  • Nombre, apellido, email → $user->getNombre()
  • CI → $ciPaciente->getDocumentoIdentidad()
  • Títulos de documentos → $doc['titulo']
  • Preguntas de encuestas → $pregunta->getTexto()
  • Descripciones → todas las salidas de texto

Además:
  • CSP bloquea scripts inline no autorizados
  • X-XSS-Protection: 0 (delegamos en CSP)
  • Tipado estricto (declare(strict_types=1)) evita type juggling
```

### 5.3 Protección contra SQL Injection

**Qué es**: Un atacante inserta código SQL malicioso en los parámetros de entrada para manipular la base de datos.

**Implementación**:

```php
// ✅ SEGURO: Prepared statement con marcadores de posición
$stmt = $this->pdo->prepare("UPDATE usuario SET nombre = ?, email = ? WHERE id = ?");
$stmt->execute([$nombre, $email, $id]);

// ❌ NUNCA: Concatenación de strings
// $this->pdo->query("UPDATE usuario SET nombre = '$nombre' WHERE id = $id");
```

**Cobertura**: 100% de las consultas SQL en todos los repositorios. El código base no contiene ni una sola consulta construida por concatenación de strings con variables de usuario.

**Verificación adicional**: `declare(strict_types=1)` fuerza tipos correctos en los parámetros.

### 5.4 Protección de Sesión

**Qué es**: Conjunto de medidas para proteger la sesión del usuario contra secuestro, fijación y ataques relacionados.

**Implementación**:

| Medida | Implementación | Archivo |
|--------|---------------|---------|
| Cookie HTTP-only | `session.cookie_httponly = 1` | Config PHP |
| Cookie Secure | `session.cookie_secure = 1` (en producción) | Config PHP |
| Solo cookies | `session.use_only_cookies = 1` | Config PHP |
| Sin ID en URL | `session.use_trans_sid = 0` | Config PHP |
| Timeout 30 min | `session.gc_maxlifetime = 1800` | Config PHP |
| Regeneración post-login | `session_regenerate_id(true)` | AuthController |
| Destrucción al logout | `session_destroy()` + `setcookie(session_name(), '', time() - 3600, '/')` | AuthController |

### 5.5 Protección de Contraseñas

**Qué es**: Almacenamiento y verificación segura de contraseñas.

**Implementación**:

```php
// Creación de hash (costo 12 por defecto)
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verificación
if (password_verify($password, $hashAlmacenado)) {
    // autenticación exitosa
}

// El hash bcrypt incluye automáticamente:
//   • Salt aleatorio (16 bytes)
//   • Costo adaptativo (12 rounds ≈ 250ms por verificación)
//   • Formato: $2y$12$...
```

**Propiedades de bcrypt**:
- Resistente a ataques de fuerza bruta por su costo computacional.
- Cada hash incluye salt único, previniendo ataques de rainbow tables.
- El costo se puede aumentar con el tiempo sin romper hashes existentes.

### 5.6 Content Security Policy (CSP)

**Qué es**: Política que define qué recursos puede cargar y ejecutar el navegador, mitigando XSS y otras inyecciones.

**Directivas implementadas**:

```
default-src 'self';                              # Por defecto, solo mismo origen
script-src 'self'                                 # Scripts solo de:
  https://cdn.jsdelivr.net                        #   Bootstrap JS
  https://cdnjs.cloudflare.com                    #   Chart.js
  'unsafe-inline';                                #   (necesario para inline actual)
style-src 'self'                                  # Estilos solo de:
  https://cdn.jsdelivr.net                        #   Bootstrap CSS
  https://cdnjs.cloudflare.com                    #   Chart.js CSS
  'unsafe-inline';                                #   (necesario para inline actual)
img-src 'self' data:;                             # Imágenes: mismo origen + data: (para fotos base64)
font-src 'self' https://cdn.jsdelivr.net;         # Fuentes
connect-src 'self';                               # XHR/Fetch solo mismo origen
frame-src 'self';                                 # Iframes solo mismo origen
object-src 'self';                                # Plugins solo mismo origen
base-uri 'self';                                  # Base URI restringido
form-action 'self';                               # Formularios solo a mismo origen
```

### 5.7 Headers de Seguridad HTTP

Se envían en todas las respuestas HTTP del sistema:

```
Content-Security-Policy: (ver sección 5.6)
X-Content-Type-Options: nosniff                   # Evita MIME sniffing
X-Frame-Options: SAMEORIGIN                       # Evita clickjacking
X-XSS-Protection: 0                               # Desactiva heurística antigua del navegador
Referrer-Policy: strict-origin-when-cross-origin   # Controla información de referente
Strict-Transport-Security: max-age=31536000;       # HSTS (producción)
  includeSubDomains
```

### 5.8 Rate Limiting

**Qué es**: Control de la frecuencia de solicitudes para prevenir abusos, fuerza bruta y denegación de servicio.

**Implementación**:
- Aplica a rutas públicas (`/publico/*`).
- Almacenamiento en archivos en `storage/rate-limit/`.
- Límite de requests por IP por minuto (configurable).
- Si se excede: HTTP 429 (Too Many Requests) con mensaje explicativo.

### 5.9 Validación y Sanitización de Entrada

**Qué es**: Verificación de que todos los datos ingresados por el usuario cumplen con el formato y tipo esperado.

**Implementación**:

| Campo | Validación |
|-------|-----------|
| Email | `filter_var($email, FILTER_VALIDATE_EMAIL)` |
| Teléfono | `/^[0-9]{8,9}$/` (8 o 9 dígitos) |
| CI (cédula) | `/^\d{8}$/` (exactamente 8 dígitos) |
| Contraseña | `strlen($password) >= 6` |
| ID numérico | `(int) $valor` con casting explícito |
| Strings | `trim()` + `htmlspecialchars()` en salida |
| Archivos | MIME + extensión + tamaño + `imagecreatefromstring()` (imágenes) |

**Además**: `declare(strict_types=1)` en todos los archivos PHP garantiza que los tipos escalares pasados a funciones coincidan exactamente con los declarados.

### 5.10 Subida Segura de Archivos

**Flujo completo de validación**:

```
1. Verificar error de upload (UPLOAD_ERR_OK)
2. Validar tipo MIME con finfo (no confiar en extensión ni mime del cliente)
3. Validar extensión contra allowlist
4. Validar tamaño máximo
5. Para imágenes: verificar con imagecreatefromstring() (evita archivos maliciosos con extensión de imagen)
6. Renombrar archivo: prefijo_descriptivo_timestamp.ext
7. Almacenar fuera del document root
```

### 5.11 Protección contra Path Traversal

**Qué es**: Ataque que manipula rutas de archivos para acceder a directorios no autorizados.

**Implementación**:
- Los archivos se almacenan con nombres generados por el sistema (prefijo + timestamp), no con el nombre original del usuario.
- No se permite al usuario especificar rutas de archivo.
- Los archivos se sirven mediante un controlador, no directamente desde el sistema de archivos.

### 5.12 Protección contra Session Fixation

**Qué es**: Ataque donde el atacante fuerza a la víctima a usar un ID de sesión conocido.

**Implementación**:
- `session_regenerate_id(true)` se llama inmediatamente después de un login exitoso, invalidando cualquier ID de sesión previo.
- La sesión se destruye completamente en el logout.

### 5.13 Protección contra Clickjacking

**Qué es**: Técnica donde el atacante incrusta la página objetivo en un iframe transparente para engañar al usuario.

**Implementación**:
- Header `X-Frame-Options: SAMEORIGIN` — solo permite iframes del mismo origen.
- CSP con `frame-src 'self'` como capa adicional.

### 5.14 MIME Sniffing Prevention

**Qué es**: Técnica donde el navegador intenta adivinar el tipo MIME de un recurso, potencialmente ejecutando contenido malicioso.

**Implementación**:
- Header `X-Content-Type-Options: nosniff` — obliga al navegador a respetar el Content-Type declarado.

### 5.15 Manejo Seguro de Errores

**Qué es**: Gestión de errores que no expone información sensible de la aplicación al usuario.

**Implementación**:
- Entorno de producción: `error_reporting(0)`, `display_errors = 0`.
- Todos los errores/excepciones se registran en `storage/logs/` con detalle completo.
- Al usuario se le muestra una página genérica "Error interno del servidor".
- Entorno de desarrollo: `APP_DEBUG=true` muestra errores detallados (solo en el entorno de desarrollo).

---

## 6. Principales Amenazas

### 6.1 Metodología STRIDE

Se aplica el modelo **STRIDE** (Microsoft) para categorizar amenazas por tipo:

| Tipo | Descripción | Ejemplo en Elyra |
|------|-------------|------------------|
| **S**poofing | Suplantación de identidad | Login con credenciales robadas |
| **T**ampering | Manipulación de datos | Modificar un documento o respuesta de encuesta |
| **R**epudiation | Negación de acciones | Un admin niega haber eliminado un documento |
| **I**nformation Disclosure | Exposición de información | Un paciente ve documentos de otro paciente |
| **D**enial of Service | Denegación de servicio | Abuso de la ruta pública de descarga de QR |
| **E**levation of Privilege | Elevación de privilegios | Un conductor accede al panel de admin |

### 6.2 Matriz de Amenazas

| ID | Amenaza | STRIDE | Activo | Prob. | Impacto | Riesgo | Controles |
|----|---------|--------|--------|-------|---------|--------|-----------|
| T-01 | **SQL Injection** en formularios | T | BD | Baja | Crítico | Medio | Prepared statements 100% (5.3) |
| T-02 | **XSS** en campos de texto | T, I | Navegador | Media | Alto | Alto | `htmlspecialchars()` (5.2), CSP (5.6) |
| T-03 | **CSRF** en formularios admin | T, E | Sesión | Media | Alto | Alto | Token CSRF (5.1) |
| T-04 | **Session Fixation** | S | Sesión | Baja | Alto | Medio | Regeneración post-login (5.12) |
| T-05 | **Fuerza bruta** a login | S | Cuentas | Alta | Alto | **Alto** | Pendiente bloqueo (ver R-P-01) |
| T-06 | **Subida de archivo malicioso** | T | Servidor | Baja | Crítico | Medio | Validación MIME + extensión + tamaño (5.10) |
| T-07 | **Path Traversal** | I, T | Archivos | Baja | Alto | Medio | Nombres seguros, controlador intermedio (5.11) |
| T-08 | **Acceso no autorizado** a rutas admin | E, I | Datos | Media | Crítico | Alto | Autenticación + roles (2.3) |
| T-09 | **Fuga de info** en errores | I | Configuración | Baja | Medio | Bajo | ErrorHandler en producción (5.15) |
| T-10 | **DoS** en rutas públicas | D | Disponibilidad | Media | Alto | Alto | Rate limiting (5.8) |
| T-11 | **Timing attack** a bcrypt | S | Hashes | Baja | Bajo | Bajo | bcrypt es inherentemente resistente |
| T-12 | **Clickjacking** | E, I | UI | Baja | Medio | Bajo | X-Frame-Options + CSP (5.13) |
| T-13 | **Man-in-the-Middle** | I, T | Tráfico | Media | Alto | Alto | HTTPS en producción |
| T-14 | **Exposición de datos en logs** | I | Datos | Baja | Alto | Bajo | Logs sin datos sensibles (2.5) |
| T-15 | **Cross-Site History Manipulation** | I | Navegador | Baja | Bajo | Bajo | CSP + Referrer-Policy |
| T-16 | **Robo de token QR** | S, I | Acceso paciente | Media | Medio | Medio | Token UUID v4, asociado a paciente específico |
| T-17 | **Inyección de cabeceras HTTP** | T | Respuesta | Baja | Medio | Bajo | Headers fijos, sin entrada de usuario en headers |

### 6.3 Modelado de Amenazas por Módulo

#### Módulo de Identidad

```
Login ───────────── T-05 (fuerza bruta), T-04 (fixation), T-11 (timing)
  │
  ▼
Sesión ──────────── T-03 (CSRF), T-08 (acceso no autorizado)
  │
  ▼
Perfil ──────────── T-01 (SQLi en edición), T-02 (XSS en campos),
                    T-06 (subida de foto maliciosa)
  │
  ▼
Logout ──────────── T-04 (sesión no destruida correctamente)
```

#### Módulo de Documentación

```
Subir archivo ───── T-06 (archivo malicioso), T-07 (path traversal),
                    T-01 (SQLi en metadatos)
  │
  ▼
Listar/buscar ───── T-01 (SQLi en búsqueda), T-08 (acceso a documentos
  │                  de otro paciente)
  ▼
Vista QR ────────── T-10 (DoS en QR), T-16 (robo de token QR)
  │
  ▼
Encuestas ───────── T-02 (XSS en preguntas/respuestas), T-03 (CSRF
                    en creación/edición)
```

#### Módulo de Ambulancias

```
Registrar ───────── T-01 (SQLi), T-02 (XSS), T-03 (CSRF)
  │
  ▼
Estado ──────────── T-03 (CSRF en cambio de estado), T-08 (cambio
                    de estado no autorizado)
  │
  ▼
Historial ───────── T-09 (exposición indebida de datos)
```

---

## 7. Controles Implementados

### 7.1 Controles Técnicos

| ID | Tipo | Descripción | Localización | Protocolo relacionado |
|----|------|-------------|--------------|----------------------|
| C-01 | Preventivo | Prepared statements PDO en 100% de queries | Todos los repositorios | 5.3 (SQLi) |
| C-02 | Preventivo | `htmlspecialchars()` en toda salida HTML | Todas las vistas | 5.2 (XSS) |
| C-03 | Preventivo | Token CSRF + validación en POST | `SessionManager`, `CsrfMiddleware` | 5.1 (CSRF) |
| C-04 | Preventivo | Regeneración de ID de sesión post-login | `AuthController::doLogin()` | 5.12 (Session Fixation) |
| C-05 | Preventivo | Validación MIME con `finfo` + extensión + tamaño | `DocumentoController`, `PerfilController` | 5.10 (Subida segura) |
| C-06 | Preventivo | Archivos fuera de document root | `storage/docs/` | 5.11 (Path Traversal) |
| C-07 | Preventivo | Headers CSP, X-Frame-Options, X-Content-Type | Middleware en `index.php` | 5.6, 5.7, 5.13, 5.14 |
| C-08 | Detectivo | Log de errores y excepciones | `ErrorHandler` | 5.15 (Manejo de errores) |
| C-09 | Detectivo | Log de cambios de estado de traslados | `TrasladoRepository` | 2.5 (Auditoría) |
| C-10 | Preventivo | Rate limiting en rutas públicas | `RateLimiter` middleware | 5.8 (Rate Limiting) |
| C-11 | Preventivo | `declare(strict_types=1)` en todo el código | Todos los archivos PHP | 5.9 (Validación) |
| C-12 | Preventivo | bcrypt para hash de contraseñas | `AuthController`, `PerfilController` | 5.5 (Contraseñas) |
| C-13 | Preventivo | Validación de email con `filter_var()` | `PerfilController` | 5.9 (Validación) |
| C-14 | Preventivo | Cookie HTTP-only + Secure (producción) | Config PHP | 5.4 (Sesión) |
| C-15 | Preventivo | `session_destroy()` en logout | `AuthController::logout()` | 5.4 (Sesión) |

### 7.2 Controles de Configuración

| ID | Configuración | Archivo |
|----|--------------|---------|
| CF-01 | `session.cookie_httponly = 1` | `php.ini` |
| CF-02 | `session.use_only_cookies = 1` | `php.ini` |
| CF-03 | `session.cookie_secure = 1` (en producción) | `php.ini` |
| CF-04 | `session.gc_maxlifetime = 1800` (30 min) | `php.ini` |
| CF-05 | `display_errors = 0` en producción | `index.php` con `APP_DEBUG` |
| CF-06 | `.env` excluido del repositorio (`.gitignore`) | `.gitignore` |
| CF-07 | `storage/` excluido del repositorio | `.gitignore` |
| CF-08 | `vendor/` excluido del repositorio | `.gitignore` |

### 7.3 Mapeo contra PRD

| PRD ID | Requisito | Control(es) | Estado |
|--------|-----------|-------------|--------|
| SEG-01 | Hash bcrypt (cost 12+) | C-12 | ✔ |
| SEG-02 | Timeout de sesión 30 min | CF-04 | ✔ |
| SEG-03 | Cerrar sesión al cerrar navegador | Cookies de sesión (PHP nativas) | ✔ |
| SEG-04 | Prepared statements | C-01 | ✔ |
| SEG-05 | Escape HTML | C-02 | ✔ |
| SEG-06 | Tokens CSRF | C-03 | ✔ |
| SEG-07 | HTTPS forzado | Configuración servidor DTI | ⬜ Producción |
| SEG-08 | Verificación por rol | A-03, T-08 | ✔ |
| SEG-09 | Logs de intentos fallidos | C-08 | ✔ |
| SEG-10 | Validación de entrada | C-11, C-13 | ✔ |
| SEG-11 | Validación archivos (MIME, tamaño) | C-05 | ✔ |
| SEG-12 | Headers de seguridad | C-07 | ✔ |
| NFR-07 | Protección SQLi, XSS, CSRF | C-01, C-02, C-03 | ✔ |

### 7.4 Mapeo contra Ley 18.331

| Ley 18.331 | Artículo | Requisito | Control(es) | Estado |
|------------|----------|-----------|-------------|--------|
| Principio de legalidad | Art. 5 | Datos obtenidos lícitamente | Finalidad documentada en PRD | ✔ |
| Principio de veracidad | Art. 5 | Datos exactos y actualizados | Validación en frontend + backend | ✔ |
| Principio de finalidad | Art. 6 | Uso solo para fin declarado | Segmentación por módulos | ✔ |
| Consentimiento | Art. 8 | Autorización del titular | Términos al recibir token | ✔ |
| Seguridad de datos | Art. 9 | Medidas técnicas y organizativas | Todo el documento | ✔ |
| Deber de confidencialidad | Art. 9 | Secreto profesional | Control de acceso por roles | ✔ |
| Derecho de acceso | Art. 13 | Ver datos personales | `/perfil` | ✔ |
| Derecho de rectificación | Art. 14 | Modificar datos inexactos | Edición de perfil | ✔ |
| Registro de bases de datos | Art. 28 | Registro ante URCDP | ⬜ Pendiente |
| Notificación de violaciones | Art. 9-bis | Reportar a URCDP | Pendiente (procedimiento) |

---

## 8. Plan de Respuesta a Incidentes

### 8.1 Clasificación

| Nivel | Descripción | Tiempo de respuesta | Ejemplo |
|-------|-------------|---------------------|---------|
| **Bajo** | Sin impacto en datos ni disponibilidad | 72 horas | Intento de login fallido aislado |
| **Medio** | Impacto limitado, datos no sensibles | 24 horas | Subida de archivo inválido, CSRF detectado |
| **Alto** | Posible exposición de datos | 4 horas | Intento de SQL injection, XSS reportado |
| **Crítico** | Brecha de datos confirmada o caída del sistema | 1 hora | Acceso no autorizado a datos de pacientes, DoS sostenido |

### 8.2 Procedimiento

```
DETECCIÓN
    │
    ├── Automática: logs de errores, excepciones
    ├── Reporte de usuario: error inesperado
    └── Monitoreo: rate limiting disparado, patrones anómalos
    │
    ▼
CLASIFICACIÓN (según tabla 7.1)
    │
    ▼
CONTENCIÓN
    │
    ├── Crítico:
    │   • Detener servicio web (apache2/nginx stop)
    │   • Revocar sesiones activas
    │   • Notificar a DTI del hospital
    │   • Notificar a URCDP (violación de datos)
    │
    ├── Alto:
    │   • Bloquear IP de origen (fail2ban/iptables)
    │   • Revisar logs de acceso y aplicación
    │   • Rotar credenciales si es necesario
    │
    └── Medio/Bajo:
        • Registrar y monitorear
        • Evaluar si escala a nivel superior
    │
    ▼
ANÁLISIS
    │
    ├── Revisar logs de aplicación: storage/logs/
    ├── Revisar logs de servidor web: /var/log/apache2/
    ├── Identificar vector de ataque
    ├── Determinar alcance (qué datos fueron accedidos/modificados)
    └── Documentar línea de tiempo del incidente
    │
    ▼
MITIGACIÓN
    │
    ├── Aplicar parche si corresponde
    ├── Reforzar controles existentes
    ├── Rotar credenciales afectadas
    └── Notificar a afectados si hay exposición de datos personales
    │
    ▼
DOCUMENTACIÓN
    │
    ├── Reporte de incidente (formato interno)
    ├── Lecciones aprendidas
    ├── Actualizar este documento de seguridad
    └── Notificar a URCDP si aplica (Art. 9-bis Ley 18.331)
```

### 8.3 Notificación a URCDP

Si ocurre una violación de datos personales que implique riesgo para los derechos de los titulares:

1. **Notificar a la URCDP** en el plazo establecido por la normativa vigente.
2. **Informar a los titulares** afectados sobre la naturaleza del incidente, datos comprometidos y medidas adoptadas.
3. **Documentar** el incidente, incluyendo causas, alcance, acciones de contención y medidas correctivas.

---

## 9. Recomendaciones para Producción

| ID | Recomendación | Prioridad | Amenaza que mitiga | Fundamento legal |
|----|--------------|-----------|-------------------|------------------|
| R-P-01 | **Bloqueo por intentos fallidos** de login (5 intentos → bloqueo 15 min) | Alta | T-05 (fuerza bruta) | Ley 18.331 Art. 9 |
| R-P-02 | **Forzar HTTPS exclusivamente** con redirect automático | Alta | T-13 (MITM) | SEG-07, Decreto 66/025 |
| R-P-03 | Configurar **HSTS** con `max-age=31536000; includeSubDomains` | Alta | T-13 (MITM) | OWASP |
| R-P-04 | **Registrar bases de datos** ante URCDP antes del despliegue | Alta | — | Ley 18.331 Art. 28 |
| R-P-05 | Agregar **2FA (TOTP)** para cuentas admin/superadmin | Media | T-05 (fuerza bruta) | Ley 18.331 Art. 9 |
| R-P-06 | Implementar **auditoría de acceso a datos**: registrar qué funcionario consultó qué paciente | Media | T-08, T-09 | Ley 18.331 Art. 9 |
| R-P-07 | Agregar **CSP-Report-Only** para monitorear violaciones antes de forzar | Media | T-02 (XSS) | OWASP |
| R-P-08 | Implementar **límite de sesiones concurrentes** por usuario | Media | T-04, T-08 | Ley 18.331 Art. 9 |
| R-P-09 | Realizar **pruebas de penetración** periódicas (anuales) | Baja | Todas | ENC 2024-2030 |
| R-P-10 | Agregar **versiones de documentos** (no perder archivos al reemplazar) | Baja | T-05 | FR-10 |
| R-P-11 | Implementar **baja lógica de usuarios** (derecho de cancelación) | Media | — | Ley 18.331 Art. 15 |
| R-P-12 | Designar **Delegado de Protección de Datos (DPO)** si corresponde | Media | — | Ley 18.331 |
| R-P-13 | Capacitar a los funcionarios del hospital en **seguridad de la información** | Media | T-02, T-05 | ENC 2024-2030 (cultura) |
| R-P-14 | Implementar **fail2ban** para bloqueo automático de IPs maliciosas | Alta | T-05, T-10 | Decreto 66/025 |

---

## Referencias

- Ley N° 18.331 — Protección de Datos Personales (2008). https://www.impo.com.uy/bases/leyes/18331-2008
- Decreto N° 92/014 — Reglamentación de Ley 18.331. https://www.impo.com.uy/bases/decretos/92-2014
- Decreto N° 66/025 — Seguridad de la información en organismos del Estado. https://www.impo.com.uy/bases/decretos/66-2025
- Ley N° 20.212 (Arts. 78-79) — Obligaciones de seguridad digital. https://www.impo.com.uy/bases/leyes/20212-2023
- Estrategia Nacional de Ciberseguridad 2024-2030 — AGESIC. https://www.gub.uy/agencia-gobierno-electronico-sociedad-informacion-conocimiento/
- Marco de Ciberseguridad del Uruguay (basado en NIST CSF). https://www.gub.uy/agencia-gobierno-electronico-sociedad-informacion-conocimiento/marco-ciberseguridad
- OWASP Top 10 — 2021. https://owasp.org/Top10/
- OWASP Cheat Sheet Series. https://cheatsheetseries.owasp.org/
- NIST SP 800-63 — Digital Identity Guidelines. https://pages.nist.gov/800-63-3/

---

*Documento alineado con el PRD v1.0, la Ley 18.331 de Protección de Datos Personales y la Estrategia Nacional de Ciberseguridad 2024-2030. Revisar y actualizar tras cada sprint o cambio significativo en la arquitectura.*
