# Planificación de Sprints — Elyra

> **Contexto:** Proyecto universitario — UTULAB. Arquitectura hexagonal, PHP 8.4 + MySQL, Bootstrap 5 vanilla JS.
> **Estado actual:** Login + Homepage pública + Dashboard admin. Controladores stub. Sin dominio, sin persistencia, sin seguridad real.
> **Enfoque:** Security-first. Cada sprint incluye medidas de seguridad explícitas.

---

## Arquitectura de Seguridad (transversal a todos los sprints)

| Medida | Implementación | Sprint |
|--------|---------------|--------|
| Password hashing | `password_hash(PASSWORD_ARGON2ID)` + `password_verify()` | 0 |
| CSRF | Token por sesión en formularios, validación en POST/PUT/DELETE | 0 |
| XSS | `htmlspecialchars($var, ENT_QUOTES)` en toda salida | 0 |
| SQL Injection | Prepared statements PDO (`real_escape_string` prohibido) | 0 |
| Session security | `session_regenerate_id()` en login, HttpOnly+Secure+SameSite, timeout 30min | 0 |
| Rate limiting | Login: 5 intentos/15min por IP. Público: 100 req/min | 0 |
| File upload | MIME type + extensión + tamaño + almacenar fuera de webroot | 1 |
| Error handling | `set_exception_handler()` sin stack traces en prod | 0 |
| CSP headers | `Content-Security-Policy` restrictiva | 0 |
| Rol-based access | admin / superadmin / conductor | 0 |

---

## Sprint 0 — Fundación & Seguridad (1-2 sem)

**Objetivo:** Base sólida y segura antes de escribir cualquier feature.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B0.1 | Unificar PDO en `Infrastructure/Persistence/MySQL/Connection.php` (eliminar `config/database.php`) | — | `src/Infrastructure/Persistence/MySQL/Connection.php` |
| B0.2 | Domain Entities: `Usuario`, `Funcionario`, `Paciente`, `Documento`, `Categoria`, `Encuesta`, `Pregunta`, `Respuesta`, `Traslado`, `ElementoTraslado`, `Ruta`, `Vehiculo`, `HistorialEstado`, `CodigoQR` | — | `src/Domain/Entity/*.php` |
| B0.3 | Value Objects: `Email`, `CodigoQR`, `EstadoTraslado`, `TipoElemento`, `TipoPregunta`, `RolUsuario` | B0.2 | `src/Domain/ValueObject/*.php` |
| B0.4 | Repository interfaces: `UsuarioRepositoryInterface`, `DocumentoRepositoryInterface`, `EncuestaRepositoryInterface`, `TrasladoRepositoryInterface`, `ConductorRepositoryInterface` | B0.2 | `src/Domain/Repository/*Interface.php` |
| B0.5 | Auth system: login con `password_verify()` contra BD, logout con session destroy + regenerate | — | `src/Infrastructure/Service/AuthService.php`, refactor `AuthController` |
| B0.6 | CSRF system: generar token, validar en POST, middleware base | — | `src/Infrastructure/Web/Middleware/CsrfMiddleware.php` |
| B0.7 | Input validation helper: sanitize, validate types, whitelist | — | `src/Infrastructure/Service/Validator.php` |
| B0.8 | Error handler: `set_exception_handler()` + logger básico | — | `src/Infrastructure/Service/ErrorHandler.php`, refactor `index.php` |
| B0.9 | Session security: timeout 30min, regenerate on login, HttpOnly+Secure+SameSite | B0.5 | `src/Infrastructure/Service/SessionManager.php` |
| B0.10 | Rate limiter: login attempts (5/15min IP) + throttle helper | — | `src/Infrastructure/Service/RateLimiter.php` |
| B0.11 | Seeders: admin user (password hasheado), categorías iniciales, rutas básicas | B0.1 | `database/seeds/seed.php` |
| B0.12 | Refactor `index.php`: middleware pipeline (session → auth → csrf → ratelimit → route) | B0.6–B0.10 | `public/index.php` |
| B0.13 | Route params: reemplazar `web.php` plano por router con soporte `{id}` | — | `src/Infrastructure/Web/Router.php`, `web.php` |

### Frontend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| F0.1 | Página 404 personalizada (acorde al diseño) | — | `views/errors/404.php`, CSS |
| F0.2 | Sistema de toasts (CSS + vanilla JS, auto-destruir 4s) | — | `public/js/elyra.js`, CSS en `elyra.css` |
| F0.3 | Breadcrumbs en layout admin (desde `$currentUri`) | — | Actualizar `views/layout/base.php` |
| F0.4 | CSRF token en `fetch()`: helper JS que agrega header `X-CSRF-Token` | F0.3 | `public/js/elyra.js` |

### Security Deliverables Sprint 0
- [x] Passwords hasheados con Argon2id
- [x] CSRF en todos los formularios y fetch
- [x] Prepared statements obligatorios (CR en code review)
- [x] Session con timeout y regeneración
- [x] Rate limiting en login
- [x] Error handler sin leak de información
- [x] CSP headers configurados
- [x] Input sanitization en toda entrada

---

## Sprint 1 — Documentos: CRUD Completo (2 sem)

**Objetivo:** Ciclo completo de vida de un documento: subir, listar, ver, editar, eliminar + QR.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B1.1 | MySQL Repository: `DocumentoRepository`, `CategoriaRepository`, `CodigoQRRepository` | B0.4 | `src/Infrastructure/Persistence/MySQL/*Repository.php` |
| B1.2 | Use Case: `SubirDocumentoUseCase` (validar PDF, generar QR, persistir) | B0.7, B1.1 | `src/Application/UseCases/SubirDocumentoUseCase.php` |
| B1.3 | Use Case: `ListarDocumentosUseCase` (paginación, filtro categoría, búsqueda) | B1.1 | `src/Application/UseCases/ListarDocumentosUseCase.php` |
| B1.4 | Use Case: `EditarDocumentoUseCase` (solo título, descripción, categoría) | B1.1 | `src/Application/UseCases/EditarDocumentoUseCase.php` |
| B1.5 | Use Case: `EliminarDocumentoUseCase` (baja lógica, desactivar QR) | B1.1 | `src/Application/UseCases/EliminarDocumentoUseCase.php` |
| B1.6 | Use Case: `VerDocumentoUseCase` (detalle completo con QR) | B1.1 | `src/Application/UseCases/VerDocumentoUseCase.php` |
| B1.7 | QR Service: generar QR con `chillerlan/php-qrcode` o phpqrcode | — | `src/Infrastructure/Service/QRGeneratorService.php` |
| B1.8 | File Storage: guardar PDF fuera de webroot (`storage/docs/`), servir via PHP | — | `src/Infrastructure/Service/FileStorageService.php` |
| B1.9 | Refactor `DocumentoController`: inyectar casos de uso, validar requests | B1.2–B1.6 | `src/Infrastructure/Web/Controller/DocumentoController.php` |
| B1.10 | Endpoint público: servir PDF por token QR (sin auth) | B1.8 | `PublicController::verDocumento()` |

**Seguridad:**
- Validación de PDF: MIME type `application/pdf`, magic bytes, extensión `.pdf`
- Tamaño máximo: 10MB
- Almacenar fuera de webroot (`storage/docs/`)
- Token QR: UUIDv4 aleatorio, no secuencial, no adivinable
- Path traversal prevention en FileStorageService

### Frontend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| F1.1 | Listado documentos (tabla responsive + cards mobile, search, filter categoría, paginación) | — | `views/documentos/index.php` |
| F1.2 | Subir documento (drag & drop nativo, barra progreso, form con título/categoría/descripción) | — | `views/documentos/subir.php` |
| F1.3 | Editar documento (formulario con datos precargados) | — | `views/documentos/editar.php` |
| F1.4 | Modal QR (QRCode.js, botón copiar link, botón imprimir) | — | `views/documentos/_modal_qr.php` |
| F1.5 | Modal confirmación eliminar | — | `views/documentos/_modal_eliminar.php` |
| F1.6 | Vista pública documento (mobile-first, visor PDF embed, feedback emojis) | — | `views/publico/documento.php` (refactor) |
| F1.7 | Dashboard admin: actualizar stat cards con datos reales | B1.2 | `views/dashboard/index.php` |

---

## Sprint 2 — Encuestas: CRUD + Resultados (2 sem)

**Objetivo:** Crear encuestas con preguntas dinámicas, responder desde mobile, ver resultados con gráficos.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B2.1 | MySQL Repository: `EncuestaRepository`, `PreguntaRepository`, `RespuestaRepository` | B0.4 | `src/Infrastructure/Persistence/MySQL/*Repository.php` |
| B2.2 | Use Case: `CrearEncuestaUseCase` (encuesta + preguntas en transacción) | B0.7, B2.1 | `src/Application/UseCases/CrearEncuestaUseCase.php` |
| B2.3 | Use Case: `PublicarEncuestaUseCase` (activar/desactivar) | B2.1 | `src/Application/UseCases/PublicarEncuestaUseCase.php` |
| B2.4 | Use Case: `ResponderEncuestaUseCase` (validar required, guardar respuestas, sesión anónima) | B0.7, B2.1 | `src/Application/UseCases/ResponderEncuestaUseCase.php` |
| B2.5 | Use Case: `ObtenerResultadosUseCase` (agregaciones, conteos, textos libres) | B2.1 | `src/Application/UseCases/ObtenerResultadosUseCase.php` |
| B2.6 | Refactor `EncuestaController` | B2.2–B2.5 | `src/Infrastructure/Web/Controller/EncuestaController.php` |

**Seguridad:**
- Sesión anónima para responder (token en session, no cookie de auth)
- Rate limiting en envío de respuestas (1 encuesta/5min por sesión)
- Validar que `encuesta_id` exista y esté activa
- Sanitizar texto libre (XSS en resultados)

### Frontend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| F2.1 | Listado encuestas (tabla + estado activa/inactiva con toggle) | — | `views/encuestas/index.php` |
| F2.2 | Crear encuesta (JS dinámico: agregar/quitar preguntas, tipos multiple/escala/texto) | — | `views/encuestas/crear.php` |
| F2.3 | Vista pública responder (mobile-first, validación client-side) | — | `views/publico/encuesta.php` (refactor) |
| F2.4 | Resultados con Chart.js (barras para multiple_choice, torta para escala, listado texto libre) | — | `views/encuestas/resultados.php` |

---

## Sprint 3 — Traslados: CRUD + Dashboard (2 sem)

**Objetivo:** Gestión completa de traslados con timeline, máquina de estados y dashboard en tiempo real.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B3.1 | MySQL Repository: `TrasladoRepository`, `ElementoTrasladoRepository`, `HistorialEstadoRepository` | B0.4 | `src/Infrastructure/Persistence/MySQL/*Repository.php` |
| B3.2 | Use Case: `RegistrarTrasladoUseCase` (validar conductor, fechas, transacción) | B0.7, B3.1 | `src/Application/UseCases/RegistrarTrasladoUseCase.php` |
| B3.3 | Use Case: `ActualizarEstadoTrasladoUseCase` (máquina de estados, historial, timestamps) | B3.1 | `src/Application/UseCases/ActualizarEstadoTrasladoUseCase.php` |
| B3.4 | Use Case: `ListarTrasladosUseCase` (filtros: estado, conductor, fecha) | B3.1 | `src/Application/UseCases/ListarTrasladosUseCase.php` |
| B3.5 | Use Case: `VerDetalleTrasladoUseCase` (timeline completo + datos) | B3.1 | `src/Application/UseCases/VerDetalleTrasladoUseCase.php` |
| B3.6 | Use Case: `HistorialTrasladosUseCase` (paginación, filtros avanzados) | B3.1 | `src/Application/UseCases/HistorialTrasladosUseCase.php` |
| B3.7 | Refactor `TrasladoController` | B3.2–B3.6 | `src/Infrastructure/Web/Controller/TrasladoController.php` |
| B3.8 | Generación automática de código de traslado (`TR-XXX`, secuencial por año) | B3.1 | `TrasladoRepository::nextCodigo()` |

**Máquina de estados:**
```
Pendiente → En curso → En destino → En retorno → Completado
    ↓
  Cancelado (desde cualquier estado antes de Completado)
```

**Seguridad:**
- Validar transiciones de estado en backend (no confiar en frontend)
- Solo `admin`/`superadmin` puede cancelar traslados
- Conductor solo puede ver sus traslados asignados
- Auditoría: `historial_estado` registra quién cambió cada estado

### Frontend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| F3.1 | Dashboard traslados (stat cards: pendientes/en_curso/completados hoy/total + tabla activos) | — | `views/traslados/index.php` |
| F3.2 | Nuevo traslado (form: conductor, copiloto, elemento, tipo, origen/destino, ruta, fechas) | — | `views/traslados/nuevo.php` |
| F3.3 | Detalle + timeline vertical (CSS puro, estados coloreados, fecha/hora) | — | `views/traslados/ver.php` |
| F3.4 | Modal actualizar estado (solo transiciones válidas según estado actual) | — | `views/traslados/_modal_estado.php` |
| F3.5 | Historial traslados (tabla + filtros: fecha, conductor, estado) | — | `views/traslados/historial.php` |
| F3.6 | Stat cards cliqueables: filtran tabla por estado (JS) | F3.1 | JS en `public/js/elyra.js` |

---

## Sprint 4 — Conductores, Rutas, Vehículos + Pulido (1 sem)

**Objetivo:** CRUD de módulos de soporte + mejoras finales de UX.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B4.1 | MySQL Repository: `ConductorRepository` (hereda de UsuarioRepository), `RutaRepository`, `VehiculoRepository` | B0.4 | `src/Infrastructure/Persistence/MySQL/*Repository.php` |
| B4.2 | Use Cases CRUD: `CrearConductorUseCase`, `ListarConductoresUseCase`, `ActualizarConductorUseCase` | B0.7, B4.1 | `src/Application/UseCases/*.php` |
| B4.3 | Use Cases CRUD: `CrearRutaUseCase`, `ListarRutasUseCase`, `ActualizarRutaUseCase` | B0.7, B4.1 | `src/Application/UseCases/*.php` |
| B4.4 | Use Cases CRUD: `CrearVehiculoUseCase`, `ListarVehiculosUseCase` | B0.7, B4.1 | `src/Application/UseCases/*.php` |
| B4.5 | Refactor `ConductorController`, `RutaController` | B4.2–B4.4 | `src/Infrastructure/Web/Controller/*.php` |

### Frontend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| F4.1 | Listado conductores (tabla + búsqueda + estado activo/inactivo) | — | `views/conductores/index.php` (refactor) |
| F4.2 | Crear/editar conductor (formulario con todos los campos de funcionario) | — | `views/conductores/crear.php` (refactor) |
| F4.3 | Listado rutas (tabla con origen, destino, km) | — | `views/rutas/index.php` (refactor) |
| F4.4 | Crear/editar ruta | — | `views/rutas/crear.php` (refactor) |
| F4.5 | Breadcrumbs en todas las vistas admin | — | Todas las vistas |
| F4.6 | Unificar modales (confirmación small, formulario medium, QR medium) | — | `views/layout/_modales.php` |

---

## Sprint 5 — Testing, Auditoría & Preparación Despliegue (1 sem)

**Objetivo:** Calidad, seguridad y deployabilidad.

### Backend

| # | Tarea | Depende |
|---|-------|---------|
| B5.1 | Tests unitarios: Value Objects, Domain Services, Use Cases | Todos los anteriores |
| B5.2 | Tests de integración: Repositories MySQL (con base de datos de test) | B5.1 |
| B5.3 | Tests de seguridad: CSRF bypass, SQL injection, XSS, path traversal | B5.1 |
| B5.4 | Auditoría OWASP Top 10 (lista de verificación) | — |
| B5.5 | Performance test: generación QR < 3s, carga de listados < 2s | — |
| B5.6 | Script de deploy: migraciones, seeders, config servidor | — |
| B5.7 | Documentación técnica: README actualizado, setup instructions | — |

### Frontend

| # | Tarea | Depende |
|---|-------|---------|
| F5.1 | Pruebas responsive en mobile <576px, tablet, desktop | Todos los anteriores |
| F5.2 | Auditoría de accesibilidad: contraste 4.5:1, navegación teclado, aria-labels | — |
| F5.3 | Carga lazy de Chart.js y QRCode.js (solo en páginas que los usan) | — |
| F5.4 | Prueba de flujo completo: login → subir doc → ver QR → escanear → ver doc → responder encuesta | — |

---

## Resumen

| Sprint | Semanas | Backend | Frontend | Total tareas |
|--------|---------|---------|----------|-------------|
| **0** — Fundación & Seguridad | 1-2 | 13 | 4 | 17 |
| **1** — Documentos CRUD | 2 | 10 | 7 | 17 |
| **2** — Encuestas | 2 | 6 | 4 | 10 |
| **3** — Traslados | 2 | 8 | 6 | 14 |
| **4** — Conductores/Rutas + Pulido | 1 | 5 | 6 | 11 |
| **5** — Testing & Deploy | 1 | 7 | 4 | 11 |
| **Total** | **8-10 sem** | **49** | **31** | **80** |

## Dependencias Clave

```
Sprint 0 ──────────────────────────────────┐
    │                                       │
    ├── Sprint 1 (Documentos) ───┐          │
    │                            │          │
    ├── Sprint 2 (Encuestas) ◄───┘          │
    │                                        │
    └── Sprint 3 (Traslados) ───────────────┘
                                              │
                    Sprint 4 (Conductores/Rutas)
                                              │
                    Sprint 5 (Testing/Deploy) ◄┘
```

- Sprint 0 es **requisito** para todos los demás
- Sprint 1 y 2 pueden solaparse parcialmente (equipos separados)
- Sprint 3 puede empezar después de Sprint 0 (no depende de 1 o 2)
- Sprint 4 puede empezar después de Sprint 3
- Sprint 5 es final

## Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| Cambio de requerimientos del hospital | Media | Alto | Sprints cortos (1-2 sem), mostrar incrementos temprano |
| Dependencia del sistema de autenticación del hospital | Alta | Alto | Implementar auth propio como fallback (Sprint 0), integrar después |
| Aprendizaje de arquitectura hexagonal | Media | Medio | Pair programming, code reviews |
| Falta de datos reales para pruebas | Baja | Medio | Seeders con datos ficticios realistas |
| Problemas de deploy en servidores del DTI | Media | Alto | Dockerizar la aplicación, script de deploy automatizado |
