# 9 - Metodología de Trabajo

## Marco de Trabajo

Se adopta **Scrum** como metodología ágil para la gestión del proyecto, adaptada al contexto académico y al tamaño del equipo.

## Roles del Equipo

| Rol | Responsable | Responsabilidades |
|---|---|---|
| Product Owner | Docente / Cliente (Hospital de Clínicas) | Define prioridades, valida entregables, aporta visión del producto |
| Scrum Master | Tom | Facilita las ceremonias Scrum, elimina bloqueos, asegura que se siga la metodología |
| Analista / Documentador | Alan | Relevamiento de requerimientos, documentación del proyecto, historias de usuario |
| Desarrollador Backend | Kevin | Implementación de la lógica de negocio, base de datos, API en PHP |
| Desarrollador Frontend | Tom | Interfaz de usuario con HTML/CSS/JS/Bootstrap, diseño responsive |
| Tester / QA | Alan | Pruebas funcionales, validación de requerimientos, reporte de bugs |
| Diseñador UX/UI | Kevin | Bocetos de interfaz, estructura de ventanas, experiencia de usuario |
| Administrador de BD | Tom | Diseño del MER, modelo relacional, migraciones y optimización de consultas |

> Los roles no son excluyentes: todos los miembros participan en varias áreas según la necesidad del sprint.

## Herramientas Utilizadas

| Propósito | Herramienta | Versión / Detalle |
|---|---|---|
| Control de versiones | Git + GitHub | Repositorio compartido, ramas individuales, Pull Requests |
| Gestión de tareas | GitHub Projects | Tablero Kanban con columnas: Pendiente, En curso, En revisión, Completado |
| Comunicación | WhatsApp / Discord | Comunicación diaria, reuniones por Discord para planificación |
| Diseño de interfaz | Diagramas en docs/ | Bosquejos en markdown, estructura de ventanas documentada |
| Base de datos | MySQL / MariaDB | Workbench para modelado, SQL nativo |
| IDE Backend | VS Code | PHP, JavaScript, SQL |
| IDE Frontend | VS Code | HTML, CSS, Bootstrap |
| Documentación | Markdown + Google Docs | Documentación técnica en el repositorio, informes en Google Drive |
| Diagramas | PlantUML | Diagramas MER y de flujo en código |
| CI/CD | GitHub Actions | Validación automática de sintaxis PHP, JS y CSS en cada PR |
| Modelado 3D | Blender | Creación del logo de UTULAB |
| Servidor local | XAMPP / Laragon | Entorno de desarrollo local con Apache + MySQL + PHP |

## Cómo Realizamos las Tareas Hasta Ahora

### Etapa 1: Análisis y Relevamiento

En esta etapa inicial, el equipo trabajó en conjunto con el docente y el cliente (Hospital de Clínicas) para entender la problemática y definir los alcances del proyecto.

**Actividades realizadas:**
1. **Reunión inicial con el cliente**: se identificaron las necesidades del hospital en cuanto a digitalización de documentos informativos para pacientes y trazabilidad de ambulancias.
2. **Definición del problema**: se redactó el planteamiento del problema, la justificación y los objetivos del proyecto.
3. **Relevamiento de requerimientos**: se identificaron y documentaron los requerimientos funcionales y no funcionales de ambos módulos.
4. **Investigación de tecnologías**: se evaluaron las tecnologías a utilizar (PHP 8.1+, MySQL, Bootstrap 5, arquitectura hexagonal).
5. **Análisis FODA**: se realizó un análisis de fortalezas, debilidades, oportunidades y amenazas del equipo y del proyecto.

**Participantes**: Alan (documentación), Kevin (requerimientos técnicos), Tom (análisis FODA y objetivos).

### Etapa 2: Diseño del Sistema

Una vez definidos los requerimientos, se procedió al diseño de la solución.

**Actividades realizadas:**
1. **Diseño de base de datos**: se creó el MER con 13 tablas organizadas en 3 módulos (identidad, documentación, ambulancias), se definieron relaciones, índices y políticas de borrado.
2. **Diseño de interfaz**: se bosquejaron las pantallas principales del sistema (dashboard, formularios, detalle, vista paciente) con su estructura de navegación.
3. **Arquitectura del sistema**: se definió la arquitectura hexagonal (puertos y adaptadores) con separación en capas de dominio, aplicación e infraestructura.
4. **Diseño de ventanas**: se documentó el árbol de navegación y el flujo entre pantallas para ambos módulos.

**Participantes**: Tom (MER y BD), Kevin (arquitectura), Alan (interfaz y ventanas).

### Etapa 3: Documentación

De forma paralela al diseño, se fue construyendo la documentación completa del proyecto.

**Actividades realizadas:**
1. **Estructura de documentación**: se definió el índice y la estructura de la documentación siguiendo la guía APA 7ª edición.
2. **Documentación de análisis**: introducción, marco contextual, planteamiento del problema, justificación, objetivos, marco teórico.
3. **Documentación de diseño**: casos de uso, diagramas de flujo, MER, diseño de interfaz, arquitectura, diseño de BD.
4. **Documentación de desarrollo**: tecnologías utilizadas, estructura del proyecto, explicación de módulos.
5.  **Documentación del logo UTULAB**: modelado 3D del logo en Blender, proceso de renderizado.

**Participantes**: Alan (doc. análisis), Tom (doc. diseño y desarrollo), Kevin (doc. UTULAB y marco teórico).

### Etapa 4: Configuración del Entorno de Desarrollo

**Actividades realizadas:**
1. **Repositorio Git**: se inicializó el repositorio con rama `main` y ramas individuales (`alan-features`, `kevin-features`, `tom-features`).
2. **CI/CD**: se configuró GitHub Actions para validar sintaxis de PHP, JS y CSS automáticamente en cada Pull Request.
3. **Base de datos**: se escribió el DDL completo (database/schema.sql) con las 13 tablas, índices y claves foráneas.
4. **Estructura del proyecto**: se organizó el código siguiendo la arquitectura hexagonal (src/Domain, src/Application, src/Infrastructure).

**Participantes**: Tom (repositorio y CI), Kevin (estructura del proyecto), Alan (documentación de configuración).

### Resumen del Progreso

| Etapa | Estado | Fecha estimada |
|-------|--------|----------------|
| Análisis y relevamiento | ✅ Completo | Semana 1-2 |
| Diseño del sistema | ✅ Completo | Semana 3-4 |
| Documentación | ✅ Completo | Semana 3-6 |
| Configuración del entorno | ✅ Completo | Semana 4-5 |
| Desarrollo backend | 🔄 En progreso | Semana 5-8 |
| Desarrollo frontend | ⏳ Pendiente | Semana 6-9 |
| Pruebas | ⏳ Pendiente | Semana 9-10 |
| Despliegue | ⏳ Pendiente | Semana 10-11 |

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

## Análisis FODA del Proyecto

### Fortalezas (Interno - Proyecto)

| Fortaleza | Descripción |
|---|---|
| F1. Arquitectura hexagonal y DDD | El diseño por capas (dominio, aplicación, infraestructura) garantiza separación de responsabilidades, testabilidad y facilidad de mantenimiento. |
| F2. Cobertura completa del ciclo de vida del software | El proyecto abarca análisis, diseño, desarrollo, pruebas y despliegue, siguiendo buenas prácticas de ingeniería de software. |
| F3. Documentación exhaustiva | Se mantiene documentación detallada de todas las fases (análisis, diseño, desarrollo, pruebas) siguiendo normas APA 7ª edición. |
| F4. Modelo de datos robusto | El MER con 13 tablas organizadas en 3 módulos (identidad, documentación, ambulancias) cubre los requisitos del negocio con relaciones, índices y políticas de integridad referencial bien definidas. |
| F5. CI/CD integrado desde el inicio | El pipeline de GitHub Actions valida automáticamente la sintaxis de PHP, JS y CSS en cada Pull Request, asegurando calidad continua. |
| F6. Diseño modular y escalable | La separación en módulos independientes (Documentación y Ambulancias) permite desarrollar, probar y desplegar cada uno de forma autónoma. |

### Debilidades (Interno - Proyecto)

| Debilidad | Descripción |
|---|---|
| D1. Sin autenticación propia | Elyra depende del sistema de autenticación centralizada del hospital, lo que limita la autonomía del proyecto y agrega una dependencia externa crítica. |
| D2. Cobertura de pruebas limitada | No se han definido pruebas automatizadas unitarias ni de integración; las pruebas previstas son funcionales y manuales. |
| D3. Sin despliegue en producción definido | El proyecto no cuenta con un entorno de producción ni un plan de despliegue concretos, solo un entorno local con XAMPP/Laragon. |
| D4. Documentación de API pendiente | No se ha generado documentación formal de los endpoints de la API (OpenAPI/Swagger), lo que dificulta la integración futura. |
| D5. Sin plan de contingencia ni backups | No hay una estrategia documentada para la recuperación ante fallos, backup de base de datos ni continuidad del servicio. |

### Oportunidades (Externo - Proyecto)

| Oportunidad | Descripción |
|---|---|
| O1. Digitalización del Hospital de Clínicas | El hospital busca modernizar sus procesos, lo que abre la puerta a futuras expansiones del sistema. |
| O2. Impacto real en pacientes | El sistema mejora la experiencia del paciente al brindar acceso inmediato a información médica desde su móvil. |
| O3. Diferenciación académica | Un proyecto con aplicación real y arquitectura moderna (hexagonal, QR, trazabilidad) destaca frente a proyectos académicos tradicionales. |
| O4. Posible escalabilidad | El diseño modular permite agregar nuevos módulos (historia clínica, turnos, facturación) en el futuro. |
| O5. Reducción de costo y papel | El sistema contribuye a la sostenibilidad al reducir la impresión masiva de documentos informativos. |
| O6. Integración con sistemas existentes | La autenticación centralizada y el panel del hospital permiten que Elyra se adopte sin cambiar la infraestructura actual. |

### Amenazas (Externo - Proyecto)

| Amenaza | Descripción |
|---|---|
| A1. Cambios en los sistemas del hospital | Si el hospital modifica su sistema de autenticación o infraestructura, Elyra podría requerir adaptaciones no planificadas. |
| A2. Restricciones de seguridad y datos sensibles | El manejo de información hospitalaria está sujeto a normativas (protección de datos, historia clínica) que podrían cambiar o interpretarse de forma más restrictiva. |
| A3. Dependencia del DTI del hospital | El alojamiento en servidores del DTI depende de la disponibilidad, prioridades y soporte del área de tecnología del hospital. |
| A4. Proyectos similares en paralelo | Otros equipos o instituciones podrían desarrollar soluciones parecidas, restando novedad o prioridad al proyecto. |
| A5. Cambios en los requisitos del cliente | El Hospital de Clínicas podría solicitar cambios funcionales tardíos que impacten el cronograma o el alcance definido. |
| A6. Limitaciones de tiempo académico | El calendario académico impone fechas de entrega fijas que pueden no alinearse con el avance real del desarrollo. |

### Estrategias derivadas del FODA

| Tipo | Estrategia |
|---|---|
| **FO** (Fortalezas + Oportunidades) | Aprovechar la arquitectura modular (F1, F6) y la documentación (F3) para posicionar a Elyra como una solución escalable (O4) que impulse la digitalización del hospital (O1) y genere impacto real en pacientes (O2). |
| **DO** (Debilidades + Oportunidades) | Compensar la falta de autenticación propia (D1) y despliegue definido (D3) alineándose con los sistemas existentes del hospital (O6), y priorizar la documentación de API (D4) para facilitar integraciones futuras (O4). |
| **FA** (Fortalezas + Amenazas) | Usar el CI/CD integrado (F5) y el diseño modular (F6) para responder rápidamente a cambios en los sistemas del hospital (A1) o en requisitos del cliente (A5) mediante iteraciones controladas. |
| **DA** (Debilidades + Amenazas) | Mitigar la dependencia del DTI (A3) y los cambios normativos (A2) definiendo un plan de contingencia temprano (D5) y estableciendo acuerdos formales con el hospital que fijen los compromisos de infraestructura y seguridad. |
