# Automatización BDD — Behat + Mink + BrowserKit

## Stack

| Capa | Tecnología | Rol |
|------|-----------|-----|
| Motor BDD | Behat 3.15 | Ejecuta features Gherkin, mapea steps a PHP |
| Lenguaje | Gherkin (`# language: es`) | Define escenarios en lenguaje natural |
| Cliente HTTP | Mink 1.12 + BrowserKit Driver | Navegador headless sin JS |
| Servidor | PHP built-in (localhost:9876) | Sirve la app real durante los tests |
| BD | MySQL (misma que desarrollo) | Datos reales, no mocks |
| Aserciones | PHPUnit 13.2 | `assertStringContainsString`, etc. |

## Arquitectura

```
Behat
  └─ behat.yml → 5 suites (una por feature)
       └─ FeatureContext.php
            ├─ __construct() → cleanupRateLimit() + startServer()
            ├─ step definitions (35 métodos)
            └─ Mink session (BrowserKit)
                 └─ http://localhost:9876 → PHP built-in server
                      └─ public/index.php (APP_ENV=testing)
                           └─ MySQL (elyra)
```

### Servidor embebido

El `FeatureContext` levanta automáticamente un servidor PHP en el puerto 9876:

```
php -S localhost:9876 -t public public/index.php
```

Se mata con `pkill -f "localhost:9876"` al finalizar. El servidor es real: misma app, misma BD, mismo middleware (CSRF, rate limiter, sesión).

### Rate limiter

El rate limiter de login escribe archivos en `storage/rate-limit/`. Como las pruebas consumen intentos, se limpia al inicio con:

```php
private static function cleanupRateLimit(): void
{
    $dir = __DIR__ . '/../../storage/rate-limit';
    if (is_dir($dir)) {
        array_map('unlink', glob($dir . '/*'));
    }
}
```

### CSRF

Cada GET a `/login` genera un token CSRF nuevo. BrowserKit mantiene la cookie de sesión entre requests, por lo que el POST envía el mismo token que recibió en el GET. El flujo es:

1. `voyALaPagina('login')` → GET `/login` → recibe formulario con `_csrf_token`
2. `rellenoCon('username', 'admin')` → llena campo
3. `presiono('Iniciar Sesión')` → POST `/login` con `_csrf_token` + credenciales

### Sesión entre scenarios

El servidor PHP mantiene sesiones en archivos. Entre scenarios, la misma cookie de sesión puede quedar activa. Para evitar que un scenario arranque ya autenticado, cada login comienza con:

```php
$this->visitPath('/logout');
$this->visitPath('/login');
```

## Features y escenarios

| Archivo | Escenarios | Estado |
|---------|-----------|--------|
| `features/auth.feature` | 4 | ✅ 4/4 |
| `features/documentos.feature` | 6 | ✅ 6/6 |
| `features/encuestas.feature` | 2 | ✅ 2/2 |
| `features/traslados.feature` | 5 | ❌ 0/5 (no implementado) |
| `features/rutas.feature` | 2 | ✅ 1/2 (listar sí, crear no) |

## Paso a paso: cómo funciona una prueba

Tomemos `features/auth.feature`:

```gherkin
# language: es

Característica: Autenticación de usuarios
  Como funcionario del hospital
  Quiero iniciar y cerrar sesión en el sistema
  Para acceder a los módulos protegidos

  Antecedentes:
    Dado estoy en la página de login

  Escenario: Login exitoso con credenciales válidas
    Cuando relleno "username" con "admin"
    Y relleno "password" con "admin"
    Y presiono "Iniciar Sesión"
    Entonces la URL debería contener "dashboard"
    Y debería ver "Panel"
```

Lo que ejecuta Behat:

1. Lee `behat.yml` → suite `auth` → `FeatureContext`
2. `__construct()` → limpia rate limiter, arranca servidor :9876
3. **Antecedentes** → `voyALaPagina('login')` → Mink visita `http://localhost:9876/login`
4. **Cuando** → `rellenoCon('username', 'admin')` → llena `<input name="username">`
5. **Y** → `rellenoCon('password', 'admin')` → llena `<input name="password">`
6. **Y** → `presiono('Iniciar Sesión')` → submit del formulario
7. **Entonces** → `urlDeberiaContener('dashboard')` → verifica URL redirigida
8. **Y** → `deberiaVer('Panel')` → verifica texto en el HTML

## Cómo correrlo

```bash
# Todo junto
composer test:bdd

# Por feature
composer behat:auth
composer behat:docs
composer behat:encuestas

# PHPUnit + Behat
composer test:all
```

## Problemas conocidos

| Problema | Solución |
|----------|----------|
| `# language: es` no encuentra i18n.php | Vendor patches (2 archivos) |
| `pdo_mysql` no disponible | PHP 8.5 compilado con `mysql` USE flag |
| `Dado que` inválido | Usar solo `Dado` |
| Rate limiter bloquea pruebas | Se limpia automáticamente al iniciar |
| BrowserKit sin JS | No puede probar formularios dinámicos (JS) |

## Vendor patches

Dos archivos requieren parches manuales porque la ruta relativa a `i18n.php` está mal en Behat 3.15:

1. `vendor/behat/gherkin/src/Behat/Gherkin/Keywords/CachedArrayKeywords.php:76`:
   `__DIR__.'/../../../i18n.php'` → `__DIR__.'/../i18n.php'`

2. `vendor/behat/behat/src/Behat/Behat/Gherkin/ServiceContainer/GherkinExtension.php:157`:
   `__DIR__.'/../../../../../gherkin/i18n.php'` → `__DIR__.'/../../../../gherkin/i18n.php'`

## Limitaciones

- **Sin JavaScript**: BrowserKit no ejecuta JS. Features con Chart.js, validaciones JS o formularios dinámicos (ej: agregar preguntas a encuesta) no son testeables.
- **Solución**: agregar `symfony/panther` para un driver con Chrome real.
- **Vendor patches frágiles**: `composer update` los revierte.
- **Solución**: empaquetar como parche vía `cweagans/composer-patches`.

## Resultados actuales

```
19 escenarios (13 pasaron, 6 fallaron)
85 pasos (66 pasaron, 6 fallaron, 13 saltadas)
0m4.60s (14.72Mb)
```

Los 6 fallos corresponden a features aún no implementadas en la app (traslados, crear ruta).
