# Elyra

> Sistema de gestión hospitalaria para el **Hospital de Clínicas**.
> Interfaz de escritorio con temática **Windows clásica**.

[![License: CC BY-NC 4.0](https://img.shields.io/badge/License-CC_BY--NC_4.0-lightgrey.svg)](https://creativecommons.org/licenses/by-nc/4.0/)
[![CI](https://github.com/Gakuus/Elyra/actions/workflows/ci.yml/badge.svg)](https://github.com/Gakuus/Elyra/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-%3E=8.1-777BB4?logo=php&logoColor=white)](https://php.net)
[![GitHub last commit](https://img.shields.io/github/last-commit/Gakuus/Elyra)](https://github.com/Gakuus/Elyra/commits)
[![Repo size](https://img.shields.io/github/repo-size/Gakuus/Elyra)](https://github.com/Gakuus/Elyra)
[![GitHub contributors](https://img.shields.io/github/contributors/Gakuus/Elyra)](https://github.com/Gakuus/Elyra/graphs/contributors)
[![Project Board](https://img.shields.io/badge/Project-Board-2A4780)](https://github.com/users/Gakuus/projects/1)

## Tabla de contenidos

- [Funcionalidades](#funcionalidades)
- [Stack tecnológico](#stack-tecnológico)
- [Arquitectura](#arquitectura)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Base de datos](#base-de-datos)
- [Roles y permisos](#roles-y-permisos)
- [Instalación](#instalación)
- [Desarrollo](#desarrollo)
- [API de rutas](#api-de-rutas)
- [Tema visual](#tema-visual)
- [Roadmap](#roadmap)

---

## Funcionalidades

### Gestión de documentación para pacientes
- Carga y clasificación de documentos informativos por categorías.
- Acceso público mediante código QR por paciente.
- Panel de administración con búsqueda por cédula, filtros y paginación.
- Vista pública de documentos asociados al paciente.

### Encuestas de satisfacción
- Creación de encuestas con preguntas de opción múltiple, escala (1–5) y texto libre.
- Respuesta anónima o vinculada a paciente mediante token QR.
- Visualización de resultados con gráficos estadísticos (Chart.js):
  - Barras horizontales para opción múltiple.
  - Doughnut + promedio para escala.
  - Lista de respuestas para texto libre.

### Trazabilidad de ambulancias
- Registro de traslados (origen, destino, paciente, conductor, vehículo).
- Seguimiento de estados: pendiente → en curso → completado / cancelado.
- Historial de traslados con filtros.
- Gestión de conductores y rutas nacionales.

### Perfil de usuario
- Autogestión de email, teléfono y contraseña.
- Foto de perfil con validación de tipo, tamaño y contenido (imagen real).
- Cédula de identidad (editable una sola vez).

### Panel de administración
- Dashboard con acceso diferenciado por rol.
- Vistas para gestión de documentos, encuestas, traslados, conductores y rutas.

---

## Stack tecnológico

| Capa        | Tecnología                          |
|-------------|-------------------------------------|
| Lenguaje    | PHP 8.5                             |
| Base de datos | MySQL / MariaDB                   |
| Frontend    | HTML5, CSS3, JavaScript (ES6+)      |
| CSS framework | Bootstrap 5                       |
| Gráficos    | Chart.js 4                          |
| Iconos      | Bootstrap Icons                     |
| Arquitectura  | Hexagonal (Puertos y Adaptadores) + DDD |

---

## Arquitectura

El proyecto sigue una arquitectura hexagonal (puertos y adaptadores) con principios de Domain-Driven Design.

```
┌─────────────────────────────────────────────────┐
│                  Infrastructure                  │
│  ┌──────────────┐  ┌────────────────────────┐   │
│  │  Persistence  │  │    Web (Controllers)   │   │
│  │  (MySQL PDO)  │  │   Views / Middleware   │   │
│  └──────┬───────┘  └───────────┬────────────┘   │
│         │                      │                  │
├─────────┴──────────────────────┴─────────────────┤
│                   Application                     │
│           (Puertos / Casos de uso)                │
├──────────────────────┬───────────────────────────┤
│                     Domain                        │
│    Entidades · Value Objects · Repositorios       │
└──────────────────────────────────────────────────┘
```

Las vistas se renderizan con PHP plano (sin motor de templates) y están separadas de la lógica de dominio.

---

## Estructura del proyecto

```
/
├── database/
│   ├── schema.sql          → Esquema completo de la base de datos
│   └── seeds/seed.php      → Datos de prueba iniciales
├── public/
│   ├── index.php           → Punto de entrada (front controller)
│   ├── router.php          → Router para el servidor embebido
│   ├── css/classic.css     → Tema Windows clásico
│   └── js/ui.js            → UI (modo oscuro, toggles)
├── src/
│   ├── Domain/
│   │   ├── Entity/         → Usuario, Paciente, Funcionario, Documento,
│   │   │                     Encuesta, Pregunta, Respuesta, Traslado, etc.
│   │   ├── ValueObject/    → RolUsuario, TipoPregunta, etc.
│   │   └── Repository/     → Interfaces de repositorio
│   ├── Application/        → Casos de uso / servicios de aplicación
│   └── Infrastructure/
│       ├── Persistence/MySQL/  → Implementaciones PDO de repositorios
│       ├── Web/
│       │   ├── Controller/    → Controladores
│       │   ├── Middleware/     → CSRF, rate limiting
│       │   ├── Router.php     → Enrutador simple
│       │   └── Routes/web.php → Definición de rutas
│       └── Service/           → SessionManager, RateLimiter, ErrorHandler
├── storage/
│   ├── logs/              → Logs de errores
│   └── rate-limit/        → Archivos de rate limiting
├── vendor/                → Dependencias (Composer)
├── views/
│   ├── layout/base.php    → Layout principal
│   ├── auth/              → Login, registro
│   ├── dashboard/         → Panel principal
│   ├── perfil/            → Perfil de usuario
│   ├── documentos/        → Gestión de documentos
│   ├── encuestas/         → Encuestas y resultados
│   ├── traslados/         → Ambulancias
│   ├── conductores/       → Conductores
│   ├── rutas/             → Rutas
│   ├── publico/           → Vistas públicas (QR, encuestas)
│   └── errors/            → 404, 500
├── .env                   → Configuración local
├── .env.example           → Template de configuración
└── composer.json
```

---

## Base de datos

### Esquema principal

- **`usuario`** — Tabla base (tipo: funcionario | paciente, nombre, apellido, email, documento_identidad, foto).
- **`funcionario`** — Administradores, superadmins y conductores (username, password_hash, rol, licencia, activo).
- **`paciente`** — Pacientes (token_acceso, codigo_qr_id, username opcional).
- **`documento`** — Documentos informativos (título, descripción, archivo, categoría, paciente asociado).
- **`categoria`** — Categorías de documentos con tipo (tipo_documento | tipo_formulario).
- **`encuesta`** — Encuestas de satisfacción (título, descripción, activa, creada_por).
- **`pregunta`** — Preguntas (tipo, texto, opciones JSON, orden).
- **`respuesta`** — Respuestas (sesion_token, valor_opcion, valor_texto, valor_numerico).
- **`traslado`** — Traslados en ambulancia (origen, destino, paciente, conductor, estado).
- **`conductor`** — Conductores registrados (nombre, licencia, activo).
- **`ruta`** — Rutas nacionales (origen, destino, distancia_km, duracion_estimada).

> Para migrar una base existente sin la columna `foto`:
> ```sql
> ALTER TABLE usuario ADD COLUMN foto LONGBLOB NULL AFTER documento_identidad;
> ```

---

## Roles y permisos

| Rol          | Acceso                                                |
|-------------|--------------------------------------------------------|
| `admin`      | Gestión completa: documentos, encuestas, traslados, conductores, rutas. |
| `superadmin` | Acceso total, incluyendo administración de usuarios.   |
| `conductor`  | Visión limitada a traslados asignados y rutas.         |
| `paciente`   | Acceso público vía QR: ver documentos propios, responder encuestas. |

El middleware de autenticación redirige a `/login` si no hay sesión activa, excepto en rutas públicas (`/publico/*`, `/login`, `/registro`).

---

## Instalación

```bash
# 1. Clonar
git clone <repo-url>
cd elyra

# 2. Dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
# Editar .env con credenciales de base de datos:
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=elyra
#   DB_USERNAME=elyra
#   DB_PASSWORD=elyra_pass

# 4. Crear base de datos e importar schema
mysql -u elyra -p elyra < database/schema.sql

# 5. (Opcional) Poblar con datos de prueba
#     Crea un admin (admin/admin) y datos de ejemplo
php database/seeds/seed.php

# 6. Iniciar servidor de desarrollo
php8.5 -S 127.0.0.1:8084 -t public
```

> **Importante**: Usar `php8.5` (tiene `pdo_mysql`). `php` por defecto (8.4) no incluye el driver.

---

## Desarrollo

### Workflow de ramas

```bash
git checkout -b <nombre>-features
# Trabajar en commits pequeños y descriptivos
git commit -m "tipo: descripción breve"
git push origin <nombre>-features
# Al finalizar, abrir Pull Request contra main
```

### Servidor de desarrollo

```bash
php8.5 -S 127.0.0.1:8084 -t public
# El servidor recarga automáticamente los cambios en PHP.
```

### Logs

Los errores se registran en `storage/logs/YYYY-MM-DD.log`. El nivel de detalle se controla con `APP_DEBUG` en `.env`.

---

## API de rutas

### Públicas (sin autenticación)

| Método | Ruta                  | Controlador         | Descripción                     |
|--------|-----------------------|---------------------|---------------------------------|
| GET    | `/`                   | PublicController    | Página de inicio (home público) |
| GET    | `/login`              | AuthController      | Formulario de inicio de sesión  |
| POST   | `/login`              | AuthController      | Procesar login                  |
| GET    | `/registro`           | AuthController      | Formulario de registro          |
| POST   | `/registro`           | AuthController      | Procesar registro               |
| GET    | `/publico/doc`        | PublicController    | Ver documento vía QR            |
| GET    | `/publico/archivo`    | PublicController    | Descargar archivo vía QR        |
| GET    | `/publico/mis-documentos` | PublicController | Documentos del paciente (vía QR)|
| GET    | `/publico/encuesta`   | PublicController    | Mostrar encuesta                |
| POST   | `/publico/encuesta`   | PublicController    | Responder encuesta              |

### Autenticadas (requieren sesión)

| Método | Ruta                                | Controlador         | Descripción                  |
|--------|--------------------------------------|---------------------|------------------------------|
| GET    | `/dashboard`                         | DashboardController | Panel principal según rol   |
| GET    | `/logout`                            | AuthController      | Cerrar sesión               |
| GET    | `/perfil`                            | PerfilController    | Ver perfil propio           |
| POST   | `/perfil`                            | PerfilController    | Actualizar perfil           |
| GET    | `/documentos`                        | DocumentoController | Listado general             |
| GET    | `/documentos/subir`                  | DocumentoController | Formulario de carga         |
| POST   | `/documentos/subir`                  | DocumentoController | Procesar carga              |
| GET    | `/documentos/editar`                 | DocumentoController | Formulario de edición       |
| POST   | `/documentos/editar`                 | DocumentoController | Procesar edición            |
| GET    | `/documentos/eliminar`               | DocumentoController | Eliminar documento          |
| GET    | `/documentos/ver`                    | DocumentoController | Ver detalle                 |
| GET    | `/documentos/archivo`                | DocumentoController | Descargar archivo           |
| GET    | `/documentos/generales`              | DocumentoController | Documentos generales        |
| GET    | `/documentos/paciente`               | DocumentoController | Buscar por CI de paciente   |
| GET    | `/encuestas`                         | EncuestaController  | Listado de encuestas        |
| GET    | `/encuestas/crear`                   | EncuestaController  | Formulario de creación      |
| POST   | `/encuestas/crear`                   | EncuestaController  | Procesar creación           |
| GET    | `/encuestas/resultados`              | EncuestaController  | Resultados con gráficos     |
| GET    | `/traslados`                         | TrasladoController  | Listado de traslados        |
| GET    | `/traslados/nuevo`                   | TrasladoController  | Formulario de nuevo traslado|
| POST   | `/traslados/nuevo`                   | TrasladoController  | Procesar nuevo traslado     |
| GET    | `/traslados/ver`                     | TrasladoController  | Detalle del traslado        |
| POST   | `/traslados/actualizar-estado`       | TrasladoController  | Cambiar estado              |
| GET    | `/traslados/historial`               | TrasladoController  | Historial con filtros       |
| GET    | `/conductores`                       | ConductorController | Listado de conductores      |
| GET    | `/conductores/crear`                 | ConductorController | Formulario de creación      |
| POST   | `/conductores/crear`                 | ConductorController | Procesar creación           |
| GET    | `/rutas`                             | RutaController      | Listado de rutas            |
| GET    | `/rutas/crear`                       | RutaController      | Formulario de creación      |
| POST   | `/rutas/crear`                       | RutaController      | Procesar creación           |

---

## Tema visual

La interfaz usa un estilo **Web 2.0 retro** mezcla de **Old Facebook** + **Windows Classic**. CSS unificado en `public/css/web20.css`:

- **Paleta**: Azul Facebook `#3B5998`, fondos gris claro `#E9EAED`, tipografía Tahoma/Verdana.
- **Header**: `.web20-header` — barra azul oscuro estilo Windows titlebar + logo.
- **Sidebar**: `.web20-sidebar` — 200px, azul con links blancos, estilo portal 2000s.
- **Paneles**: `.panel` con cabezal azul degradado (`.panel-heading`), similar a Facebook wall boxes.
- **Botones**: `.btn` con gradiente 3D, `.btn-primary` azul Facebook.
- **Tablas**: `.table thead th` fondo azul, texto blanco — estilo Windows ListView.
- **Modales**: `.modal-box` con borde azul oscuro, como ventanas de diálogo clásicas.
- **Iconos**: FamFamFam Silk icons (16×16) para acciones.
- **Modo oscuro**: Toggle en navbar con clase `dark-mode` en `<body>`.

---

## Roadmap

| Sprint | Estado     | Descripción                                              |
|--------|-----------|----------------------------------------------------------|
| S0     | ✅ Completo | Fundación & Seguridad: entities, VOs, auth, CSRF, rate limit, session |
| S1     | 🔄 En curso | Capa Aplicación, Repos y Servicios faltantes. App Layer vacía, faltan TrasladoRepo, ConductorRepo, QRService, FileService. Stubs: Conductor y Ruta controllers. |
| S2     | ⬜ Pendiente | Gestión de Usuarios: CRUD funcionarios, baja lógica. Refinar dashboard paciente y perfil. |
| S3     | ⬜ Pendiente | Reportes & Gráficos: Chart.js, export CSV/PDF, filtros fecha. |
| S4     | ⬜ Pendiente | UX Avanzado: calendario traslados, mapa rutas, búsqueda global, drag & drop. |
| S5     | ⬜ Pendiente | PWA & Mobile: manifest, service worker, responsive final. |
| S6     | ⬜ Pendiente | Polish & Accesibilidad: animaciones, WCAG AA, lazy loading. |
| S7     | ⬜ Pendiente | Testing, Seguridad Producción & Despliegue: HTTPS, bloqueo cuentas, auditoría, fail2ban, Docker. |

**Nota:** Sprint 2–5 del plan original (frontend) están mayormente implementados (layout, docs, encuestas, traslados, perfiles, QR). El plan actual refleja el estado real del código. Ver `docs/planificacion-sprints.md` para detalle.

---

## Licencia

Proyecto interno del Hospital de Clínicas.
