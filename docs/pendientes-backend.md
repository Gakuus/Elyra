# Pendientes — Backend Elyra

> **Estado actual:** Nada implementado. Controladores stub (solo renderizan vistas). Sin dominio, sin persistencia, sin seguridad real.
> **Arquitectura:** Hexagonal (Puertos y Adaptadores). PHP 8.4 + MySQL 8. Sin framework. Sin ORM. Sin dependencias externas.

---

## ⚠️ REGLA DE ORO: SEGURIDAD SIEMPRE PRIMERO

> **Cada tarea en este documento incluye requisitos de seguridad explícitos.**
> Ninguna tarea se considera COMPLETADA si no cumple TODOS los requisitos de seguridad especificados para esa tarea.
> Si una tarea no menciona seguridad, aplican las medidas globales de la sección "Checklist de Seguridad Global".

### 🚫 LO QUE NUNCA DEBE PASAR (prohibiciones absolutas)

| # | Prohibición | Ejemplo de lo que NO hacer |
|---|-------------|---------------------------|
| X.1 | **Nunca exponer tokens/credenciales en el frontend** | No `<script>const API_KEY = "abc123"</script>`, no tokens JWT en localStorage, no passwords en JS |
| X.2 | **Nunca enviar credenciales por GET** | No `GET /login?user=admin&pass=admin` (quedan en logs del server, historial, referer) |
| X.3 | **Nunca guardar secrets en texto plano** | No passwords en la DB sin hashear, no API keys en `.env` commiteado |
| X.4 | **Nunca confiar en validación del frontend** | El backend siempre valida TODO otra vez |
| X.5 | **Nunca exponer IDs autoincrementales al público** | Un paciente no debe ver `/documento/42`, debe ver `/documento?token=uuid` |
| X.6 | **Nunca devolver el password_hash en una respuesta JSON** | Ni al login, ni al perfil, ni en listados |
| X.7 | **Nunca mostrar stack traces en producción** | `display_errors=Off`, `set_exception_handler()` con respuesta genérica |
| X.8 | **Nunca concatenar strings en SQL** | Solo prepared statements. Prohibido `"SELECT * FROM users WHERE id = $id"` |
| X.9 | **Nunca subir archivos a webroot** | Los PDFs van a `storage/docs/` (fuera de `public/`), no a `public/uploads/` |
| X.10 | **Nunca usar `extract()` en producción** | Ya está en BaseController, hay que eliminarlo |

### Checklist de Seguridad Global (aplica a TODA tarea)

| # | Medida | Obligatorio | Verificación |
|---|--------|-------------|--------------|
| S.1 | **Prepared statements** en toda consulta SQL | ✅ | Sin `query()`, `exec()`, `real_escape_string()` |
| S.2 | **`htmlspecialchars($var, ENT_QUOTES)`** en toda salida a vistas | ✅ | Sin excepción |
| S.3 | **CSRF token** en todo formulario y fetch POST/PUT/DELETE | ✅ | Validar antes de procesar |
| S.4 | **Validar input** en el backend (no confiar en frontend) | ✅ | Tipo, rango, longitud, whitelist |
| S.5 | **Escapar output** según contexto (HTML, JSON, URL) | ✅ | Cada contexto su escape |
| S.6 | **Mínimo privilegio** en consultas (solo columnas necesarias) | ✅ | Sin `SELECT *` |
| S.7 | **No exponer IDs internos** en URLs públicas | ✅ | Usar tokens/UUIDs |
| S.8 | **Validar autorización** (el usuario puede hacer esto?) | ✅ | Antes de cada operación |
| S.9 | **Transacciones** en operaciones multi-tabla | ✅ | begin/commit/rollback |
| S.10 | **Manejo de errores** sin leak de información | ✅ | No mostrar stack traces |
| S.11 | **Loggear** operaciones sensibles (login, delete, estado change) | ✅ | Con usuario, IP, timestamp |
| S.12 | **Rate limiting** en endpoints públicos y login | ✅ | 5 intentos/15min login, 100 req/min público |

## Mapa de la Arquitectura (lo que hay que construir)

```
src/
├── Domain/                               ↑ CAPA DOMINIO (sin dependencias)
│   ├── Entity/          (14 entidades)   ┊   Reglas de negocio, validación
│   ├── ValueObject/     (6 VOs)          ┊   Tipos inmutables con validación
│   ├── Repository/      (5 interfaces)   ┊   Contratos de persistencia
│   └── Service/         (3 services)     ┊   Lógica de dominio compleja
│
├── Application/                          ↑ CAPA APLICACIÓN (orquestación)
│   ├── UseCases/        (~15 casos)      ┊   Cada caso de uso = 1 operación
│   ├── Ports/Input/     (5 interfaces)   ┊   Puertos de entrada
│   └── Ports/Output/    (3 interfaces)   ┊   Puertos de salida
│
├── Infrastructure/                       ↑ CAPA INFRAESTRUCTURA (implementaciones)
│   ├── Persistence/MySQL/  (8 repos)     ┊   Prepared statements, PDO
│   ├── Web/Controller/     (9 ctrls)     ┊   HTTP, validación request
│   ├── Web/Middleware/     (4 middlewares)┊   Auth, CSRF, rate limit, router
│   ├── Web/Router.php                    ┊   Routeador con parámetros {id}
│   └── Service/           (5 services)   ┊   QR, File, Auth, Session, Mail
│
config/database.php → ELIMINAR (duplicado)
public/index.php    → REFACTOR (middleware pipeline)
```

---

## Convenciones y Decisiones Técnicas

| Aspecto | Decisión |
|---------|----------|
| DB Connection | Clase singleton en `Infrastructure/Persistence/MySQL/Connection.php`. Eliminar `config/database.php`. |
| Queries | Solo prepared statements. **Prohibido** `query()`, `exec()`, `real_escape_string()`. |
| Output | `htmlspecialchars($var, ENT_QUOTES)` en toda salida hacia vistas. |
| Passwords | `password_hash(PASSWORD_ARGON2ID)` para hash. `password_verify()` para verificar. |
| Sesiones | `session_regenerate_id()` en login. Timeout 30 min. HttpOnly + Secure + SameSite=Lax. |
| CSRF | Token por sesión. Header `X-CSRF-Token` en fetches. Campo oculto en formularios. |
| Rutas | Router propio con soporte para `{id}`, `{token}`. Reemplazar array plano actual. |
| Excepciones | `DomainException` para reglas de negocio. `NotFoundException` para 404. Captura en front controller. |
| IDs | Auto-incrementales (como está en schema.sql). UUIDs solo para tokens públicos. |
| Fechas | `DateTimeImmutable` en dominio. `DATETIME` en MySQL. |
| Transacciones | Envolver en `beginTransaction/commit/rollback` cuando hay múltiples inserts. |
| Archivos | Almacenar fuera de webroot (`storage/docs/`). Servir via PHP (no acceso directo). |
| QR | Librería liviana: `chillerlan/php-qrcode` (se agrega a composer.json). |
| Tests | PHPUnit. Unitarios para Domain + Application. Integración para Infrastructure. |

> ⚠️ **Cada tarea debe pasar el Checklist de Seguridad Global (S.1–S.12) antes de marcarse como completada.**

---

## Epica 0 — Infraestructura y Seguridad (Fundación)

**Prioridad:** MUST — requisito para todo lo demás

| # | Tarea | Archivos | Dependencia |
|---|-------|----------|-------------|
| B0.1 | Unificar PDO: eliminar `config/database.php`, migrar a `Connection.php` | `src/Infrastructure/Persistence/MySQL/Connection.php` | — |
| B0.2 | Sistema de routeo con parámetros `{id}`, `{token}` | `src/Infrastructure/Web/Router.php`, refactor `web.php` + `index.php` | — |
| B0.3 | Error handler + logger (`set_exception_handler`, sin stack traces en prod) | `src/Infrastructure/Service/ErrorHandler.php`, refactor `index.php` | — |
| B0.4 | Session manager (start, regenerate, timeout 30min, HttpOnly+Secure+SameSite) | `src/Infrastructure/Service/SessionManager.php` | — |
| B0.5 | Auth service (password_hash Argon2id, verify, login, logout) | `src/Infrastructure/Service/AuthService.php`, refactor `AuthController` | B0.4 |
| B0.6 | CSRF middleware (generar token, validar en POST/PUT/DELETE) | `src/Infrastructure/Web/Middleware/CsrfMiddleware.php` | B0.2 |
| B0.7 | Auth middleware (verificar sesión, redirigir si no autenticado) | `src/Infrastructure/Web/Middleware/AuthMiddleware.php` | B0.2, B0.5 |
| B0.8 | Rate limiter (5 login/15min por IP, 100 req/min público) | `src/Infrastructure/Service/RateLimiter.php` | — |
| B0.9 | Validator helper (sanitize, validate tipos, whitelist, errores) | `src/Infrastructure/Service/Validator.php` | — |
| B0.10 | Refactor `public/index.php`: middleware pipeline completo | `public/index.php` | B0.2–B0.8 |
| B0.11 | View helper: función `h()` como alias de `htmlspecialchars()` | `src/Infrastructure/Web/Helper.php` | — |
| B0.12 | Página 404 (ruta por defecto en Router) | `views/errors/404.php` + controller | B0.2 |
| B0.13 | Eliminar `extract()` de `BaseController::render()`, pasar datos seguros | `src/Infrastructure/Web/Controller/BaseController.php` | — |

**Seguridad entregada (ver Checklist Global S.1–S.12):**
- [ ] S.1 — Prepared statements en todos los repos
- [ ] S.3 — CSRF en todo formulario
- [ ] S.4 — Validación de inputs en Validator
- [ ] S.7 — Tokens no adivinables para sesiones
- [ ] S.10 — Error handler sin stack traces
- [ ] S.11 — Logging de login/logout
- [ ] S.12 — Rate limiting en login
- Passwords con Argon2id
- Session con timeout 30min + regeneración en login
- CSP headers configurados

---

## Epica 0 — Domain Entities y Value Objects

**Prioridad:** MUST — base para toda la lógica de negocio

### Value Objects (inmutables, con validación)

| # | Value Object | Atributos | Validación |
|---|-------------|-----------|------------|
| VO.1 | `Email` | `string value` | Formato email, max 150 chars |
| VO.2 | `CodigoQR` | `string token` | UUID v4, 36 chars |
| VO.3 | `EstadoTraslado` | `string value` | Uno de: pendiente, en_curso, en_destino, en_retorno, completado, cancelado. Método `transicionesPermitidas(): array` |
| VO.4 | `TipoElemento` | `string value` | Uno de: paciente, organo, equipamiento, insumo |
| VO.5 | `TipoPregunta` | `string value` | Uno de: multiple_choice, escala, texto_libre |
| VO.6 | `RolUsuario` | `string value` | Uno de: admin, superadmin, conductor |

**Archivos:** `src/Domain/ValueObject/{Email,CodigoQR,EstadoTraslado,TipoElemento,TipoPregunta,RolUsuario}.php`

### Domain Entities

| # | Entidad | Atributos clave | Comportamiento |
|---|---------|----------------|----------------|
| E.1 | `Usuario` | id, tipo, nombre, apellido, email, documentoIdentidad, createdAt | Value Objects: Email |
| E.2 | `Funcionario` | hereda Usuario, + username, passwordHash, licencia, telefono, activo, rol | Validar rol, activo |
| E.3 | `Paciente` | hereda Usuario, + tokenAcceso, codigoQrId | Generar token |
| E.4 | `CodigoQR` | id, nombre, descripcion | |
| E.5 | `Categoria` | id, nombre, descripcion | |
| E.6 | `Documento` | id, titulo, descripcion, archivoPath, archivoNombre, qrPath, categoria, activo, subidoPor, createdAt | Baja lógica (no DELETE) |
| E.7 | `Encuesta` | id, titulo, descripcion, activa, preguntas[], creadaPor, createdAt | |
| E.8 | `Pregunta` | id, tipo (TipoPregunta), texto, opciones (array), requerida, orden | |
| E.9 | `Respuesta` | id, sesionToken, encuestaId, preguntaId, valorOpcion, valorTexto, valorNumerico | |
| E.10 | `Vehiculo` | id, patente, modelo, anio | |
| E.11 | `Ruta` | id, nombre, origen, destino, distanciaKm | |
| E.12 | `Traslado` | id, codigo, conductor, copiloto, vehiculo, ruta, origen, destino, fechas, estado (EstadoTraslado), observaciones, historial[] | Máquina de estados, generar código |
| E.13 | `ElementoTraslado` | id, tipo (TipoElemento), paciente, descripcion, cantidad | |
| E.14 | `HistorialEstado` | id, estadoAnterior, estadoNuevo, observacion, actualizadoPor, createdAt | Timestamp automático |

**Archivos:** `src/Domain/Entity/{Usuario,Funcionario,Paciente,CodigoQR,Categoria,Documento,Encuesta,Pregunta,Respuesta,Vehiculo,Ruta,Traslado,ElementoTraslado,HistorialEstado}.php`

### Repository Interfaces (puertos de salida)

| # | Interfaz | Métodos principales |
|---|----------|-------------------|
| R.1 | `UsuarioRepositoryInterface` | `findById(int): ?Usuario`, `findByEmail(Email): ?Usuario`, `save(Usuario): void` |
| R.2 | `DocumentoRepositoryInterface` | `findById(int): ?Documento`, `findAll(filters): array`, `search(string, ?int categoria): array`, `save(Documento): void`, `delete(int): void`, `count(?filters): int` |
| R.3 | `EncuestaRepositoryInterface` | `findById(int): ?Encuesta`, `findAll(): array`, `findActivas(): array`, `save(Encuesta): void`, `delete(int): void` |
| R.4 | `TrasladoRepositoryInterface` | `findById(int): ?Traslado`, `findAll(filters): array`, `findActivos(): array`, `findByConductor(int): array`, `save(Traslado): void`, `nextCodigo(): string`, `historial(filters): array` |
| R.5 | `ConductorRepositoryInterface` | `findById(int): ?Funcionario`, `findAll(): array`, `findActivos(): array`, `save(Funcionario): void` |

**Archivos:** `src/Domain/Repository/{UsuarioRepositoryInterface,DocumentoRepositoryInterface,EncuestaRepositoryInterface,TrasladoRepositoryInterface,ConductorRepositoryInterface}.php`

---

## Epica 0 — Seeders (Datos Iniciales)

| # | Seed | Datos |
|---|------|-------|
| S.1 | Admin user | `admin` / `password_hash(PASSWORD_ARGON2ID)` **nunca** texto plano, rol superadmin |
| S.2 | Categorías | Cardiología, Nefrología, Imagenología, Ginecología, Cirugía, Enfermería, Nutrición, Infectología |
| S.3 | Rutas básicas | Montevideo → Paysandú (378km), Montevideo → Salto (496km), Montevideo → Rocha (210km), Montevideo → Rivera (503km), Montevideo → Colonia (177km) |
| S.4 | Vehículos | 2-3 ambulancias con patentes ficticias |

**Archivos:** `database/seeds/seed.php`

---

## Epica 1 — Documentación: Documentos

**Prioridad:** MUST — funcionalidad principal del sistema

| # | Tarea | Archivos | Dependencia |
|---|-------|----------|-------------|
| D.1 | MySQL Repository: `DocumentoRepository` | `src/Infrastructure/Persistence/MySQL/DocumentoRepository.php` | R.2 |
| D.2 | MySQL Repository: `CategoriaRepository` | `src/Infrastructure/Persistence/MySQL/CategoriaRepository.php` | E.5 |
| D.3 | MySQL Repository: `CodigoQRRepository` | `src/Infrastructure/Persistence/MySQL/CodigoQRRepository.php` | E.4 |
| D.4 | Use Case: `SubirDocumentoUseCase` (validar PDF, generar QR, guardar archivo + BD) | `src/Application/UseCases/SubirDocumentoUseCase.php` | D.1–D.3, B0.9 |
| D.5 | Use Case: `ListarDocumentosUseCase` (paginación 10/docs, filtro categoría, búsqueda título) | `src/Application/UseCases/ListarDocumentosUseCase.php` | D.1 |
| D.6 | Use Case: `EditarDocumentoUseCase` (solo título, descripción, categoría) | `src/Application/UseCases/EditarDocumentoUseCase.php` | D.1 |
| D.7 | Use Case: `EliminarDocumentoUseCase` (baja lógica: activo=false) | `src/Application/UseCases/EliminarDocumentoUseCase.php` | D.1 |
| D.8 | Use Case: `VerDocumentoUseCase` (detalle + QR) | `src/Application/UseCases/VerDocumentoUseCase.php` | D.1 |
| D.9 | QR Service: generar código QR con `chillerlan/php-qrcode` | `src/Infrastructure/Service/QRGeneratorService.php` | Composer |
| D.10 | File Storage: guardar PDF en `storage/docs/`, servir por PHP con validación de token | `src/Infrastructure/Service/FileStorageService.php` | — |
| D.11 | Refactor `DocumentoController` (inyectar Use Cases, validar request) | `src/Infrastructure/Web/Controller/DocumentoController.php` | D.4–D.8 |
| D.12 | Endpoint público: ver documento por token QR (sin auth) | `PublicController::verDocumento()` | D.10 |

**Seguridad específica (además del Checklist Global):**
- [ ] Validar MIME type real del PDF (magic bytes, no solo extensión)
- [ ] Tamaño máximo 10MB (validar en PHP, no solo JS)
- [ ] Almacenar PDF fuera de webroot (`storage/docs/`)
- [ ] Token QR: UUIDv4 generado en servidor, no confiar en cliente
- [ ] Path traversal prevention en FileStorageService
- [ ] Al servir PDF, verificar que el token exista y el documento esté activo
- [ ] Rate limiting en endpoint público de descarga (100 req/min/IP)

**Reglas de negocio:**
- Solo PDF, máx 10MB
- QR se genera automáticamente al subir (UUID v4 como token)
- El QR apunta a `/publico/doc?token={uuid}`
- Baja lógica: activo=false, el QR deja de funcionar
- Al editar, el QR existente sigue siendo válido

---

## Epica 2 — Documentación: Encuestas

**Prioridad:** SHOULD

| # | Tarea | Archivos | Dependencia |
|---|-------|----------|-------------|
| EC.1 | MySQL Repository: `EncuestaRepository` | `src/Infrastructure/Persistence/MySQL/EncuestaRepository.php` | R.3 |
| EC.2 | MySQL Repository: `PreguntaRepository` | `src/Infrastructure/Persistence/MySQL/PreguntaRepository.php` | E.8 |
| EC.3 | MySQL Repository: `RespuestaRepository` | `src/Infrastructure/Persistence/MySQL/RespuestaRepository.php` | E.9 |
| EC.4 | Use Case: `CrearEncuestaUseCase` (encuesta + preguntas en transacción) | `src/Application/UseCases/CrearEncuestaUseCase.php` | EC.1–EC.2, B0.9 |
| EC.5 | Use Case: `PublicarEncuestaUseCase` (toggle activa/inactiva) | `src/Application/UseCases/PublicarEncuestaUseCase.php` | EC.1 |
| EC.6 | Use Case: `ResponderEncuestaUseCase` (validar required, guardar, sesión anónima) | `src/Application/UseCases/ResponderEncuestaUseCase.php` | EC.1–EC.3, B0.9 |
| EC.7 | Use Case: `ObtenerResultadosUseCase` (conteos por pregunta, textos libres) | `src/Application/UseCases/ObtenerResultadosUseCase.php` | EC.1–EC.3 |
| EC.8 | Refactor `EncuestaController` | `src/Infrastructure/Web/Controller/EncuestaController.php` | EC.4–EC.7 |

**Seguridad específica (además del Checklist Global):**
- [ ] Sesión anónima con token propio (no usar cookie de auth)
- [ ] Rate limiting: 1 encuesta/5min por sesión
- [ ] Validar que `encuesta_id` exista y esté activa
- [ ] Sanitizar texto libre (XSS al mostrar resultados)
- [ ] No almacenar IP del paciente (anonimato)
- [ ] UK en DB evita doble respuesta (S.1 + S.9)

**Reglas de negocio:**
- Una encuesta puede tener N preguntas (mín 1, máx 20)
- Tipos de pregunta: multiple_choice (opciones en JSON), escala (1-5), texto_libre
- Responder no requiere autenticación (sesión anónima con token)
- Una misma sesión no puede responder la misma pregunta dos veces (UK en DB)
- Solo encuestas activas pueden recibir respuestas

---

## Epica 3 — Ambulancias: Traslados

**Prioridad:** MUST

| # | Tarea | Archivos | Dependencia |
|---|-------|----------|-------------|
| T.1 | MySQL Repository: `TrasladoRepository` | `src/Infrastructure/Persistence/MySQL/TrasladoRepository.php` | R.4 |
| T.2 | MySQL Repository: `ElementoTrasladoRepository` | `src/Infrastructure/Persistence/MySQL/ElementoTrasladoRepository.php` | E.13 |
| T.3 | MySQL Repository: `HistorialEstadoRepository` | `src/Infrastructure/Persistence/MySQL/HistorialEstadoRepository.php` | E.14 |
| T.4 | Use Case: `RegistrarTrasladoUseCase` (conductor, fechas, elemento, transacción) | `src/Application/UseCases/RegistrarTrasladoUseCase.php` | T.1–T.3, B0.9 |
| T.5 | Use Case: `ActualizarEstadoTrasladoUseCase` (máquina de estados + historial) | `src/Application/UseCases/ActualizarEstadoTrasladoUseCase.php` | T.1–T.3 |
| T.6 | Use Case: `ListarTrasladosUseCase` (filtros: estado, conductor, fecha) | `src/Application/UseCases/ListarTrasladosUseCase.php` | T.1 |
| T.7 | Use Case: `VerDetalleTrasladoUseCase` (timeline + todos los datos) | `src/Application/UseCases/VerDetalleTrasladoUseCase.php` | T.1, T.3 |
| T.8 | Use Case: `HistorialTrasladosUseCase` (paginado, filtros avanzados) | `src/Application/UseCases/HistorialTrasladosUseCase.php` | T.1 |
| T.9 | Refactor `TrasladoController` | `src/Infrastructure/Web/Controller/TrasladoController.php` | T.4–T.8 |
| T.10 | Generación automática de código (`TR-{año}-{XXXX}`) | `TrasladoRepository::nextCodigo()` | T.1 |

**Máquina de estados:**
```
Pendiente ──→ En curso ──→ En destino ──→ En retorno ──→ Completado
    │
    └──→ Cancelado (desde cualquier estado antes de Completado)
```

**Seguridad específica (además del Checklist Global):**
- [ ] Validar transiciones de estado en el backend (NO confiar en frontend)
- [ ] Solo admin/superadmin puede cancelar traslados (authorization check)
- [ ] Conductor solo ve sus traslados asignados (scope check)
- [ ] Auditoría: `historial_estado` registra quién, cuándo y desde/hacia qué estado
- [ ] Logging de cada cambio de estado (S.11)
- [ ] Validar que conductor no esté en 2 traslados "en curso" (regla de negocio + concurrencia)
- [ ] Código de traslado secuencial NO expuesto en APIs públicas

**Validaciones:**
- No se puede completar un traslado sin pasar por todos los estados intermedios
- Cancelado requiere motivo (textarea)
- Conductor no puede estar en 2 traslados "en curso" simultáneamente
- Fecha de salida no puede ser anterior a la fecha actual

---

## Epica 4 — Ambulancias: Conductores, Rutas, Vehículos

**Prioridad:** SHOULD

| # | Tarea | Archivos | Dependencia |
|---|-------|----------|-------------|
| CR.1 | MySQL Repository: `ConductorRepository` (extends UsuarioRepository) | `src/Infrastructure/Persistence/MySQL/ConductorRepository.php` | R.5 |
| CR.2 | MySQL Repository: `RutaRepository` | `src/Infrastructure/Persistence/MySQL/RutaRepository.php` | E.11 |
| CR.3 | MySQL Repository: `VehiculoRepository` | `src/Infrastructure/Persistence/MySQL/VehiculoRepository.php` | E.10 |
| CR.4 | Use Cases: `CrearConductorUseCase`, `ListarConductoresUseCase`, `ActualizarConductorUseCase` | `src/Application/UseCases/*Conductor*.php` | CR.1, B0.9 |
| CR.5 | Use Cases: `CrearRutaUseCase`, `ListarRutasUseCase`, `ActualizarRutaUseCase` | `src/Application/UseCases/*Ruta*.php` | CR.2, B0.9 |
| CR.6 | Use Cases: `CrearVehiculoUseCase`, `ListarVehiculosUseCase` | `src/Application/UseCases/*Vehiculo*.php` | CR.3, B0.9 |
| CR.7 | Refactor `ConductorController`, `RutaController` | `src/Infrastructure/Web/Controller/*Controller.php` | CR.4–CR.6 |

**Seguridad específica:**
- [ ] Conductor = Funcionario con rol=conductor. No crear usuario suelto.
- [ ] Al crear conductor, generar password temporal hasheado (Argon2id)
- [ ] No exponer password_hash en respuestas JSON
- [ ] Validar que email sea único (S.1 + UK en DB)

---

## Resumen de Archivos a Crear

| Capa | Cantidad | Archivos |
|------|----------|----------|
| Domain/Entity | 14 | Usuario, Funcionario, Paciente, CodigoQR, Categoria, Documento, Encuesta, Pregunta, Respuesta, Vehiculo, Ruta, Traslado, ElementoTraslado, HistorialEstado |
| Domain/ValueObject | 6 | Email, CodigoQR, EstadoTraslado, TipoElemento, TipoPregunta, RolUsuario |
| Domain/Repository | 5 | UsuarioRepositoryInterface, DocumentoRepositoryInterface, EncuestaRepositoryInterface, TrasladoRepositoryInterface, ConductorRepositoryInterface |
| Domain/Service | 3 | DocumentoService, TrasladoService, EncuestaService (si se necesita lógica跨 entities) |
| Application/UseCases | ~15 | SubirDoc, ListarDoc, EditarDoc, EliminarDoc, VerDoc, CrearEncuesta, PublicarEncuesta, ResponderEncuesta, ObtenerResultados, RegistrarTraslado, ActualizarEstado, ListarTraslados, VerDetalle, Historial, + CRUD conductores/rutas/vehiculos |
| Infrastructure/MySQL | 8 | Connection, Documento, Categoria, CodigoQR, Encuesta, Pregunta, Respuesta, Traslado, ElementoTraslado, HistorialEstado, Conductor, Ruta, Vehiculo |
| Infrastructure/Service | 5 | AuthService, QRGeneratorService, FileStorageService, SessionManager, RateLimiter, Validator, ErrorHandler |
| Infrastructure/Middleware | 4 | Auth, CSRF, Router (reemplaza web.php plano), Helper |
| **Total** | **~60 archivos** | |

---

## Resumen por Prioridad

| Prioridad | Cantidad | Épicas |
|-----------|----------|--------|
| **MUST** | ~35 tareas | Epica 0 (fundación), Epica 0 (entities), Epica 1 (documentos), Epica 3 (traslados) |
| **SHOULD** | ~15 tareas | Epica 2 (encuestas), Epica 4 (conductores/rutas/vehiculos) |
| **COULD** | ~5 tareas | Epica 0 (seeders avanzados, tests) |

## Dependencia Temporal

```
Epica 0 (Fundación + Entities) ──────────────────────────────┐
    │                                                         │
    ├── Epica 1 (Documentos) ────────┐                       │
    │                                │                       │
    ├── Epica 2 (Encuestas) ◄────────┘                       │
    │                                                         │
    └── Epica 3 (Traslados) ────────────────────────────────┘
                                                              │
               Epica 4 (Conductores/Rutas) ◄──────────────────┘
```

- **Epica 0** (infraestructura + entities) es requisito para TODO
- **Epica 1 y 2** son independientes entre sí (pueden hacerse en paralelo)
- **Epica 3** puede empezar después de Epica 0
- **Epica 4** depende de Epica 3 (usa TrasladoRepository)

---

## ⚠️ Recordatorio Final

> **Cada archivo PHP que toques debe cumplir el Checklist de Seguridad Global.**
> **Cada endpoint que expongas debe tener: autenticación (si aplica), autorización, CSRF, validación de input, y rate limiting.**
> **Cada consulta SQL debe ser prepared statement.**
> **Cada salida a HTML debe pasar por `htmlspecialchars()`.**
>
> Si una tarea no tiene sentido de seguridad (ej: crear un Value Object), igual aplican S.2 (output) y S.4 (validación en constructor).
>
> **NO HAY EXCEPCIONES. Si no es seguro, no está terminado.**
