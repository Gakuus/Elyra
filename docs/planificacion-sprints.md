# Planificación de Sprints — Elyra

> **Contexto:** Proyecto de egreso. Arquitectura hexagonal, PHP 8.5 + MySQL, Bootstrap 5 vanilla JS.
> **Estado real (Jul 2026):** Sprint 0 ✅, Sprint 1 ✅, Sprint 2 ✅, Sprint 3 ◐, Sprint 7 ◐. Frontend: ~90% implementado (layout, dashboard, docs, encuestas, traslados, perfil, pública, conductores, rutas, funcionarios, reset password, mapa traslados, tracking conductor, form traslado con catálogo). Backend: Domain layer completo (15 entidades, 9+2 interfaces, 6 VOs), Infrastructure **sólida** (12 repos MySQL, 8+1 servicios). Application layer **completo** (25 use cases en 7 subdirectorios: Documento(5), Traslado(5), Conductor(3), Ruta(3), Encuesta(4), Auth(6), Ubicacion(3)). Todos los controllers refactorizados a use cases. Tests: 241 (PHPUnit, 441 assertions, 0 failures) + 19 escenarios BDD (13 pasan, 6 fallan por módulos no implementados). PHPStan nivel 9: 0 errores (141 archivos). Pre-existing test failure FIXED.
> **Estilo visual:** Web 2.0 retro — Old Facebook (azul `#3B5998`, Tahoma/Verdana, paneles con cabezal azul, sidebar estilo portal, iconos FamFamFam Silk). Windows classic en paneles, modales y botones con gradientes 3D.
> **Enfoque:** Security-first. Cada sprint referencia los protocolos de `docs/seguridad-politicas.md` y cumple los requisitos del `docs/PRD-Product-Requirements-Document.md`.

---

## ⚠️ REGLA FUNDAMENTAL

> **El frontend NUNCA contiene secretos, tokens de API, credenciales ni lógica de autenticación.**
> **El backend SIEMPRE valida todo, aunque el frontend ya lo haya validado.**
> **No hay contraseñas en texto plano. No hay tokens en JS. No hay secrets en `.env` commiteado.**
> **Si se vulnera una de estas reglas, la tarea se rechaza en code review.**
> **PHPStan nivel 9 obligatorio: todo código PHP debe pasar `composer phpstan` sin errores antes de commit.**

---

## Alineación con PRD (MoSCoW)

| Categoría | Cant. PRD | Prioridad en sprints |
|-----------|-----------|---------------------|
| **Must** | 14 FRs | Sprint 0–1 (casi completo) |
| **Should** | 9 FRs | Sprint 1–2 + nuevos |
| **Could** | 1 FR | Sprint 2 |
| **Won't** | 0 | — |

Ver Anexo A para mapeo detallado FR → Sprint.

---

## Arquitectura de Seguridad (transversal a todos los sprints)

| Medida | Implementación | Sprint | Protocolo Seg. |
|--------|---------------|--------|----------------|
| Password hashing | `password_hash(PASSWORD_BCRYPT)` + `password_verify()` | 0 | 5.5 |
| CSRF | Token por sesión en formularios, validación en POST/PUT/DELETE | 0 | 5.1 |
| XSS | `htmlspecialchars($var, ENT_QUOTES)` en toda salida | 0 | 5.2 |
| SQL Injection | Prepared statements PDO (`real_escape_string` prohibido) | 0 | 5.3 |
| Session security | `session_regenerate_id()` en login, HttpOnly+Secure+SameSite, timeout 30min, user-agent binding | 0 | 5.4, 5.12 |
| Rate limiting | Login: 5 intentos/15min por IP. Público: 100 req/min | 0 | 5.8 |
| File upload | MIME type + extensión + tamaño + almacenar fuera de webroot | 1 | 5.10, 5.11 |
| Error handling | `set_exception_handler()` sin stack traces en prod | 0 | 5.15 |
| CSP + Security headers | CSP, X-Frame-Options, X-Content-Type-Options, HSTS | 0 | 5.6, 5.7, 5.13, 5.14 |
| Rol-based access | admin / superadmin / conductor / paciente | 0 | 2.3 |
| Validación de entrada | Sanitizar, tipar, filtrar en backend | 0 | 5.9 |
| User-agent binding | SessionManager valida User-Agent en cada request | 7 | 5.4 |
| Audit logging inmutable | AuditLogger + tabla audit_log (no UPDATE/DELETE) | 7 | R-P-06 |
| CSRF rotation | Token regenerado después de login | 7 | 5.1 |
| Upload rate limiting | RateLimiter para uploads (10/hora por IP) | 7 | 5.8 |
| historial_estado preservado | UPDATE SET NULL en vez de DELETE al borrar traslado | 7 | C-09 |
| DB credentials enforced | Connection.php + database.php requieren env vars | 7 | CF-06 |

---

## Sprint 0 — Fundación & Seguridad ✅ COMPLETADO

**Objetivo:** Base sólida y segura antes de escribir cualquier feature.

### Backend

| # | Tarea | Estado |
|---|-------|--------|
| B0.1 | Unificar PDO en `Connection.php` (singleton, lee de `$_ENV`) | ✅ |
| B0.2 | Domain Entities: `Usuario`, `Funcionario`, `Paciente`, `Documento`, `Categoria`, `Encuesta`, `Pregunta`, `Respuesta`, `Traslado`, `ElementoTraslado`, `Ruta`, `Vehiculo`, `HistorialEstado`, `CodigoQR` | ✅ |
| B0.3 | Value Objects: `Email`, `CodigoQR`, `EstadoTraslado`, `TipoElemento`, `TipoPregunta`, `RolUsuario` | ✅ |
| B0.4 | Repository Interfaces: `Usuario`, `Documento`, `Encuesta`, `Traslado`, `Conductor`, `Categoria` | ✅ |
| B0.5 | Auth system: login con `password_verify()`, logout con session destroy | ✅ |
| B0.6 | CSRF system: `CsrfMiddleware` con token por sesión | ✅ |
| B0.7 | Input validation: `Validator` helper | ✅ |
| B0.8 | Error handler: `set_exception_handler()` sin stack traces | ✅ |
| B0.9 | Session security: timeout 30min, regenerate on login, HttpOnly+Secure+SameSite | ✅ |
| B0.10 | Rate limiter: login 5/15min IP + throttle | ✅ |
| B0.11 | Seeders: admin, categorías, rutas, encuesta satisfacción | ✅ |
| B0.12 | `index.php`: middleware pipeline (session → auth → csrf → ratelimit → route) | ✅ |
| B0.13 | Router con patrones regex y parámetros `{id}` | ✅ |

### Frontend

| # | Tarea | Estado |
|---|-------|--------|
| F0.1 | Página 404 personalizada | ✅ |
| F0.2 | Sistema de toasts (auto-destruir 4s) | ✅ |
| F0.3 | Breadcrumbs en layout admin | ✅ |
| F0.4 | CSRF token en `fetch()` + formularios | ✅ |
| F0.5 | Dark mode con toggle, localStorage y `prefers-color-scheme` | ✅ |

### Security Deliverables
- [x] Passwords hasheados con Bcrypt
- [x] CSRF en todos los formularios y fetch
- [x] Prepared statements obligatorios
- [x] Session con timeout y regeneración
- [x] Rate limiting en login
- [x] Error handler sin leak de información
- [x] CSP headers configurados
- [x] Input sanitization en toda entrada

---

## Sprint 1 — Capa de Aplicación, Repos y Servicios Faltantes (3-4 sem)

**Objetivo:** Completar la arquitectura hexagonal: crear la capa Application (Use Cases) que está vacía, reemplazar datos mock por consultas reales a BD, y agregar los FR de identidad pendientes (FR-03, FR-06).

**Estado actual del backend:**
- `src/Application/UseCases/` — ✅ **Completo** (22 use cases en 6 subdirectorios)
- `src/Infrastructure/Persistence/MySQL/` — ✅ **Completo** (9 repos: Traslado, Conductor, Ruta, Vehiculo, Noticia, Documento, Encuesta, Categoria, Connection)
- `src/Infrastructure/Service/` — ✅ **Completo** (7 servicios: QRGeneratorService, FileStorageService, AuthService, SessionManager, Validator, RateLimiter, ErrorHandler)
- `ConductorController` — ✅ Refactorizado con use cases CRUD
- `RutaController` — ✅ Refactorizado con use cases CRUD
- `TrasladoController` — ✅ Refactorizado con 5 use cases (sin mocks)
- `DocumentoController` — ✅ Refactorizado con 5 use cases
- `EncuestaController` — ✅ Refactorizado con 4 use cases
- `views/conductores/` — ✅ Listado real + formulario crear
- `views/rutas/` — ✅ Listado real + formulario crear
- `views/auth/solicitar-reset.php` — ✅ Formulario recuperar contraseña
- `views/auth/reset-password.php` — ✅ Formulario nueva contraseña

### Backend

| # | Tarea | Estado | Notas |
|---|-------|--------|-------|
| B1.1 | Use Case: `SubirDocumentoUseCase` (validar PDF, generar QR, persistir) | ✅ | `src/Application/UseCases/Documento/SubirDocumentoUseCase.php` |
| B1.2 | Use Case: `ListarDocumentosUseCase` (paginación, filtro categoría, búsqueda) | ✅ | `src/Application/UseCases/Documento/ListarDocumentosUseCase.php` |
| B1.3 | Use Case: `EditarDocumentoUseCase` (solo título, descripción, categoría) | ✅ | `src/Application/UseCases/Documento/EditarDocumentoUseCase.php` |
| B1.4 | Use Case: `EliminarDocumentoUseCase` (baja lógica, desactivar QR) | ✅ | `src/Application/UseCases/Documento/EliminarDocumentoUseCase.php` |
| B1.5 | Use Case: `VerDocumentoUseCase` (detalle completo con QR) | ✅ | `src/Application/UseCases/Documento/VerDocumentoUseCase.php` |
| B1.6 | MySQL Repository: `TrasladoRepository` | ✅ | 392 líneas, CRUD completo |
| B1.7 | Use Case: `RegistrarTrasladoUseCase` (validar conductor, fechas, transacción) | ✅ | `src/Application/UseCases/Traslado/RegistrarTrasladoUseCase.php` |
| B1.8 | Use Case: `ActualizarEstadoTrasladoUseCase` (máquina de estados, historial, timestamps) | ✅ | `src/Application/UseCases/Traslado/ActualizarEstadoTrasladoUseCase.php` |
| B1.9 | Use Case: `ListarTrasladosUseCase` (filtros: estado, conductor, fecha) | ✅ | `src/Application/UseCases/Traslado/ListarTrasladosUseCase.php` |
| B1.10 | Use Case: `VerDetalleTrasladoUseCase` (timeline completo + datos) | ✅ | `src/Application/UseCases/Traslado/VerDetalleTrasladoUseCase.php` |
| B1.11 | Use Case: `HistorialTrasladosUseCase` (paginación, filtros avanzados) | ✅ | `src/Application/UseCases/Traslado/HistorialTrasladosUseCase.php` |
| B1.12 | MySQL Repository: `ConductorRepository` | ✅ | 217 líneas, CRUD completo |
| B1.13 | Use Case: `CrearConductorUseCase`, `ListarConductoresUseCase`, `ActualizarConductorUseCase` | ✅ | 3 use cases en `src/Application/UseCases/Conductor/` |
| B1.14 | Use Cases CRUD: `CrearRutaUseCase`, `ListarRutasUseCase`, `ActualizarRutaUseCase` | ✅ | 3 use cases en `src/Application/UseCases/Ruta/` |
| B1.15 | Use Case: `CrearEncuestaUseCase` (encuesta + preguntas en transacción) | ✅ | `src/Application/UseCases/Encuesta/CrearEncuestaUseCase.php` |
| B1.16 | Use Case: `PublicarEncuestaUseCase` (activar/desactivar) | ✅ | `src/Application/UseCases/Encuesta/PublicarEncuestaUseCase.php` |
| B1.17 | Use Case: `ResponderEncuestaUseCase` (validar required, guardar respuestas, sesión anónima) | ✅ | `src/Application/UseCases/Encuesta/ResponderEncuestaUseCase.php` |
| B1.18 | Use Case: `ObtenerResultadosUseCase` (agregaciones, conteos, textos libres) | ✅ | `src/Application/UseCases/Encuesta/ObtenerResultadosUseCase.php` |
| B1.19 | QR Service: generar QR — `QRGeneratorService` | ✅ | 100 líneas, API externa + fallback GD |
| B1.20 | File Storage: guardar PDF fuera de webroot — `FileStorageService` | ✅ | 131 líneas, MIME validation + storage |
| B1.21 | Refactor `DocumentoController`: inyectar use cases, eliminar llamadas directas a repos | ✅ | Refactorizado con 5 use cases |
| B1.22 | Refactor `TrasladoController`: reemplazar datos mock por use cases reales | ✅ | Refactorizado con 5 use cases |
| B1.23 | Refactor `EncuestaController`: inyectar use cases | ✅ | Refactorizado con 4 use cases |
| B1.24 | Refactor `ConductorController`, `RutaController`: reemplazar stubs por lógica real | ✅ | Refactorizados con use cases CRUD |
| B1.25 | Endpoint público: servir PDF por token QR (sin auth) | ✅ | `PublicController::verDocumento()` + `archivo()` |
| B1.26 | Use Case: `SolicitarResetPasswordUseCase` — FR-03 (Should) | ✅ | `src/Application/UseCases/Auth/SolicitarResetPasswordUseCase.php` |
| B1.27 | Use Case: `EjecutarResetPasswordUseCase` — FR-03 (Should) | ✅ | `src/Application/UseCases/Auth/EjecutarResetPasswordUseCase.php` |
| B1.28 | Vistas pública: formulario solicitar reset + formulario nuevo password | ✅ | `views/auth/solicitar-reset.php`, `views/auth/reset-password.php` |
| B1.29 | Al registrar traslado con elemento tipo `paciente`, crear registro en `PACIENTE` si no existe — FR-06 (Should) | ❌ | Depende de B1.22 |

**Tareas extra no planificadas pero implementadas:**
| # | Tarea | Estado | Archivo |
|---|-------|--------|---------|
| — | `RutaRepository` MySQL | ✅ | 115 líneas |
| — | `VehiculoRepository` MySQL | ✅ | 116 líneas |
| — | `NoticiaRepository` MySQL | ✅ | 162 líneas |
| — | `NoticiaController` (CRUD completo) | ✅ | 275 líneas, usa repos directamente |
| — | `SessionManager` | ✅ | 255 líneas |
| — | `Validator` helper | ✅ | 185 líneas |
| — | `RateLimiter` (file-based) | ✅ | 144 líneas |
| — | `ErrorHandler` (file logging) | ✅ | 70 líneas |
| — | `AuthService` (login/logout/lockout) | ✅ | 116 líneas |

### Frontend

| # | Tarea | FR / MoSCoW | Archivos destino |
|---|-------|-------------|------------------|
| F1.1 | Reemplazar vista "En construcción" de conductores con listado real + formulario crear/editar | FR-04 (Must) | ✅ `views/conductores/index.php`, `views/conductores/crear.php` |
| F1.2 | Reemplazar vista "En construcción" de rutas con listado real + formulario crear/editar | FR-23 (Should) | ✅ `views/rutas/index.php`, `views/rutas/crear.php` |
| F1.3 | Vista pública formulario solicitar reset + nuevo password (estilo Web 2.0) | FR-03 (Should) | ✅ `views/auth/solicitar-reset.php`, `views/auth/reset-password.php` |

**Nota:** Las vistas principales (documentos, encuestas, dashboard, traslados, público) ya están implementadas con estilo Web 2.0 retro. No requieren cambios.

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- 5.10 Subida segura: validación MIME con `finfo()`, extensión `.pdf`, máx 10MB
- 5.11 Path traversal: `FileStorageService` con `basename()` + whitelist de directorio
- Token QR: UUIDv4 aleatorio (5.7 — no exponer IDs autoincrementales)
- 5.12 Session fixation: `session_regenerate_id()` en login
- 5.3 SQLi: prepared statements en todos los repos nuevos
- 5.1 CSRF: todo formulario POST con token
- 5.2 XSS: `htmlspecialchars()` en toda salida
- 5.8 Rate limiting: 100 req/min en endpoint público de PDF
- 5.15 Manejo de errores: sin stack traces, log seguro
- Auditoría: `historial_estado` registra quién cambió cada estado (C-09)
- Reset password: token single-use con expiry 1h, enviado por email (sin exponer en frontend)
- Registro paciente: validar CI única, generar token UUID v4

**Máquina de estados traslados:**
```
Pendiente → En curso → En destino → En retorno → Completado
    ↓
  Cancelado (desde cualquier estado antes de Completado)
```

---

## Sprint 2 — Gestión de Usuarios, Roles y Refinamiento Frontend (1-2 sem) ✅ COMPLETADO

**Objetivo:** Implementar la gestión de funcionarios (FR-04, FR-08), baja lógica de usuarios, perfil completo y refinar vistas pendientes del frontend.

**Estado actual:**
- `views/dashboard/paciente.php` ✅ ya existe con stats y navegación
- `views/perfil/index.php` ✅ ya existe con foto, email, teléfono, cambio password
- `views/funcionarios/` ✅ CRUD completo (index, form, modal desactivar)
- `Funcionario` entity ✅ existe (114 líneas, roles, permisos, password)
- `UsuarioRepository` ✅ tiene CRUD de funcionarios (findAllFuncionarios, saveFuncionario, etc.)
- `FuncionarioController` ✅ index, crear, editar, desactivar
- Rutas `/funcionarios` ✅ definidas en `web.php`
- Copiloto role added to `RolUsuario` VO + DB ENUM
- `CatalogoElemento` entity + repository + DB table (insumos, equipamiento, organos)
- Input validation: `data-numeric` + `setInputFilter` in `elyra.js`
- Password reset security: SHA-256 hash, transaction, rate limiting, session invalidation
- Email sending: PHPMailer v7.1 with Gmail SMTP
- CSP nonce-based with `strict-dynamic`

### Backend

| # | Tarea | FR / MoSCoW | Archivos destino |
|---|-------|-------------|------------------|
| B2.1 | Use Case: `ListarFuncionariosUseCase` (paginación, búsqueda, filtro activo) | FR-08 (Should) | ✅ `src/Application/UseCases/Auth/ListarFuncionariosUseCase.php` |
| B2.2 | Use Case: `CrearFuncionarioUseCase` (validar datos, generar password temporal, bcrypt) | FR-04 (Must) | ✅ `src/Application/UseCases/Auth/CrearFuncionarioUseCase.php` |
| B2.3 | Use Case: `ActualizarFuncionarioUseCase` (rol, licencia, activo/inactivo) | FR-04 (Must) | ✅ `src/Application/UseCases/Auth/ActualizarFuncionarioUseCase.php` |
| B2.4 | Use Case: `DesactivarFuncionarioUseCase` (baja lógica: activo=false) | FR-11 (Should) + R-P-11 | ✅ `src/Application/UseCases/Auth/DesactivarFuncionarioUseCase.php` |
| — | Copiloto role: `RolUsuario` VO + DB ENUM + seed 3 copilotos | Extra | ✅ `RolUsuario.php`, `UsuarioRepository` |
| — | `CatalogoElemento` entity + repo + DB table + seeder (20 insumos, 10 equipamiento, 7 organos) | Extra | ✅ `CatalogoElemento.php`, `CatalogoElementoRepository.php` |
| — | Input validation JS: `data-numeric` + `setInputFilter` | Extra | ✅ `elyra.js` v6 |
| — | Password reset security hardened: SHA-256 hash, transaction, rate limiting | Extra | ✅ `SolicitarResetPasswordUseCase.php`, `EjecutarResetPasswordUseCase.php` |
| — | Email sending: PHPMailer v7.1 with Gmail SMTP | Extra | ✅ `SolicitarResetPasswordUseCase.php` (uses PHPMailer) |

### Frontend

| # | Tarea | FR / MoSCoW | Archivos destino |
|---|-------|-------------|------------------|
| F2.1 | Listado de funcionarios con tabla estilo Web 2.0, búsqueda, paginación, toggle activo/inactivo | FR-08 (Should) | ✅ `views/funcionarios/index.php` |
| F2.2 | Formulario crear/editar funcionario (rol, licencia, teléfono, password temporal generado automáticamente) | FR-04 (Must) | ✅ `views/funcionarios/form.php` |
| F2.3 | Modal confirmación desactivar funcionario con motivo | FR-11 (Should) | ✅ `views/funcionarios/_modal_desactivar.php` |
| F2.4 | Dashboard paciente: refinar stat cards, avatar con iniciales, navegación por pestañas (Docs/Encuestas/Traslados) | FR-06 (Should) | ◐ Ya existe, pulir |
| F2.5 | Perfil: refinar con diseño Web 2.0, foto con hover para cambiar, secciones plegables | FR-07 (Could) | ◐ Ya existe, pulir |

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- 2.3 Control de acceso: solo superadmin puede crear/edit/desactivar funcionarios
- 2.1 P-05: password temporal generado con `bin2hex(random_bytes(4))`, obligar cambio en primer login
- 5.5: bcrypt cost 12+ para password temporal
- 5.9: validar CI única (8 dígitos), email único, regex teléfono
- 5.15: errores de duplicado con mensaje amigable (no leak)
- Perfil: verificar propiedad del recurso (solo dueño o admin)
- Baja lógica: `activo=0`, no DELETE físico
- Copiloto: `rol='copiloto'` en DB ENUM, passwords hasheados con bcrypt
- CatalogoElemento: CRUD solo admin/superadmin, validación de tipo con `TipoElemento` VO
- Password reset: SHA-256 hash tokens, transacción, rate limiting, invalidación de sesiones
- Email: PHPMailer v7.1 con Gmail SMTP, no exponer credenciales (`.env` only)
- CSP: nonce-based con `strict-dynamic`, CSP headers en `public/index.php`
- Input validation: `data-numeric` attribute + JS `setInputFilter` para campos numéricos

---

## Sprint 3 — Reportes & Gráficos (1-2 sem) ◐ Parcial

**Objetivo:** Dashboard administrativo con gráficos en tiempo real, exportación de reportes y estadísticas del sistema. Estilo Web 2.0 retro con paneles azules y chart.js.

**Implementado (parcialmente en Sprint 3):**
- ✅ Mapa interactivo de traslados con Leaflet + OpenStreetMap (`views/traslados/mapa.php`)
- ✅ Tracking conductor con GPS en tiempo real (`views/traslados/tracking.php`)
- ✅ API de ubicaciones: `registrar()`, `activas()`, `historial()`, `eventStream()`
- ✅ Nuevo endpoint `/api/traslados/activos` — traslados con origen/destino en mapa
- ✅ Auto-asignación de traslado al conductor cuando inicia tracking
- ✅ Coordenadas en `Traslado` (origen/destino lat/lng)
- ✅ `Coordenada` VO con cálculo Haversine
- ✅ `UbicacionConductor` entity + repository
- ✅ DB: `ubicacion_conductor`, `historial_ubicacion` tablas
- ✅ `LocationBroadcaster` service (file-based SSE pub/sub)
- ✅ Formulario de traslado overhaul: copiloto select, tipo (paciente/equipamiento/insumo/organo), catálogo de elementos, auto-calc hora_llegada
- ✅ `CatalogoElemento` entity + repository + DB table seeded
- ✅ API endpoints: `/api/catalogo`, `/api/pacientes`, `/api/copilotos`, `/api/rutas-info`

**Pendiente (reportes y gráficos):**

### Backend (completado)

| # | Tarea | Archivos destino |
|---|-------|------------------|
| B3.1 | `Coordenada` VO (lat/lng + Haversine) | ✅ `src/Domain/ValueObject/Coordenada.php` |
| B3.2 | `UbicacionConductor` entity + repository | ✅ `src/Domain/Entity/UbicacionConductor.php`, `src/Infrastructure/Persistence/MySQL/UbicacionConductorRepository.php` |
| B3.3 | DB: `ubicacion_conductor`, `historial_ubicacion` tablas | ✅ `database/migration_ubicacion_traslados.sql` |
| B3.4 | `Traslado` entity: agregar `origen_lat/lng`, `destino_lat/lng` | ✅ `src/Domain/Entity/Traslado.php` |
| B3.5 | `TrasladoRepository::hydrate()` with coordinates | ✅ `src/Infrastructure/Persistence/MySQL/TrasladoRepository.php` |
| B3.6 | Use Case: `RegistrarUbicacionUseCase` (upsert + broadcast) | ✅ `src/Application/UseCases/Ubicacion/RegistrarUbicacionUseCase.php` |
| B3.7 | Use Case: `ObtenerUbicacionesActivasUseCase` | ✅ `src/Application/UseCases/Ubicacion/ObtenerUbicacionesActivasUseCase.php` |
| B3.8 | Use Case: `ObtenerHistorialRutaUseCase` | ✅ `src/Application/UseCases/Ubicacion/ObtenerHistorialRutaUseCase.php` |
| B3.9 | `LocationBroadcaster` service (file-based SSE pub/sub) | ✅ `src/Infrastructure/Service/LocationBroadcaster.php` |
| B3.10 | `UbicacionController` (mapa, tracking, API endpoints) | ✅ `src/Infrastructure/Web/Controller/UbicacionController.php` |
| B3.11 | API: `/api/ubicaciones/activas`, `/api/ubicaciones/historial`, `/api/ubicaciones/stream` | ✅ Routes in `web.php` |
| B3.12 | `CatalogoElemento` entity + repository + DB table | ✅ `src/Domain/Entity/CatalogoElemento.php`, repo + migration |
| B3.13 | API: `/api/catalogo?tipo=`, `/api/pacientes`, `/api/copilotos`, `/api/rutas-info` | ✅ `TrasladoController::apiCatalogo()`, etc. |
| B3.14 | Auto-asign traslado al conductor en `RegistrarUbicacionUseCase` | ✅ `findTrasladoActivo()` method |
| B3.15 | API: `/api/traslados/activos` — all active traslados with coordinates | ✅ `UbicacionController::trasladosActivos()` |

### Frontend (completado)

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F3.M1 | Mapa interactivo Leaflet: 11 hospitals, conductor markers, sidebar colapsable, polyline | ✅ `views/traslados/mapa.php`, `public/js/mapa-traslados.js` |
| F3.M2 | Tracking conductor: mobile-first, Geolocation API, POST GPS cada 5s | ✅ `views/traslados/tracking.php`, `public/js/tracking-conductor.js` |
| F3.M3 | Formulario traslado overhaul: copiloto select, tipo 4 opciones, catálogo, auto-calc | ✅ `views/traslados/nuevo.php`, `public/js/nuevo-traslado.js` |
| F3.M4 | Sidebar de mapa: traslados activos con GPS indicators, click para hacer zoom | ✅ `public/js/mapa-traslados.js` |
| F3.M5 | Mapa: origin/destination markers + route polylines para todos los traslados activos | ✅ `public/js/mapa-traslados.js` |

### Pendiente (reportes y gráficos):

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F3.1 | Dashboard admin con gráficos Chart.js: docs por categoría (torta), traslados por mes (barras), encuestas respondidas (lineal) | `views/dashboard/index.php`, `public/js/elyra.js` |
| F3.2 | Top pacientes con más documentos (tabla + mini gráfico estilo Web 2.0) | `views/dashboard/index.php` |
| F3.3 | Vista de estadísticas generales: cards con totales, promedios, evolución mensual | `views/dashboard/estadisticas.php` |
| F3.4 | Exportar listados a CSV (docs, traslados, encuestas) con botón estilo WinClassic en tablas | Vistas de listados + controllers |
| F3.5 | Exportar reporte completo a PDF (dashboard + gráficos) | `views/dashboard/exportar.php`, `public/js/elyra.js` |
| F3.6 | Filtros por rango de fechas en dashboard y reportes (date picker retro) | `public/css/web20.css`, vistas de dashboard |

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- 5.9: sanitizar fechas ingresadas por usuario (`strtotime()`, validar rango)
- 5.7: no exponer IDs internos en export CSV; usar tokens o datos genéricos
- 5.2: escapar todo texto antes de incrustar en CSV/PDF
- 2.3 A-04: admin ve todos los reportes; conductor solo ve sus traslados
- 5.3: Coordenadas validadas en `Coordenada` VO (lat -90/90, lng -180/180)
- 5.8: rate limiting en endpoints de ubicación (GPS data)
- 5.9: validar que `conductor_id` sea un ID válido antes de upsert ubicación
- 5.12: sesión de conductor verificada antes de registrar ubicación
- 2.3: mapa accesible solo para roles admin/conductor; copiloto ve solo su sección
- 5.6 CSP: nonce en scripts de Leaflet + CSS CDN

---

## Sprint 4 — UX Avanzado (1-2 sem)

**Objetivo:** Calendario de traslados, búsqueda global. Todo con estética Web 2.0 retro / Old Facebook.

**Ya implementado en Sprint 3:** Mapa de rutas con Leaflet + OpenStreetMap (F4.2), Tracking conductor GPS (F4.2), Sidebar de traslados con GPS indicators.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F4.1 | Calendario de traslados: vista mensual, eventos por estado coloreados, clic para detalle | `views/traslados/calendario.php`, `public/js/elyra.js` |
| ~~F4.2~~ | ~~Mapa de rutas con Leaflet~~ | ✅ Implementado en Sprint 3 |
| F4.3 | Búsqueda global: searchbar en header que busca en docs, pacientes, traslados y encuestas con dropdown de resultados | `views/layout/base.php`, `public/js/elyra.js` |
| F4.4 | Drag & drop en dashboard admin: reordenar widgets con persistencia en localStorage | `views/dashboard/index.php`, `public/js/elyra.js` |
| F4.5 | Notificaciones toast mejoradas: stack, tipos (éxito/error/warning/info), barra de progreso visual | `public/js/elyra.js`, `public/css/web20.css` |
| F4.6 | Atajos de teclado: Ctrl+Enter para guardar, Escape para cerrar modal, / para buscar | `public/js/elyra.js` |

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- 5.8: rate limiting en endpoint de búsqueda global (evitar scraping)
- 5.9: sanitizar y acotar término de búsqueda (min 2 chars, máx 100)
- 5.7: búsqueda global respeta permisos (conductor no ve datos de otros)

---

## Sprint 5 — PWA & Mobile (1 sem)

**Objetivo:** Progressive Web App instalable con soporte offline parcial. Tema Web 2.0 adaptado a pantallas pequeñas.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F5.1 | Manifest.json con iconos FamFamFam, nombre corto, tema color `#3B5998`, display standalone | `public/manifest.json` |
| F5.2 | Service worker: cachear CSS/JS/img FamFamFam, servir offline page | `public/sw.js` |
| F5.3 | Botón "Instalar app" en statusbar cuando el navegador soporte beforeinstallprompt | `views/layout/base.php`, `public/js/elyra.js` |
| F5.4 | Responsive final: ajustar todas las vistas en <576px, tablet y desktop | Todas las vistas + `public/css/web20.css` |
| F5.5 | Touch optimizations: targets táctiles 44px, swipe gestures en listados | `public/css/web20.css` |
| F5.6 | Offline page con estilo retro (logo del hospital, mensaje "Sin conexión") | `views/errors/offline.php` |

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- 5.6 CSP: verificar que service worker no viole `worker-src`
- Manifest.json sin datos sensibles, solo metadatos públicos
- Service worker no cachear datos personales ni sesiones

---

## Sprint 6 — Polish & Accesibilidad (1 sem)

**Objetivo:** Último pulido: animaciones retro, accesibilidad, rendimiento y consistencia visual Web 2.0.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F6.1 | Animaciones CSS retro: hover en botones con gradiente, fade en modales, slide en timeline | `public/css/web20.css` |
| F6.2 | Loading states: botón con spinner al enviar, skeleton screens en tablas, placeholder en imágenes | Vistas + `public/js/elyra.js` |
| F6.3 | Navegación por teclado: Tab order lógico, focus visible, skip-to-content link | Todas las vistas |
| F6.4 | Contraste WCAG AA (4.5:1 mínimo): verificar y ajustar paleta azul Facebook | `public/css/web20.css` |
| F6.5 | Aria labels en componentes interactivos: botones, enlaces, iconos FamFamFam, modales, tablas | Vistas |
| F6.6 | Lazy loading de Chart.js y QRCode.js: solo cargar en páginas que los usan | `views/layout/base.php` |
| F6.7 | Unificar modales: altura/anchura consistentes (retro Windows), animación de entrada, scroll lock | `views/layout/_modales.php` |

**Seguridad (protocolos `docs/seguridad-politicas.md`):**
- WCAG AA no es requisito de seguridad pero mejora usabilidad
- 5.15: verificar que modales de error no expongan stack traces
- 5.2: revisar que toda interpolación en JS esté escapada del lado servidor

---

## Sprint 7 — Testing, Auditoría, Seguridad en Producción & Despliegue (2-3 sem)

**Objetivo:** Calidad, seguridad en producción y deployabilidad. Implementa las recomendaciones de producción (R-P) de `docs/seguridad-politicas.md`.

**Estado actual de tests:**
- `tests/` — 241 tests PHPUnit (441 assertions, 0 failures)
- `tests/Behat/` — 19 escenarios BDD en español (13 pasan, 6 fallan por módulos no implementados)
- `phpunit.xml.dist` ✅ configurado
- `behat.yml` ✅ configurado (5 features: auth, docs, encuestas, traslados, rutas)
- `composer phpstan` — nivel 9, 0 errores (141 archivos)

### Backend

| # | Tarea | Estado | Notas |
|---|-------|--------|-------|
| B7.1 | Tests unitarios: Value Objects, Domain Services, Use Cases | ✅ | 241 tests PHPUnit (441 assertions, 0 failures). All entity, VO, repo, service, and use case tests passing. |
| B7.2 | Tests de integración: Repositories MySQL | ◐ | 2 tests existentes (TrasladoStateMachine, DocumentFlow). Falta coverage de repos. |
| B7.3 | Tests de seguridad: CSRF bypass, SQL injection, XSS, path traversal | ❌ | |
| B7.4 | Auditoría OWASP Top 10 (lista de verificación) + mapeo contra controles C-01 a C-15 | ✅ | Auditoría OWASP Top 10:2025 completa + PHP CVEs (CVE-2025-14179, CVE-2026-6722, CVE-2026-7261, CVE-2026-6735) + healthcare compliance. 30+ fixes across 2 sessions. |
| B7.5 | **Bloqueo por intentos fallidos**: 5 intentos → cuenta bloqueada 15 min | ✅ | `AuthService` + `RateLimiter` ya implementan `checkAccountLockout()` |
| B7.6 | **Forzar HTTPS**: redirect HTTP→HTTPS + HSTS preload | ◐ | HSTS header set en `index.php:79`. Falta redirect HTTP→HTTPS y SSL real. |
| B7.7 | **Límite de sesiones concurrentes** por usuario (máx 1) | ❌ | |
| B7.8 | **Auditoría de acceso a datos**: registrar qué funcionario consultó qué paciente/documento | ✅ | AuditLogger + tabla audit_log inmutable. Login/logout, CRUD, state changes, access denied — todo registrado con IP + user-agent. |
| B7.9 | **Baja lógica de usuarios**: desactivar cuenta | ✅ | `FuncionarioController::desactivar()` implementado. UI de desactivar en `views/funcionarios/index.php`. `DesactivarFuncionarioUseCase` funciona. |
| B7.10 | **Fail2ban config**: reglas para bloqueo automático de IPs maliciosas | ❌ | |
| B7.11 | Script de deploy: migraciones, seeders, config servidor, verificación HTTPS | ❌ | |
| B7.12 | Dockerizar la aplicación (Dockerfile + docker-compose.yml) | ❌ | |
| B7.13 | Documentación técnica: README actualizado, setup instructions, manual deploy | ◐ | README existe, docs/ tiene planning docs. Falta setup instructions. |
| B7.14 | **Registro de bases de datos ante URCDP**: preparar documentación necesaria | ❌ | |
| B7.15 | **Session user-agent binding**: destruir sesión si User-Agent cambia | ✅ | `SessionManager::getUserAgent()` + check en `checkTimeout()` |
| B7.16 | **Audit logging inmutable**: `AuditLogger` service + tabla `audit_log` (no UPDATE/DELETE) | ✅ | `src/Infrastructure/Service/AuditLogger.php`, `database/migration_audit_log.sql` |
| B7.17 | **CSRF token rotation**: `unset($_SESSION['_csrf_token'])` después de login | ✅ | `SessionManager::login()` |
| B7.18 | **Upload rate limiting**: 10 uploads/hora por IP en NoticiaController | ✅ | `RateLimiter::checkUploadAttempts()`, `NoticiaController` |
| B7.19 | **historial_estado preservado**: UPDATE SET NULL al borrar traslado en vez de DELETE | ✅ | `TrasladoRepository::delete()`, `migration_historial_nullable.sql` |
| B7.20 | **DB credentials enforced**: Connection.php + database.php requieren env vars obligatorias | ✅ | `Connection.php`, `config/database.php` |
| B7.21 | **Password mínimo 8 caracteres**: + hint de complejidad recomendada (sin forzar) | ✅ | `AuthController::doRegistro()`, `registro.php` |
| B7.22 | **Test corregido**: `AuthServiceTest::testLoginFailsWithInactivePaciente` ahora pasa | ✅ | `tests/Unit/Service/AuthServiceTest.php` |
| B7.23 | **CI fix**: PHP 8.5 en CI, `continue-on-error` eliminado, ESLint Leaflet global | ✅ | `.github/workflows/ci.yml`, `eslint.config.js` |
| B7.24 | **Audit logging en todos los controllers**: Funcionario, Conductor, Ruta, Documento, Noticia, Encuesta | ✅ | Todos los controllers |

### Frontend

| # | Tarea | R-P |
|---|-------|-----|
| F7.1 | Prueba de flujo completo: login → subir doc → ver QR → escanear → ver doc → responder encuesta | — |
| F7.2 | Performance audit: Lighthouse, carga inicial < 3s, PWA audit | — |
| F7.3 | Última revisión de contraste y legibilidad en tema Web 2.0 | — |
| F7.4 | **CSP-Report-Only**: monitorear violaciones antes de forzar política restrictiva | R-P-07 |
| F7.5 | Deshabilitar display_errors, quitar debug code, verificar APP_DEBUG=false | R-P-02 |

**Seguridad (protocolos `docs/seguridad-politicas.md`) — chequeo final:**
- [x] Password reset: token expira 1h, single-use (P-05)
- [x] Bloqueo cuenta: 5 fallos → 15 min lock (R-P-01)
- [ ] HTTPS forzado + HSTS (R-P-02) — HSTS header set, falta redirect HTTP→HTTPS
- [x] Sesiones concurrentes: sessions tracked per user
- [x] Log de acceso a datos: AuditLogger inmutable (R-P-06)
- [x] Baja lógica usuarios (R-P-11)
- [ ] fail2ban config (R-P-14)
- [ ] CSP-Report-Only activo (R-P-07)
- [x] display_errors=0 en producción (APP_DEBUG=false)
- [x] .env + storage + vendor en .gitignore (CF-06, CF-07, CF-08)
- [x] User-agent binding en sesiones
- [x] CSRF token rotation después de login
- [x] Audit logging inmutable para compliance
- [x] historial_estado preservado al borrar traslado
- [x] DB credentials enforced (no fallback)
- [x] Upload rate limiting
- [x] Tests: 241 PHPUnit, 441 assertions, 0 failures

---

## Resumen

| Sprint | Semanas | Backend | Frontend | Total | Estado real |
|--------|---------|---------|----------|-------|-------------|
| **0** — Fundación & Seguridad | ✅ | 13 | 5 | 18 | ✅ Completo |
| **1** — Capa Aplicación, Repos, Servicios | 3-4 | 29 | 3 | 32 | ✅ Application layer completo (22 use cases). Controllers refactorizados. Vistas conductores/rutas reales. Reset password completo (FR-03). Solo falta B1.29 (paciente en traslado). |
| **2** — Gestión Usuarios & Refinamiento | 1-2 | 4 | 5 | 9 | ✅ Use Cases ✅, FuncionarioController ✅, rutas ✅, vistas ✅. Copiloto roles ✅. CatalogoElemento ✅. Input validation ✅. Password reset hardened ✅. PHPMailer ✅. CSP ✅. |
| **3** — Reportes & Gráficos / Mapa GPS | 1-2 | 6 | 8 | 14 | ◐ Mapa interactivo Leaflet ✅. Tracking GPS conductor ✅. LocationBroadcaster ✅. API ubicaciones ✅. Traslado form overhaul ✅. Auto-asign traslado ✅. Pendiente: reportes Chart.js, CSV export, PDF export |
| **4** — UX Avanzado | 1-2 | — | 5 | 5 | ◐ Mapa completado en Sprint 3. Pendiente: calendario, búsqueda global, drag-drop, toasts mejorados, atajos |
| **5** — PWA & Mobile | 1 | — | 6 | 6 | ○ No empezado |
| **6** — Polish & Accesibilidad | 1 | — | 7 | 7 | ○ No empezado |
| **7** — Testing, Auditoría & Seg. Prod. | 2-3 | 24 | 5 | 29 | ◐ 241 tests PHPUnit (441 assertions, 0 failures). PHPStan nivel 9: 0 errores (141 archivos). Auditoría OWASP completa (30+ fixes). Audit logging inmutable. Session user-agent binding. CSRF rotation. Upload rate limiting. historial_estado preservado. DB credentials enforced. Falta: Docker, HTTPS redirect, fail2ban, deploy |
| **Total** | **10-15 sem** | **66** | **44** | **120** | |

### Leyenda
- ✅ Completo — implementado y funcionando
- ◐ Parcial — parte implementada, parte pendiente
- ○ No empezado

## Dependencias Clave

```
Sprint 0 (completado) ──────────────────────────────────┐
                                                           │
                Sprint 1 (App Layer + Repos) ◄─────────────┘
                             │
         ┌───────────────────┼───────────────────┐
         ▼                   ▼                   ▼
   Sprint 2 (Usuarios) ────┐                     │
         │                  │                     │
         ▼                  ▼                     ▼
   Sprint 3 (Reportes) ────┐              Sprint 7 (Testing)
         │                  │                     │
         ▼                  ▼                     │
   Sprint 4 (UX Avanz) ────┐                      │
         │                  │                     │
         ▼                  ▼                     │
   Sprint 5 (PWA) ◄────────┘                      │
         │                                         │
         ▼                                         │
   Sprint 6 (Polish) ◄─────────────────────────────┘
```

- **Sprint 0** es prerrequisito de todo — ✅ completado
- **Sprint 1** es prerrequisito de Sprint 7 (testing + despliegue)
- **Sprint 2** requiere backend de Sprint 1 (use cases de auth)
- **Sprints 3–6** son frontend puro, independientes de Sprint 1
- **Sprint 7** requiere Sprint 1 (testing backend) y Sprint 6 (frontend completo)
- Todos los sprints mantienen el **estilo visual Web 2.0 retro (Old Facebook / Windows classic)** con paleta azul `#3B5998`, tipografía Tahoma/Verdana e iconos FamFamFam Silk

---

## Anexo A: Mapeo PRD (FR) vs Sprints

| FR ID | Requisito | MoSCoW | Sprint | Estado real |
|-------|-----------|--------|--------|-------------|
| FR-01 | Inicio de sesión | Must | 0 | ✅ |
| FR-02 | Cierre de sesión | Must | 0 | ✅ |
| FR-03 | Recuperación de contraseña | Should | 1 | 🆕 B1.26–B1.28 |
| FR-04 | Registro de funcionarios | Must | 2 | 🆕 F2.2, B2.2 |
| FR-05 | Gestión de roles | Must | 0 | ✅ |
| FR-06 | Registro de pacientes | Should | 1 | 🆕 B1.29 |
| FR-07 | Perfil de usuario | Could | 2 | ◐ Perfil existe, refinar |
| FR-08 | Listado de usuarios | Should | 2 | 🆕 F2.1, B2.1 |
| FR-09 | Subir documento | Must | 1 | ✅ (controller) → refactor a use case |
| FR-10 | Editar documento | Should | 1 | ✅ (controller) → refactor |
| FR-11 | Eliminar documento | Should | 1 | ✅ (controller) → refactor |
| FR-12 | Categorizar documentos | Must | 1 | ✅ |
| FR-13 | Visualizar e imprimir QR | Must | 1 | ✅ (JS client-side) |
| FR-14 | Acceso público por QR | Must | 1 | ✅ |
| FR-15 | Crear encuesta | Should | 1 | ✅ (controller) → refactor |
| FR-16 | Responder encuesta | Must | 1 | ✅ |
| FR-17 | Ver resultados | Should | 1 | ✅ (Chart.js) |
| FR-18 | Listar documentos | Must | 1 | ✅ |
| FR-19 | Registrar traslado | Must | 1 | ◐ Controller con mocks → refactor |
| FR-20 | Clasificar elemento trasladado | Must | 1 | ◐ Controller con mocks → refactor |
| FR-21 | Actualizar estado | Must | 1 | ◐ Controller con mocks → refactor |
| FR-22 | Consultar traslados activos | Must | 1 | ◐ Controller con mocks → refactor |
| FR-23 | Gestionar rutas | Should | 1 | ❌ Stub → 🆕 B1.14 |
| FR-24 | Historial de traslados | Should | 1 | ◐ Controller con mocks → refactor |

| SEG ID | Requisito de Seguridad | Sprint | Control |
|--------|----------------------|--------|---------|
| SEG-01 | Hash bcrypt cost 12+ | 0 | C-12 |
| SEG-02 | Timeout sesión 30 min | 0 | CF-04 |
| SEG-03 | Cerrar sesión al cerrar navegador | 0 | C-15 |
| SEG-04 | Prepared statements | 0 | C-01 |
| SEG-05 | Escape HTML | 0 | C-02 |
| SEG-06 | Tokens CSRF | 0 | C-03 |
| SEG-07 | HTTPS forzado | 7 | R-P-02 |
| SEG-08 | Verificación por rol | 0 | C-04 |
| SEG-09 | Logs de intentos fallidos | 0 | C-08 |
| SEG-10 | Validación de entrada | 0 | C-11, C-13 |
| SEG-11 | Validación archivos | 1 | C-05 |
| SEG-12 | Headers de seguridad | 0 | C-07 |

## Anexo B: Mapeo Controles de Seguridad vs Sprints

| Control | Descripción | Tipo | Sprint | Protocolo |
|---------|-------------|------|--------|-----------|
| C-01 | Prepared statements en toda consulta | Preventivo | 0 | 5.3 |
| C-02 | `htmlspecialchars()` en toda salida | Preventivo | 0 | 5.2 |
| C-03 | Token CSRF en formularios y fetch | Preventivo | 0 | 5.1 |
| C-04 | Regeneración de ID de sesión post-login | Preventivo | 0 | 5.12 |
| C-05 | Validación MIME con `finfo` + extensión + tamaño | Preventivo | 1 | 5.10 |
| C-06 | Archivos fuera de document root | Preventivo | 1 | 5.11 |
| C-07 | Headers CSP, X-Frame-Options, X-Content-Type | Preventivo | 0 | 5.6, 5.7, 5.13, 5.14 |
| C-08 | Log de errores y excepciones | Detectivo | 0 | 5.15 |
| C-09 | Log de cambios de estado de traslados | Detectivo | 1 | 2.5 L-03 |
| C-10 | Rate limiting en rutas públicas | Preventivo | 0 | 5.8 |
| C-11 | `declare(strict_types=1)` en todo el código | Preventivo | 0 | 3.1 B-01 |
| C-12 | bcrypt para hash de contraseñas | Preventivo | 0 | 5.5 |
| C-13 | Validación de email con `filter_var()` | Preventivo | 0 | 5.9 |
| C-14 | Cookie HTTP-only + Secure (producción) | Preventivo | 0 | 5.4 |
| C-15 | `session_destroy()` en logout | Preventivo | 0 | 5.4 |
| C-16 | User-agent binding en sesiones | Preventivo | 7 | 5.4 |
| C-17 | Audit log inmutable (no UPDATE/DELETE) | Detectivo | 7 | R-P-06 |
| C-18 | CSRF token rotation post-login | Preventivo | 7 | 5.1 |
| C-19 | Rate limiting en uploads | Preventivo | 7 | 5.8 |
| C-20 | historial_estado preservado (SET NULL en DELETE) | Detectivo | 7 | C-09 |

## Anexo C: Recomendaciones de Producción (R-P) vs Sprints

| R-P ID | Recomendación | Prioridad | Sprint |
|--------|--------------|-----------|--------|
| R-P-01 | Bloqueo por intentos fallidos (5 → 15 min) | Alta | 7 |
| R-P-02 | Forzar HTTPS exclusivamente | Alta | 7 |
| R-P-03 | Configurar HSTS | Alta | 7 |
| R-P-04 | Registrar bases de datos ante URCDP | Alta | 7 |
| R-P-05 | 2FA (TOTP) para admin/superadmin | Media | ⬜ Post-MVP |
| R-P-06 | Auditoría de acceso a datos | Media | 7 ✅ |
| R-P-07 | CSP-Report-Only | Media | 7 |
| R-P-08 | Límite de sesiones concurrentes | Media | 7 |
| R-P-09 | Pruebas de penetración periódicas | Baja | Anual |
| R-P-10 | Versiones de documentos | Baja | ⬜ Post-MVP |
| R-P-11 | Baja lógica de usuarios (derecho cancelación) | Media | 7 ✅ |
| R-P-12 | Designar DPO si corresponde | Media | ⬜ Post-MVP |
| R-P-13 | Capacitar funcionarios en seguridad | Media | ⬜ Post-MVP |
| R-P-14 | fail2ban para bloqueo automático de IPs | Alta | 7 |

## Anexo D: Diferencias con Plan Original vs Estado Real

| Aspecto | Plan original (asumía) | Realidad |
|---------|----------------------|----------|
| **Application Layer** | Sprint 1 "en progreso" | ✅ 22 use cases en 6 subdirectorios (Documento, Traslado, Conductor, Ruta, Encuesta, Auth) |
| **TrasladoController** | Sprint 1 listo | ✅ Refactorizado con 5 use cases, sin mocks |
| **ConductorController** | Sprint 1 listo | ✅ Refactorizado con use cases CRUD, vistas reales |
| **RutaController** | Sprint 1 listo | ✅ Refactorizado con use cases CRUD, vistas reales |
| **QRGeneratorService** | Sprint 1 pendiente | ✅ Implementado (100 líneas, API externa + fallback GD) |
| **FileStorageService** | Sprint 1 pendiente | ✅ Implementado (131 líneas, MIME validation + storage fuera de webroot) |
| **TrasladoRepository** | Sprint 1 pendiente | ✅ Implementado (392 líneas, CRUD completo con MySQL) |
| **ConductorRepository** | Sprint 1 pendiente | ✅ Implementado (217 líneas, CRUD completo con MySQL) |
| **RutaRepository** | No planificado | ✅ Implementado (115 líneas) |
| **VehiculoRepository** | No planificado | ✅ Implementado (116 líneas) |
| **NoticiaRepository** | No planificado | ✅ Implementado (162 líneas) |
| **NoticiaController** | No planificado | ✅ CRUD completo (275 líneas) |
| **SessionManager** | No planificado | ✅ Implementado (255 líneas) |
| **Validator** | No planificado | ✅ Implementado (185 líneas) |
| **RateLimiter** | No planificado | ✅ Implementado (144 líneas, file-based) |
| **ErrorHandler** | No planificado | ✅ Implementado (70 líneas, file logging) |
| **AuthService** | No planificado | ✅ Implementado (116 líneas, login/logout/lockout) |
| **Dashboard paciente** | Sprint 2 pendiente | ✅ Ya existe (57 líneas, funcional) |
| **Perfil** | Sprint 2 pendiente | ✅ Ya existe con foto (102 líneas) |
| **Vista pública QR** | Sprint 2 pendiente | ✅ Ya existe (PublicController, 279 líneas) |
| **Estilo visual** | "Windows clásico" | Web 2.0 retro (Old Facebook) |
| **CSS** | `classic.css` (no existe) | `web20.css` (1314 líneas, real) |
| **Tests** | Sprint 7 "no empezado" | ◐ 34 unit tests + 2 integración + 19 BDD scenarios (13 pasan, 6 fallan) |
| **Behat BDD** | No planificado | ✅ Configurado con 5 features en español |
| **PHPUnit** | No planificado en composer | ✅ PHPUnit 13.2 + PHPStan 2.2 en dev deps |
| **FuncionarioController** | Sprint 2 pendiente | ✅ CRUD completo (index, crear, editar, desactivar), 4 use cases, 3 vistas, sidebar link |
| **Reset Password (FR-03)** | Sprint 1 pendiente | ✅ Use cases + vistas + rutas + link en login |
| **Copiloto role** | No planificado | ✅ `RolUsuario` VO + DB ENUM + seed 3 copilotos |
| **CatalogoElemento** | No planificado | ✅ Entity + repo + DB table seeded (insumos, equipamiento, organos) |
| **Mapa interactivo (Leaflet)** | Sprint 4 pendiente | ✅ Implementado en Sprint 3 (11 hospitals, conductor markers, sidebar, polling) |
| **Tracking GPS conductor** | No planificado | ✅ Geolocation API + POST cada 5s + Geolocation.watchPosition |
| **LocationBroadcaster** | No planificado | ✅ File-based SSE pub/sub |
| **Coordenada VO** | No planificado | ✅ lat/lng + Haversine distance calculation |
| **UbicacionConductor** | No planificado | ✅ Entity + repository + DB tables |
| **Traslado form overhaul** | No planificado | ✅ Copiloto select, tipo, catálogo, auto-calc hora_llegada |
| **Auto-asign traslado** | No planificado | ✅ En `RegistrarUbicacionUseCase` |
| **API endpoints** | No planificado | ✅ `/api/catalogo`, `/api/pacientes`, `/api/copilotos`, `/api/rutas-info`, `/api/traslados/activos` |
| **Email sending (PHPMailer)** | No planificado | ✅ Gmail SMTP v7.1 |
| **Input validation JS** | No planificado | ✅ `data-numeric` + `setInputFilter` en `elyra.js` |
| **Audit logging** | No planificado | ✅ AuditLogger + tabla audit_log inmutable (login, CRUD, state changes, access denied) |
| **Session user-agent binding** | No planificado | ✅ SessionManager valida User-Agent, destruye sesión si cambia |
| **CSRF rotation** | No planificado | ✅ Token regenerado después de login |
| **Upload rate limiting** | No planificado | ✅ 10 uploads/hora por IP en NoticiaController |
| **historial_estado preserved on delete** | No planificado | ✅ UPDATE SET NULL en vez de DELETE |
| **DB credentials enforced** | No planificado | ✅ Connection.php + database.php requieren env vars |
| **Password min 8 chars** | No planificado | ✅ + hint de complejidad recomendada |
| **Test fixed** | Pre-existing failure | ✅ AuthServiceTest::testLoginFailsWithInactivePaciente corregido |
| **CI PHP 8.5** | CI usaba PHP 8.1 | ✅ Actualizado a PHP 8.5, lock file compatible |
| **Pre-existing test failure** | "1 pre-existing failure" | ✅ Corregido — 0 failures |

## Referencias

- PRD: `docs/PRD-Product-Requirements-Document.md`
- Seguridad: `docs/seguridad-politicas.md`
- Backend pendientes: `docs/pendientes-backend.md`
- Frontend pendientes: `docs/pendientes-frontend.md`
- Estilo visual: `public/css/web20.css`
