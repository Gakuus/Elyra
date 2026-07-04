# Estructura de la Documentación — Elyra

## 📁 Estructura de Archivos

```
docs/
├── analisis/                           # Secciones 1-12
│   ├── 03-introduccion.md
│   ├── 04-marco-contextual.md
│   ├── 05-planteamiento-problema.md
│   ├── 06-justificacion.md
│   ├── 07-objetivos.md
│   ├── 07.1-utulab-mer.md              ← MER del sistema (13 tablas)
│   ├── 07.2-utulab-diagrama-tabla.md   ← Diagrama de tabla UTULAB
│   ├── 07.3-utulab-equipo.md           ← FODA del equipo de UTULAB
│   ├── 08-marco-teorico.md
│   ├── 08.1-sistemas-operativos.md     ← SO: relevamiento, justificación, manual Debian
│   ├── 08.2-programacion-ventanas.md   ← Bosquejo de estructuras de ventanas
│   ├── 09-metodologia.md               ← FODA, roles, herramientas, cómo realizamos tareas
│   ├── 10-requerimientos.md
│   ├── 11.1-casos-de-uso.md
│   ├── 11.2-diagramas-flujo.md
│   ├── 11.3-modelo-entidad-relacion.md
│   └── 12-historias-de-usuario.md
│

├── diseno/                             # Sección 12 - Diseño
│   ├── 01-diseno-interfaz.md
│   ├── 02-arquitectura-sistema.md
│   ├── 03-diseno-base-datos.md
│   └── mer-elyra.puml
│
├── desarrollo/                         # Secciones 13-18
│   ├── 01-desarrollo-implementacion.md
│   ├── 02-pruebas.md
│   ├── 03-recomendaciones.md
│   └── 04-bibliografia.md
│
├── estructura-documentacion.md         ← Este archivo
├── guia-apa-7ma-edicion.md
└── letra-proyecto.md
```

---

## 📝 Índice General del Documento

| Nº | Sección | Archivo | Materias |
|----|---------|---------|----------|
| 1 | Índice | — | — |
| 2 | Abstract | — | — |
| 3 | Introducción | `analisis/03-introduccion.md` | General |
| 4 | Marco Contextual | `analisis/04-marco-contextual.md` | General |
| 5 | Planteamiento del Problema | `analisis/05-planteamiento-problema.md` | General |
| 6 | Justificación | `analisis/06-justificacion.md` | General |
| 7 | Objetivos | `analisis/07-objetivos.md` | General |
| 7.1 | **MER del Sistema** | `analisis/07.1-utulab-mer.md` | General |
| 7.2 | **Diagrama de Tabla UTULAB** | `analisis/07.2-utulab-diagrama-tabla.md` | General |
| 7.3 | **FODA del Equipo UTULAB** | `analisis/07.3-utulab-equipo.md` | General |
| 8 | Marco Teórico | `analisis/08-marco-teorico.md` | General |
| 8.1 | **Sistemas Operativos** | `analisis/08.1-sistemas-operativos.md` | **Sistemas Operativos** |
| 8.2 | **Programación — Ventanas** | `analisis/08.2-programacion-ventanas.md` | **Programación** |
| 9 | Metodología de Trabajo | `analisis/09-metodologia.md` | **Ing. de Software** |
| 10 | Relevamiento y Análisis | `analisis/10-requerimientos.md` | General |
| 11 | Modelado del Sistema | `analisis/11.1-casos-de-uso.md`, `11.2-diagramas-flujo.md`, `11.3-modelo-entidad-relacion.md` | General |
| 12 | Diseño de la Solución | `diseno/01-diseno-interfaz.md`, `02-arquitectura-sistema.md`, `03-diseno-base-datos.md` | General |
| 13 | Desarrollo e Implementación | `desarrollo/01-desarrollo-implementacion.md` | General |
| 14 | Pruebas del Sistema | `desarrollo/02-pruebas.md` | General |
| 15 | Resultados | — | General |
| 16 | Conclusiones | — | General |
| 17 | Recomendaciones | `desarrollo/03-recomendaciones.md` | General |
| 18 | Bibliografía | `desarrollo/04-bibliografia.md` | General |
| 19 | Anexos | — | General |


---

## 📋 Detalle por Sección

### Portada
- Nombre de la institución
- Nombre del proyecto: **Elyra**
- Integrantes: Alan, Kevin, Tom
- Docentes
- Año: 2026
- Curso

### 1 — Índice
Generar automáticamente con el índice general de arriba.

### 2 — Abstract
Resumen ejecutivo del proyecto (máx. 250 palabras).

### 3 — Introducción (`analisis/03-introduccion.md`)
- Qué es el proyecto
- Por qué surge
- Qué necesidad intenta resolver
- Breve descripción del sistema/producto

### 4 — Marco Contextual (`analisis/04-marco-contextual.md`)
- Situación actual del Hospital de Clínicas
- Cómo trabajan actualmente
- Qué dificultades existen
- Quiénes son los usuarios

### 5 — Planteamiento del Problema (`analisis/05-planteamiento-problema.md`)
- ¿Qué ocurre?
- ¿Por qué es un problema?
- ¿A quién afecta?
- ¿Qué consecuencias tiene?

### 6 — Justificación (`analisis/06-justificacion.md`)
- Por qué vale la pena hacer el proyecto
- Beneficios: educativos, sociales, organizacionales, tecnológicos

### 7 — Objetivos (`analisis/07-objetivos.md`)
- Objetivo General
- Objetivos Específicos

### 7.1 — MER del Sistema (`analisis/07.1-utulab-mer.md`)
- Modelo Entidad Relación del sistema Elyra (13 tablas)
- Módulos: Identidad, Documentación, Ambulancias
- Relaciones entre entidades

### 7.2 — Diagrama de Tabla UTULAB (`analisis/07.2-utulab-diagrama-tabla.md`)
- Modelado 3D del logo de UTULAB en Blender
- Metodología de trabajo: modelado, materiales, iluminación, renderizado

### 7.3 — FODA del Equipo UTULAB (`analisis/07.3-utulab-equipo.md`)
- Descripción del equipo (Alan, Kevin, Tom) y sus roles
- Matriz FODA enfocada en el equipo (Fortalezas, Debilidades, Oportunidades, Amenazas)
- Estrategias derivadas (FO, DO, FA, DA)
- Conclusión del análisis

### 8 — Marco Teórico (`analisis/08-marco-teorico.md`)
- Sistemas de Información
- Ciclo de Vida del Software
- Metodologías Ágiles
- Bases de Datos Relacionales
- Arquitectura Hexagonal
- Desarrollo Web (HTML5, CSS3, Bootstrap 5, JavaScript ES6+)
- Códigos QR
- Ciberseguridad

### 8.1 — Sistemas Operativos (`analisis/08.1-sistemas-operativos.md`)
- Relevamiento de SO para servidor y terminales
- Justificación de Debian 12
- Manual de instalación paso a paso de Debian 12 (Bookworm)
- Configuración post-instalación (LAMP)

### 8.2 — Programación: Estructuras de Ventanas (`analisis/08.2-programacion-ventanas.md`)
- Árbol de navegación del Módulo de Documentación
- Árbol de navegación del Módulo de Ambulancias
- Diagramas de flujo de ventanas
- Mapa de navegación general
- Convenciones de la interfaz

### 9 — Metodología de Trabajo (`analisis/09-metodologia.md`) — *Ingeniería de Software*
- Marco de trabajo (Scrum)
- Roles del equipo con responsabilidades detalladas
- Herramientas utilizadas (con versiones)
- Cómo realizamos las tareas hasta ahora (etapas)
- Organización del trabajo
- Cronograma
- Flujo de trabajo
- Análisis FODA completo (Fortalezas, Debilidades, Oportunidades, Amenazas)
- Estrategias derivadas del FODA

### 10 — Relevamiento y Análisis (`analisis/10-requerimientos.md`)
- Requerimientos Funcionales — Módulo Documentación (RF-01 a RF-13)
- Requerimientos Funcionales — Módulo Ambulancias (RF-14 a RF-21)
- Requerimientos No Funcionales (RNF-01 a RNF-10)
- Restricciones (RES-01 a RES-03)

### 11 — Modelado del Sistema
- **Casos de Uso** (`analisis/11.1-casos-de-uso.md`)
- **Diagramas de Flujo** (`analisis/11.2-diagramas-flujo.md`)
- **MER del Sistema** (`analisis/11.3-modelo-entidad-relacion.md`): 13 tablas, relaciones, índices

### 12 — Diseño de la Solución
- **Diseño de Interfaz** (`diseno/01-diseno-interfaz.md`): 10 pantallas, diseño responsive, paleta de colores
- **Arquitectura del Sistema** (`diseno/02-arquitectura-sistema.md`): Hexagonal, cliente-servidor
- **Diseño de Base de Datos** (`diseno/03-diseno-base-datos.md`): MR completo, DDL, índices, políticas CASCADE

### 13 — Desarrollo e Implementación (`desarrollo/01-desarrollo-implementacion.md`)
- Tecnologías (PHP, MySQL, HTML, CSS, JS, Bootstrap)
- Estructura del proyecto
- Explicación de módulos

### 14 — Pruebas del Sistema (`desarrollo/02-pruebas.md`)
- Pruebas funcionales y de caja negra

### 15 — Resultados
- *(A completar al finalizar el desarrollo)*

### 16 — Conclusiones
- *(A completar al finalizar el desarrollo)*

### 17 — Recomendaciones (`desarrollo/03-recomendaciones.md`)
- Posibles mejoras futuras

### 18 — Bibliografía (`desarrollo/04-bibliografia.md`)
- Referencias APA 7ª edición

### 19 — Anexos
- *(Según sea necesario: entrevistas, código, capturas, etc.)*


