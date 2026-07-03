# Pendientes — Frontend Elyra

> **Hecho:** Login + Layout base (navbar con dropdowns) + Homepage pública + Dashboard admin

---

## Épica 1 — Identidad

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | Listado de funcionarios (tabla + búsqueda) | Should | Backend (CRUD usuarios) |
| 2 | Formulario crear/editar funcionario | Should | Backend |
| 3 | Perfil de usuario (ver/editar teléfono, email) | Could | Backend |

**Nota:** Las vistas de login ya están OK. Registro de pacientes se crea automáticamente desde traslados (back-end).

---

## Épica 2 — Documentación: Documentos

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | **Listado con tabla, filtro categoría, búsqueda, paginación** | Must | Backend (listar docs) |
| 2 | **Formulario subir PDF (título, categoría, drag & drop)** | Must | Backend (subir archivo) |
| 3 | **Detalle del documento (QR, copiar link, descripción)** | Must | Backend (generar QR) |
| 4 | **Modal QR** (icono 👁 en tabla) | Must | Backend (ruta QR) |
| 5 | **Modal confirmación eliminar** | Should | Backend (eliminar) |
| 6 | Formulario editar (título, descripción, categoría) | Should | Backend |
| 7 | Modal "Asociar encuesta" | Should | Backend |

**Decisiones técnicas:**
- QR: QRCode.js (generación client-side si el backend da el token, o imagen desde backend)
- Drag & drop: nativo (File API) + Bootstrap, sin librería externa
- Subida: fetch + FormData con barra de progreso si da tiempo

---

## Épica 3 — Documentación: Encuestas

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | **Responder encuesta desde mobile** (público, sin auth) | Must | Backend (guardar respuestas) |
| 2 | Listado de encuestas (tabla + estado activa/inactiva) | Should | Backend |
| 3 | Crear encuesta (agregar preguntas dinámicamente) | Should | Backend |
| 4 | Resultados con Chart.js (barras, torta, texto libre) | Should | Backend + Chart.js |

**Decisiones técnicas:**
- Chart.js para todos los gráficos
- Creación de encuestas puede ser un formulario largo con JS para agregar/quitar preguntas dinámicamente

---

## Épica 4 — Documentación: Vista Pública

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | **Vista pública del documento por QR** (layout mobile-first, visor PDF, descargar) | Must | Backend (ruta pública) |
| 2 | Feedback de utilidad (3 emojis, fetch POST sin recargar) | Should | Backend (guardar voto) |
| 3 | Encuesta pública (formulario responsive) | Must | Backend |

**Nota:** La vista pública NO usa sidebar. En `base.php` ya está contemplado: cuando no hay sesión se renderiza solo el contenido. La vista se encarga de su propio layout.

---

## Épica 5 — Ambulancias: Traslados

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | **Dashboard con stat cards + tabla de traslados activos** | Must | Backend |
| 2 | **Formulario nuevo traslado** (conductor, elemento, origen/destino, ruta, fechas) | Must | Backend |
| 3 | **Detalle del traslado + timeline vertical de estados** | Must | Backend |
| 4 | **Modal actualizar estado** (solo transiciones válidas) | Must | Backend |
| 5 | Historial con filtros (fecha, conductor, estado) | Should | Backend |
| 6 | Modal confirmación cancelar traslado | Should | Backend |

**Máquina de estados (para el timeline):**
```
Pendiente → En curso → En destino → En retorno → Completado
    ↓
  Cancelado (desde cualquier estado antes de Completado)
```

**Decisiones técnicas:**
- Stat cards: anchor links que filtran la tabla por estado (JS del lado cliente)
- Timeline: CSS puro (vertical steps con pseudoelementos), sin librería

---

## Épica 6 — Ambulancias: Rutas y Conductores

| # | Vista | Prioridad | Dependencia |
|---|-------|-----------|-------------|
| 1 | Listado de rutas (tabla CRUD) | Should | Backend |
| 2 | Formulario crear/editar ruta | Should | Backend |
| 3 | Listado de conductores (tabla CRUD) | Should | Backend |
| 4 | Formulario crear/editar conductor | Should | Backend |

---

## Homepage Pública

| # | Elemento | Estado | Notas |
|---|----------|--------|-------|
| 1 | Hero con fondo gradiente + imagen del hospital | [x] | Igual que login pero más grande |
| 2 | Navbar pública (blanca, links suaves) | [x] | Inicio, Noticias, Servicios, Contacto, Acceso interno |
| 3 | Sección de noticias (3 cards placeholder) | [x] | Fechas reales de hc.edu.uy |
| 4 | Sección de servicios (módulos del sistema) | [x] | Cards con iconos |
| 5 | Footer institucional + Universidad de la República | [x] | |
| 6 | **Poblar noticias desde backend** | [ ] | Requiere tabla `noticias` en DB |

**Ruteo:**
- `/` → `PublicController::home` (homepage pública)
- `/login` → `AuthController::login` (login, movido de `/`)
- `/dashboard` → `DashboardController::index` (admin post-login)

---

## Generales / Infraestructura

| # | Tarea | Prioridad |
|---|-------|-----------|
| 1 | **Página 404 personalizada** (acorde al diseño) | Must |
| 2 | Sistema de notificaciones toast (copiar link, operaciones exitosas) | Should |
| 3 | Breadcrumbs en todas las páginas internas | Should |
| 4 | Unificar modales: confirmación (small), formulario (medium), QR (medium) | Should |
| 5 | Responsive: probar todas las vistas en mobile (<576px), tablet, desktop | Must |
| 6 | Accesibilidad: contraste 4.5:1, navegación teclado, aria-labels en inputs sin label visible | Should |

---

## Resumen por prioridad

| Prioridad | Cantidad |
|-----------|----------|
| **Must**  | **10 vistas** (prioritarias para MVP) |
| **Should** | **15 vistas** |
| **Could**  | **1 vista** |

## Leyenda de progreso

- [ ] No empezado
- [/] En progreso
- [x] Completado
