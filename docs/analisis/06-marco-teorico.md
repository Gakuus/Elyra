# 8 - Marco Teórico

## Sistemas de Información

Un sistema de información es un conjunto de componentes interrelacionados que recolectan, procesan, almacenan y distribuyen información para apoyar la toma de decisiones, la coordinación y el control en una organización. En el ámbito hospitalario, los sistemas de información permiten gestionar datos clínicos, administrativos y operativos de forma centralizada.

Elyra se enmarca como un sistema de información web orientado a la gestión documental y al seguimiento logístico dentro del Hospital de Clínicas.

## Ciclo de Vida del Software

El desarrollo de software sigue un ciclo de vida que comprende las etapas de planificación, análisis, diseño, implementación, pruebas, despliegue y mantenimiento. Para este proyecto se adopta un enfoque iterativo, permitiendo la entrega gradual de funcionalidades y la incorporación de retroalimentación durante el proceso.

## Metodologías Ágiles

Las metodologías ágiles priorizan la entrega continua de valor, la colaboración con el cliente y la capacidad de respuesta ante cambios. Scrum y Kanban son dos de los marcos de trabajo más utilizados. Scrum organiza el trabajo en sprints con roles definidos (Product Owner, Scrum Master, equipo de desarrollo), mientras que Kanban se enfoca en la visualización del flujo de trabajo y la limitación del trabajo en progreso.

## Bases de Datos Relacionales

Una base de datos relacional organiza los datos en tablas compuestas por filas y columnas, relacionadas entre sí mediante claves primarias y foráneas. El lenguaje estándar para su manipulación es SQL (Structured Query Language). MySQL es un sistema de gestión de bases de datos relacional de código abierto ampliamente utilizado en aplicaciones web.

Para Elyra, se empleará MySQL para almacenar la información de documentos, usuarios, traslados, encuestas y rutas.

## Arquitectura Hexagonal (Puertos y Adaptadores)

La arquitectura hexagonal, propuesta por Alistair Cockburn, busca aislar la lógica de negocio (dominio) de los detalles técnicos externos (bases de datos, interfaces de usuario, servicios externos). Se compone de tres capas principales:

- **Dominio**: Contiene las entidades, objetos de valor y reglas de negocio. Es el núcleo de la aplicación y no depende de ninguna capa externa.
- **Aplicación**: Define los puertos (interfaces) y los casos de uso que orquestan la lógica de negocio.
- **Infraestructura**: Implementa los adaptadores que conectan la aplicación con el mundo exterior (base de datos MySQL, interfaces web, servicios externos).

Esta arquitectura facilita el testing, el mantenimiento y la evolución del sistema.

## Desarrollo Web (Frontend)

### HTML5

HTML (HyperText Markup Language) es el lenguaje estándar para la estructuración de contenido en la web. HTML5 introduce elementos semánticos que mejoran la accesibilidad y el posicionamiento en buscadores.

### CSS3

CSS (Cascading Style Sheets) es el lenguaje utilizado para definir la presentación y el diseño visual de las páginas web. CSS3 incorpora características como animaciones, transiciones, flexbox y grid layout.

### Bootstrap 5

Bootstrap es un framework de código abierto para el desarrollo de interfaces web responsivas. Proporciona componentes preconstruidos (botones, formularios, tablas, navegación, etc.) y un sistema de rejilla (grid) que facilita la adaptación a diferentes tamaños de pantalla.

### JavaScript (ES6+)

JavaScript es el lenguaje de programación que permite agregar interactividad a las páginas web. ECMAScript 6 (ES6) introdujo mejoras significativas como las funciones flecha, clases, módulos, promesas y los operadores let/const.

## Códigos QR

Un código QR (Quick Response) es un código de barras bidimensional que puede almacenar información legible por dispositivos móviles mediante una cámara. En Elyra, los códigos QR se utilizan como mecanismo de acceso rápido a los documentos informativos: cada documento tendrá un QR asociado que los pacientes podrán escanear para visualizar su contenido digital.

## Ciberseguridad

La ciberseguridad en aplicaciones web abarca la protección de datos sensibles, la autenticación segura de usuarios, la prevención de ataques (XSS, SQL injection, CSRF) y la comunicación cifrada mediante HTTPS. Dado que Elyra manejará información institucional y datos de pacientes, la implementación de medidas de seguridad es fundamental.
