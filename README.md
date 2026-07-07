# Elyra

Sistema de gestión hospitalaria para el **Hospital de Clínicas**, con interfaz de escritorio estilo Windows clásico.

## Módulos

- **Gestión de documentación para pacientes** — Carga y gestión de documentos informativos con acceso mediante código QR + encuestas de satisfacción con gráficos estadísticos (Chart.js).
- **Trazabilidad de ambulancias** — Registro y seguimiento de traslados en ambulancia con gestión de rutas a nivel nacional.
- **Perfil de usuario** — Autogestión de datos personales, foto de perfil, cédula de identidad y cambio de contraseña.
- **Panel de administración** — Dashboard con acceso según roles (admin, superadmin, conductor).

## Stack

- PHP 8.5
- MySQL / MariaDB
- HTML5 / CSS3 / JavaScript (ES6+)
- Bootstrap 5 + Chart.js 4
- Interfaz visual con temática Windows clásica (`classic.css`)

## Arquitectura

Hexagonal (Puertos y Adaptadores) siguiendo principios DDD.

```
src/
├── Domain/          → Entidades, Value Objects, repositorios, servicios de dominio
├── Application/     → Puertos (interfaces) y casos de uso
└── Infrastructure/  → Adaptadores: persistencia (MySQL), web (controllers), servicios externos
```

Las vistas están en `views/` (no dentro de `src/`).

## Instalación

```bash
git clone <repo>
cd elyra
composer install
cp .env.example .env
# Editar .env con credenciales de base de datos
# Crear la base de datos y ejecutar el schema:
mysql -u usuario -p elyra < database/schema.sql
# (Opcional) Poblar con datos de prueba:
php database/seeds/seed.php
```

## Desarrollo

```bash
# Servidor embebido
php8.5 -S 127.0.0.1:8084 -t public

# Crear rama desde main
git checkout -b <nombre>-features
# Trabajar, commitar y abrir PR contra main
```

## Tema visual

La interfaz usa un theme Windows clásico (ventanas con `win-panel`, `win-titlebar`, campos `win-field`, botones `win-btn`). Soporta modo oscuro con toggle en la barra de navegación.
