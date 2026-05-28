# 9 - Metodología de Trabajo

## Marco de Trabajo

Se adopta **Scrum** como metodología ágil para la gestión del proyecto, adaptada al contexto académico y al tamaño del equipo.

## Roles del Equipo

| Rol | Responsable |
|---|---|
| Product Owner | Docente / Cliente (Hospital de Clínicas) |
| Scrum Master | A definir por el equipo |
| Equipo de Desarrollo | Alan, Kevin, Tom |

## Herramientas Utilizadas

| Propósito | Herramienta |
|---|---|
| Control de versiones | Git + GitHub |
| Gestión de tareas | GitHub Projects / Tablero Kanban |
| Comunicación | A definir por el equipo |
| Diseño de interfaz | A definir (Figma, Bootstrap Studio, etc.) |
| Base de datos | MySQL (Workbench o similar) |
| IDE / Editor | A definir por cada desarrollador |

## Organización del Trabajo

- Cada desarrollador trabaja en su rama personal (`<nombre>-features`).
- Al completar una funcionalidad, se abre un Pull Request contra `main`.
- El CI workflow valida sintaxis PHP, JS y CSS automáticamente en cada PR.
- Se requiere revisión de al menos otro miembro del equipo antes de mergear.

## Cronograma

> A completar por el equipo.

| Sprint | Duración | Entregables |
|---|---|---|
| Sprint 1 | A definir | Análisis y documentación |
| Sprint 2 | A definir | Diseño de BD y mockups |
| Sprint 3 | A definir | Desarrollo Módulo Documentación |
| Sprint 4 | A definir | Desarrollo Módulo Ambulancias |
| Sprint 5 | A definir | Integración y pruebas |
| Sprint 6 | A definir | Despliegue y documentación final |

## Flujo de Trabajo

1. **Planificación del Sprint**: se definen las tareas a realizar.
2. **Desarrollo**: cada miembro implementa en su rama.
3. **Revisión**: Pull Request + revisión cruzada.
4. **Pruebas**: verificación funcional de lo implementado.
5. **Retrospectiva**: análisis de lo que funcionó y lo que mejorar.
