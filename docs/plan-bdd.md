# Plan de Implementación — BDD con Behat + Gherkin + Mink

## Stack

| Herramienta | Rol | Equivalente Java |
|---|---|---|
| **Behat 3.15** | Motor BDD que ejecuta `.feature` | Cucumber |
| **Gherkin** | Lenguaje Given/When/Then | Gherkin |
| **Mink 1.12** | Capa de abstracción sobre navegadores | Serenity WebDriver |
| **BrowserKit** | Cliente HTTP headless (sin JS, rápido) | — |
| **PHPUnit 13.2** | Aserciones dentro de Behat | JUnit |

## Estructura de archivos

```
features/
├── auth.feature              # HU-01 Login/logout (4 escenarios)
├── documentos.feature         # HU-02 a HU-06, HU-11 (6 escenarios)
├── encuestas.feature          # HU-08 a HU-10 (2 escenarios)
├── traslados.feature          # HU-12 a HU-14, HU-16 (5 escenarios)
├── rutas.feature              # HU-15 (2 escenarios)
tests/
└── Behat/
    └── FeatureContext.php     # Definiciones de pasos Gherkin + servidor embebido
behat.yml                      # Configuración de Behat (5 suites)
```

## Flujo de ejecución

1. Behat lee `behat.yml` → localiza `features/*.feature` y `FeatureContext`
2. `FeatureContext::__construct()` levanta PHP built-in server en `localhost:9876`
3. Por cada `Scenario`, Behat ejecuta los steps mapeados a métodos de `FeatureContext`
4. Mink (vía BrowserKit) hace requests HTTP reales a `http://localhost:9876`
5. El servidor embebido corre `public/index.php` con `APP_ENV=testing`
6. Las aserciones usan `PHPUnit` dentro de `FeatureContext`

## Instalación

```bash
composer require --dev \
    behat/behat \
    friends-of-behat/mink-extension \
    behat/mink-browserkit-driver
```

### Vendor patches necesarios

1. **`vendor/behat/gherkin/src/Behat/Gherkin/Keywords/CachedArrayKeywords.php`** line 76:
   `__DIR__.'/../../../i18n.php'` → `__DIR__.'/../i18n.php'`

2. **`vendor/behat/behat/src/Behat/Behat/Gherkin/ServiceContainer/GherkinExtension.php`** line 157:
   `__DIR__.'/../../../../../gherkin/i18n.php'` → `__DIR__.'/../../../../gherkin/i18n.php'`

## Composer scripts

| Comando | Descripción |
|---|---|
| `composer behat` | Ejecutar todos los features |
| `composer behat:auth` | Solo auth.feature |
| `composer behat:docs` | Solo documentos.feature |
| `composer behat:encuestas` | Solo encuestas.feature |
| `composer behat:traslados` | Solo traslados.feature |
| `composer behat:rutas` | Solo rutas.feature |
| `composer test:bdd` | `php vendor/bin/behat` |
| `composer test:all` | Ejecuta PHPUnit + Behat |

## Features y escenarios (19 escenarios, 91 pasos)

| Feature | Escenario | HU |
|---|---|---|
| `auth.feature` | Login exitoso con credenciales válidas | HU-01 |
| `auth.feature` | Login falla con credenciales inválidas | HU-01 |
| `auth.feature` | Login falla con usuario inexistente | HU-01 |
| `auth.feature` | Logout cierra sesión correctamente | HU-01 |
| `documentos.feature` | Subir un documento PDF | HU-02 |
| `documentos.feature` | Editar título de un documento | HU-03 |
| `documentos.feature` | Eliminar un documento (confirmación) | HU-04 |
| `documentos.feature` | Listar documentos filtrados por categoría | HU-05, HU-11 |
| `documentos.feature` | Descargar QR de un documento | HU-06 |
| `documentos.feature` | Listar documentos con paginación | HU-11 |
| `encuestas.feature` | Crear una encuesta con preguntas | HU-08 |
| `encuestas.feature` | Ver resultados de una encuesta | HU-10 |
| `traslados.feature` | Registrar un nuevo traslado | HU-12 |
| `traslados.feature` | Avanzar estado del traslado | HU-13 |
| `traslados.feature` | Cancelar un traslado | HU-13 |
| `traslados.feature` | Consultar traslados activos | HU-14 |
| `traslados.feature` | Ver historial de traslados | HU-16 |
| `rutas.feature` | Crear una nueva ruta | HU-15 |
| `rutas.feature` | Listar rutas existentes | HU-15 |

## Particularidades

- **Idioma:** los `.feature` usan `# language: es` con Gherkin español.
- **Keywords españolas:** `Característica`, `Escenario`, `Antecedentes`, `Dado`, `Cuando`, `Entonces`, `Y`.
- **No usar `Dado que`:** la keyword `given` en español es solo `Dado` (no acepta `que`).
- **Driver:** se usa `browserkit_http` (headless, sin JS). No requiere Chrome/WebDriver.
- **Servidor embebido:** se levanta automáticamente en `localhost:9876` y se mata al finalizar.

## Resultados finales — Julio 2026

**Total:** 19 escenarios, 85 pasos, ejecución ~4.6s.

| Estado | Cant. | Features |
|--------|-------|----------|
| ✅ Pasan | 13 | `auth` (4/4), `documentos` (6/6), `encuestas` (2/2), `rutas listar` (1/2) |
| ❌ Fallan | 6 | `traslados` (5) — módulo no implementado, `rutas crear` (1) — formulario no existe |

**Detalle por feature:**
- `auth.feature` — 4/4 ✅ Login/logout con y sin credenciales
- `documentos.feature` — 6/6 ✅ CRUD completo + filtros + QR
- `encuestas.feature` — 2/2 ✅ Creación (página) + resultados
- `traslados.feature` — 0/5 ❌ Huérfano (no implementado)
- `rutas.feature` — 1/2 ✅ Listar; ❌ Crear (formulario no existe)

**Problemas resueltos:**
- `pdo_mysql` faltante → PHP compilado con `mysql` USE flag (PHP 8.5.6)
- Rate limiter bloqueaba pruebas → `FeatureContext::cleanupRateLimit()` limpia `storage/rate-limit/` al iniciar
- Sesión persistía entre scenarios → `visitPath('/logout')` antes de cada login
- Password incorrecta en step de autenticación → seeder usa `admin`, no `password`

## Limitaciones y alternativas

- **BrowserKit** no ejecuta JavaScript. Para features con Chart.js o validaciones JS se necesita PantherDriver.
- Para agregar Panther: `composer require --dev symfony/panther` + configurar el driver en `behat.yml`.
- **`pdo_mysql` requerido:** el servidor embebido necesita `pdo_mysql` para conectar a la BD. Sin esta extensión, los pasos fallan en ejecución real (aunque el dry-run funciona).
- Alternativa: configurar SQLite para entorno testing.
