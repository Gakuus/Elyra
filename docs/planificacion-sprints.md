# Planificación de Sprints — Elyra

> **Contexto:** Proyecto de egreso. Arquitectura hexagonal, PHP 8.5 + MySQL, Bootstrap 5 vanilla JS.
> **Estado actual (Jul 2026):** Sprint 0 completo. Backend: dominio, infraestructura, servicios y web layer listos. Frontend: vistas implementadas con tema Windows clásico. Falta la capa de Aplicación (Use Cases) y 2 repos MySQL.
> **Enfoque:** Security-first. Cada sprint incluye medidas de seguridad explícitas.

---

## ⚠️ REGLA FUNDAMENTAL

> **El frontend NUNCA contiene secretos, tokens de API, credenciales ni lógica de autenticación.**
> **El backend SIEMPRE valida todo, aunque el frontend ya lo haya validado.**
> **No hay contraseñas en texto plano. No hay tokens en JS. No hay secrets en `.env` commiteado.**
> **Si se vulnera una de estas reglas, la tarea se rechaza en code review.**

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
| Rol-based access | admin / superadmin / conductor / paciente | 0 |

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
- [x] Passwords hasheados con Argon2id
- [x] CSRF en todos los formularios y fetch
- [x] Prepared statements obligatorios
- [x] Session con timeout y regeneración
- [x] Rate limiting en login
- [x] Error handler sin leak de información
- [x] CSP headers configurados
- [x] Input sanitization en toda entrada

---

## Sprint 1 — Application Layer + Repos Faltantes (2-3 sem)

**Objetivo:** Implementar la capa de Casos de Uso (Application Layer) para desacoplar controllers de repos, implementar los 2 repos MySQL faltantes, y refactorizar controllers para inyectar use cases.

### Backend

| # | Tarea | Depende | Archivos destino |
|---|-------|---------|------------------|
| B1.1 | Use Case: `SubirDocumentoUseCase` (validar PDF, generar QR, persistir) | B0.7 | `src/Application/UseCases/Documento/SubirDocumentoUseCase.php` |
| B1.2 | Use Case: `ListarDocumentosUseCase` (paginación, filtro categoría, búsqueda) | — | `src/Application/UseCases/Documento/ListarDocumentosUseCase.php` |
| B1.3 | Use Case: `EditarDocumentoUseCase` (solo título, descripción, categoría) | — | `src/Application/UseCases/Documento/EditarDocumentoUseCase.php` |
| B1.4 | Use Case: `EliminarDocumentoUseCase` (baja lógica, desactivar QR) | — | `src/Application/UseCases/Documento/EliminarDocumentoUseCase.php` |
| B1.5 | Use Case: `VerDocumentoUseCase` (detalle completo con QR) | — | `src/Application/UseCases/Documento/VerDocumentoUseCase.php` |
| B1.6 | MySQL Repository: `TrasladoRepository` (implementa `TrasladoRepositoryInterface`) | B0.4 | `src/Infrastructure/Persistence/MySQL/TrasladoRepository.php` |
| B1.7 | Use Case: `RegistrarTrasladoUseCase` (validar conductor, fechas, transacción) | B0.7, B1.6 | `src/Application/UseCases/Traslado/RegistrarTrasladoUseCase.php` |
| B1.8 | Use Case: `ActualizarEstadoTrasladoUseCase` (máquina de estados, historial, timestamps) | B1.6 | `src/Application/UseCases/Traslado/ActualizarEstadoTrasladoUseCase.php` |
| B1.9 | Use Case: `ListarTrasladosUseCase` (filtros: estado, conductor, fecha) | B1.6 | `src/Application/UseCases/Traslado/ListarTrasladosUseCase.php` |
| B1.10 | Use Case: `VerDetalleTrasladoUseCase` (timeline completo + datos) | B1.6 | `src/Application/UseCases/Traslado/VerDetalleTrasladoUseCase.php` |
| B1.11 | Use Case: `HistorialTrasladosUseCase` (paginación, filtros avanzados) | B1.6 | `src/Application/UseCases/Traslado/HistorialTrasladosUseCase.php` |
| B1.12 | MySQL Repository: `ConductorRepository` (implementa `ConductorRepositoryInterface`) | B0.4 | `src/Infrastructure/Persistence/MySQL/ConductorRepository.php` |
| B1.13 | Use Case: `CrearConductorUseCase`, `ListarConductoresUseCase`, `ActualizarConductorUseCase` | B1.12 | `src/Application/UseCases/Conductor/*.php` |
| B1.14 | Use Cases CRUD: `CrearRutaUseCase`, `ListarRutasUseCase`, `ActualizarRutaUseCase` | — | `src/Application/UseCases/Ruta/*.php` |
| B1.15 | Use Case: `CrearEncuestaUseCase` (encuesta + preguntas en transacción) | B0.7 | `src/Application/UseCases/Encuesta/CrearEncuestaUseCase.php` |
| B1.16 | Use Case: `PublicarEncuestaUseCase` (activar/desactivar) | — | `src/Application/UseCases/Encuesta/PublicarEncuestaUseCase.php` |
| B1.17 | Use Case: `ResponderEncuestaUseCase` (validar required, guardar respuestas, sesión anónima) | B0.7 | `src/Application/UseCases/Encuesta/ResponderEncuestaUseCase.php` |
| B1.18 | Use Case: `ObtenerResultadosUseCase` (agregaciones, conteos, textos libres) | — | `src/Application/UseCases/Encuesta/ObtenerResultadosUseCase.php` |
| B1.19 | QR Service: generar QR con `chillerlan/php-qrcode` — `QRGeneratorService` | — | `src/Infrastructure/Service/QRGeneratorService.php` |
| B1.20 | File Storage: guardar PDF fuera de webroot (`storage/docs/`), servir via PHP — `FileStorageService` | — | `src/Infrastructure/Service/FileStorageService.php` |
| B1.21 | Refactor `DocumentoController`: inyectar use cases, eliminar llamadas directas a repos | B1.1–B1.5 | `src/Infrastructure/Web/Controller/DocumentoController.php` |
| B1.22 | Refactor `TrasladoController`: inyectar use cases | B1.7–B1.11 | `src/Infrastructure/Web/Controller/TrasladoController.php` |
| B1.23 | Refactor `EncuestaController`: inyectar use cases | B1.15–B1.18 | `src/Infrastructure/Web/Controller/EncuestaController.php` |
| B1.24 | Refactor `ConductorController`, `RutaController`: inyectar use cases | B1.13–B1.14 | `src/Infrastructure/Web/Controller/*.php` |
| B1.25 | Endpoint público: servir PDF por token QR (sin auth) | B1.20 | `PublicController::verDocumento()` |

**Seguridad:**
- Validación de PDF: MIME type `application/pdf`, magic bytes, extensión `.pdf`
- Tamaño máximo: 10MB
- Almacenar fuera de webroot (`storage/docs/`)
- Token QR: UUIDv4 aleatorio, no secuencial, no adivinable
- Path traversal prevention en FileStorageService
- Validar transiciones de estado en backend (no confiar en frontend)
- Solo `admin`/`superadmin` puede cancelar traslados
- Conductor solo puede ver sus traslados asignados
- Auditoría: `historial_estado` registra quién cambió cada estado
- Sesión anónima para responder encuestas

**Máquina de estados traslados:**
```
Pendiente → En curso → En destino → En retorno → Completado
    ↓
  Cancelado (desde cualquier estado antes de Completado)
```

### Frontend

Ya implementado en sprints previos. No hay tareas nuevas para este sprint.

| # | Tarea | Estado |
|---|-------|--------|
| F1.1 | Listado documentos (tabla responsive, search, filtro, paginación) | ✅ |
| F1.2 | Subir documento (drag & drop, barra progreso) | ✅ |
| F1.3 | Editar documento (formulario con datos precargados) | ✅ |
| F1.4 | Modal QR (QRCode.js, copiar link, imprimir) | ✅ |
| F1.5 | Modal confirmación eliminar | ✅ |
| F1.6 | Vista pública documento (mobile-first, visor PDF embed) | ✅ |
| F1.7 | Dashboard admin con stat cards reales | ✅ |
| F1.8 | Listado encuestas (tabla + toggle activo/inactivo) | ✅ |
| F1.9 | Crear encuesta (JS dinámico: agregar/quitar preguntas) | ✅ |
| F1.10 | Vista pública responder encuesta | ✅ |
| F1.11 | Resultados con Chart.js (barras, torta, texto libre) | ✅ |
| F1.12 | Dashboard traslados (stat cards + tabla activos) | ✅ |
| F1.13 | Nuevo traslado (form completo) | ✅ |
| F1.14 | Detalle + timeline vertical (CSS puro) | ✅ |
| F1.15 | Modal actualizar estado (transiciones válidas) | ✅ |
| F1.16 | Historial traslados (tabla + filtros) | ✅ |
| F1.17 | Listado conductores (tabla + búsqueda) | ✅ |
| F1.18 | Crear/editar conductor | ✅ |
| F1.19 | Listado rutas (tabla origen/destino/km) | ✅ |
| F1.20 | Crear/editar ruta | ✅ |
| F1.21 | Breadcrumbs en todas las vistas admin | ✅ |
| F1.22 | Tema Windows clásico (`.win-panel`, `.win-titlebar`, `.win-table`, etc.) | ✅ |
| F1.23 | Documentos separados: generales / por paciente / paciente autenticado | ✅ |
| F1.24 | Buscador por CI de 8 dígitos en documentos de paciente | ✅ |

---

## Sprint 2 — Panel Paciente (1 sem)

**Objetivo:** Vista dedicada para pacientes autenticados con su información personal, documentos, encuestas y traslados. Todo con estilo Windows clásico.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F2.1 | Dashboard paciente rediseñado: avatar con iniciales, datos personales, cards de resumen (docs, encuestas, traslados) | `views/dashboard/paciente.php`, `public/css/classic.css` |
| F2.2 | Historial de documentos del paciente (tabla win-table, búsqueda, filtro por tipo) | `views/documentos/index.php` (refactor vista paciente) |
| F2.3 | Encuestas pendientes vs completadas con progress bar y acceso directo | `views/dashboard/paciente.php` |
| F2.4 | Traslados asignados al paciente (si aplica como conductor) con timeline reducido | `views/dashboard/paciente.php` |
| F2.5 | Perfil paciente editable: cambiar contraseña, email, teléfono desde panel clásico | `views/perfil/index.php` (refactor) |
| F2.6 | Navegación paciente con win-navbar propio (menú reducido: Inicio, Mis Docs, Encuestas, Perfil) | `views/layout/base.php` |

---

## Sprint 3 — Reportes & Gráficos (1-2 sem)

**Objetivo:** Dashboard administrativo con gráficos en tiempo real, exportación de reportes y estadísticas del sistema. Estilo Windows clásico en paneles y charts.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F3.1 | Dashboard admin con gráficos Chart.js: docs por categoría (torta), traslados por mes (barras), encuestas respondidas (lineal) | `views/dashboard/index.php`, `public/js/elyra.js` |
| F3.2 | Top pacientes con más documentos (tabla + mini gráfico) | `views/dashboard/index.php` |
| F3.3 | Vista de estadísticas generales: cards con totales, promedios, evolución mensual | `views/dashboard/estadisticas.php` |
| F3.4 | Exportar listados a CSV (docs, traslados, encuestas) con botón win-btn en tablas | Vistas de listados + `controllers` |
| F3.5 | Exportar reporte completo a PDF (dashboard + gráficos) | `views/dashboard/exportar.php`, `public/js/elyra.js` |
| F3.6 | Filtros por rango de fechas en dashboard y reportes (date picker clásico) | `public/css/classic.css`, vistas de dashboard |

---

## Sprint 4 — UX Avanzado (1-2 sem)

**Objetivo:** Funcionalidades de interacción avanzada: calendario de traslados, mapa de rutas, búsqueda global y drag & drop. Todo integrado al tema Windows clásico.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F4.1 | Calendario de traslados con FullCalendar o vanilla JS: vista mensual, eventos por estado (coloreados), clic para ver detalle | `views/traslados/calendario.php`, `public/js/elyra.js` |
| F4.2 | Mapa de rutas con Leaflet + OpenStreetMap: mostrar origen/destino/paradas en mapa interactivo | `views/traslados/mapa.php`, `views/rutas/index.php` |
| F4.3 | Búsqueda global: searchbar en win-navbar que busca en docs, pacientes, traslados y encuestas con resultados en dropdown | `views/layout/base.php`, `public/js/elyra.js` |
| F4.4 | Drag & drop en dashboard admin: reordenar widgets (stat cards, gráficos, tablas) con persistencia en localStorage | `views/dashboard/index.php`, `public/js/elyra.js` |
| F4.5 | Notificaciones toast mejoradas: stack de toasts, tipos (éxito/error/warning/info), auto-destruir con progreso visual | `public/js/elyra.js`, `public/css/classic.css` |
| F4.6 | Atajos de teclado: Ctrl+Enter para guardar, Escape para cerrar modal, / para buscar | `public/js/elyra.js` |

---

## Sprint 5 — PWA & Mobile (1 sem)

**Objetivo:** Convertir la app en Progressive Web App instalable con soporte offline parcial y experiencia mobile óptima. Tema clásico adaptado a pantallas pequeñas.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F5.1 | Manifest.json con iconos, nombre corto, tema color, display standalone | `public/manifest.json` |
| F5.2 | Service worker: cachear CSS/JS/img, servir offline page cuando no hay red | `public/sw.js` |
| F5.3 | Botón "Instalar app" en win-statusbar cuando el navegador soporte beforeinstallprompt | `views/layout/base.php`, `public/js/elyra.js` |
| F5.4 | Responsive final: probar y ajustar todas las vistas en <576px, tablet y desktop | Todas las vistas + `public/css/classic.css` |
| F5.5 | Touch optimizations: aumentar targets táctiles a 44px, swipe gestures en listados | `public/css/classic.css` |
| F5.6 | Offline page personalizada con estilo Windows clásico (windlogo, mensaje) | `views/errors/offline.php`, `public/css/classic.css` |

---

## Sprint 6 — Polish & Accesibilidad (1 sem)

**Objetivo:** Último pulido de calidad: animaciones, accesibilidad WCAG AA, rendimiento y consistencia visual.

| # | Tarea | Archivos destino |
|---|-------|------------------|
| F6.1 | Animaciones CSS fluidas: hover en win-btn, fade en modales, slide en timeline, skeleton screens en carga | `public/css/classic.css` |
| F6.2 | Loading states: botón con spinner al enviar, esqueletos en tablas mientras cargan, placeholder en imágenes | Vistas + `public/js/elyra.js` |
| F6.3 | Navegación por teclado completa: Tab order lógico, focus visible, skip-to-content link | Todas las vistas |
| F6.4 | Contraste WCAG AA (4.5:1 mínimo): verificar y ajustar colores del tema clásico | `public/css/classic.css` |
| F6.5 | Aria labels en todos los componentes interactivos: botones, enlaces, iconos, modales, tablas | Vistas |
| F6.6 | Lazy loading de Chart.js y QRCode.js: solo cargar en páginas que los usan (code splitting manual) | `views/layout/base.php` |
| F6.7 | Unificar modales: altura/anchura consistentes, animación de entrada, scroll lock, foco atrapado | `views/layout/_modales.php` |

---

## Sprint 7 — Testing, Auditoría & Preparación Despliegue (1-2 sem)

**Objetivo:** Calidad, seguridad y deployabilidad.

### Backend

| # | Tarea | Depende |
|---|-------|---------|
| B7.1 | Tests unitarios: Value Objects, Domain Services, Use Cases | Sprint 1 |
| B7.2 | Tests de integración: Repositories MySQL (con base de datos de test) | B7.1 |
| B7.3 | Tests de seguridad: CSRF bypass, SQL injection, XSS, path traversal | B7.1 |
| B7.4 | Auditoría OWASP Top 10 (lista de verificación) | — |
| B7.5 | Script de deploy: migraciones, seeders, config servidor | — |
| B7.6 | Dockerizar la aplicación (Dockerfile + docker-compose.yml) | — |
| B7.7 | Documentación técnica: README actualizado, setup instructions | — |

### Frontend

| # | Tarea |
|---|-------|
| F7.1 | Prueba de flujo completo: login → subir doc → ver QR → escanear → ver doc → responder encuesta |
| F7.2 | Performance audit: Lighthouse, carga inicial < 3s, PWA audit |
| F7.3 | Última revisión de contraste y legibilidad en tema clásico |

---

## Resumen

| Sprint | Semanas | Backend | Frontend | Total |
|--------|---------|---------|----------|-------|
| **0** — Fundación & Seguridad | ✅ | 13 | 5 | 18 |
| **1** — Application Layer + Repos | 2-3 | 25 | — | 25 |
| **2** — Panel Paciente | 1 | — | 6 | 6 |
| **3** — Reportes & Gráficos | 1-2 | — | 6 | 6 |
| **4** — UX Avanzado | 1-2 | — | 6 | 6 |
| **5** — PWA & Mobile | 1 | — | 6 | 6 |
| **6** — Polish & Accesibilidad | 1 | — | 7 | 7 |
| **7** — Testing & Deploy | 1-2 | 7 | 3 | 10 |
| **Total** | **8-12 sem** | **45** | **39** | **84** |

## Dependencias Clave

```
Sprint 0 (completado) ──────────────────────────────────┐
                                                          │
               Sprint 1 (Application Layer) ◄─────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
  Sprint 2 (Panel Pte) ──┐                     │
        │                 │                     │
        ▼                 ▼                     │
  Sprint 3 (Reportes) ──┐                      │
        │                 │                     │
        ▼                 ▼                     │
  Sprint 4 (UX Avanz) ──┐                      │
        │                 │                     │
        ▼                 ▼                     ▼
  Sprint 5 (PWA) ◄───────┘              Sprint 7 (Testing)
        │                                      │
        ▼                                      │
  Sprint 6 (Polish) ◄──────────────────────────┘
```

- **Sprint 0** es prerrequisito de todo — ✅ completado
- **Sprint 1** es prerrequisito de Sprint 7 (testing)
- **Sprints 2–6** son secuenciales entre sí pero **independientes de Sprint 1** (puro frontend)
- **Sprint 7** requiere Sprint 1 (testing backend) y Sprint 6 (frontend completo)
- Todos los sprints frontend mantienen el **tema Windows clásico** como identidad visual
