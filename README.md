# Elyra

Sistema de gestión hospitalaria para el **Hospital de Clínicas**.

## Módulos

- **Gestión de documentación para pacientes** — Carga y gestión de documentos informativos con acceso mediante código QR + encuestas de satisfacción.
- **Trazabilidad de ambulancias** — Registro y seguimiento de traslados en ambulancia con gestión de rutas a nivel nacional.

## Stack

- PHP ≥ 8.1
- MySQL
- HTML5 / CSS3 / JavaScript (ES6+)
- Bootstrap 5

## Arquitectura

Hexagonal (Puertos y Adaptadores) siguiendo principios DDD.

```
src/
├── Application/     → Puertos (interfaces) y casos de uso
├── Domain/          → Entidades, Value Objects, repositorios, servicios de dominio
└── Infrastructure/  → Adaptadores: persistencia (MySQL), web (controllers), servicios externos
```

## Instalación

```bash
composer install
cp .env.example .env
# Configurar base de datos
php migrations.php
```

## Desarrollo

1. Crear rama desde `main`: `git checkout -b <nombre>-features`
2. Trabajar en la rama correspondiente
3. Al finalizar, abrir PR contra `main`


