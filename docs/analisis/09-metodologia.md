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

## Análisis FODA del Equipo y del Proyecto

### Fortalezas (Interno - Equipo)

| Fortaleza | Descripción |
|---|---|
| F1. Equipo multidisciplinario | Alan, Kevin y Tom aportan perspectivas y habilidades complementarias. |
| F2. Conocimiento técnico en las tecnologías requeridas | El equipo domina PHP, MySQL, JavaScript, HTML/CSS y Bootstrap, que son las tecnologías centrales del proyecto. |
| F3. Trabajo colaborativo con Git y GitHub | El equipo utiliza ramas individuales, Pull Requests y revisiones cruzadas, lo que asegura calidad y control de versiones. |
| F4. Metodología ágil adaptada | Scrum adaptado al contexto académico permite entregas incrementales y mejora continua. |
| F5. Documentación completa y organizada | La documentación cubre todas las fases del ciclo de vida: análisis, diseño, desarrollo y pruebas. |
| F6. Conocimiento del dominio del problema | El equipo comprende el contexto hospitalario y las necesidades del Hospital de Clínicas. |

### Debilidades (Interno - Equipo)

| Debilidad | Descripción |
|---|---|
| D1. Equipo pequeño (3 personas) | La carga de trabajo es alta y hay poca redundancia si un miembro se ausenta. |
| D2. Experiencia limitada en arquitectura hexagonal | Es la primera implementación de este patrón para el equipo, lo que puede ralentizar el desarrollo inicial. |
| D3. Disponibilidad de tiempo parcial | Al ser un proyecto académico, los integrantes tienen otras responsabilidades que limitan las horas dedicadas. |
| D4. Falta de experiencia en CI/CD | Si bien hay un workflow básico, la experiencia en integración y despliegue continuos es limitada. |
| D5. Roles de Scrum no definidos completamente | El Scrum Master aún no está asignado, y no hay experiencia previa con ceremonias Scrum formales. |

### Oportunidades (Externo - Proyecto)

| Oportunidad | Descripción |
|---|---|
| O1. Digitalización del Hospital de Clínicas | El hospital busca modernizar sus procesos, lo que abre la puerta a futuras expansiones del sistema. |
| O2. Impacto real en pacientes | El sistema mejora la experiencia del paciente al brindar acceso inmediato a información médica desde su móvil. |
| O3. Diferenciación académica | Un proyecto con aplicación real y arquitectura moderna (hexagonal, QR, trazabilidad) destaca frente a proyectos académicos tradicionales. |
| O4. Posible escalabilidad | El diseño modular permite agregar nuevos módulos (historia clínica, turnos, facturación) en el futuro. |
| O5. Reducción de costo y papel | El sistema contribuye a la sostenibilidad al reducir la impresión masiva de documentos. |
| O6. Integración con sistemas existentes | La autenticación centralizada y el panel del hospital permiten que Elyra se adopte sin cambiar la infraestructura actual. |

### Amenazas (Externo - Proyecto)

| Amenaza | Descripción |
|---|---|
| A1. Cambios en los sistemas del hospital | Si el hospital modifica su sistema de autenticación o infraestructura, Elyra podría requerir adaptaciones no planificadas. |
| A2. Restricciones de seguridad y datos sensibles | El manejo de información hospitalaria está sujeto a normativas que podrían cambiar o interpretarse de forma más restrictiva. |
| A3. Dependencia del DTI del hospital | El alojamiento en servidores del DTI depende de la disponibilidad y soporte del área de tecnología del hospital. |
| A4. Proyectos similares en paralelo | Otros equipos o instituciones podrían desarrollar soluciones similares, restando novedad al proyecto. |
| A5. Curva de aprendizaje tecnológico | La arquitectura hexagonal y la generación de QR requieren aprendizaje adicional que puede retrasar el cronograma. |
| A6. Limitaciones de tiempo académico | El calendario académico impone fechas de entrega fijas que pueden no alinearse con el avance real del desarrollo. |

### Estrategias derivadas del FODA

| Tipo | Estrategia |
|---|---|
| **FO** (Fortalezas + Oportunidades) | Aprovechar el conocimiento técnico del equipo (F2) y la documentación completa (F5) para entregar un sistema robusto que demuestre el impacto real (O2) y la escalabilidad (O4) del proyecto. |
| **DO** (Debilidades + Oportunidades) | Compensar el equipo pequeño (D1) invirtiendo en automatización (CI/CD) y priorizando las funcionalidades de mayor impacto (O2, O5) para maximizar el valor entregado con recursos limitados. |
| **FA** (Fortalezas + Amenazas) | Usar el trabajo colaborativo con Git (F3) y la metodología ágil (F4) para responder rápido a cambios del hospital (A1) o adaptaciones de seguridad (A2) mediante iteraciones cortas. |
| **DA** (Debilidades + Amenazas) | Mitigar la falta de experiencia en arquitectura hexagonal (D2) y CI/CD (D4) mediante la creación de un spike/prototipo temprano que valide la arquitectura antes del desarrollo completo, reduciendo el riesgo de retrasos (A5, A6). |
