# Contribuir a Elyra

Gracias por tu interés en contribuir. Estas son las pautas básicas.

## Pull Requests

1. Creá un fork y una rama con nombre descriptivo (`feature/...`, `fix/...`).
2. Seguí el estilo de código existente (PHP PSR-4, sin tabs, sin comentarios superfluos). El `.editorconfig` mantiene consistencia básica.
3. Asegurate de que pase los checks de CI: `npm run lint` y `php -l`.
4. Actualizá la documentación si corresponde.
5. Incluí tests cuando sea posible.
6. Hacé squash de commits si hace falta antes del merge.

## Reportar bugs

Usá la plantilla de **Bug Report** del repo. Incluí pasos concretos para reproducir.

## Convenciones de código

- PHP: tipado estricto (`declare(strict_types=1)`), sin comentarios inline.
- CSS: clases semánticas en inglés, Web 2.0 retro style (Tahoma, #3B5998, bordes 1px).
- JS: ES6+, evitar dependencias externas pesadas.
- Commits: [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `style:`, `chore:`, `docs:`, `refactor:`).
